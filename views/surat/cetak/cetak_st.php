<?php

$nomorSurat = $surat['nomor_surat'] ?? ($nomor_surat ?? '');
$isiSurat = $surat['isi_surat'] ?? ($isi_surat ?? '');
$tanggalMoc = $surat['tanggal_moc'] ?? ($tanggal_moc ?? date('Y-m-d'));
$rigDefault = $surat['kode_rig'] ?? 'GWDC 338';

function st_pick_value($text, $labels, $default = '')
{
    foreach ((array) $labels as $label) {
        if (preg_match('/^' . preg_quote($label, '/') . '\s*:?\s*(.+)$/im', $text, $match)) {
            return trim($match[1]);
        }
    }

    return $default;
}

function st_format_date_id($date)
{
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

    $timestamp = strtotime($date) ?: time();
    return date('j', $timestamp) . ' ' . $bulanIndonesia[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
}

function st_format_date_en($date)
{
    $timestamp = strtotime($date) ?: time();
    return date('F j, Y', $timestamp);
}

$employeeName = st_pick_value($isiSurat, ['Name', 'Nama'], '___________________');
$department = st_pick_value($isiSurat, ['Department', 'Departemen'], $rigDefault);
$crew = st_pick_value($isiSurat, ['Crew', 'Crew/Team', 'Team'], '');
if ($crew !== '' && stripos($department, trim($crew)) === false) {
    $department .= ', ' . strtoupper(trim($crew));
}
$employeeStatus = st_pick_value($isiSurat, ['Employee Status', 'Status Karyawan'], 'Contract');
$position = st_pick_value($isiSurat, ['Position', 'Posisi', 'Jabatan'], '___________________');
$dateOfEntry = st_pick_value($isiSurat, ['Date of entry', 'Tanggal Masuk'], st_format_date_en($tanggalMoc));
$dateOfEvaluation = st_pick_value($isiSurat, ['Date of evaluation', 'Tanggal Evaluasi'], st_format_date_en($tanggalMoc));
$evaluationMonths = st_pick_value($isiSurat, ['Evaluation Months', 'Masa Evaluasi'], '5');
$performanceIssueId = st_pick_value(
    $isiSurat,
    ['Performance Issue ID', 'Alasan Indonesia'],
    'kurangnya perencanaan dalam bekerja dan kinerja yang tidak memenuhi standar, baik dalam konteks tim maupun individu'
);
$performanceIssueEn = st_pick_value(
    $isiSurat,
    ['Performance Issue EN', 'Alasan English'],
    'a lack of work planning and a failure to meet performance standards, both in team-based and individual tasks'
);
$companyName = st_pick_value($isiSurat, ['Company', 'Perusahaan'], 'PT. SIGMA');
$requestByName = st_pick_value($isiSurat, ['Request By', 'Requested By', 'Diajukan Oleh'], 'Bendri Zona');
$requestByTitle = st_pick_value($isiSurat, ['Request By Title', 'Jabatan Pengaju'], 'Chief Mechanic');
$approvedByName = st_pick_value($isiSurat, ['Approved By', 'Disetujui Oleh'], 'Zhang Gang');
$approvedByTitle = st_pick_value($isiSurat, ['Approved By Title', 'Jabatan Penyetuju'], 'Rig Manager');

$departmentSafe = htmlspecialchars($department);
$employeeNameSafe = htmlspecialchars($employeeName);
$positionSafe = htmlspecialchars($position);
$companySafe = htmlspecialchars($companyName);
$issueIdSafe = htmlspecialchars($performanceIssueId);
$issueEnSafe = htmlspecialchars($performanceIssueEn);
$evaluationMonthsDisplay = trim($evaluationMonths) ?: '5';
$evaluationMonthsWord = $evaluationMonthsDisplay === '1' ? 'satu' : ($evaluationMonthsDisplay === '2' ? 'dua' : ($evaluationMonthsDisplay === '3' ? 'tiga' : ($evaluationMonthsDisplay === '4' ? 'empat' : ($evaluationMonthsDisplay === '5' ? 'lima' : ($evaluationMonthsDisplay === '6' ? 'enam' : $evaluationMonthsDisplay)))));
$evaluationMonthsEnglish = $evaluationMonthsDisplay === '1' ? 'one' : ($evaluationMonthsDisplay === '2' ? 'two' : ($evaluationMonthsDisplay === '3' ? 'three' : ($evaluationMonthsDisplay === '4' ? 'four' : ($evaluationMonthsDisplay === '5' ? 'five' : ($evaluationMonthsDisplay === '6' ? 'six' : $evaluationMonthsDisplay)))));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>ST - <?= htmlspecialchars($nomorSurat) ?></title>
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
            color: #1b1b1b;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5pt;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 17mm 18mm 14mm;
            background: #fff;
        }

        .header {
            display: grid;
            grid-template-columns: 35mm 1fr 35mm;
            align-items: start;
            margin-bottom: 10mm;
        }

        .logo {
            width: 31mm;
            height: auto;
            margin-top: 1mm;
        }

        .title {
            padding-top: 10mm;
            text-align: center;
        }

        .title h1 {
            margin: 0 0 1mm;
            font-size: 14pt;
            line-height: 1.1;
            font-weight: 800;
            letter-spacing: 0;
        }

        .title h2 {
            margin: 0;
            font-size: 9.5pt;
            font-style: italic;
            font-weight: 700;
            line-height: 1.1;
        }

        .employee-box {
            border-top: 1.2px solid #222;
            padding-top: 2.2mm;
            margin-bottom: 11mm;
        }

        .employee-title {
            margin: 0 0 1.2mm;
            font-weight: 700;
        }

        .employee-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 47mm;
            gap: 9mm;
        }

        .nomor-surat {
            display: flex;
            gap: 3mm;
            align-items: baseline;
            margin-bottom: 4mm;
            font-size: 10.5pt;
        }

        .nomor-label {
            font-weight: 700;
        }

        .nomor-value {
            font-weight: 600;
            word-break: break-all;
        }

        .employee-table {
            width: 100%;
            border-collapse: collapse;
        }

        .employee-table td {
            padding: 0.45mm 0;
            line-height: 1.16;
            vertical-align: top;
        }

        .employee-table .label {
            width: 30mm;
        }

        .employee-table .colon {
            width: 4mm;
            text-align: center;
        }

        .position-line {
            padding-top: 1.1mm;
            white-space: nowrap;
        }

        .body-copy {
            padding: 0 1mm;
            line-height: 1.48;
            text-align: justify;
        }

        .body-copy p {
            margin: 0 0 4.8mm;
            text-indent: 13mm;
        }

        .body-copy .english {
            font-size: 9.2pt;
            line-height: 1.42;
            font-style: italic;
            text-indent: 0;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28mm;
            margin-top: 17mm;
            padding: 0 17mm;
            text-align: center;
        }

        .signature-label {
            margin-bottom: 6mm;
        }

        .signature-space {
            height: 20mm;
        }

        .signature-name {
            display: inline-block;
            min-width: 27mm;
            border-bottom: 1px solid #111;
            font-weight: 700;
            line-height: 1.1;
        }

        .signature-title {
            margin-top: 1mm;
            font-size: 9.5pt;
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
                padding: 17mm 18mm 14mm;
            }

            .logo {
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
        <header class="header">
            <img class="logo" src="<?= BASE_URL ?>/public/img/logo-gwdc.png" alt="Logo GWDC">
            <div class="title">
                <h1>SURAT PEMULANGAN KARYAWAN</h1>
                <h2>EMPLOYEE RETURN LETTER</h2>
            </div>
            <div></div>
        </header>

        <div class="nomor-surat" style="margin-bottom: 4mm;">
            <span class="nomor-label">Nomor Surat</span>
            <span class="nomor-value">: <?= htmlspecialchars($nomorSurat) ?></span>
        </div>

        <section class="employee-box">
            <div class="employee-title">Employe Data</div>
            <div class="employee-grid">
                <table class="employee-table">
                    <tr>
                        <td class="label">Name</td>
                        <td class="colon">:</td>
                        <td><?= $employeeNameSafe ?></td>
                    </tr>
                    <tr>
                        <td class="label">Department</td>
                        <td class="colon">:</td>
                        <td><?= $departmentSafe ?></td>
                    </tr>
                    <tr>
                        <td class="label">Employee Status</td>
                        <td class="colon">:</td>
                        <td><?= htmlspecialchars($employeeStatus) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Date of entry</td>
                        <td class="colon">:</td>
                        <td><?= htmlspecialchars($dateOfEntry) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Date of evaluation</td>
                        <td class="colon">:</td>
                        <td><?= htmlspecialchars($dateOfEvaluation) ?></td>
                    </tr>
                </table>
                <div class="position-line">Position : <?= $positionSafe ?></div>
            </div>
        </section>

        <main class="body-copy">
            <p>
                Sehubungan dengan hasil evaluasi kinerja Saudara <strong><?= $employeeNameSafe ?></strong> selama
                <?= htmlspecialchars($evaluationMonthsDisplay) ?> (<?= htmlspecialchars($evaluationMonthsWord) ?>) bulan terakhir bertugas di Tim Maintenance
                <strong><?= $departmentSafe ?></strong>, kami menyimpulkan bahwa belum ada peningkatan performa yang
                signifikan. Beberapa faktor yang menjadi pertimbangan utama adalah <?= $issueIdSafe ?>.
            </p>

            <p class="english">
                Following a performance evaluation of Mr. <?= $employeeNameSafe ?> during his <?= htmlspecialchars($evaluationMonthsEnglish) ?>-month assignment
                with the <?= $departmentSafe ?> Maintenance Team, we have observed no significant improvement in his
                performance. Key factors in this evaluation include <?= $issueEnSafe ?>.
            </p>

            <p>
                Berdasarkan hal tersebut, Departemen Rig <?= $departmentSafe ?> memutuskan untuk mengembalikan
                Saudara <?= $employeeNameSafe ?> untuk segera dipulangkan kembali ke <?= $companySafe ?>. Keputusan
                ini diambil demi menjaga dan meningkatkan kelancaran operasional perawatan di Rig <?= $departmentSafe ?>.
            </p>

            <p class="english">
                Therefore, the <?= $departmentSafe ?> Rig Department has decided to return Mr. <?= $employeeNameSafe ?>
                to <?= $companySafe ?>. This decision was made to maintain and improve the smooth operation of
                maintenance activities on Rig <?= $departmentSafe ?>.
            </p>

            <p>
                Demikian surat pemberitahuan ini disampaikan. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.
            </p>

            <p class="english">
                This notice is thus formally issued. Thank you for your attention and cooperation.
            </p>
        </main>

        <section class="signature-grid">
            <div class="signature-block">
                <div class="signature-label">Request by,</div>
                <div class="signature-space"></div>
                <div class="signature-name"><?= htmlspecialchars($requestByName) ?></div>
                <div class="signature-title"><?= htmlspecialchars($requestByTitle) ?></div>
            </div>
            <div class="signature-block">
                <div class="signature-label">Approved by,</div>
                <div class="signature-space"></div>
                <div class="signature-name"><?= htmlspecialchars($approvedByName) ?></div>
                <div class="signature-title"><?= htmlspecialchars($approvedByTitle) ?></div>
            </div>
        </section>

        <div class="action-buttons no-print">
            <button onclick="window.print()" class="btn btn-success">Cetak Surat</button>
            <button onclick="window.location.href='<?= BASE_URL ?>/surat'" class="btn btn-secondary">Tutup</button>
        </div>
    </div>
</body>
</html>
