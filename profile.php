<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['oturum'])) { header("Location: login.php"); exit(); }

include("baglan.php");

$user_id = intval($_SESSION['user_id']);
$rol = $_SESSION['rol'];
$mesaj = "";
$sorgu = mysqli_query($baglanti, "SELECT * FROM users WHERE id = $user_id");
$kullanici = mysqli_fetch_assoc($sorgu);

$mevcut_muhendis_id = 0;
if ($rol === 'farmer') {
    $farmer_extra = mysqli_query($baglanti, "SELECT engineer_id FROM farmers WHERE user_id = $user_id");
    if ($f_row = mysqli_fetch_assoc($farmer_extra)) {
        $mevcut_muhendis_id = intval($f_row['engineer_id']);
    }
    $muhendisler_listesi = mysqli_query($baglanti, "SELECT id, first_name, last_name, company FROM users WHERE role = 'engineer'");
}

if (isset($_POST['guncelle'])) {
    $first_name = mysqli_real_escape_string($baglanti, trim($_POST['first_name']));
    $last_name = mysqli_real_escape_string($baglanti, trim($_POST['last_name']));
    $username = mysqli_real_escape_string($baglanti, trim($_POST['username']));
    $new_password = trim($_POST['password']);
    
    $kontrol = mysqli_query($baglanti, "SELECT id FROM users WHERE username = '$username' AND id != $user_id");
    
    if (mysqli_num_rows($kontrol) > 0) {
        $mesaj = "<div class='alert alert-danger py-2 small fw-bold'>! Bu kullanıcı adı zaten başka bir üye tarafından kullanılıyor !</div>";
    } else {
        if (!empty($new_password)) {
            $sifreli_sifre = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET first_name='$first_name', last_name='$last_name', username='$username', password='$sifreli_sifre' ";
        } else {
            $update_sql = "UPDATE users SET first_name='$first_name', last_name='$last_name', username='$username' ";
        }

        if ($rol === 'engineer') {
            $company = isset($_POST['company']) ? mysqli_real_escape_string($baglanti, trim($_POST['company'])) : 'Belirtilmedi';
            $update_sql .= ", company='$company' ";
        }
        
        $update_sql .= " WHERE id = $user_id";

        if (mysqli_query($baglanti, $update_sql)) {

            if ($rol === 'farmer') {
                $chosen_engineer = intval($_POST['engineer_id']);
                if ($chosen_engineer > 0) {
                    mysqli_query($baglanti, "UPDATE farmers SET engineer_id = $chosen_engineer WHERE user_id = $user_id");
                } else {
                    mysqli_query($baglanti, "UPDATE farmers SET engineer_id = NULL WHERE user_id = $user_id");
                }
                $mevcut_muhendis_id = $chosen_engineer; // Ekranı tazelemek için
            }

            $_SESSION['kullanici_adi'] = $username;
            $_SESSION['ad'] = mb_convert_case($first_name, MB_CASE_TITLE, "UTF-8");
            $_SESSION['soyad'] = mb_convert_case($last_name, MB_CASE_TITLE, "UTF-8");
            
            $mesaj = "<div class='alert alert-success py-2 small fw-bold'> Profil ve rol bilgileriniz başarıyla güncellendi!</div>";

            $sorgu = mysqli_query($baglanti, "SELECT * FROM users WHERE id = $user_id");
            $kullanici = mysqli_fetch_assoc($sorgu);
        } else {
            $mesaj = "<div class='alert alert-danger py-2 small'>Hata: " . mysqli_error($baglanti) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Profil Ayarları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light p-4">
<div class="container" style="max-width: 500px; margin-top: 30px;">

    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <h5 class="text-dark fw-bold m-0"><i class="fa-solid fa-user-gear text-success me-2"></i>Profil Bilgilerini Düzenle</h5>
        <a href="index.php" class="btn btn-secondary btn-sm">Panele Dön</a>
    </div>

    <?php if(!empty($mesaj)) echo $mesaj; ?>

    <div class="card p-4 shadow-sm border-0 bg-white">
        <form action="profile.php" method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold text-muted">Adınız</label>
                    <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($kullanici['first_name']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold text-muted">Soyadınız</label>
                    <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($kullanici['last_name']); ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">Kullanıcı Adı</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-user small"></i></span>
                    <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($kullanici['username']); ?>" required>
                </div>
            </div>

            <?php if($rol === 'engineer'): ?>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-success">Çalıştığınız Kurum / Firma</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-success"><i class="fa-solid fa-building-user small"></i></span>
                        <input type="text" name="company" class="form-control fw-bold text-secondary" value="<?php echo htmlspecialchars(!empty($kullanici['company']) ? $kullanici['company'] : 'Belirtilmedi'); ?>" placeholder="Örn: Tarım Kredi Kooperatifi" required>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($rol === 'farmer'): ?>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-success">Danışman Ziraat Mühendisiniz</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-success"><i class="fa-solid fa-user-doctor small"></i></span>
                        <select name="engineer_id" class="form-select fw-bold text-secondary">
                            <option value="0">-- Danışman Seçilmedi --</option>
                            <?php while($muh = mysqli_fetch_assoc($muhendisler_listesi)): ?>
                                <option value="<?php echo $muh['id']; ?>" <?php echo ($mevcut_muhendis_id === intval($muh['id'])) ? 'selected' : ''; ?>>
                                    Uzman: <?php echo htmlspecialchars($muh['first_name'] . " " . $muh['last_name'] . " (" . $muh['company'] . ")"); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <label class="form-label small fw-bold text-muted">Yeni Şifre <span class="text-danger font-monospace" style="font-size: 11px;">(Değiştirmek istemiyorsanız boş bırakın)</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-lock small"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••">
                </div>
            </div>

            <button type="submit" name="guncelle" class="btn btn-success w-100 fw-bold py-2 shadow-sm">
                Değişiklikleri Kaydet <i class="fa-solid fa-floppy-disk ms-1 small"></i>
            </button>
        </form>
    </div>

</div>
</body>
</html>