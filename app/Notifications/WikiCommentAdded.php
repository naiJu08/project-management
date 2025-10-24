<?php

namespace App\Notifications;

use App\Models\WikiPage;
use App\Models\WikiComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WikiCommentAdded extends Notification implements ShouldQueue
{
    use Queueable;

    protected $wikiPage;
    protected $comment;

    public function __construct(WikiPage $wikiPage, WikiComment $comment)
    {
        $this->wikiPage = $wikiPage;
        $this->comment = $comment;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $commenterRole = $this->comment->isClientComment() ? 'Client' : 'Team Member';
        
        return (new MailMessage)
            ->subject('New Comment on Wiki Page: ' . $this->wikiPage->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A ' . $commenterRole . ' has commented on the wiki page "' . $this->wikiPage->title . '".')
            ->line('**Comment:** ' . \Illuminate\Support\Str::limit($this->comment->content, 200))
            ->action('View Wiki Page', url('/projects/' . $this->wikiPage->project_id . '?activeTab=wiki'))
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'wiki_page_id' => $this->wikiPage->id,
            'wiki_page_title' => $this->wikiPage->title,
            'comment_id' => $this->comment->id,
            'commenter_name' => $this->comment->user->name,
            'commenter_role' => $this->comment->isClientComment() ? 'Client' : 'Team Member',
            'project_id' => $this->wikiPage->project_id,
        ];
    }
}
