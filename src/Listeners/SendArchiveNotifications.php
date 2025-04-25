<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use N02srt\AutoArchive\Events\ModelArchived;
use N02srt\AutoArchive\Events\ModelRestored;

class SendArchiveNotifications
{
    public function handle($event)
    {
        $data = [
            'event' => $event instanceof ModelArchived ? 'archived' : 'restored',
            'model' => get_class($event->model),
            'id' => $event->model->getKey(),
        ];

        // Slack
        if ($url = config('auto-archive.notifications.slack')) {
            Http::post($url, ['text' => "📦 {$data['event']} {$data['model']} #{$data['id']}"]);
        }

        // Webhook
        if ($hook = config('auto-archive.notifications.webhook')) {
            Http::post($hook, $data);
        }

        // Email
        if ($to = config('auto-archive.notifications.email')) {
            Mail::raw("Model {$data['event']}: {$data['model']} ID {$data['id']}", function ($msg) use ($to) {
                $msg->to($to)->subject('Archive Event Notification');
            });
        }
    }
}
