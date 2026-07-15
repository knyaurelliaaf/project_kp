<?php
$title = 'Hasil Import Payroll';
require_once __DIR__ . '/../layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fas fa-circle-check"></i> Hasil Import Payroll</h4>
        <p class="text-muted mb-0">Ringkasan proses import data gaji.</p>
    </div>
    <a href="<?= BASE_URL ?>/import/formImport" class="btn btn-outline-secondary btn-sm"><i class="fas fa-upload"></i> Import Lagi</a>
</div>

<div class="card-custom">
    <div class="card-body p-4">

    <div class="alert alert-success">
        <strong><?= count($sukses) ?> data berhasil diimport.</strong>
    </div>

    <?php if (!empty($gagal)): ?>
        <div class="alert alert-warning">
            <strong><?= count($gagal) ?> data gagal (badge tidak ditemukan di data crew):</strong>
            <ul>
                <?php foreach ($gagal as $item): ?>
                    <li><?= htmlspecialchars($item) ?></li>
                <?php endforeach; ?>
            </ul>
            <p class="mb-0">Cek dulu apakah badge ini sudah terdaftar di menu Data Crew, lalu import ulang.</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($duplikat)): ?>
        <div class="alert alert-info">
            <strong><?= count($duplikat) ?> data dilewati (sudah pernah diimport untuk periode ini):</strong>
            <ul>
                <?php foreach ($duplikat as $item): ?>
                    <li><?= htmlspecialchars($item) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

        <div class="d-flex gap-2 pt-2">
            <a href="<?= BASE_URL ?>/payroll" class="btn btn-sigma"><i class="fas fa-wallet"></i> Lihat Data Gaji</a>
            <a href="<?= BASE_URL ?>/import/formImport" class="btn btn-light border">Import Periode Lain</a>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
