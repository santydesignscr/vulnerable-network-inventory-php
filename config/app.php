<?php
/**
 * Application Configuration
 */

return [
    'name' => 'Net Inventory System',
    'version' => '1.0.0-vulnerable',
    'env' => $_ENV['APP_ENV'] ?? 'development',
    'debug' => $_ENV['APP_DEBUG'] ?? true,
    'base_url' => $_ENV['BASE_URL'] ?? 'http://localhost',
    
    'session' => [
        'name' => 'NET_INV_SESSION',
        'lifetime' => 7200,
        'path' => '/',
        'secure' => false, // VULNERABLE: Not using secure flag
        'httponly' => false, // VULNERABLE: Allows JavaScript access
    ],
    
    'upload' => [
        'path' => __DIR__ . '/../public/' . ($_ENV['UPLOAD_PATH'] ?? 'uploads/'),
        'max_size' => $_ENV['MAX_FILE_SIZE'] ?? 5242880,
        'allowed_extensions' => ['txt', 'cfg', 'conf', 'log', 'csv']
    ],
    
    'pagination' => [
        'per_page' => 20
    ]
];
