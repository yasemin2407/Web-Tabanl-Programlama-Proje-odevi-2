<?php
include 'baglan.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['oturum']) || $_SESSION['rol'] !== 'engineer') {
    header("Location: login.php"); exit();
}

error_reporting(E_ALL); ini_set('display_errors', 1);

if (!$baglanti) { die("Bağlantı hatası: " . mysqli_connect_error()); }

$mesaj = "";
$tarla_id = isset($_GET['tarla_id']) ? intval($_GET['tarla_id']) : 0;

if (isset($_POST['tarla_adi']) && isset($_POST['urun_tipi'])) {
    $tarla_adi = mysqli_real_escape_string($baglanti, trim($_POST['tarla_adi']));
    $urun_tipi = mysqli_real_escape_string($baglanti, trim($_POST['urun_tipi']));
    
    $update_sql = "UPDATE fields_irrigation SET field_name = '$tarla_adi', crop_type = '$urun_tipi' WHERE id = $tarla_id";
    
    if (mysqli_query($baglanti, $update_sql)) {
        $mesaj = "<div class='alert alert-success fw-bold'>🔄 Tarla bilgileri başarıyla güncellendi!</div>";
    } else {
        $mesaj = "<div class='alert alert-danger'>Hata: " . mysqli_error($baglanti) . "</div>";
    }
}

// Düzenlenecek tarlanın mevcut bilgilerini veritabanından çekip forma dolduralım
$sorgu = "SELECT * FROM fields_irrigation WHERE id = $tarla_id";
$sonuc = mysqli_query($baglanti, $sorgu);
$tarla = mysqli_fetch_assoc($sonuc);

if (!$tarla) {
    die("<div class='container mt-5 class=alert alert-danger'>🚨 Düzenlenecek geçerli bir tarla bulunamadı!</div>");
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tarla Düzenleme Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4 mx-auto bg-white shadow-sm" style="max-width: 500px; border-radius: 12px; margin-top: 50px!important;">
        <h3 class="text-center text-warning mb-4 fw-bold">Tarla Bilgilerini Düzenle</h3>
        
        <?php if (!empty($mesaj)) echo $mesaj; ?>

        <form action="edit_field.php?tarla_id=<?php echo $tarla_id; ?>" method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Tarla Adı:</label>
                <input type="text" name="tarla_adi" class="form-control" value="<?php echo htmlspecialchars($tarla['field_name']); ?>" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Ekili Ürün / Mahsul:</label>
                <input type="text" name="urun_tipi" class="form-control" value="<?php echo htmlspecialchars($tarla['crop_type']); ?>" required>
            </div>

            <button type="submit" class="btn btn-warning text-white w-100 py-2 fw-bold">Değişiklikleri Kaydet</button>
            
            <div class="text-center mt-3">
                <a href="view_farmer_fields.php" class="text-muted small">Listeye Geri Dön</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>