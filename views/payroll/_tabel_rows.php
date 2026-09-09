<?php if (empty($daftarKaryawan)): ?>
    <tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data yang cocok</td></tr>
<?php else: ?>
    <?php foreach ($daftarKaryawan as $k): ?>
    <tr>
        <td class="text-muted"><?= htmlspecialchars($k['no_urut']) ?></td>
        <td><strong><?= htmlspecialchars($k['nama']) ?></strong></td>
        <td><?= htmlspecialchars($k['jabatan']) ?></td>
        <td class="text-end fw-semibold">Rp <?= number_format($k['gaji_bersih'], 0, ',', '.') ?></td>
        <td class="text-center">
            <a href="<?= BASE_URL ?>/payroll/cetak/<?= htmlspecialchars($k['id_slip']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                <i class="fas fa-print"></i> Cetak
            </a>
        </td>
    </tr>
    <?php endforeach; ?>
<?php endif; ?>