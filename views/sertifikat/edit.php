<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-certificate"></i> Edit Sertifikat</h4>
    <a href="<?= BASE_URL ?>/crew/detail/<?= $sert['id_crew'] ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card-custom">
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/sertifikat/update/<?= (int)$sert['id_sertifikat'] ?>" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Jenis Sertifikat</label>
                    <select name="jenis" class="form-select">
                        <?php foreach (['IADC','TKBT 1','TKBT 2','OLB','OMB','JB','ASME WELDER','LAINNYA','K3 TEKNISI'] as $j): ?>
                        <option value="<?= htmlspecialchars($j) ?>" <?= ($sert['jenis'] ?? '') == $j ? 'selected' : '' ?>><?= htmlspecialchars($j) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Tanggal Expired</label>
                    <input type="date" name="tanggal_expired" class="form-control" value="<?= htmlspecialchars($sert['tanggal_expired'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">File</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <?php if (!empty($sert['file'])): ?>
                        <small class="text-muted">File saat ini: <?= htmlspecialchars($sert['file']) ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-sigma"><i class="fas fa-save"></i> Update Sertifikat</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>