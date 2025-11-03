<?php
/**
 * Import Devices from CSV
 * 
 * Usage: php scripts/import_devices.php devices.csv
 * 
 * CSV Format:
 * hostname,management_ip,device_type,vendor,model,serial,os_version,location,owner,notes
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Manual autoload if composer not available
spl_autoload_register(function ($class) {
    $prefix = 'NetInventory\\';
    $base_dir = __DIR__ . '/../src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Check arguments
if ($argc < 2) {
    die("Usage: php import_devices.php <csv_file>\n");
}

$csvFile = $argv[1];

if (!file_exists($csvFile)) {
    die("Error: File not found: {$csvFile}\n");
}

// Initialize app
$app = NetInventory\App::getInstance();
$db = $app->getDb();

// Device type mapping
$deviceTypeMap = [];
$stmt = $db->query("SELECT id, slug FROM device_types");
while ($row = $stmt->fetch()) {
    $deviceTypeMap[$row['slug']] = $row['id'];
}

// Location mapping
$locationMap = [];
$stmt = $db->query("SELECT id, name FROM locations");
while ($row = $stmt->fetch()) {
    $locationMap[$row['name']] = $row['id'];
}

// Vendor mapping
$vendorMap = [];
$stmt = $db->query("SELECT id, name FROM vendors");
while ($row = $stmt->fetch()) {
    $vendorMap[$row['name']] = $row['id'];
}

// Model mapping
$modelMap = [];
$stmt = $db->query("SELECT id, vendor_id, model_name FROM device_models");
while ($row = $stmt->fetch()) {
    $key = $row['vendor_id'] . '|' . $row['model_name'];
    $modelMap[$key] = $row['id'];
}

// Open CSV file
$handle = fopen($csvFile, 'r');
if ($handle === false) {
    die("Error: Cannot open file: {$csvFile}\n");
}

// Read header
$header = fgetcsv($handle);
if ($header === false) {
    die("Error: Empty CSV file\n");
}

$imported = 0;
$errors = 0;

echo "Starting import from {$csvFile}...\n\n";

// Read data rows
while (($data = fgetcsv($handle)) !== false) {
    if (count($data) < 2) {
        continue; // Skip empty lines
    }
    
    // Map CSV columns to array
    $row = array_combine($header, $data);
    
    $hostname = $row['hostname'] ?? '';
    $managementIp = $row['management_ip'] ?? '';
    $deviceType = $row['device_type'] ?? 'router';
    $vendor = $row['vendor'] ?? '';
    $model = $row['model'] ?? '';
    $serial = $row['serial'] ?? '';
    $osVersion = $row['os_version'] ?? '';
    $location = $row['location'] ?? '';
    $owner = $row['owner'] ?? '';
    $notes = $row['notes'] ?? '';
    
    // Validate required fields
    if (empty($hostname)) {
        echo "❌ Skipping row: Missing hostname\n";
        $errors++;
        continue;
    }
    
    // Get device type ID
    $deviceTypeId = $deviceTypeMap[$deviceType] ?? $deviceTypeMap['router'] ?? 1;
    
    // Get location ID
    $locationId = null;
    if (!empty($location) && isset($locationMap[$location])) {
        $locationId = $locationMap[$location];
    }
    
    // Get or create vendor
    $vendorId = null;
    if (!empty($vendor)) {
        if (!isset($vendorMap[$vendor])) {
            // VULNERABLE: SQL Injection
            $query = "INSERT INTO vendors (name) VALUES ('{$vendor}')";
            try {
                $db->exec($query);
                $vendorId = $db->lastInsertId();
                $vendorMap[$vendor] = $vendorId;
                echo "  ➕ Created vendor: {$vendor}\n";
            } catch (Exception $e) {
                echo "  ⚠️  Warning: Could not create vendor: {$vendor}\n";
            }
        } else {
            $vendorId = $vendorMap[$vendor];
        }
    }
    
    // Get or create model
    $modelId = null;
    if ($vendorId && !empty($model)) {
        $modelKey = $vendorId . '|' . $model;
        if (!isset($modelMap[$modelKey])) {
            // VULNERABLE: SQL Injection
            $query = "INSERT INTO device_models (vendor_id, model_name) VALUES ({$vendorId}, '{$model}')";
            try {
                $db->exec($query);
                $modelId = $db->lastInsertId();
                $modelMap[$modelKey] = $modelId;
                echo "  ➕ Created model: {$model}\n";
            } catch (Exception $e) {
                echo "  ⚠️  Warning: Could not create model: {$model}\n";
            }
        } else {
            $modelId = $modelMap[$modelKey];
        }
    }
    
    // Insert device - VULNERABLE: SQL Injection
    $ipValue = !empty($managementIp) ? "INET6_ATON('{$managementIp}')" : 'NULL';
    $modelValue = $modelId ? $modelId : 'NULL';
    $locationValue = $locationId ? $locationId : 'NULL';
    
    $query = "INSERT INTO devices 
              (hostname, management_ip, device_type_id, model_id, serial_number, 
               ios_version, location_id, owner, notes)
              VALUES 
              ('{$hostname}', {$ipValue}, {$deviceTypeId}, {$modelValue}, 
               '{$serial}', '{$osVersion}', {$locationValue}, '{$owner}', '{$notes}')";
    
    try {
        $db->exec($query);
        $imported++;
        echo "✅ Imported: {$hostname} ({$managementIp})\n";
    } catch (Exception $e) {
        $errors++;
        echo "❌ Error importing {$hostname}: " . $e->getMessage() . "\n";
    }
}

fclose($handle);

echo "\n";
echo "========================================\n";
echo "Import completed!\n";
echo "Imported: {$imported} devices\n";
echo "Errors: {$errors}\n";
echo "========================================\n";
