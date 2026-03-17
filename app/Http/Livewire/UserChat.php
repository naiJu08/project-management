<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\DirectMessage;
use Illuminate\Support\Facades\Storage;

class UserChat extends Component
{
    use WithFileUploads;

    public $users;
    public $selectedUser;
    public $selectedUserModel;

    public $message = '';
    public $chatMessages = [];
    public $files = [];

    public $search = '';

    public $editingMessageId = null;
    public $editingText = '';

    public function mount()
    {
        $this->users = User::where('id','!=',auth()->id())->get();
    }

    public function updatedSearch()
    {
        $this->users = User::where('id','!=',auth()->id())
            ->where('name','like','%'.$this->search.'%')
            ->get();
    }

    public function selectUser($userId)
    {
        $this->selectedUser = $userId;
        $this->selectedUserModel = User::find($userId);

         // ⭐ mark messages as read
            DirectMessage::where('sender_id', $userId)
                ->where('receiver_id', auth()->id())
                ->whereNull('read_at')
                ->update([
                    'read_at' => now()
                ]);

        $this->loadMessages();
    }

    public function loadMessages()
    {
        if(!$this->selectedUser) return;

        $this->chatMessages = DirectMessage::where(function ($q) {
            $q->where('sender_id', auth()->id())
              ->where('receiver_id', $this->selectedUser);
        })
        ->orWhere(function ($q) {
            $q->where('sender_id', $this->selectedUser)
              ->where('receiver_id', auth()->id());
        })
        ->orderBy('created_at','asc')
        ->get();
    }

    public function sendMessage()
{
    if(empty($this->message) && empty($this->files)){
        return;
    }

    $this->validate([
        'files.*' => 'nullable|file|max:10240'
    ]);

    if(!empty($this->files)){

        $total = count($this->files);

        foreach ($this->files as $index => $file){

            $filePath = $file->store('chat-files','public');

            DirectMessage::create([
                'sender_id' => auth()->id(),
                'receiver_id' => $this->selectedUser,
                'message' => $index == ($total - 1) ? $this->message : '',
                'file' => $filePath
            ]);
        }

    } else {

        DirectMessage::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $this->selectedUser,
            'message' => $this->message
        ]);
    }

    $this->reset(['message','files']);

    $this->loadMessages();

    $this->emit('messageSent');
}
    public function removeFile($index)
{
    unset($this->files[$index]);
    $this->files = array_values($this->files);
}

    // ⭐ DELETE MESSAGE FUNCTION
    public function deleteMessage($id)
    {
        $msg = DirectMessage::find($id);

        if ($msg && $msg->sender_id == auth()->id()) {

            if ($msg->file) {
                Storage::disk('public')->delete($msg->file);
            }

            $msg->delete();
        }

        $this->loadMessages();
    }

   public function render()
{
    if ($this->selectedUser) {

        $this->loadMessages();

        // mark new messages as read automatically
        DirectMessage::where('sender_id', $this->selectedUser)
            ->where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->update([
                'read_at' => now()
            ]);
    }

    return view('livewire.user-chat');
}

    public function editMessage($id)
{
    $msg = DirectMessage::find($id);

    $this->editingMessageId = $msg->id;
    $this->editingText = $msg->message;
}
    public function updateMessage()
{
    $msg = DirectMessage::find($this->editingMessageId);

    $msg->update([
        'message' => $this->editingText
    ]);

    // reset edit mode
    $this->editingMessageId = null;
    $this->editingText = '';

    // ⭐ refresh chat messages instantly
    $this->chatMessages = DirectMessage::where(function ($q) {
        $q->where('sender_id', auth()->id())
          ->where('receiver_id', $this->selectedUser);
    })->orWhere(function ($q) {
        $q->where('sender_id', $this->selectedUser)
          ->where('receiver_id', auth()->id());
    })
    ->orderBy('created_at')
    ->get();
}
}