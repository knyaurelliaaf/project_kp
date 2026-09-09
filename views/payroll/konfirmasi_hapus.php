<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-exclamation-triangle" style="color:#c2507a"></i> Hapus Data Periode</h4>
</div>

<div class="card-custom" style="max-width:600px;">
    <div class="card-body p-4">
        <div class="alert alert-danger">
            <strong>Peringatan — tindakan ini tidak bisa dibatalkan.</strong>
            <p class="mb-0 mt-2">
                Kamu akan menghapus <strong><?= $jumlah ?> slip gaji</strong>
                untuk periode <strong><?= htmlspecialchars($periode) ?></strong>,
                Rig <strong><?= htmlspecialchars($kodeRig) ?></strong> saja
                (rig lain di periode yang sama TIDAK ikut terhapus).
            </p>
        </div>

        <p>Biasanya dipakai kalau ternyata salah upload file Excel rig ini dan perlu import ulang dari awal.</p>

        <form method="POST" action="<?= BASE_URL ?>/payroll/hapusPeriode">
            <input type="hidden" name="periode" value="<?= htmlspecialchars($periode) ?>">
            <input type="hidden" name="id_rig" value="<?= $idRig ?>">

            <label class="form-label fw-semibold">
                Ketik ulang nama periode di atas untuk konfirmasi:
            </label>
            <input type="text" name="konfirmasi" class="form-control mb-3"
                   placeholder="<?= htmlspecialchars($periode) ?>" required autocomplete="off">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Ya, Hapus Permanen
                </button>
                <a href="<?= BASE_URL ?>/payroll" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
