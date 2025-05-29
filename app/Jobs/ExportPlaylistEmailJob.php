<?php

namespace App\Jobs;

use App\Mail\ExportPlaylistEmail;
use App\Models\Playlist;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class ExportPlaylistEmailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Playlist $playlist,
        private string $targetEmail
    ) {}

    public function handle(): void
    {
        $playlistSongs = [];

        foreach ($this->playlist->PlaylistSongs as $playlistSong) {
            array_push(
                $playlistSongs,
                [
                    'id' => $playlistSong->Song->id,
                    'title' => $playlistSong->Song->title,
                    'performer' => $playlistSong->Song->performer,
                ]
            );
        }

        $playlistData = [
            'playlist' => [
                'id' => $this->playlist->id,
                'name' => $this->playlist->name,
                'songs' => $playlistSongs,
            ]
        ];

        Mail::to($this->targetEmail)
            ->send(new ExportPlaylistEmail($playlistData));
    }
}
