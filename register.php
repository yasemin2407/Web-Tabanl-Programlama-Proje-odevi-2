<?php
include 'baglan.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }

$mesaj = "";

if (isset($_POST['kayit_ol'])) {
    $username = mysqli_real_escape_string($baglanti, trim($_POST['username']));
    $password = trim($_POST['password']);
    $first_name = mysqli_real_escape_string($baglanti, trim($_POST['first_name']));
    $last_name = mysqli_real_escape_string($baglanti, trim($_POST['last_name']));
    $role = $_POST['role']; // 'farmer' veya 'engineer'
    $company_name = mysqli_real_escape_string($baglanti, trim($_POST['company_name'] ?? ''));

    $sifreli_sifre = password_hash($password, PASSWORD_DEFAULT);

    // Kullanıcı adı kontrolü
    $kontrol = mysqli_query($baglanti, "SELECT id FROM users WHERE username = '$username'");
    if (mysqli_num_rows($kontrol) > 0) {
        $mesaj = "<div class='alert alert-danger py-2 small fw-bold'>❌ Bu kullanıcı adı zaten alınmış!</div>";
    } else {
        // 1. ADIM: Ana tabloya (users) ekleme yapıyoruz
        $user_sql = "INSERT INTO users (username, password, first_name, last_name, role) 
                     VALUES ('$username', '$sifreli_sifre', '$first_name', '$last_name', '$role')";
        
        if (mysqli_query($baglanti, $user_sql)) {
            $yeni_user_id = mysqli_insert_id($baglanti); 

            // 2. ADIM: Role göre alt tablolara kayıt yapıyoruz 
            if ($role === 'engineer') {
                mysqli_query($baglanti, "INSERT INTO engineers (user_id, company_name) VALUES ($yeni_user_id, '$company_name')");
            } else {
                mysqli_query($baglanti, "INSERT INTO farmers (user_id, engineer_id) VALUES ($yeni_user_id, NULL)");
            }

            $mesaj = "<div class='alert alert-success py-2 small fw-bold'>🎉 Kayıt başarıyla tamamlandı! Giriş yapabilirsiniz.</div>";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akıllı Tarım Otomasyonu - Kayıt Ol</title>
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
        .register-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 480px; margin-top: 20px; margin-bottom: 20px;">
    <div class="card register-card bg-white p-4">
        
        <div class="text-center mb-3">
            <div class="display-6 text-success mb-1">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h4 class="fw-bold text-dark m-0">Yeni Hesap Oluştur</h4>
            <p class="text-muted small">Sisteme kaydolarak otomasyonu kullanmaya başlayın</p>
        </div>

        <?php if(!empty($mesaj)) echo $mesaj; ?>

        <form action="register.php" method="POST">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold text-muted">Adınız</label>
                    <input type="text" name="first_name" class="form-control" placeholder="Örn: Yasemin" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold text-muted">Soyadınız</label>
                    <input type="text" name="last_name" class="form-control" placeholder="Örn: Korkmaz" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">Kullanıcı Adı</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-user small"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Kullanıcı Adınızı Giriniz..." required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">Şifre</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-lock small"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">Sistemdeki Rolünüz</label>
                <div class="d-flex gap-3 mt-1">
                    <div class="form-check flex-grow-1 border rounded p-2 px-3 bg-white">
                        <input class="form-check-input" type="radio" name="role" id="roleFarmer" value="farmer" checked onclick="toggleCompany(false)">
                        <label class="form-check-label small fw-bold text-success" for="roleFarmer">
                            <i class="fa-solid fa-wheat-awn me-1"></i> Çiftçi
                        </label>
                    </div>
                    <div class="form-check flex-grow-1 border rounded p-2 px-3 bg-white">
                        <input class="form-check-input" type="radio" name="role" id="roleEngineer" value="engineer" onclick="toggleCompany(true)">
                        <label class="form-check-label small fw-bold text-primary" for="roleEngineer">
                            <i class="fa-solid fa-user-doctor me-1"></i> Mühendis
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-4" id="companyInputSection" style="display: none;">
                <label class="form-label small fw-bold text-muted">Çalıştığınız Kurum / Şirket</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-building small"></i></span>
                    <input type="text" name="company_name" id="company_name" class="form-control" placeholder="Örn: Hektaş veya Tarım İl Md.">
                </div>
            </div>

            <button type="submit" name="kayit_ol" class="btn btn-success w-100 fw-bold py-2 shadow-sm mb-3">
                Kayıt Ol <i class="fa-solid fa-user-check ms-1 small"></i>
            </button>
            
            <div class="text-center border-top pt-3">
                <span class="text-muted small">Zaten hesabınız var mı?</span>
                <a href="login.php" class="small text-success fw-bold text-decoration-none ms-1">Giriş Yapın</a>
            </div>
        </form>

    </div>
</div>

<script>
function toggleCompany(show) {
    const section = document.getElementById('companyInputSection');
    const input = document.getElementById('company_name');
    if (show) {
        section.style.display = 'block';
        input.setAttribute('required', 'required');
    } else {
        section.style.display = 'none';
        input.removeAttribute('required');
        input.value = '';
    }
}
</script>
</body>
</html>