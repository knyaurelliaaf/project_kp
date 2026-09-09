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
    'admin_rig'   => 'Admin Rig',
    'payroll'     => 'Payroll Admin',
    'payroll_wa'  => 'Payroll',
    'admin_gaji'  => 'Payroll',
    default       => 'Role tidak dikenali (' . htmlspecialchars($role) . ')',
};
$kodeRig = $user['kode_rig'] ?? '';

// Hitung data statistik header topbar
$headerExpBadge = 0; $headerExpMcu = 0; $headerExpSert = 0; $headerExpPkwt = 0;
$headerSoonBadge = 0; $headerSoonMcu = 0; $headerSoonSert = 0; $headerSoonPkwt = 0;
$headerGagalWa = 0; $headerPendingWa = 0;

$rigIdsUser = $_SESSION['user']['rig_ids'] ?? [];
$isAllRigUser = ($role === 'super_admin') || empty($rigIdsUser);

if (in_array($role, ['super_admin', 'admin_rig'])) {
    if (file_exists(__DIR__ . '/../../app/models/BadgeModel.php')) {
        require_once __DIR__ . '/../../core/Model.php';
        require_once __DIR__ . '/../../app/models/BadgeModel.php';
        require_once __DIR__ . '/../../app/models/McuModel.php';
        require_once __DIR__ . '/../../app/models/SertifikatModel.php';
        require_once __DIR__ . '/../../app/models/PkwtModel.php';

        $bmHeader = new BadgeModel();
        $mmHeader = new McuModel();
        $smHeader = new SertifikatModel();
        $pmHeader = new PkwtModel();

        $headerExpBadge = $bmHeader->countExpired($rigIdsUser, $isAllRigUser);
        $headerExpMcu   = $mmHeader->countExpired($rigIdsUser, $isAllRigUser);
        $headerExpSert  = $smHeader->countExpired($rigIdsUser, $isAllRigUser);
        $headerExpPkwt  = $pmHeader->countExpired($rigIdsUser, $isAllRigUser);

        $headerSoonBadge = $bmHeader->countSoon($rigIdsUser, $isAllRigUser);
        $headerSoonMcu   = $mmHeader->countSoon($rigIdsUser, $isAllRigUser);
        $headerSoonSert  = $smHeader->countSoon($rigIdsUser, $isAllRigUser);
        $headerSoonPkwt  = $pmHeader->countSoon($rigIdsUser, $isAllRigUser);
    }
} elseif (in_array($role, ['payroll', 'admin_gaji', 'payroll_wa'])) {
    if (file_exists(__DIR__ . '/../../app/models/KirimLogModel.php')) {
        require_once __DIR__ . '/../../core/Model.php';
        require_once __DIR__ . '/../../app/models/KirimLogModel.php';
        $klmHeader = new KirimLogModel();
        $headerGagalWa   = $klmHeader->count('status', 'gagal') ?? 0;
        $headerPendingWa = $klmHeader->count('status', 'pending') ?? 0;
    }
}

$headerTotalExp   = $headerExpBadge + $headerExpMcu + $headerExpSert + $headerExpPkwt;
$headerTotalSoon  = $headerSoonBadge + $headerSoonMcu + $headerSoonSert + $headerSoonPkwt;
$headerTotalTasks = in_array($role, ['payroll', 'admin_gaji', 'payroll_wa']) 
    ? ($headerGagalWa + $headerPendingWa) 
    : ($headerTotalExp + $headerTotalSoon);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'ADK-6' ?> - PT. ADK-6</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/bootstrap-5.3.8-dist/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/font/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Select2 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Prevent BFCache / Force Reload on Back Button Navigation -->
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted || (window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType("navigation")[0]?.type === "back_forward")) {
                window.location.reload();
            }
        });
    </script>
</head>

<body class="<?= (strpos($currentPage, 'gaji') === 0 || strpos($currentPage, 'payroll') === 0 || $role === 'admin_gaji' || $role === 'payroll_wa' || $role === 'payroll') ? 'theme-admin-gaji' : '' ?>">

    <?php if (!empty($_SESSION['show_welcome'])): ?>
        <?php if ($role === 'super_admin'): ?>
            <?php require_once __DIR__ . '/welcome_overlay.php'; ?>
        <?php elseif (in_array($role, ['admin_gaji', 'payroll', 'payroll_wa'])): ?>
            <?php require_once __DIR__ . '/welcome_overlay_gaji.php'; ?>
        <?php endif; ?>
        <?php unset($_SESSION['show_welcome']); ?>
    <?php endif; ?>

    <div class="app">
        <!-- SIDEBAR -->
        <aside class="sb">
            <div class="sb-brand">
                <div class="sb-logo"><img src="<?= BASE_URL ?>/public/img/logo-adk.png" alt="ADK-6" style="width:100%;height:100%;object-fit:contain;padding:5px;border-radius:12px;"></div>
                <div class="sb-title">ADK-6<span>Crew Compliance</span></div>
                <button type="button" class="sb-toggle-btn" title="Toggle Sidebar">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>

            <nav class="sb-nav" id="sbNav">

                <?php if ($role === 'payroll'): ?>
                    <div class="sb-sect">
                        <span>Payroll Admin</span>
                        <div class="sb-sect-line"></div>
                        <i class="fas fa-chevron-down sb-sect-arrow"></i>
                    </div>
                    <div class="sb-group">
                        <a href="<?= BASE_URL ?>/payroll" class="sb-item <?= $currentPage == 'payroll_dashboard' ? 'on' : '' ?>">
                            <i class="fas fa-th-large"></i> <span>Dashboard</span>
                        </a>
                        <a href="<?= BASE_URL ?>/payroll/daftarKaryawan" class="sb-item <?= $currentPage == 'payroll_slip' ? 'on' : '' ?>">
                            <i class="fas fa-money-check-alt"></i> <span>Slip Gaji</span>
                        </a>
                        <a href="<?= BASE_URL ?>/import/formImport" class="sb-item <?= $currentPage == 'import' ? 'on' : '' ?>">
                            <i class="fas fa-file-import"></i> <span>Import Data Gaji</span>
                        </a>
                    </div>

                <?php elseif ($role === 'admin_gaji' || $role === 'payroll_wa'): 
                    $reqUrl = trim($_GET['url'] ?? '', '/');
                ?>
                    <div class="sb-sect">
                        <span>Slip Gaji WA</span>
                        <div class="sb-sect-line"></div>
                        <i class="fas fa-chevron-down sb-sect-arrow"></i>
                    </div>
                    <div class="sb-group">
                        <a href="<?= BASE_URL ?>/admin_gaji" class="sb-item <?= ($currentPage == 'gaji_dashboard' || $currentPage == 'gaji' || $reqUrl == 'admin_gaji' || $reqUrl == 'admin_gaji/index') ? 'on' : '' ?>">
                            <i class="fas fa-paper-plane"></i> <span>Dashboard</span>
                        </a>
                        <a href="<?= BASE_URL ?>/admin_gaji/penerima" class="sb-item <?= ($currentPage == 'gaji_penerima' || $reqUrl == 'admin_gaji/penerima') ? 'on' : '' ?>">
                            <i class="fas fa-users"></i> <span>Penerima</span>
                        </a>
                        <a href="<?= BASE_URL ?>/admin_gaji/kirim" class="sb-item <?= ($currentPage == 'gaji_kirim' || $reqUrl == 'admin_gaji/kirim') ? 'on' : '' ?>">
                            <i class="fas fa-upload"></i> <span>Upload & Kirim</span>
                        </a>
                        <a href="<?= BASE_URL ?>/admin_gaji/status" class="sb-item <?= ($currentPage == 'gaji_status' || $reqUrl == 'admin_gaji/status') ? 'on' : '' ?>">
                            <i class="fas fa-list-check"></i> <span>Status</span>
                        </a>
                    </div>

                <?php else: ?>
                    <div class="sb-sect">
                        <span>Utama</span>
                        <div class="sb-sect-line"></div>
                        <i class="fas fa-chevron-down sb-sect-arrow"></i>
                    </div>
                    <div class="sb-group">
                        <a href="<?= BASE_URL ?>/dashboard" class="sb-item <?= $currentPage == 'dashboard' ? 'on' : '' ?>">
                            <i class="fas fa-th-large"></i> <span>Dashboard</span>
                        </a>
                        <a href="<?= BASE_URL ?>/crew" class="sb-item <?= ($currentPage == 'crew' || $currentPage == 'data_crew') ? 'on' : '' ?>">
                            <i class="fas fa-users"></i> <span>Data Crew</span>
                        </a>
                        <a href="<?= BASE_URL ?>/surat" class="sb-item <?= ($currentPage == 'surat' || $currentPage == 'surat_keluar') ? 'on' : '' ?>">
                            <i class="fas fa-envelope"></i> <span>Surat Keluar</span>
                        </a>
                    </div>

                    <div class="sb-sect">
                        <span>Monitoring</span>
                        <div class="sb-sect-line"></div>
                        <i class="fas fa-chevron-down sb-sect-arrow"></i>
                    </div>
                    <div class="sb-group">
                        <a href="<?= BASE_URL ?>/badge" class="sb-item <?= ($currentPage == 'badge' || $currentPage == 'monitoring_badge') ? 'on' : '' ?>">
                            <i class="fas fa-id-card"></i> <span>Badge</span>
                        </a>
                        <a href="<?= BASE_URL ?>/mcu" class="sb-item <?= ($currentPage == 'mcu' || $currentPage == 'monitoring_mcu') ? 'on' : '' ?>">
                            <i class="fas fa-stethoscope"></i> <span>MCU</span>
                        </a>
                        <a href="<?= BASE_URL ?>/sertifikat" class="sb-item <?= ($currentPage == 'sertifikat' || $currentPage == 'monitoring_sertifikat') ? 'on' : '' ?>">
                            <i class="fas fa-certificate"></i> <span>Sertifikat</span>
                        </a>
                        <a href="<?= BASE_URL ?>/pkwt" class="sb-item <?= ($currentPage == 'pkwt' || $currentPage == 'monitoring_pkwt') ? 'on' : '' ?>">
                            <i class="fas fa-file-contract"></i> <span>PKWT</span>
                        </a>
                    </div>

                    <?php if ($role == 'super_admin'): ?>
                        <div class="sb-sect">
                            <span>Admin</span>
                            <div class="sb-sect-line"></div>
                            <i class="fas fa-chevron-down sb-sect-arrow"></i>
                        </div>
                        <div class="sb-group">
                            <a href="<?= BASE_URL ?>/admin/users" class="sb-item <?= ($currentPage == 'users' || $currentPage == 'admin_users' || $currentPage == 'pengguna') ? 'on' : '' ?>">
                                <i class="fas fa-user-cog"></i> <span>Pengguna</span>
                            </a>
                            <a href="<?= BASE_URL ?>/admin/master" class="sb-item <?= ($currentPage == 'admin' || $currentPage == 'master' || $currentPage == 'master_data') ? 'on' : '' ?>">
                                <i class="fas fa-database"></i> <span>Master Data</span>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="sb-sect">
                        <span>Lainnya</span>
                        <div class="sb-sect-line"></div>
                        <i class="fas fa-chevron-down sb-sect-arrow"></i>
                    </div>
                    <div class="sb-group">
                        <a href="<?= BASE_URL ?>/laporan" class="sb-item <?= $currentPage == 'laporan' ? 'on' : '' ?>">
                            <i class="fas fa-file-alt"></i> <span>Laporan</span>
                        </a>
                    </div>
                <?php endif; ?>

            </nav>
            <div class="sb-footer">
                <a href="<?= BASE_URL ?>/auth/logout" class="sb-item out">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Sidebar Collapsible Accordion & Toggle Collapse Script -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Collapsible Accordion Sections
            var sects = document.querySelectorAll('.sb-sect');
            sects.forEach(function(sect) {
                sect.addEventListener('click', function() {
                    var group = this.nextElementSibling;
                    if (group && group.classList.contains('sb-group')) {
                        this.classList.toggle('collapsed');
                        group.classList.toggle('collapsed');
                    }
                });
            });

            // Sidebar Toggle Collapse / Expand
            var toggleBtn = document.querySelector('.sb-toggle-btn');
            var sidebar = document.querySelector('.sb');
            if (toggleBtn && sidebar) {
                // Restore state from localStorage
                if (localStorage.getItem('sidebar_collapsed') === '1') {
                    sidebar.classList.add('is-collapsed');
                    var icon = toggleBtn.querySelector('i');
                    if (icon) icon.className = 'fas fa-chevron-right';
                }

                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('is-collapsed');
                    var isCollapsed = sidebar.classList.contains('is-collapsed');
                    localStorage.setItem('sidebar_collapsed', isCollapsed ? '1' : '0');
                    var icon = toggleBtn.querySelector('i');
                    if (icon) {
                        icon.className = isCollapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
                    }
                });
            }
        });
        </script>

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
                
                <div class="top-right d-flex align-items-center gap-2">
                    <!-- Tasks Widget Pill Button -->
                    <div class="dropdown">
                        <button class="top-btn-pill dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Daftar Dokumen & Tugas Perlu Tindakan">
                            <i class="fas fa-clipboard-check text-secondary fs-6"></i>
                            <span class="fw-semibold top-btn-label">Tasks</span>
                            <span class="badge-pill-orange"><?= $headerTotalTasks > 99 ? '99+' : $headerTotalTasks ?></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-3 shadow-lg top-dropdown-box" style="width: 320px; border-radius: 16px;">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-tasks text-primary me-2"></i>Perlu Tindakan</h6>
                                <span class="badge bg-danger rounded-pill"><?= $headerTotalTasks ?> Item</span>
                            </div>
                            
                            <?php if (in_array($role, ['super_admin', 'admin_rig'])): ?>
                                <?php if ($headerTotalTasks == 0): ?>
                                    <div class="text-center py-3 text-muted small">
                                        <i class="fas fa-check-circle text-success fs-4 d-block mb-1"></i>
                                        Semua dokumen valid! Tidak ada expired/warning.
                                    </div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush small">
                                        <?php if ($headerExpBadge > 0): ?>
                                            <a href="<?= BASE_URL ?>/badge?status=exp" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-2">
                                                <span><i class="fas fa-id-card text-danger me-2"></i>Badge Kadaluarsa</span>
                                                <span class="badge bg-danger rounded-pill"><?= $headerExpBadge ?></span>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($headerExpMcu > 0): ?>
                                            <a href="<?= BASE_URL ?>/mcu?status=exp" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-2">
                                                <span><i class="fas fa-stethoscope text-danger me-2"></i>MCU Kadaluarsa</span>
                                                <span class="badge bg-danger rounded-pill"><?= $headerExpMcu ?></span>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($headerExpSert > 0): ?>
                                            <a href="<?= BASE_URL ?>/sertifikat?status=exp" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-2">
                                                <span><i class="fas fa-certificate text-danger me-2"></i>Sertifikat Kadaluarsa</span>
                                                <span class="badge bg-danger rounded-pill"><?= $headerExpSert ?></span>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($headerExpPkwt > 0): ?>
                                            <a href="<?= BASE_URL ?>/pkwt?status=exp" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-2">
                                                <span><i class="fas fa-file-contract text-danger me-2"></i>PKWT Kadaluarsa</span>
                                                <span class="badge bg-danger rounded-pill"><?= $headerExpPkwt ?></span>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($headerTotalSoon > 0): ?>
                                            <a href="<?= BASE_URL ?>/dashboard" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-2">
                                                <span><i class="fas fa-clock text-warning me-2"></i>Akan Kadaluarsa (≤30 Hari)</span>
                                                <span class="badge bg-warning text-dark rounded-pill"><?= $headerTotalSoon ?></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="list-group list-group-flush small">
                                    <a href="<?= BASE_URL ?>/admin_gaji/status" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-2">
                                        <span><i class="fas fa-exclamation-triangle text-danger me-2"></i>Pengiriman Gagal</span>
                                        <span class="badge bg-danger rounded-pill"><?= $headerGagalWa ?></span>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin_gaji/status" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-2">
                                        <span><i class="fas fa-hourglass-half text-warning me-2"></i>Dalam Antrean (Pending)</span>
                                        <span class="badge bg-warning text-dark rounded-pill"><?= $headerPendingWa ?></span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Notification Icon Button -->
                    <div class="dropdown">
                        <button class="top-btn-icon dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifikasi System">
                            <i class="far fa-bell text-secondary fs-5"></i>
                            <?php if ($headerTotalExp > 0 || $headerGagalWa > 0): ?>
                                <span class="top-dot-indicator bg-danger"></span>
                            <?php endif; ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-3 shadow-lg top-dropdown-box" style="width: 320px; border-radius: 16px;">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-bell text-warning me-2"></i>Notifikasi</h6>
                                <span class="small text-muted">System ADK-6</span>
                            </div>
                            <div class="notification-list small" style="max-height: 220px; overflow-y: auto;">
                                <?php if ($headerTotalExp > 0): ?>
                                    <div class="p-2 mb-2 bg-light-danger border-danger-subtle rounded border">
                                        <div class="fw-bold text-danger"><i class="fas fa-exclamation-circle me-1"></i> Perhatian Expired</div>
                                        <div class="text-muted">Terdapat <?= $headerTotalExp ?> dokumen crew telah kadaluarsa.</div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($headerTotalSoon > 0): ?>
                                    <div class="p-2 mb-2 bg-light-warning border-warning-subtle rounded border">
                                        <div class="fw-bold text-warning-emphasis"><i class="fas fa-clock me-1"></i> Perhatian Warning</div>
                                        <div class="text-muted"><?= $headerTotalSoon ?> dokumen akan kadaluarsa dalam 30 hari.</div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($headerTotalExp == 0 && $headerTotalSoon == 0 && $headerGagalWa == 0): ?>
                                    <div class="text-center py-3 text-muted">
                                        <i class="fas fa-check-circle text-success fs-4 d-block mb-1"></i>
                                        Tidak ada notifikasi penting saat ini.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Action Button -->
                    <div class="dropdown">
                        <button class="top-btn-quick dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Menu Aksi Cepat">
                            <i class="fas fa-bolt text-white fs-6"></i>
                            <span class="fw-bold text-white">Quick</span>
                            <span class="badge-pill-white"><?= $headerTotalTasks > 99 ? '99+' : $headerTotalTasks ?></span>
                            <i class="fas fa-chevron-down text-white ms-1" style="font-size: 10px;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end p-2 shadow-lg top-dropdown-box" style="min-width: 230px; border-radius: 14px;">
                            <li class="dropdown-header fw-bold text-uppercase text-muted px-2 py-1" style="font-size:10px;">Menu Aksi Cepat</li>
                            <?php if (in_array($role, ['super_admin', 'admin_rig'])): ?>
                                <li><a class="dropdown-item rounded py-2 px-2" href="<?= BASE_URL ?>/crew"><i class="fas fa-user-plus text-primary me-2"></i> Data Crew</a></li>
                                <li><a class="dropdown-item rounded py-2 px-2" href="<?= BASE_URL ?>/mcu"><i class="fas fa-stethoscope text-success me-2"></i> Input / Perbarui MCU</a></li>
                                <li><a class="dropdown-item rounded py-2 px-2" href="<?= BASE_URL ?>/sertifikat"><i class="fas fa-certificate text-warning me-2"></i> Input / Perbarui Sertifikat</a></li>
                                <li><a class="dropdown-item rounded py-2 px-2" href="<?= BASE_URL ?>/pkwt"><i class="fas fa-file-contract text-info me-2"></i> Input / Perbarui PKWT</a></li>
                                <li><a class="dropdown-item rounded py-2 px-2" href="<?= BASE_URL ?>/badge"><i class="fas fa-id-card text-danger me-2"></i> Input / Perbarui Badge</a></li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li><a class="dropdown-item rounded py-2 px-2" href="<?= BASE_URL ?>/surat"><i class="fas fa-envelope text-secondary me-2"></i> Buat Surat Keluar</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item rounded py-2 px-2" href="<?= BASE_URL ?>/import/formImport"><i class="fas fa-file-import text-primary me-2"></i> Import File Gaji Excel</a></li>
                                <li><a class="dropdown-item rounded py-2 px-2" href="<?= BASE_URL ?>/admin_gaji/kirim"><i class="fas fa-paper-plane text-success me-2"></i> Upload & Kirim Slip WA</a></li>
                                <li><a class="dropdown-item rounded py-2 px-2" href="<?= BASE_URL ?>/admin_gaji/status"><i class="fas fa-list-check text-warning me-2"></i> Cek Status Pengiriman</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown ms-1">
                        <a href="#" class="profile text-decoration-none dropdown-toggle no-chevron" data-bs-toggle="dropdown" aria-expanded="false" title="Profil User">
                            <div class="av"><?= htmlspecialchars($inisial) ?></div>
                            <div class="profile-txt d-none d-md-block">
                                <div class="name"><?= htmlspecialchars($nama) ?></div>
                                <div class="role"><?= $roleLabel ?></div>
                            </div>
                            <div class="profile-cog-btn ms-2">
                                <i class="fas fa-cog"></i>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-2 shadow-lg top-dropdown-box" style="min-width: 200px; border-radius: 14px;">
                            <li class="px-2 py-2 border-bottom mb-1">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($nama) ?></div>
                                <div class="small text-muted"><?= $roleLabel ?></div>
                            </li>
                            <li><a class="dropdown-item rounded py-2 px-2" href="<?= BASE_URL ?>/auth/profile"><i class="fas fa-user-circle me-2 text-primary"></i> Edit Profil & Pass</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item rounded py-2 px-2 text-danger" href="<?= BASE_URL ?>/auth/logout"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="content">

