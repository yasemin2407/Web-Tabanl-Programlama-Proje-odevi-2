<?php
include 'baglan.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['oturum']) || $_SESSION['rol'] !== 'engineer') {
    header("Location: login.php"); exit();
}

if (isset($_GET['aksiyon']) && isset($_GET['tarla_id'])) {
    $tarla_id = intval($_GET['tarla_id']);
    $yeni_durum = ($_GET['aksiyon'] === 'ac') ? 1 : 0;

    mysqli_query($baglanti, "UPDATE fields_irrigation SET last_irrigation_duration = $yeni_durum WHERE id = $tarla_id");

    if($yeni_durum === 1) {
        mysqli_query($baglanti, "INSERT INTO irrigation_logs (field_id, duration_minutes, triggered_by) VALUES ($tarla_id, 15, 'manual')");
    }
    
    header("Location: sulama_onay.php"); exit();
}

$sorgu = "SELECT fi.*, u.username AS ciftci_adi FROM fields_irrigation fi INNER JOIN users u ON fi.farmer_id = u.id";
$sonuc = mysqli_query($baglanti, $sorgu);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sistem Sulama Onayları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light p-4">
<div class="container" style="max-width: 800px; margin-top: 30px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <h4 class="text-danger fw-bold m-0"><i class="fa-solid fa-faucet-drip me-2"></i>Uzaktan Vana Onay ve Kontrol Paneli</h4>
        <a href="index.php" class="btn btn-secondary btn-sm">Panele Dön</a>
    </div>

    <div class="row g-3">
        <?php if(mysqli_num_rows($sonuc) > 0): ?>
            <?php while($tarla = mysqli_fetch_assoc($sonuc)): ?>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold m-0 text-dark"><?php echo htmlspecialchars($tarla['field_name']); ?></h6>
                            <span class="badge bg-secondary small"><?php echo htmlspecialchars($tarla['ciftci_adi']); ?></span>
                        </div>
                        <p class="text-muted small mb-3">Ekili Ürün: <strong><?php echo htmlspecialchars($tarla['crop_type']); ?></strong></p>
                        
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <div>
                                <span class="small text-muted d-block">Vana Durumu:</span>
                                <?php if($tarla['last_irrigation_duration'] == 1): ?>
                                    <span class="badge bg-success">💧 SULANIYOR</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">🚫 KAPALI</span>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <?php if($tarla['last_irrigation_duration'] == 1): ?>
                                    <a href="sulama_onay.php?aksiyon=kapat&tarla_id=<?php echo $tarla['id']; ?>" class="btn btn-sm btn-outline-danger fw-bold">Vanayı Kapat</a>
                                <?php else: ?>
                                    <a href="sulama_onay.php?aksiyon=ac&tarla_id=<?php echo $tarla['id']; ?>" class="btn btn-primary btn-sm fw-bold">Vanayı Aç</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted py-4">Sistemde kontrol edilecek aktif bir tarla kaydı bulunamadı.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>