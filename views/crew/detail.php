<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-user"></i> Detail Crew</h4>
    <a href="<?= BASE_URL ?>/crew" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<!-- Info crew -->
<div class="card-custom mb-3">
    <div class="card-body p-4">
        <div class="row g-4 align-items-start">

            <!-- Foto -->
            <div class="col-auto text-center">
                <?php if (!empty($crew['foto'])): ?>
                    <a href="javascript:void(0)" onclick="openLightbox('<?= BASE_URL ?>/public/uploads/<?= $crew['foto'] ?>')" class="photo-link">
                        <img src="<?= BASE_URL ?>/public/uploads/<?= $crew['foto'] ?>" class="crew-photo" alt="Foto Crew">
                    </a>
                <?php else: ?>
                    <div class="crew-photo-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="col">

                <!-- Header: nama, posisi, status, edit -->
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 border-bottom pb-3 mb-3">
                    <div>
                        <h5 class="mb-1 fw-semibold"><?= $crew['nama'] ?></h5>
                        <div class="d-flex align-items-center flex-wrap gap-2 fs-6">
                            <span class="text-muted"><?= $crew['posisi'] ?? '-' ?></span>
                            <span class="rtag"><?= $crew['kode_rig'] ?></span>
                            <?= ($crew['status_aktif'] ?? 'aktif') == 'aktif' ? '<span class="stbadge st-c">Aktif</span>' : '<span class="stbadge st-n">Nonaktif</span>' ?>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/crew/edit/<?= $crew['id_crew'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i> Edit Crew</a>
                </div>

                <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">
                    <div class="col border-bottom pb-2">
                        <span class="text-muted detail-label">Crew</span>
                        <span class="fw-medium"><?= $crew['crew'] ?? '-' ?></span>
                    </div>
                    <div class="col border-bottom pb-2 detail-divider">
                        <span class="text-muted detail-label">NIK KTP</span>
                        <span class="fw-medium"><?= $crew['nik_ktp'] ?? '-' ?></span>
                    </div>
                    <div class="col border-bottom pb-2">
                        <span class="text-muted detail-label">No. Telp</span>
                        <span class="fw-medium"><?= $crew['no_telp'] ?? '-' ?></span>
                    </div>
                    <div class="col border-bottom pb-2 detail-divider">
                        <span class="text-muted detail-label">Email</span>
                        <span class="fw-medium"><?= $crew['email'] ?? '-' ?></span>
                    </div>
                    <div class="col-12 border-bottom pb-2">
                        <span class="text-muted detail-label">Alamat</span>
                        <span class="fw-medium"><?= $crew['alamat'] ?? '-' ?></span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Dokumen section -->
<div class="row crew-doc-grid">
    <!-- Badge -->
    <div class="col-md-6 mb-3">
        <div class="table-card">
            <div class="table-head">
                <div class="chart-title"><i class="fas fa-id-card chart-icon-red"></i> Badge</div>
                <a href="<?= BASE_URL ?>/badge/create/<?= $crew['id_crew'] ?>" class="btn btn-sm btn-sigma"><i class="fas fa-plus"></i> Perbarui</a>
            </div>
            <div class="tw">
                <table>
                    <thead>
                        <tr>
                            <th>No Badge</th>
                            <th>Expired</th>
                            <th>Status</th>
                            <th>File</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($badges && $badges->num_rows > 0): ?>
                            <?php while ($b = $badges->fetch_assoc()):
                                $sisa = Helper::sisaHari($b['tanggal_expired']);
                                $statusClass = $sisa < 0 ? 'dex' : ($sisa <= 30 ? 'dsn' : 'dok');
                                $statusText = $sisa < 0 ? 'Expired' : ($sisa <= 30 ? $sisa . ' hari' : 'Valid');
                            ?>
                                <tr>
                                    <td><?= $b['nomor_badge'] ?? '-' ?></td>
                                    <td class="td-sm"><?= $b['tanggal_expired'] ? date('d/m/Y', strtotime($b['tanggal_expired'])) : '-' ?></td>
                                    <td><span class="db <?= $statusClass ?>"><?= $statusText ?></span></td>
                                    <td>
                                        <?php if (!empty($b['file'])): ?>
                                            <a href="<?= BASE_URL ?>/public/uploads/<?= $b['file'] ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-download"></i></a>
                                        <?php else: ?>
                                            <span class="td-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/badge/edit/<?= $b['id_badge'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <a href="<?= BASE_URL ?>/badge/delete/<?= $b['id_badge'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus badge?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="td-empty">Belum ada data badge</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MCU -->
    <div class="col-md-6 mb-3">
        <div class="table-card">
            <div class="table-head">
                <div class="chart-title"><i class="fas fa-stethoscope chart-icon-red"></i> MCU</div>
                <a href="<?= BASE_URL ?>/mcu/create/<?= $crew['id_crew'] ?>" class="btn btn-sm btn-sigma"><i class="fas fa-plus"></i> Perbarui</a>
            </div>
            <div class="tw">
                <table>
                    <thead>
                        <tr>
                            <th>Derajat</th>
                            <th>Expired</th>
                            <th>Status</th>
                            <th>File</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($mcus && $mcus->num_rows > 0): ?>
                            <?php while ($m = $mcus->fetch_assoc()):
                                $sisa = Helper::sisaHari($m['expired']);
                                $statusClass = $sisa < 0 ? 'dex' : ($sisa <= 30 ? 'dsn' : 'dok');
                                $statusText = $sisa < 0 ? 'Expired' : ($sisa <= 30 ? $sisa . ' hari' : 'Valid');
                            ?>
                                <tr>
                                    <td><?= $m['derajat_kesehatan'] ?? '-' ?></td>
                                    <td class="td-sm"><?= $m['expired'] ? date('d/m/Y', strtotime($m['expired'])) : '-' ?></td>
                                    <td><span class="db <?= $statusClass ?>"><?= $statusText ?></span></td>
                                    <td>
                                        <?php if (!empty($m['file'])): ?>
                                            <a href="<?= BASE_URL ?>/public/uploads/<?= $m['file'] ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-download"></i></a>
                                        <?php else: ?>
                                            <span class="td-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/mcu/edit/<?= $m['id_mcu'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <a href="<?= BASE_URL ?>/mcu/delete/<?= $m['id_mcu'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus MCU?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="td-empty">Belum ada data MCU</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sertifikat -->
    <div class="col-md-6 mb-3">
        <div class="table-card">
            <div class="table-head">
                <div class="chart-title"><i class="fas fa-certificate chart-icon-red"></i> Sertifikat</div>
                <a href="<?= BASE_URL ?>/sertifikat/create/<?= $crew['id_crew'] ?>" class="btn btn-sm btn-sigma"><i class="fas fa-plus"></i> Perbarui</a>
            </div>
            <div class="tw">
                <table>
                    <thead>
                        <tr>
                            <th>Jenis</th>
                            <th>Expired</th>
                            <th>Status</th>
                            <th>File</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($sertifikats && $sertifikats->num_rows > 0): ?>
                            <?php while ($s = $sertifikats->fetch_assoc()):
                                $sisa = Helper::sisaHari($s['tanggal_expired']);
                                $statusClass = $sisa < 0 ? 'dex' : ($sisa <= 30 ? 'dsn' : 'dok');
                                $statusText = $sisa < 0 ? 'Expired' : ($sisa <= 30 ? $sisa . ' hari' : 'Valid');
                            ?>
                                <tr>
                                    <td><?= $s['jenis'] ?></td>
                                    <td class="td-sm"><?= $s['tanggal_expired'] ? date('d/m/Y', strtotime($s['tanggal_expired'])) : '-' ?></td>
                                    <td><span class="db <?= $statusClass ?>"><?= $statusText ?></span></td>
                                    <td>
                                        <?php if (!empty($s['file'])): ?>
                                            <a href="<?= BASE_URL ?>/public/uploads/<?= $s['file'] ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-download"></i></a>
                                        <?php else: ?>
                                            <span class="td-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/sertifikat/edit/<?= $s['id_sertifikat'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <a href="<?= BASE_URL ?>/sertifikat/delete/<?= $s['id_sertifikat'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus sertifikat?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="td-empty">Belum ada sertifikat</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PKWT -->
    <div class="col-md-6 mb-3">
        <div class="table-card">
            <div class="table-head">
                <div class="chart-title"><i class="fas fa-file-contract chart-icon-red"></i> PKWT</div>
                <a href="<?= BASE_URL ?>/pkwt/create/<?= $crew['id_crew'] ?>" class="btn btn-sm btn-sigma"><i class="fas fa-plus"></i> Perbarui</a>
            </div>
            <div class="tw">
                <table>
                    <thead>
                        <tr>
                            <th>Mulai</th>
                            <th>Berakhir</th>
                            <th>Status</th>
                            <th>File</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pkwts && $pkwts->num_rows > 0): ?>
                            <?php while ($p = $pkwts->fetch_assoc()):
                                $sisa = Helper::sisaHari($p['tanggal_berakhir']);
                                $statusClass = $sisa < 0 ? 'dex' : ($sisa <= 30 ? 'dsn' : 'dok');
                                $statusText = $sisa < 0 ? 'Expired' : ($sisa <= 30 ? $sisa . ' hari' : 'Valid');
                            ?>
                                <tr>
                                    <td class="td-sm"><?= $p['tanggal_mulai'] ? date('d/m/Y', strtotime($p['tanggal_mulai'])) : '-' ?></td>
                                    <td class="td-sm"><?= $p['tanggal_berakhir'] ? date('d/m/Y', strtotime($p['tanggal_berakhir'])) : '-' ?></td>
                                    <td><span class="db <?= $statusClass ?>"><?= $statusText ?></span></td>
                                    <td>
                                        <?php if (!empty($p['file'])): ?>
                                            <a href="<?= BASE_URL ?>/public/uploads/<?= $p['file'] ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-download"></i></a>
                                        <?php else: ?>
                                            <span class="td-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/pkwt/edit/<?= $p['id_pkwt'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <a href="<?= BASE_URL ?>/pkwt/delete/<?= $p['id_pkwt'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus PKWT?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="td-empty">Belum ada data PKWT</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

<!-- Lightbox -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    <img id="lightbox-img" src="" alt="Foto">
</div>

<script>
    function openLightbox(url) {
        document.getElementById('lightbox-img').src = url;
        document.getElementById('lightbox').classList.add('show');
    }

    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('show');
    }
</script>
