<?php
$app = NetInventory\App::getInstance();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center text-white">
            <h1 style="font-size: 8rem; font-weight: bold;">404</h1>
            <h2 class="mb-4">Page Not Found</h2>
            <p class="lead mb-4">The page you are looking for does not exist.</p>
            <a href="<?= $app->getBaseUrl() ?>/" class="btn btn-light btn-lg">
                <i class="bi bi-house"></i> Go to Home
            </a>
        </div>
    </div>
</body>
</html>
