<?php
$title = 'Payroll Belum Dipasangkan';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fas fa-triangle-exclamation"></i> Payroll Belum Dipasangkan</h4>
        <p class="text-muted mb-0">Pilih data crew yang sesuai untuk menyelesaikan baris import yang belum memiliki badge terdaftar.</p>
    </div>
    <a href="<?= BASE_URL ?>/payroll" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card-custom">
    <div class="card-body p-4">
        <?php if (empty($pending)): ?>
            <div class="alert alert-success mb-0">Tidak ada data payroll yang menunggu dipasangkan.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>No.</th>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th>Badge Excel</th>
                            <th style="min-width: 260px">Pasangkan ke Crew</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['periode']) ?></td>
                                <td><?= htmlspecialchars($item['no_urut']) ?></td>
                                <td><?= htmlspecialchars($item['nama']) ?></td>
                                <td><?= htmlspecialchars($item['jabatan']) ?></td>
                                <td><?= htmlspecialchars($item['badge_excel'] ?: '-') ?></td>
                                <td>
                                    <form action="<?= BASE_URL ?>/import/resolvePending" method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="id_pending" value="<?= (int) $item['id_pending'] ?>">
                                        <select name="id_crew" class="form-select form-select-sm" required>
                                            <option value="">Pilih crew</option>
                                            <?php foreach ($daftarCrew as $crew): ?>
                                                <option value="<?= (int) $crew['id_crew'] ?>" <?= ((int) $item['saran_id_crew'] === (int) $crew['id_crew']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($crew['nama']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-sigma text-nowrap">Simpan</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
