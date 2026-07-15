<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-stethoscope"></i> Monitoring MCU</h4>
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
            <button type="button" class="btn btn-sm btn-sigma" onclick="loadMcuPage(1)"><i class="fas fa-filter"></i> Filter</button>
            <a href="<?= BASE_URL ?>/mcu" class="btn btn-sm btn-outline-secondary"><i class="fas fa-sync-alt"></i></a>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="table-head">
        <div class="chart-title"><i class="fas fa-stethoscope chart-icon-red"></i> Daftar MCU (<span id="total-count"><?= $total ?></span>)</div>
    </div>
    <div class="tw">
        <table>
            <thead><tr><th>#</th><th>Nama Crew</th><th>Rig</th><th>Expired</th><th>Derajat</th><th>Next MCU</th><th>Sisa Hari</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody id="mcu-tbody">
                <?php if ($mcus && $mcus->num_rows > 0): $no = $offset; ?>
                    <?php while ($m = $mcus->fetch_assoc()): $no++;
                        $sisa = $m['sisa_hari'];
                        $sc = $sisa < 0 ? 'dex' : ($sisa <= 30 ? 'dsn' : 'dok');
                        $st = $sisa < 0 ? 'Expired' : ($sisa <= 30 ? $sisa.' hari' : 'Valid');
                    ?>
                    <tr>
                        <td class="td-muted"><?= $no ?></td>
                        <td><a href="<?= BASE_URL ?>/crew/detail/<?= $m['id_crew'] ?>" class="cn-link"><strong><?= $m['nama'] ?></strong></a></td>
                        <td><span class="rtag"><?= $m['kode_rig'] ?></span></td>
                        <td class="td-sm"><?= $m['expired'] ? date('d/m/Y', strtotime($m['expired'])) : '-' ?></td>
                        <td><?= $m['derajat_kesehatan'] ?? '-' ?></td>
                        <td class="td-sm"><?= $m['expired'] ? date('d/m/Y', strtotime($m['expired'])) : '-' ?></td>
                        <td class="td-bold"><?= $sisa ?> hari</td>
                        <td><span class="db <?= $sc ?>"><?= $st ?></span></td>
                        <td><a href="<?= BASE_URL ?>/crew/detail/<?= $m['id_crew'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="td-empty">Tidak ada data MCU</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="mcu-pagination" class="pagination-container">
        <?php if ($totalPages > 1): ?>
            <a href="javascript:void(0)" onclick="loadMcuPage(<?= $page-1 ?>)" class="btn btn-sm btn-prev <?= $page<=1?'disabled':'' ?>">&laquo;</a>
            <span id="mcu-pagination-numbers" class="pagination-numbers">
                <?php for ($p=1; $p<=min($totalPages,10); $p++): ?>
                <a href="javascript:void(0)" onclick="loadMcuPage(<?= $p ?>)" class="btn btn-sm btn-page <?= $p==$page?'active':'' ?>"><?= $p ?></a>
                <?php endfor; ?>
            </span>
            <a href="javascript:void(0)" onclick="loadMcuPage(<?= $page+1 ?>)" class="btn btn-sm btn-next <?= $page>=$totalPages?'disabled':'' ?>">&raquo;</a>
        <?php endif; ?>
    </div>
</div>

<script>
function loadMcuPage(page) {
    const rig = document.getElementById('filter-rig')?.value || '';
    const status = document.getElementById('filter-status')?.value || '';
    const search = document.getElementById('filter-search')?.value || '';
    fetch('<?= BASE_URL ?>/ajax/mcuTable?' + new URLSearchParams({page,rig,status,search}))
        .then(r=>r.json()).then(d=>{
            document.getElementById('mcu-tbody').innerHTML = d.html;
            document.getElementById('mcu-pagination-numbers').innerHTML = d.pagination;
            document.getElementById('total-count').textContent = d.total;
            const pb=document.querySelector('#mcu-pagination .btn-prev');
            const nb=document.querySelector('#mcu-pagination .btn-next');
            if(d.page>1){pb.classList.remove('disabled');pb.setAttribute('onclick','loadMcuPage('+(d.page-1)+')');}
            else{pb.classList.add('disabled');pb.removeAttribute('onclick');}
            if(d.page<d.totalPages){nb.classList.remove('disabled');nb.setAttribute('onclick','loadMcuPage('+(d.page+1)+')');}
            else{nb.classList.add('disabled');nb.removeAttribute('onclick');}
        });
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>