<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-users-cog"></i> Kelola User</h4>
    <a href="<?= BASE_URL ?>/admin/createUser" class="btn btn-sigma">
        <i class="fas fa-plus"></i> Tambah User
    </a>
</div>

<div class="table-card">
    <div class="tw">
        <table>
            <thead>
                <tr><th>#</th><th>Nama</th><th>Email</th><th>Role</th><th>Rig</th></tr>
            </thead>
            <tbody>
                <?php if ($users && $users->num_rows > 0): $no = 1; ?>
                    <?php while ($u = $users->fetch_assoc()): ?>
                    <?php
                        $roleBadge = match ($u['role']) {
                            'super_admin' => '<span class="stbadge st-n">Super Admin</span>',
                            'payroll'     => '<span class="stbadge st-p">Payroll</span>',
                            'admin_rig'   => '<span class="stbadge st-c">Admin Rig</span>',
                            default       => '<span class="stbadge">' . htmlspecialchars($u['role']) . '</span>',
                        };
                    ?>
                    <tr>
                        <td style="color:var(--muted)"><?= $no++ ?></td>
                        <td><strong><?= $u['nama'] ?></strong></td>
                        <td><?= $u['email'] ?></td>
                        <td><?= $roleBadge ?></td>
                        <td><?= $u['rig_list'] ?: '-' ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada user</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>