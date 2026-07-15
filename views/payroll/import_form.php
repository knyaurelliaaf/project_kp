<?php
$title = 'Import Payroll';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fas fa-file-import"></i> Import Data Gaji</h4>
    </div>
    <a href="<?= BASE_URL ?>/payroll" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card-custom">
    <div class="card-body p-4">

    <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/import/prosesImport" method="POST" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Nama Periode</label>
                    <input type="text" name="nama_periode" class="form-control" placeholder="Contoh: MEI - JUNI 2026" required>
                    
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Mulai Periode</label>
                <input type="date" name="tanggal_mulai" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Selesai Periode</label>
                <input type="date" name="tanggal_selesai" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">File Excel </label>
                    <input type="file" name="file_excel" class="form-control" accept=".xls,.xlsx" required>
                </div>
                <div class="col-md-12 d-flex gap-2 pt-2">
                    <button type="submit" class="btn btn-sigma"><i class="fas fa-file-import"></i> Import Data</button>
                    <a href="<?= BASE_URL ?>/payroll" class="btn btn-light border">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
