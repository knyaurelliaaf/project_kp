<?php
// Session sudah dimulai di index.php, tidak perlu session_start() lagi

// Jika sudah login, redirect
if (isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/dashboard");
    exit;
}

$error = $error ?? $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PT. ADK-6 Crew Compliance</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Login CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/login.css">
</head>
<body>

<!-- Ambient Glowing Background Elements -->
<div class="bg-orb orb-rose"></div>
<div class="bg-orb orb-matcha"></div>

<div class="container">
    <!-- LEFT PANE: LOGIN FORM -->
    <div class="form-box">
        <div class="brand-top">
            <img src="<?= BASE_URL ?>/public/img/logo-adk.png" alt="Logo ADK" class="logo-top" onerror="this.style.display='none'">
            <span class="brand-tag"><i class="fas fa-shield-alt text-rose me-1"></i> PT. ADK-6</span>
        </div>
        
        <div class="form-header">
            <h2>Selamat Datang</h2>
            <p class="subtitle">Masuk ke sistem untuk mengakses dashboard & data crew</p>
        </div>

        <?php if ($error): ?>
            <div class="error-msg animate-shake">
                <i class="fas fa-exclamation-triangle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="input-group">
                <i class="fas fa-envelope icon"></i>
                <input type="email" name="email" placeholder="Email Pengguna" required autocomplete="email">
            </div>
            <div class="input-group">
                <i class="fas fa-lock icon"></i>
                <input type="password" name="password" id="password" placeholder="Kata Sandi" required autocomplete="current-password">
                <button type="button" class="toggle-password-btn" id="togglePasswordBtn" title="Tampilkan/Sembunyikan Password">
                    <i class="fas fa-eye" id="togglePasswordIcon"></i>
                </button>
            </div>
            <button type="submit" name="login" class="login-btn" id="btnSubmit">
                <span>Login ke Sistem</span>
                <i class="fas fa-arrow-right icon-arrow"></i>
            </button>
        </form>
        
        <div class="form-footer-note">
            <span>&copy; <?= date('Y') ?> PT. ADK-6 Management System</span>
        </div>
    </div>

    <!-- RIGHT PANE: CURVED HERO INFO BOX -->
    <div class="info-box">
        <h1 class="welcome-title">Welcome Back!</h1>
        <h3 class="hero-headline-sub">Driving <span class="txt-glow-green">Operational Safety</span> & <span class="txt-glow-rose">Crew Integrity</span></h3>
        <p class="hero-desc">Sistem Monitoring Kepatuhan Crew, MCU, Sertifikat, PKWT & Pengelolaan Surat Keluar Terintegrasi</p>
        
        <div class="features">
            <div class="feature-item float-item-1">
                <div class="feat-icon"><i class="fas fa-hard-hat"></i></div>
                <div class="feat-text">
                    <strong>Monitoring Crew</strong>
                    <small>100% Real-time Tracking</small>
                </div>
            </div>
            <div class="feature-item float-item-2">
                <div class="feat-icon"><i class="fas fa-stethoscope"></i></div>
                <div class="feat-text">
                    <strong>MCU & Sertifikat</strong>
                    <small>Auto Warning Expired</small>
                </div>
            </div>
            <div class="feature-item float-item-3">
                <div class="feat-icon"><i class="fas fa-envelope-open-text"></i></div>
                <div class="feat-text">
                    <strong>Surat & PKWT</strong>
                    <small>Arsip Digital Terpadu</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const togglePasswordBtn = document.getElementById('togglePasswordBtn');
const passwordInput = document.getElementById('password');
const togglePasswordIcon = document.getElementById('togglePasswordIcon');

if (togglePasswordBtn && passwordInput) {
    togglePasswordBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        togglePasswordIcon.classList.toggle('fa-eye');
        togglePasswordIcon.classList.toggle('fa-eye-slash');
    });
}

const loginForm = document.getElementById('loginForm');
const btnSubmit = document.getElementById('btnSubmit');
if (loginForm && btnSubmit) {
    loginForm.addEventListener('submit', function() {
        btnSubmit.classList.add('submitting');
        btnSubmit.querySelector('span').innerText = 'Memproses...';
    });
}
</script>

</body>
</html>
