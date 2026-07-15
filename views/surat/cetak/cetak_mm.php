<?php
$nomorSurat = $surat['nomor_surat'] ?? ($nomor_surat ?? '');
$isiSurat = $surat['isi_surat'] ?? ($isi_surat ?? '');
$tanggalMoc = $surat['tanggal_moc'] ?? ($tanggal_moc ?? date('Y-m-d'));

function mm_pick_value($text, $labels, $default = '')
{
    foreach ((array) $labels as $label) {
        if (preg_match('/^' . preg_quote($label, '/') . '\s*:?\s*(.+)$/im', $text, $match)) {
            return trim($match[1]);
        }
    }
    return $default;
}

function mm_pick_block($text, $label, $default = '')
{
    $fieldLabels = 'Model Memo|Judul|Perihal|Lampiran|Kepada|Tujuan|Positions|Position|Posisi|Rigs|Rig|Dari|Tanggal Surat|Tempat Surat|Tempat|Isi Pembuka|Poin Memo|Isi Lanjutan|Penutup|Signer HRD(?: Title)?|Signer Rig(?: Title)?|Signer Manager(?: Title)?';
    if (preg_match('/^' . preg_quote($label, '/') . '\s*:\s*([\s\S]*?)(?=^(?:' . $fieldLabels . ')\s*:|\z)/im', $text, $match)) {
        return trim($match[1]);
    }
    return $default;
}

function mm_format_date_id($date)
{
    if (!$date) return '';
    $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $timestamp = strtotime($date) ?: time();
    return date('j', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
}

function mm_date_text($value)
{
    if (!$value) return '';
    return strtotime($value) ? mm_format_date_id($value) : $value;
}

function mm_lines($text)
{
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $text)), fn($line) => $line !== ''));
}

function mm_memo_sections($text)
{
    $sections = [];
    foreach (mm_lines($text) as $line) {
        if (preg_match('/^(\d+)\.\s*(.*)$/', $line, $match)) {
            $sections[] = ['number' => $match[1], 'title' => $match[2], 'content' => []];
        } elseif (empty($sections)) {
            $sections[] = ['number' => count($sections) + 1, 'title' => $line, 'content' => []];
        } else {
            $sections[count($sections) - 1]['content'][] = $line;
        }
    }
    return $sections;
}

$model = strtolower(mm_pick_value($isiSurat, ['Model Memo'], 'memo internal'));
$isMemorandum = strpos($model, 'memorandum') !== false;
$title = mm_pick_value($isiSurat, ['Judul'], $isMemorandum ? 'MEMORANDUM' : 'MEMO INTERNAL');
$perihal = mm_pick_value($isiSurat, ['Perihal'], $isMemorandum ? 'Tidak Perpanjang Kontrak' : 'Informasi Kebijakan Kontrak dan Hak Karyawan');
$lampiran = mm_pick_value($isiSurat, ['Lampiran'], '-');
$tujuan = mm_pick_value($isiSurat, ['Kepada', 'Tujuan'], $isMemorandum ? 'Seluruh Karyawan' : 'Seluruh Crew Drilling GW336, GW337, GW338, GW339');
$positions = mm_pick_value($isiSurat, ['Positions', 'Position', 'Posisi'], '');
$rigs = mm_pick_value($isiSurat, ['Rigs', 'Rig'], '');
$dari = mm_pick_value($isiSurat, ['Dari'], 'HRD Department');
$tempat = mm_pick_value($isiSurat, ['Tempat Surat', 'Tempat'], 'Duri');
$tanggal = mm_pick_value($isiSurat, ['Tanggal Surat'], mm_format_date_id($tanggalMoc));
$intro = mm_pick_block($isiSurat, 'Isi Pembuka', $isMemorandum
    ? 'Sehubungan dengan kebutuhan operasional dan evaluasi kontrak kerja, bersama ini disampaikan pemberitahuan terkait status perpanjangan kontrak karyawan.'
    : 'Diberitahukan kepada seluruh Crew Drilling, terkait issue yang sedang berkembang di lokasi perihal isi dari PKWT Periode 2026-2027, maka kami sampaikan penjelasan sebagai berikut:');
$points = mm_lines(mm_pick_block($isiSurat, 'Poin Memo', $isMemorandum
    ? "Karyawan yang dituju agar menyelesaikan seluruh administrasi dan serah terima pekerjaan sesuai arahan atasan langsung.\nSeluruh aset perusahaan wajib dikembalikan dalam kondisi baik sebelum proses clearance selesai.\nKoordinasi akhir dilakukan bersama Rig, HRD, dan pihak terkait sesuai kebutuhan operasional."
    : "1. Kenaikan UMK 2026\nKenaikan UMK tahun 2026 saat ini masih dalam proses pengajuan dan menunggu persetujuan dari GDAP Jakarta. Apabila pengajuan tersebut telah disetujui, maka penyesuaian gaji akan dilakukan secara paralel dan akan diinformasikan kembali melalui memo resmi selanjutnya.\n2. Pesangon / Kompensasi PKWT\nKetentuan pesangon/kompensasi tidak dicantumkan dalam draft PKWT. Namun demikian, secara aktual:\n• Pemberian uang kompensasi tetap akan diberikan seperti periode sebelumnya.\n• Pembayaran kompensasi PKWT untuk periode 2025–2026 akan dilakukan paling lambat 2 (dua) bulan setelah pengajuan pencairan, terhitung sejak tanggal PKWT close.\n3. Uang Makan\nUang makan tidak dicantumkan dalam PKWT karena mengacu pada peraturan PHR yang mewajibkan penyediaan catering bagi crew di lokasi kerja.\nNamun secara aktual:\n• Pemberian uang makan tetap berjalan sesuai ketentuan yang berlaku saat ini.\n4. Hak dan Uang Cuti\nHak dan uang cuti tidak dicantumkan dalam draft PKWT, namun secara aktual:\n• Hak cuti tetap berlaku.\n• Pengajuan cuti dilakukan sesuai prosedur, yaitu melalui persetujuan Area Manager, Leader lokasi, dan Crew Coordinator menggunakan Form Cuti.\n• Uang cuti periode 2024–2025 akan dibayarkan paling lambat akhir April 2026.\n• Uang cuti periode 2025–2026 saat ini masih dalam proses pengajuan pencairan ke GDAP Jakarta.\n5. Masa Probation (3 Bulan)\nKebijakan probation selama 3 bulan diberlakukan sebagai bentuk evaluasi dan pembinaan terhadap karyawan. Hal ini bertujuan agar karyawan dapat melakukan perbaikan dalam aspek:\n• Kinerja\n• Kedisiplinan\n• Loyalitas\n• Kerja sama tim\n• Kepatuhan terhadap peraturan perusahaan\n• Hubungan kerja dan komunikasi yang baik dengan manajemen dan leader di lokasi\nApabila selama masa probation karyawan menunjukkan komitmen dan perbaikan yang baik, maka akan dilakukan evaluasi untuk perpanjangan kontrak berikutnya."));
$memoSections = mm_memo_sections(implode("\n", $points));
$followup = mm_pick_block($isiSurat, 'Isi Lanjutan', '');
$closing = mm_pick_block($isiSurat, 'Penutup', $isMemorandum ? 'Demikian memo ini disampaikan untuk dipahami dan dilaksanakan sebagaimana mestinya.' : 'Demikian memo ini disampaikan untuk dapat dipahami dengan baik. Atas perhatian dan kerja samanya kami ucapkan terima kasih.');
$signerHrd = mm_pick_value($isiSurat, ['Signer HRD'], 'Amy Faradila');
$signerHrdTitle = mm_pick_value($isiSurat, ['Signer HRD Title'], 'HR Department');
$signerRig = mm_pick_value($isiSurat, ['Signer Rig'], '');
$signerRigTitle = mm_pick_value($isiSurat, ['Signer Rig Title'], 'Rig Superintendent');
$signerManager = mm_pick_value($isiSurat, ['Signer Manager'], 'Area Manager');
$signerManagerTitle = mm_pick_value($isiSurat, ['Signer Manager Title'], 'Area Manager');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>MM - <?= htmlspecialchars($nomorSurat) ?></title>
    <style>
        @page { size: A4; margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f0f0f0; color: #000; font-family: "Times New Roman", Times, serif; font-size: 10.5pt; }
        .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 0; background: #fff; }
        .kop { width: 100%; display: block; }
        .content-wrap { padding: <?= $isMemorandum ? '7mm 20mm 12mm' : '7mm 21mm 12mm' ?>; }
        .title { text-align: center; font-weight: 700; text-decoration: underline; font-size: 13pt; margin: 0 0 4mm; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 7mm; }
        .meta td { padding: .4mm 0; vertical-align: top; }
        .meta .label { width: 24mm; }
        .meta .sep { width: 4mm; text-align: center; }
        .subject { font-weight: 700; text-decoration: underline; }
        .content { line-height: 1.2; }
        .body { text-align: justify; margin: 5mm 0; }
        ol { margin: 4mm 0 5mm 8mm; padding-left: 6mm; }
        li { margin-bottom: 3mm; text-align: justify; padding-left: 1mm; }
        .internal-date { text-align: right; margin: 0 0 5mm; }
        .memo-list { margin: 4mm 0 5mm 6mm; padding-left: 5mm; }
        .memo-list li { margin-bottom: 3.5mm; padding-left: 1mm; }
        .memo-title { font-weight: 700; }
        .memo-paragraph { margin-top: 1.3mm; text-align: justify; }
        .memo-bullets { margin: 1.3mm 0 0 5mm; padding-left: 4mm; }
        .memo-bullets li { margin: 0 0 1mm; }
        .recipient { margin: 0 0 7mm; }
        .recipient-target { margin: 4mm 0; text-align: center; font-weight: 700; line-height: 1.4; }
        .closing { margin-top: 5mm; text-align: justify; }
        .signature { margin-top: 8mm; text-align: center; }
        .signature.single { width: 48%; margin-left: auto; }
        .signature-grid { width: 100%; border-collapse: collapse; margin-top: 8mm; }
        .signature-grid td { width: 33.33%; text-align: center; vertical-align: top; padding: 0 3mm; }
        .approval-grid { width: 100%; border-collapse: collapse; margin-top: 5mm; }
        .approval-grid > tbody > tr > td { width: 50%; vertical-align: top; padding: 0 2mm; }
        .approval-grid .signature-grid { margin-top: 2mm; }
        .approval-grid .signature-grid td { padding: 0 1mm; font-size: 8.5pt; }
        .approval-grid .sign-img { width: 22mm; height: 16mm; margin: 1mm auto; }
        .approval-grid .sign-space { height: 18mm; }
        .sign-img { width: 34mm; height: 22mm; object-fit: contain; display: block; margin: 2mm auto 1mm; }
        .sign-space { height: 24mm; }
        .sign-name { font-weight: 700; text-decoration: underline; }
        .action-buttons { margin-top: 10mm; padding: 6mm 18mm; border-top: 1px solid #ddd; text-align: center; }
        .btn { margin: 0 4mm; padding: 9px 22px; border: 0; border-radius: 5px; color: #fff; font-size: 12pt; cursor: pointer; font-family: Arial, Helvetica, sans-serif; }
        .btn-success { background: #0f9b58; }
        .btn-secondary { background: #6c757d; }
        @media print {
            body { background: #fff; }
            .page { width: auto; min-height: auto; margin: 0; }
            .kop, .sign-img { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="page">
        <img class="kop" src="<?= BASE_URL ?>/public/img/kop-surat-1.png" alt="Kop Surat">
        <div class="content-wrap">
            <?php if (!$isMemorandum): ?><div class="title"><?= htmlspecialchars(strtoupper($title)) ?></div><?php endif; ?>
            <main class="content">
                <?php if (!$isMemorandum): ?>
                <div class="internal-date"><?= htmlspecialchars($tempat) ?>, <?= htmlspecialchars(mm_date_text($tanggal)) ?></div>
                <?php endif; ?>
                <table class="meta">
                    <tr><td class="label"><?= $isMemorandum ? 'Nomor' : 'No.' ?></td><td class="sep">:</td><td><?= htmlspecialchars($nomorSurat) ?></td></tr>
                    <tr><td class="label">Perihal</td><td class="sep">:</td><td class="subject"><?= htmlspecialchars($perihal) ?></td></tr>
                    <?php if ($isMemorandum): ?>
                    <tr><td class="label">Lampiran</td><td class="sep">:</td><td><?= htmlspecialchars($lampiran) ?></td></tr>
                    <?php endif; ?>
                    <?php if ($isMemorandum): ?>
                    <tr><td class="label">Dari</td><td class="sep">:</td><td><?= htmlspecialchars($dari) ?></td></tr>
                    <?php endif; ?>
                </table>

                <?php if ($isMemorandum): ?><div class="title"><?= htmlspecialchars(strtoupper($title)) ?></div><?php endif; ?>

                <?php if ($isMemorandum): ?>
                <section>
                    <div>Kepada Yth,</div>
                    <div><?= nl2br(htmlspecialchars($tujuan)) ?></div>
                    <div>Di</div>
                    <div>Tempat</div>
                </section>
                <?php else: ?>
                <section class="recipient">
                    <div>Kepada Yth,</div>
                    <div class="recipient-target">
                        <div><?= nl2br(htmlspecialchars($tujuan)) ?></div>
                        <?php if ($positions): ?><div><?= htmlspecialchars($positions) ?></div><?php endif; ?>
                        <?php if ($rigs): ?><div><?= htmlspecialchars($rigs) ?></div><?php endif; ?>
                    </div>
                    <div>Di</div>
                    <div>Tempat</div>
                </section>
                <div>Dengan Hormat,</div>
                <?php endif; ?>

                <div class="body"><?= nl2br(htmlspecialchars($intro)) ?></div>
                <?php if ($isMemorandum): ?>
                    <ol class="memo-list">
                        <?php foreach ($memoSections as $section): ?>
                        <li value="<?= (int) $section['number'] ?>">
                            <div class="memo-title"><?= htmlspecialchars($section['title']) ?></div>
                            <?php foreach ($section['content'] as $paragraph): ?><div class="memo-paragraph"><?= htmlspecialchars($paragraph) ?></div><?php endforeach; ?>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                    <?php if ($followup): ?><div class="body"><?= nl2br(htmlspecialchars($followup)) ?></div><?php endif; ?>
                <?php else: ?>
                    <ol class="memo-list">
                        <?php foreach ($memoSections as $section): ?>
                        <li value="<?= (int) $section['number'] ?>">
                            <div class="memo-title"><?= htmlspecialchars($section['title']) ?></div>
                            <?php $bullets = array_filter($section['content'], fn($line) => preg_match('/^[•\-*]\s*/u', $line)); ?>
                            <?php $paragraphs = array_filter($section['content'], fn($line) => !preg_match('/^[•\-*]\s*/u', $line)); ?>
                            <?php foreach ($paragraphs as $paragraph): ?><div class="memo-paragraph"><?= htmlspecialchars($paragraph) ?></div><?php endforeach; ?>
                            <?php if ($bullets): ?><ul class="memo-bullets"><?php foreach ($bullets as $bullet): ?><li><?= htmlspecialchars(preg_replace('/^[•\-*]\s*/u', '', $bullet)) ?></li><?php endforeach; ?></ul><?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
                <div class="closing"><?= nl2br(htmlspecialchars($closing)) ?></div>

                <?php if ($isMemorandum): ?>
                <div class="signature single" style="width: 55%; margin-left: auto;">
                    <div><?= htmlspecialchars($tempat) ?>, <?= htmlspecialchars(mm_date_text($tanggal)) ?></div>
                    <div>PT ADK6-SIGMA</div>
                    <br>
                    <br>
                </div>
                <table class="approval-grid">
                    <tr>
                        <td>
                            <div>Mengetahui,</div>
                            <table class="signature-grid"><tr>
                                <td><img class="sign-img" src="<?= BASE_URL ?>/public/img/ttd-hrd.png" alt="Tanda tangan HRD"><div class="sign-name">Amy Faradila</div><div>HR Officer</div></td>
                                <td><img class="sign-img" src="<?= BASE_URL ?>/public/img/ttd-coord.png" alt="Tanda tangan Crew Coordinator"><div class="sign-name">Eko Hendra</div><div>Crew Coord</div></td>
                                <td><img class="sign-img" src="<?= BASE_URL ?>/public/img/ttd-headHRD.png" alt="Tanda tangan Head HRD"><div class="sign-name">Yudi Putra</div><div>Head HRD</div></td>
                            </tr></table>
                        </td>
                        <td>
                            <div>Disetujui oleh,</div>
                            <table class="signature-grid"><tr>
                                <td><img class="sign-img" src="<?= BASE_URL ?>/public/img/ttd-areaManajer.png" alt="Tanda tangan Area Manager"><div class="sign-name">Riko Prima Jaya</div><div>AREA MANAGER</div></td>
                                <td><div class="sign-space"></div><div class="sign-name">Danang S / Zainal</div><div>Rig Supt GW-339</div></td>
                                <td><div class="sign-space"></div><div class="sign-name">Thomson / Bustami</div></td>
                            </tr></table>
                        </td>
                    </tr>
                </table>
                <?php endif; ?>
            </main>
        </div>

        <div class="action-buttons no-print">
            <button onclick="window.print()" class="btn btn-success">Cetak Surat</button>
            <button onclick="window.location.href='<?= BASE_URL ?>/surat'" class="btn btn-secondary">Tutup</button>
        </div>
    </div>
</body>
</html>
