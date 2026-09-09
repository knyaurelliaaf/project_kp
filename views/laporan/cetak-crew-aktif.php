<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Data Crew Aktif</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f0f0f0;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
        }

        .page {
            width: 297mm;
            min-height: 210mm;
            margin: 0 auto;
            padding: 5mm 12mm 10mm;
            background: #fff;
        }

        .kop {
            display: block;
            width: 100%;
            max-width: 270mm;
            margin: 0 auto;
            height: auto;
        }

        .content {
            width: 100%;
            margin: 6mm auto 0;
            font-family: Calibri, Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.25;
        }

        h2 {
            text-align: center;
            font-size: 14pt;
            margin: 4mm 0 2mm 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .meta-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9.5pt;
            color: #333;
            margin-bottom: 5mm;
            padding: 3mm 0;
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6mm;
            font-size: 9pt;
        }

        th {
            background: #e5e7eb;
            padding: 6px 7px;
            border: 1px solid #999;
            text-align: left;
            font-weight: bold;
            color: #111;
        }

        td {
            padding: 5px 7px;
            border: 1px solid #ccc;
            vertical-align: top;
        }

        .td-empty {
            text-align: center;
            color: #888;
            font-style: italic;
            padding: 15px;
        }

        .no-print {
            margin-top: 6mm;
            padding-top: 4mm;
            border-top: 1px solid #ddd;
            text-align: center;
        }

        .btn {
            margin: 0 4mm;
            padding: 8px 20px;
            border: 0;
            border-radius: 5px;
            color: #fff;
            font-size: 11pt;
            cursor: pointer;
        }

        .btn-success {
            background: #0f9b58;
        }

        .btn-secondary {
            background: #6c757d;
        }

        @media print {
            body {
                background: #fff;
            }

            .page {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 5mm 10mm 10mm;
            }

            .kop {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <img class="kop" src="<?= BASE_URL ?>/public/img/kop-surat.png" alt="Kop Surat PT ADK Enam Indonesia">

        <div class="content">
            <h2>LAPORAN DATA CREW AKTIF</h2>
            <div class="meta-info">
                <div><strong>Rig Filter:</strong> <?= !empty($filter_rig) ? htmlspecialchars($filter_rig) : 'Semua Rig' ?></div>
                <div><strong>Total Crew Aktif:</strong> <?= $totalCrew ?> Orang</div>
                <div><strong>Tanggal Cetak:</strong> <?= date('d/m/Y H:i') ?></div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width:30px;">#</th>
                        <th>Nama Crew</th>
                        <th>No. Badge</th>
                        <th>NIK KTP</th>
                        <th>Posisi / Jabatan</th>
                        <th style="width:60px;">Rig</th>
                        <th style="width:50px;">Crew</th>
                        <th>No. Telp / WA</th>
                        <th>Tempat, Tgl Lahir</th>
                        <th>Email & Alamat</th>
                        <th>Tgl Masuk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($activeCrewList) && $activeCrewList->num_rows > 0): $no = 1; ?>
                        <?php while ($ac = $activeCrewList->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <strong><?= htmlspecialchars($ac['nama']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($ac['nomor_badge'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($ac['nik_ktp'] ?? '-') ?></td>
                            <td><strong><?= htmlspecialchars($ac['posisi'] ?? '-') ?></strong></td>
                            <td><?= htmlspecialchars($ac['kode_rig']) ?></td>
                            <td><?= htmlspecialchars($ac['crew'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($ac['no_telp'] ?? '-') ?></td>
                            <td>
                                <?php 
                                $tmpl = trim($ac['tempat_lahir'] ?? '');
                                $tglL = !empty($ac['tanggal_lahir']) ? date('d/m/Y', strtotime($ac['tanggal_lahir'])) : '';
                                echo ($tmpl && $tglL) ? ($tmpl . ', ' . $tglL) : ($tmpl ?: ($tglL ?: '-'));
                                ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($ac['email'] ?? '') ?>
                                <?= (!empty($ac['email']) && !empty($ac['alamat'])) ? '<br>' : '' ?>
                                <?= htmlspecialchars($ac['alamat'] ?? '') ?>
                                <?= (empty($ac['email']) && empty($ac['alamat'])) ? '-' : '' ?>
                            </td>
                            <td class="td-sm">
                                <?= !empty($ac['tanggal_masuk']) ? date('d/m/Y', strtotime($ac['tanggal_masuk'])) : '-' ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="11" class="td-empty">Tidak ada data crew aktif</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="no-print">
            <button onclick="window.print()" class="btn btn-success"><i class="fas fa-print"></i> Cetak PDF / Print</button>
            <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
        </div>
    </div>
</body>
</html>
