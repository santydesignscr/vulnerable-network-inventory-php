<?php
$app = NetInventory\App::getInstance();
$title = 'Search Results for "' . htmlspecialchars($query) . '"';
ob_start();
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1>
                <i class="bi bi-search"></i>
                Search Results
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $app->getBaseUrl() ?>/">Home</a></li>
                    <li class="breadcrumb-item active">Search</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="<?= $app->getBaseUrl() ?>/search" class="row g-3">
                        <div class="col-auto flex-grow-1">
                            <input type="text" 
                                   class="form-control" 
                                   name="q" 
                                   placeholder="Search devices, IPs, owners..." 
                                   value="<?= htmlspecialchars($query) ?>"
                                   autofocus>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        Results for: <strong>"<?= htmlspecialchars($query) ?>"</strong>
                    </h5>
                    <span class="badge bg-primary">
                        <?= count($results) ?> result<?= count($results) !== 1 ? 's' : '' ?> found
                    </span>
                </div>
                <div class="card-body">
                    <?php if (empty($results)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i>
                            <strong>No results found.</strong>
                            <p class="mb-0 mt-2">Try different search terms or check your spelling.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Hostname</th>
                                        <th>IP Address</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Owner</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $device): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= $app->getBaseUrl() ?>/devices/<?= $device['id'] ?>" class="text-decoration-none">
                                                <i class="bi bi-hdd-network"></i>
                                                <strong><?= htmlspecialchars($device['hostname']) ?></strong>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($device['ip']): ?>
                                                <code><?= htmlspecialchars($device['ip']) ?></code>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($device['type']): ?>
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars($device['type']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($device['location'] ?? '-') ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($device['owner'] ?? '-') ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= $app->getBaseUrl() ?>/devices/<?= $device['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <?php if ($app->hasRole('operator')): ?>
                                            <a href="<?= $app->getBaseUrl() ?>/devices/<?= $device['id'] ?>/edit" 
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
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
        </div>
    </div>

    <!-- Vulnerability Warning -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Vulnerability Note:</strong> This search functionality is intentionally vulnerable to SQL injection 
                attacks. Search parameters are not sanitized for educational purposes.
            </div>
        </div>
    </div>
</div>

<style>
.table tbody tr {
    cursor: pointer;
    transition: background-color 0.2s;
}

.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.badge {
    font-weight: 500;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
