<?php
include 'baglan.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['oturum']) || $_SESSION['rol'] !== 'farmer') {
    header("Location: login.php"); exit();
}

$ciftci_id = $_SESSION['user_id'];
$mesaj = "";

if (isset($_POST['sure_ayarla']) && isset($_POST['tarla_id'])) {
    $tarla_id = intval($_POST['tarla_id']);
    $dakika = intval($_POST['sulama_dakika']);
    
    $sure_sql = "UPDATE fields_irrigation SET irrigation_duration = $dakika WHERE id = $tarla_id AND farmer_id = $ciftci_id";
    if (mysqli_query($baglanti, $sure_sql)) {
        $mesaj = "<div class='alert alert-success py-2 small fw-bold'>⏱️ Sulama süresi $dakika dakika olarak güncellendi!</div>";
    }
}

if (isset($_GET['aksiyon']) && isset($_GET['id'])) {
    $tarla_id = intval($_GET['id']);
    $durum = ($_GET['aksiyon'] === 'ac') ? 1 : 0;
    
    $update_sql = "UPDATE fields_irrigation SET last_irrigation_duration = $durum WHERE id = $tarla_id AND farmer_id = $ciftci_id";
    mysqli_query($baglanti, $update_sql);
    header("Location: sulama_kontrol.php"); exit();
}

$sorgu = "SELECT * FROM fields_irrigation WHERE farmer_id = $ciftci_id";
$sonuc = mysqli_query($baglanti, $sorgu);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Manuel Sulama Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light p-4">
<div class="container" style="max-width: 750px; margin-top: 30px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <h4 class="text-danger fw-bold m-0"><i class="fa-solid fa-faucet-drip me-2"></i>Vana ve Süre Yönetimi</h4>
        <a href="index.php" class="btn btn-secondary btn-sm">Panele Dön</a>
    </div>

    <?php if(!empty($mesaj)) echo $mesaj; ?>

    <div class="row g-3">
        <?php if(mysqli_num_rows($sonuc) > 0): ?>
            <?php while($tarla = mysqli_fetch_assoc($sonuc)): ?>
                <div class="col-md-12">
                    <div class="card p-4 border-0 shadow-sm bg-white mb-2">
                        
                        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($tarla['field_name']); ?></h5>
                                <span class="badge bg-light text-secondary border">🌱 <?php echo htmlspecialchars($tarla['crop_type']); ?></span>
                            </div>
                            <div>
                                <?php if($tarla['last_irrigation_duration'] == 1): ?>
                                    <span class="badge bg-success py-2 px-3 fs-6">💧 SULANIYOR</span>
                                <?php else: ?>
                                    <span class="badge bg-danger py-2 px-3 fs-6">🚫 VANA KAPALI</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-md-6 border-end">
                                <form action="sulama_kontrol.php" method="POST" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="tarla_id" value="<?php echo $tarla['id']; ?>">
                                    <div style="width: 130px;">
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="sulama_dakika" class="form-control" value="<?php echo $tarla['irrigation_duration']; ?>" min="1" max="120" required>
                                            <span class="input-group-text bg-light small">dk</span>
                                        </div>
                                    </div>
                                    <button type="submit" name="sure_ayarla" class="btn btn-sm btn-outline-secondary fw-bold">Süreyi Ayarla</button>
                                </form>
                            </div>

                            <div class="col-md-6 ps-md-4">
                                <?php if($tarla['last_irrigation_duration'] == 1 && $tarla['irrigation_duration'] > 0): ?>
                                    <div class="d-flex align-items-center gap-2 text-primary fw-bold">
                                        <i class="fa-solid fa-clock-rotate-left fa-spin fs-5"></i>
                                        <span>Kalan Süre:</span>
                                        <span class="fs-5 text-dark countdown-timer" data-minutes="<?php echo $tarla['irrigation_duration']; ?>">--:--</span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small italic"><i class="fa-solid fa-info me-1"></i> Vana açıldığında sayaç başlayacaktır.</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end border-top pt-3">
                            <?php if($tarla['last_irrigation_duration'] == 1): ?>
                                <a href="sulama_kontrol.php?aksiyon=kapat&id=<?php echo $tarla['id']; ?>" class="btn btn-danger px-4 fw-bold shadow-sm">Vanayı Kapat</a>
                            <?php else: ?>
                                <a href="sulama_kontrol.php?aksiyon=ac&id=<?php echo $tarla['id']; ?>" class="btn btn-success px-4 fw-bold shadow-sm">Vanayı Aç</a>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted py-4">Sistemde tarlanız bulunmuyor.</div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const timers = document.querySelectorAll('.countdown-timer');
    timers.forEach(timer => {
        let minutes = parseInt(timer.getAttribute('data-minutes'));
        let totalSeconds = minutes * 60;

        function updateTimer() {
            let currentMinutes = Math.floor(totalSeconds / 60);
            let currentSeconds = totalSeconds % 60;

            currentMinutes = currentMinutes < 10 ? "0" + currentMinutes : currentMinutes;
            currentSeconds = currentSeconds < 10 ? "0" + currentSeconds : currentSeconds;

            timer.textContent = currentMinutes + ":" + currentSeconds;

            if (totalSeconds <= 0) {
                clearInterval(interval);
                timer.textContent = "Süre Doldu!";
                timer.classList.add('text-danger');
            } else {
                totalSeconds--;
            }
        }
        updateTimer();
        const interval = setInterval(updateTimer, 1000);
    });
});
</script>
</body>
</html>