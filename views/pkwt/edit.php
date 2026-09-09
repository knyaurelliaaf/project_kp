<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-file-contract"></i> Edit PKWT</h4>
    <a href="<?= BASE_URL ?>/pkwt" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali ke PKWT</a>
</div>

<div class="card-custom">
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/pkwt/update/<?= $pkwt['id_pkwt'] ?>" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control" value="<?= $pkwt['tanggal_mulai'] ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Tanggal Berakhir</label>
                    <input type="date" name="tanggal_berakhir" class="form-control" value="<?= $pkwt['tanggal_berakhir'] ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">File</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <?php if (!empty($pkwt['file'])): ?>
                        <small class="text-muted">File saat ini: <?= $pkwt['file'] ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-sigma"><i class="fas fa-save"></i> Update PKWT</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
