<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$current_username  = isset($_SESSION['kullanici_adi']) ? $_SESSION['kullanici_adi'] : '';
$current_user_role = isset($_SESSION['rol']) ? $_SESSION['rol'] : ''; 
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akıllı Tarım ve Sulama Otomasyonu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
        .main-content { flex: 1; }
        .navbar-custom { background-color: <?= $current_user_role == 'engineer' ? '#1d3557' : '#1b4d3e' ?>; }
        .footer-custom { background-color: #212529; color: #adb5bd; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
    </style>
</head>
<body>

<?php if (isset($_SESSION['oturum'])): ?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom p-3 shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-cpu-fill text-warning me-2"></i> Akıllı Tarım & Sulama Otomasyonu
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item">
                    <span class="navbar-text text-white me-2 fw-semibold">
                        <i class="bi bi-person-circle me-1 text-light"></i> Kullanıcı: <?= htmlspecialchars($current_username) ?> 
                        <span class="badge bg-light text-dark ms-1 small">
                            <?= $current_user_role == 'engineer' ? 'Ziraat Mühendisi' : 'Çiftçi' ?>
                        </span>
                    </span>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="bi bi-box-arrow-right me-1"></i> Güvenli Çıkış
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<div class="main-content container mb-5">