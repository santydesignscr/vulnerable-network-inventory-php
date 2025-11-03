<?php
namespace NetInventory\Controller;

use NetInventory\App;

/**
 * Configuration Controller
 * 
 * ⚠️ VULNERABLE BY DESIGN - FOR EDUCATIONAL PURPOSES ONLY
 * 
 * Handles device configuration uploads and viewing
 * Contains SQL Injection vulnerabilities
 */

class ConfigController
{
    private $app;
    private $db;
    
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->db = $app->getDb();
    }
    
    /**
     * View configuration
     * VULNERABLE: SQL Injection via config ID
     */
    public function view($id)
    {
        if (!$this->app->isAuthenticated()) {
            $this->app->redirect('login');
        }
        
        // VULNERABLE: No input validation
        $query = "SELECT c.*, 
                         d.hostname,
                         u.username as uploaded_by_name
                  FROM configs c
                  LEFT JOIN devices d ON c.device_id = d.id
                  LEFT JOIN users u ON c.uploaded_by = u.id
                  WHERE c.id = {$id}";
        
        try {
            $config = $this->db->query($query)->fetch();
            
            if (!$config) {
                die("Configuration not found");
            }
            
            echo $this->app->render('configs/view.php', [
                'config' => $config
            ]);
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            die("Error loading configuration");
        }
    }
    
    /**
     * Upload configuration
     * VULNERABLE: SQL Injection and file upload vulnerabilities
     */
    public function upload($deviceId)
    {
        if (!$this->app->hasRole('operator')) {
            die("Access denied");
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->app->redirect("devices/{$deviceId}");
        }
        
        $userId = $this->app->getCurrentUser()['id'];
        $notes = $_POST['notes'] ?? '';
        
        // Handle file upload (VULNERABLE: No file type validation)
        if (isset($_FILES['config_file']) && $_FILES['config_file']['error'] === UPLOAD_ERR_OK) {
            $filename = $_FILES['config_file']['name'];
            $tmpPath = $_FILES['config_file']['tmp_name'];
            
            // Read file content
            $content = file_get_contents($tmpPath);
            
            // Escape content minimally (still vulnerable to second-order injection)
            $content = addslashes($content);
            
            // VULNERABLE: SQL Injection in INSERT
            $query = "INSERT INTO configs (device_id, filename, uploaded_by, content, notes)
                     VALUES ({$deviceId}, '{$filename}', {$userId}, '{$content}', '{$notes}')";
            
            try {
                $this->db->exec($query);
                
                // Log action (also vulnerable)
                $logQuery = "INSERT INTO change_log (device_id, user_id, action, details)
                            VALUES ({$deviceId}, {$userId}, 'upload-config', '{\"filename\":\"{$filename}\"}')";
                $this->db->exec($logQuery);
                
                $this->app->redirect("devices/{$deviceId}");
                
            } catch (\PDOException $e) {
                if ($this->app->getConfig('debug')) {
                    die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
                }
                die("Error uploading configuration");
            }
        }
        
        // Handle text input
        if (!empty($_POST['config_content'])) {
            $content = addslashes($_POST['config_content']);
            $filename = $_POST['filename'] ?? 'manual-entry.txt';
            
            // VULNERABLE: SQL Injection
            $query = "INSERT INTO configs (device_id, filename, uploaded_by, content, notes)
                     VALUES ({$deviceId}, '{$filename}', {$userId}, '{$content}', '{$notes}')";
            
            try {
                $this->db->exec($query);
                $this->app->redirect("devices/{$deviceId}");
                
            } catch (\PDOException $e) {
                if ($this->app->getConfig('debug')) {
                    die("SQL Error: " . $e->getMessage());
                }
                die("Error saving configuration");
            }
        }
        
        $this->app->redirect("devices/{$deviceId}");
    }
    
    /**
     * Download configuration
     * VULNERABLE: SQL Injection via config ID
     */
    public function download($id)
    {
        if (!$this->app->isAuthenticated()) {
            die("Access denied");
        }
        
        // VULNERABLE: No input validation
        $query = "SELECT * FROM configs WHERE id = {$id}";
        
        try {
            $config = $this->db->query($query)->fetch();
            
            if (!$config) {
                die("Configuration not found");
            }
            
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $config['filename'] . '"');
            echo $config['content'];
            exit;
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage());
            }
            die("Error downloading configuration");
        }
    }
    
    /**
     * Delete configuration
     * VULNERABLE: SQL Injection and no CSRF protection
     */
    public function delete($id)
    {
        if (!$this->app->hasRole('operator')) {
            die("Access denied");
        }
        
        // Get device_id before deletion (also vulnerable)
        $query = "SELECT device_id FROM configs WHERE id = {$id}";
        $config = $this->db->query($query)->fetch();
        
        if (!$config) {
            die("Configuration not found");
        }
        
        // VULNERABLE: SQL Injection in DELETE
        $deleteQuery = "DELETE FROM configs WHERE id = {$id}";
        
        try {
            $this->db->exec($deleteQuery);
            $this->app->redirect("devices/{$config['device_id']}");
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage());
            }
            die("Error deleting configuration");
        }
    }
    
    /**
     * Show edit configuration form
     * VULNERABLE: SQL Injection via config ID
     */
    public function edit($id)
    {
        if (!$this->app->hasRole('operator')) {
            die("Access denied");
        }
        
        // VULNERABLE: No input validation
        $query = "SELECT c.*, 
                         d.hostname,
                         d.id as device_id,
                         u.username as uploaded_by_name
                  FROM configs c
                  LEFT JOIN devices d ON c.device_id = d.id
                  LEFT JOIN users u ON c.uploaded_by = u.id
                  WHERE c.id = {$id}";
        
        try {
            $config = $this->db->query($query)->fetch();
            
            if (!$config) {
                die("Configuration not found");
            }
            
            echo $this->app->render('configs/edit.php', [
                'config' => $config
            ]);
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            die("Error loading configuration");
        }
    }
    
    /**
     * Update configuration
     * VULNERABLE: SQL Injection in UPDATE statement
     */
    public function update($id)
    {
        if (!$this->app->hasRole('operator')) {
            die("Access denied");
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->app->redirect("configs/{$id}/edit");
        }
        
        // Get device_id first (also vulnerable)
        $deviceQuery = "SELECT device_id FROM configs WHERE id = {$id}";
        $result = $this->db->query($deviceQuery)->fetch();
        
        if (!$result) {
            die("Configuration not found");
        }
        
        $deviceId = $result['device_id'];
        
        // VULNERABLE: No input validation
        $filename = $_POST['filename'] ?? '';
        $content = addslashes($_POST['content'] ?? '');
        $notes = $_POST['notes'] ?? '';
        
        // VULNERABLE: SQL Injection in UPDATE
        $query = "UPDATE configs SET 
                  filename = '{$filename}',
                  content = '{$content}',
                  notes = '{$notes}'
                  WHERE id = {$id}";
        
        try {
            $this->db->exec($query);
            
            // Log the action (also vulnerable)
            $userId = $this->app->getCurrentUser()['id'];
            $logQuery = "INSERT INTO change_log (device_id, user_id, action, details) 
                        VALUES ({$deviceId}, {$userId}, 'update-config', 
                        '{\"config_id\":{$id},\"filename\":\"{$filename}\"}')";
            $this->db->exec($logQuery);
            
            $this->app->redirect("configs/{$id}");
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            die("Error updating configuration");
        }
    }
    
    /**
     * Compare two configurations
     * VULNERABLE: SQL Injection via both config IDs
     */
    public function compare()
    {
        if (!$this->app->isAuthenticated()) {
            $this->app->redirect('login');
        }
        
        $id1 = $_GET['id1'] ?? 0;
        $id2 = $_GET['id2'] ?? 0;
        
        // VULNERABLE: SQL Injection
        $query1 = "SELECT * FROM configs WHERE id = {$id1}";
        $query2 = "SELECT * FROM configs WHERE id = {$id2}";
        
        try {
            $config1 = $this->db->query($query1)->fetch();
            $config2 = $this->db->query($query2)->fetch();
            
            if (!$config1 || !$config2) {
                die("One or both configurations not found");
            }
            
            echo $this->app->render('configs/compare.php', [
                'config1' => $config1,
                'config2' => $config2
            ]);
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage());
            }
            die("Error loading configurations");
        }
    }
}
