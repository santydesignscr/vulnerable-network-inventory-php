<?php
/**
 * Database Configuration
 * 
 * ⚠️ VULNERABLE BY DESIGN - FOR EDUCATIONAL PURPOSES ONLY
 * This configuration uses insecure practices for SQL injection testing
 */

return [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'database' => $_ENV['DB_NAME'] ?? 'net_inventory',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
    'charset' => 'utf8mb4',
    'options' => [
        // VULNERABLE: Emulation mode allows multiple queries (stacked queries)
        PDO::ATTR_EMULATE_PREPARES => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
];
