<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['oturum'])) { header("Location: login.php"); exit(); }

include("baglan.php");

$user_id = intval($_SESSION['user_id']);
$rol = $_SESSION['rol'];
$mesaj = "";

// Çiftçi formu gönderdiğinde çalışan kısım
if ($rol === 'farmer' && isset($_POST['gonder'])) {
    $note = mysqli_real_escape_string($baglanti, trim($_POST['note']));
    $compressed_image = isset($_POST['compressed_image_data']) ? trim($_POST['compressed_image_data']) : '';
    
    if (!empty($compressed_image) && strpos($compressed_image, 'data:image') === 0) {
        $compressed_image = mysqli_real_escape_string($baglanti, $compressed_image);
        
        $ekle_sql = "INSERT INTO farmer_consultations (farmer_id, image_path, notes, status) 
                     VALUES ($user_id, '$compressed_image', '$note', 'Beklemede')";
                     
        if (mysqli_query($baglanti, $ekle_sql)) {
            $mesaj = "<div class='alert alert-success py-2 small fw-bold'> Fotoğraf başarıyla optimize edildi ve mühendisinize iletildi!</div>";
        } else {
            $mesaj = "<div class='alert alert-danger py-2 small'> Veritabanı Hatası: " . mysqli_error($baglanti) . "</div>";
        }
    } else {
        $mesaj = "<div class='alert alert-danger py-2 small'> Hata: Fotoğraf işlenirken bir sorun oluştu, lütfen tekrar deneyin.</div>";
    }
}

if ($rol === 'engineer' && isset($_POST['cevap_gonder'])) {
    $consult_id = intval($_POST['consult_id']);
    $response = mysqli_real_escape_string($baglanti, trim($_POST['engineer_response']));
    
    if (!empty($response)) {
        $cevap_sql = "UPDATE farmer_consultations SET engineer_response = '$response', status = 'Cevaplandı' WHERE id = $consult_id";
        if (mysqli_query($baglanti, $cevap_sql)) {
            $mesaj = "<div class='alert alert-success py-2 small fw-bold'> Cevabınız ve reçeteniz çiftçiye başarıyla iletildi!</div>";
        } else {
            $mesaj = "<div class='alert alert-danger py-2 small'>Hata: " . mysqli_error($baglanti) . "</div>";
        }
    }
}

if ($rol === 'engineer') {
    $sorgu = "SELECT u.id AS farmer_id, u.first_name, u.last_name, 
                     fc.id AS consult_id, fc.image_path, fc.notes, fc.engineer_response, fc.status, fc.created_at
              FROM users u
              LEFT JOIN farmer_consultations fc ON u.id = fc.farmer_id
              WHERE u.role = 'farmer'
              ORDER BY fc.id DESC";
} else {
    $sorgu = "SELECT * FROM farmer_consultations WHERE farmer_id = $user_id ORDER BY id DESC";
}

// İki rol için de hazırlanan $sorgu değişkeni burada güvenle çalıştırılır
$sonuc = mysqli_query($baglanti, $sorgu);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ziraat Danışmanlığı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light p-4">
<div class="container" style="max-width: 850px; margin-top: 20px;">

    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <h4 class="text-primary fw-bold m-0">
            <i class="fa-solid fa-camera text-primary me-2"></i>
            <?php echo $rol === 'engineer' ? 'Gelen Mahsul Fotoğrafları & Cevaplama' : 'Ziraat Danışmanlığı'; ?>
        </h4>
        <a href="index.php" class="btn btn-secondary btn-sm">Panele Dön</a>
    </div>

    <?php if(!empty($mesaj)) echo $mesaj; ?>

    <?php if($rol === 'farmer'): ?>
        <div class="card p-4 mb-4 shadow-sm border-0 bg-white">
            <h5 class="fw-bold mb-3 text-dark">Yeni Sorun Bildir / Fotoğraf Gönder</h5>
            <form action="consult.php" method="POST" id="farmerUploadForm">
                <input type="hidden" name="gonder" value="1">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Mahsulün / Tarlanın Fotoğrafı</label>
                    <input type="file" id="imageInput" class="form-control" accept="image/*" required>
                    <input type="hidden" name="compressed_image_data" id="compressedImageData">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Mühendisinize İletmek İstediğiniz Not</label>
                    <textarea name="note" class="form-control" rows="3" placeholder="Örn: Yapraklarda sararma var, ne yapmalıyım?" required></textarea>
                </div>
                <button type="submit" id="submitBtn" class="btn btn-primary w-100 fw-bold">
                    <i class="fa-solid fa-paper-plane me-1"></i>Mühendise Gönder
                </button>
            </form>
        </div>
    <?php endif; ?>

    <div class="card p-4 shadow-sm border-0 bg-white">
        <h5 class="fw-bold mb-3 text-dark">
            <?php echo $rol === 'engineer' ? 'Sorumlu Olduğunuz Çiftçilerden Gelen Fotoğraflar' : 'Geçmiş Bildirimleriniz'; ?>
        </h5>
        <div class="row g-3">
            <?php if($sonuc && mysqli_num_rows($sonuc) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($sonuc)): ?>
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light h-100 d-flex flex-column">
                            
                            <div class="text-center mb-2 bg-white rounded p-2" style="height: 180px; display: flex; align-items: center; justify-content: center; width: 100%; border: 1px solid #dee2e6;">
                                <?php if(!empty($row['image_path'])): ?>
                                    <?php if(strpos($row['image_path'], 'data:image') === 0): ?>
                                        <img src="<?php echo $row['image_path']; ?>" class="img-fluid rounded" style="max-height: 100%; object-fit: contain;">
                                    <?php else: ?>
                                        <img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/<?php echo ltrim($row['image_path'], './'); ?>" class="img-fluid rounded" style="max-height: 100%; object-fit: contain;">
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted small p-2">
                                        <i class="fa-solid fa-user-clock fa-2x mb-2 text-primary"></i>
                                        <br><span class="fw-bold text-secondary" style="font-size: 11px;">Henüz Fotoğraf/Sorun Bildirmedi</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($rol === 'engineer'): ?>
                                <p class="small mb-1 fw-bold text-success"><i class="fa-solid fa-user me-1"></i> Sorumlu Olduğunuz Çiftçi:</p>
                                <p class="small text-dark mb-2 fw-bold"><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></p>
                            <?php endif; ?>

                            <p class="small text-dark mb-1 fw-bold">Çiftçi Notu:</p>
                            <p class="small text-muted mb-2"><?php echo !empty($row['notes']) ? htmlspecialchars($row['notes']) : 'Henüz bir not bırakılmamış.'; ?></p>
                            
                            <?php if($rol === 'engineer'): ?>
                                <?php if(!empty($row['consult_id'])): ?>
                                    <form action="consult.php" method="POST" class="mt-auto border-top pt-2">
                                        <input type="hidden" name="consult_id" value="<?php echo $row['consult_id']; ?>">
                                        <div class="mb-2">
                                            <label class="form-label small fw-bold text-primary">Uzman Reçetesi / Cevabınız</label>
                                            <textarea name="engineer_response" class="form-control form-control-sm" rows="2" placeholder="İlaçlama veya sulama önerinizi yazın..." required><?php echo isset($row['engineer_response']) ? htmlspecialchars($row['engineer_response']) : ''; ?></textarea>
                                        </div>
                                        <button type="submit" name="cevap_gonder" class="btn btn-sm btn-success w-100 fw-bold">
                                            <i class="fa-solid fa-reply me-1"></i> Cevabı Gönder / Güncelle
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="mt-auto border-top pt-2 text-center text-muted small italic p-2 bg-white rounded border">
                                        <i class="fa-solid fa-hourglass-start me-1 text-warning"></i> Çiftçinin soru sorması bekleniyor.
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if($rol === 'farmer'): ?>
                                <div class="mt-auto border-top pt-2 bg-white p-2 rounded">
                                    <p class="small mb-1 fw-bold text-primary"><i class="fa-solid fa-user-doctor me-1"></i> Mühendisin Cevabı:</p>
                                    <p class="small text-secondary m-0 font-italic">
                                        <?php echo !empty($row['engineer_response']) ? htmlspecialchars($row['engineer_response']) : '<span class="text-muted small">Mühendisiniz henüz yanıtlamadı.</span>'; ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                <span class="badge <?php echo ($row['status'] ?? '') == 'Beklemede' ? 'bg-warning text-dark' : (($row['status'] ?? '') == 'Cevaplandı' ? 'bg-success' : 'bg-secondary'); ?> small">
                                    <?php echo $row['status'] ?? 'Aktif Takipte'; ?>
                                </span>
                                <small class="text-muted" style="font-size: 11px;"><?php echo !empty($row['created_at']) ? date("d.m.Y H:i", strtotime($row['created_at'])) : 'Bağlantı Aktif'; ?></small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted py-3">
                    <i class="fa-solid fa-users-slash fa-2x mb-2 text-secondary"></i>
                    <br>Sistemde henüz kayıtlı aktif bir çiftçi bulunmuyor.
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php if($rol === 'farmer'): ?>
<script>
document.getElementById('farmerUploadForm').addEventListener('submit', function(e) {
    const imageInput = document.getElementById('imageInput');
    const submitBtn = document.getElementById('submitBtn');
    const hiddenData = document.getElementById('compressedImageData');
    
    if (hiddenData.value && hiddenData.value.startsWith('data:image')) {
        return; 
    }
    
    e.preventDefault();
    
    if (imageInput.files.length === 0 || !imageInput.files[0]) {
        alert('Lütfen bir fotoğraf seçin.');
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Fotoğraf Optimize Ediliyor...';
    
    const file = imageInput.files[0];
    const reader = new FileReader();
    
    reader.onload = function(event) {
        const img = new Image();
        img.onload = function() {
            const canvas = document.createElement('canvas');
            const max_size = 600;
            let width = img.width;
            let height = img.height;
            
            if (width > height) {
                if (width > max_size) {
                    height *= max_size / width;
                    width = max_size;
                }
            } else {
                if (height > max_size) {
                    width *= max_size / height;
                    height = max_size;
                }
            }
            
            canvas.width = width;
            canvas.height = height;
            
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);
            
            const compressedDataUrl = canvas.toDataURL('image/jpeg', 0.6);
            
            hiddenData.value = compressedDataUrl;
            document.getElementById('farmerUploadForm').submit();
        };
        img.src = event.target.result;
    };
    reader.readAsDataURL(file);
});
</script>
<?php endif; ?>

</body>
</html>