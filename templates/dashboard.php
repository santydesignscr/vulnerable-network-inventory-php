<?php
$app = NetInventory\App::getInstance();
$title = 'Dashboard - Net Inventory';
ob_start();
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="bi bi-speedometer2"></i> Dashboard
        </h1>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Devices</h5>
                <h2 class="mb-0"><?= $stats['total_devices'] ?></h2>
                <small>Registered in system</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Device Types</h5>
                <h2 class="mb-0"><?= count($stats['by_type']) ?></h2>
                <small>Different categories</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">IP Assignments</h5>
                <h2 class="mb-0"><?= $stats['total_ips'] ?></h2>
                <small>
                    IPv4: <?= $stats['ipv4_count'] ?> | 
                    IPv6: <?= $stats['ipv6_count'] ?>
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Your Role</h5>
                <h2 class="mb-0"><?= strtoupper($user['role']) ?></h2>
                <small><?= htmlspecialchars($user['full_name']) ?></small>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Devices by Type</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($stats['by_type'])): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($stats['by_type'] as $type): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($type['label']) ?>
                        <span class="badge bg-primary rounded-pill"><?= $type['count'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted">No devices registered yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Devices</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($stats['recent_devices'])): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($stats['recent_devices'] as $device): ?>
                    <a href="<?= $app->getBaseUrl() ?>/devices/<?= $device['id'] ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?= htmlspecialchars($device['hostname']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($device['type']) ?></small>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-globe"></i> <?= htmlspecialchars($device['ip'] ?? 'N/A') ?>
                        </small>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted">No recent devices.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-activity"></i> Recent Activity</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($stats['recent_activity'])): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Device</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['recent_activity'] as $log): ?>
                            <tr>
                                <td><small><?= htmlspecialchars($log['created_at']) ?></small></td>
                                <td><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                                <td>
                                    <span class="badge bg-info"><?= htmlspecialchars($log['action']) ?></span>
                                </td>
                                <td>
                                    <?php if ($log['hostname']): ?>
                                    <a href="<?= $app->getBaseUrl() ?>/devices/<?= $log['device_id'] ?>">
                                        <?= htmlspecialchars($log['hostname']) ?>
                                    </a>
                                    <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= htmlspecialchars($log['details'] ?? '') ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">No recent activity.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Custom Query Tool (Admin Only) - VERY VULNERABLE -->
<?php if ($app->hasRole('admin')): ?>
<div class="row">
    <div class="col-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="bi bi-terminal"></i> Custom SQL Query Tool (Admin Only)
                    <span class="badge bg-warning text-dark">⚠️ DANGEROUS</span>
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="mb-3">
                        <label class="form-label">SQL Query:</label>
                        <textarea class="form-control font-monospace" name="custom_query" rows="3" placeholder="SELECT * FROM devices WHERE..."><?= isset($stats['custom_query']) ? htmlspecialchars($stats['custom_query']) : '' ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-play-fill"></i> Execute Query
                    </button>
                </form>
                
                <?php if (isset($stats['custom_result'])): ?>
                <hr>
                <h6>Query Results:</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <?php if (!empty($stats['custom_result'])): ?>
                        <thead>
                            <tr>
                                <?php foreach (array_keys($stats['custom_result'][0]) as $col): ?>
                                <th><?= htmlspecialchars($col) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['custom_result'] as $row): ?>
                            <tr>
                                <?php foreach ($row as $val): ?>
                                <td><?= htmlspecialchars($val ?? 'NULL') ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <?php else: ?>
                        <tbody>
                            <tr><td class="text-muted">No results</td></tr>
                        </tbody>
                        <?php endif; ?>
                    </table>
                </div>
                <?php endif; ?>
                
                <?php if (isset($stats['custom_error'])): ?>
                <div class="alert alert-danger mt-3">
                    <strong>SQL Error:</strong> <?= htmlspecialchars($stats['custom_error']) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
