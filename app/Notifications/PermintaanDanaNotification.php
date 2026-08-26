<?php

namespace App\Notifications;

use App\Models\PermintaanDana;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PermintaanDanaNotification extends Notification
{
    use Queueable;

    public function __construct(
        private PermintaanDana $permintaanDana,
        private string $title,
        private string $message,
        private string $link,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'link' => $this->link,
            'permintaan_dana_id' => $this->permintaanDana->id,
            'nomor_permintaan' => $this->permintaanDana->nomor_permintaan,
        ];
    }
}
