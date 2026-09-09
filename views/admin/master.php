<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-database"></i> Master Data</h4>
</div>

<div class="row g-3">
    <!-- JENIS SURAT -->
    <div class="col-xl-8 col-lg-7 col-md-12">
        <div class="table-card">
            <div class="table-head">
                <div class="chart-title"><i class="fas fa-envelope chart-icon-red"></i> Jenis Surat</div>
            </div>
            <div class="tw">
                <table class="master-jenis-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Surat</th>
                            <th>Format Nomor</th>
                            <th>Keterangan</th>
                            <th class="th-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($jenis_surat && $jenis_surat->num_rows > 0): ?>
                            <?php while ($j = $jenis_surat->fetch_assoc()): ?>
                            <tr>
                                <td><span class="rtag"><?= htmlspecialchars($j['kode']) ?></span></td>
                                <td><strong><?= htmlspecialchars($j['nama_surat']) ?></strong></td>
                                <td class="format-cell"><code><?= htmlspecialchars($j['format_nomor']) ?></code></td>
                                <td class="td-sm"><?= htmlspecialchars($j['keterangan'] ?? '-') ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/deleteJenisSurat/<?= $j['id_jenis'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus jenis surat ini?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="td-empty">Belum ada jenis surat</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAMBAH JENIS SURAT -->
    <div class="col-xl-4 col-lg-5 col-md-12">
        <div class="card-custom">
            <div class="card-body p-4">
                <h5 class="mb-3"><i class="fas fa-plus-circle"></i> Tambah Jenis Surat</h5>
                <form method="POST" action="<?= BASE_URL ?>/admin/storeJenisSurat">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kode</label>
                        <input type="text" name="kode" class="form-control" placeholder="Contoh: SR, CO, MOC" required>
                        <small class="text-muted">Kode unik, maks 10 karakter. Contoh: SPM, SKA, MM</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Surat</label>
                        <input type="text" name="nama_surat" class="form-control" placeholder="Service Request" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Format Nomor</label>
                        <input type="text" name="format_nomor" class="form-control" placeholder="{kode}/{rig}/ADK/SUB GDAP/{tahun}/{bulan}/{no}" required>
                        <small class="text-muted">
                            Placeholder: <code>{kode}</code> <code>{rig}</code> <code>{tahun}</code> <code>{bulan}</code> <code>{no}</code>
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Deskripsi surat..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-sigma w-100"><i class="fas fa-save"></i> Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
