<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['oturum'])) { header("Location: login.php"); exit(); }

include("baglan.php");

$user_id = intval($_SESSION['user_id']);
$mesaj = "";

if (isset($_POST['tarla_ekle'])) {
    $field_name = mysqli_real_escape_string($baglanti, trim($_POST['field_name']));
    
    if(!empty($field_name)) {
        $ekle_sql_canli = "INSERT INTO fields_irrigation (farmer_id, field_name, crop_type, soil_moisture, irrigation_duration, last_irrigation_duration) 
                           VALUES ($user_id, '$field_name', 'Belirtilmedi', 45, 0, 0)";

        if (mysqli_query($baglanti, $ekle_sql_canli)) {
            $mesaj = "<div class='alert alert-success py-2 small fw-bold'>🌾 Yeni tarla başarıyla sisteme eklendi!</div>";
        } else {
            $mesaj = "<div class='alert alert-danger py-2 small fw-bold'>❌ Tarla eklenirken hata oluştu: " . mysqli_error($baglanti) . "</div>";
        }
    }
}

if (isset($_POST['tarla_guncelle'])) {
    $field_id = intval($_POST['field_id']);
    $new_field_name = mysqli_real_escape_string($baglanti, trim($_POST['field_name']));
    
    if(!empty($new_field_name)) {
        $guncelle_sql = "UPDATE fields_irrigation SET field_name = '$new_field_name' WHERE id = $field_id AND farmer_id = $user_id";
        if (mysqli_query($baglanti, $guncelle_sql)) {
            $mesaj = "<div class='alert alert-info py-2 small fw-bold'>🔄 Tarla bilgileri başarıyla güncellendi!</div>";
        }
    }
}

if (isset($_GET['sil'])) {
    $field_id = intval($_GET['sil']);
    $sil_sql = "DELETE FROM fields_irrigation WHERE id = $field_id AND farmer_id = $user_id";
    if (mysqli_query($baglanti, $sil_sql)) {
        $mesaj = "<div class='alert alert-danger py-2 small fw-bold'>🗑️ Tarla sistemden kaldırıldı.</div>";
    }
}

$sorgu = "SELECT * FROM fields_irrigation WHERE farmer_id = $user_id ORDER BY id DESC";
$sonuc = mysqli_query($baglanti, $sorgu);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tarlalarım ve Sensör Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light p-4">
<div class="container" style="max-width: 900px; margin-top: 20px;">

    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <h4 class="text-success fw-bold m-0"><i class="fa-solid fa-wheat-awn me-2"></i>Tarla Yönetim Merkezi</h4>
        <a href="index.php" class="btn btn-secondary btn-sm">Panele Dön</a>
    </div>

    <?php if(!empty($mesaj)) echo $mesaj; ?>

    <div class="card p-3 mb-4 shadow-sm border-0 bg-white">
        <form action="tarlalarim.php" method="POST" class="row g-2 align-items-center">
            <div class="col-md-9">
                <input type="text" name="field_name" class="form-control" placeholder="Örn: Kuzey Domates Tarlası, Yol Kenarı Yonca..." required>
            </div>
            <div class="col-md-3">
                <button type="submit" name="tarla_ekle" class="btn btn-success w-100 fw-bold"><i class="fa-solid fa-plus me-1"></i>Yeni Tarla Ekle</button>
            </div>
        </form>
    </div>

    <div class="card p-3 shadow-sm border-0 bg-white">
        <h5 class="fw-bold mb-3 text-dark">Kayıtlı Tarlalarınız ve Anlık Veriler</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tarla Adı</th>
                        <th>Toprak Nemi</th>
                        <th>Sıcaklık</th>
                        <th>Vana Durumu</th>
                        <th class="text-center">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($sonuc) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($sonuc)): ?>
                            <?php 
                            $nem = isset($row['moisture_level']) ? $row['moisture_level'] : (isset($row['moisture']) ? $row['moisture'] : 45);
                            $sicaklik = isset($row['temperature']) ? $row['temperature'] : (isset($row['temp']) ? $row['temp'] : 26);
                            $vana = isset($row['valve_status']) ? $row['valve_status'] : 0;
                            ?>
                            <tr>
                                <td class="fw-bold text-secondary"><?php echo htmlspecialchars($row['field_name']); ?></td>
                                <td><span class="badge bg-info text-dark">%<?php echo $nem; ?></span></td>
                                <td><?php echo $sicaklik; ?>°C</td>
                                <td>
                                    <?php echo $vana == 1 ? 
                                        '<span class="badge bg-success"><i class="fa-solid fa-droplet fa-spin me-1"></i> Açık</span>' : 
                                        '<span class="badge bg-secondary">Kapalı</span>'; ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editModal" 
                                            data-id="<?php echo $row['id']; ?>" 
                                            data-name="<?php echo htmlspecialchars($row['field_name']); ?>">
                                        <i class="fa-solid fa-pen-to-square"></i> Düzenle
                                    </button>
                                    
                                    <a href="tarlalarim.php?sil=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Bu tarlayı silmek istediğinize emin misiniz?');">
                                        <i class="fa-solid fa-trash"></i> Sil
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Sisteme kayıtlı tarlanız bulunmuyor. Yukarıdan ekleyebilirsiniz.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold" id="editModalLabel"><i class="fa-solid fa-pen-to-square me-2"></i>Tarla Adını Güncelle</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="tarlalarim.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="field_id" id="modal_field_id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Yeni Tarla Adı</label>
                        <input type="text" name="field_name" id="modal_field_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-sm btn-secondary fw-bold" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" name="tarla_guncelle" class="btn btn-sm btn-primary fw-bold">Değişiklikleri Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const fieldId = button.getAttribute('data-id');
            const fieldName = button.getAttribute('data-name');
            
            const modalFieldId = editModal.querySelector('#modal_field_id');
            const modalFieldName = editModal.querySelector('#modal_field_name');
            
            modalFieldId.value = fieldId;
            modalFieldName.value = fieldName;
        });
    }
</script>
</body>
</html>