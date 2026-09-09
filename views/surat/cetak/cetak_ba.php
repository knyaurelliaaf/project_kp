<?php
$nomorSurat = $surat['nomor_surat'] ?? ($nomor_surat ?? '');
$isiSurat = $surat['isi_surat'] ?? ($isi_surat ?? '');
$tanggalMoc = $surat['tanggal_moc'] ?? ($tanggal_moc ?? date('Y-m-d'));

function ba_pick_value($text, $labels, $default = '')
{
    foreach ((array) $labels as $label) {
        if (preg_match('/^' . preg_quote($label, '/') . '\s*:?\s*(.+)$/im', $text, $match)) {
            return trim($match[1]);
        }
    }
    return $default;
}

function ba_format_date_id($date)
{
    if (!$date) return '';
    $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $timestamp = strtotime($date) ?: time();
    return date('j', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
}

function ba_format_date_id_with_day($date)
{
    if (!$date) return '';
    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $timestamp = strtotime($date);
    if (!$timestamp) return $date;
    return $days[(int) date('w', $timestamp)] . ', ' . ba_format_date_id($date);
}

function ba_date_text($value, $withDay = false)
{
    if (!$value) return '';
    return strtotime($value) ? ($withDay ? ba_format_date_id_with_day($value) : ba_format_date_id($value)) : $value;
}

$tujuan = ba_pick_value($isiSurat, ['Kepada', 'Tujuan'], 'Crew terkait');
$positions = ba_pick_value($isiSurat, ['Posisi', 'Position', 'Positions'], '');
$rigs = ba_pick_value($isiSurat, ['Rigs', 'Rig'], '');
$nama = ba_pick_value($isiSurat, ['Nama', 'Nama Crew'], '');
$posisi = ba_pick_value($isiSurat, ['Jabatan', 'Posisi'], '');
$rig = ba_pick_value($isiSurat, ['Rig'], '');

$displayName = $nama ?: ($tujuan ?: 'Crew terkait');
$displayPosisi = $posisi ?: ($positions ?: '-');

$kontrak = ba_pick_value($isiSurat, ['No Kontrak', 'Nomor Kontrak'], '');
$startOjt = ba_pick_value($isiSurat, ['Start OJT', 'OJT Start'], '');
$endOjt = ba_pick_value($isiSurat, ['End OJT', 'OJT End'], '');
$assessmentDate = ba_pick_value($isiSurat, ['Tanggal Assessment', 'Assessment Date'], '');
$hasil = ba_pick_value($isiSurat, ['Hasil', 'Result'], 'Passed');
$place = ba_pick_value($isiSurat, ['Tempat', 'Tempat Surat'], 'Tanggul');
$letterDate = ba_pick_value($isiSurat, ['Tanggal Surat'], ba_format_date_id($tanggalMoc));
$signer = ba_pick_value($isiSurat, ['Signer DSR', 'Signer', 'Penandatangan'], '');
$signerTitle = ba_pick_value($isiSurat, ['Signer Title', 'Jabatan Penandatangan'], '');
$signerRsm = ba_pick_value($isiSurat, ['Signer RSM'], '');
$signerHse = ba_pick_value($isiSurat, ['Signer HSE'], '');

$startOjtText = ba_date_text($startOjt);
$endOjtText = ba_date_text($endOjt);
$assessmentDateText = ba_date_text($assessmentDate);
$assessmentDateWithDay = ba_date_text($assessmentDate, true);
$letterDateText = $letterDate ? ba_date_text($letterDate) : ba_format_date_id($tanggalMoc);

// Determine rig display name
$rigDisplay = $rigs ?: $rig ?: 'GW-339';
$rigShort = str_replace(['GWDC ', 'GWDC', 'GW '], ['GW-', 'GW-', 'GW-'], $rigDisplay);
if (strpos($rigShort, 'GW-') === false && strpos($rigShort, 'GW') !== false) {
    $rigShort = str_replace('GW', 'GW-', $rigShort);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BA - <?= htmlspecialchars($nomorSurat) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @page { size: A4 portrait; margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f0f0f0; color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 8.2pt; }
        .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 9mm 16mm 12mm; background: #fff; position: relative; }
        .content-wrap { padding: 0 8mm; }

        .letterhead-table { width: 100%; border-collapse: collapse; border-bottom: 1px solid #222; margin-bottom: 1mm; }
        .letterhead-table td { vertical-align: middle; padding: 1mm 2mm 2mm; }
        .letterhead-table .logo-cell { width: 34mm; text-align: left; }
        .letterhead-table .logo-cell img { width: 31mm; height: 16mm; object-fit: contain; }
        .letterhead-table .brand-cell { text-align: center; }
        .letterhead-table .brand-title { font-size: 15pt; font-weight: 800; letter-spacing: 0; color: #777; line-height: 1; }
        .letterhead-table .brand-subtitle { font-size: 5.5pt; font-weight: 700; margin-top: 1mm; }
        .letterhead-table .brand-address { font-size: 4.6pt; margin-top: .8mm; color: #333; }
        .letterhead-table .sigma-cell { width: 35mm; text-align: right; }
        .letterhead-table .sigma-cell img { width: 33mm; height: 14mm; object-fit: contain; }

        .title { text-align: center; margin: 2mm 0 8mm; border-top: 1px solid #222; padding-top: 2mm; }
        .title h1 { margin: 0; font-size: 13pt; line-height: 1.35; text-decoration: underline; font-weight: 800; }
        .title .number { font-size: 8pt; font-weight: 700; margin-top: 1mm; }

        .content { width: 100%; line-height: 1.75; }
        .body-copy { margin-bottom: 3mm; text-align: justify; font-size: 8.2pt; }
        .body-copy-sm { margin-bottom: 2mm; text-align: justify; font-size: 8.2pt; }
        .mark { text-decoration: underline; text-decoration-color: #e75b8a; text-decoration-thickness: 1.2px; text-underline-offset: 2px; }

        .assessment-table { width: 100%; border-collapse: collapse; margin: 2mm auto 8mm; font-size: 6.2pt; line-height: 1.15; }
        .assessment-table th { background: #00aeef; color: #000; font-weight: 800; padding: 1.5mm 1mm; text-align: center; border: 1px solid #000; vertical-align: middle; }
        .assessment-table td { background: #00aeef; border: 1px solid #000; padding: 1.5mm 1mm; vertical-align: middle; text-align: center; font-weight: 700; }
        .assessment-table td.left { text-align: center; }
        .assessment-table .no-col { width: 9mm; }
        .assessment-table .nama-col { width: 36mm; }
        .assessment-table .posisi-col { width: 25mm; }
        .assessment-table .date-col { width: 26mm; }
        .result-passed { color: #000; font-weight: 700; }
        .result-failed { color: #c42c2c; font-weight: 700; }

        .signature-section { width: 100%; margin-top: 24mm; }
        .signature-table { width: 100%; border-collapse: collapse; }
        .signature-table td { text-align: center; vertical-align: top; padding: 0 5mm; width: 33.33%; }
        .signature-table .sign-place { font-size: 8pt; margin-bottom: 1mm; text-align: left; }
        .signature-table .approval { font-size: 8pt; margin-bottom: 28mm; text-align: left; }
        .signature-table .signer-name { font-size: 7.5pt; margin-top: 1mm; }
        .signature-table .signer-title { font-size: 7.2pt; margin-top: 1.5mm; }

        .action-buttons { margin-top: 10mm; padding: 6mm 18mm; border-top: 1px solid #ddd; text-align: center; }
        .btn { margin: 0 4mm; padding: 9px 22px; border: 0; border-radius: 5px; color: #fff; font-size: 12pt; cursor: pointer; }
        .btn-success { background: #0f9b58; }
        .btn-secondary { background: #6c757d; }
        @media print {
            body { background: #fff; }
            .page { width: auto; min-height: auto; margin: 0; }
            .assessment-table th { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .assessment-table td { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="page">

        <table class="letterhead-table">
            <tr>
                <td class="logo-cell"><img src="<?= BASE_URL ?>/public/img/logo-gwdc.png" alt="Logo GWDC"></td>
                <td class="brand-cell">
                    <div class="brand-title">PT. GREATWALL DRILLING ASIA PACIFIC</div>
                    <div class="brand-address">Office: Jl. Bypass Rumbai - Minas Road, A8 F1, Pekanbaru Riau</div>
                    <div class="brand-subtitle">Providing The Best Solution For Your Drilling Needs</div>
                </td>
                <td class="sigma-cell">
                    <img src="<?= BASE_URL ?>/public/img/tsi.jpg" alt="TSI">
                </td>
            </tr>
        </table>

        <div class="content-wrap">

            <section class="title">
                <h1>BERITA ACARA EVALUASI HASIL ASSESSMENT & OJT CREW PENGGANTI RIG DRILLING <?= htmlspecialchars($rigShort) ?></h1>
                <div class="number">No: <?= htmlspecialchars($nomorSurat) ?></div>
            </section>

            <main class="content">
                <td class="sigma-cell">
                    <img src="<?= BASE_URL ?>/public/img/tsi.jpg" alt="TSI">
                </td>
            </tr>
        </table>

        <div class="content-wrap">

            <section class="title">
                <h1>BERITA ACARA EVALUASI HASIL ASSESSMENT & OJT CREW PENGGANTI RIG DRILLING <?= htmlspecialchars($rigShort) ?></h1>
                <div class="number">No: <?= htmlspecialchars($nomorSurat) ?></div>
            </section>

            <main class="content">
                <div class="body-copy">
                    Berita acara ini dibuat oleh 3 pilar <?= htmlspecialchars($rigShort) ?> (RSM/Rig Supt., DSR, dan FHL) dengan tujuan untuk melakukan <span class="mark">Verifikasi & Validasi</span> kelulusan OJT, <span class="mark">Familiariasasi</span> dan assessment personel Rig Drilling <?= htmlspecialchars($rigShort) ?> dengan nomor Kontrak <?= htmlspecialchars($kontrak ?: 'SPHR00618A') ?> yang dilakukan pada hari <?= htmlspecialchars($assessmentDateWithDay ?: 'Kamis, 18 Juni 2026') ?>. Berdasarkan <span class="mark">Verifikasi & Validasi</span> tersebut, personel di bawah ini dinyatakan lulus karena sudah memenuhi persyaratan sesuai kontrak dan dari hasil assessment yang sudah dilakukan.
                </div>

                <table class="assessment-table">
                    <thead>
                        <tr>
                            <th class="no-col">NO</th>
                            <th class="nama-col">NAMA CREW</th>
                            <th class="posisi-col">POSISI</th>
                            <th class="date-col">START OJT</th>
                            <th class="date-col">END OJT</th>
                            <th class="date-col">DATE ASSESSMENT</th>
                            <th>RESULT ASSESSMENT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td class="left"><strong><?= htmlspecialchars($displayName) ?></strong></td>
                            <td><?= htmlspecialchars($displayPosisi) ?></td>
                            <td><?= htmlspecialchars($startOjtText ?: '___________________') ?></td>
                            <td><?= htmlspecialchars($endOjtText ?: '___________________') ?></td>
                            <td><?= htmlspecialchars($assessmentDateText ?: '___________________') ?></td>
                            <td class="<?= strtolower($hasil) === 'passed' ? 'result-passed' : 'result-failed' ?>">
                                <strong><?= htmlspecialchars(ucfirst($hasil)) ?></strong>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="body-copy-sm">
                    Berita Acara ini digunakan sebagai referensi untuk proses approval pada evaluasi <span class="mark">crew</span> pengganti posisi <span class="mark">vacant</span>.
                </div>

                <div class="body-copy-sm">
                    Demikian berita acara ini dibuat untuk kelancaran operasi Rig <?= htmlspecialchars($rigShort) ?> dengan nomor <span class="mark">Kontrak</span>:<br>
                    <?= htmlspecialchars($kontrak ?: 'SPHR00618A') ?>.
                </div>

                <div class="signature-section">
                    <table class="signature-table">
                        <tr>
                            <td>
                                <div class="sign-place"><span class="mark"><?= htmlspecialchars($place ?: 'Tanggul') ?></span>, <?= htmlspecialchars($letterDateText) ?></div>
                                <div class="approval"><span class="mark">Disetujui</span> oleh,</div>
                                <div class="signer-name"><?= htmlspecialchars($signer ?: $nama ?: '___________________') ?></div>
                                <div class="signer-title">DSR <?= htmlspecialchars($rigShort) ?></div>
                            </td>
                            <td>
                                <div class="sign-place">&nbsp;</div>
                                <div class="approval">&nbsp;</div>
                                <div class="signer-name"><?= htmlspecialchars($signerRsm ?: 'Bustami Arifin') ?></div>
                                <div class="signer-title">RSM <?= htmlspecialchars($rigShort) ?></div>
                            </td>
                            <td>
                                <div class="sign-place">&nbsp;</div>
                                <div class="approval">&nbsp;</div>
                                <div class="signer-name"><?= htmlspecialchars($signerHse ?: 'Edison') ?></div>
                                <div class="signer-title">HSE Coord EDPP</div>
                            </td>
                        </tr>
                    </table>
                </div>
            </main>
        </div>

        <div class="action-buttons no-print">
            <button onclick="window.print()" class="btn btn-success"><i class="fas fa-print"></i> Cetak Surat</button>
            <button onclick="window.location.href='<?= BASE_URL ?>/surat'" class="btn btn-secondary"><i class="fas fa-times"></i> Tutup</button>
        </div>
    </div>
</body>
</html>
