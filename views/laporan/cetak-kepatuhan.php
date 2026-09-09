<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        @page { size: A4; margin: 8mm; }
        body { font-family: Arial, sans-serif; color: #000; font-size: 8pt; margin: 0; padding: 0; }
        .kop { text-align: center; border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 8px; }
        .kop h1 { font-size: 12pt; margin: 0 0 2px; }
        .kop p { margin: 1px 0; font-size: 8pt; }
        
        .rig-section { margin-bottom: 10px; page-break-inside: avoid; }
        .rig-title { font-size: 10pt; font-weight: bold; background: #e0e0e0; padding: 3px 5px; margin: 5px 0 3px; }
        
        .crew-tables { display: flex; gap: 5px; margin: 3px 0; }
        .crew-table-wrapper { flex: 1; }
        .crew-table-wrapper h4 { font-size: 8pt; margin: 0 0 2px; padding: 2px; text-align: center; }
        .crew-table-wrapper.aktif h4 { background: #e8f5e9; }
        .crew-table-wrapper.nonaktif h4 { background: #ffebee; }
        
        table { width: 100%; border-collapse: collapse; font-size: 7.5pt; }
        th, td { border: 1px solid #000; padding: 2px; text-align: left; }
        th { background: #f0f0f0; font-weight: bold; }
        
        .no-print { display: none; }
        
        @media print { 
            .no-print { display: none !important; }
            .rig-section { page-break-inside: avoid; }
        }
        
        .footer { font-size: 7pt; text-align: right; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="kop">
        <h1>LAPORAN DATA CREW</h1>
        <p>Sistem Monitoring K3 Crew Compliance | Dicetak: <?= date('d/m/Y H:i') ?> WIB</p>
    </div>

    <?php foreach ($rigList as $rig): 
        $activeCrews = array_filter($crewList, function($c) use ($rig) { 
            return $c['id_rig'] == $rig['id_rig'] && $c['status_aktif'] == 'aktif'; 
        });
        $nonActiveCrews = array_filter($crewList, function($c) use ($rig) { 
            return $c['id_rig'] == $rig['id_rig'] && $c['status_aktif'] == 'nonaktif'; 
        });
        
        if (empty($activeCrews) && empty($nonActiveCrews)) continue;
    ?>
        <div class="rig-section">
            <div class="rig-title">Rig <?= htmlspecialchars($rig['kode_rig']) ?> - <?= htmlspecialchars($rig['nama_rig'] ?? '') ?></div>
            
            <div class="crew-tables">
                <?php if (!empty($activeCrews)): ?>
                    <div class="crew-table-wrapper aktif">
                        <h4>Crew Aktif (<?= count($activeCrews) ?>)</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 8%;">#</th>
                                    <th style="width: 35%;">Nama</th>
                                    <th style="width: 20%;">Crew</th>
                                    <th style="width: 37%;">Jabatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($activeCrews as $crew): 
                                ?>
                                    <tr>
                                        <td style="text-align: center;"><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($crew['nama']) ?></td>
                                        <td><?= htmlspecialchars($crew['crew'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($crew['posisi'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($nonActiveCrews)): ?>
                    <div class="crew-table-wrapper nonaktif">
                        <h4>Non-Aktif (<?= count($nonActiveCrews) ?>)</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 8%;">#</th>
                                    <th style="width: 35%;">Nama</th>
                                    <th style="width: 20%;">Crew</th>
                                    <th style="width: 37%;">Jabatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach ($nonActiveCrews as $crew): 
                                ?>
                                    <tr>
                                        <td style="text-align: center;"><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($crew['nama']) ?></td>
                                        <td><?= htmlspecialchars($crew['crew'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($crew['posisi'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="footer">
        Dicetak oleh sistem pada <?= date('d/m/Y H:i') ?> WIB
    </div>

    <div class="no-print" style="position: fixed; top: 10px; right: 10px; background: white; padding: 10px; border: 1px solid #000;">
        <button onclick="window.print()">Cetak PDF</button>
        <button onclick="window.close()">Tutup</button>
    </div>
    <script>window.addEventListener('load', function () { window.print(); });</script>
</body>
</html>