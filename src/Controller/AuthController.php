<?php
namespace NetInventory\Controller;

use NetInventory\App;
use NetInventory\Model\User;

/**
 * Authentication Controller
 * 
 * ⚠️ VULNERABLE BY DESIGN - FOR EDUCATIONAL PURPOSES ONLY
 * 
 * Contains intentional SQL Injection vulnerabilities:
 * - Direct concatenation of user input in SQL queries
 * - No prepared statements
 * - No input validation
 * - Weak password hashing
 */

class AuthController
{
    private $app;
    private $db;
    
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->db = $app->getDb();
    }
    
    /**
     * Show login form
     */
    public function showLogin()
    {
        if ($this->app->isAuthenticated()) {
            $this->app->redirect('');
        }
        
        echo $this->app->render('auth/login.php', [
            'error' => $_SESSION['login_error'] ?? null
        ]);
        
        unset($_SESSION['login_error']);
    }
    
    /**
     * Handle login
     * 
     * VULNERABLE: SQL Injection via username and password fields
     * 
     * Example exploits:
     * Username: admin' OR '1'='1
     * Password: anything
     * 
     * Username: admin'--
     * Password: (empty)
     * 
     * Username: ' UNION SELECT 1,2,3,4,'admin',5,6,7,8,9--
     * Password: anything
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->app->redirect('login');
        }
        
        // VULNERABLE: Direct access to POST data without validation
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // VULNERABLE: SQL Injection - Direct string concatenation
        $query = "SELECT * FROM users WHERE username = '{$username}' AND password_hash = '{$password}'";
        
        // Alternative vulnerable query (commented for different test scenarios)
        // $query = "SELECT * FROM users WHERE username = '{$username}' LIMIT 1";
        
        try {
            // VULNERABLE: Executing unsanitized query
            $stmt = $this->db->query($query);
            $user = $stmt->fetch();
            
            if ($user) {
                // VULNERABLE: No proper password verification
                // In reality, should use password_verify()
                
                $this->app->setCurrentUser([
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'full_name' => $user['full_name']
                ]);
                
                // Update last login - ALSO VULNERABLE
                $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = {$user['id']}";
                $this->db->exec($updateQuery);
                
                $this->app->redirect('');
            } else {
                $_SESSION['login_error'] = 'Invalid credentials';
                $this->app->redirect('login');
            }
            
        } catch (\PDOException $e) {
            // VULNERABLE: Information disclosure
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            
            $_SESSION['login_error'] = 'Login failed';
            $this->app->redirect('login');
        }
    }
    
    /**
     * Logout
     */
    public function logout()
    {
        $this->app->logout();
        session_destroy();
        $this->app->redirect('login');
    }
    
    /**
     * Show registration form (if enabled)
     */
    public function showRegister()
    {
        echo $this->app->render('auth/register.php');
    }
    
    /**
     * Handle registration
     * VULNERABLE: SQL Injection in INSERT query
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->app->redirect('register');
        }
        
        // VULNERABLE: No input validation
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $fullName = $_POST['full_name'] ?? '';
        
        // VULNERABLE: Weak password hashing (MD5)
        $passwordHash = md5($password);
        
        // VULNERABLE: SQL Injection in INSERT
        $query = "INSERT INTO users (username, email, password_hash, role, full_name) 
                  VALUES ('{$username}', '{$email}', '{$passwordHash}', 'viewer', '{$fullName}')";
        
        try {
            $this->db->exec($query);
            $_SESSION['login_success'] = 'Registration successful! Please login.';
            $this->app->redirect('login');
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            
            $_SESSION['register_error'] = 'Registration failed';
            $this->app->redirect('register');
        }
    }
    
    /**
     * Password reset (vulnerable)
     * VULNERABLE: SQL Injection via email parameter
     */
    public function resetPassword()
    {
        $email = $_GET['email'] ?? '';
        
        // VULNERABLE: SQL Injection
        $query = "SELECT * FROM users WHERE email = '{$email}'";
        
        try {
            $stmt = $this->db->query($query);
            $user = $stmt->fetch();
            
            if ($user) {
                // In real app, would send reset email
                echo "Password reset link sent to: " . htmlspecialchars($email);
            } else {
                echo "Email not found";
            }
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            echo "Error processing request";
        }
    }
}
