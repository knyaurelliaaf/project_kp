<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-user"></i> Detail Crew</h4>
    <a href="<?= $backUrl ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> <?= $backLabel ?></a>
</div>

<!-- Info crew -->
<div class="card-custom mb-3">
    <div class="card-body p-4">
        <div class="crew-detail-row">

            <!-- Kolom 1: Foto -->
            <div class="crew-detail-photo">
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

            <!-- Kolom 2: Info -->
            <div class="crew-detail-info">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                    <div>
                        <h5 class="fw-semibold mb-2 crew-detail-name"><?= $crew['nama'] ?></h5>
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                            <span class="text-muted crew-detail-posisi"><?= $crew['posisi'] ?? '-' ?></span>
                            <span class="rtag"><?= $crew['kode_rig'] ?></span>
                            <?= ($crew['status_aktif'] ?? 'aktif') == 'aktif' ? '<span class="stbadge st-c">Aktif</span>' : '<span class="stbadge st-n">Nonaktif</span>' ?>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/crew/edit/<?= $crew['id_crew'] ?><?= !empty($filterQuery) ? '?'.$filterQuery : '' ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i> Edit Crew</a>
                </div>

                <?php

                function extractTglLahirDariNIK($nik)
                {
                    if (strlen($nik ?? '') < 12) return null;
                    $day = (int)substr($nik, 6, 2);
                    $month = (int)substr($nik, 8, 2);
                    $year = (int)substr($nik, 10, 2);
                    
                    if ($day > 40) $day -= 40;

                    $currentYear = (int)date('y'); // last 2 digits of current year
                    $fullYear = ($year <= $currentYear) ? 2000 + $year : 1900 + $year;

                    if (checkdate($month, $day, $fullYear)) {
                        return date('d/m/Y', strtotime("$fullYear-$month-$day"));
                    }
                    return null;
                }

                $tglLahirDariNIK = extractTglLahirDariNIK($crew['nik_ktp'] ?? '');
                $tglLahirDisplay = $tglLahirDariNIK;
                if (!$tglLahirDariNIK && !empty($crew['tanggal_lahir'])) {
                    $tglLahirDisplay = date('d/m/Y', strtotime($crew['tanggal_lahir']));
                }
                ?>

                <!-- Grid 2 kolom -->
                <div class="crew-info-grid">
                    <!-- Kolom Kiri -->
                    <div class="crew-info-col">
                        <div class="detail-item detail-item-lg">
                            <span class="detail-label">Crew</span>
                            <span class="fw-medium text-nowrap"><?= $crew['crew'] ?? '-' ?></span>
                        </div>
                        <div class="detail-item detail-item-lg">
                            <span class="detail-label">Tempat, Tgl Lahir</span>
                            <span class="fw-medium">
                                <?php
                                $tmpl = trim($crew['tempat_lahir'] ?? '');
                                if ($tmpl !== '' && $tglLahirDisplay) {
                                    echo $tmpl . ', ' . $tglLahirDisplay;
                                } elseif ($tmpl !== '') {
                                    echo $tmpl;
                                } elseif ($tglLahirDisplay) {
                                    echo $tglLahirDisplay;
                                } else {
                                    echo '-';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="detail-item detail-item-lg">
                            <span class="detail-label">No. Telp</span>
                            <span class="fw-medium text-nowrap"><?= $crew['no_telp'] ?? '-' ?></span>
                        </div>
                        <div class="detail-item detail-item-lg">
                            <span class="detail-label">Tanggal Masuk</span>
                            <span class="fw-medium text-nowrap"><?= !empty($crew['tanggal_masuk']) ? date('d/m/Y', strtotime($crew['tanggal_masuk'])) : '-' ?></span>
                        </div>
                    </div>

                    <!-- Kolom Kanan -->
                    <div class="crew-info-col">
                        <div class="detail-item detail-item-lg">
                            <span class="detail-label">NIK KTP</span>
                            <span class="fw-medium text-nowrap"><?= $crew['nik_ktp'] ?? '-' ?></span>
                        </div>
                        <div class="detail-item detail-item-lg detail-item-address">
                            <span class="detail-label">Alamat</span>
                            <span class="fw-medium address-text"><?= $crew['alamat'] ?? '-' ?></span>
                        </div>
                        <div class="detail-item detail-item-lg detail-item-email">
                            <span class="detail-label">Email</span>
                            <span class="fw-medium email-text"><?= $crew['email'] ?? '-' ?></span>
                        </div>
                        <?php if (!empty($crew['tanggal_nonaktif']) || ($crew['status_aktif'] ?? '') == 'nonaktif'): ?>
                            <div class="detail-item detail-item-lg">
                                <span class="detail-label">Tanggal Nonaktif</span>
                                <span class="fw-medium text-danger"><?= !empty($crew['tanggal_nonaktif']) ? date('d/m/Y', strtotime($crew['tanggal_nonaktif'])) : '-' ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <hr style="margin: 20px 0; border-color: var(--p100);">

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
                                             <td class="text-center">
                                                 <?php if (!empty($b['file'])): 
                                                     $ext = strtolower(pathinfo($b['file'], PATHINFO_EXTENSION));
                                                     $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                                     $fileUrl = BASE_URL . '/public/uploads/' . $b['file'];
                                                 ?>
                                                     <div class="d-flex gap-1 justify-content-center">
                                                         <?php if ($isImg): ?>
                                                             <button type="button" onclick="openLightbox('<?= $fileUrl ?>')" class="btn btn-sm btn-outline-success" title="Lihat Foto"><i class="fas fa-eye"></i></button>
                                                         <?php else: ?>
                                                             <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Lihat PDF"><i class="fas fa-eye"></i></a>
                                                         <?php endif; ?>
                                                         <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Unduh File"><i class="fas fa-download"></i></a>
                                                     </div>
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
                                             <td class="text-center">
                                                 <?php if (!empty($m['file'])): 
                                                     $ext = strtolower(pathinfo($m['file'], PATHINFO_EXTENSION));
                                                     $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                                     $fileUrl = BASE_URL . '/public/uploads/' . $m['file'];
                                                 ?>
                                                     <div class="d-flex gap-1 justify-content-center">
                                                         <?php if ($isImg): ?>
                                                             <button type="button" onclick="openLightbox('<?= $fileUrl ?>')" class="btn btn-sm btn-outline-success" title="Lihat Foto"><i class="fas fa-eye"></i></button>
                                                         <?php else: ?>
                                                             <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Lihat PDF"><i class="fas fa-eye"></i></a>
                                                         <?php endif; ?>
                                                         <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Unduh File"><i class="fas fa-download"></i></a>
                                                     </div>
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
                                             <td class="text-center">
                                                 <?php if (!empty($s['file'])): 
                                                     $ext = strtolower(pathinfo($s['file'], PATHINFO_EXTENSION));
                                                     $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                                     $fileUrl = BASE_URL . '/public/uploads/' . $s['file'];
                                                 ?>
                                                     <div class="d-flex gap-1 justify-content-center">
                                                         <?php if ($isImg): ?>
                                                             <button type="button" onclick="openLightbox('<?= $fileUrl ?>')" class="btn btn-sm btn-outline-success" title="Lihat Foto"><i class="fas fa-eye"></i></button>
                                                         <?php else: ?>
                                                             <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Lihat PDF"><i class="fas fa-eye"></i></a>
                                                         <?php endif; ?>
                                                         <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Unduh File"><i class="fas fa-download"></i></a>
                                                     </div>
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
                                             <td class="text-center">
                                                 <?php if (!empty($p['file'])): 
                                                     $ext = strtolower(pathinfo($p['file'], PATHINFO_EXTENSION));
                                                     $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                                     $fileUrl = BASE_URL . '/public/uploads/' . $p['file'];
                                                 ?>
                                                     <div class="d-flex gap-1 justify-content-center">
                                                         <?php if ($isImg): ?>
                                                             <button type="button" onclick="openLightbox('<?= $fileUrl ?>')" class="btn btn-sm btn-outline-success" title="Lihat Foto"><i class="fas fa-eye"></i></button>
                                                         <?php else: ?>
                                                             <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Lihat PDF"><i class="fas fa-eye"></i></a>
                                                         <?php endif; ?>
                                                         <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Unduh File"><i class="fas fa-download"></i></a>
                                                     </div>
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