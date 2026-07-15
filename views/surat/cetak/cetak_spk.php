<?php
/**
 * Template cetak SPK.
 *
 * Controller mengirim array $surat. Template ini memakai gambar kop utuh
 * public/img/kop-surat.png agar hasilnya sama dengan referensi.
 */
$nomorSurat = $surat['nomor_surat'] ?? ($nomor_surat ?? '');
$tujuanSurat = $surat['tujuan'] ?? ($tujuan ?? 'DSR Rig GWDC 336');
$perihalSurat = $surat['perihal'] ?? ($perihal ?? 'Surat Pengantar Crew Pengganti');
$isiSurat = $surat['isi_surat'] ?? ($isi_surat ?? '-');
$tanggalMoc = $surat['tanggal_moc'] ?? ($tanggal_moc ?? date('Y-m-d'));

$introText = 'Bersama ini kami kirimkan pengganti Floorman dengan data:';
$crewNama = '___________________';
$crewJabatan = '___________________';
$crewBadge = '___________________';
$permanenText = '';
$closingText = 'Demikian yang dapat disampaikan, atas perhatian dan kerjasama kami ucapkan Terima kasih';

if (preg_match('/Bersama ini.*?dengan data:/is', $isiSurat, $match)) {
    $introText = trim(preg_replace('/\s+/', ' ', $match[0]));
}
if (preg_match('/Nama\s*:?\s*([^\r\n]+)/i', $isiSurat, $match)) {
    $crewNama = trim($match[1]);
}
if (preg_match('/Jabatan\s*:?\s*([^\r\n]+)/i', $isiSurat, $match)) {
    $crewJabatan = trim($match[1]);
}
if (preg_match('/No\s*Badge\s*:?\s*([^\r\n]+)/i', $isiSurat, $match)) {
    $crewBadge = trim($match[1]);
}
if (preg_match('/Dengan ini.*?(?=Demikian|$)/is', $isiSurat, $match)) {
    $permanenText = trim(preg_replace('/\s+/', ' ', $match[0]));
}

$bulanIndonesia = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember',
];
$timestamp = strtotime($tanggalMoc) ?: time();
$tanggalCetak = date('j', $timestamp) . ' ' . $bulanIndonesia[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);

$permanenText = preg_replace('/posisi\s*/i', 'posisi ', $permanenText);
$permanenText = preg_replace('/\s+/', ' ', trim($permanenText));
$permanenHtml = htmlspecialchars($permanenText);
$crewJabatanClean = trim($crewJabatan);
$crewNamaClean = trim($crewNama);

if ($crewJabatanClean !== '' && $crewJabatanClean !== '___________________') {
    $jabatanAwal = strtok($crewJabatanClean, ' ');

    if ($jabatanAwal && stripos($permanenText, $crewJabatanClean) === false) {
        $permanenText = preg_replace(
            '/posisi\s+' . preg_quote($jabatanAwal, '/') . '\b/i',
            'posisi ' . $crewJabatanClean,
            $permanenText,
            1
        );
        $permanenHtml = htmlspecialchars($permanenText);
    }

    $permanenHtml = preg_replace(
        '/' . preg_quote(htmlspecialchars($crewJabatanClean), '/') . '/',
        '<strong>' . htmlspecialchars($crewJabatanClean) . '</strong>',
        $permanenHtml,
        1
    );
}

if ($crewNamaClean !== '' && $crewNamaClean !== '___________________') {
    $permanenHtml = preg_replace(
        '/' . preg_quote(htmlspecialchars($crewNamaClean), '/') . '/',
        '<strong>' . htmlspecialchars($crewNamaClean) . '</strong>',
        $permanenHtml,
        1
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK - <?= htmlspecialchars($nomorSurat) ?></title>
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
            font-size: 12pt;
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
            width: 78%;
            margin: 8mm auto 0;
            font-family: Calibri, Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.28;
        }

        .nomor-surat {
            display: flex;
            gap: 3mm;
            align-items: baseline;
            margin-bottom: 5mm;
            text-align: left;
            font-size: 11pt;
        }

        .nomor-label {
            font-weight: 700;
        }

        .nomor-value {
            font-weight: 600;
            word-break: break-all;
        }

        .alamat-tujuan {
            margin-bottom: 8.7mm;
            font-family: Cambria, "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.45;
        }

        .alamat-tujuan div {
            margin-bottom: 3.3mm;
        }

        .alamat-tujuan strong {
            font-weight: normal;
        }

        .alamat-tujuan .tujuan {
            font-weight: bold;
        }

        .perihal {
            margin-bottom: 5.6mm;
        }

        .pembuka {
            margin-bottom: 14.5mm;
        }

        .isi-surat {
            text-align: justify;
        }

        .paragraf-isi {
            margin-bottom: 5.1mm;
            white-space: normal;
        }

        .intro {
            margin-bottom: 3.2mm;
        }

        .crew-data {
            width: 68%;
            margin: 0 auto 5mm auto;
            border-collapse: collapse;
        }

        .crew-data td {
            padding: 0 0 4.2mm 0;
            vertical-align: top;
            white-space: normal;
        }

        .crew-data .no {
            width: 8mm;
            text-align: right;
            padding-right: 4mm;
            font-weight: 700;
        }

        .crew-data .label {
            width: 20mm;
            font-weight: 700;
            white-space: nowrap;
        }

        .crew-data .colon {
            width: 5mm;
            text-align: center;
        }

        .crew-data .value {
            font-weight: 500;
        }

        .permanen {
            width: 95%;
            margin: 0 auto 8.7mm auto;
            text-align: justify;
            text-indent: 7mm;
            line-height: 1.35;
        }

        .closing {
            margin-bottom: 10mm;
        }

        .ttd-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 0;
        }

        .ttd-right {
            width: 53mm;
            text-align: center;
        }

        .tempat-tanggal {
            margin-bottom: 1.5mm;
        }

        .ttd-img {
            display: block;
            width: 45mm;
            margin: 0 auto -1mm;
        }

        .nama-ttd {
            font-weight: bold;
        }

        .jabatan {
            margin-top: 0.8mm;
            font-family: Cambria, "Times New Roman", Times, serif;
            font-size: 12pt;
        }

        .action-buttons {
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

        <main class="content">
            <div class="nomor-surat">
                <span class="nomor-label">Nomor Surat</span>
                <span class="nomor-value">: <?= htmlspecialchars($nomorSurat) ?></span>
            </div>

            <section class="alamat-tujuan">
                <div><strong>Kepada Yth.</strong></div>
                <div class="tujuan"><?= htmlspecialchars($tujuanSurat) ?></div>
                <div>Di tempat</div>
            </section>

            <div class="perihal">
                Perihal: <?= htmlspecialchars($perihalSurat) ?>
            </div>

            <div class="pembuka">Dengan hormat</div>

            <div class="isi-surat">
                <div class="paragraf-isi intro">
                    <?= htmlspecialchars($introText) ?>
                </div>

                <table class="crew-data">
                    <tr>
                        <td class="no">1.</td>
                        <td class="label">Nama</td>
                        <td class="colon">:</td>
                        <td class="value"><?= htmlspecialchars($crewNama) ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="label">Jabatan</td>
                        <td class="colon">:</td>
                        <td class="value"><?= htmlspecialchars($crewJabatan) ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="label">No badge</td>
                        <td class="colon">:</td>
                        <td class="value"><?= htmlspecialchars($crewBadge) ?></td>
                    </tr>
                </table>

                <?php if ($permanenText !== ''): ?>
                <div class="paragraf-isi permanen">
                    <?= $permanenHtml ?>
                </div>
                <?php endif; ?>

                <div class="paragraf-isi closing">
                    <?= htmlspecialchars($closingText) ?>
                </div>

                <div class="ttd-container">
                    <div class="ttd-right">
                        <div class="tempat-tanggal">
                            Duri,&nbsp; <?= htmlspecialchars($tanggalCetak) ?>
                        </div>
                        <img class="ttd-img" src="<?= BASE_URL ?>/public/img/ttd-hrd.png" alt="Tanda tangan HRD">
                        <div class="nama-ttd">Amy Faradila</div>
                        <div class="jabatan">HRD Department</div>
                    </div>
                </div>
            </div>
        </main>

        <div class="action-buttons no-print">
            <button onclick="window.print()" class="btn btn-success">Cetak Surat</button>
            <button onclick="window.location.href='<?= BASE_URL ?>/surat'" class="btn btn-secondary">Tutup</button>
        </div>
    </div>
</body>
</html>
