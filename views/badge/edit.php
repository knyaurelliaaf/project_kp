<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-id-card"></i> Edit Badge</h4>
    <a href="<?= BASE_URL ?>/crew/detail/<?= $badge['id_crew'] ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card-custom">
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/badge/update/<?= $badge['id_badge'] ?>" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Nomor Badge</label>
                    <input type="text" name="nomor_badge" class="form-control" value="<?= htmlspecialchars($badge['nomor_badge'] ?? '') ?>" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Tanggal Expired</label>
                    <input type="date" name="tanggal_expired" class="form-control" value="<?= $badge['tanggal_expired'] ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">File (Scan Badge)</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <?php if (!empty($badge['file'])): ?>
                        <small class="text-muted">File saat ini: <?= $badge['file'] ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-sigma"><i class="fas fa-save"></i> Update Badge</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>