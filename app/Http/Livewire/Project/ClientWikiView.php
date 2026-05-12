<?php

namespace App\Http\Livewire\Project;

use App\Models\Project;
use App\Models\WikiPage;
use Livewire\Component;

class ClientWikiView extends Component
{
    public $projectId;
    public $pages;
    public $selectedPage;
    public $searchTerm = '';
    public $newComment = '';
    public $replyToCommentId = null;
    public $signoffRemarks = '';

    protected $listeners = ['refreshPages' => 'loadPages'];

    public function mount($projectId)
    {
        $this->projectId = $projectId;

        if (!$this->canAccessProject()) {
            abort(403, 'You do not have access to this project.');
        }

        $this->loadPages();
    }

    public function getProjectProperty()
    {
        return Project::findOrFail($this->projectId);
    }

    public function loadPages()
    {
        $query = WikiPage::query()
            ->where('project_id', $this->projectId)
            ->clientVisible()
            ->where(function ($query) {
                $query->whereNull('parent_id')
                    ->orWhereDoesntHave('parent', function ($parentQuery) {
                        $parentQuery->clientVisible();
                    });
            })
            ->with(['children' => function ($query) {
                $query->clientVisible();
            }, 'creator', 'updater', 'activeSignoffs']);
        
        if ($this->searchTerm) {
            $query->where('title', 'like', '%' . $this->searchTerm . '%');
        }
        
        $this->pages = $query->get();

        if (!$this->selectedPage && $this->pages->isNotEmpty()) {
            $this->selectPage($this->pages->first()->id);
        }
    }

    public function selectPage($pageId)
    {
        $this->selectedPage = WikiPage::with([
            'children' => function ($query) {
                $query->clientVisible();
            },
            'comments.user',
            'comments.replies.user',
            'activeSignoffs.client',
            'signoffs'
        ])->clientVisible()->find($pageId);
        
        if (!$this->selectedPage) {
            session()->flash('error', 'Page not found or not available.');
            return;
        }
        
        if (!$this->canAccessProject()) {
            session()->flash('error', 'You do not have access to this page.');
            $this->selectedPage = null;
            return;
        }
        
        $this->newComment = '';
        $this->replyToCommentId = null;
    }

    public function addComment()
    {
        if (!auth()->user()->can('Comment on wiki')) {
            session()->flash('error', 'You do not have permission to comment.');
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
        session()->flash('success', 'Comment added successfully!');
    }

    public function replyToComment($commentId)
    {
        $this->replyToCommentId = $commentId;
    }

    public function cancelReply()
    {
        $this->replyToCommentId = null;
    }

    public function signOffPage()
    {
        if (!auth()->user()->can('Sign off wiki')) {
            session()->flash('error', 'You do not have permission to sign off.');
            return;
        }

        // Check if already signed off
        $existingSignoff = $this->selectedPage->signoffs()
            ->where('client_id', auth()->id())
            ->where('version_signed', $this->selectedPage->version)
            ->where('is_outdated', false)
            ->first();

        if ($existingSignoff) {
            session()->flash('error', 'You have already signed off this version.');
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
        session()->flash('success', 'Document signed off successfully!');
    }

    public function render()
    {
        return view('livewire.project.client-wiki-view');
    }

    private function canAccessProject(): bool
    {
        $user = auth()->user();

        if ($this->isClientWikiUser()) {
            return $this->project->users()->where('users.id', $user->id)->exists()
                && WikiPage::where('project_id', $this->projectId)
                ->clientVisible()
                ->exists();
        }

        return $this->project->owner_id === $user->id
            || $this->project->users()->where('users.id', $user->id)->exists();
    }

    private function isClientWikiUser(): bool
    {
        $user = auth()->user();

        return $user->roles->contains(fn ($role) => strtolower($role->name) === 'client')
            && $user->can('View client wiki');
    }
}
