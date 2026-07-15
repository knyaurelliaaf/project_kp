<?php
$user = $_SESSION['user'] ?? null;
$currentPage = $currentPage ?? 'dashboard';

$nama = $user['nama'] ?? 'Admin';
$inisial = '';
foreach (explode(' ', $nama) as $w) {
    if (!empty($w)) $inisial .= strtoupper($w[0]);
}
$inisial = substr($inisial, 0, 2);

$role = $user['role'] ?? '';
$roleLabel = match ($role) {
    'super_admin' => 'Super Admin',
    'payroll' => 'Payroll',
    default       => 'Role tidak dikenali (' . htmlspecialchars($role) . ')',
};

$kodeRig = $user['kode_rig'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'ADK-6' ?> - PT. ADK-6</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="/project_kp/bootstrap-5.3.8-dist/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="/project_kp/font/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/project_kp/public/css/style.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Select2 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>

<body>

    <div class="app">
        <!-- SIDEBAR -->
        <aside class="sb">
            <div class="sb-brand">
                <div class="sb-logo">Σ</div>
                <div class="sb-title">ADK-6<span>Crew Compliance</span></div>
            </div>
            <nav class="sb-nav">
                <div class="sb-sect">Utama</div>
                <a href="<?= BASE_URL ?>/dashboard" class="sb-item <?= $currentPage == 'dashboard' ? 'on' : '' ?>">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                <a href="<?= BASE_URL ?>/crew" class="sb-item">
                    <i class="fas fa-users"></i> Data Crew
                </a>
                <a href="<?= BASE_URL ?>/surat" class="sb-item <?= $currentPage == 'surat' ? 'on' : '' ?>">
                    <i class="fas fa-envelope"></i> Surat Keluar
                </a>

                <div class="sb-sect">Monitoring</div>
                <a href="<?= BASE_URL ?>/badge" class="sb-item <?= $currentPage == 'monitoring' ? 'on' : '' ?>">
                    <i class="fas fa-id-card"></i> Badge
                </a>
                <a href="<?= BASE_URL ?>/mcu" class="sb-item <?= $currentPage == 'monitoring' ? 'on' : '' ?>">
                    <i class="fas fa-stethoscope"></i> MCU
                </a>
                <a href="<?= BASE_URL ?>/sertifikat" class="sb-item <?= $currentPage == 'monitoring' ? 'on' : '' ?>">
                    <i class="fas fa-certificate"></i> Sertifikat
                </a>
                <a href="<?= BASE_URL ?>/pkwt" class="sb-item <?= $currentPage == 'monitoring' ? 'on' : '' ?>">
                    <i class="fas fa-file-contract"></i> PKWT
                </a>

                <?php if (($user['role'] ?? '') == 'super_admin'): ?>
                    <div class="sb-sect">Admin</div>
                    <a href="<?= BASE_URL ?>/admin/users" class="sb-item">
                        <i class="fas fa-user-cog"></i> Pengguna
                    </a>
                    <a href="<?= BASE_URL ?>/admin/master" class="sb-item <?= $currentPage == 'admin' ? 'on' : '' ?>">
                        <i class="fas fa-database"></i> Master Data
                    </a>
                <?php endif; ?>
            </nav>
            <div class="sb-footer">
                <a href="<?= BASE_URL ?>/auth/logout" class="sb-item out">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </aside>

        <!-- MAIN -->
        <main class="main">
            <div class="top">
                <div class="top-left">
                    <div class="breadcrumb">
                        <i class="fas fa-home" style="font-size:13px"></i>
                        <span>Beranda</span>
                        <i class="fas fa-chevron-right" style="font-size:11px"></i>
                        <strong><?= $title ?? 'Dashboard' ?></strong>
                    </div>
                </div>
                <div class="top-right">
                    <div class="profile">
                        <div class="av"><?= $inisial ?></div>
                        <div class="profile-txt">
                            <div class="name"><?= $nama ?></div>
                            <div class="role"><?= $roleLabel ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content">
