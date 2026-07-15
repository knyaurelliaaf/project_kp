<?php
$nomorSurat = $surat['nomor_surat'] ?? ($nomor_surat ?? '');
$isiSurat = $surat['isi_surat'] ?? ($isi_surat ?? '');
$tanggalMoc = $surat['tanggal_moc'] ?? ($tanggal_moc ?? date('Y-m-d'));

function sp_pick_value($text, $labels, $default = '')
{
    foreach ((array) $labels as $label) {
        if (preg_match('/^' . preg_quote($label, '/') . '\s*:?\s*(.+)$/im', $text, $match)) {
            return trim($match[1]);
        }
    }

    return $default;
}

function sp_timestamp_from_id($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return false;
    }

    $timestamp = strtotime($value);
    if ($timestamp) {
        return $timestamp;
    }

    $months = [
        'januari' => '01',
        'februari' => '02',
        'maret' => '03',
        'april' => '04',
        'mei' => '05',
        'juni' => '06',
        'juli' => '07',
        'agustus' => '08',
        'september' => '09',
        'oktober' => '10',
        'november' => '11',
        'desember' => '12',
    ];

    if (preg_match('/(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/i', $value, $match)) {
        $month = $months[strtolower($match[2])] ?? '';
        if ($month !== '') {
            return strtotime($match[3] . '-' . $month . '-' . str_pad($match[1], 2, '0', STR_PAD_LEFT));
        }
    }

    return false;
}

function sp_format_date_id($date, $withDay = false)
{
    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
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

    $timestamp = sp_timestamp_from_id($date) ?: time();
    $text = date('j', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    return $withDay ? $days[(int) date('w', $timestamp)] . ', Tanggal ' . $text : $text;
}

$level = sp_pick_value($isiSurat, ['Tingkat Peringatan'], 'Pertama');
$letterTitle = sp_pick_value($isiSurat, ['Judul Surat', 'Judul'], 'SURAT PERINGATAN ' . strtoupper($level));
$employeeName = sp_pick_value($isiSurat, ['Nama Karyawan', 'Nama Crew', 'Nama'], '___________________');
$employeeTitle = sp_pick_value($isiSurat, ['Jabatan Karyawan', 'Jabatan Crew', 'Jabatan', 'Posisi'], '___________________');
$badge = sp_pick_value($isiSurat, ['No Badge', 'Nomor Badge'], '-');
$rig = sp_pick_value($isiSurat, ['Rig'], '');
$crew = sp_pick_value($isiSurat, ['Crew'], '');
$violationTitle = sp_pick_value($isiSurat, ['Judul Pelanggaran'], 'TENTANG KEDISIPLINAN,MELANGGAR ATURAN PERUSAHAAN,ETIKA KINERJA YANG SIGNIFIKAN');
$violationDetail = sp_pick_value($isiSurat, ['Detail Pelanggaran', 'Pelanggaran'], 'Sehubungan dengan terjadinya pelanggaran yang dilakukan karyawan berupa temuan finding cctv dilokasi.');
$disciplineAction = sp_pick_value($isiSurat, ['Tindakan Disiplin'], 'Maka dari itu perusahaan melakukan tindakan disiplin Coaching Diyard selama 3 hari dan menonaktifkan absensi kehadiran/Alfa serta memotong gaji karyawan yang melanggar aturan.');
$effectiveStart = sp_pick_value($isiSurat, ['Masa Berlaku Mulai'], sp_format_date_id($tanggalMoc));
$effectiveEnd = sp_pick_value($isiSurat, ['Masa Berlaku Sampai'], '');
$letterDateRaw = sp_pick_value($isiSurat, ['Tanggal Surat'], $tanggalMoc);
$letterDate = sp_format_date_id($letterDateRaw);
$letterDateWithDay = sp_format_date_id($letterDateRaw ?: $tanggalMoc, true);
$signer = strtoupper(sp_pick_value($isiSurat, ['Signer', 'Penandatangan'], 'Amy Faradila'));
$signerTitle = sp_pick_value($isiSurat, ['Signer Title', 'Jabatan Penandatangan'], 'HRD Dept');
$coordinatorName = sp_pick_value($isiSurat, ['Coordinator', 'Crew Coordinator'], 'EKO HENDRA');
$coordinatorTitle = sp_pick_value($isiSurat, ['Coordinator Title'], 'Crew Coordinator');
$managementCompany = sp_pick_value($isiSurat, ['Perusahaan Manajemen'], 'PT. ADK Enam Indonesia Proj PT Greatwall Drilling Company');
$placement = trim(($employeeTitle ?: '') . ($rig ? ' ' . $rig : '') . ($crew ? '-' . $crew : ''));
$effectiveText = $effectiveStart;
if ($effectiveEnd !== '') {
    $effectiveText .= ' s/d ' . $effectiveEnd;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SP - <?= htmlspecialchars($nomorSurat) ?></title>
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
            background: #e9e9e9;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.2pt;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 3mm 12mm 8mm;
            background: #fff;
        }

        .kop {
            display: block;
            width: calc(100% + 24mm);
            max-width: none;
            margin: -3mm -12mm 3mm;
            height: auto;
        }

        .title {
            text-align: center;
            line-height: 1.1;
            margin-top: -2mm;
        }

        .title h1 {
            display: inline-block;
            margin: 0;
            border-bottom: 1.6px solid #000;
            font-size: 12pt;
            font-weight: 700;
            text-transform: uppercase;
        }

        .number {
            margin-top: .8mm;
            font-size: 10.5pt;
        }

        .content {
            width: 91%;
            margin: 7mm auto 0;
            line-height: 1.16;
        }

        .opening {
            margin: 0 0 5mm;
            text-align: justify;
        }

        .opening strong {
            font-weight: 700;
        }

        .data-table {
            width: 78%;
            margin: 0 0 5mm;
            border-collapse: collapse;
        }

        .data-table td {
            padding: .35mm 0;
            vertical-align: top;
        }

        .data-table .label {
            width: 37mm;
            letter-spacing: 2.2px;
        }

        .data-table .label-normal {
            letter-spacing: 0;
        }

        .data-table .colon {
            width: 8mm;
            text-align: center;
        }

        .data-table .value {
            font-weight: 700;
        }

        .section-title {
            margin: 4mm 0 3mm;
            font-weight: 700;
            text-transform: uppercase;
        }

        .paragraph {
            margin: 0 0 3.2mm 7mm;
            text-align: justify;
        }

        .closing {
            margin: 5mm 0 0;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1.25fr;
            gap: 14mm;
            align-items: end;
            margin-top: 9mm;
        }

        .signature-block {
            min-height: 28mm;
            text-align: center;
            font-size: 9.5pt;
        }

        .signature-label {
            margin-bottom: 2mm;
            text-align: left;
        }

        .signature-block.employee .signature-label {
            text-align: center;
        }

        .signature-space {
            height: 14mm;
        }

        .signature-img {
            display: block;
            width: 33mm;
            height: 18mm;
            object-fit: contain;
            margin: -1mm auto -2mm;
        }

        .signature-name {
            font-weight: 700;
        }

        .signature-title {
            margin-top: .5mm;
        }

        .employee .signature-name {
            font-weight: 400;
        }

        .cc {
            margin-top: 6mm;
            font-size: 9.5pt;
            line-height: 1.25;
        }

        .cc-title {
            display: inline-block;
            border-bottom: 1px solid #000;
        }

        .action-buttons {
            margin-top: 8mm;
            padding-top: 5mm;
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
                padding: 3mm 12mm 8mm;
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
        <img class="kop" src="<?= BASE_URL ?>/public/img/kop-surat.png" alt="Kop Surat PT ADK Enam Indonesia">

        <section class="title">
            <h1><?= htmlspecialchars($letterTitle) ?></h1>
            <div class="number">No : <?= htmlspecialchars($nomorSurat) ?></div>
        </section>

        <main class="content">
            <p class="opening">
                Pada hari ini, <?= htmlspecialchars($letterDateWithDay) ?>
                Manajemen <strong><?= htmlspecialchars($managementCompany) ?></strong> memberikan
                <strong><u><?= htmlspecialchars(ucwords(strtolower($letterTitle))) ?></u></strong>
                kepada :
            </p>

            <table class="data-table">
                <tr>
                    <td class="label">Nama</td>
                    <td class="colon">:</td>
                    <td class="value"><?= htmlspecialchars($employeeName) ?></td>
                </tr>
                <tr>
                    <td class="label-normal">No Badge</td>
                    <td class="colon">:</td>
                    <td><?= htmlspecialchars($badge) ?></td>
                </tr>
                <tr>
                    <td class="label-normal">Jabatan</td>
                    <td class="colon">:</td>
                    <td><?= htmlspecialchars($placement ?: $employeeTitle) ?></td>
                </tr>
                <tr>
                    <td class="label-normal">Peringatan Tingkat <?= htmlspecialchars($level) ?> ini diterbitkan dikarenakan, :</td>
                    <td class="colon"></td>
                    <td></td>
                </tr>
            </table>

            <div class="section-title">1.&nbsp; <?= htmlspecialchars($violationTitle) ?></div>

            <p class="paragraph"><?= htmlspecialchars($violationDetail) ?></p>
            <p class="paragraph">
                Pasal 1.8 Karyawan wajib mematuhi seluruh peraturan perusahaan dan menerima konsekuensi atas pelanggaran yang dilakukan sesuai ketentuan yang berlaku dan peraturan ketenagakerjaan. Pasal 1.6 Karyawan menyetujui untuk dilakukan review dan penilaian kinerja oleh Perusahaan dan Pemberi Kerja (PHR) selama masa kontrak berjalan.
            </p>
            <p class="paragraph"><?= htmlspecialchars($disciplineAction) ?></p>
            <p class="paragraph">
                Surat Peringatan <?= htmlspecialchars($level) ?> ini berlaku selama 1 ( Satu ) bulan,
                terhitung mulai tanggal <?= htmlspecialchars($effectiveText ?: $letterDate) ?>.
            </p>
            <p class="paragraph">
                Apabila Surat Peringatan ini sudah jatuh tempo, Saudara dapat memperbaiki kesalahan yang telah di perbuat atau tidak melakukan kesalahan lain yang sejenis, maka dengan sendirinya Surat peringatan ini akan gugur, namun apabila saudara tidak dapat memperbaikinya atau melakukan kesalahan lain yang sejenis, maka surat peringatan ini dapat mengakibatkan <strong>Pemutusan Hubungan kerja.</strong>
            </p>

            <p class="closing">Demikian Surat peringatan ini dibuat untuk menjadi perhatian</p>

            <section class="signature-grid">
                <div class="signature-block">
                    <div class="signature-label">Di Keluarkan oleh</div>
                    <img class="signature-img" src="<?= BASE_URL ?>/public/img/ttd-hrd.png" alt="Tanda tangan HRD">
                    <div class="signature-name"><?= htmlspecialchars($signer) ?></div>
                    <div class="signature-title"><?= htmlspecialchars($signerTitle) ?></div>
                </div>

                <div class="signature-block">
                    <div class="signature-label">&nbsp;</div>
                    <div class="signature-space"></div>
                    <div class="signature-name"><?= htmlspecialchars($coordinatorName) ?></div>
                    <div class="signature-title"><?= htmlspecialchars($coordinatorTitle) ?></div>
                </div>

                <div class="signature-block employee">
                    <div class="signature-label">Karyawan Yang di Peringat kan</div>
                    <div class="signature-space"></div>
                    <div class="signature-name"><?= htmlspecialchars($employeeName) ?></div>
                </div>
            </section>

            <div class="cc">
                <div class="cc-title">Tembusan :</div>
                <div>-&nbsp;&nbsp; Direktur</div>
                <div>-&nbsp;&nbsp; HRD Jakarta</div>
            </div>
        </main>

        <div class="action-buttons no-print">
            <button onclick="window.print()" class="btn btn-success">Cetak Surat</button>
            <button onclick="window.location.href='<?= BASE_URL ?>/surat'" class="btn btn-secondary">Tutup</button>
        </div>
    </div>
</body>
</html>
