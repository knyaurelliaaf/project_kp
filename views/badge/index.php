<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-id-card"></i> Monitoring Badge</h4>
</div>

<?php if (($expiredCount ?? 0) > 0 || ($soonCount ?? 0) > 0): ?>
<div class="alert alert-expired-banner d-flex align-items-center justify-content-between p-3 mb-3">
    <div class="d-flex align-items-center gap-3">
        <div class="alert-icon-box text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 18px; flex-shrink: 0; background: var(--p700, #B03060);">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div>
            <h6 class="mb-1 fw-bold" style="color: var(--p900, #7D2645);"><i class="fas fa-bell me-1"></i> Perhatian: Dokumen Badge Membutuhkan Tindakan</h6>
            <div class="small text-dark">
                <?php if (($expiredCount ?? 0) > 0): ?>
                    <span class="badge bg-danger me-1" style="font-size:12px;"><i class="fas fa-times-circle"></i> <?= $expiredCount ?> Kadaluarsa (Expired)</span>
                <?php endif; ?>
                <?php if (($soonCount ?? 0) > 0): ?>
                    <span class="badge bg-warning text-dark me-1" style="font-size:12px;"><i class="fas fa-clock"></i> <?= $soonCount ?> Akan Kadaluarsa (≤30 hari)</span>
                <?php endif; ?>
                <span class="text-muted ms-1">• Segera perbarui atau lakukan perpanjangan badge crew.</span>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <?php if (($expiredCount ?? 0) > 0): ?>
            <a href="<?= BASE_URL ?>/badge?status=exp" class="btn btn-sm btn-danger shadow-sm"><i class="fas fa-filter me-1"></i> Lihat Expired</a>
        <?php endif; ?>
        <?php if (($soonCount ?? 0) > 0): ?>
            <a href="<?= BASE_URL ?>/badge?status=soon" class="btn btn-sm btn-warning text-dark shadow-sm"><i class="fas fa-clock me-1"></i> Lihat Warning</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Filter -->
<div class="filter-bar mb-3">
    <form method="GET" action="<?= BASE_URL ?>/badge" class="row g-2 align-items-end">
        <?php if ($isSuperAdmin): ?>
        <div class="col-md-2">
            <label class="form-label small">Rig</label>
            <select name="rig" class="form-select form-select-sm">
                <option value="">Semua</option>
                <?php foreach ($rigList as $rig): ?>
                <option value="<?= $rig['kode_rig'] ?>" <?= $filter_rig == $rig['kode_rig'] ? 'selected' : '' ?>><?= $rig['kode_rig'] ?></option>
                <?php endforeach; ?>
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
                <tr><th>#</th><th>Nama Crew</th><th>Rig</th><th>Posisi</th><th>No Badge</th><th>Expired</th><th>Sisa Hari</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody id="badge-tbody">
                <?php if ($badges && $badges->num_rows > 0): $no = $offset; ?>
                    <?php while ($b = $badges->fetch_assoc()): $no++;
                        $sisa = $b['sisa_hari'];
                        $statusClass = $sisa < 0 ? 'dex' : ($sisa <= 30 ? 'dsn' : 'dok');
                        $statusText = $sisa < 0 ? 'Expired' : ($sisa <= 30 ? $sisa.' hari' : 'Valid');
                    ?>
                    <tr>
                        <td class="td-muted"><?= $no ?></td>
                        <td><a href="<?= BASE_URL ?>/crew/detail/<?= $b['id_crew'] ?>?from=badge" class="cn-link"><strong><?= $b['nama'] ?></strong></a></td>
                        <td><span class="rtag"><?= $b['kode_rig'] ?></span></td>
                        <td><?= $b['posisi'] ?? '-' ?></td>
                        <td><?= $b['nomor_badge'] ?? '-' ?></td>
                        <td class="td-sm"><?= $b['tanggal_expired'] ? date('d/m/Y', strtotime($b['tanggal_expired'])) : '-' ?></td>
                        <td class="td-bold"><?= $sisa ?> hari</td>
                        <td><span class="db <?= $statusClass ?>"><?= $statusText ?></span></td>
                        <td><a href="<?= BASE_URL ?>/crew/detail/<?= $b['id_crew'] ?>?from=badge" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="td-empty">Tidak ada data badge</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <div id="badge-pagination" class="pagination-container">
        <?php if ($totalPages > 1): ?>
            <a href="javascript:void(0)" onclick="loadBadgePage(<?= $page-1 ?>)" class="btn btn-sm btn-prev <?= $page <= 1 ? 'disabled' : '' ?>">&laquo;</a>
            <span id="badge-pagination-numbers" class="pagination-numbers">
                <?= Helper::renderPaginationNumbers($page ?? 1, $totalPages ?? 1, 'loadBadgePage') ?>
            </span>
            <a href="javascript:void(0)" onclick="loadBadgePage(<?= $page+1 ?>)" class="btn btn-sm btn-next <?= $page >= $totalPages ? 'disabled' : '' ?>">&raquo;</a>
        <?php endif; ?>

            <script>
    // Reset to clean initial page when coming back via browser back button from another page
    (function() {
        var hasFilterParams = window.location.search.indexOf('rig=') > -1 || 
                              window.location.search.indexOf('status=') > -1 || 
                              window.location.search.indexOf('search=') > -1 ||
                              window.location.search.indexOf('page=') > -1;
        if (hasFilterParams) {
            // Check if navigated here via back/forward
            var navEntries = performance.getEntriesByType('navigation');
            if (navEntries.length > 0 && navEntries[0].type === 'back_forward') {
                window.location.replace('<?= BASE_URL ?>/badge');
            }
        }
    })();

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
