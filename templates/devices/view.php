<?php
$app = NetInventory\App::getInstance();
$title = htmlspecialchars($device['hostname']) . ' - Device Details';
ob_start();
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h1>
            <i class="bi bi-router"></i>
            <?= htmlspecialchars($device['hostname']) ?>
            <span class="badge bg-secondary"><?= htmlspecialchars($device['device_type']) ?></span>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= $app->getBaseUrl() ?>/">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= $app->getBaseUrl() ?>/devices">Devices</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($device['hostname']) ?></li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4 text-end">
        <?php if ($app->hasRole('operator')): ?>
        <a href="<?= $app->getBaseUrl() ?>/devices/<?= $device['id'] ?>/edit" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <?php endif; ?>
        <?php if ($app->hasRole('admin')): ?>
        <button class="btn btn-danger" onclick="if(confirm('Delete this device?')) deleteDevice(<?= $device['id'] ?>)">
            <i class="bi bi-trash"></i> Delete
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Device Information -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Device Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Hostname</label>
                        <div class="fw-bold"><?= htmlspecialchars($device['hostname']) ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Management IP</label>
                        <div class="fw-bold">
                            <code><?= htmlspecialchars($device['ip_address'] ?? 'N/A') ?></code>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Device Type</label>
                        <div><?= htmlspecialchars($device['device_type']) ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Model</label>
                        <div>
                            <?php if ($device['vendor_name'] && $device['model_name']): ?>
                            <?= htmlspecialchars($device['vendor_name']) ?> - <?= htmlspecialchars($device['model_name']) ?>
                            <?php else: ?>
                            <span class="text-muted">Not specified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Serial Number</label>
                        <div><?= htmlspecialchars($device['serial_number'] ?? 'N/A') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">OS Version</label>
                        <div><?= htmlspecialchars($device['ios_version'] ?? 'N/A') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Location</label>
                        <div><?= htmlspecialchars($device['location_name'] ?? 'N/A') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Owner / Team</label>
                        <div><?= htmlspecialchars($device['owner'] ?? 'N/A') ?></div>
                    </div>
                    <?php if ($device['notes']): ?>
                    <div class="col-12 mb-3">
                        <label class="text-muted small">Notes</label>
                        <div><?= nl2br(htmlspecialchars($device['notes'])) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-6">
                        <label class="text-muted small">Created</label>
                        <div><?= htmlspecialchars($device['created_at']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Last Updated</label>
                        <div><?= htmlspecialchars($device['updated_at']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card bg-primary text-white mb-3">
            <div class="card-body text-center">
                <h3 class="mb-0"><?= count($interfaces) ?></h3>
                <small>Interfaces</small>
            </div>
        </div>
        <div class="card bg-success text-white mb-3">
            <div class="card-body text-center">
                <h3 class="mb-0"><?= count($configs) ?></h3>
                <small>Configurations</small>
            </div>
        </div>
        <div class="card bg-warning text-white mb-3">
            <div class="card-body text-center">
                <h3 class="mb-0"><?= count($ipAssignments) ?></h3>
                <small>IP Assignments</small>
            </div>
        </div>
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3 class="mb-0"><?= count($logs) ?></h3>
                <small>Recent Changes</small>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3" id="deviceTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="interfaces-tab" data-bs-toggle="tab" data-bs-target="#interfaces" type="button">
            <i class="bi bi-diagram-3"></i> Interfaces
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="ips-tab" data-bs-toggle="tab" data-bs-target="#ips" type="button">
            <i class="bi bi-hdd-network"></i> IP Assignments
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="configs-tab" data-bs-toggle="tab" data-bs-target="#configs" type="button">
            <i class="bi bi-file-earmark-text"></i> Configurations
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button">
            <i class="bi bi-clock-history"></i> Change Log
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Interfaces Tab -->
    <div class="tab-pane fade show active" id="interfaces" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <?php if (empty($interfaces)): ?>
                <p class="text-muted">No interfaces registered for this device.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Interface Name</th>
                                <th>Description</th>
                                <th>Speed</th>
                                <th>IP Address</th>
                                <th>Admin Status</th>
                                <th>Oper Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($interfaces as $iface): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($iface['name']) ?></code></td>
                                <td><?= htmlspecialchars($iface['description'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($iface['speed'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($iface['ip'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-<?= $iface['admin_status'] === 'up' ? 'success' : 'secondary' ?>">
                                        <?= htmlspecialchars($iface['admin_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $iface['oper_status'] === 'up' ? 'success' : 'warning' ?>">
                                        <?= htmlspecialchars($iface['oper_status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- IP Assignments Tab -->
    <div class="tab-pane fade" id="ips" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">IP Address Assignments</h5>
                <?php if ($app->hasRole('operator')): ?>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignIpModal">
                    <i class="bi bi-plus-circle"></i> Assign IP
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($ipAssignments)): ?>
                <p class="text-muted">No IP addresses assigned to this device.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>IP Address</th>
                                <th>Prefix</th>
                                <th>Interface</th>
                                <th>Assigned For</th>
                                <th>Assigned At</th>
                                <?php if ($app->hasRole('operator')): ?>
                                <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ipAssignments as $ip): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($ip['ip_address']) ?></code></td>
                                <td><?= htmlspecialchars($ip['prefix'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($ip['interface_name'] ?? 'Device Management') ?></td>
                                <td><?= htmlspecialchars($ip['assigned_for'] ?? '-') ?></td>
                                <td><small><?= htmlspecialchars($ip['assigned_at']) ?></small></td>
                                <?php if ($app->hasRole('operator')): ?>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('Delete IP assignment?')) deleteIpAssignment(<?= $ip['id'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Configurations Tab -->
    <div class="tab-pane fade" id="configs" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Configuration Backups</h5>
                <?php if ($app->hasRole('operator')): ?>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bi bi-upload"></i> Upload Config
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($configs)): ?>
                <p class="text-muted">No configurations uploaded yet.</p>
                <?php else: ?>
                <div class="list-group">
                    <?php foreach ($configs as $config): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">
                                <i class="bi bi-file-earmark-text"></i>
                                <?= htmlspecialchars($config['filename']) ?>
                            </h6>
                            <div>
                                <a href="<?= $app->getBaseUrl() ?>/configs/<?= $config['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="<?= $app->getBaseUrl() ?>/configs/<?= $config['id'] ?>/download" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-download"></i> Download
                                </a>
                                <?php if ($app->hasRole('operator')): ?>
                                <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('Delete config?')) deleteConfig(<?= $config['id'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="mb-1 small text-muted">
                            Uploaded by <?= htmlspecialchars($config['username'] ?? 'Unknown') ?> 
                            on <?= htmlspecialchars($config['uploaded_at']) ?>
                        </p>
                        <?php if ($config['notes']): ?>
                        <p class="mb-0 small"><?= htmlspecialchars($config['notes']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Change Log Tab -->
    <div class="tab-pane fade" id="logs" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <?php if (empty($logs)): ?>
                <p class="text-muted">No change history available.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><small><?= htmlspecialchars($log['created_at']) ?></small></td>
                                <td><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                                <td>
                                    <span class="badge bg-info"><?= htmlspecialchars($log['action']) ?></span>
                                </td>
                                <td><small><?= htmlspecialchars($log['details'] ?? '') ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Assign IP Modal -->
<div class="modal fade" id="assignIpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign IP Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $app->getBaseUrl() ?>/ip/assign">
                <input type="hidden" name="device_id" value="<?= $device['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">IP Address *</label>
                        <input type="text" class="form-control" name="ip" placeholder="192.168.1.1 or 2001:db8::1" required>
                        <div class="form-text">Enter IPv4 or IPv6 address</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prefix Length</label>
                        <input type="number" class="form-control" name="prefix" placeholder="24" min="0" max="128">
                        <div class="form-text">Leave empty if not applicable</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Interface</label>
                        <select class="form-select" name="interface_id">
                            <option value="">Device Management IP</option>
                            <?php foreach ($interfaces as $iface): ?>
                            <option value="<?= $iface['id'] ?>">
                                <?= htmlspecialchars($iface['name']) ?>
                                <?php if ($iface['description']): ?>
                                - <?= htmlspecialchars($iface['description']) ?>
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assigned For</label>
                        <input type="text" class="form-control" name="assigned_for" placeholder="Primary IP, VPN, etc.">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign IP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Configuration Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $app->getBaseUrl() ?>/configs/upload/<?= $device['id'] ?>" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Configuration File</label>
                        <input type="file" class="form-control" name="config_file" accept=".txt,.cfg,.conf,.log">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteDevice(id) {
    fetch('<?= $app->getBaseUrl() ?>/devices/' + id + '/delete', {
        method: 'POST'
    }).then(() => {
        window.location.href = '<?= $app->getBaseUrl() ?>/devices';
    });
}

function deleteConfig(id) {
    fetch('<?= $app->getBaseUrl() ?>/configs/' + id + '/delete', {
        method: 'POST'
    }).then(() => {
        window.location.reload();
    });
}

function deleteIpAssignment(id) {
    fetch('<?= $app->getBaseUrl() ?>/ip/' + id + '/delete', {
        method: 'POST'
    }).then(() => {
        window.location.reload();
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
