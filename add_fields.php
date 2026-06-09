<?php
include 'baglan.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['oturum']) || $_SESSION['rol'] !== 'farmer') {
    header("Location: login.php"); exit();
}

$ciftci_id = $_SESSION['user_id'];
$mesaj = "";

if (isset($_POST['tarla_adi']) && isset($_POST['urun_tipi'])) {
    $tarla_adi = mysqli_real_escape_string($baglanti, $_POST['tarla_adi']);
    $urun_tipi = mysqli_real_escape_string($baglanti, $_POST['urun_tipi']);
    
    $ekle_sql = "INSERT INTO fields_irrigation (farmer_id, field_name, crop_type, irrigation_status) 
                 VALUES ($ciftci_id, '$tarla_adi', '$urun_tipi', 0)";
                 
    if (mysqli_query($baglanti, $ekle_sql)) {
        $mesaj = "<div class='alert alert-success py-2'> Yeni tarla başarıyla eklendi!</div>";
    }
}

// Çiftçinin kendi tarlalarının listelenmesi
$sorgu = "SELECT * FROM fields_irrigation WHERE farmer_id = $ciftci_id";
$sonuc = mysqli_query($baglanti, $sorgu);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tarlalarım ve Ekleme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container" style="max-width: 700px; margin-top: 30px;">
    
    <?php if(!empty($mesaj)) echo $mesaj; ?>

    <div class="card p-4 mb-4 shadow-sm border-0 bg-white">
        <h5 class="fw-bold text-success mb-3">Yeni Tarla Ekle</h5>
        <form action="add_fields.php" method="POST">
            <div class="mb-3">
                <input type="text" name="tarla_adi" class="form-control" placeholder="Tarla Adı (Örn: Güney Tarlası)" required>
            </div>
            <div class="mb-3">
                <input type="text" name="urun_tipi" class="form-control" placeholder="Ekili Ürün (Örn: Domates)" required>
            </div>
            <button type="submit" class="btn btn-success w-100 fw-bold">Tarlayı Kaydet (Veri Ekle)</button>
        </form>
    </div>

    <div class="card p-4 shadow-sm border-0 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-dark m-0">Kayıtlı Tarlalarım</h5>
            <a href="index.php" class="btn btn-sm btn-secondary">Panele Dön</a>
        </div>
        <ul class="list-group">
            <?php if(mysqli_num_rows($sonuc) > 0): ?>
                <?php while($tarla = mysqli_fetch_assoc($sonuc)): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <strong><?php echo $tarla['field_name']; ?></strong>
                        <span class="badge bg-primary rounded-pill"><?php echo $tarla['crop_type']; ?></span>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li class="list-group-item text-muted text-center">Henüz ekli bir tarlanız yok.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>
</body>
</html>