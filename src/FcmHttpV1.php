<?php
namespace Edujugon\PushNotification;

use Exception;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use GuzzleHttp\Client;

class FcmHttpV1 extends Fcm
{
    /**
     * The scope required to send a notification
     * @var string $scope
     */
    protected $scope = "https://www.googleapis.com/auth/firebase.messaging";

    /**
     * Client to do the request
     *
     * @var \GuzzleHttp\Client $client
     */
    protected $client;

    /**
     * Client to do the request
     *
     * @var \Google\Auth\Credentials\ServiceAccountCredentials $credentials
     */
    protected $credentials;

    /**
     * Fcm constructor.
     * Override parent constructor.
     */
    public function __construct()
    {
        $this->client = new Client($this->config['guzzle'] ?? []);
        $this->config = $this->initializeConfig('fcm_http_v1');
        $this->credentials = new ServiceAccountCredentials(
            $this->scope,
            $this->config['json_credentials_path'] ?? $this->config['credentials']
        );
        $this->url = "https://fcm.googleapis.com/v1/projects/{$this->credentials->getProjectId()}/messages:send";
    }

    /**
     * @param $message
     * @return array
     */
    protected function buildMessage($message)
    {
        if (!isset($message['message'])) {
            $message['message'] = $message;
        }

        // if no notification nor data keys, then set Data Message as default.
        if (!array_key_exists('data', $message['message']) && !array_key_exists('notification', $message['message'])) {
            $message['message']['data'] = $message;
        }

        $message['message']['android']['priority'] = $this->config['priority'] ?? 'high';

        return $message;
    }

    /**
     * Set the needed headers for the push notification.
     *
     * @return array
     */
    protected function addRequestHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Content-Type' =>'application/json'
        ];
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getAccessToken(): string
    {
        $result = $this->credentials->fetchAuthToken(HttpHandlerFactory::build($this->client));

        return $result['access_token'];
    }

    /**
     * Set the needed fields for the push notification
     *
     * @param string $device_token
     * @param array $message
     * @return array
     */
    protected function addRequestFields($device_token, $message)
    {
        return array_merge_recursive($this->buildMessage($message), ['message' => ['token'  => $device_token]]);
    }

    public function send(array $deviceTokens, array $message)
    {
        $headers = $this->addRequestHeaders();

        $feedback = [];
        foreach ($deviceTokens as $deviceToken) {
            $fields = $this->addRequestFields($deviceToken, $message);

            try {
                $result = $this->client->post(
                    $this->url,
                    [
                        'headers' => $headers,
                        'json' => $fields,
                    ]
                );

                $json = $result->getBody()->getContents();

                $feedback[$deviceToken] = json_decode($json, false, 512, JSON_BIGINT_AS_STRING);
            } catch (Exception $e) {
                $feedback[$deviceToken] = (object) ['success' => false, 'error' => $e->getMessage()];
            }
        }

        $this->setFeedback((object) $feedback);

        return $this->feedback;
    }

    /**
     * Send notification by topic.
     * if isCondition is true, $topic will be treated as an expression
     *
     * @param string $topic
     * @param array $message
     * @param bool $isCondition
     * @return object
     */
    public function sendByTopic($topic, $message, $isCondition = false)
    {
        $message = $this->buildTopicMessage($topic, $message, $isCondition);

        $headers = $this->addRequestHeaders();

        try {
            $result = $this->client->post(
                $this->url,
                [
                    'headers' => $headers,
                    'json' => $message,
                ]
            );

            $json = $result->getBody()->getContents();

            $feedback = json_decode($json, false, 512, JSON_BIGINT_AS_STRING);
        } catch (Exception $e) {
            $feedback = (object) ['success' => false, 'error' => $e->getMessage()];
        }

        $this->setFeedback($feedback);

        return $this->feedback;
    }

    /**
     * Prepare the data to be sent
     * Used to send by topic.
     *
     * @param $topic
     * @param $message
     * @param $isCondition
     * @return array
     */
    protected function buildTopicMessage($topic, $message, $isCondition)
    {
        $condition = $isCondition ? ['condition' => $topic] : ['topic' => $topic];

        return array_merge_recursive($this->buildMessage($message), ['message' => $condition]);
    }

    /**
     * Provide the unregistered tokens of the notification sent.
     *
     * @param array $devices_token
     * @return array $tokenUnRegistered
     */
    public function getUnregisteredDeviceTokens(array $devices_token)
    {
        if (!isset($this->feedback)) {
            return [];
        }

        $unregistered_tokens = $devices_token;

        foreach ($devices_token as $device_token) {
            // If there is any failure sending the notification
            if (isset($this->feedback->{$device_token}->failure)) {
                foreach ($this->feedback->{$device_token}->results as $message) {
                    // Walk the array looking for any error.
                    // If no error, unset it from all token lists that will become the unregistered tokens array.
                    if (!isset($message->error)) {
                        unset($unregistered_tokens[$device_token]);
                    }
                }
            }
        }

        return $unregistered_tokens;
    }
}