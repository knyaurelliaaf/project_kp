<?php
$nomorSurat = $surat['nomor_surat'] ?? ($nomor_surat ?? '');
$isiSurat = $surat['isi_surat'] ?? ($isi_surat ?? '');
$tanggalMoc = $surat['tanggal_moc'] ?? ($tanggal_moc ?? date('Y-m-d'));

function sj_pick_value($text, $labels, $default = '')
{
    foreach ((array) $labels as $label) {
        if (preg_match('/^' . preg_quote($label, '/') . '\s*:?\s*(.+)$/im', $text, $match)) {
            return trim($match[1]);
        }
    }
    return $default;
}

function sj_format_date_id($date)
{
    if (!$date) return '';
    $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $timestamp = strtotime($date) ?: time();
    return date('j', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
}

function sj_format_date_id_with_day($date)
{
    if (!$date) return '';
    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $timestamp = strtotime($date);
    if (!$timestamp) return $date;
    return $days[(int) date('w', $timestamp)] . ' tanggal ' . sj_format_date_id($date);
}

$agreementDate = sj_pick_value($isiSurat, ['Tanggal Perjanjian'], $tanggalMoc);
$p1Name = sj_pick_value($isiSurat, ['Pihak Pertama Nama'], '');
$p1Birth = sj_pick_value($isiSurat, ['Pihak Pertama TTL'], '');
$p1Address = sj_pick_value($isiSurat, ['Pihak Pertama Alamat'], '');
$p1Domicile = sj_pick_value($isiSurat, ['Pihak Pertama Domisili'], '');
$p1Position = sj_pick_value($isiSurat, ['Pihak Pertama Jabatan'], '');
$p2Name = sj_pick_value($isiSurat, ['Pihak Kedua Nama'], 'Widhia Lukman Hakim');
$p2Birth = sj_pick_value($isiSurat, ['Pihak Kedua TTL'], 'Padang, 03 September 1986');
$p2Address = sj_pick_value($isiSurat, ['Pihak Kedua Alamat'], 'Jl. Asrama Tribat Gg.Patih No.555 Pematang Pudu Mandau Duri');
$p2Domicile = sj_pick_value($isiSurat, ['Pihak Kedua Domisili'], 'Jl. Asrama Tribat Gg.Patih No.555 Pematang Pudu Mandau Duri');
$p2Position = sj_pick_value($isiSurat, ['Pihak Kedua Jabatan'], 'Direktur Utama PT. ADK Enam Indonesia');
$fee = sj_pick_value($isiSurat, ['Biaya Sertifikat'], 'Rp. 12.000.000');
$certName = sj_pick_value($isiSurat, ['Nama Sertifikat'], 'Asme Welder');
$deduction = sj_pick_value($isiSurat, ['Potongan Perbulan'], 'Rp. 2.000.000');
$courseDuration = sj_pick_value($isiSurat, ['Durasi Sertifikasi'], '2 (dua)');
$workPeriod = sj_pick_value($isiSurat, ['Ikatan Kerja'], '1 (satu) bulan');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SJ - <?= htmlspecialchars($nomorSurat) ?></title>
    <style>
        @page { size: A4; margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f0f0f0; color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 8.5pt; }
        .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 13mm 25mm 16mm; background: #fff; }
        .title { text-align: center; font-weight: 700; line-height: 1.15; margin: 0 0 12mm; }
        .content { line-height: 1.25; }
        .party-table { width: 100%; border-collapse: collapse; margin: 5mm 0 6mm 13mm; }
        .party-table td { padding: 2mm 0; vertical-align: top; }
        .party-table .label { width: 34mm; }
        .party-table .sep { width: 5mm; }
        .party-table .value { border-bottom: 1px dotted #111; width: 94mm; }
        .section-title { font-weight: 700; margin: 4mm 0 3mm; }
        ol { margin: 3mm 0 5mm 8mm; padding-left: 5mm; }
        li { margin-bottom: 1.2mm; text-align: justify; padding-left: 1mm; }
        .closing { text-align: justify; margin-top: 4mm; }
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 9mm; }
        .signature-table td { width: 50%; vertical-align: top; font-weight: 700; }
        .sign-space { height: 27mm; }
        .sign-name { font-weight: 700; text-decoration: underline; }
        .action-buttons { margin-top: 10mm; padding: 6mm 18mm; border-top: 1px solid #ddd; text-align: center; }
        .btn { margin: 0 4mm; padding: 9px 22px; border: 0; border-radius: 5px; color: #fff; font-size: 12pt; cursor: pointer; }
        .btn-success { background: #0f9b58; }
        .btn-secondary { background: #6c757d; }
        @media print {
            body { background: #fff; }
            .page { width: auto; min-height: auto; margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="title">
            <div>SURAT PERJANJIAN</div>
            <div>ANTARA KARYAWAN DAN PERUSAHAAN</div>
        </div>

        <main class="content">
            <p>Pada hari ini <?= htmlspecialchars(sj_format_date_id_with_day($agreementDate)) ?> sepakat membuat perjanjian yaitu:</p>

            <table class="party-table">
                <tr><td class="label">Nama</td><td class="sep">:</td><td class="value"><?= htmlspecialchars($p1Name) ?></td></tr>
                <tr><td class="label">Tempat/tgl.lahir</td><td class="sep">:</td><td class="value"><?= htmlspecialchars($p1Birth) ?></td></tr>
                <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value"><?= htmlspecialchars($p1Address) ?></td></tr>
                <tr><td class="label">Domisili</td><td class="sep">:</td><td class="value"><?= htmlspecialchars($p1Domicile) ?></td></tr>
                <tr><td class="label">Jabatan</td><td class="sep">:</td><td class="value"><?= htmlspecialchars($p1Position) ?></td></tr>
            </table>

            <div class="section-title">Untuk selanjutnya disebut PIHAK PERTAMA</div>
            <table class="party-table">
                <tr><td class="label">Nama</td><td class="sep">:</td><td><?= htmlspecialchars($p2Name) ?></td></tr>
                <tr><td class="label">Tempat/tgl.lahir</td><td class="sep">:</td><td><?= htmlspecialchars($p2Birth) ?></td></tr>
                <tr><td class="label">Alamat</td><td class="sep">:</td><td><?= htmlspecialchars($p2Address) ?></td></tr>
                <tr><td class="label">Domisili</td><td class="sep">:</td><td><?= htmlspecialchars($p2Domicile) ?></td></tr>
                <tr><td class="label">Jabatan</td><td class="sep">:</td><td><?= htmlspecialchars($p2Position) ?></td></tr>
            </table>

            <div class="section-title">Untuk selanjutnya disebut PIHAK KEDUA</div>
            <p>Maka melalui surat perjanjian ini disetujui oleh kedua belah pihak ketentuan-ketentuan sebagaimana tercantum di bawah ini:</p>
            <ol>
                <li>PIHAK PERTAMA bersedia membuat sertifikat <?= htmlspecialchars($certName) ?> dengan biaya <?= htmlspecialchars($fee) ?> dengan potongan perbulan <?= htmlspecialchars($deduction) ?>.</li>
                <li>PIHAK KEDUA bersedia membayarkan biaya sertifikasi ke provider Sigma Enam Indonesia dengan sistem mendahulukan pembayaran Sertifikasi Pihak Pertama ke Provider.</li>
                <li>PIHAK PERTAMA berjanji akan melunasi biaya Sertifikasi <?= htmlspecialchars($certName) ?> dengan sistem pemotongan gaji yang disesuaikan oleh Pihak Kedua dari ijazah Asli Sekolah terakhir sebagai jaminannya.</li>
                <li>PIHAK PERTAMA tidak akan mengundurkan diri dari Pihak Kedua selama biaya Sertifikasi <?= htmlspecialchars($certName) ?> belum dilunasi.</li>
                <li>PIHAK PERTAMA akan menerima Sertifikat Asli setelah melunasi biaya Sertifikasi.</li>
                <li>Surat Perjanjian ini dibuat dalam <?= htmlspecialchars($courseDuration) ?> rangkap dan masing-masing bermaterai cukup dan memiliki kekuatan hukum yang sama. Masing-masing surat untuk PIHAK PERTAMA dan PIHAK KEDUA.</li>
                <li>Surat Perjanjian dibuat dan ditandatangani oleh kedua belah pihak secara sadar dan tanpa tekanan dari pihak manapun di tempat dan waktu penandatanganan Surat Perjanjian ini.</li>
            </ol>
            <p class="closing">Demikian Surat Perjanjian Hutang ini dibuat bersama di hadapan saksi-saksi dalam keadaan sehat jasmani dan rohani untuk dijadikan pegangan hukum bagi masing-masing pihak.</p>

            <table class="signature-table">
                <tr>
                    <td>PIHAK PERTAMA</td>
                    <td style="text-align:center;">PIHAK KEDUA</td>
                </tr>
                <tr>
                    <td><div class="sign-space"></div><span>..........................</span></td>
                    <td style="text-align:center;"><div class="sign-space"></div><span class="sign-name"><?= htmlspecialchars(strtoupper($p2Name)) ?></span></td>
                </tr>
            </table>
        </main>

        <div class="action-buttons no-print">
            <button onclick="window.print()" class="btn btn-success">Cetak Surat</button>
            <button onclick="window.location.href='<?= BASE_URL ?>/surat'" class="btn btn-secondary">Tutup</button>
        </div>
    </div>
</body>
</html>
