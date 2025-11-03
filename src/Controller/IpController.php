<?php
namespace NetInventory\Controller;

use NetInventory\App;

/**
 * IP Assignment Controller
 * 
 * ⚠️ VULNERABLE BY DESIGN - FOR EDUCATIONAL PURPOSES ONLY
 * 
 * Manages IP assignments for devices and interfaces
 * Contains SQL Injection vulnerabilities
 */

class IpController
{
    private $app;
    private $db;
    
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->db = $app->getDb();
    }
    
    /**
     * Create new IP assignment
     * VULNERABLE: SQL Injection in INSERT
     */
    public function assign()
    {
        if (!$this->app->hasRole('operator')) {
            die("Access denied");
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->app->redirect('');
        }
        
        // VULNERABLE: No input validation
        $deviceId = $_POST['device_id'] ?? '';
        $interfaceId = $_POST['interface_id'] ?? 'NULL';
        $ip = $_POST['ip'] ?? '';
        $prefix = $_POST['prefix'] ?? 'NULL';
        $assignedFor = $_POST['assigned_for'] ?? '';
        
        // VULNERABLE: SQL Injection
        $query = "INSERT INTO ip_assignments 
                  (device_id, interface_id, ip, prefix, assigned_for)
                  VALUES 
                  ({$deviceId}, {$interfaceId}, INET6_ATON('{$ip}'), {$prefix}, '{$assignedFor}')";
        
        try {
            $this->db->exec($query);
            
            // Log the action (also vulnerable)
            $userId = $this->app->getCurrentUser()['id'];
            $logQuery = "INSERT INTO change_log (device_id, user_id, action, details) 
                        VALUES ({$deviceId}, {$userId}, 'assign-ip', '{\"ip\":\"{$ip}\",\"prefix\":{$prefix}}')";
            $this->db->exec($logQuery);
            
            $_SESSION['success_message'] = 'IP assigned successfully';
            $this->app->redirect("devices/{$deviceId}");
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            
            $_SESSION['error_message'] = 'Error assigning IP';
            $this->app->redirect("devices/{$deviceId}");
        }
    }
    
    /**
     * Delete IP assignment
     * VULNERABLE: SQL Injection and no CSRF protection
     */
    public function delete($id)
    {
        if (!$this->app->hasRole('operator')) {
            die("Access denied");
        }
        
        // Get device_id before deletion (also vulnerable)
        $query = "SELECT device_id FROM ip_assignments WHERE id = {$id}";
        $assignment = $this->db->query($query)->fetch();
        
        if (!$assignment) {
            die("IP assignment not found");
        }
        
        // VULNERABLE: SQL Injection in DELETE
        $deleteQuery = "DELETE FROM ip_assignments WHERE id = {$id}";
        
        try {
            $this->db->exec($deleteQuery);
            
            $_SESSION['success_message'] = 'IP assignment deleted';
            $this->app->redirect("devices/{$assignment['device_id']}");
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage());
            }
            
            $_SESSION['error_message'] = 'Error deleting IP assignment';
            $this->app->redirect("devices/{$assignment['device_id']}");
        }
    }
    
    /**
     * List all IP assignments
     * VULNERABLE: SQL Injection in search
     */
    public function index()
    {
        if (!$this->app->isAuthenticated()) {
            $this->app->redirect('login');
        }
        
        // VULNERABLE: No input validation
        $search = $_GET['search'] ?? '';
        
        $query = "SELECT ia.*, 
                         INET6_NTOA(ia.ip) as ip_address,
                         d.hostname,
                         i.name as interface_name
                  FROM ip_assignments ia
                  LEFT JOIN devices d ON ia.device_id = d.id
                  LEFT JOIN interfaces i ON ia.interface_id = i.id
                  WHERE 1=1";
        
        // VULNERABLE: SQL Injection
        if (!empty($search)) {
            $query .= " AND (INET6_NTOA(ia.ip) LIKE '%{$search}%' 
                        OR d.hostname LIKE '%{$search}%'
                        OR ia.assigned_for LIKE '%{$search}%')";
        }
        
        $query .= " ORDER BY ia.assigned_at DESC";
        
        try {
            $assignments = $this->db->query($query)->fetchAll();
            
            echo $this->app->render('ip/list.php', [
                'assignments' => $assignments,
                'search' => $search
            ]);
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            die("Error loading IP assignments");
        }
    }
    
    /**
     * Check IP availability
     * VULNERABLE: SQL Injection
     */
    public function checkAvailability()
    {
        if (!$this->app->isAuthenticated()) {
            $this->app->json(['error' => 'Unauthorized'], 401);
        }
        
        $ip = $_GET['ip'] ?? '';
        
        // VULNERABLE: SQL Injection
        $query = "SELECT COUNT(*) as count 
                  FROM ip_assignments 
                  WHERE INET6_NTOA(ip) = '{$ip}'";
        
        try {
            $result = $this->db->query($query)->fetch();
            $available = $result['count'] == 0;
            
            $this->app->json([
                'ip' => $ip,
                'available' => $available,
                'message' => $available ? 'IP is available' : 'IP is already assigned'
            ]);
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                $this->app->json(['error' => $e->getMessage()], 500);
            }
            $this->app->json(['error' => 'Check failed'], 500);
        }
    }
}
