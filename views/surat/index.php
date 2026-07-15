<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-envelope"></i> Manajemen Surat Keluar</h4>
    <a href="<?= BASE_URL ?>/surat/create" class="btn btn-sigma">
        <i class="fas fa-plus"></i> Buat Surat Baru
    </a>
</div>

<!-- Filter -->
<div class="filter-bar mb-3">
    <form method="GET" action="<?= BASE_URL ?>/surat" class="row g-2 align-items-end" id="filter-form">
        <?php if ($isSuperAdmin): ?>
            <div class="col-md-2">
                <label class="form-label small">Rig</label>
                <select name="rig" class="form-select form-select-sm" id="filter-rig">
                    <option value="">Semua</option>
                    <?php while ($rig = $rigList->fetch_assoc()): ?>
                        <option value="<?= $rig['kode_rig'] ?>" <?= $filter_rig == $rig['kode_rig'] ? 'selected' : '' ?>><?= $rig['kode_rig'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        <?php endif; ?>
        <div class="col-md-2">
            <label class="form-label small">Jenis</label>
            <select name="jenis" class="form-select form-select-sm" id="filter-jenis">
                <option value="">Semua</option>
                <?php while ($j = $jenis_surat->fetch_assoc()): ?>
                    <option value="<?= $j['kode'] ?>" <?= $filter_jenis == $j['kode'] ? 'selected' : '' ?>><?= $j['kode'] ?> - <?= $j['nama_surat'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Tahun</label>
            <select name="tahun" class="form-select form-select-sm" id="filter-tahun">
                <option value="">Semua</option>
                <?php for ($y = date('Y'); $y >= 2025; $y--): ?>
                    <option value="<?= $y ?>" <?= $filter_tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Cari</label>
            <input type="text" name="search" class="form-control form-control-sm" id="filter-search" placeholder="Nomor surat atau keterangan..." value="<?= htmlspecialchars($filter_search) ?>">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-sm btn-sigma"><i class="fas fa-filter"></i> Filter</button>
            <a href="<?= BASE_URL ?>/surat" class="btn btn-sm btn-outline-secondary"><i class="fas fa-sync-alt"></i></a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="table-card" id="surat-table">
    <div class="table-head">
        <div class="chart-title"><i class="fas fa-table chart-icon-red"></i> Daftar Surat Keluar (<span id="total-surat"><?= $totalSuratAll ?></span> surat)</div>
    </div>
    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th class="th-no">No</th>
                    <th class="th-nomor">Nomor Surat</th>
                    <th class="th-jenis">Jenis</th>
                    <th class="th-rig">Rig</th>
                    <th class="th-tgl">Tanggal</th>
                    <th>Keterangan</th>
                    <th class="th-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody id="surat-tbody">
                <?php if ($surat && $surat->num_rows > 0): $no = $offset; ?>
                    <?php while ($s = $surat->fetch_assoc()): $no++; ?>
                        <tr>
                            <td class="td-muted"><?= $no ?></td>
                            <td><strong class="cn"><?= $s['nomor_surat'] ?></strong></td>
                            <td><span class="rtag"><?= $s['kode_jenis'] ?></span></td>
                            <td><?= $s['kode_rig'] ?></td>
                            <td class="td-sm"><?= $s['tanggal_moc'] ? date('d/m/Y', strtotime($s['tanggal_moc'])) : '-' ?></td>
                            <td class="td-ellipsis"><?= htmlspecialchars($s['keterangan'] ?? '-') ?></td>
                            <td class="table-actions-cell">
                                <div class="table-actions">
                                    <a href="<?= BASE_URL ?>/surat/cetak/<?= $s['id_surat'] ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Cetak"><i class="fas fa-print"></i></a>
                                    <a href="<?= BASE_URL ?>/surat/edit/<?= $s['id_surat'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?= BASE_URL ?>/surat/delete/<?= $s['id_surat'] ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Hapus" onclick="return confirm('Hapus surat ini?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>

                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="td-empty">Belum ada surat</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div id="surat-pagination" class="pagination-container">
        <?php if ($totalPages > 1): ?>
            <a href="javascript:void(0)" onclick="loadSuratPage(<?= $page - 1 ?>)" class="btn btn-sm btn-prev <?= $page <= 1 ? 'disabled' : '' ?>">&laquo;</a>
            <span id="surat-pagination-numbers" class="pagination-numbers">
                <?php for ($p = 1; $p <= min($totalPages, 10); $p++): ?>
                    <a href="javascript:void(0)" onclick="loadSuratPage(<?= $p ?>)" class="btn btn-sm btn-page <?= $p == $page ? 'active' : '' ?>"><?= $p ?></a>
                <?php endfor; ?>
            </span>
            <a href="javascript:void(0)" onclick="loadSuratPage(<?= $page + 1 ?>)" class="btn btn-sm btn-next <?= $page >= $totalPages ? 'disabled' : '' ?>">&raquo;</a>
        <?php endif; ?>
    </div>
</div>

<script>
    function loadSuratPage(page) {
        const rig = document.getElementById('filter-rig')?.value || '';
        const jenis = document.getElementById('filter-jenis')?.value || '';
        const tahun = document.getElementById('filter-tahun')?.value || '';
        const search = document.getElementById('filter-search')?.value || '';

        const params = new URLSearchParams({
            page,
            rig,
            jenis,
            tahun,
            search
        });

        fetch('<?= BASE_URL ?>/ajax/suratTable?' + params.toString())
            .then(res => res.json())
            .then(data => {
                document.getElementById('surat-tbody').innerHTML = data.html;
                document.getElementById('surat-pagination-numbers').innerHTML = data.pagination;
                document.getElementById('total-surat').textContent = data.total;

                const prevBtn = document.querySelector('#surat-pagination .btn-prev');
                if (data.page > 1) {
                    prevBtn.classList.remove('disabled');
                    prevBtn.setAttribute('onclick', 'loadSuratPage(' + (data.page - 1) + ')');
                } else {
                    prevBtn.classList.add('disabled');
                    prevBtn.removeAttribute('onclick');
                }

                const nextBtn = document.querySelector('#surat-pagination .btn-next');
                if (data.page < data.totalPages) {
                    nextBtn.classList.remove('disabled');
                    nextBtn.setAttribute('onclick', 'loadSuratPage(' + (data.page + 1) + ')');
                } else {
                    nextBtn.classList.add('disabled');
                    nextBtn.removeAttribute('onclick');
                }
            });
    }
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>