<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class NotificationService
{
    private string $websocketUrl;
    private string $key;
    private string $secret;
    private Client $client;

    public function __construct()
    {
        $this->websocketUrl = 'https://websocket.dmpt.my.id/notification';
        $this->key = 'key';
        $this->secret = 'secret';
        $this->client = new Client();
    }

    /**
     * Send notification through WebSocket
     *
     * @param string $channel Channel name
     * @param string $event Event name
     * @param array $data Notification data
     * @return bool
     */
    public function send(string $channel, string $event, array $data): bool
    {
        try {
            $response = $this->client->post($this->websocketUrl, [
                'headers' => [
                    'key' => $this->key,
                    'secret' => $this->secret,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'channel' => $channel,
                    'event' => $event,
                    'data' => $data
                ]
            ]);

            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            // Log error if needed
            return false;
        }
    }

    /**
     * Send news notification
     *
     * @param string $title News title
     * @param string $content News content
     * @return bool
     */
    public function sendPrivateNotification(string $title, string $content, int $id): bool
    {
        return $this->send('notification', 'leaves-' . $id, [
            'title' => $title,
            'content' => $content
        ]);
    }
}
