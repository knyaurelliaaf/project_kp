<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-2 laporan-page-title">
    <h4><i class="fas <?= $jenis_laporan === 'turnover' ? 'fa-chart-line' : ($jenis_laporan === 'crew_aktif' ? 'fa-users' : 'fa-file-alt') ?>"></i> <?= $jenis_laporan === 'turnover' ? 'Laporan Turn Over Karyawan' : ($jenis_laporan === 'crew_aktif' ? 'Laporan Data Crew Aktif Overall' : 'Laporan Dokumen Expired') ?></h4>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
    <div class="laporan-switcher d-flex align-items-center gap-2">
        <a href="<?= BASE_URL ?>/laporan?laporan=dokumen" class="laporan-switch <?= $jenis_laporan === 'dokumen' ? 'active' : '' ?>"><i class="fas fa-file-alt"></i> Dokumen Expired</a>
        <a href="<?= BASE_URL ?>/laporan?laporan=turnover" class="laporan-switch <?= $jenis_laporan === 'turnover' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> Turn Over</a>
        <a href="<?= BASE_URL ?>/laporan?laporan=crew_aktif" class="laporan-switch <?= $jenis_laporan === 'crew_aktif' ? 'active' : '' ?>"><i class="fas fa-users"></i> Laporan Crew Aktif</a>
    </div>
    
    <?php if ($jenis_laporan === 'dokumen'): ?>
        <a href="<?= BASE_URL ?>/laporan/cetak?type=<?= urlencode($filter_type ?? 'expired') ?>&rig=<?= urlencode($filter_rig ?? '') ?>&dokumen=<?= urlencode($filter_dokumen ?? '') ?>" target="_blank" class="laporan-switch active-danger" style="text-decoration:none;"><i class="fas fa-print"></i> Cetak Dokumen</a>
    <?php elseif ($jenis_laporan === 'crew_aktif'): ?>
        <a href="<?= BASE_URL ?>/laporan/cetakCrewAktif?rig=<?= urlencode($filter_rig ?? '') ?>" target="_blank" class="laporan-switch active-danger" style="text-decoration:none;"><i class="fas fa-print"></i> Cetak Laporan Crew Aktif</a>
    <?php endif; ?>
</div>

<?php if ($jenis_laporan === 'turnover'): ?>
    <div class="filter-bar mb-3">
        <form method="GET" action="<?= BASE_URL ?>/laporan" class="row g-2 align-items-end">
            <input type="hidden" name="laporan" value="turnover">
            <div class="col-md-3">
                <label class="form-label small">Periode</label>
                <select name="periode" id="periode-turnover" class="form-select form-select-sm" onchange="toggleBulanTurnover(this.value)">
                    <option value="bulan" <?= $periode === 'bulan' ? 'selected' : '' ?>>Per Bulan</option>
                    <option value="semester" <?= $periode === 'semester' ? 'selected' : '' ?>>Per 6 Bulan</option>
                    <option value="tahun" <?= $periode === 'tahun' ? 'selected' : '' ?>>Per Tahun</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Tahun</label>
                <select name="tahun" class="form-select form-select-sm">
                    <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                        <option value="<?= $y ?>" <?= (int) $tahun === (int) $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3" id="bulan-turnover-wrap">
                <label class="form-label small">Bulan / Penentu Semester</label>
                <select name="bulan" class="form-select form-select-sm">
                    <?php $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; foreach ($months as $key => $month): $value = str_pad((string) ($key + 1), 2, '0', STR_PAD_LEFT); ?>
                        <option value="<?= $value ?>" <?= $bulan === $value ? 'selected' : '' ?>><?= $month ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 laporan-filter-actions">
                <button type="submit" class="btn btn-sm btn-sigma"><i class="fas fa-filter"></i> Terapkan Filter</button>
                <a href="<?= BASE_URL ?>/laporan?laporan=turnover" class="btn btn-sm btn-outline-secondary"><i class="fas fa-sync-alt"></i></a>
                <a href="<?= BASE_URL ?>/laporan/cetakTurnover?periode=<?= urlencode($periode) ?>&tahun=<?= urlencode($tahun) ?>&bulan=<?= urlencode($bulan) ?>" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fas fa-print"></i> Cetak</a>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-3 turnover-stats">
        <div class="col-md-3"><div class="turnover-stat stat-total"><i class="fas fa-users"></i><div><span>Total Crew Aktif</span><strong><?= $totalCrew ?></strong></div></div></div>
        <div class="col-md-3"><div class="turnover-stat stat-masuk"><i class="fas fa-user-plus"></i><div><span>Crew Masuk</span><strong><?= $turnover['total_masuk'] ?></strong></div></div></div>
        <div class="col-md-3"><div class="turnover-stat stat-keluar"><i class="fas fa-user-minus"></i><div><span>Crew Keluar</span><strong><?= $turnover['total_keluar'] ?></strong></div></div></div>
        <div class="col-md-3"><div class="turnover-stat stat-rate"><i class="fas fa-percent"></i><div><span>Turn Over Rate</span><strong><?= $totalCrew > 0 ? round($turnover['total_keluar'] / $totalCrew * 100, 1) : 0 ?>%</strong></div></div></div>
    </div>

    <div class="chart-card turnover-chart-card mb-3">
        <div class="chart-head"><div class="chart-title"><i class="fas fa-chart-bar chart-icon-red"></i><span id="turnover-chart-title">Grafik Turn Over</span></div></div>
        <div class="turnover-chart-wrap"><canvas id="turnover-chart"></canvas></div>
    </div>

    <div class="table-card mb-3">
        <div class="table-head"><div class="chart-title"><i class="fas fa-sign-out-alt chart-icon-red"></i> Daftar Crew Keluar (<?= $turnover['total_keluar'] ?>)</div></div>
        <div class="tw"><table><thead><tr><th>#</th><th>Nama</th><th>Posisi</th><th>Rig</th><th>Crew</th><th>Tgl Nonaktif</th></tr></thead><tbody>
            <?php if ($turnover['keluar'] && $turnover['keluar']->num_rows > 0): $no = 1; while ($crew = $turnover['keluar']->fetch_assoc()): ?>
                <tr><td class="td-muted"><?= $no++ ?></td><td><strong><?= htmlspecialchars($crew['nama']) ?></strong></td><td class="td-muted"><?= htmlspecialchars($crew['posisi']) ?></td><td><span class="rtag"><?= htmlspecialchars($crew['kode_rig']) ?></span></td><td><?= htmlspecialchars($crew['crew'] ?? '-') ?></td><td class="td-sm"><?= $crew['tanggal_nonaktif'] ? date('d/m/Y', strtotime($crew['tanggal_nonaktif'])) : '-' ?></td></tr>
            <?php endwhile; else: ?><tr><td colspan="6" class="td-empty">Tidak ada crew keluar pada periode ini</td></tr><?php endif; ?>
        </tbody></table></div>
    </div>

    <div class="table-card mb-3">
        <div class="table-head"><div class="chart-title"><i class="fas fa-sign-in-alt chart-icon-red"></i> Daftar Crew Masuk (<?= $turnover['total_masuk'] ?>)</div></div>
        <div class="tw"><table><thead><tr><th>#</th><th>Nama</th><th>Posisi</th><th>Rig</th><th>Crew</th><th>Tgl Masuk</th></tr></thead><tbody>
            <?php if ($turnover['masuk'] && $turnover['masuk']->num_rows > 0): $no = 1; while ($crew = $turnover['masuk']->fetch_assoc()): ?>
                <tr><td class="td-muted"><?= $no++ ?></td><td><strong><?= htmlspecialchars($crew['nama']) ?></strong></td><td class="td-muted"><?= htmlspecialchars($crew['posisi']) ?></td><td><span class="rtag"><?= htmlspecialchars($crew['kode_rig']) ?></span></td><td><?= htmlspecialchars($crew['crew'] ?? '-') ?></td><td class="td-sm"><?= $crew['tanggal_masuk'] ? date('d/m/Y', strtotime($crew['tanggal_masuk'])) : '-' ?></td></tr>
            <?php endwhile; else: ?><tr><td colspan="6" class="td-empty">Tidak ada crew masuk pada periode ini</td></tr><?php endif; ?>
        </tbody></table></div>
    </div>

    <script>
        function toggleBulanTurnover(value) { document.getElementById('bulan-turnover-wrap').style.display = value === 'tahun' ? 'none' : 'block'; }
        toggleBulanTurnover('<?= $periode ?>');
        const chartData = <?= json_encode($chartData) ?>;
        const periode = '<?= $periode ?>', bulan = '<?= $bulan ?>', tahun = '<?= $tahun ?>';
        const monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        let labels, masukData, keluarData, chartTitle;
        if (periode === 'bulan') { const i = parseInt(bulan, 10); labels = [monthNames[i - 1]]; masukData = [chartData[i]?.masuk || 0]; keluarData = [chartData[i]?.keluar || 0]; chartTitle = 'Grafik Turn Over Bulan ' + monthNames[i - 1] + ' ' + tahun; }
        else if (periode === 'semester') { const start = parseInt(bulan, 10) <= 6 ? 0 : 6; labels = monthNames.slice(start, start + 6); masukData = labels.map((_, i) => chartData[start + i + 1]?.masuk || 0); keluarData = labels.map((_, i) => chartData[start + i + 1]?.keluar || 0); chartTitle = 'Grafik Turn Over Semester ' + (start === 0 ? '1' : '2') + ' ' + tahun; }
        else { labels = monthNames; masukData = labels.map((_, i) => chartData[i + 1]?.masuk || 0); keluarData = labels.map((_, i) => chartData[i + 1]?.keluar || 0); chartTitle = 'Grafik Turn Over Tahun ' + tahun; }
        document.getElementById('turnover-chart-title').textContent = chartTitle;
        new Chart(document.getElementById('turnover-chart'), { type: 'bar', data: { labels, datasets: [ { label: 'Crew Masuk', data: masukData, backgroundColor: '#0f9b58', borderRadius: 7, maxBarThickness: 42 }, { label: 'Crew Keluar', data: keluarData, backgroundColor: '#b31312', borderRadius: 7, maxBarThickness: 42 } ] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { usePointStyle: true, padding: 18 } } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0, stepSize: 1 }, grid: { color: 'rgba(43,42,76,.08)' } } } } });
    </script>

<?php elseif ($jenis_laporan === 'crew_aktif'): ?>
    <div class="filter-bar mb-3">
        <form method="GET" action="<?= BASE_URL ?>/laporan" class="row g-2 align-items-end">
            <input type="hidden" name="laporan" value="crew_aktif">
            <?php if ($isSuperAdmin): ?>
                <div class="col-md-4">
                    <label class="form-label small">Rig</label>
                    <select name="rig" class="form-select form-select-sm">
                        <option value="">Semua Rig</option>
                        <?php foreach ($rigList as $rig): ?>
                            <option value="<?= htmlspecialchars($rig['kode_rig']) ?>" <?= ($filter_rig ?? '') === $rig['kode_rig'] ? 'selected' : '' ?>><?= htmlspecialchars($rig['kode_rig']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <div class="col-md-4">
                <button type="submit" class="btn btn-sm btn-sigma"><i class="fas fa-filter"></i> Terapkan Filter</button>
                <a href="<?= BASE_URL ?>/laporan?laporan=crew_aktif" class="btn btn-sm btn-outline-secondary"><i class="fas fa-sync-alt"></i> Reset</a>
            </div>
        </form>
    </div>

    <!-- Summary Stats Card -->
    <div class="row mb-3">
        <div class="col-md-4 mb-2">
            <div class="card stat-card border-success">
                <div class="card-body d-flex align-items-center justify-content-between p-3">
                    <div>
                        <div class="text-muted small">Total Crew Aktif Saat Ini</div>
                        <h3 class="fw-bold text-success mb-0"><?= $totalCrew ?> <span class="fs-6 fw-normal text-muted">Orang</span></h3>
                    </div>
                    <div class="fs-1 text-success opacity-75"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Detail Crew Aktif Overall -->
    <div class="table-card mb-3">
        <div class="table-head d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="chart-title"><i class="fas fa-users text-primary me-1"></i> Daftar Seluruh Crew Aktif (<?= $totalCrew ?>)</div>
            <input type="text" id="searchCrewAktifPage" class="form-control form-control-sm w-auto" placeholder="Cari nama / posisi / NIK / rig..." onkeyup="filterTabelCrewAktifPage()">
        </div>
        <div class="tw">
            <table id="tabelCrewAktifPage">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Crew</th>
                        <th>No. Badge</th>
                        <th>NIK KTP</th>
                        <th>Posisi / Jabatan</th>
                        <th>Rig</th>
                        <th>Crew</th>
                        <th>No. Telp / WA</th>
                        <th>Tempat, Tgl Lahir</th>
                        <th>Email & Alamat</th>
                        <th>Tgl Masuk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($activeCrewList) && $activeCrewList->num_rows > 0): $no = 1; ?>
                        <?php while ($ac = $activeCrewList->fetch_assoc()): ?>
                        <tr>
                            <td class="td-muted"><?= $no++ ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/crew/detail/<?= $ac['id_crew'] ?>" class="fw-bold text-decoration-none text-dark">
                                    <?= htmlspecialchars($ac['nama']) ?>
                                </a>
                                <div class="small text-muted">ID: #<?= $ac['id_crew'] ?></div>
                            </td>
                            <td class="td-sm"><?= htmlspecialchars($ac['nomor_badge'] ?? '-') ?></td>
                            <td class="td-sm"><?= htmlspecialchars($ac['nik_ktp'] ?? '-') ?></td>
                            <td class="td-muted"><strong><?= htmlspecialchars($ac['posisi'] ?? '-') ?></strong></td>
                            <td><span class="rtag"><?= htmlspecialchars($ac['kode_rig']) ?></span></td>
                            <td><?= htmlspecialchars($ac['crew'] ?? '-') ?></td>
                            <td>
                                <?php if (!empty($ac['no_telp'])): ?>
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $ac['no_telp']) ?>" target="_blank" class="text-decoration-none text-success">
                                        <i class="fab fa-whatsapp me-1"></i><?= htmlspecialchars($ac['no_telp']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="td-sm">
                                <?php 
                                $tmpl = trim($ac['tempat_lahir'] ?? '');
                                $tglL = !empty($ac['tanggal_lahir']) ? date('d/m/Y', strtotime($ac['tanggal_lahir'])) : '';
                                echo ($tmpl && $tglL) ? ($tmpl . ', ' . $tglL) : ($tmpl ?: ($tglL ?: '-'));
                                ?>
                            </td>
                            <td class="td-sm">
                                <?php if (!empty($ac['email'])): ?>
                                    <div><i class="fas fa-envelope text-muted me-1"></i><?= htmlspecialchars($ac['email']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($ac['alamat'])): ?>
                                    <div class="small text-muted text-truncate" style="max-width: 180px;"><?= htmlspecialchars($ac['alamat']) ?></div>
                                <?php endif; ?>
                                <?php if (empty($ac['email']) && empty($ac['alamat'])): ?>-<?php endif; ?>
                            </td>
                            <td class="td-sm">
                                <?= !empty($ac['tanggal_masuk']) ? date('d/m/Y', strtotime($ac['tanggal_masuk'])) : '-' ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="11" class="td-empty">Tidak ada data crew aktif</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function filterTabelCrewAktifPage() {
        const input = document.getElementById("searchCrewAktifPage");
        const filter = input.value.toLowerCase();
        const table = document.getElementById("tabelCrewAktifPage");
        const tr = table.getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) {
            let textContent = tr[i].textContent || tr[i].innerText;
            if (textContent.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
    </script>

<?php else: ?>
    <div class="filter-bar mb-3"><form method="GET" action="<?= BASE_URL ?>/laporan" class="row g-2 align-items-end"><input type="hidden" name="laporan" value="dokumen">
        <div class="col-md-3"><label class="form-label small">Jenis Laporan</label><select name="type" class="form-select form-select-sm"><option value="expired" <?= $filter_type === 'expired' ? 'selected' : '' ?>>Expired</option><option value="warning" <?= $filter_type === 'warning' ? 'selected' : '' ?>>Akan Expired (1-30 hari)</option></select></div>
        <?php if ($isSuperAdmin): ?><div class="col-md-3"><label class="form-label small">Rig</label><select name="rig" class="form-select form-select-sm"><option value="">Semua Rig</option><?php foreach ($rigList as $rig): ?><option value="<?= htmlspecialchars($rig['kode_rig']) ?>" <?= $filter_rig === $rig['kode_rig'] ? 'selected' : '' ?>><?= htmlspecialchars($rig['kode_rig']) ?></option><?php endforeach; ?></select></div><?php endif; ?>
        <div class="col-md-3"><label class="form-label small">Jenis Dokumen</label><select name="dokumen" class="form-select form-select-sm"><option value="">Semua Dokumen</option><?php foreach (['badge' => 'Badge', 'mcu' => 'MCU', 'sertifikat' => 'Sertifikat', 'pkwt' => 'PKWT'] as $key => $label): ?><option value="<?= $key ?>" <?= $filter_dokumen === $key ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><button type="submit" class="btn btn-sm btn-sigma"><i class="fas fa-filter"></i> Filter</button> <a href="<?= BASE_URL ?>/laporan" class="btn btn-sm btn-outline-secondary"><i class="fas fa-sync-alt"></i></a></div>
    </form></div>
    <?php
    $icons = ['Badge' => 'fa-id-badge', 'MCU' => 'fa-heartbeat', 'Sertifikat' => 'fa-certificate', 'PKWT' => 'fa-file-contract'];
    $dokumenList = ($filter_dokumen !== '') ? [$filter_dokumen] : ['badge', 'mcu', 'sertifikat', 'pkwt'];
    $dokumenLabel = ['badge' => 'Badge', 'mcu' => 'MCU', 'sertifikat' => 'Sertifikat', 'pkwt' => 'PKWT'];
    foreach ($dokumenList as $key): $jenis = $dokumenLabel[$key]; $items = $grouped[$jenis]; ?>
        <div class="table-card mb-3"><div class="table-head"><div class="chart-title"><i class="fas <?= $icons[$jenis] ?> chart-icon-red"></i> <?= $jenis ?> <span>(<?= count($items) ?>)</span></div></div><div class="tw"><table><thead><tr><th>#</th><th>Nama</th><th>Posisi</th><th>Rig</th><th>Crew</th><th>Tanggal Berakhir</th><th>Status</th></tr></thead><tbody>
            <?php if ($items): foreach ($items as $no => $item): ?><tr><td class="td-muted"><?= $no + 1 ?></td><td><a class="cn-link" href="<?= BASE_URL ?>/crew/detail/<?= $item['id_crew'] ?>"><?= htmlspecialchars($item['nama']) ?></a></td><td class="td-muted"><?= htmlspecialchars($item['posisi']) ?></td><td><span class="rtag"><?= htmlspecialchars($item['kode_rig']) ?></span></td><td><?= htmlspecialchars($item['crew'] ?: '-') ?></td><td class="td-sm"><?= date('d/m/Y', strtotime($item['tanggal_expired'])) ?></td><td><span class="db <?= $filter_type === 'expired' ? 'dex' : 'dsn' ?>"><i class="fas <?= $filter_type === 'expired' ? 'fa-times' : 'fa-clock' ?>"></i> <?= $filter_type === 'expired' ? 'Expired ' . abs($item['sisa_hari']) . ' hari' : $item['sisa_hari'] . ' hari lagi' ?></span></td></tr><?php endforeach; else: ?><tr><td colspan="7" class="td-empty">Tidak ada dokumen <?= strtolower($jenis) ?> <?= $filter_type === 'expired' ? 'expired' : 'yang akan expired' ?></td></tr><?php endif; ?>
        </tbody></table></div></div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
