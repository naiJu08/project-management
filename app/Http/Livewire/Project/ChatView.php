<?php

namespace App\Http\Livewire\Project;

use App\Models\Project;
use App\Models\ProjectChat;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatView extends Component
{
    use WithFileUploads;

    public $projectId;
    public $chatMessages = [];
    public $newMessage = '';
    public $replyingTo = null;
    public $editingMessageId = null;
    public $editingText = '';
    public $searchQuery = '';
    public $attachedFile = null;
    public $attachedImage = null;
    public $showDeleteConfirm = false;
    public $messageToDelete = null;

    protected $rules = [
        'newMessage' => 'nullable|string|max:5000',
        'attachedFile' => 'nullable|file|max:10240',
        'attachedImage' => 'nullable|image|max:5120',
    ];

    protected $messages = [
        'attachedImage.max' => 'The attached image must not be larger than 5 MB.',
        'attachedFile.max' => 'The attached file must not be larger than 10 MB.',
        'attachedImage.image' => 'The attached file must be an image.',
    ];

    public function mount($projectId)
    {
        $this->projectId = $projectId;
        $this->loadMessages();
    }

    public function getProjectProperty()
    {
        return Project::findOrFail($this->projectId);
    }

    public function loadMessages()
    {
        $query = ProjectChat::forProject($this->projectId);

        if ($this->searchQuery) {
            $query->where('message', 'like', '%' . $this->searchQuery . '%');
        }

        $this->chatMessages = $query->get();
    }

    public function sendMessage()
    {
        if (empty($this->newMessage) && !$this->attachedFile && !$this->attachedImage) {
            session()->flash('error', 'Please enter a message or attach a file/image');
            return;
        }

        // Validate attachments with custom messages
        $this->validate([
            'attachedImage' => 'nullable|image|max:5120',
            'attachedFile' => 'nullable|file|max:10240',
        ], [
            'attachedImage.max' => 'The attached image must not be larger than 5 MB.',
            'attachedFile.max' => 'The attached file must not be larger than 10 MB.',
            'attachedImage.image' => 'The attached file must be an image.',
        ]);

        $messageText = $this->newMessage;
        $attachments = [];

        // Handle image upload
        if ($this->attachedImage) {
            $imagePath = $this->attachedImage->store('chat-images', 'public');
            $attachments['image'] = $imagePath;
        }

        // Handle file upload
        if ($this->attachedFile) {
            $filePath = $this->attachedFile->store('chat-files', 'public');
            $attachments['file'] = [
                'path' => $filePath,
                'name' => $this->attachedFile->getClientOriginalName(),
                'size' => $this->attachedFile->getSize(),
            ];
        }

        ProjectChat::create([
            'project_id' => $this->projectId,
            'user_id' => Auth::id(),
            'message' => $messageText,
            'reply_to_id' => $this->replyingTo,
            'attachments' => !empty($attachments) ? json_encode($attachments) : null,
        ]);

        $this->newMessage = '';
        $this->replyingTo = null;
        $this->attachedFile = null;
        $this->attachedImage = null;
        $this->loadMessages();
    }

    public function replyTo($messageId)
    {
        $this->replyingTo = $messageId;
    }

    public function cancelReply()
    {
        $this->replyingTo = null;
    }

    public function editMessage($messageId)
    {
        $message = ProjectChat::find($messageId);
        
        if ($message->user_id !== Auth::id()) {
            session()->flash('error', 'You can only edit your own messages');
            return;
        }

        $this->editingMessageId = $messageId;
        $this->editingText = $message->message;
    }

    public function saveEdit()
    {
        $this->validate(['editingText' => 'required|string|max:5000']);

        $message = ProjectChat::find($this->editingMessageId);
        
        if ($message->user_id !== Auth::id()) {
            session()->flash('error', 'You can only edit your own messages');
            return;
        }

        $message->update([
            'message' => $this->editingText,
            'is_edited' => true,
            'edited_at' => now(),
        ]);

        $this->editingMessageId = null;
        $this->editingText = '';
        $this->loadMessages();
    }

    public function cancelEdit()
    {
        $this->editingMessageId = null;
        $this->editingText = '';
    }

    public function confirmDeleteMessage($messageId)
    {
        $message = ProjectChat::find($messageId);
        
        if ($message->user_id !== Auth::id()) {
            session()->flash('error', 'You can only delete your own messages');
            return;
        }

        $this->messageToDelete = $messageId;
        $this->showDeleteConfirm = true;
    }

    public function deleteMessage()
    {
        $message = ProjectChat::find($this->messageToDelete);
        
        if ($message->user_id !== Auth::id()) {
            session()->flash('error', 'You can only delete your own messages');
            return;
        }

        $message->delete();
        $this->loadMessages();
        session()->flash('success', 'Message deleted successfully!');

        $this->showDeleteConfirm = false;
        $this->messageToDelete = null;
    }

    public function cancelDeleteMessage()
    {
        $this->showDeleteConfirm = false;
        $this->messageToDelete = null;
    }

    public function search()
    {
        $this->loadMessages();
    }

    public function clearSearch()
    {
        $this->searchQuery = '';
        $this->loadMessages();
    }

    public function removeImage()
    {
        $this->attachedImage = null;
    }

    public function removeFile()
    {
        $this->attachedFile = null;
    }

    public function render()
    {
        return view('livewire.project.chat-view', [
            'messages' => $this->chatMessages,
        ]);
    }
}
