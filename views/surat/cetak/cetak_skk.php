<?php
$nomorSurat = $surat['nomor_surat'] ?? ($nomor_surat ?? '');
$isiSurat = $surat['isi_surat'] ?? ($isi_surat ?? '');
$tanggalMoc = $surat['tanggal_moc'] ?? ($tanggal_moc ?? date('Y-m-d'));

function skk_pick_value($text, $labels, $default = '')
{
    foreach ((array) $labels as $label) {
        if (preg_match('/^' . preg_quote($label, '/') . '\s*:?\s*(.+)$/im', $text, $match)) {
            return trim($match[1]);
        }
    }
    return $default;
}

function skk_format_date_id($date)
{
    if (!$date) return '';
    $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $timestamp = strtotime($date) ?: time();
    return date('j', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
}

$tujuan = skk_pick_value($isiSurat, ['Kepada', 'Tujuan'], '');
$nama = skk_pick_value($isiSurat, ['Nama'], '');
$jabatan = skk_pick_value($isiSurat, ['Jabatan'], '');
$positions = skk_pick_value($isiSurat, ['Posisi'], '');
$project = skk_pick_value($isiSurat, ['Project', 'Proyek'], 'Drilling at PT . Greatwall Drilling Asia Pacific');
$startDate = skk_pick_value($isiSurat, ['Mulai Bekerja', 'Start Date'], '');

$displayName = $nama ?: ($tujuan ?: 'Crew terkait');
$displayJabatan = $jabatan ?: ($positions ?: '-');

$startDateText = $startDate ? skk_format_date_id($startDate) : '';
$letterDateText = skk_format_date_id($tanggalMoc);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SKK - <?= htmlspecialchars($nomorSurat) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @page { size: A4; margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f0f0f0; color: #111; font-family: Arial, Helvetica, sans-serif; font-size: 11pt; }
        .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 0; background: #fff; position: relative; }
        .kop-surat { width: 100%; display: block; }
        .content-wrap { padding: 0mm 18mm 10mm; }
        .title { text-align: center; margin: 0 0 5mm; }
        .title h1 { margin: 0 0 1mm; font-size: 16pt; text-decoration: underline; }
        .title .sub-title { font-size: 12pt; font-style: italic; margin-bottom: 1mm; }
        .title .number { font-size: 10.5pt; font-weight: 700; }
        .content { width: 100%; line-height: 1.5; }
        .greeting { margin: 4mm 0 3mm; }
        .greeting-en { margin-bottom: 5mm; font-style: italic; }
        .body-copy { margin-bottom: 4mm; text-align: justify; }
        .body-copy-en { margin-bottom: 5mm; text-align: justify; font-style: italic; }
        .employee-table { margin: 4mm 0 5mm; width: 100%; border-collapse: collapse; }
        .employee-table td { padding: 1.5mm 2mm; vertical-align: top; }
        .employee-table .label { width: 40mm; font-weight: 700; }
        .employee-table .label-en { width: 40mm; font-style: italic; color: #555; font-size: 9.5pt; }
        .employee-table .colon { width: 5mm; text-align: center; }
        .employee-table .value-en { font-style: italic; color: #555; font-size: 9.5pt; }
        .closing-en { font-style: italic; margin-top: 3mm; }
        .signature { margin-top: 10mm; width: 50%; margin-left: auto; text-align: center; }
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
                <h1>SURAT KETERANGAN KERJA</h1>
                <div class="sub-title">TO WHOM IT MAY CONCERN</div>
                <div class="number"><?= htmlspecialchars($nomorSurat) ?></div>
            </section>

            <main class="content">
                <div class="greeting">Dengan ini menerangkan bahwa:</div>
                <div class="greeting-en">This is to certified that</div>

                <table class="employee-table">
                    <tr>
                        <td class="label">Nama</td>
                        <td class="colon">:</td>
                        <td><strong><?= htmlspecialchars($displayName) ?></strong></td>
                    </tr>
                    <tr>
                        <td class="label-en">Name</td>
                        <td class="colon"></td>
                        <td class="value-en"><?= htmlspecialchars($displayName) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Proyek & Lokasi</td>
                        <td class="colon">:</td>
                        <td><?= htmlspecialchars($project) ?></td>
                    </tr>
                    <tr>
                        <td class="label-en">Project & Location</td>
                        <td class="colon"></td>
                        <td class="value-en"><?= htmlspecialchars($project) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Jabatan</td>
                        <td class="colon">:</td>
                        <td><?= htmlspecialchars($displayJabatan) ?></td>
                    </tr>
                    <tr>
                        <td class="label-en">Classification</td>
                        <td class="colon"></td>
                        <td class="value-en"><?= htmlspecialchars($displayJabatan) ?></td>
                    </tr>
                </table>

                <div class="body-copy">
                    Adalah benar merupakan karyawan PT.Greatwall Drilling Asia Pasifik yang telah aktif bekerja<?= $startDateText ? ' dan terhitung mulai tanggal ' . $startDateText : '' ?> sampai dengan surat keterangan ini diterbitkan.
                </div>
                <div class="body-copy-en">
                    It is true that I am an employee of PT GREATWALL DRILLING ASIA PACIFIC who has been actively working<?= $startDateText ? ' and has been working since ' . $startDateText : '' ?> until this certificate is issued.
                </div>

                <div class="body-copy">
                    Demikian surat keterangan ini dibuat agar dapat dipergunakan sebagaimana mestinya.
                </div>

                <section class="signature">
                    <div style="margin-bottom:20mm;"></div>
                    <img class="signature-img" src="<?= BASE_URL ?>/public/img/ttd-hrd.png" alt="Tanda tangan HRD">
                    <div class="signer"><?= htmlspecialchars($signer ?? 'Amy Faradila') ?></div>
                    <div><?= htmlspecialchars($signerTitle ?? 'HRD Department') ?></div>
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