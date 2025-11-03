<?php
$app = NetInventory\App::getInstance();
$title = 'Devices List - Net Inventory';
ob_start();
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-router"></i> Network Devices</h1>
        <p class="text-muted">Total: <?= $total ?> devices</p>
    </div>
    <div class="col-md-6 text-end">
        <?php if ($app->hasRole('operator')): ?>
        <a href="<?= $app->getBaseUrl() ?>/devices/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Device
        </a>
        <?php endif; ?>
        <a href="<?= $app->getBaseUrl() ?>/devices/export?format=csv" class="btn btn-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
        <a href="<?= $app->getBaseUrl() ?>/devices/export?format=json" class="btn btn-info">
            <i class="bi bi-filetype-json"></i> Export JSON
        </a>
    </div>
</div>

<!-- Filters Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= $app->getBaseUrl() ?>/devices" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Hostname, IP, Owner...">
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Device Type</label>
                <select class="form-select" name="type">
                    <option value="">All Types</option>
                    <?php foreach ($types as $type): ?>
                    <option value="<?= $type['id'] ?>" <?= (isset($_GET['type']) && $_GET['type'] == $type['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($type['label']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Location</label>
                <select class="form-select" name="location">
                    <option value="">All Locations</option>
                    <?php foreach ($locations as $location): ?>
                    <option value="<?= $location['id'] ?>" <?= (isset($_GET['location']) && $_GET['location'] == $location['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($location['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Devices Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($devices)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No devices found. <?php if ($app->hasRole('operator')): ?>
            <a href="<?= $app->getBaseUrl() ?>/devices/create">Add your first device</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>
                            <a href="?sort=hostname&order=<?= ($_GET['order'] ?? 'ASC') === 'ASC' ? 'DESC' : 'ASC' ?>" class="text-decoration-none">
                                Hostname <i class="bi bi-arrow-down-up"></i>
                            </a>
                        </th>
                        <th>Management IP</th>
                        <th>Type</th>
                        <th>Model</th>
                        <th>Location</th>
                        <th>Owner</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devices as $device): ?>
                    <tr>
                        <td>
                            <a href="<?= $app->getBaseUrl() ?>/devices/<?= $device['id'] ?>" class="fw-bold">
                                <?= htmlspecialchars($device['hostname']) ?>
                            </a>
                        </td>
                        <td>
                            <code><?= htmlspecialchars($device['ip_address'] ?? 'N/A') ?></code>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= htmlspecialchars($device['device_type']) ?></span>
                        </td>
                        <td>
                            <?php if ($device['vendor_name'] && $device['model_name']): ?>
                            <small><?= htmlspecialchars($device['vendor_name']) ?><br>
                            <?= htmlspecialchars($device['model_name']) ?></small>
                            <?php else: ?>
                            <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($device['location_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($device['owner'] ?? 'N/A') ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= $app->getBaseUrl() ?>/devices/<?= $device['id'] ?>" class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($app->hasRole('operator')): ?>
                                <a href="<?= $app->getBaseUrl() ?>/devices/<?= $device['id'] ?>/edit" class="btn btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php if ($app->hasRole('admin')): ?>
                                <button type="button" class="btn btn-outline-danger" title="Delete" 
                                        onclick="if(confirm('Delete device?')) { deleteDevice(<?= $device['id'] ?>); }">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search ?? '') ?>">Previous</a>
                </li>
                
                <?php for ($i = 1; $i <= min($totalPages, 10); $i++): ?>
                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search ?? '') ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteDevice(id) {
    // VULNERABLE: No CSRF token
    fetch('<?= $app->getBaseUrl() ?>/devices/' + id + '/delete', {
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
