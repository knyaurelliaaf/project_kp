<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-edit"></i> Edit Crew</h4>
    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card-custom">
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/crew/update/<?= $crew['id_crew'] ?>" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Nama</label>
                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($crew['nama']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Posisi</label>
                    <input type="text" name="posisi" class="form-control" value="<?= htmlspecialchars($crew['posisi']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Crew</label>
                    <select name="crew" class="form-select">
                        <option value="">Pilih Crew</option>
                        <option value="A" <?= ($crew['crew'] ?? '') == 'A' ? 'selected' : '' ?>>Crew A</option>
                        <option value="B" <?=($crew['crew'] ?? '') == 'B' ? 'seleceted' : '' ?>>Crew B</option>
                        <option value="C" <?=($crew['crew'] ?? '') == 'C' ? 'selected' : '' ?>>Crew C</option>
                    </select> 
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">NIK KTP</label>
                    <input type="text" name="nik_ktp" class="form-control" value="<?= htmlspecialchars($crew['nik_ktp'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">No. Telp</label>
                    <input type="text" name="no_telp" class="form-control" value="<?= htmlspecialchars($crew['no_telp'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($crew['email'] ?? '') ?>">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-semibold">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($crew['alamat'] ?? '') ?></textarea>
                </div>
                <?php if ($isSuperAdmin): ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Rig</label>
                    <select name="id_rig" class="form-select">
                        <?php while ($rig = $rigs->fetch_assoc()): ?>
                        <option value="<?= $rig['id_rig'] ?>" <?= $crew['id_rig'] == $rig['id_rig'] ? 'selected' : '' ?>><?= $rig['kode_rig'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status_aktif" class="form-select">
                        <option value="aktif" <?= ($crew['status_aktif'] ?? '') == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="nonaktif" <?= ($crew['status_aktif'] ?? '') == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Foto</label>
                    <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png">
                    <?php if (!empty($crew['foto'])): ?>
                        <small class="text-muted">Foto saat ini: <?= $crew['foto'] ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-sigma"><i class="fas fa-save"></i> Update</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>