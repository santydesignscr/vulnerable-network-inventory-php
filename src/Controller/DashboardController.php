<?php
namespace NetInventory\Controller;

use NetInventory\App;

/**
 * Dashboard Controller
 */

class DashboardController
{
    private $app;
    private $db;
    
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->db = $app->getDb();
    }
    
    /**
     * Show dashboard with statistics
     * VULNERABLE: SQL Injection in custom queries
     */
    public function index()
    {
        if (!$this->app->isAuthenticated()) {
            $this->app->redirect('login');
        }
        
        // Get statistics
        $stats = [];
        
        // Total devices
        $stats['total_devices'] = $this->db->query("SELECT COUNT(*) as count FROM devices")->fetch()['count'];
        
        // Devices by type
        $stats['by_type'] = $this->db->query("
            SELECT dt.label, COUNT(*) as count 
            FROM devices d 
            JOIN device_types dt ON d.device_type_id = dt.id 
            GROUP BY dt.label
        ")->fetchAll();
        
        // Recent devices
        $stats['recent_devices'] = $this->db->query("
            SELECT d.id, d.hostname, INET6_NTOA(d.management_ip) as ip, 
                   dt.label as type, d.created_at
            FROM devices d
            LEFT JOIN device_types dt ON d.device_type_id = dt.id
            ORDER BY d.created_at DESC
            LIMIT 5
        ")->fetchAll();
        
        // Recent activity
        $stats['recent_activity'] = $this->db->query("
            SELECT cl.*, u.username, d.hostname
            FROM change_log cl
            LEFT JOIN users u ON cl.user_id = u.id
            LEFT JOIN devices d ON cl.device_id = d.id
            ORDER BY cl.created_at DESC
            LIMIT 10
        ")->fetchAll();
        
        // Devices without configs
        $stats['no_config_count'] = $this->db->query("
            SELECT COUNT(*) as count 
            FROM devices d 
            LEFT JOIN configs c ON d.id = c.device_id 
            WHERE c.id IS NULL
        ")->fetch()['count'];
        
        // IP assignments statistics
        $stats['total_ips'] = $this->db->query("SELECT COUNT(*) as count FROM ip_assignments")->fetch()['count'];
        
        $stats['ipv4_count'] = $this->db->query("
            SELECT COUNT(*) as count 
            FROM ip_assignments 
            WHERE CHAR_LENGTH(INET6_NTOA(ip)) <= 15
        ")->fetch()['count'];
        
        $stats['ipv6_count'] = $this->db->query("
            SELECT COUNT(*) as count 
            FROM ip_assignments 
            WHERE CHAR_LENGTH(INET6_NTOA(ip)) > 15
        ")->fetch()['count'];
        
        // Custom query support (VERY VULNERABLE)
        if (isset($_GET['custom_query']) && $this->app->hasRole('admin')) {
            $customQuery = $_GET['custom_query'];
            
            try {
                $stats['custom_result'] = $this->db->query($customQuery)->fetchAll();
                $stats['custom_query'] = $customQuery;
            } catch (\PDOException $e) {
                $stats['custom_error'] = $e->getMessage();
            }
        }
        
        echo $this->app->render('dashboard.php', [
            'stats' => $stats,
            'user' => $this->app->getCurrentUser()
        ]);
    }
    
    /**
     * Search globally
     * VULNERABLE: SQL Injection in search parameter
     */
    public function search()
    {
        if (!$this->app->isAuthenticated()) {
            $this->app->redirect('login');
        }
        
        $q = $_GET['q'] ?? '';
        
        if (empty($q)) {
            $this->app->redirect('');
        }
        
        // VULNERABLE: SQL Injection
        $query = "SELECT d.id, d.hostname, INET6_NTOA(d.management_ip) as ip,
                         dt.label as type, l.name as location, d.owner
                  FROM devices d
                  LEFT JOIN device_types dt ON d.device_type_id = dt.id
                  LEFT JOIN locations l ON d.location_id = l.id
                  WHERE d.hostname LIKE '%{$q}%'
                     OR INET6_NTOA(d.management_ip) LIKE '%{$q}%'
                     OR d.owner LIKE '%{$q}%'
                     OR d.serial_number LIKE '%{$q}%'";
        
        try {
            $results = $this->db->query($query)->fetchAll();
            
            echo $this->app->render('search_results.php', [
                'query' => $q,
                'results' => $results
            ]);
            
        } catch (\PDOException $e) {
            if ($this->app->getConfig('debug')) {
                die("SQL Error: " . $e->getMessage() . "<br>Query: " . $query);
            }
            die("Search failed");
        }
    }
}
