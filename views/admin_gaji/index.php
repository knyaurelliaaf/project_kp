<?php require_once __DIR__ . '/../layout/header.php'; ?>

<!-- HERO BANNER -->
<div class="ag-hero mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-hand-wave me-2"></i> Welcome, <?= htmlspecialchars($nama) ?>! 👋</h4>
            <p class="mb-0 small">Selamat datang di Dashboard Kirim Slip Gaji. Kelola penerima, unggah berkas slip gaji, dan pantau pengiriman WhatsApp otomatis.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/admin_gaji/kirim" class="btn btn-ag-primary px-4 py-2">
                <i class="fas fa-upload me-2"></i> Upload Slip Gaji
            </a>
        </div>
    </div>
</div>

<!-- STAT CARDS -->
<div class="stat-row">
    <div class="sc">
        <div class="sc-top">
            <div class="sc-icon ic-brown"><i class="fas fa-users"></i></div>
            <span class="sc-trend tr-green">Aktif</span>
        </div>
        <div><div class="sc-lbl">Penerima Aktif</div><div class="sc-val"><?= $total_penerima ?></div></div>
    </div>
    <div class="sc">
        <div class="sc-top">
            <div class="sc-icon ic-teal"><i class="fas fa-check-circle"></i></div>
        </div>
        <div><div class="sc-lbl">Terkirim</div><div class="sc-val"><?= $terkirim ?></div></div>
    </div>
    <div class="sc">
        <div class="sc-top">
            <div class="sc-icon ic-gold"><i class="fas fa-clock"></i></div>
        </div>
        <div><div class="sc-lbl">Pending</div><div class="sc-val"><?= $pending ?></div></div>
    </div>
    <div class="sc">
        <div class="sc-top">
            <div class="sc-icon ic-red"><i class="fas fa-times-circle"></i></div>
        </div>
        <div><div class="sc-lbl">Gagal</div><div class="sc-val"><?= $gagal ?></div></div>
    </div>
</div>

<!-- QUICK ACTION GRID -->
<div class="quick-grid mt-4">
    <div class="quick-card">
        <div class="icon mb-3"><i class="fas fa-users"></i></div>
        <h6>Kelola Penerima</h6>
        <p>Tambah, edit, nonaktifkan penerima slip gaji karyawan.</p>
        <a href="<?= BASE_URL ?>/admin_gaji/penerima" class="btn-link-custom">Kelola <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
    <div class="quick-card">
        <div class="icon mb-3"><i class="fas fa-upload"></i></div>
        <h6>Upload Slip Gaji</h6>
        <p>Upload ZIP berisi PDF slip gaji (nama file = NIK atau Nama).</p>
        <a href="<?= BASE_URL ?>/admin_gaji/kirim" class="btn-link-custom">Upload <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
    <div class="quick-card">
        <div class="icon mb-3"><i class="fas fa-list-check"></i></div>
        <h6>Status Pengiriman</h6>
        <p>Lihat progress real-time & status pengiriman slip gaji.</p>
        <a href="<?= BASE_URL ?>/admin_gaji/status" class="btn-link-custom">Lihat <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

