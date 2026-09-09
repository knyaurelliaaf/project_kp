<?php
$nomorSurat = $surat['nomor_surat'] ?? ($nomor_surat ?? '');
$isiSurat = $surat['isi_surat'] ?? ($isi_surat ?? '');
$tanggalMoc = $surat['tanggal_moc'] ?? ($tanggal_moc ?? date('Y-m-d'));
$kopSurat = BASE_URL . '/public/img/kop-surat.png';

function ska_pick_value($text, $labels, $default = '')
{
    foreach ((array) $labels as $label) {
        if (preg_match('/^' . preg_quote($label, '/') . '\s*:?\s*(.+)$/im', $text, $match)) {
            return trim($match[1]);
        }
    }

    return $default;
}

function ska_pick_nth_value($text, $label, $index, $default = '')
{
    if (!preg_match_all('/^' . preg_quote($label, '/') . '\s*:?\s*(.+)$/im', $text, $matches)) {
        return $default;
    }

    return trim($matches[1][$index] ?? $default);
}

function ska_format_date_id($date)
{
    $months = [
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

    $timestamp = strtotime($date) ?: time();
    return date('j', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
}

$letterTitle = ska_pick_value($isiSurat, ['Judul Surat', 'Judul'], 'SURAT KETERANGAN KERJA');
$statementName = ska_pick_value($isiSurat, ['Nama Penandatangan', 'Nama HRD'], ska_pick_nth_value($isiSurat, 'Nama', 0, 'Amy Faradila'));
$statementTitle = ska_pick_value($isiSurat, ['Jabatan Penandatangan', 'Jabatan HRD'], ska_pick_nth_value($isiSurat, 'Jabatan', 0, 'HRD Department'));
$statementAddress = ska_pick_value($isiSurat, ['Alamat Penandatangan', 'Alamat HRD'], ska_pick_nth_value($isiSurat, 'Alamat', 0, 'Jl. Karang Anyer II, Kec Mandau'));

$employeeName = ska_pick_value($isiSurat, ['Nama Karyawan', 'Nama Crew'], ska_pick_nth_value($isiSurat, 'Nama', 1, '___________________'));
$employeeTitle = ska_pick_value($isiSurat, ['Jabatan Karyawan', 'Jabatan Crew', 'Posisi'], ska_pick_nth_value($isiSurat, 'Jabatan', 1, '___________________'));
$employeeAddress = ska_pick_value($isiSurat, ['Alamat Karyawan', 'Alamat Crew'], ska_pick_nth_value($isiSurat, 'Alamat', 1, '___________________'));

$company = ska_pick_value($isiSurat, ['Perusahaan', 'Company'], 'PT Greatwall Drilling Company');
$startDate = ska_pick_value($isiSurat, ['Tanggal Mulai', 'Mulai Bekerja', 'TMT'], '');
$place = ska_pick_value($isiSurat, ['Tempat Surat', 'Tempat'], 'Duri');
$signer = ska_pick_value($isiSurat, ['Signer', 'Penandatangan'], 'Amy Faradila');
$signerTitle = ska_pick_value($isiSurat, ['Signer Title', 'Jabatan Signer'], 'HRD Department');
$letterDate = ska_pick_value($isiSurat, ['Tanggal Surat'], ska_format_date_id($tanggalMoc));

$activeSentence = ska_pick_value($isiSurat, ['Kalimat Aktif', 'Keterangan Aktif'], '');
if ($activeSentence === '') {
    $activeSentence = 'Merupakan salah satu karyawan kami yang masih aktif bekerja, ditempatkan di ' . $company;
    if ($employeeTitle !== '' && $employeeTitle !== '___________________') {
        $activeSentence .= ' sebagai ' . $employeeTitle;
    }
    if ($startDate !== '') {
        $activeSentence .= ' terhitung dari ' . $startDate;
    }
    $activeSentence .= ' hingga saat ini dan menunjukkan kinerja yang baik.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SKA - <?= htmlspecialchars($nomorSurat) ?></title>
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
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 0 15mm 13mm;
            background: #fff;
        }

        .kop {
            display: block;
            width: calc(100% + 30mm);
            max-width: none;
            margin: 0 -15mm;
            height: auto;
        }

        .title {
            margin-top: 4mm;
            text-align: center;
            line-height: 1.15;
        }

        .title h1 {
            margin: 0;
            font-size: 12pt;
            text-decoration: underline;
        }

        .title .number {
            margin-top: 1mm;
            font-size: 10.5pt;
        }

        .content {
            width: 78%;
            margin: 18mm auto 0;
            line-height: 1.35;
        }

        .data-table {
            width: 100%;
            margin: 4mm 0;
            border-collapse: collapse;
        }

        .data-table td {
            padding: 1.8mm 0;
            vertical-align: top;
        }

        .data-table .label {
            width: 37mm;
            padding-left: 4mm;
        }

        .data-table .colon {
            width: 5mm;
            text-align: center;
        }

        .paragraph {
            margin: 6mm 0 0;
            text-align: justify;
        }

        .signature-wrap {
            display: flex;
            justify-content: flex-end;
            margin-top: 18mm;
        }

        .signature {
            width: 56mm;
            text-align: center;
        }

        .signature-date {
            margin-bottom: 5mm;
        }

        .signature-img {
            display: block;
            width: 43mm;
            height: 23mm;
            object-fit: contain;
            margin: 0 auto -1mm;
        }

        .signer {
            font-weight: 700;
            text-decoration: underline;
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
                padding: 0 15mm 13mm;
            }

            .kop,
            .signature-img {
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
        <img class="kop" src="<?= $kopSurat1 ?>" alt="Kop Surat PT ADK Enam Indonesia">

        <section class="title">
            <h1><?= htmlspecialchars($letterTitle) ?></h1>
            <div class="number">Nomor: <?= htmlspecialchars($nomorSurat) ?></div>
        </section>

        <main class="content">
            <p>Yang bertandatangan di bawah ini :</p>

            <table class="data-table">
                <tr>
                    <td class="label">Nama</td>
                    <td class="colon">:</td>
                    <td><?= htmlspecialchars($statementName) ?></td>
                </tr>
                <tr>
                    <td class="label">Jabatan</td>
                    <td class="colon">:</td>
                    <td><?= htmlspecialchars($statementTitle) ?></td>
                </tr>
                <tr>
                    <td class="label">Alamat</td>
                    <td class="colon">:</td>
                    <td><?= htmlspecialchars($statementAddress) ?></td>
                </tr>
            </table>

            <p>Dengan ini menerangkan bahwa</p>

            <table class="data-table">
                <tr>
                    <td class="label">Nama</td>
                    <td class="colon">:</td>
                    <td><?= htmlspecialchars($employeeName) ?></td>
                </tr>
                <tr>
                    <td class="label">Jabatan</td>
                    <td class="colon">:</td>
                    <td><?= htmlspecialchars($employeeTitle) ?></td>
                </tr>
                <tr>
                    <td class="label">Alamat</td>
                    <td class="colon">:</td>
                    <td><?= htmlspecialchars($employeeAddress) ?></td>
                </tr>
            </table>

            <p class="paragraph"><?= htmlspecialchars($activeSentence) ?></p>
            <p class="paragraph">Demikian surat keterangan ini dibuat agar dapat dipergunakan sebagaimana mestinya.</p>

            <section class="signature-wrap">
                <div class="signature">
                    <div class="signature-date"><?= htmlspecialchars($place) ?>, <?= htmlspecialchars($letterDate) ?></div>
                    <img class="signature-img" src="<?= BASE_URL ?>/public/img/ttd-hrd.png" alt="Tanda tangan HRD">
                    <div class="signer"><?= htmlspecialchars($signer) ?></div>
                    <div><?= htmlspecialchars($signerTitle) ?></div>
                </div>
            </section>
        </main>

        <div class="action-buttons no-print">
            <button onclick="window.print()" class="btn btn-success">Cetak Surat</button>
            <button onclick="window.location.href='<?= BASE_URL ?>/surat'" class="btn btn-secondary">Tutup</button>
        </div>
    </div>
</body>
</html>
