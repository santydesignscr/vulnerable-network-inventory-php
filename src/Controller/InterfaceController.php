<?php
namespace NetInventory\Controller;

use NetInventory\App;

/**
 * Interface Controller
 * 
 * ⚠️ VULNERABLE BY DESIGN - FOR EDUCATIONAL PURPOSES ONLY
 * 
 * Contains intentional SQL Injection vulnerabilities:
 * - Direct concatenation of user input in SQL queries
 * - No prepared statements
 * - No input validation
 */

class InterfaceController
{
    private $app;
    private $db;
    
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->db = $app->getDb();
    }
    
    /**
     * Show create interface form
     * VULNERABLE: SQL Injection via device_id parameter
     */
    public function create($deviceId)
    {
        if (!$this->app->hasRole('operator')) {
            die("Access denied");
        }
        
        // VULNERABLE: No input validation on device_id
        $query = "SELECT d.*, INET6_NTOA(d.management_ip) as ip_address 
                  FROM devices d WHERE d.id = {$deviceId}";
        
        try {
            $device = $this->db->query($query)->fetch();
            
            if (!$device) {
                die("Device not found");
            }
            
            echo $this->app->render('interfaces/edit.php', [
                'device' => $device,
                'interface' => null
            ]);
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            die("Error loading device");
        }
    }
    
    /**
     * Store new interface
     * VULNERABLE: SQL Injection in INSERT statement
     */
    public function store($deviceId)
    {
        if (!$this->app->hasRole('operator')) {
            die("Access denied");
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->app->redirect("interfaces/create/{$deviceId}");
        }
        
        // VULNERABLE: No input validation
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $speed = $_POST['speed'] ?? '';
        $macAddress = $_POST['mac_address'] ?? '';
        $adminStatus = $_POST['admin_status'] ?? 'unknown';
        $operStatus = $_POST['oper_status'] ?? 'unknown';
        $ipAddress = $_POST['ip_address'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // Convert MAC address to binary if provided
        $macBinary = 'NULL';
        if (!empty($macAddress)) {
            // VULNERABLE: Direct use of user input
            $macClean = str_replace([':', '-', '.'], '', $macAddress);
            $macBinary = "UNHEX('{$macClean}')";
        }
        
        // Convert IP address using INET6_ATON if provided
        $ipBinary = 'NULL';
        if (!empty($ipAddress)) {
            $ipBinary = "INET6_ATON('{$ipAddress}')";
        }
        
        // VULNERABLE: SQL Injection in INSERT
        $query = "INSERT INTO interfaces 
                  (device_id, name, description, speed, mac_address, admin_status, 
                   oper_status, ip_address, notes)
                  VALUES 
                  ({$deviceId}, '{$name}', '{$description}', '{$speed}', {$macBinary}, 
                   '{$adminStatus}', '{$operStatus}', {$ipBinary}, '{$notes}')";
        
        try {
            $this->db->exec($query);
            $interfaceId = $this->db->lastInsertId();
            
            // Log the action (also vulnerable)
            $userId = $this->app->getCurrentUser()['id'];
            $logQuery = "INSERT INTO change_log (device_id, user_id, action, details) 
                        VALUES ({$deviceId}, {$userId}, 'add-interface', 
                        '{\"interface\":\"{$name}\"}')";
            $this->db->exec($logQuery);
            
            $this->app->redirect("devices/{$deviceId}");
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            die("Error creating interface");
        }
    }
    
    /**
     * Show edit interface form
     * VULNERABLE: SQL Injection via interface ID
     */
    public function edit($id)
    {
        if (!$this->app->hasRole('operator')) {
            die("Access denied");
        }
        
        // VULNERABLE: No input validation
        $query = "SELECT i.*, 
                         INET6_NTOA(i.ip_address) as ip,
                         HEX(i.mac_address) as mac_hex,
                         d.id as device_id,
                         d.hostname
                  FROM interfaces i
                  JOIN devices d ON i.device_id = d.id
                  WHERE i.id = {$id}";
        
        try {
            $interface = $this->db->query($query)->fetch();
            
            if (!$interface) {
                die("Interface not found");
            }
            
            // Format MAC address for display
            if ($interface['mac_hex']) {
                $mac = $interface['mac_hex'];
                $interface['mac_formatted'] = implode(':', str_split($mac, 2));
            }
            
            // Get device info
            $deviceQuery = "SELECT d.*, INET6_NTOA(d.management_ip) as ip_address 
                           FROM devices d WHERE d.id = {$interface['device_id']}";
            $device = $this->db->query($deviceQuery)->fetch();
            
            echo $this->app->render('interfaces/edit.php', [
                'device' => $device,
                'interface' => $interface
            ]);
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            die("Error loading interface");
        }
    }
    
    /**
     * Update interface
     * VULNERABLE: SQL Injection in UPDATE statement
     */
    public function update($id)
    {
        if (!$this->app->hasRole('operator')) {
            die("Access denied");
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->app->redirect("interfaces/{$id}/edit");
        }
        
        // Get device_id first (also vulnerable)
        $deviceQuery = "SELECT device_id FROM interfaces WHERE id = {$id}";
        $result = $this->db->query($deviceQuery)->fetch();
        $deviceId = $result['device_id'];
        
        // VULNERABLE: No input validation
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $speed = $_POST['speed'] ?? '';
        $macAddress = $_POST['mac_address'] ?? '';
        $adminStatus = $_POST['admin_status'] ?? 'unknown';
        $operStatus = $_POST['oper_status'] ?? 'unknown';
        $ipAddress = $_POST['ip_address'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // Convert MAC address to binary if provided
        $macBinary = 'NULL';
        if (!empty($macAddress)) {
            $macClean = str_replace([':', '-', '.'], '', $macAddress);
            $macBinary = "UNHEX('{$macClean}')";
        }
        
        // Convert IP address using INET6_ATON if provided
        $ipBinary = 'NULL';
        if (!empty($ipAddress)) {
            $ipBinary = "INET6_ATON('{$ipAddress}')";
        }
        
        // VULNERABLE: SQL Injection in UPDATE
        $query = "UPDATE interfaces SET 
                  name = '{$name}',
                  description = '{$description}',
                  speed = '{$speed}',
                  mac_address = {$macBinary},
                  admin_status = '{$adminStatus}',
                  oper_status = '{$operStatus}',
                  ip_address = {$ipBinary},
                  notes = '{$notes}'
                  WHERE id = {$id}";
        
        try {
            $this->db->exec($query);
            
            // Log the action (also vulnerable)
            $userId = $this->app->getCurrentUser()['id'];
            $logQuery = "INSERT INTO change_log (device_id, user_id, action, details) 
                        VALUES ({$deviceId}, {$userId}, 'update-interface', 
                        '{\"interface\":\"{$name}\"}')";
            $this->db->exec($logQuery);
            
            $this->app->redirect("devices/{$deviceId}");
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            die("Error updating interface");
        }
    }
    
    /**
     * Delete interface
     * VULNERABLE: SQL Injection via ID parameter
     */
    public function delete($id)
    {
        if (!$this->app->hasRole('operator')) {
            die("Access denied");
        }
        
        // Get device_id first (also vulnerable)
        $deviceQuery = "SELECT device_id FROM interfaces WHERE id = {$id}";
        $result = $this->db->query($deviceQuery)->fetch();
        
        if (!$result) {
            die("Interface not found");
        }
        
        $deviceId = $result['device_id'];
        
        // VULNERABLE: No CSRF token validation
        // VULNERABLE: SQL Injection
        $query = "DELETE FROM interfaces WHERE id = {$id}";
        
        try {
            $this->db->exec($query);
            
            // Log the action (also vulnerable)
            $userId = $this->app->getCurrentUser()['id'];
            $logQuery = "INSERT INTO change_log (device_id, user_id, action, details) 
                        VALUES ({$deviceId}, {$userId}, 'delete-interface', 
                        '{\"interface_id\":{$id}}')";
            $this->db->exec($logQuery);
            
            $this->app->redirect("devices/{$deviceId}");
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            die("Error deleting interface");
        }
    }
}
