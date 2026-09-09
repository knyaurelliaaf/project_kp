<?php
function rp($v) {
    $v = (float) $v;
    return $v == 0 ? '-' : number_format($v, 0, ',', '.');
}
function fmtRp($v) {
    return '<div class="nilai-box"><span>Rp</span><span>' . rp($v) . '</span></div>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SLIP GAJI <?= htmlspecialchars($slip['nama']) ?> - <?= htmlspecialchars($slip['jabatan']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 portrait; margin: 1.2cm; }
        body {
            font-family: Calibri, Arial, sans-serif;
            font-size: 10pt;
            color: #000;
            background: #fff;
        }
        .page {
            width: 19cm;
            margin: 0 auto;
            border: 2px solid #000;
        }

        /* KOP */
        .kop { display: flex; border-bottom: 3px double #000; }
        .kop-kiri { width: 62%; overflow: hidden; }
        .kop-kiri img { width: 100%; height: auto; display: block; }
        .kop-kanan { width: 38%; border-left: 2px solid #000; text-align: center; padding: 12px 8px; }
        .kop-kanan .judul { font-weight: 700; font-size: 15pt; letter-spacing: 2px; }
        .kop-kanan .periode { font-style: italic; font-size: 10pt; margin-top: 4px; }

        /* INFO karyawan */
        .info { display: flex; border-bottom: 3px double #000; }
        .info-kiri { width: 55%; padding: 6px 12px; }
        .info-kiri table { width: 100%; border-collapse: collapse; }
        .info-kiri td { padding: 1px 4px; font-size: 10pt; vertical-align: top; }
        .info-kiri td.lbl { width: 50px; font-weight: 700; }
        .info-kiri td.colon { width: 10px; }

        .info-kanan { width: 45%; padding: 6px 12px; }
        .info-kanan table { width: 100%; border-collapse: collapse; }
        .info-kanan td { padding: 1px 4px; font-size: 10pt; vertical-align: top; }
        .info-kanan td.lbl { font-weight: 700; padding-right: 4px; }
        .info-kanan td.val { text-align: right; padding-right: 4px; }
        .info-kanan td.sat { padding-left: 0; }

        /* RINCIAN - tanpa garis vertikal pemisah */
        .rincian { display: flex; }
        .rincian-kiri { width: 55%; }
        .rincian-kanan { width: 45%; }
        .section-title { font-weight: 700; padding: 4px 12px; letter-spacing: 1px; }
        .rincian table { width: 100%; border-collapse: collapse; }
        .rincian td { padding: 2px 12px; font-size: 10pt; vertical-align: top; }
        .rincian td.item { width: 60%; }
        .rincian td.nilai { width: 40%; }
        .nilai-box { display: flex; justify-content: space-between; align-items: center; width: 100%; }
        .rincian .sub-title td { font-weight: 700; padding-top: 6px; padding-bottom: 2px; }

        /* TOTAL - dalam satu baris */
        .total-row {
            display: flex;
            border-top: 3px double #000;
            border-bottom: 3px double #000;
            font-weight: 700;
            font-size: 10pt;
        }
        .total-row .tr-kiri { width: 55%; padding: 4px 12px; display: flex; justify-content: space-between; align-items: center; border-right: 2px solid #000; }
        .total-row .tr-kanan { width: 45%; padding: 4px 12px; display: flex; justify-content: space-between; align-items: center; }

        /* KOREKSI */
        .koreksi {
            display: flex; justify-content: space-between; align-items: center; padding: 4px 12px;
            border-bottom: 3px double #000;
            font-weight: 700; font-size: 10pt;
        }

        /* GAJI BERSIH */
        .gaji-bersih {
            display: flex; justify-content: space-between; align-items: center; padding: 6px 12px;
            border-bottom: 3px double #000;
            font-weight: 700; font-size: 12pt;
        }

        /* FOOTER */
        .footer { display: flex; min-height: 90px; border-top: 1px solid #000; }
        .ttd-box { width: 30%; border-right: 2px solid #000; text-align: center; padding: 10px; display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 20px; }
        .ttd-box .lbl { font-weight: 700; font-size: 10pt; }
        .ttd-box .nama { font-weight: 700; text-decoration: underline; margin-top: 4px; font-size: 10pt; }
        .ttd-box .jab { font-weight: 700; font-size: 10pt; }
        .note-box { width: 70%; padding: 10px 12px; }
        .note-box .lbl { font-weight: 700; font-size: 10pt; margin-bottom: 4px; }
        .note-box .item { font-style: italic; font-size: 10pt; }

        .no-print { margin-top: 20px; text-align: center; }
        .no-print button { padding: 10px 25px; border: none; border-radius: 8px; font-size: 12pt; cursor: pointer; background: #E8789A; color: #fff; }

        @media print {
            .no-print { display: none; }
            body { background: none; padding: 1.2cm; }
            .page { width: 100%; border: 2px solid #000; margin: 0; }
            @page { margin: 0; }
        }
    </style>
</head>
<body>
<div class="page">

    <!-- KOP -->
    <div class="kop">
        <div class="kop-kiri"><img src="<?= BASE_URL ?>/public/img/kop-surat.png" alt="Kop"></div>
        <div class="kop-kanan">
            <div class="judul">SLIP GAJI</div>
            <div class="periode"><?= htmlspecialchars($slip['periode']) ?></div>
        </div>
    </div>

    <!-- INFO -->
    <div class="info">
        <div class="info-kiri">
            <table>
                <tr><td class="lbl">Nama</td><td class="colon">:</td><td><?= htmlspecialchars($slip['nama']) ?></td></tr>
                <tr><td class="lbl">Badge</td><td class="colon">:</td><td><?= htmlspecialchars($slip['badge']) ?></td></tr>
                <tr><td class="lbl">Jabatan</td><td class="colon">:</td><td><?= htmlspecialchars($slip['jabatan']) ?></td></tr>
            </table>
        </div>
        <div class="info-kanan">
            <table>
                <tr><td class="lbl">HARI KERJA</td><td>:</td><td class="val"><?= (int)$slip['hari_kerja'] ?></td><td class="sat">HARI</td></tr>
                <tr><td class="lbl">SAKIT</td><td>:</td><td class="val"><?= $slip['sakit'] > 0 ? (int)$slip['sakit'] : '-' ?></td><td class="sat">HARI</td></tr>
                <tr><td class="lbl">IZIN</td><td>:</td><td class="val"><?= $slip['izin'] > 0 ? (int)$slip['izin'] : '-' ?></td><td class="sat">HARI</td></tr>
                <tr><td class="lbl">ALPHA</td><td>:</td><td class="val"><?= $slip['alpha'] > 0 ? (int)$slip['alpha'] : '-' ?></td><td class="sat">HARI</td></tr>
                <tr><td class="lbl">CUTI</td><td>:</td><td class="val"><?= $slip['cuti'] > 0 ? (int)$slip['cuti'] : '-' ?></td><td class="sat">HARI</td></tr>
                <tr><td class="lbl">OVER TIME</td><td>:</td><td class="val"><?= $slip['over_time'] > 0 ? (int)$slip['over_time'] : '-' ?></td><td class="sat">JAM</td></tr>
            </table>
        </div>
    </div>

    <?php $hasRincian = array_key_exists('gaji_pokok', $slip); ?>

    <!-- RINCIAN PENDAPATAN & POTONGAN -->
    <?php if ($hasRincian): ?>
    <div class="rincian">
        <div class="rincian-kiri">
            <div class="section-title">PENDAPATAN</div>
            <table>
                <tr><td class="item" style="font-weight:700;">GAJI POKOK</td><td class="nilai"><?= fmtRp($slip['gaji_pokok']) ?></td></tr>
                <tr><td class="item">Tunjangan Jabatan</td><td class="nilai"><?= fmtRp($slip['tunjangan_jabatan']) ?></td></tr>
                <tr class="sub-title"><td colspan="2">TUNJANGAN TIDAK TETAP</td></tr>
                <tr><td class="item">Reguler Over Time ( ROT )</td><td class="nilai"><?= fmtRp($slip['rot']) ?></td></tr>
                <tr><td class="item">Tunjangan Transport</td><td class="nilai"><?= fmtRp($slip['tunjangan_transport']) ?></td></tr>
                <tr><td class="item">Tunjangan Perumahan</td><td class="nilai"><?= fmtRp($slip['tunjangan_perumahan']) ?></td></tr>
                <tr><td class="item">Uang Makan</td><td class="nilai"><?= fmtRp($slip['uang_makan']) ?></td></tr>
                <tr class="sub-title"><td colspan="2">OVER TIME</td></tr>
                <tr><td class="item">Total Over Time</td><td class="nilai"><?= fmtRp($slip['total_over_time']) ?></td></tr>
                <tr><td class="item">Uang Makan Over Time</td><td class="nilai"><?= fmtRp($slip['uang_makan_ot']) ?></td></tr>
            </table>
        </div>
        <div class="rincian-kanan">
            <div class="section-title">POTONGAN</div>
            <table>
                <tr><td class="item">PPh 21</td><td class="nilai"><?= fmtRp($slip['pph21']) ?></td></tr>
                <tr><td class="item">BPJS Ketenagakerjaan ( JHT ) 2%</td><td class="nilai"><?= fmtRp($slip['bpjs_jht']) ?></td></tr>
                <tr><td class="item">BPJS Pensiun 1%</td><td class="nilai"><?= fmtRp($slip['bpjs_pensiun']) ?></td></tr>
                <tr><td class="item">BPJS Kesehatan 1%</td><td class="nilai"><?= fmtRp($slip['bpjs_kesehatan']) ?></td></tr>
                <tr><td class="item">Potongan Sertifikasi</td><td class="nilai"><?= fmtRp($slip['potongan_sertifikasi']) ?></td></tr>
                <tr><td class="item">Potongan Pinjaman</td><td class="nilai"><?= fmtRp($slip['potongan_pinjaman']) ?></td></tr>
                <tr><td class="item">Potongan Alpha</td><td class="nilai"><?= fmtRp($slip['potongan_alpha']) ?></td></tr>
            </table>
        </div>
    </div>

    <!-- TOTAL -->
    <div class="total-row">
        <div class="tr-kiri">
            <span>TOTAL PENGHASILAN</span>
            <div style="width: 140px;"><?= fmtRp($slip['total_penghasilan']) ?></div>
        </div>
        <div class="tr-kanan">
            <span>TOTAL POTONGAN</span>
            <div style="width: 140px;"><?= fmtRp($slip['total_potongan']) ?></div>
        </div>
    </div>

    <!-- KOREKSI -->
    <div class="koreksi">
        <span>KOREKSI KESALAHAN BULAN LALU</span>
        <div style="width: 140px;"><?= fmtRp($slip['koreksi_bulan_lalu']) ?></div>
    </div>
    <?php else: ?>
    <div class="rincian">
        <div class="rincian-kiri" style="width:100%; padding: 4px 12px 10px;">
            <div class="section-title" style="padding-left:0;">RINCIAN PENDAPATAN &amp; POTONGAN</div>
            <div>Rincian belum tersedia pada data impor periode ini. Total gaji bersih di bawah tetap menggunakan nilai yang tersimpan.</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- GAJI BERSIH -->
    <div class="gaji-bersih">
        <span>GAJI BERSIH</span>
        <div style="width: 160px;"><?= fmtRp($slip['gaji_bersih']) ?></div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <div class="ttd-box">
            <div class="lbl">TTD</div>
            <div>
                <div class="nama"><?= htmlspecialchars($slip['nama']) ?></div>
                <div class="jab"><?= htmlspecialchars($slip['jabatan']) ?></div>
            </div>
        </div>
        <div class="note-box">
            <div class="lbl">NOTE :</div>
            <div class="item">1. SEGERA LAPORKAN JIKA ADA KEKURANGAN / KELEBIHAN GAJI</div>
        </div>
    </div>

</div>

<div class="no-print">
    <button onclick="cetakPDF()"> Cetak / Simpan sebagai PDF</button>
</div>
<script>
function cetakPDF() {
    const nama    = <?= json_encode($slip['nama']) ?>;
    const jabatan = <?= json_encode($slip['jabatan']) ?>;
    const judulAsli = document.title;
    document.title = 'SLIP GAJI ' + nama + ' - ' + jabatan;
    window.print();
    setTimeout(() => { document.title = judulAsli; }, 1000);
}
</script>
</body>
</html>
