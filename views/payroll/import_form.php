<?php
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container py-4">
    <!-- FORM IMPORT -->
    <div class="card-custom mb-4" style="border: 2px solid #775537 !important;">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-3" style="color: #775537;">
                <i class="fas fa-file-import me-2"></i>Import Data Gaji dari Excel
            </h4>

            <?php $flash = Helper::getFlash(); if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] == 'error' ? 'danger' : 'success' ?> alert-dismissible fade show mb-3" role="alert">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/import/prosesImport" method="POST" enctype="multipart/form-data" class="mt-3">
                <div class="row g-3 mb-3">
                    <!-- NAMA PERIODE -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark"><i class="fas fa-calendar-alt me-1"></i> Nama Periode <span class="text-danger">*</span></label>
                        <input type="text" name="nama_periode" class="form-control" placeholder="Cth: MEI - JUNI 2026" required>
                        <small class="text-muted">Masukkan nama periode gaji sesuai laporan Excel.</small>
                    </div>

                    <!-- TENTUKAN RIG -->
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold text-dark mb-0"><i class="fas fa-industry me-1"></i> Tentukan Rig </label>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalTambahRig">
                                <i class="fas fa-plus me-1"></i>Tambah Rig Baru
                            </button>
                        </div>
                        <select name="id_rig" class="form-select">
                            <option value="">-- Belum Ditentukan --</option>
                            <?php if (!empty($daftarRig)): ?>
                                <?php foreach ($daftarRig as $r): 
                                    $displayName = !empty($r['nama_rig']) ? $r['nama_rig'] : $r['kode_rig'];
                                    if (stripos($displayName, 'Rig') === false) {
                                        $displayName = 'Rig ' . $displayName;
                                    }
                                ?>
                                    <option value="<?= $r['id_rig'] ?>">
                                        <?= htmlspecialchars($displayName) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted"></small>
                    </div>
                </div>

                <!-- FILE EXCEL -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark"><i class="fas fa-file-excel me-1"></i> File Excel (.xls, .xlsx) <span class="text-danger">*</span></label>
                    <input type="file" name="file_excel" class="form-control" accept=".xls,.xlsx" required>
                    <small class="text-muted">Pastikan file Excel memiliki Sheet (Maksimal 10MB).</small>
                </div>

                <!-- BUTTONS -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-ag-primary px-4">
                        <i class="fas fa-upload me-1"></i> Import Data Sekarang
                    </button>
                    <a href="<?= BASE_URL ?>/payroll/daftarKaryawan" class="btn btn-outline-secondary px-3">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Slip Gaji
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- RIWAYAT IMPORT & AKSES LANGSUNG SLIP GAJI -->
    <div class="card-custom">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0" style="color: #775537;">
                    <i class="fas fa-history me-2"></i>Riwayat Import Data Gaji
                </h5>
                <span class="badge bg-secondary">Total: <?= count($historyImport ?? []) ?> Periode</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #C0DDDA;">
                        <tr>
                            <th style="color:#775537;">#</th>
                            <th style="color:#775537;">Periode Gaji</th>
                            <th style="color:#775537;">Rig</th>
                            <th style="color:#775537;" class="text-center">Jumlah Karyawan</th>
                            <th style="color:#775537;" class="text-end">Total Gaji Bersih</th>
                            <th style="color:#775537;">Waktu Import</th>
                            <th style="color:#775537;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($historyImport)): $no = 1; ?>
                            <?php foreach ($historyImport as $h): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <strong style="color: #775537;"><?= htmlspecialchars($h['periode']) ?></strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($h['kode_rig'])): ?>
                                            <span class="badge bg-success"><i class="fas fa-industry me-1"></i>Rig <?= htmlspecialchars($h['kode_rig']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-circle me-1"></i>Belum Ditentukan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info text-dark fs-6 px-3 py-1"><?= (int)$h['total_karyawan'] ?> Karyawan</span>
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        Rp <?= number_format((float)$h['total_gaji'], 0, ',', '.') ?>
                                    </td>
                                    <td class="small text-muted">
                                        <?= !empty($h['created_at']) ? date('d M Y H:i', strtotime($h['created_at'])) : '-' ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= BASE_URL ?>/payroll/daftarKaryawan?periode=<?= urlencode($h['periode']) ?>&id_rig=<?= $h['id_rig'] ?? '' ?>" class="btn btn-sm btn-ag-primary">
                                            <i class="fas fa-eye me-1"></i> Lihat Slip Gaji
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fs-3 d-block mb-2"></i>
                                    Belum ada data gaji yang di-import.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH & KELOLA RIG BARU -->
<div class="modal fade" id="modalTambahRig" tabindex="-1" aria-labelledby="modalTambahRigLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #C0DDDA;">
                <h5 class="modal-title fw-bold" id="modalTambahRigLabel" style="color: #775537;">
                    <i class="fas fa-industry me-2"></i>Kelola &amp; Tambah Rig
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- FORM TAMBAH -->
                <form action="<?= BASE_URL ?>/import/storeRig" method="POST" class="mb-4">
                    <label class="form-label fw-bold">Tambah Rig Baru <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" name="nama_rig" class="form-control" placeholder="Contoh: GW-340 atau Rig GW-340" required>
                        <button type="submit" class="btn btn-ag-primary"><i class="fas fa-plus me-1"></i> Simpan Rig</button>
                    </div>
                </form>

                <hr>

                <!-- DAFTAR RIG SAAT INI -->
                <h6 class="fw-bold mb-2" style="color: #775537;"><i class="fas fa-list me-1"></i> Daftar Rig Aktif</h6>
                <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama Rig</th>
                                <th class="text-center" style="width: 90px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($daftarRig)): ?>
                                <?php foreach ($daftarRig as $r): 
                                    $displayNama = !empty($r['nama_rig']) ? $r['nama_rig'] : 'Rig ' . $r['kode_rig'];
                                    if (stripos($displayNama, 'Rig') === false) {
                                        $displayNama = 'Rig ' . $displayNama;
                                    }
                                ?>
                                    <tr>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($displayNama) ?></td>
                                        <td class="text-center">
                                            <a href="<?= BASE_URL ?>/import/deleteRig/<?= $r['id_rig'] ?>" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="return confirm('Apakah Anda yakin ingin menghapus <?= htmlspecialchars($displayNama) ?>?')">
                                                <i class="fas fa-trash-alt"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted small py-2">Belum ada Rig yang tersimpan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
