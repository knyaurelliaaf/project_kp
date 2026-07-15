<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-user-plus"></i> Tambah Crew</h4>
    <a href="<?= BASE_URL ?>/crew" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card-custom">
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/crew/store">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Nama</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Posisi</label>
                    <select name="posisi" class="form-select">
                        <option value="">Pilih Posisi</option>
                        <?php
                        $posisiModel = $this->model('MasterPosisiModel');
                        $posisiList = $posisiModel->all();
                        while ($p = $posisiList->fetch_assoc()):
                        ?>
                        <option value="<?= $p['nama_posisi'] ?>"><?= $p['nama_posisi'] ?></option>
                        <?php endwhile; ?>
                </select>
                </div>
                <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Crew</label>
                <select name="regu" class="form-select">
                    <option value="">Pilih Crew</option>
                    <option value="A">Crew A</option>
                    <option value="B">Crew B</option>
                    <option value="C">Crew C</option>
                </select>
            </div>
                <?php if ($isSuperAdmin): ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Rig</label>
                    <select name="id_rig" class="form-select" required>
                        <?php while ($rig = $rigs->fetch_assoc()): ?>
                        <option value="<?= $rig['id_rig'] ?>"><?= $rig['kode_rig'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php else: ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Rig</label>
                    <input type="text" class="form-control" value="<?= $kode_rig ?>" disabled>
                </div>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-sigma"><i class="fas fa-save"></i> Simpan</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>