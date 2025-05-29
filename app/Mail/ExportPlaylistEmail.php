<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExportPlaylistEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private $playlistData) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('noreply@openmusicapi.com', 'OpenMusic API - No Reply'),
            subject: 'Export Playlist Email',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.export-playlist',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn() => json_encode($this->playlistData),
                'playlist.json'
            )->withMime('application/json')
        ];
    }
}
