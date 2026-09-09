<?php
$nomorSurat = $surat['nomor_surat'] ?? ($nomor_surat ?? '');
$isiSurat = $surat['isi_surat'] ?? ($isi_surat ?? '');
$tanggalMoc = $surat['tanggal_moc'] ?? ($tanggal_moc ?? date('Y-m-d'));

function mcu_pick_value($text, $labels, $default = '')
{
    foreach ((array) $labels as $label) {
        if (preg_match('/^' . preg_quote($label, '/') . '\s*:?\s*(.+)$/im', $text, $match)) {
            return trim($match[1]);
        }
    }
    return $default;
}

function mcu_format_date_id($date)
{
    if (!$date) return '';
    $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $timestamp = strtotime($date) ?: time();
    return date('j', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
}

$tglSurat = mcu_format_date_id($tanggalMoc);
$nama = mcu_pick_value($isiSurat, ['Nama', 'Name'], '…….');
$umur = mcu_pick_value($isiSurat, ['Umur', 'Age'], '………. Tahun');
$jenisKelamin = mcu_pick_value($isiSurat, ['Jenis Kelamin', 'Sex'], 'Laki-Laki');
$pekerjaan = mcu_pick_value($isiSurat, ['Pekerjaan', 'Job Title'], '……… 339');
$perusahaan = mcu_pick_value($isiSurat, ['Perusahaan', 'Company'], 'PT SIGMA / Greatwall Drilling Asia Pasific');
$alamat = mcu_pick_value($isiSurat, ['Alamat', 'Address'], '………………………….');
$bloodType = mcu_pick_value($isiSurat, ['Blood Type', 'Blood type'], '-');
$nik = mcu_pick_value($isiSurat, ['NIK', 'NIK ID Card'], '…………………………..');
$mcuRequirement = mcu_pick_value($isiSurat, ['MCU Requirement'], 'MCU PHR  (Pre-Employee)');
$dateMcu = mcu_pick_value($isiSurat, ['Date MCU'], mcu_format_date_id($tanggalMoc));
$locationMcu = mcu_pick_value($isiSurat, ['Location MCU'], 'RS Mutia Sari Duri');
$locationJob = mcu_pick_value($isiSurat, ['Location Job'], 'Field Worker / Pekerja Rig');
$rigLocation = mcu_pick_value($isiSurat, ['Rig Location'], 'Drilling GW ….');
$keluhan = mcu_pick_value($isiSurat, ['Keluhan', 'Complaint'], '-');
$pemeriksaan = mcu_pick_value($isiSurat, ['Pemeriksaan', 'Physical Examination'], '-');
$diagnosa = mcu_pick_value($isiSurat, ['Diagnosa', 'Working Diagnosis'], '-');
$pengobatan = mcu_pick_value($isiSurat, ['Pengobatan', 'Medicine'], '');
$signer = mcu_pick_value($isiSurat, ['Signer', 'Penandatangan'], 'Rifqi Haidi');
$signerTitle = mcu_pick_value($isiSurat, ['Signer Title', 'Jabatan Penandatangan'], 'HSE Coordinator PT GDAP');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>MCU - Cover Letter</title>
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
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 8mm 12mm 10mm;
            background: #fff;
            position: relative;
        }

        /* Header: judul di tengah, logo kanan atas, garis penuh di bawah */
        .letterhead {
            position: relative;
            min-height: 22mm;
            padding-bottom: 2mm;
            border-bottom: 2px solid #000;
            margin-bottom: 4mm;
        }
        .brand-logo {
            position: absolute;
            top: 0;
            right: 0;
            width: 58mm;
            max-width: 100%;
            height: auto;
            object-fit: contain;
        }
        .title {
            text-align: center;
            font-size: 14pt;
            font-weight: 700;
            text-decoration: underline;
            margin: 1mm 0 0.5mm;
        }
        .cover-label {
            text-align: center;
            font-size: 11pt;
            font-weight: 400;
            margin-bottom: 1mm;
        }

        .body-text p,
        .body-text div {
            margin: 0 0 0.5mm;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3mm 0;
            font-size: 10.5pt;
        }
        .data-table td {
            padding: 1.4mm 3mm;
            vertical-align: top;
            border: 1px solid #000;
            font-weight: 400;
        }
        .data-table .label-cell {
            width: 22%;
        }
        .data-table .value-cell {
            width: 28%;
        }
        .data-table .label-cell-full {
            width: 22%;
        }
        .data-table .bold-value {
            font-weight: 700;
        }

        .complaint-block {
            margin: 3mm 0;
        }
        .complaint-label-row {
            display: flex;
            justify-content: space-between;
            font-size: 10.5pt;
            margin-bottom: 1mm;
        }
        .complaint-label-col {
            width: 49%;
        }
        .complaint-label-full {
            font-size: 10.5pt;
            margin-bottom: 1mm;
        }
        .value-box-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5pt;
        }
        .value-box-table td {
            border: 1px solid #000;
            padding: 1.4mm 3mm;
            vertical-align: top;
            width: 50%;
        }
        .value-box-table.tall td {
            height: 12mm;
        }
        .value-box-table.short td {
            height: 6mm;
        }

        .signature {
            margin-top: 6mm;
        }
        .sign-space {
            height: 10mm;
        }
        .sign-name {
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
            font-family: Arial, Helvetica, sans-serif;
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
                padding: 8mm 12mm 10mm;
            }
            .brand-logo {
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
            <img class="brand-logo" src="<?= BASE_URL ?>/public/img/kop-gwdc.jpg" alt="Logo GWDC">
            <div class="title">SURAT PENGANTAR</div>
            <div class="cover-label">COVER LETTER</div>
        </header>

        <div class="body-text">
            <div>Tanggal / Date : <?= htmlspecialchars($tglSurat) ?></div>
            <div>Kepada / to</div>
            <div>Yth. <?= htmlspecialchars($locationMcu) ?></div>
            <div>Jl. Bathin Betuah No. 1 A , jln. Kebun Karet, Duri, Riau</div>
            <p style="margin-top:1.5mm;">Bersama dengan ini kami kirimkan seorang karyawan kami/Herewith we send our staff with :</p>
        </div>

        <table class="data-table">
            <tr>
                <td class="label-cell">Nama / Name</td>
                <td class="value-cell"><?= htmlspecialchars($nama) ?></td>
                <td class="label-cell">Umur / Age</td>
                <td class="value-cell"><?= htmlspecialchars($umur) ?></td>
            </tr>
            <tr>
                <td class="label-cell">Jenis Kelamin / Sex</td>
                <td class="value-cell"><?= htmlspecialchars($jenisKelamin) ?></td>
                <td class="label-cell">Pekerjaan / Job Title</td>
                <td class="value-cell"><?= htmlspecialchars($pekerjaan) ?></td>
            </tr>
            <tr>
                <td class="label-cell-full">Perusahaan / Company</td>
                <td colspan="3"><?= htmlspecialchars($perusahaan) ?></td>
            </tr>
            <tr>
                <td class="label-cell-full">Alamat / Address</td>
                <td colspan="3"><?= htmlspecialchars($alamat) ?></td>
            </tr>
            <tr>
                <td class="label-cell-full">Blood type</td>
                <td colspan="3"><?= htmlspecialchars($bloodType) ?></td>
            </tr>
            <tr>
                <td class="label-cell-full">NIK ID Card</td>
                <td colspan="3"><?= htmlspecialchars($nik) ?></td>
            </tr>
            <tr>
                <td class="label-cell-full">MCU Requirement</td>
                <td colspan="3"><?= htmlspecialchars($mcuRequirement) ?></td>
            </tr>
            <tr>
                <td class="label-cell-full">Date MCU</td>
                <td colspan="3" class="bold-value"><?= htmlspecialchars($dateMcu) ?></td>
            </tr>
            <tr>
                <td class="label-cell-full">Location MCU</td>
                <td colspan="3"><?= htmlspecialchars($locationMcu) ?></td>
            </tr>
            <tr>
                <td class="label-cell-full">Location Job</td>
                <td colspan="3"><?= htmlspecialchars($locationJob) ?></td>
            </tr>
            <tr>
                <td class="label-cell-full">Rig Location</td>
                <td colspan="3"><?= htmlspecialchars($rigLocation) ?></td>
            </tr>
        </table>

        <div class="complaint-block">
            <div class="complaint-label-row">
                <div class="complaint-label-col">Keluhan /<br>Complaint :</div>
                <div class="complaint-label-col">Pemeriksaan Fisik atau Penunjang /<br>Physical or Supporting Examination :</div>
            </div>
            <table class="value-box-table tall">
                <tr>
                    <td><?= nl2br(htmlspecialchars($keluhan)) ?></td>
                    <td><?= nl2br(htmlspecialchars($pemeriksaan)) ?></td>
                </tr>
            </table>
        </div>

        <div class="complaint-block">
            <div class="complaint-label-full">Diagnosa Sementara / Working Diagnosis :</div>
            <table class="value-box-table short">
                <tr>
                    <td colspan="2"><?= nl2br(htmlspecialchars($diagnosa)) ?></td>
                </tr>
            </table>
        </div>

        <div class="complaint-block">
            <div class="complaint-label-full">Pengobatan & Tindakan Sementara / Medicine & Supportive Treatment :</div>
            <table class="value-box-table tall">
                <tr>
                    <td colspan="2"><?= nl2br(htmlspecialchars($pengobatan)) ?></td>
                </tr>
            </table>
        </div>

        <div class="body-text">
            <p style="margin-top:2mm;">Mohon dilakukan pemeriksaan berikut sebagai Standard Pemeriksaan MCU PHR /Please perform the following checks as a Standard MCU PHR:</p>
            <p style="margin-left:5mm;">1.&nbsp;&nbsp;&nbsp;MCU requirement will attach</p>
            <p style="margin-top:2mm;">Kami ucapkan banyak terima kasih atas bantuannya / Thank you very much for your good cooperation.</p>
        </div>

        <div class="signature">
            <div>Hormat / Regards,</div>
            <div class="sign-space"></div>
            <div class="sign-name"><?= htmlspecialchars($signer) ?></div>
            <div><?= htmlspecialchars($signerTitle) ?></div>
        </div>

        <div class="action-buttons no-print">
            <button onclick="downloadPDF()" class="btn btn-success"><i class="fas fa-file-pdf"></i> Download PDF</button>
            <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print"></i> Cetak</button>
            <button onclick="window.location.href='<?= BASE_URL ?>/surat'" class="btn btn-secondary">Tutup</button>
        </div>
        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
        <script>
        function downloadPDF() {
            var element = document.querySelector('.page');
            // Sembunyikan tombol aksi saat generate PDF
            var buttons = document.querySelector('.action-buttons');
            if (buttons) buttons.style.display = 'none';

            var namaFile = '<?= preg_replace("/[^a-zA-Z0-9_-]/", "_", $nomorSurat ?: "MCU_Cover_Letter") ?>';

            var opt = {
                margin:       0,
                filename:     namaFile + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  {
                    scale: 2,
                    useCORS: true,
                    letterRendering: true,
                    logging: false
                },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
            };

            html2pdf().set(opt).from(element).save().then(function() {
                // Tampilkan kembali tombol setelah selesai
                if (buttons) buttons.style.display = '';
            }).catch(function() {
                if (buttons) buttons.style.display = '';
                alert('Gagal membuat PDF. Silakan coba lagi.');
            });
        }
        </script>
    </div>
</body>
</html>