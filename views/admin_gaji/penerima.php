<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="ag-header-title"><i class="fas fa-users ag-header-icon me-2"></i> Kelola Penerima Slip Gaji</h4>
    <div>
        <button class="btn btn-ag-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-1"></i> Tambah Penerima
        </button>
    </div>
</div>

<?php $flash = Helper::getFlash(); if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] == 'error' ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
        <?= $flash['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- FILTER BAR & SEARCH TOOLS -->
<div class="card-custom mb-4 p-3" style="background: #C0DDDA !important; border: 2px solid #775537 !important;">
    <form method="GET" action="<?= BASE_URL ?>/admin_gaji/penerima" class="row g-2 align-items-center">
        <div class="col-md-5">
            <label class="form-label fw-bold small text-dark mb-1"><i class="fas fa-search me-1"></i> Cari Nama / No. WA / Rig / Crew / Posisi</label>
            <div class="input-group input-group-sm">
                <input type="text" name="q" class="form-control" placeholder="Cari nama, nomor WA, Rig, Crew, atau Posisi..." value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small text-dark mb-1"><i class="fas fa-users me-1"></i> Filter Crew</label>
            <select name="crew" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Crew</option>
                <option value="A" <?= ($crew ?? '') == 'A' ? 'selected' : '' ?>>Crew A</option>
                <option value="B" <?= ($crew ?? '') == 'B' ? 'selected' : '' ?>>Crew B</option>
                <option value="C" <?= ($crew ?? '') == 'C' ? 'selected' : '' ?>>Crew C</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small text-dark mb-1"><i class="fas fa-user-check me-1"></i> Filter Status</label>
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="aktif" <?= ($status ?? '') == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="nonaktif" <?= ($status ?? '') == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </div>
        <div class="col-md-3 text-end pt-3 d-flex gap-2 justify-content-end">
            <button type="submit" class="btn btn-sm btn-ag-primary"><i class="fas fa-search me-1"></i> Cari</button>
            <a href="<?= BASE_URL ?>/admin_gaji/penerima" class="btn btn-sm btn-outline-secondary"><i class="fas fa-sync-alt me-1"></i> Reset</a>
        </div>
    </form>
</div>

<div class="table-card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Karyawan</th>
                        <th>No. WA</th>
                        <th>Rig</th>
                        <th>Crew</th>
                        <th>Posisi</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody id="penerima-tbody">
                    <?php if ($penerima && $penerima->num_rows > 0): $no = ($offset ?? 0) + 1; ?>
                        <?php while ($p = $penerima->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong style="color: #775537;"><?= htmlspecialchars($p['nama']) ?></strong></td>
                            <td><?= htmlspecialchars($p['nomor_wa']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($p['kode_rig'] ?? '-') ?></span></td>
                            <td><span class="badge bg-outline-dark text-dark border"><?= htmlspecialchars($p['crew'] ?? '-') ?></span></td>
                            <td><?= htmlspecialchars($p['posisi'] ?? '-') ?></td>
                            <td>
                                <?= $p['status_aktif'] == 'aktif' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' ?>
                            </td>
                            <td class="text-end">
                                <?php $filterQs = !empty($_GET) ? '?' . http_build_query($_GET) : ''; ?>
                                <button class="btn btn-sm btn-outline-primary me-1" onclick="editPenerima(<?= $p['id_penerima'] ?>, '<?= addslashes(htmlspecialchars($p['nama'])) ?>', '<?= addslashes(htmlspecialchars($p['nomor_wa'])) ?>', '<?= $p['id_rig'] ?? '' ?>', '<?= addslashes(htmlspecialchars($p['crew'] ?? '')) ?>', '<?= addslashes(htmlspecialchars($p['posisi'] ?? '')) ?>')" title="Edit Data"><i class="fas fa-edit"></i></button>
                                <a href="<?= BASE_URL ?>/admin_gaji/togglePenerima/<?= $p['id_penerima'] ?><?= $filterQs ?>" class="btn btn-sm btn-outline-warning me-1" title="Ubah Status Aktif"><i class="fas fa-power-off"></i></a>
                                <a href="<?= BASE_URL ?>/admin_gaji/deletePenerima/<?= $p['id_penerima'] ?><?= $filterQs ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus penerima ini?')" title="Hapus Penerima"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada data penerima</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <!-- Pagination -->
    <div class="pagination-container" id="penerima-pagination">
        <?php if (isset($totalPages) && $totalPages > 1): ?>
            <a href="javascript:void(0)" onclick="loadPenerimaPage(<?= max(1, ($page ?? 1) - 1) ?>)" class="btn btn-sm btn-prev <?= ($page ?? 1) <= 1 ? 'disabled' : '' ?>">&laquo;</a>
            <span class="pagination-numbers">
                <?= Helper::renderPaginationNumbers($page ?? 1, $totalPages ?? 1, 'loadPenerimaPage') ?>
            </span>
            <a href="javascript:void(0)" onclick="loadPenerimaPage(<?= min($totalPages, ($page ?? 1) + 1) ?>)" class="btn btn-sm btn-next <?= ($page ?? 1) >= $totalPages ? 'disabled' : '' ?>">&raquo;</a>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formTambah" action="<?= BASE_URL ?>/admin_gaji/storePenerima" onsubmit="this.action = '<?= BASE_URL ?>/admin_gaji/storePenerima' + (window.location.search || '')">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Tambah Penerima</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap Karyawan</label>
                        <input type="text" name="nama" class="form-control" required placeholder="Contoh: Budi Santoso">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">No. WhatsApp (Format: 628xxx)</label>
                        <input type="text" name="nomor_wa" class="form-control" placeholder="62812xxxxxx" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rig (Opsional)</label>
                        <select name="id_rig" class="form-select">
                            <option value="">-- Pilih Rig --</option>
                            <?php if (!empty($rigList)): ?>
                                <?php foreach ($rigList as $r): ?>
                                    <option value="<?= $r['id_rig'] ?>"><?= htmlspecialchars($r['kode_rig']) ?> - <?= htmlspecialchars($r['nama_rig']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Crew (Opsional)</label>
                            <select name="crew" class="form-select">
                                <option value="">-- Pilih Crew --</option>
                                <option value="A">Crew A</option>
                                <option value="B">Crew B</option>
                                <option value="C">Crew C</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Posisi / Jabatan (Opsional)</label>
                            <select name="posisi" class="form-select">
                                <option value="">-- Pilih Posisi --</option>
                                <?php if (!empty($posisiList)): ?>
                                    <?php foreach ($posisiList as $pos): ?>
                                        <option value="<?= htmlspecialchars($pos['nama_posisi']) ?>"><?= htmlspecialchars($pos['nama_posisi']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-ag-primary"><i class="fas fa-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formEdit" action="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit Penerima</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap Karyawan</label>
                        <input type="text" name="nama" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">No. WhatsApp</label>
                        <input type="text" name="nomor_wa" id="edit_wa" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rig (Opsional)</label>
                        <select name="id_rig" id="edit_id_rig" class="form-select">
                            <option value="">-- Pilih Rig --</option>
                            <?php if (!empty($rigList)): ?>
                                <?php foreach ($rigList as $r): ?>
                                    <option value="<?= $r['id_rig'] ?>"><?= htmlspecialchars($r['kode_rig']) ?> - <?= htmlspecialchars($r['nama_rig']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Crew (Opsional)</label>
                            <select name="crew" id="edit_crew" class="form-select">
                                <option value="">-- Pilih Crew --</option>
                                <option value="A">Crew A</option>
                                <option value="B">Crew B</option>
                                <option value="C">Crew C</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Posisi / Jabatan (Opsional)</label>
                            <select name="posisi" id="edit_posisi" class="form-select">
                                <option value="">-- Pilih Posisi --</option>
                                <?php if (!empty($posisiList)): ?>
                                    <?php foreach ($posisiList as $pos): ?>
                                        <option value="<?= htmlspecialchars($pos['nama_posisi']) ?>"><?= htmlspecialchars($pos['nama_posisi']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-ag-primary"><i class="fas fa-save me-1"></i>Update</button>
                </div>
            </form>
        </div>
</div>
</div>

<script>
function editPenerima(id, nama, wa, id_rig, crew, posisi) {
    const query = window.location.search || '';
    document.getElementById('formEdit').action = '<?= BASE_URL ?>/admin_gaji/updatePenerima/' + id + query;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_wa').value = wa;
    document.getElementById('edit_id_rig').value = id_rig || '';

    let cleanCrew = (crew || '').replace(/^Crew\s+/i, '').trim();
    document.getElementById('edit_crew').value = cleanCrew;

    const selectPos = document.getElementById('edit_posisi');
    if (selectPos) {
        selectPos.value = posisi || '';
        if (posisi && selectPos.selectedIndex <= 0) {
            let matched = false;
            for (let opt of selectPos.options) {
                if (opt.value && opt.value.toLowerCase() === posisi.toLowerCase()) {
                    selectPos.value = opt.value;
                    matched = true;
                    break;
                }
            }
            if (!matched && posisi) {
                const customOpt = new Option(posisi, posisi, true, true);
                selectPos.add(customOpt);
            }
        }
    }

    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}

let penerimaDebounce;

function loadPenerimaPage(page = 1) {
    const q = document.querySelector('input[name="q"]')?.value || '';
    const status = document.querySelector('select[name="status"]')?.value || '';
    const crew = document.querySelector('select[name="crew"]')?.value || '';
    const params = new URLSearchParams({ page, q, status, crew });

    fetch('<?= BASE_URL ?>/admin_gaji/penerimaTable?' + params.toString())
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('penerima-tbody');
            const pag = document.getElementById('penerima-pagination');
            if (tbody) tbody.innerHTML = data.html;
            if (pag) pag.innerHTML = data.pagination;

            const newUrl = window.location.pathname + '?' + params.toString();
            window.history.replaceState({ path: newUrl }, '', newUrl);
        })
        .catch(err => console.error('Error live search penerima:', err));
}

document.addEventListener('DOMContentLoaded', function() {
    const qInput = document.querySelector('input[name="q"]');
    const statusSelect = document.querySelector('select[name="status"]');

    if (qInput) {
        qInput.addEventListener('input', function() {
            clearTimeout(penerimaDebounce);
            penerimaDebounce = setTimeout(() => {
                loadPenerimaPage(1);
            }, 250);
        });
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            loadPenerimaPage(1);
        });
    }

    const crewSelect = document.querySelector('select[name="crew"]');
    if (crewSelect) {
        crewSelect.addEventListener('change', function() {
            loadPenerimaPage(1);
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

