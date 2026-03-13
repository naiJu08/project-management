<?php

namespace App\Notifications;

use App\Models\WikiPage;
use App\Models\WikiSignoff;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WikiPageSignedOff extends Notification implements ShouldQueue
{
    use Queueable;

    protected $wikiPage;
    protected $signoff;

    public function __construct(WikiPage $wikiPage, WikiSignoff $signoff)
    {
        $this->wikiPage = $wikiPage;
        $this->signoff = $signoff;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Wiki Page Signed Off: ' . $this->wikiPage->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('The wiki page "' . $this->wikiPage->title . '" has been signed off by ' . $this->signoff->client->name . '.')
            ->line('**Version:** ' . $this->signoff->version_signed)
            ->line('**Signed Off At:** ' . $this->signoff->signed_off_at->format('M d, Y H:i'))
            ->when($this->signoff->remarks, function ($mail) {
                return $mail->line('**Remarks:** ' . $this->signoff->remarks);
            })
            ->action('View Wiki Page', url('/projects/' . $this->wikiPage->project_id . '?activeTab=wiki'))
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'wiki_page_id' => $this->wikiPage->id,
            'wiki_page_title' => $this->wikiPage->title,
            'signoff_id' => $this->signoff->id,
            'client_name' => $this->signoff->client->name,
            'version_signed' => $this->signoff->version_signed,
            'signed_off_at' => $this->signoff->signed_off_at->toDateTimeString(),
            'project_id' => $this->wikiPage->project_id,
        ];
    }
}
