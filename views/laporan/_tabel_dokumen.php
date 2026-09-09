<table>
    <thead><tr><th>#</th><th>Nama</th><th>Posisi</th><th>Rig</th><th>Crew</th><th>Tanggal Berakhir</th><th>Keterangan</th></tr></thead>
    <tbody>
        <?php if ($itemsToPrint): foreach ($itemsToPrint as $no => $item): ?>
            <tr><td><?= $no + 1 ?></td><td><?= htmlspecialchars($item['nama']) ?></td><td><?= htmlspecialchars($item['posisi']) ?></td><td><?= htmlspecialchars($item['kode_rig']) ?></td><td><?= htmlspecialchars($item['crew'] ?: '-') ?></td><td><?= date('d/m/Y', strtotime($item['tanggal_expired'])) ?></td><td><?= $filter_type === 'expired' ? 'Expired ' . abs($item['sisa_hari']) . ' hari' : $item['sisa_hari'] . ' hari lagi' ?></td></tr>
        <?php endforeach; else: ?>
            <tr><td colspan="7" class="empty">Tidak ada data</td></tr>
        <?php endif; ?>
    </tbody>
</table>
