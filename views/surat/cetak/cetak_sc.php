<?php
$nomorSurat = $surat['nomor_surat'] ?? ($nomor_surat ?? '');
$isiSurat = $surat['isi_surat'] ?? ($isi_surat ?? '');
$tanggalMoc = $surat['tanggal_moc'] ?? ($tanggal_moc ?? date('Y-m-d'));

function sc_pick_value($text, $labels, $default = '')
{
    foreach ((array) $labels as $label) {
        if (preg_match('/^' . preg_quote($label, '/') . '\s*:?\s*(.+)$/im', $text, $match)) {
            return trim($match[1]);
        }
    }

    return $default;
}

function sc_format_date_id($date, $withDay = false)
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

    $timestamp = strtotime($date) ?: time();
    $text = date('j', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    return $withDay ? $days[(int) date('w', $timestamp)] . ', ' . $text : $text;
}

$tujuan = sc_pick_value($isiSurat, ['Kepada', 'Tujuan'], 'All crew');
$positions = sc_pick_value($isiSurat, ['Positions', 'Position', 'Posisi'], 'Roustabout, Roomboy, Accs.Control, ToolPusher, Driller, Asst.Driller, Derrickman, Floorman, Mudboy, Mechanic, Motorman, Electric, Welder, TeknisiCrane, HSEOfficer');
$rigs = sc_pick_value($isiSurat, ['Rigs', 'Rig'], 'GW339, GW338, GW337, GW336');
$purpose = sc_pick_value(
    $isiSurat,
    ['Purpose', 'Maksud Panggilan'],
    'menandatangani ulang perjanjian kontrak kerja (Revisi PKWT yang lama), karena terdapat perubahan pada struktur gaji baru dan hal lainnya'
);
$callDate = sc_pick_value($isiSurat, ['Call Date', 'Tanggal Panggilan'], sc_format_date_id($tanggalMoc, true));
$callTime = sc_pick_value($isiSurat, ['Call Time', 'Jam'], '10.00 Wib');
$letterDate = sc_pick_value($isiSurat, ['Letter Date', 'Tanggal Surat'], sc_format_date_id($tanggalMoc));
$place = sc_pick_value($isiSurat, ['Place', 'Tempat Surat'], 'Duri');
$bodyText = sc_pick_value(
    $isiSurat,
    ['Body', 'Isi'],
    'Bersama surat ini kami menyampaikan kepada saudara posisi yang disebutkan diatas agar dapat hadir kekantor, untuk ' . $purpose . '. Maka dari itu dihimbau untuk dapat hadir ke kantor pada:'
);
$note = sc_pick_value(
    $isiSurat,
    ['Note', 'Catatan'],
    'Untuk yang berhalangan hadir karna masih schedule, diharapkan datang saat Schedule Off.'
);
$signer = sc_pick_value($isiSurat, ['Signer', 'Penandatangan'], 'Amy Faradila');
$signerTitle = sc_pick_value($isiSurat, ['Signer Title', 'Jabatan Penandatangan'], 'HRD Departement');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SC - <?= htmlspecialchars($nomorSurat) ?></title>
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
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 11mm 13mm 10mm;
            background: #fff;
            position: relative;
        }

        .letterhead {
            display: grid;
            grid-template-columns: 27mm 1fr 46mm;
            align-items: center;
            min-height: 24mm;
            margin-bottom: 6mm;
            border-top: 3mm solid #303030;
        }

        .brand-logo {
            width: 24mm;
            height: 22mm;
            object-fit: contain;
        }

        .brand-title {
            padding-left: 5mm;
            font-size: 18pt;
            font-weight: 800;
            letter-spacing: 0;
        }

        .brand-subtitle {
            margin-top: 1mm;
            padding-left: 24mm;
            font-size: 8pt;
            font-style: italic;
            font-weight: 700;
        }

        .sigma-band {
            align-self: start;
            height: 12mm;
            padding-top: 3.2mm;
            background: linear-gradient(135deg, transparent 0 24%, #f2aa2f 24% 100%);
            color: #c42c2c;
            font-size: 13pt;
            font-weight: 800;
            text-align: right;
            white-space: nowrap;
        }

        .title {
            text-align: center;
            margin: 3mm 0 11mm;
        }

        .title h1 {
            margin: 0 0 5mm;
            font-size: 16pt;
            text-decoration: underline;
        }

        .title .number {
            font-size: 10.5pt;
            font-weight: 700;
        }

        .content {
            width: 78%;
            margin: 0 auto;
            line-height: 1.48;
        }

        .recipient {
            margin-bottom: 10mm;
        }

        .recipient-target {
            margin: 6mm 0 7mm;
            text-align: center;
            font-weight: 700;
            line-height: 1.55;
        }

        .greeting {
            margin: 10mm 0 5mm;
        }

        .body-copy {
            margin-bottom: 5mm;
            text-align: justify;
        }

        .schedule {
            width: 62%;
            margin: 4mm auto;
            border-collapse: collapse;
        }

        .schedule td {
            padding: 1.5mm 0;
            vertical-align: top;
        }

        .schedule .label {
            width: 25mm;
        }

        .schedule .colon {
            width: 5mm;
            text-align: center;
        }

        .closing {
            margin-top: 4mm;
            text-align: justify;
        }

        .signature {
            margin-top: 10mm;
            width: 48mm;
        }

        .signature-place {
            margin-bottom: 2mm;
        }

        .signature-img {
            display: block;
            width: 37mm;
            height: 22mm;
            object-fit: contain;
            margin-left: -4mm;
        }

        .signer {
            font-weight: 700;
            text-decoration: underline;
        }

        .note {
            position: absolute;
            left: 30mm;
            right: 18mm;
            bottom: 9mm;
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
                padding: 11mm 13mm 10mm;
            }

            .brand-logo,
            .signature-img,
            .sigma-band {
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
        <header class="letterhead">
            <img class="brand-logo" src="<?= BASE_URL ?>/public/img/logo-adk.png" alt="Logo ADK-G">
            <div>
                <div class="brand-title">PT. ADK ENAM INDONESIA</div>
                <div class="brand-subtitle">ala bizikrillahi tatma'innul qulub</div>
            </div>
            <div class="sigma-band">SIGMA GROUP</div>
        </header>

        <section class="title">
            <h1>SURAT PANGGILAN</h1>
            <div class="number">No: <?= htmlspecialchars($nomorSurat) ?></div>
        </section>

        <main class="content">
            <section class="recipient">
                <div>Kepada Yth,</div>
                <div class="recipient-target">
                    <div><?= htmlspecialchars($tujuan) ?> Potition : <?= htmlspecialchars($positions) ?></div>
                    <div><?= htmlspecialchars($rigs) ?></div>
                </div>
                <div>Di</div>
                <div>Tempat</div>
            </section>

            <div class="greeting">Dengan Hormat,</div>

            <div class="body-copy"><?= nl2br(htmlspecialchars($bodyText)) ?></div>

            <table class="schedule">
                <tr>
                    <td class="label">Hari</td>
                    <td class="colon">:</td>
                    <td><?= htmlspecialchars($callDate) ?></td>
                </tr>
                <tr>
                    <td class="label">Jam</td>
                    <td class="colon">:</td>
                    <td><?= htmlspecialchars($callTime) ?></td>
                </tr>
            </table>

            <div class="closing">
                Demikian disampaian untuk dapat dilaksanakan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.
            </div>

            <section class="signature">
                <div class="signature-place"><?= htmlspecialchars($place) ?>, <?= htmlspecialchars($letterDate) ?></div>
                <img class="signature-img" src="<?= BASE_URL ?>/public/img/ttd-hrd.png" alt="Tanda tangan HRD">
                <div class="signer"><?= htmlspecialchars($signer) ?></div>
                <div><?= htmlspecialchars($signerTitle) ?></div>
            </section>
        </main>

        <div class="note">Note : <?= htmlspecialchars($note) ?></div>

        <div class="action-buttons no-print">
            <button onclick="window.print()" class="btn btn-success">Cetak Surat</button>
            <button onclick="window.location.href='<?= BASE_URL ?>/surat'" class="btn btn-secondary">Tutup</button>
        </div>
    </div>
</body>
</html>
