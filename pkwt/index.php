<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-file-contract"></i> Monitoring PKWT</h4>
</div>

<div class="filter-bar mb-3">
    <form onsubmit="return false;" class="row g-2 align-items-end">
        <?php if ($isSuperAdmin): ?>
        <div class="col-md-2">
            <label class="form-label small">Rig</label>
            <select name="rig" class="form-select form-select-sm" id="filter-rig">
                <option value="">Semua</option>
                <?php $rigList->data_seek(0); while ($rig = $rigList->fetch_assoc()): ?>
                <option value="<?= $rig['kode_rig'] ?>" <?= $filter_rig == $rig['kode_rig'] ? 'selected' : '' ?>><?= $rig['kode_rig'] ?></option>
                <?php endwhile; ?>
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
            <button type="button" class="btn btn-sm btn-sigma" onclick="loadPkwtPage(1)"><i class="fas fa-filter"></i> Filter</button>
            <a href="<?= BASE_URL ?>/pkwt" class="btn btn-sm btn-outline-secondary"><i class="fas fa-sync-alt"></i></a>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-head">
        <div class="chart-title"><i class="fas fa-file-contract chart-icon-red"></i> Daftar PKWT (<span id="total-count"><?= $total ?></span>)</div>
    </div>
    <div class="tw">
        <table>
            <thead><tr><th>#</th><th>Nama Crew</th><th>Rig</th><th>Mulai</th><th>Berakhir</th><th>Sisa Hari</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody id="pkwt-tbody">
                <?php if ($pkwts && $pkwts->num_rows > 0): $no = $offset; ?>
                    <?php while ($p = $pkwts->fetch_assoc()): $no++;
                        $sisa = $p['sisa_hari'];
                        $sc = $sisa < 0 ? 'dex' : ($sisa <= 30 ? 'dsn' : 'dok');
                        $st = $sisa < 0 ? 'Expired' : ($sisa <= 30 ? $sisa.' hari' : 'Valid');
                    ?>
                    <tr>
                        <td class="td-muted"><?= $no ?></td>
                        <td><a href="<?= BASE_URL ?>/crew/detail/<?= $p['id_crew'] ?>" class="cn-link"><strong><?= $p['nama'] ?></strong></a></td>
                        <td><span class="rtag"><?= $p['kode_rig'] ?></span></td>
                        <td class="td-sm"><?= $p['tanggal_mulai'] ? date('d/m/Y', strtotime($p['tanggal_mulai'])) : '-' ?></td>
                        <td class="td-sm"><?= $p['tanggal_berakhir'] ? date('d/m/Y', strtotime($p['tanggal_berakhir'])) : '-' ?></td>
                        <td class="td-bold"><?= $sisa ?> hari</td>
                        <td><span class="db <?= $sc ?>"><?= $st ?></span></td>
                        <td><a href="<?= BASE_URL ?>/crew/detail/<?= $p['id_crew'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="td-empty">Tidak ada data PKWT</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="pkwt-pagination" class="pagination-container">
        <?php if ($totalPages > 1): ?>
            <a href="javascript:void(0)" onclick="loadPkwtPage(<?= $page-1 ?>)" class="btn btn-sm btn-prev <?= $page<=1?'disabled':'' ?>">&laquo;</a>
            <span id="pkwt-pagination-numbers" class="pagination-numbers">
                <?php for ($p=1; $p<=min($totalPages,10); $p++): ?>
                <a href="javascript:void(0)" onclick="loadPkwtPage(<?= $p ?>)" class="btn btn-sm btn-page <?= $p==$page?'active':'' ?>"><?= $p ?></a>
                <?php endfor; ?>
            </span>
            <a href="javascript:void(0)" onclick="loadPkwtPage(<?= $page+1 ?>)" class="btn btn-sm btn-next <?= $page>=$totalPages?'disabled':'' ?>">&raquo;</a>
        <?php endif; ?>
    </div>
</div>

<script>
function loadPkwtPage(page) {
    const rig = document.getElementById('filter-rig')?.value || '';
    const status = document.getElementById('filter-status')?.value || '';
    const search = document.getElementById('filter-search')?.value || '';
    fetch('<?= BASE_URL ?>/ajax/pkwtTable?' + new URLSearchParams({page,rig,status,search}))
        .then(r=>r.json()).then(d=>{
            document.getElementById('pkwt-tbody').innerHTML = d.html;
            document.getElementById('pkwt-pagination-numbers').innerHTML = d.pagination;
            document.getElementById('total-count').textContent = d.total;
            const pb=document.querySelector('#pkwt-pagination .btn-prev');
            const nb=document.querySelector('#pkwt-pagination .btn-next');
            if(d.page>1){pb.classList.remove('disabled');pb.setAttribute('onclick','loadPkwtPage('+(d.page-1)+')');}
            else{pb.classList.add('disabled');pb.removeAttribute('onclick');}
            if(d.page<d.totalPages){nb.classList.remove('disabled');nb.setAttribute('onclick','loadPkwtPage('+(d.page+1)+')');}
            else{nb.classList.add('disabled');nb.removeAttribute('onclick');}
        });
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>