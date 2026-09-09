<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Turn Over Karyawan</title>
    <style>
        @page {
            size: A4;
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
            font-size: 11pt;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 3.5mm 15mm 12mm;
            background: #fff;
        }

        .kop {
            display: block;
            width: 100%;
            max-width: 186mm;
            margin: 0 auto;
            height: auto;
        }

        .content {
            width: 100%;
            margin: 8mm auto 0;
            font-family: Calibri, Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.28;
        }

        h2 {
            text-align: center;
            font-size: 14pt;
            margin: 10mm 0 3mm 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .periode-text {
            text-align: center;
            font-size: 10pt;
            color: #444;
            margin-bottom: 8mm;
        }

        h3 {
            font-size: 11pt;
            margin: 6mm 0 3mm 0;
            padding-bottom: 2mm;
            border-bottom: 2px solid #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6mm;
            font-size: 9.5pt;
        }

        th {
            background: #eaeaea;
            padding: 6px 8px;
            border: 1px solid #bbb;
            text-align: left;
            font-weight: bold;
        }

        td {
            padding: 5px 8px;
            border: 1px solid #ccc;
        }

        .td-empty {
            text-align: center;
            color: #999;
            font-style: italic;
            padding: 15px;
        }

        .no-print {
            margin-top: 10mm;
            padding-top: 6mm;
            border-top: 1px solid #ddd;
            text-align: center;
        }

        .btn {
            margin: 0 4mm;
            padding: 9px 22px;
            border: 0;
            border-radius: 5px;
            color: #fff;
            font-size: 12pt;
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
                padding: 3.5mm 15mm 12mm;
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
            <h2>Laporan Turn Over Karyawan</h2>
            <div class="periode-text">
                Periode: 
                <?php 
                if ($periode == 'bulan'):
                    $bulanNama = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                    echo $bulanNama[(int)$bulan - 1] . ' ' . $tahun;
                elseif ($periode == 'semester'):
                    $smt = ($bulan <= 6) ? 1 : 2;
                    echo 'Semester ' . $smt . ' ' . $tahun;
                else:
                    echo 'Tahun ' . $tahun;
                endif;
                ?>
            </div>

            <h3>Crew Keluar (<?= $turnover['total_keluar'] ?>)</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Posisi</th>
                        <th>Rig</th>
                        <th>Crew</th>
                        <th>Tgl Nonaktif</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($turnover['keluar'] && $turnover['keluar']->num_rows > 0): $no = 1; ?>
                        <?php while ($c = $turnover['keluar']->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($c['nama']) ?></strong></td>
                            <td><?= htmlspecialchars($c['posisi']) ?></td>
                            <td><?= htmlspecialchars($c['kode_rig']) ?></td>
                            <td><?= htmlspecialchars($c['crew'] ?? '-') ?></td>
                            <td><?= $c['tanggal_nonaktif'] ? date('d/m/Y', strtotime($c['tanggal_nonaktif'])) : '-' ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="td-empty">Tidak ada crew keluar di periode ini</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h3>Crew Masuk (<?= $turnover['total_masuk'] ?>)</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Posisi</th>
                        <th>Rig</th>
                        <th>Crew</th>
                        <th>Tgl Masuk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($turnover['masuk'] && $turnover['masuk']->num_rows > 0): $no = 1; ?>
                        <?php while ($c = $turnover['masuk']->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($c['nama']) ?></strong></td>
                            <td><?= htmlspecialchars($c['posisi']) ?></td>
                            <td><?= htmlspecialchars($c['kode_rig']) ?></td>
                            <td><?= htmlspecialchars($c['crew'] ?? '-') ?></td>
                            <td><?= $c['tanggal_masuk'] ? date('d/m/Y', strtotime($c['tanggal_masuk'])) : '-' ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="td-empty">Tidak ada crew masuk di periode ini</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h3>Daftar Detail Crew Aktif (<?= $totalCrew ?>)</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Crew</th>
                        <th>Posisi / Jabatan</th>
                        <th>Rig</th>
                        <th>Crew</th>
                        <th>No. Telp</th>
                        <th>NIK KTP</th>
                        <th>Tempat, Tgl Lahir</th>
                        <th>Alamat & Email</th>
                        <th>Tgl Masuk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($activeCrewList) && $activeCrewList->num_rows > 0): $no = 1; ?>
                        <?php while ($ac = $activeCrewList->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($ac['nama']) ?></strong></td>
                            <td><?= htmlspecialchars($ac['posisi'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($ac['kode_rig']) ?></td>
                            <td><?= htmlspecialchars($ac['crew'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($ac['no_telp'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($ac['nik_ktp'] ?? '-') ?></td>
                            <td>
                                <?php 
                                $tmpl = trim($ac['tempat_lahir'] ?? '');
                                $tglL = !empty($ac['tanggal_lahir']) ? date('d/m/Y', strtotime($ac['tanggal_lahir'])) : '';
                                echo ($tmpl && $tglL) ? ($tmpl . ', ' . $tglL) : ($tmpl ?: ($tglL ?: '-'));
                                ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($ac['email'] ?? '') ?>
                                <?= (!empty($ac['email']) && !empty($ac['alamat'])) ? ' | ' : '' ?>
                                <?= htmlspecialchars($ac['alamat'] ?? '') ?>
                                <?= (empty($ac['email']) && empty($ac['alamat'])) ? '-' : '' ?>
                            </td>
                            <td><?= !empty($ac['tanggal_masuk']) ? date('d/m/Y', strtotime($ac['tanggal_masuk'])) : '-' ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="10" class="td-empty">Tidak ada data crew aktif</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>

        <div class="no-print">
            <button onclick="window.print()" class="btn btn-success"><i class="fas fa-print"></i> Cetak</button>
            <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
        </div>
    </div>
</body>
</html>