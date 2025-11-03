<?php
namespace NetInventory\Controller;

use NetInventory\App;

/**
 * Device Controller
 * 
 * ⚠️ VULNERABLE BY DESIGN - FOR EDUCATIONAL PURPOSES ONLY
 * 
 * Contains multiple SQL Injection vulnerabilities:
 * - Search functionality
 * - Filtering and sorting
 * - CRUD operations
 * - Export functionality
 */

class DeviceController
{
    private $app;
    private $db;
    
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->db = $app->getDb();
    }
    
    /**
     * List all devices (paginated)
     * VULNERABLE: SQL Injection in search, sort, and filter parameters
     */
    public function index()
    {
        if (!$this->app->isAuthenticated()) {
            $this->app->redirect('login');
        }
        
        // VULNERABLE: No input sanitization
        $search = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? '';
        $location = $_GET['location'] ?? '';
        $sort = $_GET['sort'] ?? 'hostname';
        $order = $_GET['order'] ?? 'ASC';
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // VULNERABLE: SQL Injection in WHERE and ORDER BY clauses
        $query = "SELECT d.*, 
                         INET6_NTOA(d.management_ip) as ip_address,
                         dt.label as device_type,
                         dm.model_name,
                         v.name as vendor_name,
                         l.name as location_name
                  FROM devices d
                  LEFT JOIN device_types dt ON d.device_type_id = dt.id
                  LEFT JOIN device_models dm ON d.model_id = dm.id
                  LEFT JOIN vendors v ON dm.vendor_id = v.id
                  LEFT JOIN locations l ON d.location_id = l.id
                  WHERE 1=1";
        
        // VULNERABLE: Direct string concatenation
        if (!empty($search)) {
            $query .= " AND (d.hostname LIKE '%{$search}%' 
                        OR d.owner LIKE '%{$search}%'
                        OR INET6_NTOA(d.management_ip) LIKE '%{$search}%')";
        }
        
        if (!empty($type)) {
            $query .= " AND d.device_type_id = {$type}";
        }
        
        if (!empty($location)) {
            $query .= " AND d.location_id = {$location}";
        }
        
        // VULNERABLE: Unvalidated ORDER BY
        $query .= " ORDER BY {$sort} {$order}";
        $query .= " LIMIT {$perPage} OFFSET {$offset}";
        
        try {
            $stmt = $this->db->query($query);
            $devices = $stmt->fetchAll();
            
            // Get total count (also vulnerable)
            $countQuery = "SELECT COUNT(*) as total FROM devices d WHERE 1=1";
            if (!empty($search)) {
                $countQuery .= " AND (d.hostname LIKE '%{$search}%' OR d.owner LIKE '%{$search}%')";
            }
            $total = $this->db->query($countQuery)->fetch()['total'];
            
            // Get filters data
            $types = $this->db->query("SELECT * FROM device_types")->fetchAll();
            $locations = $this->db->query("SELECT * FROM locations")->fetchAll();
            
            echo $this->app->render('devices/list.php', [
                'devices' => $devices,
                'types' => $types,
                'locations' => $locations,
                'search' => $search,
                'currentPage' => $page,
                'totalPages' => ceil($total / $perPage),
                'total' => $total
            ]);
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            die("Error loading devices");
        }
    }
    
    /**
     * View single device
     * VULNERABLE: SQL Injection via device ID
     */
    public function view($id)
    {
        if (!$this->app->isAuthenticated()) {
            $this->app->redirect('login');
        }
        
        // VULNERABLE: No input validation on ID
        $query = "SELECT d.*, 
                         INET6_NTOA(d.management_ip) as ip_address,
                         dt.label as device_type,
                         dm.model_name,
                         v.name as vendor_name,
                         l.name as location_name
                  FROM devices d
                  LEFT JOIN device_types dt ON d.device_type_id = dt.id
                  LEFT JOIN device_models dm ON d.model_id = dm.id
                  LEFT JOIN vendors v ON dm.vendor_id = v.id
                  LEFT JOIN locations l ON d.location_id = l.id
                  WHERE d.id = {$id}";
        
        try {
            $device = $this->db->query($query)->fetch();
            
            if (!$device) {
                die("Device not found");
            }
            
            // Get interfaces (also vulnerable)
            $interfacesQuery = "SELECT *, INET6_NTOA(ip_address) as ip 
                               FROM interfaces 
                               WHERE device_id = {$id}";
            $interfaces = $this->db->query($interfacesQuery)->fetchAll();
            
            // Get configs (also vulnerable)
            $configsQuery = "SELECT c.*, u.username 
                            FROM configs c 
                            LEFT JOIN users u ON c.uploaded_by = u.id 
                            WHERE c.device_id = {$id} 
                            ORDER BY c.uploaded_at DESC";
            $configs = $this->db->query($configsQuery)->fetchAll();
            
            // Get change log (also vulnerable)
            $logQuery = "SELECT cl.*, u.username 
                        FROM change_log cl 
                        LEFT JOIN users u ON cl.user_id = u.id 
                        WHERE cl.device_id = {$id} 
                        ORDER BY cl.created_at DESC 
                        LIMIT 10";
            $logs = $this->db->query($logQuery)->fetchAll();
            
            // Get IP assignments (also vulnerable)
            $ipQuery = "SELECT ia.*, 
                               INET6_NTOA(ia.ip) as ip_address,
                               i.name as interface_name
                        FROM ip_assignments ia
                        LEFT JOIN interfaces i ON ia.interface_id = i.id
                        WHERE ia.device_id = {$id}
                        ORDER BY ia.assigned_at DESC";
            $ipAssignments = $this->db->query($ipQuery)->fetchAll();
            
            echo $this->app->render('devices/view.php', [
                'device' => $device,
                'interfaces' => $interfaces,
                'configs' => $configs,
                'logs' => $logs,
                'ipAssignments' => $ipAssignments
            ]);
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            die("Error loading device");
        }
    }
    
    /**
     * Create new device form
     */
    public function create()
    {
        if (!$this->app->hasRole('operator')) {
            die("Access denied");
        }
        
        $types = $this->db->query("SELECT * FROM device_types")->fetchAll();
        $locations = $this->db->query("SELECT * FROM locations")->fetchAll();
        $models = $this->db->query("SELECT dm.*, v.name as vendor_name 
                                    FROM device_models dm 
                                    JOIN vendors v ON dm.vendor_id = v.id")->fetchAll();
        
        echo $this->app->render('devices/edit.php', [
            'device' => null,
            'types' => $types,
            'locations' => $locations,
            'models' => $models
        ]);
    }
    
    /**
     * Store new device
     * VULNERABLE: SQL Injection in INSERT statement
     */
    public function store()
    {
        if (!$this->app->hasRole('operator')) {
            die("Access denied");
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->app->redirect('devices/create');
        }
        
        // VULNERABLE: No input validation
        $hostname = $_POST['hostname'] ?? '';
        $ip = $_POST['management_ip'] ?? '';
        $typeId = $_POST['device_type_id'] ?? 0;
        $modelId = $_POST['model_id'] ?? 'NULL';
        $serial = $_POST['serial_number'] ?? '';
        $iosVersion = $_POST['ios_version'] ?? '';
        $locationId = $_POST['location_id'] ?? 'NULL';
        $owner = $_POST['owner'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // VULNERABLE: SQL Injection in INSERT
        $query = "INSERT INTO devices 
                  (hostname, management_ip, device_type_id, model_id, serial_number, 
                   ios_version, location_id, owner, notes)
                  VALUES 
                  ('{$hostname}', INET6_ATON('{$ip}'), {$typeId}, {$modelId}, 
                   '{$serial}', '{$iosVersion}', {$locationId}, '{$owner}', '{$notes}')";
        
        try {
            $this->db->exec($query);
            $deviceId = $this->db->lastInsertId();
            
            // Log the action (also vulnerable)
            $userId = $this->app->getCurrentUser()['id'];
            $logQuery = "INSERT INTO change_log (device_id, user_id, action, details) 
                        VALUES ({$deviceId}, {$userId}, 'create', '{\"hostname\":\"{$hostname}\"}')";
            $this->db->exec($logQuery);
            
            $this->app->redirect("devices/{$deviceId}");
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            die("Error creating device");
        }
    }
    
    /**
     * Edit device form
     * VULNERABLE: SQL Injection via ID parameter
     */
    public function edit($id)
    {
        if (!$this->app->hasRole('operator')) {
            die("Access denied");
        }
        
        // VULNERABLE: No input validation
        $query = "SELECT d.*, INET6_NTOA(d.management_ip) as ip_address 
                  FROM devices d WHERE d.id = {$id}";
        $device = $this->db->query($query)->fetch();
        
        if (!$device) {
            die("Device not found");
        }
        
        $types = $this->db->query("SELECT * FROM device_types")->fetchAll();
        $locations = $this->db->query("SELECT * FROM locations")->fetchAll();
        $models = $this->db->query("SELECT dm.*, v.name as vendor_name 
                                    FROM device_models dm 
                                    JOIN vendors v ON dm.vendor_id = v.id")->fetchAll();
        
        echo $this->app->render('devices/edit.php', [
            'device' => $device,
            'types' => $types,
            'locations' => $locations,
            'models' => $models
        ]);
    }
    
    /**
     * Update device
     * VULNERABLE: SQL Injection in UPDATE statement
     */
    public function update($id)
    {
        if (!$this->app->hasRole('operator')) {
            die("Access denied");
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->app->redirect("devices/{$id}/edit");
        }
        
        // VULNERABLE: No input validation
        $hostname = $_POST['hostname'] ?? '';
        $ip = $_POST['management_ip'] ?? '';
        $typeId = $_POST['device_type_id'] ?? 0;
        $modelId = $_POST['model_id'] ?? 'NULL';
        $serial = $_POST['serial_number'] ?? '';
        $iosVersion = $_POST['ios_version'] ?? '';
        $locationId = $_POST['location_id'] ?? 'NULL';
        $owner = $_POST['owner'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // VULNERABLE: SQL Injection in UPDATE
        $query = "UPDATE devices SET 
                  hostname = '{$hostname}',
                  management_ip = INET6_ATON('{$ip}'),
                  device_type_id = {$typeId},
                  model_id = {$modelId},
                  serial_number = '{$serial}',
                  ios_version = '{$iosVersion}',
                  location_id = {$locationId},
                  owner = '{$owner}',
                  notes = '{$notes}'
                  WHERE id = {$id}";
        
        try {
            $this->db->exec($query);
            
            // Log the action (also vulnerable)
            $userId = $this->app->getCurrentUser()['id'];
            $logQuery = "INSERT INTO change_log (device_id, user_id, action, details) 
                        VALUES ({$id}, {$userId}, 'update', '{\"hostname\":\"{$hostname}\"}')";
            $this->db->exec($logQuery);
            
            $this->app->redirect("devices/{$id}");
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            die("Error updating device");
        }
    }
    
    /**
     * Delete device
     * VULNERABLE: SQL Injection via ID parameter
     */
    public function delete($id)
    {
        if (!$this->app->hasRole('admin')) {
            die("Access denied - Admin only");
        }
        
        // VULNERABLE: No CSRF token validation
        // VULNERABLE: SQL Injection
        $query = "DELETE FROM devices WHERE id = {$id}";
        
        try {
            $this->db->exec($query);
            $this->app->redirect('devices');
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            die("Error deleting device");
        }
    }
    
    /**
     * Export devices to CSV
     * VULNERABLE: SQL Injection in export query
     */
    public function export()
    {
        if (!$this->app->isAuthenticated()) {
            die("Access denied");
        }
        
        // VULNERABLE: No input validation
        $format = $_GET['format'] ?? 'csv';
        $filter = $_GET['filter'] ?? '';
        
        $query = "SELECT d.hostname, 
                         INET6_NTOA(d.management_ip) as ip,
                         dt.label as type,
                         dm.model_name as model,
                         d.serial_number,
                         d.ios_version,
                         l.name as location,
                         d.owner
                  FROM devices d
                  LEFT JOIN device_types dt ON d.device_type_id = dt.id
                  LEFT JOIN device_models dm ON d.model_id = dm.id
                  LEFT JOIN locations l ON d.location_id = l.id
                  WHERE 1=1";
        
        // VULNERABLE: SQL Injection in filter
        if (!empty($filter)) {
            $query .= " AND ({$filter})";
        }
        
        try {
            $devices = $this->db->query($query)->fetchAll();
            
            if ($format === 'json') {
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="devices.json"');
                echo json_encode($devices, JSON_PRETTY_PRINT);
            } else {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="devices.csv"');
                
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Hostname', 'IP', 'Type', 'Model', 'Serial', 'IOS Version', 'Location', 'Owner']);
                
                foreach ($devices as $device) {
                    fputcsv($output, $device);
                }
                
                fclose($output);
            }
            exit;
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            die("Export failed");
        }
    }
}
