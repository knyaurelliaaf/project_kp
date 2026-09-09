<?php require_once __DIR__ . '/../layout/header.php'; ?>



<?php if (empty($daftarPeriode)): ?>

    <!-- EMPTY STATE -->
    <div class="empty-state">
        <div class="icon"><i class="fas fa-file-invoice"></i></div>
        <h4>Belum Ada Data Gaji</h4>
        <p>Mulai import data gaji pertama Anda dari file Excel Summary Sigma.</p>
        <a href="<?= BASE_URL ?>/import/formImport" class="btn-sigma" style="text-decoration:none; display:inline-block; padding:12px 28px;">
            <i class="fas fa-file-import"></i> Import Data Gaji Pertama
        </a>
    </div>

<?php else: ?>

    <!-- HERO / HEADER -->
    <div class="payroll-hero">
        <div class="payroll-hero-left">
            <div class="greeting">Welcome Admin Payroll ADK-6 👋</div>
            <div class="title">Payroll Dashboard</div>
            <div class="sub">
                Terbaru: <strong><?= htmlspecialchars($periodeTerbaru) ?></strong>
                Rig <strong><?= htmlspecialchars($kodeRigTerbaru ?? 'Belum Ditentukan') ?></strong>
            </div>
        </div>
        <div class="payroll-hero-right">
            <div class="payroll-hero-stat">
                <div class="num"><?= (int)($stats['total_periode'] ?? count($daftarPeriode)) ?></div>
                <div class="lbl">Periode</div>
            </div>
            <div class="payroll-hero-divider"></div>
            <div class="payroll-hero-stat">
                <div class="num"><?= (int)($stats['total_karyawan'] ?? 0) ?></div>
                <div class="lbl">Karyawan</div>
            </div>
            <div class="payroll-hero-divider"></div>
            <div class="payroll-hero-stat">
                <div class="num"><?= (int)($stats['total_rig'] ?? 0) ?></div>
                <div class="lbl">Rig</div>
            </div>
        </div>
    </div>

    <!-- QUICK ACTION -->
    <div class="quick-grid" style="margin-top:16px;">
        <div class="quick-card">
            <div class="icon"><i class="fas fa-eye"></i></div>
            <h6>Lihat Slip Gaji Terbaru</h6>
            <p><?= htmlspecialchars($periodeTerbaru) ?> — Rig <?= htmlspecialchars($kodeRigTerbaru ?? 'Belum Ditentukan') ?> (<?= (int)($ringkasan['jumlah_karyawan'] ?? 0) ?> Karyawan)</p>
            <a href="<?= BASE_URL ?>/payroll/daftarKaryawan?periode=<?= urlencode($periodeTerbaru) ?>&id_rig=<?= $idRigTerbaru ?>"
               class="btn-link-custom">
                Lihat Semua <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="quick-card">
            <div class="icon"><i class="fas fa-upload"></i></div>
            <h6>Import Data Baru</h6>
            <p>Tambah data gaji dari file Excel rig lain atau periode baru.</p>
            <a href="<?= BASE_URL ?>/import/formImport" class="btn-link-custom">
                Import Sekarang <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- DAFTAR PERIODE -->
    <div class="table-card mt-3">
        <div class="table-head">
            <div class="chart-title">
                <i class="fas fa-layer-group" style="color:var(--p600)"></i>
                Semua Periode & Rig
                <span class="badge bg-light text-dark ms-2"><?= count($daftarPeriode) ?></span>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/import/formImport" class="btn-sigma btn-sm" style="text-decoration:none; font-size:12px; padding:6px 14px;">
                    <i class="fas fa-plus"></i> Import
                </a>
            </div>
        </div>
        <div class="p-3">
            <div class="periode-grid">
                <?php foreach ($daftarPeriode as $i => $p): ?>
                    <a href="<?= BASE_URL ?>/payroll/daftarKaryawan?periode=<?= urlencode($p['periode']) ?>&id_rig=<?= $p['id_rig'] ?? '' ?>"
                       class="periode-card" style="animation-delay: <?= $i * 0.05 ?>s;">
                        <div class="icon-wrap">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="info">
                            <div class="name"><?= htmlspecialchars($p['periode']) ?></div>
                            <div class="meta">
                                <?= !empty($p['kode_rig']) ? 'Rig ' . htmlspecialchars($p['kode_rig']) : 'Belum Ada Rig' ?>
                                <span class="mx-1">·</span>
                                <i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($p['tanggal_mulai'])) ?>
                            </div>
                        </div>
                        <div class="badge-count"><?= $p['jumlah'] ?? 0 ?></div>
                        <div class="arrow"><i class="fas fa-chevron-right"></i></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- STATISTIK RINGKASAN (Optional) -->
    <?php if (!empty($statistikPerRig)): ?>
    <div class="row g-3 mt-2">
        <div class="col-12">
            <div class="chart-card">
                <div class="chart-title"><i class="fas fa-chart-bar" style="color:var(--m500)"></i> Distribusi Data per Rig</div>
                <div class="row g-2 mt-2">
                    <?php foreach ($statistikPerRig as $rig): ?>
                        <div class="col-md-3 col-6">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <div class="fw-bold fs-4"><?= $rig['jumlah'] ?></div>
                                <div class="text-muted small">Rig <?= htmlspecialchars($rig['kode_rig']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
