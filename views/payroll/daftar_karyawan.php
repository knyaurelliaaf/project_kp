<?php require_once __DIR__ . '/../layout/header.php'; ?>

<?php if (empty($periode) || (empty($idRig) && empty($daftarKaryawan))): ?>
    <!-- PERIODE PICKER (no parameter) -->
    <div class="phdr mb-3">
        <div>
            <h4 class="mb-1"><i class="fas fa-money-check-alt"></i> Slip Gaji</h4>
            <small class="text-muted">Pilih periode dan rig untuk melihat daftar slip gaji.</small>
        </div>
        <a href="<?= BASE_URL ?>/payroll" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>

    <?php if (empty($daftarPeriode)): ?>
        <div class="empty-state">
            <div class="icon"><i class="fas fa-file-invoice"></i></div>
            <h4>Belum Ada Data Gaji</h4>
            <p>Import data gaji terlebih dahulu untuk melihat slip.</p>
            <a href="<?= BASE_URL ?>/import/formImport" class="btn-sigma" style="text-decoration:none; display:inline-block; padding:12px 28px;">
                <i class="fas fa-file-import"></i> Import Data Gaji
            </a>
        </div>
    <?php else: ?>
        <?php
            // Group periods by Rig
            $rigGroups = [];
            foreach ($daftarPeriode as $p) {
                $rigKey = !empty($p['kode_rig']) ? $p['kode_rig'] : 'Belum Ditentukan';
                $rigGroups[$rigKey][] = $p;
            }
        ?>

        <!-- RIG FILTER TABS -->
        <div class="card-custom mb-4 p-3" style="background: #C0DDDA !important; border: 2px solid #775537 !important;">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="fw-bold me-2" style="color: #775537;"><i class="fas fa-filter me-1"></i> Pilih Rig:</span>
                <button type="button" class="btn btn-sm btn-ag-primary rig-filter-btn active" data-rig="all" onclick="filterByRig('all', this)">
                    <i class="fas fa-th-large me-1"></i> Semua Rig (<?= count($daftarPeriode) ?> Total)
                </button>
                <?php foreach ($rigGroups as $rigName => $periods): ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary rig-filter-btn" data-rig="<?= htmlspecialchars($rigName) ?>" onclick="filterByRig('<?= htmlspecialchars($rigName) ?>', this)">
                        <i class="fas fa-industry me-1"></i> Rig <?= htmlspecialchars($rigName) ?> (<?= count($periods) ?> Bulan)
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- PERIODE LIST GROUPED BY RIG -->
        <?php foreach ($rigGroups as $rigName => $periods): ?>
            <div class="rig-section-group mb-4" data-rig-group="<?= htmlspecialchars($rigName) ?>">
                <div class="d-flex align-items-center mb-2">
                    <h5 class="fw-bold mb-0 me-2" style="color: #775537;">
                        <i class="fas fa-industry me-2"></i>Rig <?= htmlspecialchars($rigName) ?>
                    </h5>
                    <span class="badge bg-warning text-dark fw-bold"><?= count($periods) ?> Periode Bulan</span>
                </div>
                <div class="table-card card-custom mb-3">
                    <div class="p-3">
                        <div class="periode-grid">
                            <?php foreach ($periods as $i => $p): ?>
                                <a href="<?= BASE_URL ?>/payroll/daftarKaryawan?periode=<?= urlencode($p['periode']) ?>&id_rig=<?= $p['id_rig'] ?? '' ?>"
                                   class="periode-card">
                                    <div class="icon-wrap">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="info">
                                        <div class="name"><?= htmlspecialchars($p['periode']) ?></div>
                                        <div class="meta">
                                            Rig <?= htmlspecialchars($rigName) ?>
                                            <span class="mx-1">·</span>
                                            <i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($p['tanggal_mulai'])) ?>
                                        </div>
                                    </div>
                                    <div class="arrow"><i class="fas fa-chevron-right"></i></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <script>
        function filterByRig(rigName, btn) {
            document.querySelectorAll('.rig-filter-btn').forEach(b => {
                b.classList.remove('btn-ag-primary', 'active');
                b.classList.add('btn-outline-secondary');
            });
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-ag-primary', 'active');

            const sections = document.querySelectorAll('.rig-section-group');
            sections.forEach(sec => {
                if (rigName === 'all' || sec.getAttribute('data-rig-group') === rigName) {
                    sec.style.display = 'block';
                } else {
                    sec.style.display = 'none';
                }
            });
        }
        </script>
    <?php endif; ?>

<?php else: ?>
    <!-- SLIP GAJI VIEW (periode selected) -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>
            <i class="fas fa-money-check-alt"></i>
            Slip Gaji <?= htmlspecialchars($periode) ?>
            <?php 
                $displayRig = $kodeRig;
                if (empty($displayRig) && !empty($idRig)) {
                    $rigModelTemp = new RigModel();
                    $rTemp = $rigModelTemp->find($idRig);
                    $displayRig = $rTemp['kode_rig'] ?? null;
                }
            ?>
            <?php if (!empty($displayRig)): ?>
                (Rig <?= htmlspecialchars($displayRig) ?>)
            <?php else: ?>
                <span class="badge bg-warning text-dark">Rig Belum Ditentukan</span>
            <?php endif; ?>
        </h4>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/payroll/konfirmasiHapusPeriode?periode=<?= urlencode($periode) ?>&id_rig=<?= $idRig ?>" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-trash"></i> Hapus Data Ini
            </a>
            <a href="<?= BASE_URL ?>/payroll/daftarKaryawan" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </div>

    <?php if (!empty($hasUnassignedRig)): ?>
        <!-- RIG ASSIGNMENT FORM -->
        <div class="alert alert-info border-0 mb-3">
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Data gaji ini belum memiliki rig yang ditentukan.</strong>
            </div>
            <p class="mb-2">Silakan tentukan rig untuk seluruh data periode ini:</p>
            <form action="<?= BASE_URL ?>/payroll/assignRig" method="POST" class="d-flex gap-2 align-items-center">
                <input type="hidden" name="periode" value="<?= htmlspecialchars($periode) ?>">
                <select name="id_rig" class="form-select" style="max-width: 200px;" required>
                    <option value="">-- Pilih Rig --</option>
                    <?php if (!empty($daftarRig)): ?>
                        <?php foreach ($daftarRig as $rig): ?>
                            <option value="<?= $rig['id_rig'] ?>"><?= htmlspecialchars($rig['kode_rig']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <button type="submit" class="btn btn-sigma btn-sm">
                    <i class="fas fa-tag"></i> Tentukan Rig
                </button>
            </form>
        </div>
    <?php endif; ?>

    <div class="filter-bar mb-3">
        <label class="form-label">Cari Karyawan</label>
        <input type="text" id="search-karyawan" class="form-control" placeholder="Cari berdasarkan no urut, nama, atau jabatan...">
    </div>

    <div class="table-card">
        <div class="tw">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Gaji Bersih</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tabel-body">
                    <?php require __DIR__ . '/_tabel_rows.php'; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($periode) && (!empty($idRig) || !empty($daftarKaryawan))): ?>
<script>
(function() {
    const periode = <?= json_encode($periode) ?>;
    const idRig = <?= json_encode($idRig) ?>;
    const baseUrl = <?= json_encode(BASE_URL) ?>;
    const input = document.getElementById('search-karyawan');
    const tbody = document.getElementById('tabel-body');
    let timer = null;

    input.addEventListener('keyup', function() {
        clearTimeout(timer);
        const keyword = input.value;

        timer = setTimeout(function() {
            fetch(baseUrl + '/payroll/cariKaryawanAjax?periode=' + encodeURIComponent(periode) + '&id_rig=' + encodeURIComponent(idRig) + '&search=' + encodeURIComponent(keyword))
                .then(function(res) { return res.text(); })
                .then(function(html) { tbody.innerHTML = html; })
                .catch(function() {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Gagal memuat data</td></tr>';
                });
        }, 350);
    });
})();
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
