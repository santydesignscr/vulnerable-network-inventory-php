<?php
/**
 * Configuration Edit Form Template
 * 
 * ⚠️ VULNERABLE BY DESIGN - FOR EDUCATIONAL PURPOSES ONLY
 */

$app = NetInventory\App::getInstance();
$title = 'Edit Configuration - ' . htmlspecialchars($config['filename']);
ob_start();
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> 
                        Edit Configuration
                    </h4>
                    <div>
                        <a href="/net-inventory/public/configs/<?= htmlspecialchars($config['id']) ?>" 
                           class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Device Info -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Device:</strong> 
                                <a href="/net-inventory/public/devices/<?= htmlspecialchars($config['device_id']) ?>">
                                    <?= htmlspecialchars($config['hostname']) ?>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <strong>Uploaded:</strong> <?= htmlspecialchars($config['uploaded_at']) ?>
                                by <?= htmlspecialchars($config['uploaded_by_name'] ?? 'Unknown') ?>
                            </div>
                        </div>
                    </div>

                    <!-- Configuration Form -->
                    <form method="POST" action="/net-inventory/public/configs/<?= $config['id'] ?>/edit">
                        <div class="row">
                            <!-- Filename -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">
                                    Filename <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       name="filename" 
                                       placeholder="e.g., running-config.txt, startup-config.cfg"
                                       value="<?= htmlspecialchars($config['filename']) ?>"
                                       required>
                                <small class="text-muted">
                                    Enter a descriptive filename for this configuration
                                </small>
                            </div>

                            <!-- Configuration Content -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">
                                    Configuration Content <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        Paste or edit the device configuration below
                                    </small>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" 
                                                class="btn btn-outline-secondary" 
                                                onclick="document.getElementById('content').style.fontSize = '12px'">
                                            <i class="bi bi-zoom-out"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-secondary" 
                                                onclick="document.getElementById('content').style.fontSize = '14px'">
                                            <i class="bi bi-zoom-in"></i>
                                        </button>
                                    </div>
                                </div>
                                <textarea class="form-control font-monospace" 
                                          id="content"
                                          name="content" 
                                          rows="20" 
                                          style="font-size: 13px; line-height: 1.4;"
                                          required><?= htmlspecialchars($config['content']) ?></textarea>
                                <small class="text-muted">
                                    Total: <span id="char-count"><?= number_format(strlen($config['content'])) ?></span> characters,
                                    <span id="line-count"><?= number_format(substr_count($config['content'], "\n") + 1) ?></span> lines
                                </small>
                            </div>

                            <!-- Notes -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" 
                                          name="notes" 
                                          rows="3" 
                                          placeholder="Add notes about this configuration update..."><?= htmlspecialchars($config['notes'] ?? '') ?></textarea>
                                <small class="text-muted">
                                    Optional: Add notes about the changes or purpose of this configuration
                                </small>
                            </div>
                        </div>

                        <!-- Vulnerability Warning -->
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Vulnerability Note:</strong> This form is intentionally vulnerable to SQL injection 
                            attacks. Input is not sanitized for educational purposes.
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="/net-inventory/public/configs/<?= htmlspecialchars($config['id']) ?>" 
                                   class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Update Configuration
                                </button>
                            </div>
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

#content {
    tab-size: 4;
    -moz-tab-size: 4;
}

/* Code editor style */
.font-monospace {
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace !important;
}
</style>

<script>
// Update character and line count
const textarea = document.getElementById('content');
const charCount = document.getElementById('char-count');
const lineCount = document.getElementById('line-count');

textarea.addEventListener('input', function() {
    const text = this.value;
    charCount.textContent = text.length.toLocaleString();
    lineCount.textContent = (text.split('\n').length).toLocaleString();
});

// Prevent tab key from moving focus
textarea.addEventListener('keydown', function(e) {
    if (e.key === 'Tab') {
        e.preventDefault();
        const start = this.selectionStart;
        const end = this.selectionEnd;
        const value = this.value;
        
        // Insert tab character
        this.value = value.substring(0, start) + '\t' + value.substring(end);
        
        // Move cursor after the tab
        this.selectionStart = this.selectionEnd = start + 1;
    }
});

// Warn user before leaving if there are unsaved changes
let originalContent = textarea.value;
window.addEventListener('beforeunload', function(e) {
    if (textarea.value !== originalContent) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        return e.returnValue;
    }
});

// Don't warn after form submission
document.querySelector('form').addEventListener('submit', function() {
    originalContent = textarea.value;
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
