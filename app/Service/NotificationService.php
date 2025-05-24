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
     * Send private notification to specific user
     *
     * @param string $title Notification title
     * @param string $content Notification content
     * @param int $userId User ID to send notification to
     * @return bool
     */
    public function sendPrivateNotification(string $title, string $content, int $userId): bool
    {
        return $this->send(
            'leaves-' . $userId,  // channel format: leaves-{userId}
            'notification',       // event name
            [
                'title' => $title,
                'content' => $content
            ]
        );
    }

    /**
     * Send public notification to all users
     *
     * @param string $title Notification title
     * @param string $content Notification content
     * @return bool
     */
    public function sendPublicNotification(string $title, string $content): bool
    {
        return $this->send(
            'notification',
            'notification',
            [
                'title' => $title,
                'content' => $content
            ]
        );
    }

    /**
     * Send notification to specific group
     *
     * @param string $role Role Name
     * @param string $title Notification title
     * @param string $content Notification content
     * @return bool
     */
    public function sendRoleNotification(string $role, string $title, string $content): bool
    {
        return $this->send(
            $role,  // channel format: group-{group}
            'notification',
            [
                'title' => $title,
                'content' => $content
            ]
        );
    }
}
