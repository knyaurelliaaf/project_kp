<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-certificate"></i> Monitoring Sertifikat</h4>
</div>

<?php if (($expiredCount ?? 0) > 0 || ($soonCount ?? 0) > 0): ?>
<div class="alert alert-expired-banner d-flex align-items-center justify-content-between p-3 mb-3">
    <div class="d-flex align-items-center gap-3">
        <div class="alert-icon-box text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 18px; flex-shrink: 0; background: var(--p700, #B03060);">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div>
            <h6 class="mb-1 fw-bold" style="color: var(--p900, #7D2645);"><i class="fas fa-bell me-1"></i> Perhatian: Sertifikat Crew Membutuhkan Tindakan</h6>
            <div class="small text-dark">
                <?php if (($expiredCount ?? 0) > 0): ?>
                    <span class="badge bg-danger me-1" style="font-size:12px;"><i class="fas fa-times-circle"></i> <?= $expiredCount ?> Kadaluarsa (Expired)</span>
                <?php endif; ?>
                <?php if (($soonCount ?? 0) > 0): ?>
                    <span class="badge bg-warning text-dark me-1" style="font-size:12px;"><i class="fas fa-clock"></i> <?= $soonCount ?> Akan Kadaluarsa (≤30 hari)</span>
                <?php endif; ?>
                <span class="text-muted ms-1">• Segera lakukan perpanjangan sertifikat crew.</span>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <?php if (($expiredCount ?? 0) > 0): ?>
            <a href="<?= BASE_URL ?>/sertifikat?status=exp" class="btn btn-sm btn-danger shadow-sm"><i class="fas fa-filter me-1"></i> Lihat Expired</a>
        <?php endif; ?>
        <?php if (($soonCount ?? 0) > 0): ?>
            <a href="<?= BASE_URL ?>/sertifikat?status=soon" class="btn btn-sm btn-warning text-dark shadow-sm"><i class="fas fa-clock me-1"></i> Lihat Warning</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="filter-bar mb-3">
    <form onsubmit="return false;" class="row g-2 align-items-end">
        <?php if ($isSuperAdmin): ?>
        <div class="col-md-2">
            <label class="form-label small">Rig</label>
            <select name="rig" class="form-select form-select-sm" id="filter-rig">
                <option value="">Semua</option>
                <?php foreach ($rigList as $rig): ?>
                <option value="<?= $rig['kode_rig'] ?>" <?= $filter_rig == $rig['kode_rig'] ? 'selected' : '' ?>><?= $rig['kode_rig'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="col-md-2">
            <label class="form-label small">Status</label>
            <select name="status" class="form-select form-select-sm" id="filter-status">
                <option value="">Semua</option>
                <option value="ok" <?= $filter_status == 'ok' ? 'selected' : '' ?>>Valid</option>
                <option value="soon" <?= $filter_status == 'soon' ? 'selected' : '' ?>>Segera</option>
                <option value="exp" <?= $filter_status == 'exp' ? 'selected' : '' ?>>Expired</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Cari</label>
            <input type="text" name="search" class="form-control form-control-sm" id="filter-search" placeholder="Nama crew..." value="<?= htmlspecialchars($filter_search) ?>">
        </div>
        <div class="col-md-3">
            <button type="button" class="btn btn-sm btn-sigma" onclick="loadSertPage(1)"><i class="fas fa-filter"></i> Filter</button>
            <a href="<?= BASE_URL ?>/sertifikat" class="btn btn-sm btn-outline-secondary"><i class="fas fa-sync-alt"></i></a>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-head">
        <div class="chart-title"><i class="fas fa-certificate chart-icon-red"></i> Daftar Sertifikat (<span id="total-count"><?= $total ?></span>)</div>
    </div>
    <div class="tw">
        <table>
            <thead><tr><th>#</th><th>Nama Crew</th><th>Rig</th><th>Posisi</th><th>Jenis</th><th>Expired</th><th>Sisa Hari</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody id="sert-tbody">
                <?php if ($serts && $serts->num_rows > 0): $no = $offset; ?>
                    <?php while ($s = $serts->fetch_assoc()): $no++;
                        $sisa = $s['sisa_hari'];
                        $sc = $sisa < 0 ? 'dex' : ($sisa <= 30 ? 'dsn' : 'dok');
                        $st = $sisa < 0 ? 'Expired' : ($sisa <= 30 ? $sisa.' hari' : 'Valid');
                    ?>
                    <tr>
                        <td class="td-muted"><?= $no ?></td>
                        <td><a href="<?= BASE_URL ?>/crew/detail/<?= $s['id_crew'] ?>?from=sertifikat" class="cn-link"><strong><?= $s['nama'] ?></strong></a></td>
                        <td><span class="rtag"><?= $s['kode_rig'] ?></span></td>
                        <td><?= $s['posisi'] ?? '-' ?></td>
                        <td><?= $s['jenis'] ?></td>
                        <td class="td-sm"><?= $s['tanggal_expired'] ? date('d/m/Y', strtotime($s['tanggal_expired'])) : '-' ?></td>
                        <td class="td-bold"><?= $sisa ?> hari</td>
                        <td><span class="db <?= $sc ?>"><?= $st ?></span></td>
                        <td><a href="<?= BASE_URL ?>/crew/detail/<?= $s['id_crew'] ?>?from=sertifikat" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="td-empty">Tidak ada data sertifikat</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="sert-pagination" class="pagination-container">
        <?php if ($totalPages > 1): ?>
            <a href="javascript:void(0)" onclick="loadSertPage(<?= $page-1 ?>)" class="btn btn-sm btn-prev <?= $page<=1?'disabled':'' ?>">&laquo;</a>
            <span id="sert-pagination-numbers" class="pagination-numbers">
                <?= Helper::renderPaginationNumbers($page ?? 1, $totalPages ?? 1, 'loadSertPage') ?>
            </span>
            <a href="javascript:void(0)" onclick="loadSertPage(<?= $page+1 ?>)" class="btn btn-sm btn-next <?= $page>=$totalPages?'disabled':'' ?>">&raquo;</a>
        <?php endif; ?>
    </div>
</div>

<script>
function loadSertPage(page) {
    const rig = document.getElementById('filter-rig')?.value || '';
    const status = document.getElementById('filter-status')?.value || '';
    const search = document.getElementById('filter-search')?.value || '';
    fetch('<?= BASE_URL ?>/ajax/sertifikatTable?' + new URLSearchParams({page,rig,status,search}))
        .then(r=>r.json()).then(d=>{
            document.getElementById('sert-tbody').innerHTML = d.html;
            document.getElementById('sert-pagination-numbers').innerHTML = d.pagination;
            document.getElementById('total-count').textContent = d.total;
            const pb=document.querySelector('#sert-pagination .btn-prev');
            const nb=document.querySelector('#sert-pagination .btn-next');
            if(d.page>1){pb.classList.remove('disabled');pb.setAttribute('onclick','loadSertPage('+(d.page-1)+')');}
            else{pb.classList.add('disabled');pb.removeAttribute('onclick');}
            if(d.page<d.totalPages){nb.classList.remove('disabled');nb.setAttribute('onclick','loadSertPage('+(d.page+1)+')');}
            else{nb.classList.add('disabled');nb.removeAttribute('onclick');}
        });
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
