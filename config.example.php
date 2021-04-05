<?php
// Copy this file 'config.example.php' to 'config.php'
$config = [
    'base_url' => 'https://vaccine.yourdomain.com',
    // IP that run.php will be called
    'self_ip' => '1.2.3.4',
    // MySQL connection details
    'mysql' => [
        'host' => 'localhost',
        'port' => '3306',
        'user' => 'vaccine-user',
        'password' => '',
        'db_name' => 'vaccine'
    ],
    //
    'email' => [
        'host' => 'mail.yourdomain.com',
        'port' => 587,
        'username' => 'vaccine@yourdomain.com',
        'password' => '',
        'from_name' => 'Vaccine Notifier',
        'from_address' => 'vaccine@yourdomain.com',
        'reply_to' => [
            'name' => 'Your Name',
            'email' => 'youremail@yourdomain.com'
        ]
    ],
    'google_maps' => [
        'api_key' => ''
    ],
    'web_push' => [
        'subject' => 'mailto:youremail@yourdomain.com',
        // VAPID public & private key
        'public_key' => '',
        'private_key' => ''
    ],
    // Starts with G-
    'google_analytics_id' => ''
];