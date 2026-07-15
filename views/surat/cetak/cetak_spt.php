<?php
$nomorSurat = $surat['nomor_surat'] ?? ($nomor_surat ?? '');
$isiSurat = $surat['isi_surat'] ?? ($isi_surat ?? '');
$tanggalMoc = $surat['tanggal_moc'] ?? ($tanggal_moc ?? date('Y-m-d'));

function spt_pick_value($text, $labels, $default = '')
{
    foreach ((array) $labels as $label) {
        if (preg_match('/^' . preg_quote($label, '/') . '\s*:?\s*(.+)$/im', $text, $match)) {
            return trim($match[1]);
        }
    }

    return $default;
}

function spt_format_date_id($date)
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

$tujuan = spt_pick_value($isiSurat, ['Kepada', 'Tujuan'], $surat['tujuan'] ?? 'Crew GWAP 339');
$perihal = spt_pick_value($isiSurat, ['Perihal'], $surat['perihal'] ?? 'Pemberitahuan');
$lampiran = spt_pick_value($isiSurat, ['Lampiran'], '1');
$paragraph1 = spt_pick_value($isiSurat, ['Paragraf 1'], 'Diberitahukan kepada seluruh karyawan GWAP339 yang sudah menanda tangani PKWT diwajibkan hadir bekerja sesuai dengan schedule yang sudah diterbitkan oleh management, bagi yang berhalangan sakit agar dapat melampirkan bukti surat sakit dari klinik maupun tempat berobat.');
$paragraph2 = spt_pick_value($isiSurat, ['Paragraf 2'], 'Untuk Rig Admin masing-masing Crew agar melaporkan setiap kekurangan crew yang ada dilapangan kepada Tool Pusher, RSM dan Rig Superintendent yang bertugas.');
$paragraph3 = spt_pick_value($isiSurat, ['Paragraf 3'], 'Bagi karyawan yang masih proses CCPM agar tetap hadir dilapangan sesuai dengan schedule kerja masing-masing, tidak ada alasan lagi untuk tidak hadir karena kita sudah memberikan toleransi waktu untuk menyelesaikan schedule kerja diperusahaan lama.');
$closing = spt_pick_value($isiSurat, ['Penutup'], 'Demikian kami sampaikan atas perhatian dan kerjasamanya kami ucapkan terimakasih.');
$place = spt_pick_value($isiSurat, ['Tempat Surat', 'Tempat'], 'Duri');
$letterDate = spt_pick_value($isiSurat, ['Tanggal Surat'], spt_format_date_id($tanggalMoc));
$signer = spt_pick_value($isiSurat, ['Signer', 'Penandatangan'], 'Y. Chandra');
$signerTitle = spt_pick_value($isiSurat, ['Signer Title', 'Jabatan Penandatangan'], 'Project Manager');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPT - <?= htmlspecialchars($nomorSurat) ?></title>
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
            font-size: 11pt;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 5mm 15mm 13mm;
            background: #fff;
        }

        .kop {
            display: block;
            width: 100%;
            max-width: 186mm;
            margin: 0 auto 8mm;
            height: auto;
        }

        .content {
            width: 82%;
            margin: 0 auto;
            line-height: 1.18;
        }

        .meta {
            width: 100%;
            margin-bottom: 6mm;
            border-collapse: collapse;
        }

        .meta td {
            padding: .4mm 0;
            vertical-align: top;
        }

        .meta .label {
            width: 22mm;
        }

        .meta .colon {
            width: 4mm;
            text-align: center;
        }

        .subject {
            font-weight: 700;
        }

        .recipient {
            margin: 6mm 0;
        }

        .recipient-target {
            margin: 2mm 0 0 7mm;
            font-weight: 700;
        }

        .greeting {
            margin: 5mm 0 3mm;
        }

        p {
            margin: 0 0 4.3mm;
            text-align: justify;
        }

        .signature-wrap {
            display: flex;
            justify-content: flex-end;
            margin-top: 5mm;
        }

        .signature {
            width: 58mm;
            text-align: center;
        }

        .signature-date {
            margin-bottom: 1mm;
        }

        .signature-img {
            display: block;
            width: 44mm;
            height: 24mm;
            object-fit: contain;
            margin: 0 auto -2mm;
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
                padding: 5mm 15mm 13mm;
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
        <img class="kop" src="<?= BASE_URL ?>/public/img/kop-surat-1.png" alt="Kop Surat PT ADK Enam Indonesia">

        <main class="content">
            <table class="meta">
                <tr>
                    <td class="label">Nomor</td>
                    <td class="colon">:</td>
                    <td><?= htmlspecialchars($nomorSurat) ?></td>
                </tr>
                <tr>
                    <td class="label">Perihal</td>
                    <td class="colon">:</td>
                    <td class="subject"><?= htmlspecialchars($perihal) ?></td>
                </tr>
                <tr>
                    <td class="label">Lampiran</td>
                    <td class="colon">:</td>
                    <td><?= htmlspecialchars($lampiran) ?></td>
                </tr>
            </table>

            <section class="recipient">
                <div>Kepada Yth,</div>
                <div class="recipient-target">- <?= htmlspecialchars($tujuan) ?></div>
                <div>Di</div>
                <div>Tempat</div>
            </section>

            <div class="greeting">Dengan Hormat,</div>

            <p><?= htmlspecialchars($paragraph1) ?></p>
            <p><?= htmlspecialchars($paragraph2) ?></p>
            <p><?= htmlspecialchars($paragraph3) ?></p>
            <p><?= htmlspecialchars($closing) ?></p>

            <section class="signature-wrap">
                <div class="signature">
                    <div class="signature-date"><?= htmlspecialchars($place) ?>, <?= htmlspecialchars($letterDate) ?></div>
                    <img class="signature-img" src="<?= BASE_URL ?>/public/img/tt-projectManajer.png" alt="Tanda tangan Project Manager">
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
