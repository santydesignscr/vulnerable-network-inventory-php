<?php
$app = NetInventory\App::getInstance();
$title = ($device ? 'Edit Device' : 'Create Device') . ' - Net Inventory';
ob_start();
?>

<div class="row mb-4">
    <div class="col-12">
        <h1>
            <i class="bi bi-<?= $device ? 'pencil' : 'plus-circle' ?>"></i>
            <?= $device ? 'Edit' : 'Create' ?> Device
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= $app->getBaseUrl() ?>/">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= $app->getBaseUrl() ?>/devices">Devices</a></li>
                <li class="breadcrumb-item active"><?= $device ? 'Edit' : 'Create' ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <!-- VULNERABLE: No CSRF token -->
                <form method="POST" action="<?= $device ? $app->getBaseUrl() . '/devices/' . $device['id'] . '/edit' : $app->getBaseUrl() . '/devices/create' ?>">
                    
                    <div class="row g-3">
                        <!-- Hostname -->
                        <div class="col-md-6">
                            <label for="hostname" class="form-label">Hostname *</label>
                            <input type="text" class="form-control" id="hostname" name="hostname" 
                                   value="<?= htmlspecialchars($device['hostname'] ?? '') ?>" required>
                        </div>
                        
                        <!-- Management IP -->
                        <div class="col-md-6">
                            <label for="management_ip" class="form-label">Management IP</label>
                            <input type="text" class="form-control" id="management_ip" name="management_ip" 
                                   value="<?= htmlspecialchars($device['ip_address'] ?? '') ?>" 
                                   placeholder="10.0.0.1">
                        </div>
                        
                        <!-- Device Type -->
                        <div class="col-md-6">
                            <label for="device_type_id" class="form-label">Device Type *</label>
                            <select class="form-select" id="device_type_id" name="device_type_id" required>
                                <option value="">Select type...</option>
                                <?php foreach ($types as $type): ?>
                                <option value="<?= $type['id'] ?>" 
                                    <?= (isset($device['device_type_id']) && $device['device_type_id'] == $type['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['label']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Model -->
                        <div class="col-md-6">
                            <label for="model_id" class="form-label">Model</label>
                            <select class="form-select" id="model_id" name="model_id">
                                <option value="">Select model...</option>
                                <?php foreach ($models as $model): ?>
                                <option value="<?= $model['id'] ?>" 
                                    <?= (isset($device['model_id']) && $device['model_id'] == $model['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($model['vendor_name'] . ' - ' . $model['model_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Serial Number -->
                        <div class="col-md-6">
                            <label for="serial_number" class="form-label">Serial Number</label>
                            <input type="text" class="form-control" id="serial_number" name="serial_number" 
                                   value="<?= htmlspecialchars($device['serial_number'] ?? '') ?>">
                        </div>
                        
                        <!-- IOS Version -->
                        <div class="col-md-6">
                            <label for="ios_version" class="form-label">OS Version</label>
                            <input type="text" class="form-control" id="ios_version" name="ios_version" 
                                   value="<?= htmlspecialchars($device['ios_version'] ?? '') ?>" 
                                   placeholder="16.9.4">
                        </div>
                        
                        <!-- Location -->
                        <div class="col-md-6">
                            <label for="location_id" class="form-label">Location</label>
                            <select class="form-select" id="location_id" name="location_id">
                                <option value="">Select location...</option>
                                <?php foreach ($locations as $location): ?>
                                <option value="<?= $location['id'] ?>" 
                                    <?= (isset($device['location_id']) && $device['location_id'] == $location['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($location['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Owner -->
                        <div class="col-md-6">
                            <label for="owner" class="form-label">Owner / Team</label>
                            <input type="text" class="form-control" id="owner" name="owner" 
                                   value="<?= htmlspecialchars($device['owner'] ?? '') ?>" 
                                   placeholder="NetOps Team">
                        </div>
                        
                        <!-- Notes -->
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($device['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?= $device ? $app->getBaseUrl() . '/devices/' . $device['id'] : $app->getBaseUrl() . '/devices' ?>" 
                           class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> <?= $device ? 'Update' : 'Create' ?> Device
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card bg-light">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-info-circle"></i> Help
                </h5>
                <p class="small mb-2"><strong>Required fields:</strong></p>
                <ul class="small">
                    <li>Hostname: Unique device identifier</li>
                    <li>Device Type: Select from available types</li>
                </ul>
                
                <p class="small mb-2 mt-3"><strong>Optional fields:</strong></p>
                <ul class="small">
                    <li>Management IP: IPv4 or IPv6 address</li>
                    <li>Model: Choose manufacturer and model</li>
                    <li>Serial Number: Device serial</li>
                    <li>OS Version: Firmware/IOS version</li>
                    <li>Location: Physical location</li>
                    <li>Owner: Team or person responsible</li>
                </ul>
            </div>
        </div>
        
        <?php if ($device): ?>
        <div class="card bg-info text-white mt-3">
            <div class="card-body">
                <h6 class="card-title">Device Info</h6>
                <p class="small mb-1">
                    <strong>Created:</strong> <?= htmlspecialchars($device['created_at']) ?>
                </p>
                <p class="small mb-0">
                    <strong>Last Updated:</strong> <?= htmlspecialchars($device['updated_at']) ?>
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
