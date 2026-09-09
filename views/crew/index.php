<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-users"></i> Data Crew</h4>
    <a href="<?= BASE_URL ?>/crew/create" class="btn btn-sigma">
        <i class="fas fa-plus"></i> Tambah Crew
    </a>
</div>

<!-- Filter -->
<div class="filter-bar mb-3">
    <form method="GET" action="<?= BASE_URL ?>/crew" class="row g-2 align-items-end">
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
                <option value="aktif" <?= ($filter_status ?? '') == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="nonaktif" <?= ($filter_status ?? '') == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label small">Crew</label>
            <select name="crew" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="A" <?= ($filter_crew ?? '') == 'A' ? 'selected' : '' ?>>Crew A</option>
                <option value="B" <?= ($filter_crew ?? '') == 'B' ? 'selected' : '' ?>>Crew B</option>
                <option value="C" <?= ($filter_crew ?? '') == 'C' ? 'selected' : '' ?>>Crew C</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Cari</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama, posisi, ID..." value="<?= htmlspecialchars($filter_search) ?>">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-sm btn-sigma"><i class="fas fa-filter"></i> Filter</button>
            <a href="<?= BASE_URL ?>/crew?reset=1" class="btn btn-sm btn-outline-secondary" title="Reset Filter"><i class="fas fa-sync-alt"></i></a>
        </div>
    </form>
</div>

<div class="d-flex justify-content-end mb-2">
    <select class="form-select form-select-sm" style="width:180px;" onchange="applySort(this.value)">
        <option value="nama-asc" <?= ($sort == 'nama' && $order == 'ASC') ? 'selected' : '' ?>>Nama A-Z</option>
        <option value="nama-desc" <?= ($sort == 'nama' && $order == 'DESC') ? 'selected' : '' ?>>Nama Z-A</option>
        <option value="id_crew-asc" <?= ($sort == 'id_crew' && $order == 'ASC') ? 'selected' : '' ?>>ID Terkecil</option>
        <option value="id_crew-desc" <?= ($sort == 'id_crew' && $order == 'DESC') ? 'selected' : '' ?>>ID Terbesar</option>
    </select>
</div>

<!-- Table -->
<div class="table-card" id="crew-table">
    <div class="table-head">
        <div class="chart-title"><i class="fas fa-users chart-icon-red"></i> Daftar Crew (<span id="total-crew-count"><?= $totalCrew ?></span> crew)</div>
    </div>
    <div class="tw">
        <table>
            <thead>
                <tr>
                    <th class="th-no">#</th>
                    <th>Nama</th>
                    <th>Posisi</th>
                    <th class="th-rig">Rig</th>
                    <th>Crew</th>
                    <th>Badge</th>
                    <th>MCU</th>
                    <th>Sertifikat</th>
                    <th>PKWT</th>
                    <th class="th-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody id="crew-tbody">
                <?php if ($crew && $crew->num_rows > 0): $no = $offset;
                    $colors = ['#2B2A4C', '#B31312', '#0f9b58', '#7c3aed', '#0891b2', '#b45309', '#be185d', '#0369a1'];
                    $fq = !empty($filterQuery) ? '?' . $filterQuery : '';
                ?>
                    <?php while ($c = $crew->fetch_assoc()): $no++;
                        $bs = $c['badge_status'] ?? 'ok';
                        $ms = $c['mcu_status'] ?? 'ok';
                        $ss = $c['sertifikat_status'] ?? 'ok';
                        $ps = $c['pkwt_status'] ?? 'ok';
                        $sl = ['ok' => '<span class="db dok"><i class="fas fa-check"></i> Valid</span>', 'soon' => '<span class="db dsn"><i class="fas fa-clock"></i> Segera</span>', 'exp' => '<span class="db dex"><i class="fas fa-times"></i> Expired</span>'];
                    ?>
                        <tr>
                            <td class="td-muted"><?= $no ?></td>
                            <td>
                                <div class="crew-av">
                                    <div class="cav" style="background:<?= $colors[$no % count($colors)] ?>"><?= strtoupper(substr($c['nama'], 0, 2)) ?></div>
                                    <div>
                                        <a href="<?= BASE_URL ?>/crew/detail/<?= $c['id_crew'] ?><?= $fq ?>" class="cn-link">
                                            <div class="cn">
                                                <?= $c['nama'] ?>
                                                <?php if (($c['status_aktif'] ?? 'aktif') == 'nonaktif'): ?>
                                                    <span class="stbadge st-n" style="font-size:10px;margin-left:5px;">Nonaktif</span>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                        <div class="cp">ID: <?= $c['id_crew'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="td-muted"><?= $c['posisi'] ?></td>
                            <td><span class="rtag"><?= $c['kode_rig'] ?></span></td>
                            <td><span class="rtag"><?= htmlspecialchars($c['crew'] ?? '-') ?></span></td>
                            <td><?= $sl[$bs] ?></td>
                            <td><?= $sl[$ms] ?></td>
                            <td><?= $sl[$ss] ?></td>
                            <td><?= $sl[$ps] ?></td>
                            <td>
                                <?php $isNonaktif = ($c['status_aktif'] ?? 'aktif') == 'nonaktif'; ?>
                                <a href="<?= BASE_URL ?>/crew/detail/<?= $c['id_crew'] ?><?= $fq ?>" class="btn btn-sm btn-outline-info" title="Detail"><i class="fas fa-eye"></i></a>

                                <?php if (!$isNonaktif): ?>
                                    <a href="<?= BASE_URL ?>/crew/edit/<?= $c['id_crew'] ?><?= $fq ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <?php endif; ?>

                                <a href="<?= BASE_URL ?>/crew/toggleStatus/<?= $c['id_crew'] ?>"
                                    class="btn btn-sm <?= $isNonaktif ? 'btn-outline-success' : 'btn-outline-danger' ?>"
                                    title="<?= $isNonaktif ? 'Aktifkan' : 'Nonaktifkan' ?>"
                                    onclick="return confirm('<?= $isNonaktif ? 'Aktifkan' : 'Nonaktifkan' ?> crew ini?')">
                                    <i class="fas <?= $isNonaktif ? 'fa-user-check' : 'fa-user-slash' ?>"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="td-empty">Belum ada crew</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div id="crew-pagination" class="pagination-container">
        <?php if ($totalPages > 1): ?>
            <a href="javascript:void(0)" onclick="loadCrewPage(<?= $page - 1 ?>)" class="btn btn-sm btn-prev <?= $page <= 1 ? 'disabled' : '' ?>">&laquo;</a>
            <span id="crew-pagination-numbers" class="pagination-numbers">
                <?= Helper::renderPaginationNumbers($page ?? 1, $totalPages ?? 1, 'loadCrewPage') ?>
            </span>
            <a href="javascript:void(0)" onclick="loadCrewPage(<?= $page + 1 ?>)" class="btn btn-sm btn-next <?= $page >= $totalPages ? 'disabled' : '' ?>">&raquo;</a>
        <?php endif; ?>
    </div>
</div>

<script>
    function loadCrewPage(page) {
        const rig = document.querySelector('select[name="rig"]')?.value || '';
        const search = document.querySelector('input[name="search"]')?.value || '';
        const urlParams = new URLSearchParams(window.location.search);
        const sort = urlParams.get('sort') || 'nama';
        const order = urlParams.get('order') || 'ASC';
        const status = document.querySelector('select[name="status"]')?.value || '';
        const crew = document.querySelector('select[name="crew"]')?.value || '';
        
        const params = new URLSearchParams();
        if (page > 1) params.set('page', page);
        if (rig) params.set('rig', rig);
        if (search) params.set('search', search);
        if (status) params.set('status', status);
        if (crew) params.set('crew', crew);
        if (sort !== 'nama') params.set('sort', sort);
        if (order !== 'ASC') params.set('order', order);

        const fetchParams = new URLSearchParams(params);
        fetchParams.set('source', 'crew');
        if (!fetchParams.has('page')) fetchParams.set('page', page);

        history.pushState(null, '', window.location.pathname + (params.toString() ? '?' + params.toString() : ''));

        fetch('<?= BASE_URL ?>/ajax/crewTable?' + fetchParams.toString())
            .then(res => res.json())
            .then(data => {
                document.getElementById('crew-tbody').innerHTML = data.html;
                document.getElementById('crew-pagination-numbers').innerHTML = data.pagination;
                document.getElementById('total-crew-count').textContent = data.total;

                const prevBtn = document.querySelector('#crew-pagination .btn-prev');
                if (data.page > 1) {
                    prevBtn.classList.remove('disabled');
                    prevBtn.setAttribute('onclick', 'loadCrewPage(' + (data.page - 1) + ')');
                } else {
                    prevBtn.classList.add('disabled');
                    prevBtn.removeAttribute('onclick');
                }

                const nextBtn = document.querySelector('#crew-pagination .btn-next');
                if (data.page < data.totalPages) {
                    nextBtn.classList.remove('disabled');
                    nextBtn.setAttribute('onclick', 'loadCrewPage(' + (data.page + 1) + ')');
                } else {
                    nextBtn.classList.add('disabled');
                    nextBtn.removeAttribute('onclick');
                }
            });
    }

    function applySort(value) {
        const params = new URLSearchParams(window.location.search);
        const [sort, order] = value.split('-');
        params.set('sort', sort);
        params.set('order', order.toUpperCase());
        window.location.href = '<?= BASE_URL ?>/crew?' + params.toString();
    }
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
