<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\User;
use App\Models\DirectMessage;

class UserChat extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat';
    protected static ?string $navigationLabel = 'User Chat';
    protected static ?string $slug = 'user-chat';
    protected static string $view = 'filament.pages.user-chat';
    protected static ?int $navigationSort = 5;

    public $users;
    public $selectedUser;
    public $messages = [];
    public $message;
    public $selectedUserModel;

    public function mount()
    {
        $this->users = User::where('id','!=',auth()->id())->get();
    }

    public function selectUser($userId)
    {
        $this->selectedUser = $userId;

        $this->selectedUserModel = User::find($userId);

        $this->loadMessages();
    }

    public function loadMessages()
    {
        if(!$this->selectedUser){
            return;
        }

        $this->messages = DirectMessage::where(function ($q) {
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

     public function getChatMessagesProperty()
{
    if(!$this->selectedUser){
        return collect();
    }

    return DirectMessage::where(function ($q) {
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
    public function refreshMessages()
    {
        $this->loadMessages();
    }

   public function sendMessage()
{
    $this->message = trim($this->message);

    if(!$this->message || !$this->selectedUser){
        return;
    }

    DirectMessage::create([
        'sender_id' => auth()->id(),
        'receiver_id' => $this->selectedUser,
        'message' => $this->message,
    ]);

    $this->message = '';

    $this->loadMessages();
}
}