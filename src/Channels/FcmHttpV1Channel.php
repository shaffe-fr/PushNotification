<?php

namespace Edujugon\PushNotification\Channels;

use Edujugon\PushNotification\Messages\PushMessage;

class FcmHttpV1Channel extends PushChannel
{
    /**
     * {@inheritdoc}
     */
    protected function pushServiceName()
    {
        return 'fcm_http_v1';
    }

    /**
     * @inheritdoc
     */
    protected function buildData(PushMessage $message)
    {
        $data = [];

        if ($message->title != null || $message->body != null || $message->click_action != null) {
            $data = [
                'notification' => [
                    'title' => $message->title,
                    'body' => $message->body,
                ],
                'android' => [
                    'notification' => [
                        'color' => $message->color,
                        'click_action' => $message->click_action,
                        'title' => $message->title,
                        'body' => $message->body,
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $message->title,
                                'body' => $message->body,
                            ],
                            'category' => $message->click_action,
                        ],
                    ],
                ],
            ];

            // Set custom badge number when isset in PushMessage
            if (!empty($message->badge)) {
                $data['apns']['payload']['aps']['badge'] = $message->badge;
            }

            if (!empty($message->sound)) {
                $data['android']['notification']['sound'] = $message->sound;
                $data['apns']['payload']['aps']['sound'] = $message->sound;
            }

            // Set icon when isset in PushMessage
            if (!empty($message->icon)) {
                $data['android']['notification']['icon'] = $message->icon;
            }
        }

        if (!empty($message->extra)) {
            $data['data'] = $message->extra;
        }

        return ['message' => $data];
    }
}
