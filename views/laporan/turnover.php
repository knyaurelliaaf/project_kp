<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-chart-line"></i> Laporan Turn Over Karyawan</h4>
</div>

<!-- Filter -->
<div class="filter-bar mb-3">
    <form method="GET" action="<?= BASE_URL ?>/laporan/turnover" class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label small">Periode</label>
            <select name="periode" class="form-select form-select-sm" onchange="togglePeriode(this.value)">
                <option value="bulan" <?= $periode == 'bulan' ? 'selected' : '' ?>>Per Bulan</option>
                <option value="semester" <?= $periode == 'semester' ? 'selected' : '' ?>>Per 6 Bulan</option>
                <option value="tahun" <?= $periode == 'tahun' ? 'selected' : '' ?>>Per Tahun</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Tahun</label>
            <select name="tahun" class="form-select form-select-sm">
                <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-2" id="bulan-select">
            <label class="form-label small">Bulan</label>
            <select name="bulan" class="form-select form-select-sm">
                <?php 
                $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                for ($m = 1; $m <= 12; $m++): 
                ?>
                <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= $bulan == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>><?= $months[$m-1] ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" name="action" value="filter" class="btn btn-sm btn-sigma"><i class="fas fa-filter"></i> Filter</button>
            <a href="<?= BASE_URL ?>/laporan/turnover" class="btn btn-sm btn-outline-secondary"><i class="fas fa-sync-alt"></i></a>
            <a id="btnCetak" href="<?= BASE_URL ?>/laporan/cetakTurnover?periode=<?= $periode ?>&tahun=<?= $tahun ?>&bulan=<?= $bulan ?>" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fas fa-print"></i> Cetak</a>
        </div>
    </form>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-success">
            <div class="card-body text-center">
                <div class="text-muted small">Total Crew Aktif</div>
                <div class="stat-value text-success"><?= $totalCrew ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-info">
            <div class="card-body text-center">
                <div class="text-muted small">Crew Masuk</div>
                <div class="stat-value text-info"><?= $turnover['total_masuk'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-danger">
            <div class="card-body text-center">
                <div class="text-muted small">Crew Keluar</div>
                <div class="stat-value text-danger"><?= $turnover['total_keluar'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card border-warning">
            <div class="card-body text-center">
                <div class="text-muted small">Turn Over Rate</div>
                <div class="stat-value text-warning">
                    <?= $totalCrew > 0 ? round($turnover['total_keluar'] / $totalCrew * 100, 1) : 0 ?>%
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart -->
<div class="card-custom mb-3">
    <div class="card-body p-4">
        <h5 class="mb-3"><i class="fas fa-chart-bar"></i> Grafik Turn Over Tahun <?= $tahun ?></h5>
        <div style="height:300px;">
            <canvas id="turnover-chart"></canvas>
        </div>
    </div>
</div>

<!-- Tabel Crew Keluar -->
<div class="table-card mb-3">
    <div class="table-head">
        <div class="chart-title"><i class="fas fa-sign-out-alt chart-icon-red"></i> Daftar Crew Keluar (<?= $turnover['total_keluar'] ?>)</div>
    </div>
    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Nama</th><th>Posisi</th><th>Rig</th><th>Crew</th><th>Tgl Nonaktif</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($turnover['keluar'] && $turnover['keluar']->num_rows > 0): $no = 1; ?>
                    <?php while ($c = $turnover['keluar']->fetch_assoc()): ?>
                    <tr>
                        <td class="td-muted"><?= $no++ ?></td>
                        <td><strong><?= htmlspecialchars($c['nama']) ?></strong></td>
                        <td class="td-muted"><?= htmlspecialchars($c['posisi']) ?></td>
                        <td><span class="rtag"><?= htmlspecialchars($c['kode_rig']) ?></span></td>
                        <td><?= htmlspecialchars($c['crew'] ?? '-') ?></td>
                        <td class="td-sm"><?= $c['tanggal_nonaktif'] ? date('d/m/Y', strtotime($c['tanggal_nonaktif'])) : '-' ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="td-empty">Tidak ada crew keluar di periode ini</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tabel Crew Masuk -->
<div class="table-card mb-3">
    <div class="table-head">
        <div class="chart-title"><i class="fas fa-sign-in-alt chart-icon-green"></i> Daftar Crew Masuk (<?= $turnover['total_masuk'] ?>)</div>
    </div>
    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Nama</th><th>Posisi</th><th>Rig</th><th>Crew</th><th>Tgl Masuk</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($turnover['masuk'] && $turnover['masuk']->num_rows > 0): $no = 1; ?>
                    <?php while ($c = $turnover['masuk']->fetch_assoc()): ?>
                    <tr>
                        <td class="td-muted"><?= $no++ ?></td>
                        <td><strong><?= htmlspecialchars($c['nama']) ?></strong></td>
                        <td class="td-muted"><?= htmlspecialchars($c['posisi']) ?></td>
                        <td><span class="rtag"><?= htmlspecialchars($c['kode_rig']) ?></span></td>
                        <td><?= htmlspecialchars($c['crew'] ?? '-') ?></td>
                        <td class="td-sm"><?= $c['tanggal_masuk'] ? date('d/m/Y', strtotime($c['tanggal_masuk'])) : '-' ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="td-empty">Tidak ada crew masuk di periode ini</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tabel Laporan Crew Aktif Lengkap -->
<div class="table-card mb-3">
    <div class="table-head d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="chart-title"><i class="fas fa-users text-primary me-1"></i> Laporan Detail Crew Aktif (<?= $totalCrew ?>)</div>
        <input type="text" id="searchCrewAktif" class="form-control form-control-sm w-auto" placeholder="Cari nama/posisi/rig..." onkeyup="filterTabelCrewAktif()">
    </div>
    <div class="tw">
        <table id="tabelCrewAktif">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Crew</th>
                    <th>Posisi / Jabatan</th>
                    <th>Rig</th>
                    <th>Crew</th>
                    <th>No. Telp / WA</th>
                    <th>NIK KTP</th>
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
                            <strong><?= htmlspecialchars($ac['nama']) ?></strong>
                            <div class="small text-muted">ID: #<?= $ac['id_crew'] ?></div>
                        </td>
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
                        <td class="td-sm"><?= htmlspecialchars($ac['nik_ktp'] ?? '-') ?></td>
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
                    <tr><td colspan="10" class="td-empty">Tidak ada data crew aktif</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterTabelCrewAktif() {
    const input = document.getElementById("searchCrewAktif");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("tabelCrewAktif");
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

<script>
function togglePeriode(value) {
    const bulanSelect = document.getElementById('bulan-select');
    if (value == 'tahun') {
        bulanSelect.style.display = 'none';
    } else {
        bulanSelect.style.display = 'block';
    }
}

// Chart.js - Dinamis sesuai filter periode
const chartData = <?= json_encode($chartData) ?>;
const periode = '<?= $periode ?>';
const bulan = '<?= $bulan ?>';
const tahun = '<?= $tahun ?>';
const monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

let labels, masukData, keluarData, chartTitle;

if (periode === 'bulan') {
    // 1 bulan sesuai pilihan
    const bulanIdx = parseInt(bulan);
    labels = [monthNames[bulanIdx - 1]];
    masukData = [chartData[bulanIdx]?.masuk || 0];
    keluarData = [chartData[bulanIdx]?.keluar || 0];
    chartTitle = 'Grafik Turn Over Bulan ' + monthNames[bulanIdx - 1] + ' ' + tahun;
} else if (periode === 'semester') {
    // 6 bulan sesuai semester
    const semester = parseInt(bulan) <= 6 ? 1 : 2;
    const startIdx = semester === 1 ? 0 : 6;
    const endIdx = semester === 1 ? 6 : 12;
    labels = monthNames.slice(startIdx, endIdx);
    masukData = labels.map((_, i) => chartData[startIdx + i + 1]?.masuk || 0);
    keluarData = labels.map((_, i) => chartData[startIdx + i + 1]?.keluar || 0);
    chartTitle = 'Grafik Turn Over Semester ' + semester + ' ' + tahun;
} else {
    // 12 bulan
    labels = monthNames;
    masukData = labels.map((_, i) => chartData[i+1]?.masuk || 0);
    keluarData = labels.map((_, i) => chartData[i+1]?.keluar || 0);
    chartTitle = 'Grafik Turn Over Tahun ' + tahun;
}

// Update judul chart
document.querySelector('.card-custom h5').textContent = ' ' + chartTitle;

new Chart(document.getElementById('turnover-chart'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            { label: 'Masuk', data: masukData, backgroundColor: '#0f9b58', borderRadius: 5 },
            { label: 'Keluar', data: keluarData, backgroundColor: '#B31312', borderRadius: 5 }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});

// Update cetak link when filters change
document.querySelectorAll('select[name="periode"], select[name="tahun"], select[name="bulan"]').forEach(el => {
    el.addEventListener('change', updateCetakLink);
});

function updateCetakLink() {
    const periode = document.querySelector('select[name="periode"]').value;
    const tahun = document.querySelector('select[name="tahun"]').value;
    const bulan = document.querySelector('select[name="bulan"]').value;
    const base = '<?= BASE_URL ?>/laporan/cetakTurnover';
    document.getElementById('btnCetak').href = base + '?periode=' + periode + '&tahun=' + tahun + '&bulan=' + bulan;
}

// Toggle bulan on load
togglePeriode('<?= $periode ?>');
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>