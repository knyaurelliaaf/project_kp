<?php require_once __DIR__ . '/../layout/header.php'; ?>

<?php
$selectedPeriode = $_GET['periode'] ?? date('Y-m');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="ag-header-title mb-1"><i class="fas fa-upload ag-header-icon me-2"></i> Upload &amp; Kirim Slip Gaji</h4>
        <small class="text-muted">Kelola pengunggahan file slip gaji dan pengiriman pesan WhatsApp otomatis</small>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/admin_gaji/status?periode=<?= urlencode($selectedPeriode) ?>" class="btn btn-outline-dark btn-sm">
            <i class="fas fa-tasks me-1"></i> Lihat Status Pengiriman
        </a>
    </div>
</div>

<?php $flash = Helper::getFlash(); if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] == 'error' ? 'danger' : 'success' ?> alert-dismissible fade show mb-4" role="alert">
        <?= $flash['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- ALUR 2 LANGKAH: UPLOAD & KIRIM -->
<div class="row g-4 mb-4">
    <!-- LANGKAH 1: UPLOAD ZIP -->
    <div class="col-lg-7">
        <div class="card-custom h-100 p-4">
            <div class="d-flex align-items-center mb-3">
                <span class="badge me-2 px-2 py-1 fs-6" style="background:#775537;color:#fff;">1</span>
                <h5 class="fw-bold mb-0" style="color: #775537;">Upload File ZIP Slip Gaji</h5>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin_gaji/upload" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold text-dark small mb-1">Periode Gaji</label>
                        <input type="month" name="periode" class="form-control form-control-lg fs-6" value="<?= htmlspecialchars($selectedPeriode) ?>" required>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-semibold text-dark small mb-1">File ZIP (.zip)</label>
                        <input type="file" name="zip_file" class="form-control form-control-lg fs-6" accept=".zip" required>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block mb-3">
                            <i class="fas fa-info-circle me-1"></i> Format nama PDF di ZIP: <strong>NAMA - JABATAN.pdf</strong> atau <strong>Nama Karyawan.pdf</strong>
                        </small>
                        <button type="submit" class="btn btn-ag-primary btn-lg w-100 fw-bold shadow-sm">
                            <i class="fas fa-cloud-upload-alt me-2"></i> Upload &amp; Siapkan Antrean
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- LANGKAH 2: LAUNCHER KIRIM WA -->
    <div class="col-lg-5">
        <div class="card-custom h-100 p-4 d-flex flex-column justify-content-between text-center" style="background: #FBE29D !important; border: 2.5px solid #775537 !important; border-radius: 18px;">
            <div>
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <span class="badge me-2 px-2 py-1 fs-6" style="background:#775537;color:#fff;">2</span>
                    <h5 class="fw-bold mb-0" style="color: #775537;">Kirim WA Otomatis</h5>
                </div>
                <p class="text-dark small mb-3">
                    Setelah berhasil mengunggah file ZIP, klik tombol di bawah untuk memulai pengiriman WhatsApp secara otomatis.
                </p>
            </div>
            
            <div class="my-auto py-2">
                <form method="POST" action="<?= BASE_URL ?>/admin_gaji/startWorker">
                    <input type="hidden" name="periode" value="<?= htmlspecialchars($selectedPeriode) ?>">
                    <button type="submit" class="btn btn-ag-success btn-lg w-100 py-3 fw-bold shadow-sm">
                        <i class="fab fa-whatsapp me-2 fs-5"></i> Mulai Kirim WA Otomatis
                    </button>
                </form>
            </div>

            <div class="mt-2">
                <small class="text-muted"><i class="fas fa-shield-alt me-1 text-success"></i> Pengiriman berjalan otomatis di background server</small>
            </div>
        </div>
    </div>
</div>

<!-- TABEL HASIL UPLOAD & ANTREAN SEKARANG -->
<div class="card-custom">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="fw-bold mb-0" style="color: #775537;">
                <i class="fas fa-list-ul me-2"></i>Daftar Antrean &amp; Hasil Upload (Periode <?= htmlspecialchars($selectedPeriode) ?>)
            </h5>
            <div class="d-flex gap-2">
                <span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="fas fa-hourglass-half me-1"></i> Pending: <?= $totalPending ?? 0 ?></span>
                <span class="badge bg-success px-3 py-2 fs-6"><i class="fas fa-check-circle me-1"></i> Terkirim: <?= $totalTerkirim ?? 0 ?></span>
                <span class="badge bg-danger px-3 py-2 fs-6"><i class="fas fa-times-circle me-1"></i> Gagal: <?= $totalGagal ?? 0 ?></span>
            </div>
        </div>

        <div class="table-responsive rounded-3 border" style="border-color: #C0DDDA !important;">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: #C0DDDA;">
                    <tr>
                        <th style="color: #775537;">#</th>
                        <th style="color: #775537;">Nama Karyawan</th>
                        <th style="color: #775537;">No. WhatsApp</th>
                        <th style="color: #775537;">Nama File PDF</th>
                        <th style="color: #775537;">Status</th>
                        <th style="color: #775537;" class="text-end">Aksi / Info</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($logs) && $logs && $logs->num_rows > 0): $no = 1; ?>
                        <?php while ($l = $logs->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong style="color: #775537;"><?= htmlspecialchars($l['nama']) ?></strong></td>
                                <td><?= htmlspecialchars($l['nomor_wa'] ?: '-') ?></td>
                                <td>
                                    <code><?= htmlspecialchars($l['nama_file']) ?></code>
                                    <a href="<?= BASE_URL ?>/admin_gaji/previewPdf?periode=<?= urlencode($l['periode']) ?>&file=<?= urlencode($l['nama_file']) ?>" target="_blank" class="badge bg-light text-primary border border-primary ms-1 text-decoration-none" title="Buka / Lihat File PDF">
                                        <i class="fas fa-file-pdf text-danger me-1"></i> Lihat PDF
                                    </a>
                                </td>
                                <td>
                                    <?php
                                    if ($l['status'] == 'terkirim') echo '<span class="badge bg-success">Terkirim</span>';
                                    elseif ($l['status'] == 'pending') echo '<span class="badge bg-warning text-dark">Pending</span>';
                                    else echo '<span class="badge bg-danger">Gagal</span>';
                                    ?>
                                </td>
                                <td class="text-end small text-muted">
                                    <?= htmlspecialchars($l['dikirim_pada'] ?? 'Dalam antrean') ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fs-3 d-block mb-2 text-secondary"></i>
                                Belum ada file slip gaji yang diunggah untuk periode <strong><?= htmlspecialchars($selectedPeriode) ?></strong>.<br>
                                <small class="text-muted">Pilih file <strong>ZIP (.zip)</strong> berisi PDF slip gaji pada form di atas, lalu klik <strong>"Upload &amp; Siapkan Antrean"</strong>.</small>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="text-end mt-3">
            <a href="<?= BASE_URL ?>/admin_gaji/status?periode=<?= urlencode($selectedPeriode) ?>" class="btn btn-sm btn-outline-secondary">
                Lihat Selengkapnya di Menu Status <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
