<?php
$app = NetInventory\App::getInstance();
$title = 'Configuration - ' . htmlspecialchars($config['filename']);
ob_start();
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1>
            <i class="bi bi-file-earmark-text"></i>
            <?= htmlspecialchars($config['filename']) ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= $app->getBaseUrl() ?>/">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= $app->getBaseUrl() ?>/devices">Devices</a></li>
                <li class="breadcrumb-item">
                    <a href="<?= $app->getBaseUrl() ?>/devices/<?= $config['device_id'] ?>">
                        <?= htmlspecialchars($config['hostname']) ?>
                    </a>
                </li>
                <li class="breadcrumb-item active">Configuration</li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= $app->getBaseUrl() ?>/configs/<?= $config['id'] ?>/download" class="btn btn-success">
            <i class="bi bi-download"></i> Download
        </a>
        <a href="<?= $app->getBaseUrl() ?>/devices/<?= $config['device_id'] ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Device
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Configuration Content</h5>
                <button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard(document.querySelector('.config-content').textContent)">
                    <i class="bi bi-clipboard"></i> Copy
                </button>
            </div>
            <div class="card-body p-0">
                <pre class="config-content mb-0"><?= htmlspecialchars($config['content']) ?></pre>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Device</label>
                    <div>
                        <a href="<?= $app->getBaseUrl() ?>/devices/<?= $config['device_id'] ?>">
                            <?= htmlspecialchars($config['hostname']) ?>
                        </a>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="text-muted small">Filename</label>
                    <div><?= htmlspecialchars($config['filename']) ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="text-muted small">Uploaded By</label>
                    <div><?= htmlspecialchars($config['uploaded_by_name'] ?? 'Unknown') ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="text-muted small">Upload Date</label>
                    <div><?= htmlspecialchars($config['uploaded_at']) ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="text-muted small">Size</label>
                    <div><?= number_format(strlen($config['content'])) ?> bytes</div>
                </div>
                
                <?php if ($config['notes']): ?>
                <div class="mb-3">
                    <label class="text-muted small">Notes</label>
                    <div><?= nl2br(htmlspecialchars($config['notes'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title">Actions</h6>
                <div class="d-grid gap-2">
                    <a href="<?= $app->getBaseUrl() ?>/configs/<?= $config['id'] ?>/download" class="btn btn-sm btn-success">
                        <i class="bi bi-download"></i> Download
                    </a>
                    <?php if ($app->hasRole('operator')): ?>
                    <button class="btn btn-sm btn-danger" onclick="if(confirm('Delete this configuration?')) deleteConfig(<?= $config['id'] ?>)">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteConfig(id) {
    fetch('<?= $app->getBaseUrl() ?>/configs/' + id + '/delete', {
        method: 'POST'
    }).then(() => {
        window.location.href = '<?= $app->getBaseUrl() ?>/devices/<?= $config['device_id'] ?>';
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
