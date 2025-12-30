<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging Configuration
    |--------------------------------------------------------------------------
    |
    | This is the configuration for Firebase Cloud Messaging.
    | You can obtain these credentials from your Firebase Console.
    |
    */
    
    'project_id' => env('FCM_PROJECT_ID'),
    'private_key_id' => env('FCM_PRIVATE_KEY_ID'),
    'private_key' => env('FCM_PRIVATE_KEY'),
    'client_email' => env('FCM_CLIENT_EMAIL'),
    'client_id' => env('FCM_CLIENT_ID'),
    'auth_uri' => env('FCM_AUTH_URI'),
    'token_uri' => env('FCM_TOKEN_URI'),
    'auth_provider_x509_cert_url' => env('FCM_AUTH_PROVIDER_X509_CERT_URL'),
    'client_x509_cert_url' => env('FCM_CLIENT_X509_CERT_URL'),
    
    // Alternative: You can also use a service account JSON file
    'service_account_file' => env('FCM_SERVICE_ACCOUNT_FILE', storage_path('app/firebase-credentials.json')),
];