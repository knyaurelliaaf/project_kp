<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: Georgia, 'Times New Roman', serif; color: #000; font-size: 11pt; margin: 0; }
        .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; }
        .kop h1 { font-size: 16pt; margin: 0 0 4px; }.kop p { margin: 0; }
        h2 { text-align: center; font-size: 14pt; margin: 20px 0 6px; text-transform: uppercase; }
        .meta { text-align: center; font-size: 10pt; margin-bottom: 18px; }
        h3 { font-size: 12pt; margin: 20px 0 7px; border-bottom: 1px solid #000; padding-bottom: 4px; }
        h4 { font-size: 10.5pt; margin: 12px 0 5px; }
        table { width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 12px; }
        th, td { border: 1px solid #000; padding: 5px; vertical-align: top; } th { background: #e8e8e8; text-align: left; }
        .empty { text-align: center; font-style: italic; }.no-print { text-align: center; margin-top: 20px; }
        button { padding: 8px 16px; cursor: pointer; }
        @media print { .no-print { display: none !important; } h3 { break-after: avoid; } table { break-inside: avoid; } }
    </style>
</head>
<body>
    <h2>Laporan Dokumen <?= $filter_type === 'expired' ? 'Expired' : 'Akan Expired' ?></h2>
    <div class="meta">Dicetak: <?= date('d/m/Y H:i') ?> WIB<br>Filter Rig: <?= htmlspecialchars($filter_rig_label) ?> &nbsp;|&nbsp; Dokumen: <?= htmlspecialchars($filter_dokumen_label) ?></div>

    <?php
    $dokumenMapping = [
        'badge'       => 'Badge',
        'mcu'         => 'MCU',
        'sertifikat'  => 'Sertifikat',
        'pkwt'        => 'PKWT'
    ];
    if ($filter_dokumen !== ''):
        // Hanya tampilkan jenis dokumen yang dipilih
        $jenis = $dokumenMapping[$filter_dokumen] ?? $filter_dokumen;
        $items = $grouped[$jenis];
    ?>
        <h3><?= $jenis ?> (<?= count($items) ?>)</h3>
        <?php if ($items && $filter_rig === ''): $perRig = []; foreach ($items as $item) { $perRig[$item['kode_rig']][] = $item; } ?>
            <?php foreach ($perRig as $kodeRig => $rigItems): ?>
                <h4>Rig <?= htmlspecialchars($kodeRig) ?></h4>
                <?php $itemsToPrint = $rigItems; require __DIR__ . '/_tabel_dokumen.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <?php $itemsToPrint = $items; require __DIR__ . '/_tabel_dokumen.php'; ?>
        <?php endif; ?>
    <?php else: ?>
        <?php foreach (['Badge', 'MCU', 'Sertifikat', 'PKWT'] as $jenis): $items = $grouped[$jenis]; ?>
            <h3><?= $jenis ?> (<?= count($items) ?>)</h3>
            <?php if ($items && $filter_rig === ''): $perRig = []; foreach ($items as $item) { $perRig[$item['kode_rig']][] = $item; } ?>
                <?php foreach ($perRig as $kodeRig => $rigItems): ?>
                    <h4>Rig <?= htmlspecialchars($kodeRig) ?></h4>
                    <?php $itemsToPrint = $rigItems; require __DIR__ . '/_tabel_dokumen.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <?php $itemsToPrint = $items; require __DIR__ . '/_tabel_dokumen.php'; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <div class="no-print"><button onclick="window.print()">Cetak</button> <button onclick="window.close()">Tutup</button></div>
    <script>window.addEventListener('load', function () { window.print(); });</script>
</body>
</html>
