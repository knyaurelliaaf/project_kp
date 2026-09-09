<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-users-cog"></i> Kelola User</h4>
    <a href="<?= BASE_URL ?>/admin/createUser" class="btn btn-sigma">
        <i class="fas fa-plus me-1"></i> Tambah User
    </a>
</div>

<?php $flash = Helper::getFlash(); if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] == 'error' ? 'danger' : 'success' ?> alert-dismissible fade show mb-3" role="alert">
        <?= $flash['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="table-card">
    <div class="tw">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Rig</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users && $users->num_rows > 0): $no = 1; ?>
                    <?php while ($u = $users->fetch_assoc()): ?>
                    <?php
                        $roleBadge = match ($u['role']) {
                            'super_admin' => '<span class="badge bg-danger">Super Admin</span>',
                            'payroll'     => '<span class="badge bg-primary">Payroll Admin</span>',
                            'payroll_wa'  => '<span class="badge bg-success">Payroll WA</span>',
                            'admin_gaji'  => '<span class="badge bg-success">Admin Gaji</span>',
                            'admin_rig'   => '<span class="badge bg-info text-dark">Admin Rig</span>',
                            default       => '<span class="badge bg-secondary">' . htmlspecialchars($u['role']) . '</span>',
                        };
                    ?>
                    <tr>
                        <td style="color:var(--muted)"><?= $no++ ?></td>
                        <td><strong><?= htmlspecialchars($u['nama']) ?></strong></td>
                        <td><code><?= htmlspecialchars($u['email']) ?></code></td>
                        <td><?= $roleBadge ?></td>
                        <td><?= htmlspecialchars($u['rig_list'] ?: '-') ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?= $u['id_user'] ?>, '<?= addslashes(htmlspecialchars($u['nama'])) ?>', '<?= addslashes(htmlspecialchars($u['email'])) ?>', '<?= addslashes(htmlspecialchars($u['role'])) ?>')">
                                <i class="fas fa-edit me-1"></i> Edit Email / Password
                            </button>
                            <a href="<?= BASE_URL ?>/admin/deleteUser/<?= $u['id_user'] ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')" title="Hapus User">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada user</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit User / Reset Password -->
<div class="modal fade" id="modalEditUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formEditUser" action="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit Email & Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap</label>
                        <input type="text" name="nama" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Utama (Ganti Email User)</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required placeholder="nama@perusahaan.com">
                        <small class="text-muted">* Ubah email dummy ke email resmi user di sini.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role / Akses</label>
                        <select name="role" id="edit_role" class="form-select" required>
                            <option value="super_admin">Super Admin</option>
                            <option value="payroll">Payroll Admin</option>
                            <option value="admin_gaji">Payroll (Admin Gaji WA)</option>
                            <option value="admin_rig">Admin Rig</option>
                        </select>
                    </div>
                    <hr class="my-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-danger"><i class="fas fa-key me-1"></i> Reset Password User</label>
                        <input type="password" name="password" class="form-control" placeholder="Ketik password baru jika ingin mereset">
                        <small class="text-muted">* <strong>Kosongkan</strong> jika tidak ingin mereset password user.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editUser(id, nama, email, role) {
    document.getElementById('formEditUser').action = '<?= BASE_URL ?>/admin/updateUser/' + id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    new bootstrap.Modal(document.getElementById('modalEditUser')).show();
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>