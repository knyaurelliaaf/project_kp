<?php
$days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$today = $days[date('w')] . ', ' . date('d') . ' ' . $months[date('n') - 1] . ' ' . date('Y');
?>

<?php require_once __DIR__ . '/../layout/header.php'; ?>

<!-- HERO BANNER -->
<div class="hero">
    <div class="hero-deco"></div>
    <div class="hero-deco2"></div>
    <div class="hero-left">
        <div class="greeting"><i class="fas fa-fire"></i> Selamat datang kembali,</div>
        <div class="title">Crew Compliance Monitoring</div>
        <div class="sub"><i class="fas fa-calendar-alt"></i> <?= $today ?> &nbsp;·&nbsp; <i class="fas fa-sync-alt"></i> Update real-time</div>
    </div>
    <div class="hero-right">
        <div class="hero-stat">
            <?php $complyPercent = $totalCrew > 0 ? round(($totalCrew - $totalExpired) / $totalCrew * 100, 1) : 100; ?>
            <div class="num"><?= $complyPercent ?>%</div>
            <div class="lbl">Tingkat Kepatuhan</div>
        </div>
        <div class="hero-divider"></div>
        <div class="hero-stat">
            <div class="num"><?= $totalExpired ?></div>
            <div class="lbl">Expired Hari Ini</div>
        </div>
        <div class="hero-divider"></div>
        <div class="hero-stat">
            <div class="num"><?= $totalWarning ?></div>
            <div class="lbl">Akan Expired</div>
        </div>
    </div>
</div>

<!-- STAT CARDS -->
<div class="stat-row">
    <div class="sc">
        <div class="sc-top">
            <div class="sc-icon ic-navy"><i class="fas fa-users"></i></div>
            <span class="sc-trend tr-green">Aktif</span>
        </div>
        <div>
            <div class="sc-lbl">Total Crew</div>
            <div class="sc-val"><?= $totalCrew ?></div>
        </div>
        <div class="sc-bar">
            <div class="sc-bar-fill sc-bar-fill-navy"></div>
        </div>
    </div>
    <div class="sc">
        <div class="sc-top">
            <div class="sc-icon ic-blue"><i class="fas fa-envelope"></i></div>
            <span class="sc-trend sc-trend-blue">Total</span>
        </div>
        <div>
            <div class="sc-lbl">Total Surat</div>
            <div class="sc-val"><?= $totalSurat ?></div>
        </div>
        <div class="sc-bar">
            <div class="sc-bar-fill sc-bar-fill-blue"></div>
        </div>
    </div>
    <div class="sc">
        <div class="sc-top">
            <div class="sc-icon ic-red"><i class="fas fa-times-circle"></i></div>
            <span class="sc-trend tr-red">Kritis</span>
        </div>
        <div>
            <div class="sc-lbl">Dokumen Expired</div>
            <div class="sc-val sc-val-red"><?= $totalExpired ?></div>
        </div>
        <div class="sc-bar">
            <div class="sc-bar-fill sc-bar-fill-red" data-width="<?= $totalCrew > 0 ? round($totalExpired / $totalCrew * 100) : 0 ?>"></div>
        </div>
    </div>
    <div class="sc">
        <div class="sc-top">
            <div class="sc-icon ic-orange"><i class="fas fa-clock"></i></div>
            <span class="sc-trend tr-orange">30 hari</span>
        </div>
        <div>
            <div class="sc-lbl">Akan Expired</div>
            <div class="sc-val sc-val-orange"><?= $totalWarning ?></div>
        </div>
        <div class="sc-bar">
            <div class="sc-bar-fill sc-bar-fill-orange" data-width="<?= $totalCrew > 0 ? round($totalWarning / $totalCrew * 100) : 0 ?>"></div>
        </div>
    </div>
    <div class="sc">
        <div class="sc-top">
            <div class="sc-icon ic-green"><i class="fas fa-check-circle"></i></div>
            <span class="sc-trend tr-green"><?= $complyPercent ?>%</span>
        </div>
        <div>
            <div class="sc-lbl">Crew Aktif</div>
            <div class="sc-val sc-val-green"><?= $totalCrew - $totalExpired ?></div>
        </div>
        <div class="sc-bar">
            <div class="sc-bar-fill sc-bar-fill-green" data-width="<?= $complyPercent ?>"></div>
        </div>
    </div>
</div>

<!-- COMPLIANCE RINGS -->
<div class="ring-row">
    <?php
    $badgeTotal = $totalCrew > 0 ? round(($totalCrew - $expiredBadge) / $totalCrew * 100) : 100;
    $mcuTotal = $totalCrew > 0 ? round(($totalCrew - $expiredMcu) / $totalCrew * 100) : 100;
    $sertTotal = $totalCrew > 0 ? round(($totalCrew - $expiredSertifikat) / $totalCrew * 100) : 100;
    $pkwtTotal = $totalCrew > 0 ? round(($totalCrew - $expiredPkwt) / $totalCrew * 100) : 100;
    ?>
    <div class="ring-card">
        <div class="ring-wrap"><canvas id="r1" width="72" height="72"></canvas>
            <div class="ring-pct"><?= $badgeTotal ?>%</div>
        </div>
        <div class="ring-lbl">ID Badge</div>
        <div class="ring-sub">Kepatuhan</div>
    </div>
    <div class="ring-card">
        <div class="ring-wrap"><canvas id="r2" width="72" height="72"></canvas>
            <div class="ring-pct"><?= $mcuTotal ?>%</div>
        </div>
        <div class="ring-lbl">MCU</div>
        <div class="ring-sub">Kepatuhan</div>
    </div>
    <div class="ring-card">
        <div class="ring-wrap"><canvas id="r3" width="72" height="72"></canvas>
            <div class="ring-pct"><?= $sertTotal ?>%</div>
        </div>
        <div class="ring-lbl">Sertifikat</div>
        <div class="ring-sub">Kepatuhan</div>
    </div>
    <div class="ring-card">
        <div class="ring-wrap"><canvas id="r4" width="72" height="72"></canvas>
            <div class="ring-pct"><?= $pkwtTotal ?>%</div>
        </div>
        <div class="ring-lbl">PKWT</div>
        <div class="ring-sub">Kepatuhan</div>
    </div>
</div>

<!-- ALERT STRIP -->
<div class="alert-strip">

    <!-- EXPIRED -->
    <div class="al al-exp">
        <div class="al-head">
            <div class="al-title r"><i class="fas fa-exclamation-circle"></i> Dokumen Sudah Expired</div>
            <span class="al-count r"><?= $totalExpired ?> dokumen</span>
        </div>
        <div class="al-items">
            <?php
            $countExp = 0;
            if ($crewExpired && $crewExpired->num_rows > 0):
                $crewExpired->data_seek(0);
                while ($crew = $crewExpired->fetch_assoc()):
                    // Hitung sisa hari langsung dari tanggal, tidak andalkan nilai status dari controller
                    $checks = [
                        'Badge'      => $crew['badge_exp']      ?? null,
                        'MCU'        => $crew['mcu_exp']        ?? null,
                        'Sertifikat' => $crew['sertifikat_exp'] ?? null,
                        'PKWT'       => $crew['pkwt_exp']       ?? null,
                    ];

                    $kategoriExp = null;
                    $tglExp      = null;
                    $sisaExp     = null;

                    foreach ($checks as $label => $tgl):
                        if (empty($tgl)) continue;
                        $sisa = Helper::sisaHari($tgl);
                        // Ambil yang paling parah (paling negatif) sebagai yang ditampilkan
                        if ($sisa < 0 && ($sisaExp === null || $sisa < $sisaExp)):
                            $kategoriExp = $label;
                            $tglExp      = $tgl;
                            $sisaExp     = $sisa;
                        endif;
                    endforeach;

                    if ($kategoriExp !== null && $countExp < 3):
                        $countExp++;
                        $init = strtoupper(substr($crew['nama'], 0, 2));
                        $hariLewat = abs($sisaExp);
                ?>
                        <div class="al-row">
                            <div class="al-av av-r"><?= $init ?></div>
                            <div class="al-info">
                                <div class="al-name"><?= $crew['nama'] ?></div>
                                <div class="al-meta">
                                    <i class="fas fa-id-card"></i>
                                    <?= $kategoriExp ?> — Expired <?= Helper::formatDate($tglExp) ?>
                                    <span class="text-danger">(<?= $hariLewat ?> hari lalu)</span>
                                </div>
                            </div>
                            <div class="al-right">
                                <div class="al-rig"><?= $crew['kode_rig'] ?></div>
                                <span class="al-tag tag-exp">EXPIRED</span>
                            </div>
                        </div>
                <?php
                    endif;
                endwhile;
            endif;

            if ($countExp === 0): ?>
                <div class="al-row">
                    <div class="al-info">
                        <div class="al-name">Tidak ada dokumen expired 🎉</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- AKAN EXPIRED -->
    <div class="al al-soon">
        <div class="al-head">
            <div class="al-title o"><i class="fas fa-clock"></i> Akan Expired 30 Hari</div>
            <span class="al-count o"><?= $totalWarning ?> dokumen</span>
        </div>
        <div class="al-items">
            <?php
            $countWarn = 0;
            if ($crewExpired && $crewExpired->num_rows > 0):
                $crewExpired->data_seek(0);
                while ($crew = $crewExpired->fetch_assoc()):
                    $checks = [
                        'Badge'      => $crew['badge_exp']      ?? null,
                        'MCU'        => $crew['mcu_exp']        ?? null,
                        'Sertifikat' => $crew['sertifikat_exp'] ?? null,
                        'PKWT'       => $crew['pkwt_exp']       ?? null,
                    ];

                    $kategoriWarn = null;
                    $tglWarn      = null;
                    $sisaWarn     = null;

                    foreach ($checks as $label => $tgl):
                        if (empty($tgl)) continue;
                        $sisa = Helper::sisaHari($tgl);
                        // Ambil yang paling dekat expired di range warning (1-30 hari)
                        if ($sisa > 0 && $sisa <= 30 && ($sisaWarn === null || $sisa < $sisaWarn)):
                            $kategoriWarn = $label;
                            $tglWarn      = $tgl;
                            $sisaWarn     = $sisa;
                        endif;
                    endforeach;

                    if ($kategoriWarn !== null && $countWarn < 3):
                        $countWarn++;
                        $init = strtoupper(substr($crew['nama'], 0, 2));
            ?>
                        <div class="al-row">
                            <div class="al-av av-o"><?= $init ?></div>
                            <div class="al-info">
                                <div class="al-name"><?= $crew['nama'] ?></div>
                                <div class="al-meta">
                                    <i class="fas fa-id-card"></i>
                                    <?= $kategoriWarn ?> — Exp <?= Helper::formatDate($tglWarn) ?>
                                </div>
                            </div>
                            <div class="al-right">
                                <div class="al-rig"><?= $crew['kode_rig'] ?></div>
                                <span class="al-tag tag-soon"><?= $sisaWarn ?> hari</span>
                            </div>
                        </div>
            <?php
                    endif;
                endwhile;
            endif; ?>
        </div>
    </div>

</div>

<!-- CHART ROW -->
<div class="chart-row">
    <div class="chart-card">
        <div class="chart-head">
            <div class="chart-title"><i class="fas fa-chart-bar chart-icon-red"></i> Dokumen Expired per Rig</div>
            <div class="chart-pills">
                <span class="cpill on" data-filter="semua">Semua</span>
                <span class="cpill" data-filter="badge">Badge</span>
                <span class="cpill" data-filter="mcu">MCU</span>
                <span class="cpill" data-filter="sertifikat">Sertifikat</span>
            </div>
        </div>
        <div class="legend-row"><span class="leg"><span class="leg-dot leg-dot-red"></span>Expired</span><span class="leg"><span class="leg-dot leg-dot-orange"></span>Akan Expired</span><span class="leg"><span class="leg-dot leg-dot-green"></span>Valid</span></div>
        <div class="chart-canvas"><canvas id="main-chart"></canvas></div>
    </div>
    <div class="chart-card">
        <div class="chart-head">
            <div class="chart-title"><i class="fas fa-chart-pie chart-icon-red"></i> Status Kepatuhan</div>
        </div>
        <div class="legend-row">
            <span class="leg"><span class="leg-dot leg-dot-green-circle"></span>Comply <?= $complyPercent ?>%</span>
            <span class="leg"><span class="leg-dot leg-dot-orange-circle"></span>Warning <?= $warningPercent ?>%</span>
            <span class="leg"><span class="leg-dot leg-dot-red-circle"></span>Non-Comply <?= $nonComplyPercent ?>%</span>
        </div>
        <div class="chart-canvas"><canvas id="donut-chart"></canvas></div>
    </div>
</div>

<!-- MINI CHARTS -->
<div class="mini-grid">
    <div class="mini-card">
        <div class="mini-title"><i class="fas fa-stethoscope"></i> MCU Expired per Rig</div>
        <div class="chart-canvas-sm"><canvas id="mini1"></canvas></div>
    </div>
    <div class="mini-card">
        <div class="mini-title"><i class="fas fa-certificate"></i> Sertifikat Expired per Rig</div>
        <div class="chart-canvas-sm"><canvas id="mini2"></canvas></div>
    </div>
</div>

<!-- TABLE -->
<div class="table-card" id="crew-table">
    <div class="table-head">
        <div class="chart-title"><i class="fas fa-table chart-icon-red"></i> Ringkasan Crew (<?= $totalCrewAll ?> crew)</div>
        <div class="tbl-actions">
            <button class="tbl-btn pdf" onclick="window.print()"><i class="fas fa-file-pdf"></i> PDF</button>
            <button class="tbl-btn xl"><i class="fas fa-file-excel"></i> Excel</button>
        </div>
    </div>
    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Crew</th>
                    <th>Rig</th>
                    <th>Posisi</th>
                    <th>Badge</th>
                    <th>MCU</th>
                    <th>Sertifikat</th>
                    <th>PKWT</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="crew-tbody">
                <?php if ($allCrew && $allCrew->num_rows > 0): $no = $offset;
                    while ($crew = $allCrew->fetch_assoc()): $no++;
                        $init = strtoupper(substr($crew['nama'], 0, 2));
                        $colors = ['#2B2A4C', '#B31312', '#0f9b58', '#7c3aed', '#0891b2', '#b45309', '#be185d', '#0369a1'];
                        $color = $colors[($no - 1) % count($colors)];
                        $badgeStatus = $crew['badge_status'] ?? 'ok';
                        $mcuStatus = $crew['mcu_status'] ?? 'ok';
                        $sertStatus = $crew['sertifikat_status'] ?? 'ok';
                        $pkwtStatus = $crew['pkwt_status'] ?? 'ok';
                        $statusLabels = ['ok' => '<span class="db dok"><i class="fas fa-check"></i> Valid</span>', 'soon' => '<span class="db dsn"><i class="fas fa-clock"></i> Segera</span>', 'exp' => '<span class="db dex"><i class="fas fa-times"></i> Expired</span>'];
                        if (in_array('exp', [$badgeStatus, $mcuStatus, $sertStatus, $pkwtStatus])) $overall = '<span class="stbadge st-n">Non-Comply</span>';
                        elseif (in_array('soon', [$badgeStatus, $mcuStatus, $sertStatus, $pkwtStatus])) $overall = '<span class="stbadge st-w">Warning</span>';
                        else $overall = '<span class="stbadge st-c">Comply</span>';
                ?>
                        <tr>
                            <td class="td-muted td-bold"><?= $no ?></td>
                            <td>
                                <div class="crew-av">
                                    <div class="cav" style="background:<?= $color ?>"><?= $init ?></div>
                                    <div>
                                        <div class="cn"><?= $crew['nama'] ?></div>
                                        <div class="cp">ID: <?= $crew['id_crew'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="rtag"><?= $crew['kode_rig'] ?></span></td>
                            <td class="td-muted"><?= $crew['posisi'] ?></td>
                            <td><?= $statusLabels[$badgeStatus] ?></td>
                            <td><?= $statusLabels[$mcuStatus] ?></td>
                            <td><?= $statusLabels[$sertStatus] ?></td>
                            <td><?= $statusLabels[$pkwtStatus] ?></td>
                            <td><?= $overall ?></td>
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">Tidak ada data crew</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
<div id="crew-pagination" class="pagination-container">
    <?php if ($totalPages > 1): ?>
        <a href="javascript:void(0)" onclick="loadPage(<?= $page - 1 ?>)" class="btn btn-sm btn-prev <?= $page <= 1 ? 'disabled' : '' ?>">&laquo;</a>
        <span id="pagination-numbers" class="pagination-numbers">
            <?php for ($p = 1; $p <= min($totalPages, 10); $p++): ?>
                <a href="javascript:void(0)" onclick="loadPage(<?= $p ?>)" class="btn btn-sm btn-page <?= $p == $page ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </span>
        <a href="javascript:void(0)" onclick="loadPage(<?= $page + 1 ?>)" class="btn btn-sm btn-next <?= $page >= $totalPages ? 'disabled' : '' ?>">&raquo;</a>
    <?php endif; ?>
</div>

<!-- CHART.JS SCRIPTS -->
<script>
    const RED = '#B31312',
        ORG = '#d97706',
        GRN = '#0f9b58',
        NAV = '#2B2A4C';
    const rigLabels = <?= json_encode(array_column($rigStats, 'kode_rig')) ?>;
    const chartDataByCategory = {
        semua: {
            expired: <?= json_encode(array_column($rigStats, 'expired')) ?>,
            warning: <?= json_encode(array_column($rigStats, 'warning')) ?>,
            valid: <?= json_encode(array_map(function ($r) {
                        return $r['total_crew'] - $r['expired'] - $r['warning'];
                    }, $rigStats)) ?>
        },
        badge: {
            expired: <?= json_encode(array_column($rigStats, 'badge_expired')) ?>,
            warning: <?= json_encode(array_column($rigStats, 'badge_warning')) ?>,
            valid: <?= json_encode(array_column($rigStats, 'badge_valid')) ?>
        },
        mcu: {
            expired: <?= json_encode(array_column($rigStats, 'mcu_expired')) ?>,
            warning: <?= json_encode(array_column($rigStats, 'mcu_warning')) ?>,
            valid: <?= json_encode(array_column($rigStats, 'mcu_valid')) ?>
        },
        sertifikat: {
            expired: <?= json_encode(array_column($rigStats, 'sert_expired')) ?>,
            warning: <?= json_encode(array_column($rigStats, 'sert_warning')) ?>,
            valid: <?= json_encode(array_column($rigStats, 'sert_valid')) ?>
        }
    };

    const mainChart = new Chart(document.getElementById('main-chart'), {
        type: 'bar',
        data: {
            labels: rigLabels.length ? rigLabels : ['GW-336', 'GW-337', 'GW-338', 'GW-339'],
            datasets: [{
                    label: 'Expired',
                    data: chartDataByCategory.semua.expired,
                    backgroundColor: RED + 'cc',
                    borderRadius: 5
                },
                {
                    label: 'Akan Expired',
                    data: chartDataByCategory.semua.warning,
                    backgroundColor: ORG + 'cc',
                    borderRadius: 5
                },
                {
                    label: 'Valid',
                    data: chartDataByCategory.semua.valid,
                    backgroundColor: GRN + 'cc',
                    borderRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#7a7a9a' } },
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' }, ticks: { stepSize: 1, font: { size: 10 }, color: '#7a7a9a' } }
            }
        }
    });

    new Chart(document.getElementById('donut-chart'), {
        type: 'doughnut',
        data: {
            labels: ['Comply', 'Warning', 'Non-Comply'],
            datasets: [{ data: [<?= $complyPercent ?>, <?= $warningPercent ?>, <?= $nonComplyPercent ?>], backgroundColor: [GRN, ORG, RED], borderWidth: 0 }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '72%', plugins: { legend: { display: false } } }
    });

    const miniOpt = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 9 }, color: '#7a7a9a' } },
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { stepSize: 1, font: { size: 9 }, color: '#7a7a9a' } }
        }
    };

    const mcuExpiredData = <?= json_encode(array_column($rigStats, 'mcu_expired')) ?>;
    const sertExpiredData = <?= json_encode(array_column($rigStats, 'sert_expired')) ?>;

    new Chart(document.getElementById('mini1'), {
        type: 'bar',
        data: { labels: rigLabels.length ? rigLabels : ['336', '337', '338', '339'], datasets: [{ data: mcuExpiredData, backgroundColor: NAV + 'bb', borderRadius: 4 }] },
        options: miniOpt
    });
    new Chart(document.getElementById('mini2'), {
        type: 'bar',
        data: { labels: rigLabels.length ? rigLabels : ['336', '337', '338', '339'], datasets: [{ data: sertExpiredData, backgroundColor: ORG + 'bb', borderRadius: 4 }] },
        options: miniOpt
    });

    function drawRing(id, pct, color) {
        const c = document.getElementById(id);
        if (!c) return;
        const ctx = c.getContext('2d'), r = 30, cx = 36, cy = 36, lw = 7;
        ctx.clearRect(0, 0, 72, 72);
        ctx.beginPath(); ctx.arc(cx, cy, r, 0, Math.PI * 2); ctx.strokeStyle = '#f0f2f8'; ctx.lineWidth = lw; ctx.stroke();
        ctx.beginPath(); ctx.arc(cx, cy, r, -Math.PI / 2, (-Math.PI / 2) + (Math.PI * 2 * pct / 100)); ctx.strokeStyle = color; ctx.lineWidth = lw; ctx.lineCap = 'round'; ctx.stroke();
    }

    drawRing('r1', <?= $badgeTotal ?>, RED);
    drawRing('r2', <?= $mcuTotal ?>, GRN);
    drawRing('r3', <?= $sertTotal ?>, ORG);
    drawRing('r4', <?= $pkwtTotal ?>, '#1d6fcb');

    document.querySelectorAll('.cpill').forEach(pill => {
        pill.addEventListener('click', () => {
            document.querySelectorAll('.cpill').forEach(x => x.classList.remove('on'));
            pill.classList.add('on');
            const category = pill.getAttribute('data-filter');
            const data = chartDataByCategory[category];
            mainChart.data.datasets[0].data = data.expired;
            mainChart.data.datasets[1].data = data.warning;
            mainChart.data.datasets[2].data = data.valid;
            mainChart.update();
        });
    });

    function loadPage(page) {
        fetch('<?= BASE_URL ?>/ajax/crewTable?page=' + page)
            .then(res => res.json())
            .then(data => {
                document.querySelector('#crew-tbody').innerHTML = data.html;
                document.querySelector('#pagination-numbers').innerHTML = data.pagination;

                const prevBtn = document.querySelector('.btn-prev');
                if (data.page > 1) { prevBtn.classList.remove('disabled'); prevBtn.setAttribute('onclick', 'loadPage(' + (data.page - 1) + ')'); }
                else { prevBtn.classList.add('disabled'); prevBtn.removeAttribute('onclick'); }

                const nextBtn = document.querySelector('.btn-next');
                if (data.page < data.totalPages) { nextBtn.classList.remove('disabled'); nextBtn.setAttribute('onclick', 'loadPage(' + (data.page + 1) + ')'); }
                else { nextBtn.classList.add('disabled'); nextBtn.removeAttribute('onclick'); }
            });
    }

    document.querySelectorAll('.sc-bar-fill[data-width]').forEach(el => {
        el.style.width = el.getAttribute('data-width') + '%';
    });
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>