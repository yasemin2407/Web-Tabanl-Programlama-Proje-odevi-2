<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['oturum']) || $_SESSION['rol'] !== 'engineer') {
    header("Location: login.php"); exit();
}

include("baglan.php");

if (isset($_GET['sil_id'])) {
    $sil_id = intval($_GET['sil_id']);
    $sil_sql = "DELETE FROM farmer_consultations WHERE farmer_id = $sil_id";
    mysqli_query($baglanti, $sil_sql);
    header("Location: view_farmer_fields.php"); exit();
}

$engineer_id = intval($_SESSION['user_id']);

$sorgu = "SELECT 
            u.id AS ciftci_id,
            u.username AS ciftci_adi,
            u.first_name,
            u.last_name,
            fi.field_name,
            fi.crop_type
          FROM users u
          LEFT JOIN fields_irrigation fi ON u.id = fi.farmer_id
          WHERE u.role = 'farmer'
          ORDER BY u.id DESC";

$sonuc = mysqli_query($baglanti, $sorgu);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sorumlu Çiftçilerim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light p-4">
<div class="container bg-white p-4 rounded shadow-sm" style="max-width: 950px; margin-top: 40px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-success fw-bold m-0"><i class="fa-solid fa-users-gear me-2"></i>Sorumlu Olduğunuz Çiftçiler ve Tarlalar</h4>
        <a href="index.php" class="btn btn-secondary btn-sm">Panele Dön</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Kullanıcı Adı</th>
                    <th>Adı Soyadı</th>
                    <th>Tarla Adı</th>
                    <th>Mahsul Türü</th>
                    <th class="text-center">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if($sonuc && mysqli_num_rows($sonuc) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($sonuc)): ?>
                        <tr>
                            <td><strong>#<?php echo $row['ciftci_id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($row['ciftci_adi']); ?></td>
                            <td><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></td>
                            <td>
                                <?php echo !empty($row['field_name']) ? htmlspecialchars($row['field_name']) : '<span class="text-muted small"><em>Tarla Kaydı Yok</em></span>'; ?>
                            </td>
                            <td>
                                <?php echo !empty($row['crop_type']) ? '<span class="badge bg-success">'.htmlspecialchars($row['crop_type']).'</span>' : '<span class="text-muted small">-</span>'; ?>
                            </td>
                            <td class="text-center">
                                <a href="view_farmer_fields.php?sil_id=<?php echo $row['ciftci_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bu çiftçinin danışma geçmişini temizlemek istediğinize emin misiniz?');">
                                    <i class="fa-solid fa-trash-can me-1"></i> Geçmişi Temizle
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">Sisteme kayıtlı herhangi bir çiftçi bulunamadı.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>