<?php

namespace App\Actions;

use App\Models\PushSubscription;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class SendPushNotification
{
    public static function send(int $userId, string $title, string $body, array $data = []): void
    {
        $subscriptions = PushSubscription::where('user_id', $userId)->get();
        if ($subscriptions->isEmpty()) return;

        $auth = [
            'VAPID' => [
                'subject'    => config('app.url'),
                'publicKey'  => env('VAPID_PUBLIC_KEY'),
                'privateKey' => env('VAPID_PRIVATE_KEY'),
            ],
        ];

        $webPush = new WebPush($auth);

        $payload = json_encode([
            'title' => $title,
            'body'  => $body,
            'data'  => $data,
            'icon'  => '/build/favicon.ico',
            'badge' => '/build/favicon.ico',
        ]);

        foreach ($subscriptions as $sub) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint'      => $sub->endpoint,
                    'publicKey'     => $sub->public_key,
                    'authToken'     => $sub->auth_token,
                    'encoding'      => $sub->encoding ?? 'aesgcm',
                ]),
                $payload
            );
        }

        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                $endpoint = $report->getEndpoint();
                PushSubscription::where('endpoint', $endpoint)->delete();
                report(new \Exception('Push failed: ' . $report->getReason()));
            }
        }
    }
}
