<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-edit"></i> Edit Crew</h4>
    <a href="<?= BASE_URL ?>/crew/detail/<?= $crew['id_crew'] ?><?= !empty($filterQuery) ? '?'.$filterQuery : '' ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card-custom">
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/crew/update/<?= $crew['id_crew'] ?><?= !empty($filterQuery) ? '?'.$filterQuery : '' ?>" enctype="multipart/form-data">
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
                        <option value="B" <?= ($crew['crew'] ?? '') == 'B' ? 'selected' : '' ?>>Crew B</option>
                        <option value="C" <?= ($crew['crew'] ?? '') == 'C' ? 'selected' : '' ?>>Crew C</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">NIK KTP</label>
                    <input type="text" name="nik_ktp" class="form-control" id="nikKtp" value="<?= htmlspecialchars($crew['nik_ktp'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="tanggalLahir" class="form-control" value="<?= !empty($crew['tanggal_lahir']) ? $crew['tanggal_lahir'] : '' ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($crew['email'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">No. Telp</label>
                    <input type="text" name="no_telp" class="form-control" value="<?= htmlspecialchars($crew['no_telp'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="form-control" value="<?= htmlspecialchars($crew['tempat_lahir'] ?? '') ?>">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-semibold">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($crew['alamat'] ?? '') ?></textarea>
                </div>
                <?php if ($isSuperAdmin): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Rig</label>
                        <select name="id_rig" class="form-select">
                            <?php foreach ($rigs as $rig): ?>
                                <option value="<?= $rig['id_rig'] ?>" <?= $crew['id_rig'] == $rig['id_rig'] ? 'selected' : '' ?>><?= $rig['kode_rig'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Tanggal / Periode Masuk</label>
                    <input type="date" name="tanggal_masuk" class="form-control" value="<?= !empty($crew['tanggal_masuk']) ? $crew['tanggal_masuk'] : '' ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status_aktif" class="form-select" id="statusAktifSelect" onchange="toggleTanggalNonaktif(this.value)">
                        <option value="aktif" <?= ($crew['status_aktif'] ?? '') == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="nonaktif" <?= ($crew['status_aktif'] ?? '') == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3" id="wrapTanggalNonaktif" style="<?= ($crew['status_aktif'] ?? '') == 'nonaktif' ? '' : 'display:none;' ?>">
                    <label class="form-label fw-semibold">Tanggal Selesai / Nonaktif</label>
                    <input type="date" name="tanggal_nonaktif" id="tanggalNonaktif" class="form-control" value="<?= !empty($crew['tanggal_nonaktif']) ? $crew['tanggal_nonaktif'] : date('Y-m-d') ?>">
                    <small class="text-muted">Otomatis terisi tanggal hari ini saat status dinonaktifkan.</small>
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

<script>
function toggleTanggalNonaktif(val) {
    const wrap = document.getElementById('wrapTanggalNonaktif');
    if (wrap) {
        wrap.style.display = (val === 'nonaktif') ? 'block' : 'none';
    }
}

function extractTglLahirDariNIK(nik) {
    if (!nik || nik.length < 12) return null;
    const day = parseInt(nik.substr(6, 2));
    const month = parseInt(nik.substr(8, 2));
    const year = parseInt(nik.substr(10, 2));
    
    let adjustedDay = day;
    if (adjustedDay > 40) adjustedDay -= 40;
    
    const currentYear = parseInt(new Date().getFullYear().toString().slice(-2));
    const fullYear = (year <= currentYear) ? 2000 + year : 1900 + year;
    
    const date = new Date(fullYear, month - 1, adjustedDay);
    if (date.getFullYear() === fullYear && date.getMonth() === month - 1 && date.getDate() === adjustedDay) {
        return fullYear + '-' + String(month).padStart(2, '0') + '-' + String(adjustedDay).padStart(2, '0');
    }
    return null;
}

document.getElementById('nikKtp')?.addEventListener('input', function() {
    const nik = this.value.trim();
    const tglLahir = extractTglLahirDariNIK(nik);
    if (tglLahir) {
        document.getElementById('tanggalLahir').value = tglLahir;
    }
});

// Auto-fill on page load if NIK already exists
document.addEventListener('DOMContentLoaded', function() {
    const nik = document.getElementById('nikKtp')?.value.trim();
    if (nik && !document.getElementById('tanggalLahir').value) {
        const tglLahir = extractTglLahirDariNIK(nik);
        if (tglLahir) {
            document.getElementById('tanggalLahir').value = tglLahir;
        }
    }
});
</script>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
