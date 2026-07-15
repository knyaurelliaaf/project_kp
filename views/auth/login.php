<?php
// Session sudah dimulai di index.php, tidak perlu session_start() lagi

// Jika sudah login, redirect
if (isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/dashboard");
    exit;
}

$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ADK-6</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/project_kp/public/css/login.css">
</head>
<body>

<div class="container">
    <div class="form-box">
        <img src="/project_kp/public/img/logo-adk.png" alt="Logo ADK" class="logo-top" onerror="this.style.display='none'">
        
        <h2>Selamat Datang</h2>
        <p class="subtitle">Silakan login untuk mengakses sistem</p>

        <?php if ($error): ?>
            <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <i class="fas fa-envelope icon"></i>
                <input type="email" name="email" placeholder="Masukkan Email" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock icon"></i>
                <input type="password" name="password" placeholder="Masukkan Password" required>
            </div>
            <button type="submit" name="login" class="login-btn"><i class="fas fa-sign-in-alt"></i> Login</button>
        </form>
    </div>

    <div class="info-box">
        <h1>PT ADK-6</h1>
        <p>Sistem Monitoring Crew<br>dan Pengelolaan Surat</p>
        <div class="features">
            <div class="feature-item"><i class="fas fa-hard-hat"></i><span>Monitoring Crew</span></div>
            <div class="feature-item"><i class="fas fa-envelope"></i><span>Surat Keluar</span></div>
            <div class="feature-item"><i class="fas fa-chart-bar"></i><span>Dashboard</span></div>
        </div>
    </div>
</div>

</body>
</html>
