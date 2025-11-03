<?php
namespace NetInventory;

/**
 * Application Bootstrap Class
 * 
 * ⚠️ VULNERABLE BY DESIGN - FOR EDUCATIONAL PURPOSES ONLY
 * 
 * This class contains intentional security vulnerabilities:
 * - No input sanitization
 * - Insecure database connection handling
 * - No CSRF protection
 * - Weak session management
 */

class App
{
    private static $instance = null;
    private $db;
    private $config;
    
    private function __construct()
    {
        $this->loadEnv();
        $this->config = require __DIR__ . '/../config/app.php';
        $this->initSession();
        $this->initDatabase();
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Load environment variables from .env file
     * VULNERABLE: No validation of environment values
     */
    private function loadEnv()
    {
        $envFile = __DIR__ . '/../.env';
        if (!file_exists($envFile)) {
            $envFile = __DIR__ . '/../.env.example';
        }
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }
    
    /**
     * Initialize session
     * VULNERABLE: No secure session configuration
     * - No httponly flag
     * - No secure flag for HTTPS
     * - Weak session regeneration
     */
    private function initSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_lifetime' => $this->config['session']['lifetime'],
                'cookie_path' => $this->config['session']['path'],
                'cookie_secure' => false, // VULNERABLE: Should be true for HTTPS
                'cookie_httponly' => false, // VULNERABLE: Should be true
                'use_strict_mode' => false, // VULNERABLE: Should be true
            ]);
        }
    }
    
    /**
     * Initialize database connection
     * VULNERABLE: Uses emulated prepares allowing stacked queries
     */
    private function initDatabase()
    {
        $dbConfig = require __DIR__ . '/../config/database.php';
        
        try {
            $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
            
            // VULNERABLE: PDO::ATTR_EMULATE_PREPARES = true allows multiple queries
            $this->db = new \PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
            
        } catch (\PDOException $e) {
            if ($this->config['debug']) {
                die("Database Connection Error: " . $e->getMessage()); // VULNERABLE: Info disclosure
            }
            die("Database connection failed");
        }
    }
    
    /**
     * Get database connection
     * @return \PDO
     */
    public function getDb()
    {
        return $this->db;
    }
    
    /**
     * Get configuration
     */
    public function getConfig($key = null)
    {
        if ($key === null) {
            return $this->config;
        }
        
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    /**
     * Check if user is authenticated
     * VULNERABLE: Simple session check without token validation
     */
    public function isAuthenticated()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Get current user
     * VULNERABLE: Returns user data from session without re-validation
     */
    public function getCurrentUser()
    {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'role' => $_SESSION['role'] ?? 'viewer',
            'full_name' => $_SESSION['full_name'] ?? null,
        ];
    }
    
    /**
     * Set current user in session
     * VULNERABLE: No session regeneration, no protection against session fixation
     */
    public function setCurrentUser($user)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Check if user has role
     * VULNERABLE: Simple role check that can be manipulated via session
     */
    public function hasRole($role)
    {
        $user = $this->getCurrentUser();
        if (!$user) return false;
        
        $roles = ['viewer' => 1, 'operator' => 2, 'admin' => 3];
        $userLevel = $roles[$user['role']] ?? 0;
        $requiredLevel = $roles[$role] ?? 999;
        
        return $userLevel >= $requiredLevel;
    }
    
    /**
     * Logout user
     * VULNERABLE: Incomplete session cleanup
     */
    public function logout()
    {
        // VULNERABLE: Should destroy session completely
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['role']);
        unset($_SESSION['full_name']);
    }
    
    /**
     * Redirect helper
     */
    public function redirect($path)
    {
        $baseUrl = rtrim($this->config['base_url'], '/');
        header("Location: {$baseUrl}/{$path}");
        exit;
    }
    
    /**
     * Get base URL
     */
    public function getBaseUrl()
    {
        return $this->config['base_url'];
    }
    
    /**
     * Simple view renderer
     * VULNERABLE: No output escaping, XSS possible
     */
    public function render($template, $data = [])
    {
        extract($data); // VULNERABLE: Variable extraction without validation
        
        $templateFile = __DIR__ . '/../templates/' . $template;
        
        if (!file_exists($templateFile)) {
            die("Template not found: {$template}");
        }
        
        ob_start();
        include $templateFile;
        return ob_get_clean();
    }
    
    /**
     * JSON response helper
     * VULNERABLE: No proper Content-Type header security
     */
    public function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
