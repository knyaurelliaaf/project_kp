<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="ag-header-title"><i class="fas fa-user-cog ag-header-icon me-2"></i> Edit Profil & Pengaturan Akun Saya</h4>
</div>

<?php $flash = Helper::getFlash(); if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] == 'error' ? 'danger' : 'success' ?> alert-dismissible fade show mb-3" role="alert">
        <?= $flash['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card-custom">
            <div class="card-body p-4">
                <form method="POST" action="<?= BASE_URL ?>/auth/updateProfile">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Utama Akun</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required placeholder="nama@perusahaan.com">
                        <small class="text-muted">* Anda dapat mengubah email dummy menjadi email resmi/pribadi Anda di sini.</small>
                    </div>



                    <hr class="my-4" style="border-color: #775537;">

                    <h5 class="fw-bold mb-3" style="color: #775537;"><i class="fas fa-key me-2"></i> Ubah Password Saya</h5>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Saat Ini (Lama)</label>
                        <input type="password" name="password_lama" class="form-control" placeholder="Masukkan password saat ini jika ingin ganti password">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Baru</label>
                        <input type="password" name="password_baru" class="form-control" placeholder="Masukkan password baru">
                        <small class="text-muted">* <strong>Kosongkan password lama & baru</strong> jika Anda hanya ingin mengubah Nama atau Email.</small>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-ag-primary btn-lg">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
