<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-stethoscope"></i> Edit MCU</h4>
    <a href="<?= BASE_URL ?>/crew/detail/<?= $mcu['id_crew'] ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card-custom">
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/mcu/update/<?= $mcu['id_mcu'] ?>" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Derajat Kesehatan</label>
                    <select name="derajat_kesehatan" class="form-select">
                        <option value="">Pilih</option>
                        <?php foreach (['P1', 'P2', 'P3', 'P4', 'P5'] as $d): ?>
                        <option value="<?= $d ?>" <?= ($mcu['derajat_kesehatan'] ?? '') == $d ? 'selected' : '' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Tanggal Expired</label>
                    <input type="date" name="expired" class="form-control" value="<?= $mcu['expired'] ?>">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-semibold">Catatan</label>
                    <textarea name="catatan" class="form-control" rows="2"><?= htmlspecialchars($mcu['catatan'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">File</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <?php if (!empty($mcu['file'])): ?>
                        <small class="text-muted">File saat ini: <?= $mcu['file'] ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-sigma"><i class="fas fa-save"></i> Update MCU</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
