<?php

namespace App\Http\Livewire\Project;

use App\Models\Project;
use App\Models\WikiPage;
use App\Models\BacklogItem;
use App\Services\OllamaService;
use App\Services\WikiPdfExportService;
use App\Jobs\GenerateBacklogFromWiki;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WikiView extends Component
{
    use WithFileUploads;
    public $projectId;
    public $pages;
    public $selectedPage;
    public $isEditing = false;
    public $isCreating = false;
    public $title = '';
    public $content = '';
    public $searchTerm = '';
    public $parentId = null;
    public $showDeleteConfirm = false;
    public $pageToDelete = null;
    public $showCommentDeleteConfirm = false;
    public $commentToDelete = null;
    public $clientVisible = false;
    public $newComment = '';
    public $replyToCommentId = null;
    public $signoffRemarks = '';
    public $isGeneratingBacklog = false;
    public $generationProgress = '';
    public $showBacklogPreview = false;
    public $generatedBacklog = [];
    public $jobId = null;
    public $generationPercentage = 0;
    public $trixAttachment;
    public $pendingAttachments = [];
    public $message = '';
    public $messageType = '';

    protected $listeners = ['refreshPages' => 'loadPages', 'checkJobProgress' => 'checkJobProgress'];

    public function mount($projectId)
    {
        $this->projectId = $projectId;
        $this->loadPages();
    }

    protected function setMessage($type, $message)
    {
        $this->message = $message;
        $this->messageType = $type;
        
        // Auto-clear message after 5 seconds
        $this->dispatchBrowserEvent('auto-clear-message', ['delay' => 5000]);
    }

    public function getProjectProperty()
    {
        return Project::findOrFail($this->projectId);
    }

    public function loadPages()
    {
        $query = $this->project->wikiPages()->with('children', 'creator', 'updater');
        
        if ($this->searchTerm) {
            $query->where('title', 'like', '%' . $this->searchTerm . '%');
        }
        
        $this->pages = $query->get();
    }

    public function canCreate()
    {
        return auth()->user()->can('Create wiki page');
    }

    public function canEdit()
    {
        return auth()->user()->can('Update wiki page');
    }

    public function canDelete()
    {
        return auth()->user()->can('Delete wiki page');
    }

    public function selectPage($pageId)
    {
        $page = WikiPage::with([
            'children',
            'comments.user',
            'comments.replies.user',
            'activeSignoffs.client',
            'signoffs'
        ])->find($pageId);

        if ($page) {
            $this->repairPersistedAttachmentUrls($page);
            $page->refresh()->load([
                'children',
                'comments.user',
                'comments.replies.user',
                'activeSignoffs.client',
                'signoffs'
            ]);
        }

        $this->selectedPage = $page;
        $this->isEditing = false;
        $this->newComment = '';
        $this->replyToCommentId = null;
    }

    public function editPage($pageId = null)
    {
        if (!$this->canEdit() && $pageId) {
            $this->setMessage('error', 'You do not have permission to edit wiki pages.');
            return;
        }

        if (!$this->canCreate() && !$pageId) {
            $this->setMessage('error', 'You do not have permission to create wiki pages.');
            return;
        }

        if ($pageId) {
            $page = WikiPage::find($pageId);
            $this->selectedPage = $page;
            $this->title = $page->title;
            $this->content = $page->content;
            $this->parentId = $page->parent_id;
            $this->clientVisible = $page->client_visible;
            $this->isCreating = false;
        } else {
            $this->title = '';
            $this->content = '';
            $this->selectedPage = null;
            $this->parentId = null;
            $this->clientVisible = false;
            $this->isCreating = true;
        }
        $this->isEditing = true;
        $this->emit('edit-mode-entered');
    }

    public function createSubPage($parentId)
    {
        if (!$this->canCreate()) {
            $this->setMessage('error', 'You do not have permission to create wiki pages.');
            return;
        }

        $this->title = '';
        $this->content = '';
        $this->selectedPage = null;
        $this->parentId = $parentId;
        $this->isCreating = true;
        $this->isEditing = true;
        $this->emit('edit-mode-entered');
    }

    public function savePage()
    {
        if ($this->selectedPage && !$this->canEdit()) {
            $this->setMessage('error', 'You do not have permission to edit wiki pages.');
            return;
        }

        if (!$this->selectedPage && !$this->canCreate()) {
            $this->setMessage('error', 'You do not have permission to create wiki pages.');
            return;
        }

        $this->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $pageId = null;

        if ($this->selectedPage) {
            $normalizedContent = $this->normalizeWikiContent($this->content, $this->selectedPage);

            // Update existing page
            $this->selectedPage->update([
                'title' => $this->title,
                'content' => $normalizedContent,
                'updated_by' => auth()->id(),
                'version' => $this->selectedPage->version + 1,
                'client_visible' => $this->clientVisible,
                'client_visible_at' => $this->clientVisible ? ($this->selectedPage->client_visible_at ?? now()) : null,
            ]);
            $this->content = $normalizedContent;
            $pageId = $this->selectedPage->id;
            $this->setMessage('success', 'Wiki page updated successfully!');
        } else {
            // Create new page
            $page = WikiPage::create([
                'project_id' => $this->project->id,
                'title' => $this->title,
                'content' => $this->content,
                'parent_id' => $this->parentId,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'client_visible' => $this->clientVisible,
                'client_visible_at' => $this->clientVisible ? now() : null,
            ]);
            $this->syncPendingAttachments($page);
            $normalizedContent = $this->normalizeWikiContent($page->content, $page);
            $page->update(['content' => $normalizedContent]);
            $this->content = $normalizedContent;
            $pageId = $page->id;
            $this->setMessage('success', 'Wiki page created successfully!');
        }

        $this->isEditing = false;
        $this->isCreating = false;
        $this->loadPages();
        $this->selectPage($pageId);
    }

    public function updatedTrixAttachment()
    {
        if ($this->trixAttachment) {
            $this->validate([
                'trixAttachment' => 'required|file|max:10240', // Max 10MB
            ]);

            if ($this->selectedPage) {
                $media = $this->selectedPage->addMedia($this->trixAttachment)
                    ->usingName($this->trixAttachment->getClientOriginalName())
                    ->toMediaCollection('wiki_attachments');

                $this->dispatchBrowserEvent('trix-attachment-uploaded', [
                    'url' => $media->getUrl(),
                    'filename' => $media->file_name,
                    'contentType' => $media->mime_type,
                ]);
            } else {
                $temporaryPath = $this->trixAttachment->store('wiki/temp', 'public');
                $temporaryUrl = Storage::disk('public')->url($temporaryPath);

                $this->pendingAttachments[] = [
                    'path' => $temporaryPath,
                    'url' => $temporaryUrl,
                    'filename' => $this->trixAttachment->getClientOriginalName(),
                    'content_type' => $this->trixAttachment->getMimeType(),
                ];

                $this->dispatchBrowserEvent('trix-attachment-uploaded', [
                    'url' => $temporaryUrl,
                    'filename' => $this->trixAttachment->getClientOriginalName(),
                    'contentType' => $this->trixAttachment->getMimeType(),
                ]);
            }

            $this->trixAttachment = null;
        }
    }

    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->title = '';
        $this->content = '';
    }

    public function confirmDelete($pageId)
    {
        if (!$this->canDelete()) {
            $this->setMessage('error', 'You do not have permission to delete wiki pages.');
            return;
        }

        $this->pageToDelete = $pageId;
        $this->showDeleteConfirm = true;
    }

    public function deletePage()
    {
        if (!$this->canDelete()) {
            $this->setMessage('error', 'You do not have permission to delete wiki pages.');
            return;
        }

        $page = WikiPage::find($this->pageToDelete);
        if ($page && $page->project_id === $this->project->id) {
            $childCount = $page->children()->count();
            $page->delete();
            
            if ($this->selectedPage && $this->selectedPage->id === $page->id) {
                $this->selectedPage = null;
            }
            
            $this->loadPages();
            
            if ($childCount > 0) {
                $this->setMessage('success', "Wiki page and {$childCount} sub-page(s) deleted successfully!");
            } else {
                $this->setMessage('success', 'Wiki page deleted successfully!');
            }
        }

        $this->showDeleteConfirm = false;
        $this->pageToDelete = null;
    }

    public function cancelDelete()
    {
        $this->showDeleteConfirm = false;
        $this->pageToDelete = null;
    }

    public function addComment()
    {
        if (!auth()->user()->can('Comment on wiki')) {
            $this->setMessage('error', 'You do not have permission to comment.');
            return;
        }

        $this->validate([
            'newComment' => 'required|string|max:5000',
        ]);

        $this->selectedPage->allComments()->create([
            'user_id' => auth()->id(),
            'content' => $this->newComment,
            'parent_comment_id' => $this->replyToCommentId,
        ]);

        $this->newComment = '';
        $this->replyToCommentId = null;
        $this->selectPage($this->selectedPage->id);
        $this->setMessage('success', 'Comment added successfully!');
    }

    public function replyToComment($commentId)
    {
        $this->replyToCommentId = $commentId;
    }

    public function cancelReply()
    {
        $this->replyToCommentId = null;
    }

    public function confirmDeleteComment($commentId)
    {
        $comment = \App\Models\WikiComment::find($commentId);
        
        if ($comment && $comment->canDelete()) {
            $this->commentToDelete = $commentId;
            $this->showCommentDeleteConfirm = true;
        } else {
            $this->setMessage('error', 'You cannot delete this comment.');
        }
    }

    public function deleteComment()
    {
        $comment = \App\Models\WikiComment::find($this->commentToDelete);
        
        if ($comment && $comment->canDelete()) {
            $comment->delete();
            $this->selectPage($this->selectedPage->id);
            $this->setMessage('success', 'Comment deleted successfully!');
        } else {
            $this->setMessage('error', 'You cannot delete this comment.');
        }

        $this->showCommentDeleteConfirm = false;
        $this->commentToDelete = null;
    }

    public function cancelDeleteComment()
    {
        $this->showCommentDeleteConfirm = false;
        $this->commentToDelete = null;
    }

    public function signOffPage()
    {
        if (!auth()->user()->hasRole('Client')) {
            $this->setMessage('error', 'Only clients can sign off documents.');
            return;
        }

        if (!auth()->user()->can('Sign off wiki')) {
            $this->setMessage('error', 'You do not have permission to sign off.');
            return;
        }

        // Check if already signed off
        $existingSignoff = $this->selectedPage->signoffs()
            ->where('client_id', auth()->id())
            ->where('version_signed', $this->selectedPage->version)
            ->where('is_outdated', false)
            ->first();

        if ($existingSignoff) {
            $this->setMessage('error', 'You have already signed off this version.');
            return;
        }

        $this->selectedPage->signoffs()->create([
            'client_id' => auth()->id(),
            'signed_off_by' => auth()->id(),
            'signed_off_at' => now(),
            'version_signed' => $this->selectedPage->version,
            'remarks' => $this->signoffRemarks,
            'is_outdated' => false,
        ]);

        $this->signoffRemarks = '';
        $this->selectPage($this->selectedPage->id);
        $this->setMessage('success', 'Document signed off successfully!');
    }

    public function exportPagePdf($pageId)
    {
        $page = WikiPage::findOrFail($pageId);
        
        if ($page->project_id !== $this->project->id) {
            $this->setMessage('error', 'Page not found.');
            return;
        }
        
        $pdfService = app(WikiPdfExportService::class);
        $pdf = $pdfService->exportPage($page);
        
        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, Str::slug($page->title) . '.pdf');
    }

    public function exportMasterPdf()
    {
        $pdfService = app(WikiPdfExportService::class);
        $pdf = $pdfService->exportProjectWiki($this->project);
        
        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, Str::slug($this->project->name) . '-wiki-master.pdf');
    }

    public function generateBacklogFromWiki()
    {
        try {
            // Generate unique job ID
            $this->jobId = Str::uuid()->toString();
            $this->isGeneratingBacklog = true;
            $this->generationProgress = 'Starting backlog generation...';
            $this->generationPercentage = 0;

            // Dispatch job
            GenerateBacklogFromWiki::dispatch($this->projectId, auth()->id(), $this->jobId);

            // Start polling for progress
            $this->dispatchBrowserEvent('start-job-polling', ['jobId' => $this->jobId]);

            $this->setMessage('info', 'Backlog generation started. This may take 1-2 minutes...');

        } catch (\Exception $e) {
            Log::error('Failed to start backlog generation: ' . $e->getMessage());
            $this->setMessage('error', 'Failed to start backlog generation: ' . $e->getMessage());
            $this->isGeneratingBacklog = false;
        }
    }

    public function checkJobProgress()
    {
        if (!$this->jobId) {
            return;
        }

        $progress = Cache::get("backlog_generation_{$this->jobId}");

        if ($progress) {
            $this->generationProgress = $progress['message'];
            $this->generationPercentage = $progress['percentage'];

            if ($progress['status'] === 'success') {
                $this->isGeneratingBacklog = false;
                $this->setMessage('success', $progress['message']);
                $this->dispatchBrowserEvent('stop-job-polling');
                $this->dispatchBrowserEvent('refresh-backlog');
                Cache::forget("backlog_generation_{$this->jobId}");
                $this->jobId = null;
            } elseif ($progress['status'] === 'error') {
                $this->isGeneratingBacklog = false;
                $this->setMessage('error', $progress['message']);
                $this->dispatchBrowserEvent('stop-job-polling');
                Cache::forget("backlog_generation_{$this->jobId}");
                $this->jobId = null;
            }
        }
    }

    public function confirmBacklogGeneration()
    {
        if (empty($this->generatedBacklog) || !isset($this->generatedBacklog['epics'])) {
            $this->setMessage('error', 'No backlog data to create.');
            return;
        }

        try {
            DB::beginTransaction();
            
            $createdCount = 0;
            
            foreach ($this->generatedBacklog['epics'] as $epicData) {
                // Create Epic
                $epic = BacklogItem::create([
                    'project_id' => $this->project->id,
                    'type' => BacklogItem::TYPE_EPIC,
                    'title' => $epicData['title'],
                    'description' => $epicData['description'],
                    'status' => BacklogItem::STATUS_TODO,
                    'priority' => $this->mapPriority($epicData['priority']),
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
                $createdCount++;
                
                if (isset($epicData['features'])) {
                    foreach ($epicData['features'] as $featureData) {
                        // Create Feature
                        $feature = BacklogItem::create([
                            'project_id' => $this->project->id,
                            'parent_id' => $epic->id,
                            'type' => BacklogItem::TYPE_FEATURE,
                            'title' => $featureData['title'],
                            'description' => $featureData['description'],
                            'status' => BacklogItem::STATUS_TODO,
                            'priority' => $this->mapPriority($featureData['priority']),
                            'created_by' => auth()->id(),
                            'updated_by' => auth()->id(),
                        ]);
                        $createdCount++;
                        
                        if (isset($featureData['user_stories'])) {
                            foreach ($featureData['user_stories'] as $storyData) {
                                // Create User Story
                                $story = BacklogItem::create([
                                    'project_id' => $this->project->id,
                                    'parent_id' => $feature->id,
                                    'type' => BacklogItem::TYPE_USER_STORY,
                                    'title' => $storyData['title'],
                                    'description' => $storyData['description'],
                                    'status' => BacklogItem::STATUS_TODO,
                                    'priority' => $this->mapPriority($storyData['priority']),
                                    'estimated_hours' => $storyData['estimated_hours'] ?? null,
                                    'acceptance_criteria' => isset($storyData['acceptance_criteria']) 
                                        ? json_encode($storyData['acceptance_criteria']) 
                                        : null,
                                    'created_by' => auth()->id(),
                                    'updated_by' => auth()->id(),
                                ]);
                                $createdCount++;
                            }
                        }
                    }
                }
            }
            
            DB::commit();
            
            $this->showBacklogPreview = false;
            $this->generatedBacklog = [];
            $this->generationProgress = '';
            
            $this->setMessage('success', "Successfully created {$createdCount} backlog items!");
            
            // Emit event to refresh backlog if on same page
            $this->emit('backlogCreated');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create backlog items: ' . $e->getMessage());
            $this->setMessage('error', 'Failed to create backlog items: ' . $e->getMessage());
        }
    }

    public function cancelBacklogGeneration()
    {
        $this->showBacklogPreview = false;
        $this->generatedBacklog = [];
        $this->generationProgress = '';
    }

    private function collectAllWikiContent(): string
    {
        $allPages = $this->project->wikiPages()->get();
        $content = "PROJECT: " . $this->project->name . "\n\n";
        
        foreach ($allPages as $page) {
            $content .= "PAGE: " . $page->title . "\n";
            $content .= strip_tags($page->content) . "\n\n";
            $content .= "---\n\n";
        }
        
        return $content;
    }

    private function mapPriority(string $priority): string
    {
        $priorityMap = [
            'High' => BacklogItem::PRIORITY_HIGH,
            'Medium' => BacklogItem::PRIORITY_MEDIUM,
            'Low' => BacklogItem::PRIORITY_LOW,
        ];
        
        return $priorityMap[$priority] ?? BacklogItem::PRIORITY_MEDIUM;
    }

    private function syncPendingAttachments(WikiPage $page): void
    {
        if (empty($this->pendingAttachments)) {
            return;
        }

        $updatedContent = $page->content ?? '';

        foreach ($this->pendingAttachments as $attachment) {
            $temporaryPath = $attachment['path'] ?? null;

            if (!$temporaryPath || !Storage::disk('public')->exists($temporaryPath)) {
                continue;
            }

            $media = $page->addMedia(Storage::disk('public')->path($temporaryPath))
                ->usingName($attachment['filename'] ?? basename($temporaryPath))
                ->toMediaCollection('wiki_attachments');

            $temporaryUrl = $attachment['url'] ?? Storage::disk('public')->url($temporaryPath);
            $updatedContent = str_replace($temporaryUrl, $media->getUrl(), $updatedContent);

            Storage::disk('public')->delete($temporaryPath);
        }

        $page->update(['content' => $updatedContent]);
        $this->content = $updatedContent;
        $this->pendingAttachments = [];
    }

    private function normalizeWikiContent(?string $content, WikiPage $page): string
    {
        if (blank($content)) {
            return '';
        }

        return preg_replace_callback('/<figure[^>]*data-trix-attachment="([^"]+)"[^>]*>.*?<\/figure>/s', function ($matches) use ($page) {
            $figure = $matches[0];
            $attachmentData = json_decode(html_entity_decode($matches[1]), true);

            if (!is_array($attachmentData)) {
                return $figure;
            }

            $media = $this->findMatchingMedia($page, $attachmentData);

            if (!$media) {
                return $figure;
            }

            $attachmentData['url'] = $media->getUrl();
            $updatedAttachment = htmlspecialchars(json_encode($attachmentData), ENT_QUOTES, 'UTF-8');
            $updatedFigure = preg_replace('/data-trix-attachment="([^"]+)"/', 'data-trix-attachment="' . $updatedAttachment . '"', $figure, 1);
            $updatedFigure = preg_replace('/<img([^>]*)src="([^"]*)"([^>]*)>/', '<img$1src="' . $media->getUrl() . '"$3>', $updatedFigure, 1);

            return $updatedFigure;
        }, $content) ?? $content;
    }

    private function findMatchingMedia(WikiPage $page, array $attachmentData)
    {
        $mediaItems = $page->getMedia('wiki_attachments')->sortByDesc('id')->values();
        $filename = $attachmentData['filename'] ?? null;
        $name = $attachmentData['name'] ?? null;
        $url = $attachmentData['url'] ?? null;

        return $mediaItems->firstWhere('file_name', $filename)
            ?? $mediaItems->firstWhere('name', $filename)
            ?? $mediaItems->firstWhere('file_name', $name)
            ?? $mediaItems->firstWhere('name', $name)
            ?? $mediaItems->first(function ($item) use ($url) {
                return $url && (
                    str_contains($url, $item->file_name)
                    || str_contains($url, (string) $item->id)
                );
            });
    }

    private function repairPersistedAttachmentUrls(WikiPage $page): void
    {
        $normalizedContent = $this->normalizeWikiContent($page->content, $page);

        if ($normalizedContent !== ($page->content ?? '')) {
            $page->updateQuietly(['content' => $normalizedContent]);
        }
    }

    public function render()
    {
        return view('livewire.project.wiki-view');
    }
}
