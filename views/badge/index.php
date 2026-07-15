<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-id-card"></i> Monitoring Badge</h4>
</div>

<!-- Filter -->
<div class="filter-bar mb-3">
    <form method="GET" action="<?= BASE_URL ?>/badge" class="row g-2 align-items-end">
        <?php if ($isSuperAdmin): ?>
        <div class="col-md-2">
            <label class="form-label small">Rig</label>
            <select name="rig" class="form-select form-select-sm">
                <option value="">Semua</option>
                <?php $rigList->data_seek(0); while ($rig = $rigList->fetch_assoc()): ?>
                <option value="<?= $rig['kode_rig'] ?>" <?= $filter_rig == $rig['kode_rig'] ? 'selected' : '' ?>><?= $rig['kode_rig'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="col-md-2">
            <label class="form-label small">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="ok" <?= $filter_status == 'ok' ? 'selected' : '' ?>>Valid</option>
                <option value="soon" <?= $filter_status == 'soon' ? 'selected' : '' ?>>Segera (≤30 hari)</option>
                <option value="exp" <?= $filter_status == 'exp' ? 'selected' : '' ?>>Expired</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Cari</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama atau nomor badge..." value="<?= htmlspecialchars($filter_search) ?>">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-sm btn-sigma"><i class="fas fa-filter"></i> Filter</button>
            <a href="<?= BASE_URL ?>/badge" class="btn btn-sm btn-outline-secondary"><i class="fas fa-sync-alt"></i></a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="table-card" id="badge-table">
    <div class="table-head">
        <div class="chart-title"><i class="fas fa-id-card chart-icon-red"></i> Daftar Badge (<span><?= $totalBadges ?></span>)</div>
    </div>
    <div class="tw">
        <table>
            <thead>
                <tr><th>#</th><th>Nama Crew</th><th>Rig</th><th>No Badge</th><th>Expired</th><th>Sisa Hari</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php if ($badges && $badges->num_rows > 0): $no = $offset; ?>
                    <?php while ($b = $badges->fetch_assoc()): $no++;
                        $sisa = $b['sisa_hari'];
                        $statusClass = $sisa < 0 ? 'dex' : ($sisa <= 30 ? 'dsn' : 'dok');
                        $statusText = $sisa < 0 ? 'Expired' : ($sisa <= 30 ? $sisa.' hari' : 'Valid');
                    ?>
                    <tr>
                        <td class="td-muted"><?= $no ?></td>
                        <td><a href="<?= BASE_URL ?>/crew/detail/<?= $b['id_crew'] ?>" class="cn-link"><strong><?= $b['nama'] ?></strong></a></td>
                        <td><span class="rtag"><?= $b['kode_rig'] ?></span></td>
                        <td><?= $b['nomor_badge'] ?? '-' ?></td>
                        <td class="td-sm"><?= $b['tanggal_expired'] ? date('d/m/Y', strtotime($b['tanggal_expired'])) : '-' ?></td>
                        <td class="td-bold"><?= $sisa ?> hari</td>
                        <td><span class="db <?= $statusClass ?>"><?= $statusText ?></span></td>
                        <td><a href="<?= BASE_URL ?>/crew/detail/<?= $b['id_crew'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="td-empty">Tidak ada data badge</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <div id="badge-pagination" class="pagination-container">
        <?php if ($totalPages > 1): ?>
            <a href="javascript:void(0)" onclick="loadBadgePage(<?= $page-1 ?>)" class="btn btn-sm btn-prev <?= $page <= 1 ? 'disabled' : '' ?>">&laquo;</a>
            <span id="badge-pagination-numbers" class="pagination-numbers">
                <?php for ($p = 1; $p <= min($totalPages, 10); $p++): ?>
                <a href="javascript:void(0)" onclick="loadBadgePage(<?= $p ?>)" class="btn btn-sm btn-page <?= $p == $page ? 'active' : '' ?>"><?= $p ?></a>
                <?php endfor; ?>
            </span>
            <a href="javascript:void(0)" onclick="loadBadgePage(<?= $page+1 ?>)" class="btn btn-sm btn-next <?= $page >= $totalPages ? 'disabled' : '' ?>">&raquo;</a>
        <?php endif; ?>

            <script>
    function loadBadgePage(page) {
        const rig = document.querySelector('select[name="rig"]')?.value || '';
        const status = document.querySelector('select[name="status"]')?.value || '';
        const search = document.querySelector('input[name="search"]')?.value || '';
        const params = new URLSearchParams({ page, rig, status, search });
        
        fetch('<?= BASE_URL ?>/ajax/badgeTable?' + params.toString())
            .then(res => res.json())
            .then(data => {
                document.getElementById('badge-tbody').innerHTML = data.html;
                document.getElementById('badge-pagination-numbers').innerHTML = data.pagination;
                
                const prevBtn = document.querySelector('#badge-pagination .btn-prev');
                if (data.page > 1) { prevBtn.classList.remove('disabled'); prevBtn.setAttribute('onclick', 'loadBadgePage(' + (data.page - 1) + ')'); }
                else { prevBtn.classList.add('disabled'); prevBtn.removeAttribute('onclick'); }
                
                const nextBtn = document.querySelector('#badge-pagination .btn-next');
                if (data.page < data.totalPages) { nextBtn.classList.remove('disabled'); nextBtn.setAttribute('onclick', 'loadBadgePage(' + (data.page + 1) + ')'); }
                else { nextBtn.classList.add('disabled'); nextBtn.removeAttribute('onclick'); }
            });
    }
        </script>
    </div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>