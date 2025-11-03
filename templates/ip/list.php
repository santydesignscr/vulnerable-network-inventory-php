<?php
$app = NetInventory\App::getInstance();
$title = 'IP Address Management';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-hdd-network"></i> IP Address Management</h1>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-10">
                <input type="text" 
                       class="form-control" 
                       name="search" 
                       placeholder="Search by IP address, hostname, or purpose..." 
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Results -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-list-ul"></i> IP Assignments
            <span class="badge bg-secondary"><?= count($assignments) ?></span>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($assignments)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <?= empty($search) ? 'No IP assignments found in the system.' : 'No IP assignments match your search.' ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>IP Address</th>
                        <th>Prefix</th>
                        <th>Device</th>
                        <th>Interface</th>
                        <th>Assigned For</th>
                        <th>Assigned At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $ip): ?>
                    <tr>
                        <td>
                            <code class="<?= strpos($ip['ip_address'], ':') !== false ? 'text-info' : '' ?>">
                                <?= htmlspecialchars($ip['ip_address']) ?>
                            </code>
                        </td>
                        <td><?= htmlspecialchars($ip['prefix'] ?? '-') ?></td>
                        <td>
                            <a href="<?= $app->getBaseUrl() ?>/devices/<?= $ip['device_id'] ?>">
                                <?= htmlspecialchars($ip['hostname']) ?>
                            </a>
                        </td>
                        <td>
                            <?php if ($ip['interface_name']): ?>
                            <code><?= htmlspecialchars($ip['interface_name']) ?></code>
                            <?php else: ?>
                            <span class="text-muted">Management</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($ip['assigned_for'] ?? '-') ?></td>
                        <td><small><?= htmlspecialchars($ip['assigned_at']) ?></small></td>
                        <td>
                            <a href="<?= $app->getBaseUrl() ?>/devices/<?= $ip['device_id'] ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if ($app->hasRole('operator')): ?>
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="if(confirm('Delete this IP assignment?')) deleteIpAssignment(<?= $ip['id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
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
