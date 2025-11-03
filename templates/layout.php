<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Net Inventory System' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link href="<?= $app->getBaseUrl() ?>/assets/css/app.css" rel="stylesheet">
</head>
<body>
    <?php if ($app->isAuthenticated()): ?>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= $app->getBaseUrl() ?>/">
                <i class="bi bi-hdd-network"></i> Net Inventory
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $app->getBaseUrl() ?>/">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $app->getBaseUrl() ?>/devices">
                            <i class="bi bi-router"></i> Devices
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $app->getBaseUrl() ?>/ip">
                            <i class="bi bi-hdd-network"></i> IP Management
                        </a>
                    </li>
                    <?php if ($app->hasRole('operator')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $app->getBaseUrl() ?>/devices/create">
                            <i class="bi bi-plus-circle"></i> Add Device
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Search -->
                <form class="d-flex me-3" action="<?= $app->getBaseUrl() ?>/search" method="GET">
                    <input class="form-control me-2" type="search" name="q" placeholder="Search..." value="<?= $_GET['q'] ?? '' ?>">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
                
                <!-- User Menu -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <?= htmlspecialchars($app->getCurrentUser()['username']) ?>
                            <span class="badge bg-secondary"><?= $app->getCurrentUser()['role'] ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= $app->getBaseUrl() ?>/logout">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="container-fluid py-4">
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); endif; ?>
        
        <!-- Page Content -->
        <?= $content ?? '' ?>
    </main>
    
    <!-- Footer -->
    <footer class="bg-light text-center py-3 mt-5">
        <div class="container">
            <p class="text-muted mb-0">
                Net Inventory System v1.0.0
                <?php if ($app->getConfig('debug')): ?>
                <span class="badge bg-danger">⚠️ VULNERABLE MODE - FOR TESTING ONLY</span>
                <?php endif; ?>
            </p>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= $app->getBaseUrl() ?>/assets/js/app.js"></script>
</body>
</html>
