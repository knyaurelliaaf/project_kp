<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-id-card"></i> Perpanjang Badge</h4>
    <a href="<?= BASE_URL ?>/crew/detail/<?= $id_crew ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card-custom">
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/badge/store" enctype="multipart/form-data">
            <input type="hidden" name="id_crew" value="<?= $id_crew ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Nomor Badge</label>
                    <input type="text" name="nomor_badge" class="form-control" value="<?= htmlspecialchars($nomor_badge_lama ?? '') ?>" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Tanggal Expired Baru</label>
                    <input type="date" name="tanggal_expired" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">File (Scan Badge)</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                </div>
            </div>
            <button type="submit" class="btn btn-sigma"><i class="fas fa-save"></i> Simpan Badge</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>