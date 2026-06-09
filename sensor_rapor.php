<?php
include 'baglan.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['oturum']) || $_SESSION['rol'] !== 'engineer') {
    header("Location: login.php"); exit();
}

$sorgu = "SELECT sl.*, fi.field_name, u.username AS ciftci_adi 
          FROM sensor_logs sl
          INNER JOIN fields_irrigation fi ON sl.field_id = fi.id
          INNER JOIN users u ON fi.farmer_id = u.id
          ORDER BY sl.reading_time DESC LIMIT 15";
$sonuc = mysqli_query($baglanti, $sorgu);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sensör Analiz Raporları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container bg-white p-4 rounded shadow-sm" style="max-width: 850px; margin-top: 40px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-success fw-bold">📊 Anlık Sensör Analiz Raporları</h4>
        <a href="index.php" class="btn btn-secondary btn-sm">Panele Dön</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-success text-dark">
                <tr>
                    <th>Zaman Verisi</th>
                    <th>Çiftçi</th>
                    <th>Tarla Adı</th>
                    <th>Toprak Nemi</th>
                    <th>Sıcaklık</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($sonuc) > 0): ?>
                    <?php while($satir = mysqli_fetch_assoc($sonuc)): ?>
                        <tr>
                            <td class="small text-muted"><?php echo $satir['reading_time']; ?></td>
                            <td><strong><?php echo htmlspecialchars($satir['ciftci_adi']); ?></strong></td>
                            <td><?php echo htmlspecialchars($satir['field_name']); ?></td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $satir['moisture_value']; ?>%;">
                                        %<?php echo $satir['moisture_value']; ?>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-warning text-dark"><?php echo $satir['temperature_value']; ?>°C</span></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">Henüz kaydedilmiş bir sensör logu bulunmuyor.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>