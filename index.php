<?php
// 1. OTURUM BAŞLATMA
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. MERKEZİ VERİTABANI BAĞLANTISI
include("baglan.php");

// 3. KULLANICININ KENDİ İSTEĞİYLE HESABINI SİLMESİ
if (isset($_GET['hesabimi_sil'])) {
    $kullanici_id = intval($_SESSION['user_id']);

    $sil_sql = "DELETE FROM users WHERE id = $kullanici_id";
    
    if (mysqli_query($baglanti, $sil_sql)) {
        session_destroy(); 
        header("Location: register.php?mesaj=hesap_silindi"); 
        exit();
    }
}

// 4. GİRİŞ GÜVENLİK KONTROLÜ
if (!isset($_SESSION['oturum']) || $_SESSION['oturum'] !== true) {
    header("Location: login.php");
    exit();
}

// Giriş ekranında SESSION'a aldığımız kurumsal Ad-Soyadı çekiyoruz
$oturum_sahibi_ad_soyad = $_SESSION['ad'] . " " . $_SESSION['soyad'];
$kullanici_rolu = $_SESSION['rol']; 
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akıllı Tarım Otomasyonu - Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .navbar-brand { font-weight: bold; color: #2b9348 !important; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="p-4">

<div class="container mb-4">
    <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm">
        <a class="navbar-brand text-decoration-none" href="index.php">
            <i class="fa-solid fa-seedling text-success me-2"></i>Akıllı Tarım
        </a>
        
        <div class="dropdown">
            <button class="btn btn-light border dropdown-toggle fw-bold d-flex align-items-center gap-2" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-bars text-secondary"></i> 
                <span><?php echo htmlspecialchars($oturum_sahibi_ad_soyad); ?></span>
                <span class="badge <?php echo $kullanici_rolu === 'engineer' ? 'bg-primary' : 'bg-success'; ?> small">
                    <?php echo $kullanici_rolu === 'engineer' ? 'Mühendis' : 'Çiftçi'; ?>
                </span>
            </button>
            
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="userMenu" style="border-radius: 8px;">
                <li class="px-3 py-2 text-muted small border-bottom mb-1">
                    <i class="fa-solid fa-circle-user text-secondary me-1"></i> Oturum: <?php echo $_SESSION['kullanici_adi']; ?>
                </li>
                
                <li>
                    <a class="dropdown-item py-2 text-dark small fw-bold" href="profile.php">
                        <i class="fa-solid fa-user-pen text-success me-2"></i>Profilimi Düzenle
                    </a>
                </li>
                
                <li>
                    <a class="dropdown-item py-2 text-dark small" href="logout.php">
                        <i class="fa-solid fa-right-from-bracket text-danger me-2"></i> Güvenli Çıkış
                    </a>
                </li>
                
                <li><hr class="dropdown-divider"></li>
                
                <li>
                    <a class="dropdown-item py-2 text-danger small fw-bold" href="index.php?hesabimi_sil=true" 
                       onclick="return confirm('Hesabınızı ve tarlalarınızı tamamen silmek istediğinize emin misiniz? Bu işlem geri alınamaz!');">
                        <i class="fa-solid fa-user-slash me-2"></i> Hesabımı Sil
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        
        <?php if ($kullanici_rolu === 'engineer'): ?>
            <div class="col-12 mb-4">
                <?php
                $user_id_check = intval($_SESSION['user_id']);
$comp_sorgu = mysqli_query($baglanti, "SELECT company FROM users WHERE id = '" . intval($user_id_check) . "'");
                $comp_row = mysqli_fetch_assoc($comp_sorgu);
                $calistigi_yer = isset($comp_row['company']) ? $comp_row['company'] : 'Belirtilmedi';
                ?>
                <div class="p-4 bg-primary text-white rounded-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h2><i class="fa-solid fa-user-doctor me-2"></i>Mühendis Yönetim Paneli</h2>
                            <p class="lead mb-0">Sorumlu olduğunuz çiftçileri ve tarla durumlarını buradan analiz edebilirsiniz.</p>
                        </div>
                        <div class="bg-white text-primary px-3 py-2 rounded shadow-sm fw-bold mt-2 mt-sm-0">
                            <i class="fa-solid fa-building text-warning me-2"></i>Kurum: <span class="text-dark"><?php echo htmlspecialchars($calistigi_yer); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card card-custom p-4 text-center bg-white h-100 d-flex flex-column">
                    <div class="text-primary mb-3"><i class="fa-solid fa-users fa-3x"></i></div>
                    <h4>Çiftçilerim</h4>
                    <p class="text-muted small">Sisteme kayıtlı çiftçilerinizin listesi ve detayları.</p>
                    <a href="view_farmer_fields.php" class="btn btn-primary w-100 fw-bold mt-auto">Listeyi Görüntüle</a>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card card-custom p-4 text-center bg-white h-100 d-flex flex-column">
                    <div class="text-info mb-3"><i class="fa-solid fa-chart-line fa-3x"></i></div>
                    <h4>Sensör Analizleri</h4>
                    <p class="text-muted small">Tarlalardan gelen nem ve sıcaklık grafik verileri.</p>
                    <a href="sensor_rapor.php" class="btn btn-info text-white w-100 fw-bold mt-auto">Raporları Aç</a>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card card-custom p-4 text-center bg-white h-100 d-flex flex-column">
                    <div class="text-warning mb-3"><i class="fa-solid fa-droplet fa-3x"></i></div>
                    <h4>Sulama Onayları</h4>
                    <p class="text-muted small">Kritik nem seviyesindeki tarlaların sulama istekleri.</p>
                    <a href="sulama_onay.php" class="btn btn-warning text-white w-100 fw-bold mt-auto">Talepleri İncele</a>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card card-custom p-4 text-center bg-white h-100 d-flex flex-column">
                    <div class="text-success mb-3"><i class="fa-solid fa-camera fa-3x text-success"></i></div>
                    <h4 class="text-success">Tarla Sorunları</h4>
                    <p class="text-muted small">Çiftçilerin yüklediği mahsul fotoğrafları ve teşhis talepleri.</p>
                    <a href="consult.php" class="btn btn-success w-100 fw-bold mt-auto">Fotoğrafları İncele</a>
                </div>
            </div>

        <?php else: ?>
            <div class="col-12 mb-4">
                <div class="p-4 bg-success text-white rounded-3 shadow-sm">
                    <h2><i class="fa-solid fa-wheat-awn me-2"></i>Çiftçi Takip Paneli</h2>
                    <p class="lead mb-0">Tarlanızdaki anlık sensör verilerini takip edin ve otomatik sulama sistemini yönetin.</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card p-4 shadow-sm border-0 bg-white h-100 text-center">
                        <div class="fs-1 text-success mb-2"><i class="fa-solid fa-thermometer"></i></div>
                        <h5 class="fw-bold text-dark">Anlık Sensör Verileri</h5>
                        <p class="text-muted small mb-4">Toprak Nemi: %45 | Hava Sıcaklığı: 26°C</p>
                        <a href="tarlalarim.php" class="btn btn-success w-100 fw-bold mt-auto">Tarlamı İncele</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-4 shadow-sm border-0 bg-white h-100 text-center">
                        <div class="fs-1 text-danger mb-2"><i class="fa-solid fa-faucet-drip"></i></div>
                        <h5 class="fw-bold text-dark">Manuel Sulama Sistemi</h5>
                        <p class="text-muted small mb-4">Vanalara anlık sinyal göndererek sulamayı başlat/durdur.</p>
                        <a href="sulama_kontrol.php" class="btn btn-danger w-100 fw-bold mt-auto">Sistemi Çalıştır</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-4 shadow-sm border-0 bg-white h-100 text-center">
                        <div class="fs-1 text-primary mb-2"><i class="fa-solid fa-camera text-primary"></i></div>
                        <h5 class="fw-bold text-dark">Ziraat Danışmanlığı</h5>
                        <p class="text-muted small mb-4">Mahsulünüzün fotoğrafını yükleyin, mühendisinizden anında teşhis alın.</p>
                        <a href="consult.php" class="btn btn-primary w-100 fw-bold mt-auto">Sorun Bildir / Fotoğraf Yükle</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>