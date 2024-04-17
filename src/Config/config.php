<?php
/**
 * @see https://github.com/Edujugon/PushNotification
 */

return [
    'gcm' => [
        'priority' => 'normal',
        'dry_run' => false,
        'apiKey' => 'My_ApiKey',
        // Optional: Default Guzzle request options for each GCM request
        // See https://docs.guzzlephp.org/en/stable/request-options.html
        'guzzle' => [],
    ],
    'fcm' => [
        'priority' => 'normal',
        'dry_run' => false,
        'apiKey' => 'My_ApiKey',
        // Optional: Default Guzzle request options for each FCM request
        // See https://docs.guzzlephp.org/en/stable/request-options.html
        'guzzle' => [],
    ],
    'fcm_http_v1' => [
        'priority' => 'normal',
        // 'dry_run' => false, Not used
        'json_credentials_path' => env('FCM_HTTP_V1_CREDENTIALS_PATH', null),
        'credentials' => [
            'type' => env('FCM_HTTP_V1_CREDENTIALS_TYPE', null),
            'project_id' => env('FCM_HTTP_V1_CREDENTIALS_PROJECT_ID', null),
            'private_key_id' => env('FCM_HTTP_V1_CREDENTIALS_PRIVATE_KEY_ID', null),
            'private_key' => env('FCM_HTTP_V1_CREDENTIALS_PRIVATE_KEY', null),
            'client_email' => env('FCM_HTTP_V1_CREDENTIALS_CLIENT_EMAIL', null),
            'client_id' => env('FCM_HTTP_V1_CREDENTIALS_CLIENT_ID', null),
            'auth_uri' => env('FCM_HTTP_V1_CREDENTIALS_AUTH_URI', null),
            'token_uri' => env('FCM_HTTP_V1_CREDENTIALS_TOKEN_URI', null),
            'auth_provider_x509_cert_url' => env('FCM_HTTP_V1_CREDENTIALS_AUTH_PROVIDER_X509_CERT_URL', null),
            'client_x509_cert_url' => env('FCM_HTTP_V1_CREDENTIALS_CLIENT_X509_CERT_URL', null),
            'universe_domain' => env('FCM_HTTP_V1_CREDENTIALS_UNIVERSE_DOMAIN', null),
        ],
        // Optional: Default Guzzle request options for each FCM request
        // See https://docs.guzzlephp.org/en/stable/request-options.html
        'guzzle' => [],
    ],
    'apn' => [
        'certificate' => __DIR__ . '/iosCertificates/apns-dev-cert.pem',
        'passPhrase' => 'secret', //Optional
        'passFile' => __DIR__ . '/iosCertificates/yourKey.pem', //Optional
        'dry_run' => true,
    ],
];
