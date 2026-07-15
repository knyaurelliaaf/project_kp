<?php
$nomorSurat = $surat['nomor_surat'] ?? ($nomor_surat ?? '');
$isiSurat = $surat['isi_surat'] ?? ($isi_surat ?? '');
$tanggalMoc = $surat['tanggal_moc'] ?? ($tanggal_moc ?? date('Y-m-d'));

function sr_pick_value($text, $labels, $default = '')
{
    foreach ((array) $labels as $label) {
        if (preg_match('/^' . preg_quote($label, '/') . '\s*:?\s*(.+)$/im', $text, $match)) {
            return trim($match[1]);
        }
    }
    return $default;
}

function sr_format_date_id($date)
{
    if (!$date) return '';
    $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $timestamp = strtotime($date) ?: time();
    return date('j', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
}

function sr_date_text($value)
{
    if (!$value) return '';
    return strtotime($value) ? sr_format_date_id($value) : $value;
}

$tujuan = sr_pick_value($isiSurat, ['Kepada', 'Tujuan'], 'Seluruh karyawan Crew GWDC 339,338,337,336');
$positions = sr_pick_value($isiSurat, ['Posisi', 'Position', 'Positions'], '');
$rigs = sr_pick_value($isiSurat, ['Rigs', 'Rig'], '');
$perihal = sr_pick_value($isiSurat, ['Perihal'], 'Ketentuan Pengunduran Diri (Resign) dan Penalty Kontrak');
$lampiran = sr_pick_value($isiSurat, ['Lampiran'], '1');
$letterDate = sr_pick_value($isiSurat, ['Tanggal Surat'], sr_format_date_id($tanggalMoc));
$place = sr_pick_value($isiSurat, ['Tempat Surat', 'Tempat'], '');
$signer = sr_pick_value($isiSurat, ['Signer', 'Penandatangan'], '');
$signerTitle = sr_pick_value($isiSurat, ['Signer Title', 'Jabatan Penandatangan'], '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SR - <?= htmlspecialchars($nomorSurat) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @page { size: A4; margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f0f0f0; color: #000; font-family: "Times New Roman", Times, serif; font-size: 10.5pt; }
        .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 0; background: #fff; position: relative; }
        .kop-surat { width: 100%; display: block; }
        .content-wrap { padding: 7mm 21mm 10mm; }
        .title { text-align: center; margin: 0 0 11mm; }
        .title h1 { margin: 0; font-size: 13pt; text-decoration: underline; font-weight: 700; }
        .content { width: 100%; line-height: 1.15; }
        .meta-table { border-collapse: collapse; margin-bottom: 8mm; width: 100%; }
        .meta-table td { padding: 0; vertical-align: top; }
        .meta-table .label { width: 24mm; }
        .meta-table .sep { width: 4mm; text-align: center; }
        .meta-table .value { width: auto; }
        .subject { font-weight: 700; text-decoration: underline; }
        .recipient { margin-bottom: 6mm; }
        .recipient-line { margin-bottom: 1mm; }
        .greeting { margin: 5mm 0 3mm; text-align: justify; }
        .body-copy { margin-bottom: 3mm; text-align: justify; line-height: 1.15; }
        .ordered-list { margin: 5mm 0 5mm 8mm; padding-left: 6mm; }
        .ordered-list li { margin-bottom: 4mm; text-align: justify; padding-left: 2mm; }
        .signature { margin-top: 5mm; width: 48%; margin-left: auto; text-align: center; }
        .signature-img { display: block; width: 37mm; height: 22mm; object-fit: contain; margin: 0 auto 2mm; }
        .signer { font-weight: 700; text-decoration: underline; }
        .action-buttons { margin-top: 10mm; padding: 6mm 18mm; border-top: 1px solid #ddd; text-align: center; }
        .btn { margin: 0 4mm; padding: 9px 22px; border: 0; border-radius: 5px; color: #fff; font-size: 12pt; cursor: pointer; }
        .btn-success { background: #0f9b58; }
        .btn-secondary { background: #6c757d; }
        @media print {
            body { background: #fff; }
            .page { width: auto; min-height: auto; margin: 0; }
            .kop-surat { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="page">
        <img class="kop-surat" src="<?= BASE_URL ?>/public/img/kop-surat-1.png" alt="Kop Surat">

        <div class="content-wrap">
            <section class="title">
                <h1>MEMO INTERNAL</h1>
            </section>

            <main class="content">
                <table class="meta-table">
                    <tr>
                        <td class="label">Nomor</td>
                        <td class="sep">:</td>
                        <td class="value"><?= htmlspecialchars($nomorSurat) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Perihal</td>
                        <td class="sep">:</td>
                        <td class="value subject"><?= htmlspecialchars($perihal) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Lampiran</td>
                        <td class="sep">:</td>
                        <td class="value"><?= htmlspecialchars($lampiran) ?></td>
                    </tr>
                </table>

                <section class="recipient">
                    <div class="recipient-line">Kepada Yth,</div>
                    <div><?= htmlspecialchars($tujuan) ?></div>
                    <?php if ($positions): ?>
                    <div>Posisi: <?= htmlspecialchars($positions) ?></div>
                    <?php endif; ?>
                    <?php if ($rigs): ?>
                    <div>Rig: <?= htmlspecialchars($rigs) ?></div>
                    <?php endif; ?>
                    <div>Di</div>
                    <div>Tempat</div>
                </section>

                <div class="greeting">
                    Sehubungan dalam rangka menjaga disiplin kerja serta kelancaran operasional perusahaan, bersama ini disampaikan kembali ketentuan terkait <strong>pengunduran diri (resign)</strong> karena masih ditemukannya karyawan yang mengajukan pengunduran diri sebelum masa kontrak kerja berakhir serta tidak menjalankan prosedur yang telah ditetapkan, maka melalui memo ini perusahaan menegaskan kembali ketentuan sebagai berikut:
                </div>

                <ol class="ordered-list">
                    <li>Setiap karyawan yang bermaksud <strong>mengundurkan diri (resign)</strong> wajib mengajukan <strong>surat pemberitahuan resmi minimal 1 (satu) bulan sebelumnya (1 Month Notice)</strong> kepada perusahaan.</li>
                    <li>Karyawan yang <strong>mengundurkan diri sebelum masa kontrak kerja selesai</strong> akan dikenakan <strong>penalty/denda sesuai dengan ketentuan dalam Perjanjian Kerja yang telah disepakati.</strong></li>
                    <li>Karyawan yang <strong>tidak menjalankan 1 Month Notice</strong>, meninggalkan pekerjaan secara sepihak, atau berhenti tanpa prosedur yang berlaku akan dianggap melanggar perjanjian kerja dan perusahaan berhak menerapkan sanksi administratif serta penalty sesuai ketentuan perusahaan.</li>
                    <li>Selama masa notice period, karyawan tetap wajib <strong>menjalankan tugas dan tanggung jawab pekerjaan sampai proses serah terima pekerjaan (handover)</strong> selesai dan disetujui oleh atasan langsung.</li>
                </ol>

                <div class="body-copy" style="margin-top:5mm;">
                    Perusahaan <strong>tidak akan memproses clearance maupun administrasi akhir karyawan</strong> apabila kewajiban tersebut tidak dipenuhi sesuai ketentuan yang berlaku.
                </div>
                <div class="body-copy">
                    Memo ini bersifat mengikat dan wajib dipatuhi oleh seluruh karyawan tanpa pengecualian guna menjaga disiplin kerja serta kelancaran operasional perusahaan.
                </div>
                <div class="body-copy">
                    Demikian memo ini disampaikan untuk dipahami dan dilaksanakan sebagaimana mestinya.
                </div>

                <section class="signature">
                    <div style="margin-bottom:2mm;"><?= htmlspecialchars($place ?: 'Duri') ?>, <?= htmlspecialchars(sr_date_text($letterDate) ?: sr_format_date_id($tanggalMoc)) ?></div>
                    <img class="signature-img" src="<?= BASE_URL ?>/public/img/ttd-hrd.png" alt="Tanda tangan HRD">
                    <div class="signer"><?= htmlspecialchars($signer ?: 'Amy Faradila') ?></div>
                    <div><?= htmlspecialchars($signerTitle ?: 'HRD Department') ?></div>
                </section>
            </main>
        </div>

        <div class="action-buttons no-print">
            <button onclick="window.print()" class="btn btn-success"><i class="fas fa-print"></i> Cetak Surat</button>
            <button onclick="window.location.href='<?= BASE_URL ?>/surat'" class="btn btn-secondary"><i class="fas fa-times"></i> Tutup</button>
        </div>
    </div>
</body>
</html>
