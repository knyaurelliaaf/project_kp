<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-user-plus"></i> Tambah User</h4>
    <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card-custom">
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/admin/storeUser">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Nama</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Role</label>
                    <!-- REVISI: tambah opsi 'payroll', dan sembunyiin rig-section
                         untuk super_admin MAUPUN payroll (dua-duanya gak perlu dibatasi per rig) -->
                    <select name="role" class="form-select" onchange="document.getElementById('rig-section').style.display = (this.value == 'super_admin' || this.value == 'payroll') ? 'none' : 'block'">
                        <option value="admin_rig">Admin Rig</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="payroll">Payroll</option>
                    </select>
                </div>

                <div class="col-md-12 mb-3" id="rig-section">
                    <label class="form-label fw-semibold">Rig yang Dikelola (bisa pilih lebih dari satu)</label>
                    <div class="row">
                        <?php while ($rig = $rigs->fetch_assoc()): ?>
                        <div class="col-md-3 mb-2">
                            <label class="d-flex align-items-center gap-2">
                                <input type="checkbox" name="id_rig[]" value="<?= $rig['id_rig'] ?>">
                                <span><?= $rig['kode_rig'] ?></span>
                            </label>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-sigma"><i class="fas fa-save"></i> Simpan</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>