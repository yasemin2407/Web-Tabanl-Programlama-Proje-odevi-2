<?php
include 'baglan.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Eğer kullanıcı zaten giriş yapmışsa direkt ana sayfaya yönlendir
if (isset($_SESSION['oturum']) && $_SESSION['oturum'] === true) {
    header("Location: index.php"); exit();
}

$hata = "";

if (isset($_POST['giris_yap'])) {
    $username = mysqli_real_escape_string($baglanti, trim($_POST['username']));
    $password = trim($_POST['password']);
    
    // SQL sorgusunda şifreyi değil, sadece kullanıcı adını aratıyoruz
    $sorgu = "SELECT * FROM users WHERE username = '$username'";
    $sonuc = mysqli_query($baglanti, $sorgu);

    if (mysqli_num_rows($sonuc) == 1) {
        $kullanici = mysqli_fetch_assoc($sonuc);
        
        // Veritabanındaki hash'lenmiş şifreyi PHP fonksiyonu ile doğruluyoruz
        if (password_verify($password, $kullanici['password'])) {
            $_SESSION['oturum'] = true;
            $_SESSION['user_id'] = $kullanici['id'];
            $_SESSION['kullanici_adi'] = $kullanici['username'];
            $_SESSION['rol'] = $kullanici['role'];
            
            $_SESSION['ad'] = mb_convert_case($kullanici['first_name'], MB_CASE_TITLE, "UTF-8");
            $_SESSION['soyad'] = mb_convert_case($kullanici['last_name'], MB_CASE_TITLE, "UTF-8");

            header("Location: index.php"); exit();
        } else {
            $hata = "Kullanıcı adı veya şifre hatalı!";
        }
    } else {
        $hata = "Kullanıcı adı veya şifre hatalı!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akıllı Tarım Otomasyonu - Giriş Yap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 420px;">
    <div class="card login-card bg-white p-4 animate__animated animate__fadeIn">
        
        <div class="text-center mb-4">
            <div class="display-5 text-success mb-2">
                <i class="fa-solid fa-seedling"></i>
            </div>
            <h4 class="fw-bold text-dark m-0">Sisteme Giriş Yap</h4>
            <p class="text-muted small">Akıllı Tarım Yönetim Otomasyonu</p>
        </div>

        <?php if(!empty($hata)): ?>
            <div class="alert alert-danger py-2 small text-center fw-bold mb-3">
                <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo $hata; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">Kullanıcı Adı</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-user small"></i></span>
                    <input type="text" name="username" class="form-control border-start-0" placeholder="Kullanıcı adınızı girin" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold text-muted">Şifre</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-lock small"></i></span>
                    <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" name="giris_yap" class="btn btn-success w-100 fw-bold py-2 shadow-sm mb-3">
                Giriş Yap <i class="fa-solid fa-arrow-right-to-bracket ms-1 small"></i>
            </button>
            
            <div class="text-center border-top pt-3">
                <span class="text-muted small">Hesabınız yok mu?</span>
                <a href="register.php" class="small text-success fw-bold text-decoration-none ms-1">Hemen Kaydolun</a>
            </div>
        </form>

    </div>
</div>

</body>
</html>