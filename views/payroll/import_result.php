<?php
require_once __DIR__ . '/../layout/header.php';
$periodeTarget = $namaPeriode ?? $periodeTerbaru ?? '';
$idRigTarget   = $idRigSelect ?? $idRigTerbaru ?? '';

$displayRigName = 'Belum Ditentukan';
if (!empty($idRigTarget)) {
    $rigModel = new RigModel();
    $r = $rigModel->find((int)$idRigTarget);
    if ($r) {
        $displayRigName = !empty($r['nama_rig']) ? $r['nama_rig'] : $r['kode_rig'];
    }
} elseif (!empty($kodeRig) && $kodeRig !== 'Belum Ditentukan') {
    $displayRigName = $kodeRig;
}

if ($displayRigName !== 'Belum Ditentukan' && stripos($displayRigName, 'Rig') === false) {
    $displayRigName = 'Rig ' . $displayRigName;
}
?>

<div class="container py-4">
    <div class="card-custom mb-4" style="border: 2px solid #775537 !important;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h4 class="fw-bold mb-0" style="color: #775537;">
                    <i class="fas fa-check-circle text-success me-2"></i>Hasil Import Data Slip Gaji
                </h4>
                <div>
                    <a href="<?= BASE_URL ?>/payroll/daftarKaryawan?periode=<?= urlencode($periodeTarget) ?>&id_rig=<?= $idRigTarget ?>" class="btn btn-ag-primary">
                        <i class="fas fa-list-ul me-1"></i> Lihat Hasil di Halaman Slip Gaji
                    </a>
                    <a href="<?= BASE_URL ?>/import/formImport" class="btn btn-outline-secondary ms-1">
                        <i class="fas fa-file-import me-1"></i> Import Periode Lain
                    </a>
                </div>
            </div>

            <!-- INFO SUMMARY BADGES -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded border">
                        <small class="text-muted d-block mb-1">Periode Gaji:</small>
                        <strong class="fs-5" style="color: #775537;"><?= htmlspecialchars($periodeTarget) ?></strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded border">
                        <small class="text-muted d-block mb-1">Status Rig:</small>
                        <?php if ($displayRigName !== 'Belum Ditentukan'): ?>
                            <span class="badge bg-success fs-6"><i class="fas fa-industry me-1"></i><?= htmlspecialchars($displayRigName) ?></span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark fs-6"><i class="fas fa-exclamation-circle me-1"></i>Belum Ditentukan</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded border">
                        <small class="text-muted d-block mb-1">Total Berhasil Di-import:</small>
                        <span class="badge bg-info text-dark fs-6 px-3 py-1"><?= count($sukses ?? []) ?> Karyawan</span>
                    </div>
                </div>
            </div>

            <!-- ALERT DUPLIKAT JIKA ADA -->
            <?php if (!empty($duplikat)): ?>
                <div class="alert alert-warning mb-4">
                    <h6 class="fw-bold"><i class="fas fa-exclamation-triangle me-1"></i> <?= count($duplikat) ?> Data dilewati (Sudah ada di periode ini):</h6>
                    <ul class="mb-0 small">
                        <?php foreach ($duplikat as $item): ?>
                            <li><?= htmlspecialchars($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- TABLE RINCIAN DATA YANG BERHASIL DI-IMPORT -->
            <h5 class="fw-bold mb-3" style="color: #775537;"><i class="fas fa-users me-2"></i>Rincian Karyawan Yang Di-import</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #C0DDDA;">
                        <tr>
                            <th style="color:#775537;">No. Urut</th>
                            <th style="color:#775537;">Nama Karyawan</th>
                            <th style="color:#775537;">Badge</th>
                            <th style="color:#775537;">Jabatan</th>
                            <th style="color:#775537;" class="text-end">Gaji Bersih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($detailImport)): ?>
                            <?php foreach ($detailImport as $d): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($d['no_urut']) ?></span></td>
                                    <td><strong style="color: #775537;"><?= htmlspecialchars($d['nama']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($d['badge'] ?: '-') ?></code></td>
                                    <td><?= htmlspecialchars($d['jabatan']) ?></td>
                                    <td class="text-end fw-bold text-success">
                                        Rp <?= number_format((float)$d['gaji_bersih'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach ($sukses as $item): ?>
                                <tr>
                                    <td colspan="5"><?= htmlspecialchars($item) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- CTA BOTTOM BUTTON -->
            <div class="mt-4 text-center">
                <a href="<?= BASE_URL ?>/payroll/daftarKaryawan?periode=<?= urlencode($periodeTarget) ?>&id_rig=<?= $idRigTarget ?>" class="btn btn-ag-primary btn-lg px-4">
                    <i class="fas fa-list-ul me-2"></i> Buka Halaman Slip Gaji Sekarang
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>