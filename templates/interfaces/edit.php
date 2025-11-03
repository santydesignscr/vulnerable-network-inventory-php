<?php
/**
 * Interface Create/Edit Form Template
 * 
 * ⚠️ VULNERABLE BY DESIGN - FOR EDUCATIONAL PURPOSES ONLY
 */

$app = NetInventory\App::getInstance();
$isEdit = isset($interface) && $interface !== null;
$pageTitle = $isEdit ? 'Edit Interface' : 'Add New Interface';
$title = $pageTitle . ' - ' . htmlspecialchars($device['hostname']);
ob_start();
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-diagram-3"></i> 
                        <?= $pageTitle ?>
                    </h4>
                    <a href="/net-inventory/public/devices/<?= htmlspecialchars($device['id']) ?>" 
                       class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Device
                    </a>
                </div>
                <div class="card-body">
                    <!-- Device Info -->
                    <div class="alert alert-info mb-4">
                        <strong>Device:</strong> <?= htmlspecialchars($device['hostname']) ?>
                        <?php if (!empty($device['ip_address'])): ?>
                        <span class="text-muted">
                            (<code><?= htmlspecialchars($device['ip_address']) ?></code>)
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Interface Form -->
                    <form method="POST" action="<?= $isEdit ? '/net-inventory/public/interfaces/' . $interface['id'] . '/edit' : '/net-inventory/public/interfaces/create/' . $device['id'] ?>">
                        <div class="row">
                            <!-- Interface Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Interface Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       name="name" 
                                       placeholder="e.g., GigabitEthernet0/1, FastEthernet1/0/1, eth0"
                                       value="<?= htmlspecialchars($interface['name'] ?? '') ?>"
                                       required>
                                <small class="text-muted">
                                    Examples: GigabitEthernet0/1, FastEthernet1/0/1, eth0, ens33
                                </small>
                            </div>

                            <!-- Speed -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Speed</label>
                                <select class="form-select" name="speed">
                                    <option value="">Select Speed</option>
                                    <option value="10 Mbps" <?= ($interface['speed'] ?? '') === '10 Mbps' ? 'selected' : '' ?>>10 Mbps</option>
                                    <option value="100 Mbps" <?= ($interface['speed'] ?? '') === '100 Mbps' ? 'selected' : '' ?>>100 Mbps</option>
                                    <option value="1 Gbps" <?= ($interface['speed'] ?? '') === '1 Gbps' ? 'selected' : '' ?>>1 Gbps</option>
                                    <option value="10 Gbps" <?= ($interface['speed'] ?? '') === '10 Gbps' ? 'selected' : '' ?>>10 Gbps</option>
                                    <option value="25 Gbps" <?= ($interface['speed'] ?? '') === '25 Gbps' ? 'selected' : '' ?>>25 Gbps</option>
                                    <option value="40 Gbps" <?= ($interface['speed'] ?? '') === '40 Gbps' ? 'selected' : '' ?>>40 Gbps</option>
                                    <option value="100 Gbps" <?= ($interface['speed'] ?? '') === '100 Gbps' ? 'selected' : '' ?>>100 Gbps</option>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="description" 
                                       placeholder="e.g., Uplink to Core Switch, Server Connection, LAN Users"
                                       value="<?= htmlspecialchars($interface['description'] ?? '') ?>">
                            </div>

                            <!-- IP Address -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">IP Address</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="ip_address" 
                                       placeholder="e.g., 192.168.1.1 or 2001:db8::1"
                                       value="<?= htmlspecialchars($interface['ip'] ?? '') ?>">
                                <small class="text-muted">
                                    Supports both IPv4 and IPv6
                                </small>
                            </div>

                            <!-- MAC Address -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">MAC Address</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="mac_address" 
                                       placeholder="e.g., AA:BB:CC:DD:EE:FF"
                                       value="<?= htmlspecialchars($interface['mac_formatted'] ?? '') ?>">
                                <small class="text-muted">
                                    Format: AA:BB:CC:DD:EE:FF or AA-BB-CC-DD-EE-FF
                                </small>
                            </div>

                            <!-- Admin Status -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Administrative Status</label>
                                <select class="form-select" name="admin_status">
                                    <option value="unknown" <?= ($interface['admin_status'] ?? 'unknown') === 'unknown' ? 'selected' : '' ?>>Unknown</option>
                                    <option value="up" <?= ($interface['admin_status'] ?? '') === 'up' ? 'selected' : '' ?>>Up</option>
                                    <option value="down" <?= ($interface['admin_status'] ?? '') === 'down' ? 'selected' : '' ?>>Down</option>
                                </select>
                                <small class="text-muted">
                                    Administrative status (configured state)
                                </small>
                            </div>

                            <!-- Operational Status -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Operational Status</label>
                                <select class="form-select" name="oper_status">
                                    <option value="unknown" <?= ($interface['oper_status'] ?? 'unknown') === 'unknown' ? 'selected' : '' ?>>Unknown</option>
                                    <option value="up" <?= ($interface['oper_status'] ?? '') === 'up' ? 'selected' : '' ?>>Up</option>
                                    <option value="down" <?= ($interface['oper_status'] ?? '') === 'down' ? 'selected' : '' ?>>Down</option>
                                    <option value="dormant" <?= ($interface['oper_status'] ?? '') === 'dormant' ? 'selected' : '' ?>>Dormant</option>
                                </select>
                                <small class="text-muted">
                                    Operational status (actual state)
                                </small>
                            </div>

                            <!-- Notes -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" 
                                          name="notes" 
                                          rows="3" 
                                          placeholder="Additional notes or comments about this interface..."><?= htmlspecialchars($interface['notes'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Vulnerability Warning -->
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Vulnerability Note:</strong> This form is intentionally vulnerable to SQL injection 
                            attacks. Input is not sanitized for educational purposes.
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/net-inventory/public/devices/<?= htmlspecialchars($device['id']) ?>" 
                               class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> 
                                <?= $isEdit ? 'Update Interface' : 'Create Interface' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-label {
    font-weight: 600;
}

.form-control:focus,
.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.text-danger {
    color: #dc3545 !important;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
