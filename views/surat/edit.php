<?php
$isiSurat = $surat['isi_surat'] ?? '';
$isSpkSurat = ($surat['kode_jenis'] ?? '') === 'SPK';

$scPositionOptions = [];
$posisiModel = $this->model('MasterPosisiModel');
$posisiList = $posisiModel->all();
while ($p = $posisiList->fetch_assoc()) {
    if (!empty($p['nama_posisi'])) {
        $scPositionOptions[] = $p['nama_posisi'];
    }
}

$scRigOptions = [];
$rigModelForSc = $this->model('RigModel');
$rigListForSc = $rigModelForSc->allActive();
while ($r = $rigListForSc->fetch_assoc()) {
    if (!empty($r['kode_rig'])) {
        $scRigOptions[] = $r['kode_rig'];
    }
}

$spkNama = '';
$spkJabatan = '';
$spkBadge = '';
$spkMengganti = '';
$spkCrew = '';
$spkRig = $surat['kode_rig'] ?? '';

$stName = '';
$stDepartment = '';
$stCrew = '';
$stStatus = '';
$stPosition = '';
$stDateEntry = '';
$stDateEval = '';
$stEvalMonths = '';
$stCompany = '';
$stRequestBy = '';
$stRequestTitle = '';
$stApprovedBy = '';
$stApprovedTitle = '';
$stIssueId = '';
$stIssueEn = '';

function edit_pick_value($text, $labels, $default = '')
{
    foreach ((array) $labels as $label) {
        if (preg_match('/^' . preg_quote($label, '/') . '\s*:?\s*(.+)$/im', $text, $match)) {
            return trim($match[1]);
        }
    }

    return $default;
}

function edit_parse_date_id($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    if (strtotime($value)) {
        return date('Y-m-d', strtotime($value));
    }

    $months = [
        'januari' => '01',
        'februari' => '02',
        'maret' => '03',
        'april' => '04',
        'mei' => '05',
        'juni' => '06',
        'juli' => '07',
        'agustus' => '08',
        'september' => '09',
        'oktober' => '10',
        'november' => '11',
        'desember' => '12',
    ];

    if (preg_match('/(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/i', $value, $match)) {
        $month = $months[strtolower($match[2])] ?? '';
        if ($month !== '') {
            return $match[3] . '-' . $month . '-' . str_pad($match[1], 2, '0', STR_PAD_LEFT);
        }
    }

    return '';
}

$scTujuan = edit_pick_value($isiSurat, ['Kepada', 'Tujuan'], $surat['tujuan'] ?? 'All Crew');
$scPositions = edit_pick_value($isiSurat, ['Positions', 'Position', 'Posisi'], '');
$scRigs = edit_pick_value($isiSurat, ['Rigs', 'Rig'], '');
$scPurpose = edit_pick_value($isiSurat, ['Purpose', 'Maksud Panggilan'], 'menandatangani ulang perjanjian kontrak kerja (Revisi PKWT yang lama), karena terdapat perubahan pada struktur gaji baru dan hal lainnya');
$scCallDate = edit_parse_date_id(edit_pick_value($isiSurat, ['Call Date', 'Tanggal Panggilan'], ''));
$scCallTimeRaw = edit_pick_value($isiSurat, ['Call Time', 'Jam'], '10.00 Wib');
$scCallTime = preg_match('/(\d{1,2})[\.:](\d{2})/', $scCallTimeRaw, $match) ? str_pad($match[1], 2, '0', STR_PAD_LEFT) . ':' . $match[2] : '10:00';
$scLetterDate = edit_parse_date_id(edit_pick_value($isiSurat, ['Letter Date', 'Tanggal Surat'], $surat['tanggal_moc'] ?? ''));
$scPlace = edit_pick_value($isiSurat, ['Place', 'Tempat Surat'], 'Duri');

$skaStatementName = edit_pick_value($isiSurat, ['Nama Penandatangan', 'Nama HRD'], 'Amy Faradila');
$skaStatementTitle = edit_pick_value($isiSurat, ['Jabatan Penandatangan', 'Jabatan HRD'], 'HRD Department');
$skaStatementAddress = edit_pick_value($isiSurat, ['Alamat Penandatangan', 'Alamat HRD'], 'Jl. Karang Anyer II, Kec Mandau');
$skaEmployeeName = edit_pick_value($isiSurat, ['Nama Karyawan', 'Nama Crew'], '');
$skaEmployeeTitle = edit_pick_value($isiSurat, ['Jabatan Karyawan', 'Jabatan Crew', 'Posisi'], '');
$skaEmployeeAddress = edit_pick_value($isiSurat, ['Alamat Karyawan', 'Alamat Crew'], '');
$skaCompany = edit_pick_value($isiSurat, ['Perusahaan', 'Company'], 'PT Greatwall Drilling Company');
$skaStartDate = edit_parse_date_id(edit_pick_value($isiSurat, ['Tanggal Mulai', 'Mulai Bekerja', 'TMT'], ''));
$skaPlace = edit_pick_value($isiSurat, ['Tempat Surat', 'Tempat'], 'Duri');
$skaLetterDate = edit_parse_date_id(edit_pick_value($isiSurat, ['Tanggal Surat'], $surat['tanggal_moc'] ?? ''));

$spLevel = edit_pick_value($isiSurat, ['Tingkat Peringatan'], 'Pertama');
$spEmployeeName = edit_pick_value($isiSurat, ['Nama Karyawan', 'Nama Crew'], '');
$spEmployeeTitle = edit_pick_value($isiSurat, ['Jabatan Karyawan', 'Jabatan Crew', 'Jabatan', 'Posisi'], '');
$spBadge = edit_pick_value($isiSurat, ['No Badge', 'Nomor Badge'], '');
$spRig = edit_pick_value($isiSurat, ['Rig'], $surat['kode_rig'] ?? '');
$spCrew = edit_pick_value($isiSurat, ['Crew'], '');
$spViolationTitle = edit_pick_value($isiSurat, ['Judul Pelanggaran'], 'TENTANG KEDISIPLINAN, MELANGGAR ATURAN PERUSAHAAN, ETIKA KINERJA YANG SIGNIFIKAN');
$spViolationDetail = edit_pick_value($isiSurat, ['Detail Pelanggaran', 'Pelanggaran'], 'Sehubungan dengan terjadinya pelanggaran yang dilakukan karyawan berupa temuan finding cctv dilokasi.');
$spDisciplineAction = edit_pick_value($isiSurat, ['Tindakan Disiplin'], 'Maka dari itu perusahaan melakukan tindakan disiplin Coaching Diyard selama 3 hari dan menonaktifkan absensi kehadiran/Alfa serta memotong gaji karyawan yang melanggar aturan.');
$spEffectiveStart = edit_parse_date_id(edit_pick_value($isiSurat, ['Masa Berlaku Mulai'], $surat['tanggal_moc'] ?? ''));
$spEffectiveEnd = edit_parse_date_id(edit_pick_value($isiSurat, ['Masa Berlaku Sampai'], ''));
$spPlace = edit_pick_value($isiSurat, ['Tempat Surat', 'Tempat'], 'Duri');
$spLetterDate = edit_parse_date_id(edit_pick_value($isiSurat, ['Tanggal Surat'], $surat['tanggal_moc'] ?? ''));
$spSigner = edit_pick_value($isiSurat, ['Signer', 'Penandatangan'], 'Amy Faradila');
$spSignerTitle = edit_pick_value($isiSurat, ['Signer Title', 'Jabatan Penandatangan'], 'HRD Dept');

$sptTujuan = edit_pick_value($isiSurat, ['Kepada', 'Tujuan'], $surat['tujuan'] ?? 'Crew GWAP 339');
$sptPerihal = edit_pick_value($isiSurat, ['Perihal'], $surat['perihal'] ?? 'Pemberitahuan');
$sptLampiran = edit_pick_value($isiSurat, ['Lampiran'], '1');
$sptParagraph1 = edit_pick_value($isiSurat, ['Paragraf 1'], 'Diberitahukan kepada seluruh karyawan GWAP339 yang sudah menanda tangani PKWT diwajibkan hadir bekerja sesuai dengan schedule yang sudah diterbitkan oleh management, bagi yang berhalangan sakit agar dapat melampirkan bukti surat sakit dari klinik maupun tempat berobat.');
$sptParagraph2 = edit_pick_value($isiSurat, ['Paragraf 2'], 'Untuk Rig Admin masing-masing Crew agar melaporkan setiap kekurangan crew yang ada dilapangan kepada Tool Pusher, RSM dan Rig Superintendent yang bertugas.');
$sptParagraph3 = edit_pick_value($isiSurat, ['Paragraf 3'], 'Bagi karyawan yang masih proses CCPM agar tetap hadir dilapangan sesuai dengan schedule kerja masing-masing, tidak ada alasan lagi untuk tidak hadir karena kita sudah memberikan toleransi waktu untuk menyelesaikan schedule kerja diperusahaan lama.');
$sptClosing = edit_pick_value($isiSurat, ['Penutup'], 'Demikian kami sampaikan atas perhatian dan kerjasamanya kami ucapkan terimakasih.');
$sptPlace = edit_pick_value($isiSurat, ['Tempat Surat', 'Tempat'], 'Duri');
$sptLetterDate = edit_parse_date_id(edit_pick_value($isiSurat, ['Tanggal Surat'], $surat['tanggal_moc'] ?? ''));
$sptSigner = edit_pick_value($isiSurat, ['Signer', 'Penandatangan'], 'Fery Chandra');
$sptSignerTitle = edit_pick_value($isiSurat, ['Signer Title', 'Jabatan Penandatangan'], 'Project Manager');

$srTujuan = edit_pick_value($isiSurat, ['Kepada', 'Tujuan'], $surat['tujuan'] ?? 'All Crew');
$srPositions = edit_pick_value($isiSurat, ['Posisi', 'Position'], '');
$srRigs = edit_pick_value($isiSurat, ['Rigs', 'Rig'], '');
$srLetterDate = edit_parse_date_id(edit_pick_value($isiSurat, ['Tanggal Surat'], $surat['tanggal_moc'] ?? ''));
$srPlace = edit_pick_value($isiSurat, ['Tempat Surat', 'Tempat'], 'Duri');
$srLampiran = edit_pick_value($isiSurat, ['Lampiran'], '1');
$srSigner = edit_pick_value($isiSurat, ['Signer', 'Penandatangan'], 'Amy Faradila');
$srPerihal = edit_pick_value($isiSurat, ['Perihal'], $surat['perihal'] ?? 'Ketentuan Pengunduran Diri (Resign) dan Penalty Kontrak');

$skkTujuan = edit_pick_value($isiSurat, ['Kepada', 'Tujuan'], $surat['tujuan'] ?? 'Crew terkait');
$skkPositions = edit_pick_value($isiSurat, ['Posisi', 'Position'], '');
$skkRigs = edit_pick_value($isiSurat, ['Rigs', 'Rig'], '');
$skkName = edit_pick_value($isiSurat, ['Nama'], '');
$skkPosition = edit_pick_value($isiSurat, ['Jabatan'], '');
$skkProject = edit_pick_value($isiSurat, ['Project', 'Proyek'], 'Drilling at PT. Greatwall Drilling Asia Pacific');
$skkStartDate = edit_parse_date_id(edit_pick_value($isiSurat, ['Mulai Bekerja', 'Start Date'], ''));
$skkLetterDate = edit_parse_date_id(edit_pick_value($isiSurat, ['Tanggal Surat'], $surat['tanggal_moc'] ?? ''));
$skkPlace = edit_pick_value($isiSurat, ['Tempat Surat', 'Tempat'], 'Duri');

$baTujuan = edit_pick_value($isiSurat, ['Kepada', 'Tujuan'], $surat['tujuan'] ?? 'Crew terkait');
$baPositions = edit_pick_value($isiSurat, ['Posisi', 'Position'], '');
$baRigs = edit_pick_value($isiSurat, ['Rigs', 'Rig'], '');
$baName = edit_pick_value($isiSurat, ['Nama'], '');
$baPosition = edit_pick_value($isiSurat, ['Jabatan', 'Posisi'], '');
$baRig = edit_pick_value($isiSurat, ['Rig'], $surat['kode_rig'] ?? '');
$baContract = edit_pick_value($isiSurat, ['No Kontrak', 'Nomor Kontrak'], 'SPHR00618A');
$baStartOjt = edit_parse_date_id(edit_pick_value($isiSurat, ['Start OJT', 'OJT Start'], ''));
$baEndOjt = edit_parse_date_id(edit_pick_value($isiSurat, ['End OJT', 'OJT End'], ''));
$baAssessmentDate = edit_parse_date_id(edit_pick_value($isiSurat, ['Tanggal Assessment', 'Assessment Date'], ''));
$baResult = edit_pick_value($isiSurat, ['Hasil', 'Result'], 'Passed');
$baPlace = edit_pick_value($isiSurat, ['Tempat', 'Tempat Surat'], 'Tanggul');
$baLetterDate = edit_parse_date_id(edit_pick_value($isiSurat, ['Tanggal Surat'], $surat['tanggal_moc'] ?? ''));
$baSignerDsr = edit_pick_value($isiSurat, ['Signer DSR', 'Signer', 'Penandatangan'], '');
$baSignerRsm = edit_pick_value($isiSurat, ['Signer RSM'], '');
$baSignerHse = edit_pick_value($isiSurat, ['Signer HSE'], '');

$sjDate = edit_parse_date_id(edit_pick_value($isiSurat, ['Tanggal Perjanjian'], $surat['tanggal_moc'] ?? ''));
$sjP1Name = edit_pick_value($isiSurat, ['Pihak Pertama Nama'], '');
$sjP1Birth = edit_pick_value($isiSurat, ['Pihak Pertama TTL'], '');
$sjP1Address = edit_pick_value($isiSurat, ['Pihak Pertama Alamat'], '');
$sjP1Domicile = edit_pick_value($isiSurat, ['Pihak Pertama Domisili'], '');
$sjP1Position = edit_pick_value($isiSurat, ['Pihak Pertama Jabatan'], '');
$sjCertName = edit_pick_value($isiSurat, ['Nama Sertifikat'], 'Asme Welder');
$sjFee = edit_pick_value($isiSurat, ['Biaya Sertifikat'], 'Rp. 12.000.000');
$sjDeduction = edit_pick_value($isiSurat, ['Potongan Perbulan'], 'Rp. 2.000.000');
$sjWorkPeriod = edit_pick_value($isiSurat, ['Ikatan Kerja'], '1 (satu) bulan');

function edit_pick_block($text, $label, $default = '')
{
    if (preg_match('/^' . preg_quote($label, '/') . '\s*:?\s*([\s\S]*?)(?:\n[A-Za-z][A-Za-z ]{1,35}\s*:|$)/im', $text, $match)) {
        return trim($match[1]);
    }
    return $default;
}

$mmModel = edit_pick_value($isiSurat, ['Model Memo'], 'Memo Internal');
$mmPerihal = edit_pick_value($isiSurat, ['Perihal'], $surat['perihal'] ?? 'Perihal PKWT 2026-2027 Sigma Drilling-GWDC');
$mmLampiran = edit_pick_value($isiSurat, ['Lampiran'], '-');
$mmTujuan = edit_pick_value($isiSurat, ['Kepada', 'Tujuan'], $surat['tujuan'] ?? 'Seluruh Karyawan');
$mmPositions = edit_pick_value($isiSurat, ['Positions', 'Position', 'Posisi'], '');
$mmPositionSelections = array_filter(array_map('trim', explode(',', $mmPositions)));
$mmRigs = edit_pick_value($isiSurat, ['Rigs', 'Rig'], '');
$mmLetterDate = edit_parse_date_id(edit_pick_value($isiSurat, ['Tanggal Surat'], $surat['tanggal_moc'] ?? ''));
$mmPlace = edit_pick_value($isiSurat, ['Tempat Surat', 'Tempat'], 'Duri');
$mmIntro = edit_pick_block($isiSurat, 'Isi Pembuka', 'Sehubungan dengan periode PKWT tahun 2026-2027, bersama ini disampaikan informasi dan ketentuan yang perlu diperhatikan oleh seluruh karyawan terkait proses administrasi kontrak kerja.');
$mmPoints = edit_pick_block($isiSurat, 'Poin Memo', "Karyawan wajib melengkapi dokumen pendukung PKWT sesuai jadwal yang ditentukan.\nSetiap perubahan data pribadi, posisi, atau penempatan rig wajib diinformasikan kepada HRD.\nKaryawan tetap wajib menjalankan tugas dan tanggung jawab selama proses administrasi berjalan.");
$mmFollowup = edit_pick_block($isiSurat, 'Isi Lanjutan', '');
$mmClosing = edit_pick_block($isiSurat, 'Penutup', 'Demikian memo ini disampaikan untuk dipahami dan dilaksanakan sebagaimana mestinya.');
$mmSignerHrd = edit_pick_value($isiSurat, ['Signer HRD'], 'Amy Faradila');
$mmSignerHrdTitle = edit_pick_value($isiSurat, ['Signer HRD Title'], 'HR Department');
$mmSignerRig = edit_pick_value($isiSurat, ['Signer Rig'], '');
$mmSignerRigTitle = edit_pick_value($isiSurat, ['Signer Rig Title'], 'Rig Superintendent');
$mmSignerManager = edit_pick_value($isiSurat, ['Signer Manager'], 'Area Manager');
$mmSignerManagerTitle = edit_pick_value($isiSurat, ['Signer Manager Title'], 'Area Manager');

if (preg_match('/Nama\s*:?\s*([^\r\n]+)/i', $isiSurat, $match)) {
    $spkNama = trim($match[1]);
}
if (preg_match('/Jabatan\s*:?\s*([^\r\n]+)/i', $isiSurat, $match)) {
    $spkJabatan = trim($match[1]);
}
if (preg_match('/No\s*Badge\s*:?\s*([^\r\n]+)/i', $isiSurat, $match)) {
    $spkBadge = trim($match[1]);
}
if (preg_match('/menggantikan posisi\s+(.+?)\s*\./i', $isiSurat, $match)) {
    $spkMengganti = trim($match[1]);
}
if (preg_match('/\bcrew\s+([A-Z0-9]+)\b/i', $isiSurat, $match)) {
    $spkCrew = strtoupper(trim($match[1]));
}

if (preg_match('/^Name\s*:\s*(.+)$/im', $isiSurat, $match)) {
    $stName = trim($match[1]);
}
if (preg_match('/^(?:Department|Departemen)\s*:\s*(.+)$/im', $isiSurat, $match)) {
    $stDepartment = trim($match[1]);
}
if (preg_match('/^Crew\s*:\s*(.+)$/im', $isiSurat, $match)) {
    $stCrew = trim($match[1]);
}
if ($stDepartment !== '' && $stCrew !== '' && strpos($stDepartment, ',') === false && preg_match('/^[A-Z0-9]+$/i', $stCrew)) {
    $stDepartment .= ', ' . strtoupper($stCrew);
}
if (preg_match('/^Employee Status\s*:\s*(.+)$/im', $isiSurat, $match)) {
    $stStatus = trim($match[1]);
}
if (preg_match('/^(?:Position|Posisi|Jabatan)\s*:\s*(.+)$/im', $isiSurat, $match)) {
    $stPosition = trim($match[1]);
}
if (preg_match('/^Date of entry\s*:\s*(.+)$/im', $isiSurat, $match)) {
    $rawDateEntry = trim($match[1]);
    $stDateEntry = $rawDateEntry && strtotime($rawDateEntry) ? date('Y-m-d', strtotime($rawDateEntry)) : '';
}
if (preg_match('/^Date of evaluation\s*:\s*(.+)$/im', $isiSurat, $match)) {
    $rawDateEval = trim($match[1]);
    $stDateEval = $rawDateEval && strtotime($rawDateEval) ? date('Y-m-d', strtotime($rawDateEval)) : '';
}
if (preg_match('/^Evaluation Months\s*:\s*(.+)$/im', $isiSurat, $match)) {
    $stEvalMonths = trim($match[1]);
}
if (preg_match('/^Company\s*:\s*(.+)$/im', $isiSurat, $match)) {
    $stCompany = trim($match[1]);
}
if (preg_match('/^Request By\s*:\s*(.+)$/im', $isiSurat, $match)) {
    $stRequestBy = trim($match[1]);
}
if (preg_match('/^Request By Title\s*:\s*(.+)$/im', $isiSurat, $match)) {
    $stRequestTitle = trim($match[1]);
}
if (preg_match('/^Approved By\s*:\s*(.+)$/im', $isiSurat, $match)) {
    $stApprovedBy = trim($match[1]);
}
if (preg_match('/^Approved By Title\s*:\s*(.+)$/im', $isiSurat, $match)) {
    $stApprovedTitle = trim($match[1]);
}
if (preg_match('/^Performance Issue ID\s*:\s*(.+)$/im', $isiSurat, $match)) {
    $stIssueId = trim($match[1]);
}
if (preg_match('/^Performance Issue EN\s*:\s*(.+)$/im', $isiSurat, $match)) {
    $stIssueEn = trim($match[1]);
}

require_once __DIR__ . '/../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-edit"></i> Edit Surat</h4>
    <a href="<?= BASE_URL ?>/surat" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card-custom">
    <div class="card-body p-4">
        <div class="alert alert-info mb-3">
            <strong>Nomor Surat:</strong> <?= htmlspecialchars($surat['nomor_surat']) ?> (tidak dapat diubah)
        </div>

        <form method="POST" action="<?= BASE_URL ?>/surat/update/<?= $surat['id_surat'] ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Jenis Surat</label>
                    <select name="id_jenis" id="id_jenis" class="form-select" required onchange="toggleEditFields()">
                        <?php $jenis_surat->data_seek(0); while ($j = $jenis_surat->fetch_assoc()): ?>
                        <option
                            value="<?= $j['id_jenis'] ?>"
                            data-kode="<?= htmlspecialchars($j['kode']) ?>"
                            <?= $surat['id_jenis'] == $j['id_jenis'] ? 'selected' : '' ?>>
                            <?= $j['kode'] ?> - <?= $j['nama_surat'] ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <?php if ($isSuperAdmin): ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Rig</label>
                    <select name="id_rig" class="form-select">
                        <?php $rigList->data_seek(0); while ($rig = $rigList->fetch_assoc()): ?>
                        <option value="<?= $rig['id_rig'] ?>" <?= $surat['id_rig'] == $rig['id_rig'] ? 'selected' : '' ?>><?= $rig['kode_rig'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Tanggal MOC</label>
                    <input type="date" name="tanggal_moc" class="form-control" value="<?= htmlspecialchars($surat['tanggal_moc'] ?? '') ?>">
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label fw-semibold">Catatan internal</label>
                    <textarea name="keterangan" class="form-control edit-note" rows="3" placeholder="Tidak tercetak di surat"><?= htmlspecialchars($surat['keterangan'] ?? '') ?></textarea>
                </div>

                <div id="umum-fields" class="col-md-12">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Tujuan</label>
                            <input type="text" name="tujuan" class="form-control" value="<?= htmlspecialchars($surat['tujuan'] ?? '') ?>">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Perihal</label>
                            <input type="text" name="perihal" class="form-control" value="<?= htmlspecialchars($surat['perihal'] ?? '') ?>">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Isi Surat</label>
                            <textarea name="isi_surat" id="isi_surat" class="form-control" rows="8"><?= htmlspecialchars($isiSurat) ?></textarea>
                            <small class="text-muted">Untuk surat non-SPK, isi surat masih bisa diedit manual.</small>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="isi_surat_generated" id="isi_surat_generated" value="<?= htmlspecialchars($isiSurat) ?>">

                <div id="spk-fields" class="col-md-12" style="display:none;">
                    <div class="spk-workspace">
                        <div class="spk-left">
                            <section class="spk-panel">
                                <h5>1. Dasar surat</h5>
                                <div class="mb-3">
                                    <label class="form-label">Tujuan Surat</label>
                                    <input type="text" name="tujuan" id="tujuan_spk" class="form-control spk-input" value="<?= htmlspecialchars($surat['tujuan'] ?? 'DSR Rig GWDC') ?>">
                                </div>
                                <div>
                                    <label class="form-label">Perihal</label>
                                    <input type="text" name="perihal" id="perihal_spk" class="form-control spk-input" value="<?= htmlspecialchars($surat['perihal'] ?? 'Surat Pengantar Crew Pengganti') ?>">
                                </div>
                            </section>

                            <section class="spk-panel">
                                <h5>2. Data crew</h5>
                                <div class="mb-3">
                                    <label class="form-label">Cari crew</label>
                                    <input list="crew-list" id="crew-input" class="form-control spk-input"
                                           placeholder="Ketik nama crew..."
                                           value="<?= htmlspecialchars(trim($spkNama . ($spkJabatan ? ' - ' . $spkJabatan : ''))) ?>"
                                           onchange="onCrewSelect(this.value)">
                                    <datalist id="crew-list">
                                        <?php
                                        $crewModel = $this->model('CrewModel');
                                        $allCrew = $crewModel->getAllActive();
                                        while ($c = $allCrew->fetch_assoc()):
                                        ?>
                                        <option data-id="<?= $c['id_crew'] ?>" value="<?= $c['nama'] ?> - <?= $c['posisi'] ?>">
                                        <?php endwhile; ?>
                                    </datalist>
                                    <input type="hidden" name="id_crew" id="id_crew" value="">
                                </div>

                                <div class="spk-chip-row">
                                    <span class="spk-chip" id="chip_jabatan">Jabatan: <?= htmlspecialchars($spkJabatan ?: '-') ?></span>
                                    <span class="spk-chip" id="chip_badge">Badge: <?= htmlspecialchars($spkBadge ?: '-') ?></span>
                                    <span class="spk-chip" id="chip_rig">Rig: <?= htmlspecialchars($spkRig ?: '-') ?></span>
                                    <span class="spk-chip" id="chip_crew">Crew: <?= htmlspecialchars($spkCrew ?: '-') ?></span>
                                </div>
                            </section>

                            <section class="spk-panel">
                                <h5>3. Detail tambahan</h5>
                                <label class="form-label">Nama yang digantikan</label>
                                <input type="text" id="posisi_vacant" class="form-control spk-input" value="<?= htmlspecialchars($spkMengganti) ?>" placeholder="cth. vacant Floorman Alm Royden Tambunan">
                            </section>
                        </div>

                        <aside class="spk-preview-wrap">
                            <h5><i class="fas fa-eye"></i> Preview read-only</h5>
                            <div class="spk-preview">
                                <div class="spk-preview-kop">
                                    <span></span>
                                    <strong></strong>
                                </div>
                                <div class="spk-preview-address">
                                    <div>Kepada Yth.</div>
                                    <div id="preview_tujuan"></div>
                                    <div>Di tempat</div>
                                </div>
                                <div class="spk-preview-subject">Perihal: <span id="preview_perihal"></span></div>
                                <div class="spk-preview-greeting">Dengan hormat</div>
                                <p id="preview_intro"></p>
                                <table class="spk-preview-crew">
                                    <tr>
                                        <td>1.</td>
                                        <td>Nama</td>
                                        <td>:</td>
                                        <td id="preview_nama"></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>Jabatan</td>
                                        <td>:</td>
                                        <td id="preview_jabatan"></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>No Badge</td>
                                        <td>:</td>
                                        <td id="preview_badge"></td>
                                    </tr>
                                </table>
                                <p id="preview_permanen"></p>
                                <p>Demikian yang dapat disampaikan, atas perhatian dan kerjasama kami ucapkan Terima kasih.</p>
                                <div class="spk-preview-sign">
                                    <div>Duri, ...</div>
                                    <div class="spk-sign-space"></div>
                                    <div>Amy Faradila</div>
                                    <div>HRD Department</div>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>

                <div id="st-fields" class="col-md-12" style="display:none;">
                    <div class="st-workspace">
                        <div class="st-left">
                            <section class="st-panel">
                                <h5>1. Data karyawan</h5>
                                <div class="mb-3">
                                    <label class="form-label">Cari crew</label>
                                    <div class="crew-search-wrap">
                                        <input type="text" id="st_crew_search" class="form-control spk-input"
                                               placeholder="Ketik nama crew..." autocomplete="off"
                                               value="<?= htmlspecialchars($stCrew) ?>"
                                               onfocus="showCrewDropdown('st_crew_search', 'st_crew_options')"
                                               oninput="filterCrewOptions('st_crew_search', 'st_crew_options')">
                                        <div id="st_crew_options" class="crew-options" style="display:none;"></div>
                                    </div>
                                    <input type="hidden" id="st_id_crew" value="">
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nama</label>
                                        <input type="text" id="st_name" class="form-control spk-input" value="<?= htmlspecialchars($stName) ?>" placeholder="Nama karyawan" oninput="buildStIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Departemen</label>
                                        <input type="text" id="st_department" class="form-control spk-input" value="<?= htmlspecialchars($stDepartment ?: 'GWDC 338, C') ?>" placeholder="GWDC 338, C" oninput="buildStIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Employee Status</label>
                                        <input type="text" id="st_status" class="form-control spk-input" value="<?= htmlspecialchars($stStatus ?: 'Contract') ?>" placeholder="Contract" oninput="buildStIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Position</label>
                                        <input type="text" id="st_position" class="form-control spk-input" value="<?= htmlspecialchars($stPosition) ?>" placeholder="Position" oninput="buildStIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Date of entry</label>
                                        <input type="date" id="st_date_entry" class="form-control spk-input" value="<?= htmlspecialchars($stDateEntry) ?>" oninput="buildStIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Date of evaluation</label>
                                        <input type="date" id="st_date_eval" class="form-control spk-input" value="<?= htmlspecialchars($stDateEval) ?>" oninput="buildStIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Evaluation Months</label>
                                        <input type="text" id="st_eval_months" class="form-control spk-input" value="<?= htmlspecialchars($stEvalMonths ?: '5') ?>" placeholder="5" oninput="buildStIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Perusahaan</label>
                                        <input type="text" id="st_company" class="form-control spk-input" value="<?= htmlspecialchars($stCompany ?: 'PT. SIGMA') ?>" placeholder="PT. SIGMA" oninput="buildStIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Diajukan Oleh</label>
                                        <input type="text" id="st_request_by" class="form-control spk-input" value="<?= htmlspecialchars($stRequestBy ?: 'Bendri Zona') ?>" placeholder="Nama pengaju" oninput="buildStIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Jabatan Pengaju</label>
                                        <input type="text" id="st_request_title" class="form-control spk-input" value="<?= htmlspecialchars($stRequestTitle ?: 'Chief Mechanic') ?>" placeholder="Jabatan pengaju" oninput="buildStIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Disetujui Oleh</label>
                                        <input type="text" id="st_approved_by" class="form-control spk-input" value="<?= htmlspecialchars($stApprovedBy ?: 'Zhang Gang') ?>" placeholder="Nama penyetuju" oninput="buildStIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Jabatan Penyetuju</label>
                                        <input type="text" id="st_approved_title" class="form-control spk-input" value="<?= htmlspecialchars($stApprovedTitle ?: 'Rig Manager') ?>" placeholder="Jabatan penyetuju" oninput="buildStIsi()">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Alasan</label>
                                        <textarea id="st_issue_id" class="form-control spk-input" rows="3" placeholder="Tulis alasan dalam bahasa Indonesia" oninput="buildStIsi()"><?= htmlspecialchars($stIssueId) ?></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Alasan English (otomatis)</label>
                                        <textarea id="st_issue_en_preview" class="form-control spk-input" rows="3" readonly><?= htmlspecialchars($stIssueEn) ?></textarea>
                                        <input type="hidden" id="st_issue_en" value="<?= htmlspecialchars($stIssueEn) ?>">
                                    </div>
                                </div>
                            </section>
                        </div>

                        <aside class="st-preview-wrap">
                            <h5><i class="fas fa-eye"></i> Preview live</h5>
                            <div class="st-preview">
                                <div class="st-preview-head">
                                    <div class="st-preview-title">SURAT PEMULANGAN KARYAWAN</div>
                                    <div class="st-preview-subtitle">EMPLOYEE RETURN LETTER</div>
                                </div>
                                <div class="st-preview-meta">
                                    <div><strong>Name:</strong> <span id="stp_name">...</span></div>
                                    <div><strong>Department:</strong> <span id="stp_department">...</span></div>
                                    <div><strong>Status:</strong> <span id="stp_status">...</span></div>
                                    <div><strong>Position:</strong> <span id="stp_position">...</span></div>
                                </div>
                                <div class="st-preview-body">
                                    <p id="stp_body">Preview akan muncul di sini saat Anda mengisi data.</p>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>

                <div id="sc-fields" class="col-md-12" style="display:none;">
                    <div class="sc-workspace">
                        <div class="sc-left">
                            <section class="sc-panel">
                                <h5>1. Tujuan panggilan</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Kepada</label>
                                        <input type="text" name="tujuan" id="sc_tujuan" class="form-control spk-input" value="<?= htmlspecialchars($scTujuan) ?>" oninput="buildScIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Posisi</label>
                                        <input type="text" id="sc_positions" class="form-control spk-input" value="<?= htmlspecialchars($scPositions) ?>" placeholder="Floorman, Roustabout" oninput="buildScIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Rig</label>
                                        <input type="text" id="sc_rigs" class="form-control spk-input" value="<?= htmlspecialchars($scRigs) ?>" placeholder="GW339, GW338" oninput="buildScIsi()">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Maksud Panggilan</label>
                                        <input type="text" id="sc_purpose" class="form-control spk-input" value="<?= htmlspecialchars($scPurpose) ?>" oninput="buildScIsi()">
                                    </div>
                                </div>
                            </section>

                            <section class="sc-panel">
                                <h5>2. Jadwal hadir</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Panggilan</label>
                                        <input type="date" id="sc_call_date" class="form-control spk-input" value="<?= htmlspecialchars($scCallDate) ?>" oninput="buildScIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Jam</label>
                                        <input type="time" id="sc_call_time" class="form-control spk-input" value="<?= htmlspecialchars($scCallTime) ?>" oninput="buildScIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Surat</label>
                                        <input type="date" id="sc_letter_date" class="form-control spk-input" value="<?= htmlspecialchars($scLetterDate) ?>" oninput="buildScIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tempat Surat</label>
                                        <input type="text" id="sc_place" class="form-control spk-input" value="<?= htmlspecialchars($scPlace) ?>" oninput="buildScIsi()">
                                    </div>
                                </div>
                                <input type="hidden" name="perihal" id="sc_perihal" value="<?= htmlspecialchars($surat['perihal'] ?? 'Surat Panggilan') ?>">
                            </section>
                        </div>

                        <aside class="sc-preview-wrap">
                            <h5><i class="fas fa-eye"></i> Preview live</h5>
                            <div class="sc-preview">
                                <div class="st-preview-head">
                                    <div class="st-preview-title">SURAT PANGGILAN</div>
                                    <div>No: <?= htmlspecialchars($surat['nomor_surat']) ?></div>
                                </div>
                                <div class="st-preview-meta">
                                    <div><strong>Kepada:</strong> <span id="scp_tujuan">...</span></div>
                                    <div><strong>Posisi:</strong> <span id="scp_positions">...</span></div>
                                    <div><strong>Rig:</strong> <span id="scp_rigs">...</span></div>
                                    <div><strong>Hari/Jam:</strong> <span id="scp_schedule">...</span></div>
                                </div>
                                <div class="st-preview-body">
                                    <p id="scp_body">Preview akan muncul di sini saat Anda mengisi data.</p>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>

                <div id="ska-fields" class="col-md-12" style="display:none;">
                    <div class="ska-workspace">
                        <div class="ska-left">
                            <section class="ska-panel">
                                <h5>1. Data karyawan</h5>
                                <div class="mb-3">
                                    <label class="form-label">Cari crew</label>
                                    <div class="crew-search-wrap">
                                        <input type="text" id="ska_crew_search" class="form-control spk-input"
                                               placeholder="Ketik nama crew..." autocomplete="off"
                                               value="<?= htmlspecialchars(trim($skaEmployeeName . ($skaEmployeeTitle ? ' - ' . $skaEmployeeTitle : ''))) ?>"
                                               onfocus="showCrewDropdown('ska_crew_search', 'ska_crew_options')"
                                               oninput="filterCrewOptions('ska_crew_search', 'ska_crew_options')">
                                        <div id="ska_crew_options" class="crew-options" style="display:none;"></div>
                                    </div>
                                    <input type="hidden" id="ska_id_crew" value="">
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Karyawan</label>
                                        <input type="text" id="ska_employee_name" class="form-control spk-input" value="<?= htmlspecialchars($skaEmployeeName) ?>" placeholder="Nama karyawan" oninput="buildSkaIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Jabatan Karyawan</label>
                                        <input type="text" id="ska_employee_title" class="form-control spk-input" value="<?= htmlspecialchars($skaEmployeeTitle) ?>" placeholder="Jabatan" oninput="buildSkaIsi()">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Alamat Karyawan</label>
                                        <input type="text" id="ska_employee_address" class="form-control spk-input" value="<?= htmlspecialchars($skaEmployeeAddress) ?>" placeholder="Alamat karyawan" oninput="buildSkaIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Perusahaan Penempatan</label>
                                        <input type="text" id="ska_company" class="form-control spk-input" value="<?= htmlspecialchars($skaCompany) ?>" oninput="buildSkaIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Mulai</label>
                                        <input type="date" id="ska_start_date" class="form-control spk-input" value="<?= htmlspecialchars($skaStartDate) ?>" oninput="buildSkaIsi()">
                                    </div>
                                </div>
                            </section>

                            <section class="ska-panel">
                                <h5>2. Penandatangan</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Penandatangan</label>
                                        <input type="text" id="ska_statement_name" class="form-control spk-input" value="<?= htmlspecialchars($skaStatementName) ?>" oninput="buildSkaIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Jabatan Penandatangan</label>
                                        <input type="text" id="ska_statement_title" class="form-control spk-input" value="<?= htmlspecialchars($skaStatementTitle) ?>" oninput="buildSkaIsi()">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Alamat Penandatangan</label>
                                        <input type="text" id="ska_statement_address" class="form-control spk-input" value="<?= htmlspecialchars($skaStatementAddress) ?>" oninput="buildSkaIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tempat Surat</label>
                                        <input type="text" id="ska_place" class="form-control spk-input" value="<?= htmlspecialchars($skaPlace) ?>" oninput="buildSkaIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Surat</label>
                                        <input type="date" id="ska_letter_date" class="form-control spk-input" value="<?= htmlspecialchars($skaLetterDate) ?>" oninput="buildSkaIsi()">
                                    </div>
                                </div>
                                <input type="hidden" name="perihal" id="ska_perihal" value="<?= htmlspecialchars($surat['perihal'] ?? 'Surat Keterangan Kerja') ?>">
                            </section>
                        </div>

                        <aside class="ska-preview-wrap">
                            <h5><i class="fas fa-eye"></i> Preview live</h5>
                            <div class="ska-preview">
                                <div class="st-preview-head">
                                    <div class="st-preview-title">SURAT KETERANGAN KERJA</div>
                                    <div>Nomor: <?= htmlspecialchars($surat['nomor_surat']) ?></div>
                                </div>
                                <div class="st-preview-meta">
                                    <div><strong>Penandatangan:</strong> <span id="skap_statement_name">...</span></div>
                                    <div><strong>Karyawan:</strong> <span id="skap_employee_name">...</span></div>
                                    <div><strong>Jabatan:</strong> <span id="skap_employee_title">...</span></div>
                                    <div><strong>Penempatan:</strong> <span id="skap_company">...</span></div>
                                </div>
                                <div class="st-preview-body">
                                    <p id="skap_body">Preview akan muncul di sini saat Anda mengisi data.</p>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>

                <div id="sp-fields" class="col-md-12" style="display:none;">
                    <div class="sp-workspace">
                        <div class="sp-left">
                            <section class="sp-panel">
                                <h5>1. Data karyawan</h5>
                                <div class="mb-3">
                                    <label class="form-label">Cari karyawan</label>
                                    <div class="crew-search-wrap">
                                        <input type="text" id="sp_crew_search" class="form-control spk-input"
                                               placeholder="Ketik nama karyawan..." autocomplete="off"
                                               value="<?= htmlspecialchars(trim($spEmployeeName . ($spEmployeeTitle ? ' - ' . $spEmployeeTitle : ''))) ?>"
                                               onfocus="showCrewDropdown('sp_crew_search', 'sp_crew_options')"
                                               oninput="filterCrewOptions('sp_crew_search', 'sp_crew_options')">
                                        <div id="sp_crew_options" class="crew-options" style="display:none;"></div>
                                    </div>
                                    <input type="hidden" id="sp_id_crew" value="">
                                </div>
                                <div class="spk-chip-row">
                                    <span class="spk-chip" id="sp_chip_nama">Nama: <?= htmlspecialchars($spEmployeeName ?: '-') ?></span>
                                    <span class="spk-chip" id="sp_chip_badge">Badge: <?= htmlspecialchars($spBadge ?: '-') ?></span>
                                    <span class="spk-chip" id="sp_chip_posisi">Posisi: <?= htmlspecialchars($spEmployeeTitle ?: '-') ?></span>
                                    <span class="spk-chip" id="sp_chip_rig">Rig: <?= htmlspecialchars($spRig ?: '-') ?></span>
                                    <span class="spk-chip" id="sp_chip_crew">Crew: <?= htmlspecialchars($spCrew ?: '-') ?></span>
                                </div>
                                <small class="text-muted d-block mb-3">Nama, jabatan, no badge, rig, dan crew terisi otomatis dari data karyawan.</small>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Peringatan Tingkat</label>
                                        <select id="sp_level" class="form-select spk-input" onchange="buildSpIsi()">
                                            <option value="Pertama" <?= strcasecmp($spLevel, 'Pertama') === 0 ? 'selected' : '' ?>>Pertama</option>
                                            <option value="Kedua" <?= strcasecmp($spLevel, 'Kedua') === 0 ? 'selected' : '' ?>>Kedua</option>
                                            <option value="Ketiga" <?= strcasecmp($spLevel, 'Ketiga') === 0 ? 'selected' : '' ?>>Ketiga</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Penandatangan</label>
                                        <input type="text" id="sp_signer" class="form-control spk-input" value="<?= htmlspecialchars($spSigner) ?>" oninput="buildSpIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Jabatan Penandatangan</label>
                                        <input type="text" id="sp_signer_title" class="form-control spk-input" value="<?= htmlspecialchars($spSignerTitle) ?>" oninput="buildSpIsi()">
                                    </div>
                                </div>
                                <input type="hidden" id="sp_employee_name" value="<?= htmlspecialchars($spEmployeeName) ?>">
                                <input type="hidden" id="sp_badge" value="<?= htmlspecialchars($spBadge) ?>">
                                <input type="hidden" id="sp_position" value="<?= htmlspecialchars($spEmployeeTitle) ?>">
                                <input type="hidden" id="sp_rig" value="<?= htmlspecialchars($spRig) ?>">
                                <input type="hidden" id="sp_crew" value="<?= htmlspecialchars($spCrew) ?>">
                            </section>

                            <section class="sp-panel">
                                <h5>2. Detail peringatan</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Surat</label>
                                        <input type="date" id="sp_letter_date" class="form-control spk-input" value="<?= htmlspecialchars($spLetterDate) ?>" oninput="buildSpIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Masa Berlaku Mulai</label>
                                        <input type="date" id="sp_effective_start" class="form-control spk-input" value="<?= htmlspecialchars($spEffectiveStart) ?>" oninput="buildSpIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Masa Berlaku Sampai</label>
                                        <input type="date" id="sp_effective_end" class="form-control spk-input" value="<?= htmlspecialchars($spEffectiveEnd) ?>" oninput="buildSpIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Dikeluarkan Oleh</label>
                                        <input type="text" id="sp_place" class="form-control spk-input" value="<?= htmlspecialchars($spPlace) ?>" oninput="buildSpIsi()">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Judul Pelanggaran</label>
                                        <input type="text" id="sp_violation_title" class="form-control spk-input" value="<?= htmlspecialchars($spViolationTitle) ?>" oninput="buildSpIsi()">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Temuan / Pelanggaran</label>
                                        <textarea id="sp_violation_detail" class="form-control spk-input" rows="3" oninput="buildSpIsi()"><?= htmlspecialchars($spViolationDetail) ?></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Tindakan Disiplin</label>
                                        <textarea id="sp_discipline_action" class="form-control spk-input" rows="3" oninput="buildSpIsi()"><?= htmlspecialchars($spDisciplineAction) ?></textarea>
                                    </div>
                                </div>
                                <input type="hidden" name="perihal" id="sp_perihal" value="<?= htmlspecialchars($surat['perihal'] ?? 'Surat Peringatan') ?>">
                                <input type="hidden" name="tujuan" id="sp_tujuan" value="<?= htmlspecialchars($surat['tujuan'] ?? '') ?>">
                            </section>
                        </div>

                        <aside class="sp-preview-wrap">
                            <h5><i class="fas fa-eye"></i> Preview live</h5>
                            <div class="sp-preview">
                                <div class="st-preview-head">
                                    <div class="st-preview-title" id="spp_title">SURAT PERINGATAN <?= htmlspecialchars(strtoupper($spLevel ?: 'PERTAMA')) ?></div>
                                    <div>No: <?= htmlspecialchars($surat['nomor_surat']) ?></div>
                                </div>
                                <div class="st-preview-meta">
                                    <div><strong>Nama:</strong> <span id="spp_name">...</span></div>
                                    <div><strong>No Badge:</strong> <span id="spp_badge">...</span></div>
                                    <div><strong>Jabatan:</strong> <span id="spp_position">...</span></div>
                                    <div><strong>Berlaku:</strong> <span id="spp_effective">...</span></div>
                                </div>
                                <div class="st-preview-body">
                                    <p id="spp_body">Preview akan muncul di sini saat Anda mengisi data.</p>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>

                <div id="spt-fields" class="col-md-12" style="display:none;">
                    <div class="spt-workspace">
                        <div class="spt-left">
                            <section class="spt-panel">
                               <h5>1. Tujuan panggilan</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Kepada</label>
                                        <input type="text" name="tujuan" id="sc_tujuan" class="form-control spk-input" value="<?= htmlspecialchars($scTujuan) ?>" oninput="buildScIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Posisi</label>
                                        <input type="text" id="sc_positions" class="form-control spk-input" value="<?= htmlspecialchars($scPositions) ?>" placeholder="Floorman, Roustabout" oninput="buildScIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Rig</label>
                                        <input type="text" id="sc_rigs" class="form-control spk-input" value="<?= htmlspecialchars($scRigs) ?>" placeholder="GW339, GW338" oninput="buildScIsi()">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Maksud Panggilan</label>
                                        <input type="text" id="sc_purpose" class="form-control spk-input" value="<?= htmlspecialchars($scPurpose) ?>" oninput="buildScIsi()">
                                    </div>
                                </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tempat Surat</label>
                                        <input type="text" id="spt_place" class="form-control spk-input" value="<?= htmlspecialchars($sptPlace) ?>" oninput="buildSptIsi()">
                                    </div>
                                
                            </section>

                            <section class="spt-panel">
                                <h5>2. Isi pemberitahuan</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Paragraf 1</label>
                                        <textarea id="spt_paragraph_1" class="form-control spk-input" rows="3" oninput="buildSptIsi()"><?= htmlspecialchars($sptParagraph1) ?></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Paragraf 2</label>
                                        <textarea id="spt_paragraph_2" class="form-control spk-input" rows="3" oninput="buildSptIsi()"><?= htmlspecialchars($sptParagraph2) ?></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Paragraf 3</label>
                                        <textarea id="spt_paragraph_3" class="form-control spk-input" rows="3" oninput="buildSptIsi()"><?= htmlspecialchars($sptParagraph3) ?></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Penutup</label>
                                        <textarea id="spt_closing" class="form-control spk-input" rows="2" oninput="buildSptIsi()"><?= htmlspecialchars($sptClosing) ?></textarea>
                                    </div>
                                </div>
                            </section>

                            <section class="spt-panel">
                                <h5>3. Penandatangan</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Surat</label>
                                        <input type="date" id="spt_letter_date" class="form-control spk-input" value="<?= htmlspecialchars($sptLetterDate) ?>" oninput="buildSptIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Penandatangan</label>
                                        <input type="text" id="spt_signer" class="form-control spk-input" value="<?= htmlspecialchars($sptSigner) ?>" oninput="buildSptIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Jabatan</label>
                                        <input type="text" id="spt_signer_title" class="form-control spk-input" value="<?= htmlspecialchars($sptSignerTitle) ?>" oninput="buildSptIsi()">
                                    </div>
                                </div>
                            </section>
                        </div>

                        <aside class="spt-preview-wrap">
                            <h5><i class="fas fa-eye"></i> Preview live</h5>
                            <div class="spt-preview">
                                <div class="st-preview-head">
                                    <div class="st-preview-title">PEMBERITAHUAN</div>
                                    <div>Nomor: <?= htmlspecialchars($surat['nomor_surat']) ?></div>
                                </div>
                                <div class="st-preview-meta">
                                    <div><strong>Kepada:</strong> <span id="sptp_tujuan">...</span></div>
                                    <div><strong>Lampiran:</strong> <span id="sptp_lampiran">...</span></div>
                                    <div><strong>Tanggal:</strong> <span id="sptp_date">...</span></div>
                                </div>
                                <div class="st-preview-body">
                                    <p id="sptp_body">Preview akan muncul di sini saat Anda mengisi data.</p>
                                </div>
                            </div>
                        </aside>
                    </div>
                    </div>

                <!--  SR - Surat Resign  -->
                <div id="sr-fields" class="col-md-12" style="display:none;">
                    <div class="spt-workspace">
                        <div class="spt-left">
                            <section class="spt-panel">
                                <h5>1. Tujuan surat</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Cari crew</label>
                                        <div class="crew-search-wrap">
                                            <input type="text" id="sr_crew_search" class="form-control spk-input" placeholder="Ketik nama crew..." autocomplete="off" onfocus="showCrewDropdown('sr_crew_search', 'sr_crew_options')" oninput="filterCrewOptions('sr_crew_search', 'sr_crew_options')">
                                            <div id="sr_crew_options" class="crew-options" style="display:none;"></div>
                                        </div>
                                        <input type="hidden" id="sr_id_crew" value="">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Kepada</label>
                                        <input type="text" name="tujuan" id="sr_tujuan" class="form-control spk-input"
                                            list="sr-tujuan-list" value="<?= htmlspecialchars($srTujuan) ?>" oninput="buildSrIsi()">
                                        <datalist id="sr-tujuan-list">
                                            <option value="All Crew"></option>
                                            <option value="All Rig"></option>
                                            <option value="Semua Rig"></option>
                                            <option value="Crew terkait"></option>
                                        </datalist>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Posisi</label>
                                        <input type="text" id="sr_positions" class="form-control spk-input" value="<?= htmlspecialchars($srPositions) ?>" placeholder="Roustabout, Roomboy" oninput="buildSrIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Rig</label>
                                        <input type="text" id="sr_rigs" class="form-control spk-input" value="<?= htmlspecialchars($srRigs) ?>" placeholder="GW339, GW338" oninput="buildSrIsi()">
                                    </div>
                                    <input type="hidden" name="perihal" id="sr_perihal" value="<?= htmlspecialchars($srPerihal) ?>">
                                </div>
                            </section>
                            <section class="spt-panel">
                                <h5>2. Detail memo</h5>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Surat</label><input type="date" id="sr_letter_date" class="form-control spk-input" value="<?= htmlspecialchars($srLetterDate) ?>" oninput="buildSrIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Tempat Surat</label><input type="text" id="sr_place" class="form-control spk-input" value="<?= htmlspecialchars($srPlace) ?>" oninput="buildSrIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Lampiran</label><input type="text" id="sr_lampiran" class="form-control spk-input" value="<?= htmlspecialchars($srLampiran) ?>" oninput="buildSrIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Penandatangan</label><input type="text" id="sr_signer" class="form-control spk-input" value="<?= htmlspecialchars($srSigner) ?>" oninput="buildSrIsi()"></div>
                                </div>
                            </section>
                        </div>
                        <aside class="spt-preview-wrap">
                            <h5><i class="fas fa-eye"></i> Preview live</h5>
                            <div class="spt-preview">
                                <div class="st-preview-head"><div class="st-preview-title">MEMO INTERNAL</div><div>Nomor: <?= htmlspecialchars($surat['nomor_surat']) ?></div></div>
                                <div class="st-preview-meta">
                                    <div><strong>Kepada:</strong> <span id="srp_tujuan">...</span></div>
                                    <div><strong>Rig:</strong> <span id="srp_rigs">...</span></div>
                                    <div><strong>Posisi:</strong> <span id="srp_positions">...</span></div>
                                </div>
                                <div class="st-preview-body"><p id="srp_body">Preview akan muncul di sini saat Anda mengisi data.</p></div>
                            </div>
                        </aside>
                    </div>
                </div>

                <!--  SKK - Surat Keterangan Kerja  -->
                <div id="skk-fields" class="col-md-12" style="display:none;">
                    <div class="spt-workspace">
                        <div class="spt-left">
                            <section class="spt-panel">
                                <h5>1. Tujuan surat</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Kepada</label>
                                        <input type="text" name="tujuan" id="skk_tujuan" class="form-control spk-input"
                                            list="skk-tujuan-list" value="<?= htmlspecialchars($skkTujuan) ?>" oninput="buildSkkIsi()">
                                        <datalist id="skk-tujuan-list">
                                            <option value="All Crew"></option>
                                            <option value="All Rig"></option>
                                            <option value="Semua Rig"></option>
                                            <option value="Crew terkait"></option>
                                        </datalist>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Posisi</label>
                                        <input type="text" id="skk_positions" class="form-control spk-input" value="<?= htmlspecialchars($skkPositions) ?>" placeholder="Roustabout, Roomboy" oninput="buildSkkIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Rig</label>
                                        <input type="text" id="skk_rigs" class="form-control spk-input" value="<?= htmlspecialchars($skkRigs) ?>" placeholder="GW339, GW338" oninput="buildSkkIsi()">
                                    </div>
                                </div>
                            </section>
                            <section class="spt-panel">
                                <h5>2. Data karyawan</h5>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Cari crew</label>
                                    <div class="crew-search-wrap">
                                        <input type="text" id="skk_crew_search" class="form-control spk-input" placeholder="Ketik nama crew..." autocomplete="off" onfocus="showCrewDropdown('skk_crew_search', 'skk_crew_options')" oninput="filterCrewOptions('skk_crew_search', 'skk_crew_options')">
                                        <div id="skk_crew_options" class="crew-options" style="display:none;"></div>
                                    </div>
                                    <input type="hidden" id="skk_id_crew" value="">
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label fw-semibold">Nama</label><input type="text" id="skk_name" class="form-control spk-input" readonly value="<?= htmlspecialchars($skkName) ?>" oninput="buildSkkIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Jabatan</label><input type="text" id="skk_position" class="form-control spk-input" readonly value="<?= htmlspecialchars($skkPosition) ?>" oninput="buildSkkIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Proyek & Lokasi</label><input type="text" id="skk_project" class="form-control spk-input" value="<?= htmlspecialchars($skkProject) ?>" oninput="buildSkkIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Mulai Bekerja</label><input type="date" id="skk_start_date" class="form-control spk-input" value="<?= htmlspecialchars($skkStartDate) ?>" oninput="buildSkkIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Surat</label><input type="date" id="skk_letter_date" class="form-control spk-input" value="<?= htmlspecialchars($skkLetterDate) ?>" oninput="buildSkkIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Tempat Surat</label><input type="text" id="skk_place" class="form-control spk-input" value="<?= htmlspecialchars($skkPlace) ?>" oninput="buildSkkIsi()"></div>
                                </div>
                                <input type="hidden" name="perihal" id="skk_perihal" value="Surat Keterangan Kerja">
                            </section>
                        </div>
                        <aside class="spt-preview-wrap">
                            <h5><i class="fas fa-eye"></i> Preview live</h5>
                            <div class="spt-preview">
                                <div class="st-preview-head"><div class="st-preview-title">SURAT KETERANGAN KERJA</div><div>Nomor: <?= htmlspecialchars($surat['nomor_surat']) ?></div></div>
                                <div class="st-preview-meta">
                                    <div><strong>Nama:</strong> <span id="skkp_name">...</span></div>
                                    <div><strong>Jabatan:</strong> <span id="skkp_position">...</span></div>
                                    <div><strong>Project:</strong> <span id="skkp_project">...</span></div>
                                </div>
                                <div class="st-preview-body"><p id="skkp_body">Pilih crew untuk mengisi otomatis.</p></div>
                            </div>
                        </aside>
                    </div>
                </div>

                <!--  BA - Berita Acara  -->
                <div id="ba-fields" class="col-md-12" style="display:none;">
                    <div class="spt-workspace">
                        <div class="spt-left">
                            <section class="spt-panel">
                                <h5>1. Tujuan surat</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Kepada</label>
                                        <input type="text" name="tujuan" id="ba_tujuan" class="form-control spk-input"
                                            list="ba-tujuan-list" value="<?= htmlspecialchars($baTujuan) ?>" oninput="buildBaIsi()">
                                        <datalist id="ba-tujuan-list">
                                            <option value="All Crew"></option>
                                            <option value="All Rig"></option>
                                            <option value="Semua Rig"></option>
                                            <option value="Crew terkait"></option>
                                        </datalist>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Posisi</label>
                                        <input type="text" id="ba_positions" class="form-control spk-input" value="<?= htmlspecialchars($baPositions) ?>" placeholder="Roustabout, Roomboy" oninput="buildBaIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Rig</label>
                                        <input type="text" id="ba_rigs" class="form-control spk-input" value="<?= htmlspecialchars($baRigs) ?>" placeholder="GW339, GW338" oninput="buildBaIsi()">
                                    </div>
                                </div>
                            </section>
                            <section class="spt-panel">
                                <h5>2. Data assessment</h5>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Cari crew</label>
                                    <div class="crew-search-wrap">
                                        <input type="text" id="ba_crew_search" class="form-control spk-input" placeholder="Ketik nama crew..." autocomplete="off" onfocus="showCrewDropdown('ba_crew_search', 'ba_crew_options')" oninput="filterCrewOptions('ba_crew_search', 'ba_crew_options')">
                                        <div id="ba_crew_options" class="crew-options" style="display:none;"></div>
                                    </div>
                                    <input type="hidden" id="ba_id_crew" value="">
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label fw-semibold">Nama Crew</label><input type="text" id="ba_name" class="form-control spk-input" readonly value="<?= htmlspecialchars($baName) ?>" oninput="buildBaIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Posisi</label><input type="text" id="ba_position" class="form-control spk-input" readonly value="<?= htmlspecialchars($baPosition) ?>" oninput="buildBaIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Rig</label><input type="text" id="ba_rig" class="form-control spk-input" readonly value="<?= htmlspecialchars($baRig) ?>" oninput="buildBaIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Nomor Kontrak</label><input type="text" id="ba_contract" class="form-control spk-input" value="<?= htmlspecialchars($baContract) ?>" oninput="buildBaIsi()"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Start OJT</label><input type="date" id="ba_start_ojt" class="form-control spk-input" value="<?= htmlspecialchars($baStartOjt) ?>" oninput="buildBaIsi()"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">End OJT</label><input type="date" id="ba_end_ojt" class="form-control spk-input" value="<?= htmlspecialchars($baEndOjt) ?>" oninput="buildBaIsi()"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Date Assessment</label><input type="date" id="ba_assessment_date" class="form-control spk-input" value="<?= htmlspecialchars($baAssessmentDate) ?>" oninput="buildBaIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Hasil</label><select id="ba_result" class="form-select spk-input" onchange="buildBaIsi()"><option value="Passed" <?= $baResult === 'Passed' ? 'selected' : '' ?>>Passed</option><option value="Failed" <?= $baResult === 'Failed' ? 'selected' : '' ?>>Failed</option></select></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Tempat</label><input type="text" id="ba_place" class="form-control spk-input" value="<?= htmlspecialchars($baPlace) ?>" oninput="buildBaIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Surat</label><input type="date" id="ba_letter_date" class="form-control spk-input" value="<?= htmlspecialchars($baLetterDate) ?>" oninput="buildBaIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Nama Signer (DSR)</label><input type="text" id="ba_signer" class="form-control spk-input" value="<?= htmlspecialchars($baSignerDsr) ?>" placeholder="Nama DSR" oninput="buildBaIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Nama Signer (RSM)</label><input type="text" id="ba_signer_rsm" class="form-control spk-input" value="<?= htmlspecialchars($baSignerRsm) ?>" placeholder="Nama RSM" oninput="buildBaIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Nama Signer (HSE)</label><input type="text" id="ba_signer_hse" class="form-control spk-input" value="<?= htmlspecialchars($baSignerHse) ?>" placeholder="Nama HSE" oninput="buildBaIsi()"></div>
                                </div>
                                <input type="hidden" name="perihal" id="ba_perihal" value="Berita Acara Evaluasi Hasil Assessment & OJT">
                            </section>
                        </div>
                        <aside class="spt-preview-wrap">
                            <h5><i class="fas fa-eye"></i> Preview live</h5>
                            <div class="spt-preview">
                                <div class="st-preview-head"><div class="st-preview-title">BERITA ACARA ASSESSMENT & OJT</div><div>Nomor: <?= htmlspecialchars($surat['nomor_surat']) ?></div></div>
                                <div class="st-preview-meta">
                                    <div><strong>Nama:</strong> <span id="bap_name">...</span></div>
                                    <div><strong>Rig:</strong> <span id="bap_rig">...</span></div>
                                    <div><strong>Hasil:</strong> <span id="bap_result">...</span></div>
                                </div>
                                <div class="st-preview-body"><p id="bap_body">Pilih crew untuk mengisi otomatis.</p></div>
                            </div>
                        </aside>
                    </div>
                </div>

                <!--  SJ - Surat Perjanjian  -->
                <div id="sj-fields" class="col-md-12" style="display:none;">
                    <div class="spt-workspace">
                        <div class="spt-left">
                            <section class="spt-panel">
                                <h5>1. Data pihak pertama</h5>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Cari crew</label>
                                    <div class="crew-search-wrap">
                                        <input type="text" id="sj_crew_search" class="form-control spk-input" placeholder="Ketik nama crew..." autocomplete="off" onfocus="showCrewDropdown('sj_crew_search', 'sj_crew_options')" oninput="filterCrewOptions('sj_crew_search', 'sj_crew_options')">
                                        <div id="sj_crew_options" class="crew-options" style="display:none;"></div>
                                    </div>
                                    <input type="hidden" id="sj_id_crew" value="">
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Perjanjian</label><input type="date" id="sj_date" class="form-control spk-input" value="<?= htmlspecialchars($sjDate) ?>" oninput="buildSjIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Nama</label><input type="text" id="sj_p1_name" class="form-control spk-input" value="<?= htmlspecialchars($sjP1Name) ?>" oninput="buildSjIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Tempat/tgl lahir</label><input type="text" id="sj_p1_birth" class="form-control spk-input" value="<?= htmlspecialchars($sjP1Birth) ?>" oninput="buildSjIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Jabatan</label><input type="text" id="sj_p1_position" class="form-control spk-input" value="<?= htmlspecialchars($sjP1Position) ?>" oninput="buildSjIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Alamat</label><input type="text" id="sj_p1_address" class="form-control spk-input" value="<?= htmlspecialchars($sjP1Address) ?>" oninput="buildSjIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Domisili</label><input type="text" id="sj_p1_domicile" class="form-control spk-input" value="<?= htmlspecialchars($sjP1Domicile) ?>" oninput="buildSjIsi()"></div>
                                </div>
                            </section>
                            <section class="spt-panel">
                                <h5>2. Detail perjanjian</h5>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label fw-semibold">Nama Sertifikat</label><input type="text" id="sj_cert_name" class="form-control spk-input" value="<?= htmlspecialchars($sjCertName) ?>" oninput="buildSjIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Biaya Sertifikat</label><input type="text" id="sj_fee" class="form-control spk-input" value="<?= htmlspecialchars($sjFee) ?>" oninput="buildSjIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Potongan Perbulan</label><input type="text" id="sj_deduction" class="form-control spk-input" value="<?= htmlspecialchars($sjDeduction) ?>" oninput="buildSjIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Ikatan Kerja</label><input type="text" id="sj_work_period" class="form-control spk-input" value="<?= htmlspecialchars($sjWorkPeriod) ?>" oninput="buildSjIsi()"></div>
                                </div>
                                <input type="hidden" name="perihal" id="sj_perihal" value="Surat Perjanjian Antara Karyawan dan Perusahaan">
                            </section>
                        </div>
                        <aside class="spt-preview-wrap">
                            <h5><i class="fas fa-eye"></i> Preview live</h5>
                            <div class="spt-preview">
                                <div class="st-preview-head"><div class="st-preview-title">SURAT PERJANJIAN</div><div>Nomor: <?= htmlspecialchars($surat['nomor_surat']) ?></div></div>
                                <div class="st-preview-meta">
                                    <div><strong>Pihak Pertama:</strong> <span id="sjp_name"><?= htmlspecialchars($sjP1Name ?: '...') ?></span></div>
                                    <div><strong>Sertifikat:</strong> <span id="sjp_cert"><?= htmlspecialchars($sjCertName) ?></span></div>
                                    <div><strong>Biaya:</strong> <span id="sjp_fee"><?= htmlspecialchars($sjFee) ?></span></div>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>

                <!--  MM - Memo  -->
                <div id="mm-fields" class="col-md-12" style="display:none;">
                    <div class="spt-workspace">
                        <div class="spt-left">
                            <section class="spt-panel">
                                <h5>1. Model memo</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Model</label>
                                        <select id="mm_model" class="form-select spk-input" onchange="toggleMemoModel(); buildMmIsi();">
                                            <option value="Memo Internal" <?= $mmModel === 'Memo Internal' ? 'selected' : '' ?>>Memo Internal</option>
                                            <option value="Memorandum" <?= $mmModel === 'Memorandum' ? 'selected' : '' ?>>Memorandum</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Surat</label><input type="date" id="mm_letter_date" class="form-control spk-input" value="<?= htmlspecialchars($mmLetterDate) ?>" oninput="buildMmIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Tempat Surat</label><input type="text" id="mm_place" class="form-control spk-input" value="<?= htmlspecialchars($mmPlace) ?>" oninput="buildMmIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Lampiran</label><input type="text" id="mm_lampiran" class="form-control spk-input" value="<?= htmlspecialchars($mmLampiran) ?>" oninput="buildMmIsi()"></div>
                                    <div class="col-md-12"><label class="form-label fw-semibold">Perihal</label><input type="text" name="perihal" id="mm_perihal" class="form-control spk-input" value="<?= htmlspecialchars($mmPerihal) ?>" oninput="buildMmIsi()"></div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Kepada</label>
                                        <input type="text" name="tujuan" id="mm_tujuan" class="form-control spk-input" list="mm-tujuan-list" value="<?= htmlspecialchars($mmTujuan) ?>" oninput="buildMmIsi()">
                                        <datalist id="mm-tujuan-list">
                                            <option value="All Rig"></option>
                                            <option value="Semua Rig"></option>
                                            <option value="Crew terkait"></option>
                                        </datalist>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Posisi</label>
                                        <select id="mm_positions" class="form-select spk-input" multiple data-placeholder="Pilih satu atau lebih posisi" onchange="buildMmIsi()">
                                            <?php foreach ($scPositionOptions as $posisiOption): ?>
                                                <option value="<?= htmlspecialchars($posisiOption) ?>" <?= in_array($posisiOption, $mmPositionSelections, true) ? 'selected' : '' ?>><?= htmlspecialchars($posisiOption) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Rig</label>
                                        <input type="text" id="mm_rigs" class="form-control spk-input" list="mm-rig-list" value="<?= htmlspecialchars($mmRigs) ?>" oninput="buildMmIsi()">
                                        <datalist id="mm-rig-list">
                                            <?php foreach ($scRigOptions as $rigOption): ?>
                                                <option value="<?= htmlspecialchars($rigOption) ?>"></option>
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>
                                </div>
                            </section>
                            <section class="spt-panel">
                                <h5>2. Isi memo</h5>
                                <div class="row g-3">
                                    <div class="col-md-12"><label class="form-label fw-semibold">Isi Pembuka</label><textarea id="mm_intro" class="form-control spk-input" rows="4" oninput="buildMmIsi()"><?= htmlspecialchars($mmIntro) ?></textarea></div>
                                    <div class="col-md-12"><label class="form-label fw-semibold">Poin Memo</label><textarea id="mm_points" class="form-control spk-input" rows="14" oninput="buildMmIsi()"><?= htmlspecialchars($mmPoints) ?></textarea></div>
                                    <div class="col-md-12"><label class="form-label fw-semibold">Isi Lanjutan (khusus Memorandum)</label><textarea id="mm_followup" class="form-control spk-input" rows="10" oninput="buildMmIsi()"><?= htmlspecialchars($mmFollowup) ?></textarea></div>
                                    <div class="col-md-12"><label class="form-label fw-semibold">Penutup</label><textarea id="mm_closing" class="form-control spk-input" rows="2" oninput="buildMmIsi()"><?= htmlspecialchars($mmClosing) ?></textarea></div>
                                </div>
                            </section>
                            <section class="spt-panel">
                                <h5>3. Tanda tangan</h5>
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label fw-semibold">Signer HRD</label><input type="text" id="mm_signer_hrd" class="form-control spk-input" value="<?= htmlspecialchars($mmSignerHrd) ?>" oninput="buildMmIsi()"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Jabatan HRD</label><input type="text" id="mm_signer_hrd_title" class="form-control spk-input" value="<?= htmlspecialchars($mmSignerHrdTitle) ?>" oninput="buildMmIsi()"></div>
                                    <div class="col-md-6 memo-memorandum-only"><label class="form-label fw-semibold">Signer Rig</label><input type="text" id="mm_signer_rig" class="form-control spk-input" value="<?= htmlspecialchars($mmSignerRig) ?>" oninput="buildMmIsi()"></div>
                                    <div class="col-md-6 memo-memorandum-only"><label class="form-label fw-semibold">Jabatan Rig</label><input type="text" id="mm_signer_rig_title" class="form-control spk-input" value="<?= htmlspecialchars($mmSignerRigTitle) ?>" oninput="buildMmIsi()"></div>
                                    <div class="col-md-6 memo-memorandum-only"><label class="form-label fw-semibold">Signer Manager</label><input type="text" id="mm_signer_manager" class="form-control spk-input" value="<?= htmlspecialchars($mmSignerManager) ?>" oninput="buildMmIsi()"></div>
                                    <div class="col-md-6 memo-memorandum-only"><label class="form-label fw-semibold">Jabatan Manager</label><input type="text" id="mm_signer_manager_title" class="form-control spk-input" value="<?= htmlspecialchars($mmSignerManagerTitle) ?>" oninput="buildMmIsi()"></div>
                                </div>
                            </section>
                        </div>
                        <aside class="spt-preview-wrap">
                            <h5><i class="fas fa-eye"></i> Preview live</h5>
                            <div class="spt-preview">
                                <div class="st-preview-head"><div class="st-preview-title" id="mmp_title"><?= htmlspecialchars(strtoupper($mmModel === 'Memorandum' ? 'MEMORANDUM' : 'MEMO INTERNAL')) ?></div><div>Nomor: <?= htmlspecialchars($surat['nomor_surat']) ?></div></div>
                                <div class="st-preview-meta">
                                    <div><strong>Perihal:</strong> <span id="mmp_perihal"><?= htmlspecialchars($mmPerihal) ?></span></div>
                                    <div><strong>Kepada:</strong> <span id="mmp_tujuan"><?= htmlspecialchars($mmTujuan) ?></span></div>
                                    <div><strong>Model:</strong> <span id="mmp_model"><?= htmlspecialchars($mmModel) ?></span></div>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>

                <div class="col-md-12">
                    <hr>
                    <h6 class="fw-bold mb-3">Lampiran / Arsip Surat Tertandatangan</h6>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Link File</label>
                    <input type="url" name="link_file" class="form-control" value="<?= htmlspecialchars($surat['link_file'] ?? '') ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Nama File</label>
                    <input type="text" name="nama_file" class="form-control" value="<?= htmlspecialchars($surat['nama_file'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-sigma"><i class="fas fa-save"></i> Update</button>
        </form>
    </div>
</div>

<style>
    .edit-note {
        background: #fffaf0;
        border-style: dashed;
    }

    .spk-workspace,
    .st-workspace,
    .sc-workspace,
    .ska-workspace,
    .sp-workspace,
    .spt-workspace {
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(310px, .95fr);
        gap: 20px;
        margin: 6px 0 18px;
    }

    .spk-left,
    .st-left,
    .sc-left,
    .ska-left,
    .sp-left,
    .spt-left {
        display: grid;
        gap: 16px;
    }

    .spk-panel,
    .spk-preview-wrap,
    .st-panel,
    .st-preview-wrap,
    .sc-panel,
    .sc-preview-wrap,
    .ska-panel,
    .ska-preview-wrap,
    .sp-panel,
    .sp-preview-wrap,
    .spt-panel,
    .spt-preview-wrap {
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
        padding: 18px;
    }

    .spk-panel h5,
    .spk-preview-wrap h5,
    .st-panel h5,
    .st-preview-wrap h5,
    .sc-panel h5,
    .sc-preview-wrap h5,
    .ska-panel h5,
    .ska-preview-wrap h5,
    .sp-panel h5,
    .sp-preview-wrap h5,
    .spt-panel h5,
    .spt-preview-wrap h5 {
        margin: 0 0 14px;
        color: var(--text);
        font-size: 15px;
        font-weight: 700;
    }

    .spk-panel .form-label,
    .st-panel .form-label,
    .sc-panel .form-label,
    .ska-panel .form-label,
    .sp-panel .form-label,
    .spt-panel .form-label {
        font-weight: 500;
    }

    .spk-input {
        min-height: 45px;
        font-size: 15px;
        font-weight: 400;
    }

    .spk-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 8px;
    }

    .spk-chip {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: 5px 10px;
        border-radius: 8px;
        background: rgba(43, 42, 76, .09);
        color: var(--navy);
        font-size: 12px;
        font-weight: 500;
    }

    .spk-preview-wrap,
    .st-preview-wrap,
    .sc-preview-wrap,
    .ska-preview-wrap,
    .sp-preview-wrap,
    .spt-preview-wrap {
        align-self: start;
        position: sticky;
        top: 16px;
        background: #f8f8fb;
    }

    .spk-preview-wrap h5 i,
    .st-preview-wrap h5 i,
    .sc-preview-wrap h5 i,
    .ska-preview-wrap h5 i,
    .sp-preview-wrap h5 i,
    .spt-preview-wrap h5 i {
        margin-right: 7px;
        color: var(--navy);
    }

    .spk-preview,
    .st-preview,
    .sc-preview,
    .ska-preview,
    .sp-preview,
    .spt-preview {
        border-radius: 10px;
        background: #fff;
        border: 1px solid var(--border);
        padding: 20px 18px;
        min-height: 430px;
        font-family: Calibri, Arial, sans-serif;
        font-size: 12px;
        line-height: 1.45;
        color: var(--text);
        box-shadow: 0 12px 28px rgba(15, 23, 42, .08);
    }

    .spk-preview p,
    .st-preview p,
    .sc-preview p,
    .ska-preview p,
    .sp-preview p,
    .spt-preview p {
        margin-bottom: 10px;
        text-align: justify;
    }

    .spk-preview-kop {
        margin-bottom: 18px;
    }

    .spk-preview-kop span,
    .spk-preview-kop strong {
        display: block;
        height: 12px;
        border-radius: 2px;
    }

    .spk-preview-kop span {
        width: 100%;
        background: var(--navy);
        margin-bottom: 5px;
    }

    .spk-preview-kop strong {
        width: 62%;
        margin-left: auto;
        background: var(--red);
    }

    .spk-preview-address {
        margin-bottom: 14px;
    }

    .spk-preview-subject,
    .spk-preview-greeting {
        margin-bottom: 12px;
    }

    .spk-preview-crew {
        width: 88%;
        margin: 2px auto 12px;
        border-collapse: collapse;
    }

    .spk-preview-crew td {
        padding: 1px 4px 5px 0;
        vertical-align: top;
    }

    .spk-preview-crew td:first-child {
        width: 18px;
        text-align: right;
        padding-right: 8px;
    }

    .spk-preview-crew td:nth-child(2) {
        width: 62px;
    }

    .spk-preview-crew td:nth-child(3) {
        width: 10px;
    }

    .spk-preview-sign {
        width: 44%;
        margin-left: auto;
        margin-top: 20px;
        text-align: center;
    }

    .spk-sign-space {
        height: 42px;
    }

    .crew-search-wrap {
        position: relative;
    }

    .crew-options {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 20;
        max-height: 220px;
        overflow-y: auto;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 12px 24px rgba(15, 23, 42, .10);
    }

    .crew-option {
        width: 100%;
        padding: 10px 12px;
        border: 0;
        background: #fff;
        text-align: left;
        font-size: 13px;
        color: var(--text);
        cursor: pointer;
    }

    .crew-option:hover,
    .crew-option.active {
        background: var(--pk4);
        color: var(--pk);
    }

    .st-preview-head {
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
        text-align: center;
    }

    .st-preview-title {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .08em;
        margin-bottom: 2px;
    }

    .st-preview-subtitle {
        font-size: 11px;
        font-style: italic;
        color: var(--muted);
    }

    .st-preview-meta {
        display: grid;
        gap: 6px;
        margin-bottom: 12px;
    }

    .st-preview-body {
        text-align: justify;
    }

    @media (max-width: 992px) {
        .spk-workspace,
        .st-workspace,
        .sc-workspace,
        .ska-workspace,
        .sp-workspace,
        .spt-workspace {
            grid-template-columns: 1fr;
        }

        .spk-preview-wrap,
        .st-preview-wrap,
        .sc-preview-wrap,
        .ska-preview-wrap,
        .sp-preview-wrap,
        .spt-preview-wrap {
            position: static;
        }
    }
</style>

<script>
let selectedCrewData = {
    nama: <?= json_encode($spkNama) ?>,
    posisi: <?= json_encode($spkJabatan) ?>,
    nomor_badge: <?= json_encode($spkBadge ?: '-') ?>,
    kode_rig: <?= json_encode($spkRig) ?>,
    crew: <?= json_encode($spkCrew) ?>
};
let crewSearchOptions = <?= json_encode($crewSearchOptions ?? []) ?>;
let scAllRigs = <?= json_encode($scRigOptions) ?>;

document.addEventListener('DOMContentLoaded', () => {
    toggleEditFields();
    buildSpkIsi();
    buildStIsi();
    buildScIsi();
    buildSkaIsi();
    buildSpIsi();
    buildSptIsi();
    buildCrewDropdown('st_crew_options');
    buildCrewDropdown('ska_crew_options');
    buildCrewDropdown('sp_crew_options');
    buildCrewDropdown('sr_crew_options');
    buildCrewDropdown('skk_crew_options');
    buildCrewDropdown('ba_crew_options');
    buildCrewDropdown('sj_crew_options');
    initScPositionSelect();
});

document.addEventListener('click', (event) => {
    if (!event.target.closest('.crew-search-wrap')) {
        ['st_crew_options', 'ska_crew_options', 'sp_crew_options', 'sr_crew_options', 'skk_crew_options', 'ba_crew_options', 'sj_crew_options'].forEach(optionsId => {
            const options = document.getElementById(optionsId);
            if (options) options.style.display = 'none';
        });
    }
});

document.getElementById('tujuan_spk').addEventListener('input', updateSpkPreview);
document.getElementById('perihal_spk').addEventListener('input', updateSpkPreview);
document.getElementById('posisi_vacant').addEventListener('input', buildSpkIsi);

function isSpkSelected() {
    const jenis = document.getElementById('id_jenis');
    const selected = jenis.options[jenis.selectedIndex];
    return (selected.getAttribute('data-kode') || '') === 'SPK';
}

function isStSelected() {
    const jenis = document.getElementById('id_jenis');
    const selected = jenis.options[jenis.selectedIndex];
    return (selected.getAttribute('data-kode') || '') === 'ST';
}

function isScSelected() {
    const jenis = document.getElementById('id_jenis');
    const selected = jenis.options[jenis.selectedIndex];
    return (selected.getAttribute('data-kode') || '') === 'SC';
}

function isSkaSelected() {
    const jenis = document.getElementById('id_jenis');
    const selected = jenis.options[jenis.selectedIndex];
    return (selected.getAttribute('data-kode') || '') === 'SKA';
}

function isSpSelected() {
    const jenis = document.getElementById('id_jenis');
    const selected = jenis.options[jenis.selectedIndex];
    return (selected.getAttribute('data-kode') || '') === 'SP';
}

function isSptSelected() {
    const jenis = document.getElementById('id_jenis');
    const selected = jenis.options[jenis.selectedIndex];
    return (selected.getAttribute('data-kode') || '') === 'SPT';
}

function isSrSelected() {
    const jenis = document.getElementById('id_jenis');
    const selected = jenis.options[jenis.selectedIndex];
    return (selected.getAttribute('data-kode') || '') === 'SR';
}

function isSkkSelected() {
    const jenis = document.getElementById('id_jenis');
    const selected = jenis.options[jenis.selectedIndex];
    return (selected.getAttribute('data-kode') || '') === 'SKK';
}

function isBaSelected() {
    const jenis = document.getElementById('id_jenis');
    const selected = jenis.options[jenis.selectedIndex];
    return (selected.getAttribute('data-kode') || '') === 'BA';
}

function isSjSelected() {
    const jenis = document.getElementById('id_jenis');
    const selected = jenis.options[jenis.selectedIndex];
    return (selected.getAttribute('data-kode') || '') === 'SJ';
}

function isMmSelected() {
    const jenis = document.getElementById('id_jenis');
    const selected = jenis.options[jenis.selectedIndex];
    return (selected.getAttribute('data-kode') || '') === 'MM';
}

function toggleEditFields() {
    const isSpk = isSpkSelected();
    const isSt = isStSelected();
    const isSc = isScSelected();
    const isSka = isSkaSelected();
    const isSp = isSpSelected();
    const isSpt = isSptSelected();
    const isSr = isSrSelected();
    const isSkk = isSkkSelected();
    const isBa = isBaSelected();
    const isSj = isSjSelected();
    const isMm = isMmSelected();
    const isSpecial = isSpk || isSt || isSc || isSka || isSp || isSpt || isSr || isSkk || isBa || isSj || isMm;

    document.querySelectorAll('#umum-fields input, #umum-fields textarea, #umum-fields select').forEach(el => {
        el.disabled = isSpecial;
    });

    document.querySelectorAll('#spk-fields input, #spk-fields textarea, #spk-fields select').forEach(el => {
        el.disabled = !isSpk;
    });

    document.querySelectorAll('#st-fields input, #st-fields textarea, #st-fields select').forEach(el => {
        el.disabled = !isSt;
    });

    document.querySelectorAll('#sc-fields input, #sc-fields textarea, #sc-fields select').forEach(el => {
        el.disabled = !isSc;
    });

    document.querySelectorAll('#ska-fields input, #ska-fields textarea, #ska-fields select').forEach(el => {
        el.disabled = !isSka;
    });

    document.querySelectorAll('#sp-fields input, #sp-fields textarea, #sp-fields select').forEach(el => {
        el.disabled = !isSp;
    });

    document.querySelectorAll('#spt-fields input, #spt-fields textarea, #spt-fields select').forEach(el => {
        el.disabled = !isSpt;
    });

    document.querySelectorAll('#sr-fields input, #sr-fields textarea, #sr-fields select').forEach(el => {
        el.disabled = !isSr;
    });

    document.querySelectorAll('#skk-fields input, #skk-fields textarea, #skk-fields select').forEach(el => {
        el.disabled = !isSkk;
    });

    document.querySelectorAll('#ba-fields input, #ba-fields textarea, #ba-fields select').forEach(el => {
        el.disabled = !isBa;
    });

    document.querySelectorAll('#sj-fields input, #sj-fields textarea, #sj-fields select').forEach(el => {
        el.disabled = !isSj;
    });

    document.querySelectorAll('#mm-fields input, #mm-fields textarea, #mm-fields select').forEach(el => {
        el.disabled = !isMm;
    });

    document.getElementById('umum-fields').style.display = isSpecial ? 'none' : 'block';
    document.getElementById('spk-fields').style.display = isSpk ? 'block' : 'none';
    document.getElementById('st-fields').style.display = isSt ? 'block' : 'none';
    document.getElementById('sc-fields').style.display = isSc ? 'block' : 'none';
    document.getElementById('ska-fields').style.display = isSka ? 'block' : 'none';
    document.getElementById('sp-fields').style.display = isSp ? 'block' : 'none';
    document.getElementById('spt-fields').style.display = isSpt ? 'block' : 'none';
    document.getElementById('sr-fields').style.display = isSr ? 'block' : 'none';
    document.getElementById('skk-fields').style.display = isSkk ? 'block' : 'none';
    document.getElementById('ba-fields').style.display = isBa ? 'block' : 'none';
    document.getElementById('sj-fields').style.display = isSj ? 'block' : 'none';
    document.getElementById('mm-fields').style.display = isMm ? 'block' : 'none';

    if (isSpk) {
        buildSpkIsi();
    } else if (isSt) {
        buildStIsi();
    } else if (isSc) {
        buildScIsi();
    } else if (isSka) {
        buildSkaIsi();
    } else if (isSp) {
        buildSpIsi();
    } else if (isSpt) {
        buildSptIsi();
    } else if (isSr) {
        buildSrIsi();
    } else if (isSkk) {
        buildSkkIsi();
    } else if (isBa) {
        buildBaIsi();
    } else if (isSj) {
        buildSjIsi();
    } else if (isMm) {
        toggleMemoModel();
        buildMmIsi();
    } else {
        document.getElementById('isi_surat_generated').value = '';
    }
}

function buildCrewDropdown(optionsId = 'st_crew_options') {
    const wrap = document.getElementById(optionsId);
    if (!wrap) return;
    wrap.innerHTML = '';
    crewSearchOptions.forEach(item => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'crew-option';
        button.textContent = item.label;
        button.dataset.id = item.id;
        button.dataset.label = item.label;
        const targetMap = {
            st_crew_options: ['st_crew_search', 'st_id_crew'],
            ska_crew_options: ['ska_crew_search', 'ska_id_crew'],
            sp_crew_options: ['sp_crew_search', 'sp_id_crew'],
            sr_crew_options: ['sr_crew_search', 'sr_id_crew'],
            skk_crew_options: ['skk_crew_search', 'skk_id_crew'],
            ba_crew_options: ['ba_crew_search', 'ba_id_crew']
            ,sj_crew_options: ['sj_crew_search', 'sj_id_crew']
        };
        const target = targetMap[optionsId] || ['st_crew_search', 'st_id_crew'];
        button.addEventListener('click', () => selectCrewOption(item.id, item.label, target[0], optionsId, target[1]));
        wrap.appendChild(button);
    });
}

function showCrewDropdown(inputId = 'st_crew_search', optionsId = 'st_crew_options') {
    const wrap = document.getElementById(optionsId);
    if (!wrap) return;
    filterCrewOptions(inputId, optionsId);
    wrap.style.display = 'block';
}

function filterCrewOptions(inputId = 'st_crew_search', optionsId = 'st_crew_options') {
    const query = document.getElementById(inputId).value.toLowerCase();
    const wrap = document.getElementById(optionsId);
    if (!wrap) return;
    const items = wrap.querySelectorAll('.crew-option');
    let visible = 0;
    items.forEach(item => {
        const match = item.textContent.toLowerCase().includes(query);
        item.style.display = match ? 'block' : 'none';
        if (match) visible++;
    });
    wrap.style.display = visible ? 'block' : 'none';
}

function selectCrewOption(id, label, inputId = 'st_crew_search', optionsId = 'st_crew_options', hiddenId = 'st_id_crew') {
    const inputEl = document.getElementById(inputId);
    const optionsEl = document.getElementById(optionsId);
    const hiddenEl = document.getElementById(hiddenId);
    if (inputEl) inputEl.value = label;
    if (optionsEl) optionsEl.style.display = 'none';
    if (hiddenEl) hiddenEl.value = id;
    getCrewData(id);
}

function onCrewSelect(value) {
    const options = document.querySelectorAll('#crew-list option');
    let crewId = '';
    options.forEach(opt => {
        if (opt.value === value) {
            crewId = opt.getAttribute('data-id');
        }
    });
    document.getElementById('id_crew').value = crewId;
    getCrewData(crewId);
}

function getCrewData(id_crew) {
    if (!id_crew) return;

    fetch('<?= BASE_URL ?>/ajax/getCrewData/' + id_crew)
        .then(res => res.json())
        .then(data => {
            selectedCrewData = data;
            setCrewChips(data);
            if (isSpkSelected()) {
                buildSpkIsi();
            } else if (isStSelected()) {
                applyCrewDataToStForm(data);
                buildStIsi();
            } else if (isSkaSelected()) {
                applyCrewDataToSkaForm(data);
                buildSkaIsi();
            } else if (isSpSelected()) {
                applyCrewDataToSpForm(data);
                buildSpIsi();
            } else if (isSrSelected()) {
                applyCrewDataToSrForm(data);
                buildSrIsi();
            } else if (isSkkSelected()) {
                applyCrewDataToSkkForm(data);
                buildSkkIsi();
            } else if (isBaSelected()) {
                applyCrewDataToBaForm(data);
                buildBaIsi();
            } else if (isSjSelected()) {
                applyCrewDataToSjForm(data);
                buildSjIsi();
            }
        });
}

function setCrewChips(data) {
    document.getElementById('chip_jabatan').textContent = 'Jabatan: ' + ((data && data.posisi) || '-');
    document.getElementById('chip_badge').textContent = 'Badge: ' + ((data && data.nomor_badge) || '-');
    document.getElementById('chip_rig').textContent = 'Rig: ' + ((data && data.kode_rig) || '-');
    document.getElementById('chip_crew').textContent = 'Crew: ' + ((data && data.crew) || '-');
}

function applyCrewDataToStForm(data) {
    if (!data) return;
    const stName = document.getElementById('st_name');
    const stDepartment = document.getElementById('st_department');
    const stPosition = document.getElementById('st_position');
    if (stName) stName.value = data.nama || '';
    if (stDepartment) stDepartment.value = formatStDepartment(data.kode_rig, data.crew);
    if (stPosition) stPosition.value = data.posisi || '';
}

function applyCrewDataToSkaForm(data) {
    if (!data) return;
    const skaName = document.getElementById('ska_employee_name');
    const skaPosition = document.getElementById('ska_employee_title');
    if (skaName) skaName.value = data.nama || '';
    if (skaPosition) skaPosition.value = data.posisi || '';
}

function applyCrewDataToSpForm(data) {
    if (!data) return;
    const spName = document.getElementById('sp_employee_name');
    const spPosition = document.getElementById('sp_position');
    const spBadge = document.getElementById('sp_badge');
    const spRig = document.getElementById('sp_rig');
    const spCrew = document.getElementById('sp_crew');
    if (spName) spName.value = data.nama || '';
    if (spPosition) spPosition.value = data.posisi || '';
    if (spBadge) spBadge.value = data.nomor_badge || '';
    if (spRig) spRig.value = data.kode_rig || '';
    if (spCrew) spCrew.value = data.crew || '';
    setSpCrewChips(data);
}

function applyCrewDataToSrForm(data) {
    if (!data) return;
    const srTujuan = document.getElementById('sr_tujuan');
    const srPositions = document.getElementById('sr_positions');
    const srRigs = document.getElementById('sr_rigs');
    if (srTujuan) srTujuan.value = data.nama || '';
    if (srPositions) srPositions.value = data.posisi || '';
    if (srRigs) srRigs.value = data.kode_rig || '';
}

function applyCrewDataToSkkForm(data) {
    if (!data) return;
    const skkName = document.getElementById('skk_name');
    const skkPosition = document.getElementById('skk_position');
    const skkTujuan = document.getElementById('skk_tujuan');
    if (skkName) skkName.value = data.nama || '';
    if (skkPosition) skkPosition.value = data.posisi || '';
    if (skkTujuan) skkTujuan.value = data.nama || '';
}

function applyCrewDataToBaForm(data) {
    if (!data) return;
    const baName = document.getElementById('ba_name');
    const baPosition = document.getElementById('ba_position');
    const baRig = document.getElementById('ba_rig');
    const baTujuan = document.getElementById('ba_tujuan');
    if (baName) baName.value = data.nama || '';
    if (baPosition) baPosition.value = data.posisi || '';
    if (baRig) baRig.value = data.kode_rig || '';
    if (baTujuan) baTujuan.value = data.nama || '';
}

function applyCrewDataToSjForm(data) {
    if (!data) return;
    const sjName = document.getElementById('sj_p1_name');
    const sjPosition = document.getElementById('sj_p1_position');
    const sjAddress = document.getElementById('sj_p1_address');
    const sjDomicile = document.getElementById('sj_p1_domicile');
    if (sjName) sjName.value = data.nama || '';
    if (sjPosition) sjPosition.value = data.posisi || '';
    if (sjAddress) sjAddress.value = data.alamat || '';
    if (sjDomicile) sjDomicile.value = data.alamat || '';
}

function setSpCrewChips(data = null) {
    const values = data || {};
    document.getElementById('sp_chip_nama').textContent = 'Nama: ' + (values.nama || '-');
    document.getElementById('sp_chip_badge').textContent = 'Badge: ' + (values.nomor_badge || '-');
    document.getElementById('sp_chip_posisi').textContent = 'Posisi: ' + (values.posisi || '-');
    document.getElementById('sp_chip_rig').textContent = 'Rig: ' + (values.kode_rig || '-');
    document.getElementById('sp_chip_crew').textContent = 'Crew: ' + (values.crew || '-');
}

function formatStDepartment(rig, crew) {
    const rigValue = (rig || '').trim();
    const crewValue = (crew || '').toString().trim();
    return [rigValue, crewValue].filter(Boolean).join(', ');
}

function formatDateId(value, withDay = false) {
    if (!value) return '';
    const d = new Date(value + 'T00:00:00');
    if (isNaN(d)) return value;
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const dateText = d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    return withDay ? days[d.getDay()] + ', ' + dateText : dateText;
}

function translateIssueToEnglish(text) {
    if (!text) return '';

    let translated = text.trim();
    translated = translated
        .replace(/\bkurangnya\b/gi, 'a lack of')
        .replace(/\bperencanaan\b/gi, 'planning')
        .replace(/\bdalam bekerja\b/gi, 'in work')
        .replace(/\bkinerja\b/gi, 'performance')
        .replace(/\btidak memenuhi standar\b/gi, 'not meeting standards')
        .replace(/\bstandar\b/gi, 'standards')
        .replace(/\bbaik\b/gi, 'both')
        .replace(/\btim\b/gi, 'team')
        .replace(/\bindividu\b/gi, 'individual')
        .replace(/\bpeningkatan\b/gi, 'improvement')
        .replace(/\bsignifikan\b/gi, 'significant');

    return translated.charAt(0).toUpperCase() + translated.slice(1);
}

function buildScIsi() {
    if (!isScSelected()) return;

    const values = {
        tujuan: document.getElementById('sc_tujuan').value.trim(),
        positions: document.getElementById('sc_positions').value.trim(),
        rigs: document.getElementById('sc_rigs').value.trim(),
        purpose: document.getElementById('sc_purpose').value.trim(),
        callDate: document.getElementById('sc_call_date').value,
        callTime: document.getElementById('sc_call_time').value,
        letterDate: document.getElementById('sc_letter_date').value,
        place: document.getElementById('sc_place').value.trim()
    };

    const callTime = values.callTime ? values.callTime.replace(':', '.') + ' Wib' : '10.00 Wib';
    const content = [
        'Kepada: ' + (values.tujuan || 'All Crew'),
        'Positions: ' + (values.positions || 'Roustabout, Roomboy, AccesControl'),
        'Rigs: ' + (values.rigs || 'GW339, GW338, GW337, GW336'),
        'Purpose: ' + (values.purpose || 'menandatangani ulang perjanjian kontrak kerja (Revisi PKWT yang lama), karena terdapat perubahan pada struktur gaji baru dan hal lainnya'),
        'Call Date: ' + (formatDateId(values.callDate, true) || 'Kamis, 24 Oktober 2024'),
        'Call Time: ' + callTime,
        'Letter Date: ' + (formatDateId(values.letterDate) || '23 Oktober 2024'),
        'Place: ' + (values.place || 'Duri')
    ].join('\n');

    document.getElementById('isi_surat').value = content;
    document.getElementById('isi_surat_generated').value = content;
    updateScPreview(values, callTime);
}

function updateScPreview(values = null, callTime = null) {
    if (!isScSelected()) return;

    values = values || {
        tujuan: document.getElementById('sc_tujuan').value.trim(),
        positions: document.getElementById('sc_positions').value.trim(),
        rigs: document.getElementById('sc_rigs').value.trim(),
        purpose: document.getElementById('sc_purpose').value.trim(),
        callDate: document.getElementById('sc_call_date').value
    };
    callTime = callTime || ((document.getElementById('sc_call_time').value || '10:00').replace(':', '.') + ' Wib');

    document.getElementById('scp_tujuan').textContent = values.tujuan || 'All Crew';
    document.getElementById('scp_positions').textContent = values.positions || '...';
    document.getElementById('scp_rigs').textContent = values.rigs || '...';
    document.getElementById('scp_schedule').textContent = (formatDateId(values.callDate, true) || '...') + ' / ' + callTime;
    document.getElementById('scp_body').textContent =
        'Bersama surat ini kami menyampaikan kepada saudara posisi yang disebutkan diatas agar dapat hadir kekantor, untuk ' +
        (values.purpose || '...') + '. Maka dari itu dihimbau untuk dapat hadir ke kantor pada:';
}

function buildSkaIsi() {
    if (!isSkaSelected()) return;

    const values = {
        statementName: document.getElementById('ska_statement_name').value.trim(),
        statementTitle: document.getElementById('ska_statement_title').value.trim(),
        statementAddress: document.getElementById('ska_statement_address').value.trim(),
        employeeName: document.getElementById('ska_employee_name').value.trim(),
        employeeTitle: document.getElementById('ska_employee_title').value.trim(),
        employeeAddress: document.getElementById('ska_employee_address').value.trim(),
        company: document.getElementById('ska_company').value.trim(),
        startDate: document.getElementById('ska_start_date').value,
        place: document.getElementById('ska_place').value.trim(),
        letterDate: document.getElementById('ska_letter_date').value
    };

    const startDateText = formatDateId(values.startDate);
    const letterDateText = formatDateId(values.letterDate);
    const employeeTitle = values.employeeTitle || '___________________';
    const company = values.company || 'PT Greatwall Drilling Company';
    const activeSentence = 'Merupakan salah satu karyawan kami yang masih aktif bekerja, ditempatkan di ' +
        company + ' sebagai ' + employeeTitle +
        (startDateText ? ' terhitung dari ' + startDateText : '') +
        ' hingga saat ini dan menunjukkan kinerja yang baik.';

    const content = [
        'Judul Surat: SURAT KETERANGAN KERJA',
        'Nama Penandatangan: ' + (values.statementName || 'Amy Faradila'),
        'Jabatan Penandatangan: ' + (values.statementTitle || 'HRD Department'),
        'Alamat Penandatangan: ' + (values.statementAddress || 'Jl. Karang Anyer II, Kec Mandau'),
        'Nama Karyawan: ' + (values.employeeName || '___________________'),
        'Jabatan Karyawan: ' + employeeTitle,
        'Alamat Karyawan: ' + (values.employeeAddress || '___________________'),
        'Perusahaan: ' + company,
        'Tanggal Mulai: ' + (startDateText || ''),
        'Kalimat Aktif: ' + activeSentence,
        'Tempat Surat: ' + (values.place || 'Duri'),
        'Tanggal Surat: ' + (letterDateText || formatDateId('<?= date('Y-m-d') ?>')),
        'Signer: ' + (values.statementName || 'Amy Faradila'),
        'Signer Title: ' + (values.statementTitle || 'HRD Department')
    ].join('\n');

    document.getElementById('isi_surat').value = content;
    document.getElementById('isi_surat_generated').value = content;
    updateSkaPreview(values, activeSentence);
}

function updateSkaPreview(values = null, activeSentence = null) {
    if (!isSkaSelected()) return;

    values = values || {
        statementName: document.getElementById('ska_statement_name').value.trim(),
        employeeName: document.getElementById('ska_employee_name').value.trim(),
        employeeTitle: document.getElementById('ska_employee_title').value.trim(),
        company: document.getElementById('ska_company').value.trim()
    };

    document.getElementById('skap_statement_name').textContent = values.statementName || 'Amy Faradila';
    document.getElementById('skap_employee_name').textContent = values.employeeName || '...';
    document.getElementById('skap_employee_title').textContent = values.employeeTitle || '...';
    document.getElementById('skap_company').textContent = values.company || 'PT Greatwall Drilling Company';
    document.getElementById('skap_body').textContent = activeSentence || 'Preview akan muncul di sini saat Anda mengisi data.';
}

function buildSptIsi() {
    if (!isSptSelected()) return;

    const values = {
        tujuan: document.getElementById('spt_tujuan').value.trim(),
        perihal: document.getElementById('spt_perihal').value.trim(),
        lampiran: document.getElementById('spt_lampiran').value.trim(),
        paragraph1: document.getElementById('spt_paragraph_1').value.trim(),
        paragraph2: document.getElementById('spt_paragraph_2').value.trim(),
        paragraph3: document.getElementById('spt_paragraph_3').value.trim(),
        closing: document.getElementById('spt_closing').value.trim(),
        place: document.getElementById('spt_place').value.trim(),
        letterDate: document.getElementById('spt_letter_date').value,
        signer: document.getElementById('spt_signer').value.trim(),
        signerTitle: document.getElementById('spt_signer_title').value.trim()
    };

    const content = [
        'Kepada: ' + (values.tujuan || 'Crew GWAP 339'),
        'Perihal: ' + (values.perihal || 'Pemberitahuan'),
        'Lampiran: ' + (values.lampiran || '1'),
        'Paragraf 1: ' + (values.paragraph1 || ''),
        'Paragraf 2: ' + (values.paragraph2 || ''),
        'Paragraf 3: ' + (values.paragraph3 || ''),
        'Penutup: ' + (values.closing || ''),
        'Tempat Surat: ' + (values.place || 'Duri'),
        'Tanggal Surat: ' + (formatDateId(values.letterDate) || formatDateId('<?= date('Y-m-d') ?>')),
        'Signer: ' + (values.signer || 'Y. Chandra'),
        'Signer Title: ' + (values.signerTitle || 'Project Manager')
    ].join('\n');

    document.getElementById('isi_surat').value = content;
    document.getElementById('isi_surat_generated').value = content;
    updateSptPreview(values);
}

function updateSptPreview(values = null) {
    if (!isSptSelected()) return;

    values = values || {
        tujuan: document.getElementById('spt_tujuan').value.trim(),
        lampiran: document.getElementById('spt_lampiran').value.trim(),
        paragraph1: document.getElementById('spt_paragraph_1').value.trim(),
        letterDate: document.getElementById('spt_letter_date').value
    };

    document.getElementById('sptp_tujuan').textContent = values.tujuan || 'Crew GWAP 339';
    document.getElementById('sptp_lampiran').textContent = values.lampiran || '1';
    document.getElementById('sptp_date').textContent = formatDateId(values.letterDate) || '...';
    document.getElementById('sptp_body').textContent = values.paragraph1 || 'Preview akan muncul di sini saat Anda mengisi data.';
}

function buildStIsi() {
    if (!isStSelected()) return;

    const values = {
        name: document.getElementById('st_name').value.trim(),
        department: document.getElementById('st_department').value.trim(),
        status: document.getElementById('st_status').value.trim(),
        position: document.getElementById('st_position').value.trim(),
        dateEntry: document.getElementById('st_date_entry').value,
        dateEval: document.getElementById('st_date_eval').value,
        evalMonths: document.getElementById('st_eval_months').value.trim(),
        company: document.getElementById('st_company').value.trim(),
        requestBy: document.getElementById('st_request_by').value.trim(),
        requestTitle: document.getElementById('st_request_title').value.trim(),
        approvedBy: document.getElementById('st_approved_by').value.trim(),
        approvedTitle: document.getElementById('st_approved_title').value.trim(),
        issueId: document.getElementById('st_issue_id').value.trim(),
        issueEn: document.getElementById('st_issue_en').value.trim()
    };

    const issueId = values.issueId || 'kurangnya perencanaan dalam bekerja dan kinerja yang tidak memenuhi standar, baik dalam konteks tim maupun individu';
    const issueEn = translateIssueToEnglish(issueId);

    const formatDate = (value) => {
        if (!value) return '';
        const d = new Date(value);
        return isNaN(d) ? value : d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    };

    const content = [
        'Name: ' + (values.name || '___________________'),
        'Department: ' + (values.department || 'GWDC 338, C'),
        'Employee Status: ' + (values.status || 'Contract'),
        'Position: ' + (values.position || '___________________'),
        'Date of entry: ' + (formatDate(values.dateEntry) || '___________________'),
        'Date of evaluation: ' + (formatDate(values.dateEval) || '___________________'),
        'Evaluation Months: ' + (values.evalMonths || '5'),
        'Performance Issue ID: ' + issueId,
        'Performance Issue EN: ' + issueEn,
        'Company: ' + (values.company || 'PT. SIGMA'),
        'Request By: ' + (values.requestBy || 'Bendri Zona'),
        'Request By Title: ' + (values.requestTitle || 'Chief Mechanic'),
        'Approved By: ' + (values.approvedBy || 'Zhang Gang'),
        'Approved By Title: ' + (values.approvedTitle || 'Rig Manager')
    ].join('\n');

    document.getElementById('isi_surat').value = content;
    document.getElementById('isi_surat_generated').value = content;
    document.getElementById('st_issue_en').value = issueEn;
    document.getElementById('st_issue_en_preview').value = issueEn;
    updateStPreview();
}

function updateStPreview() {
    if (!isStSelected()) return;

    const values = {
        name: document.getElementById('st_name').value.trim() || '...',
        department: document.getElementById('st_department').value.trim() || '...',
        status: document.getElementById('st_status').value.trim() || '...',
        position: document.getElementById('st_position').value.trim() || '...',
        issueId: document.getElementById('st_issue_id').value.trim() || '...'
    };

    document.getElementById('stp_name').textContent = values.name;
    document.getElementById('stp_department').textContent = values.department;
    document.getElementById('stp_status').textContent = values.status;
    document.getElementById('stp_position').textContent = values.position;
    document.getElementById('stp_body').textContent = values.issueId;
}

function buildSpIsi() {
    if (!isSpSelected()) return;

    const values = {
        name: document.getElementById('sp_employee_name').value.trim(),
        badge: document.getElementById('sp_badge').value.trim(),
        position: document.getElementById('sp_position').value.trim(),
        rig: document.getElementById('sp_rig').value.trim(),
        crew: document.getElementById('sp_crew').value.trim(),
        level: document.getElementById('sp_level').value,
        letterDate: document.getElementById('sp_letter_date').value,
        effectiveStart: document.getElementById('sp_effective_start').value,
        effectiveEnd: document.getElementById('sp_effective_end').value,
        place: document.getElementById('sp_place').value.trim(),
        violationTitle: document.getElementById('sp_violation_title').value.trim(),
        violationDetail: document.getElementById('sp_violation_detail').value.trim(),
        disciplineAction: document.getElementById('sp_discipline_action').value.trim(),
        signer: document.getElementById('sp_signer').value.trim(),
        signerTitle: document.getElementById('sp_signer_title').value.trim()
    };

    const title = 'SURAT PERINGATAN ' + (values.level || 'PERTAMA').toUpperCase();
    const content = [
        'Judul Surat: ' + title,
        'Tingkat Peringatan: ' + (values.level || 'Pertama'),
        'Nama Karyawan: ' + (values.name || '___________________'),
        'Jabatan Karyawan: ' + (values.position || '___________________'),
        'No Badge: ' + (values.badge || '-'),
        'Rig: ' + (values.rig || ''),
        'Crew: ' + (values.crew || ''),
        'Judul Pelanggaran: ' + (values.violationTitle || 'TENTANG KEDISIPLINAN, MELANGGAR ATURAN PERUSAHAAN, ETIKA KINERJA YANG SIGNIFIKAN'),
        'Detail Pelanggaran: ' + (values.violationDetail || ''),
        'Tindakan Disiplin: ' + (values.disciplineAction || ''),
        'Masa Berlaku Mulai: ' + (formatDateId(values.effectiveStart) || ''),
        'Masa Berlaku Sampai: ' + (formatDateId(values.effectiveEnd) || ''),
        'Tempat Surat: ' + (values.place || 'Duri'),
        'Tanggal Surat: ' + (formatDateId(values.letterDate) || formatDateId('<?= date('Y-m-d') ?>')),
        'Signer: ' + (values.signer || 'Amy Faradila'),
        'Signer Title: ' + (values.signerTitle || 'HRD Dept')
    ].join('\n');

    document.getElementById('sp_perihal').value = title;
    document.getElementById('sp_tujuan').value = values.name || '';
    document.getElementById('isi_surat').value = content;
    document.getElementById('isi_surat_generated').value = content;
    updateSpPreview(values, title);
}

function updateSpPreview(values = null, title = null) {
    if (!isSpSelected()) return;

    values = values || {
        name: document.getElementById('sp_employee_name').value.trim(),
        badge: document.getElementById('sp_badge').value.trim(),
        position: document.getElementById('sp_position').value.trim(),
        level: document.getElementById('sp_level').value,
        effectiveStart: document.getElementById('sp_effective_start').value,
        effectiveEnd: document.getElementById('sp_effective_end').value,
        violationDetail: document.getElementById('sp_violation_detail').value.trim(),
        disciplineAction: document.getElementById('sp_discipline_action').value.trim()
    };

    title = title || 'SURAT PERINGATAN ' + (values.level || 'PERTAMA').toUpperCase();
    const effectiveText = [formatDateId(values.effectiveStart), formatDateId(values.effectiveEnd)]
        .filter(Boolean)
        .join(' s/d ');

    document.getElementById('spp_title').textContent = title;
    document.getElementById('spp_name').textContent = values.name || '...';
    document.getElementById('spp_badge').textContent = values.badge || '...';
    document.getElementById('spp_position').textContent = values.position || '...';
    document.getElementById('spp_effective').textContent = effectiveText || '...';
    document.getElementById('spp_body').textContent =
        (values.violationDetail || 'Detail pelanggaran belum diisi.') + ' ' +
        (values.disciplineAction || '');
}

function buildSpkIsi() {
    if (!isSpkSelected()) return;

    const data = selectedCrewData || {};
    const posisi = data.posisi || '___________________';
    const nama = data.nama || '___________________';
    const badge = data.nomor_badge || '-';
    const rig = data.kode_rig || '___________________';
    const crew = data.crew ? ' crew ' + data.crew : '';
    const posisiLengkap = [posisi, rig].filter(Boolean).join(' ') + crew;
    const mengganti = document.getElementById('posisi_vacant').value || '___________________';

    const generatedContent =
        'Bersama ini kami kirimkan pengganti ' + posisi + ' dengan data:\n\n' +
        '1. Nama      : ' + nama + '\n' +
        '   Jabatan   : ' + posisi + '\n' +
        '   No Badge  : ' + badge + '\n\n' +
        'Dengan ini, menyatakan bahwa nama yang bersangkutan dipermanenkan di posisi ' +
        posisiLengkap + ' atas nama ' + nama + ' menggantikan posisi ' + mengganti + '.\n\n' +
        'Demikian yang dapat disampaikan, atas perhatian dan kerjasama kami ucapkan Terima kasih.';

    document.getElementById('isi_surat').value = generatedContent;
    document.getElementById('isi_surat_generated').value = generatedContent;

    updateSpkPreview(posisi, nama, posisiLengkap, mengganti);
}

function updateSpkPreview(posisi = null, nama = null, posisiLengkap = null, mengganti = null) {
    const tujuan = document.getElementById('tujuan_spk').value || 'DSR Rig GWDC';
    const perihal = document.getElementById('perihal_spk').value || 'Surat Pengantar Crew Pengganti';
    const data = selectedCrewData || {};
    const previewPosisi = posisi || data.posisi || '...';
    const previewNama = nama || data.nama || '...';
    const previewRig = data.kode_rig || '...';
    const previewCrew = data.crew ? ' crew ' + data.crew : '';
    const previewPosisiLengkap = posisiLengkap || [previewPosisi, previewRig].filter(Boolean).join(' ') + previewCrew;
    const previewMengganti = mengganti || document.getElementById('posisi_vacant').value || '...';

    document.getElementById('preview_tujuan').textContent = tujuan;
    document.getElementById('preview_perihal').textContent = perihal;
    document.getElementById('preview_intro').textContent =
        'Bersama ini kami kirimkan pengganti ' + previewPosisi + ' dengan data:';
    document.getElementById('preview_nama').textContent = previewNama;
    document.getElementById('preview_jabatan').textContent = previewPosisi;
    document.getElementById('preview_badge').textContent = data.nomor_badge || '-';
    document.getElementById('preview_permanen').textContent =
        'Dengan ini, menyatakan bahwa nama yang bersangkutan dipermanenkan di posisi ' +
        previewPosisiLengkap + ' atas nama ' + previewNama + ' menggantikan posisi ' + previewMengganti + '.';
}

function buildSrIsi() {
    if (!isSrSelected()) return;

    const values = {
        tujuan: document.getElementById('sr_tujuan').value.trim(),
        positions: document.getElementById('sr_positions').value.trim(),
        rigs: document.getElementById('sr_rigs').value.trim(),
        letterDate: document.getElementById('sr_letter_date').value,
        place: document.getElementById('sr_place').value.trim(),
        lampiran: document.getElementById('sr_lampiran').value.trim(),
        signer: document.getElementById('sr_signer').value.trim()
    };

    const content = [
        'Kepada: ' + (values.tujuan || 'All Crew'),
        'Posisi: ' + (values.positions || ''),
        'Rigs: ' + (values.rigs || ''),
        'Tanggal Surat: ' + (formatDateId(values.letterDate) || formatDateId('<?= date('Y-m-d') ?>')),
        'Tempat Surat: ' + (values.place || 'Duri'),
        'Lampiran: ' + (values.lampiran || '1'),
        'Signer: ' + (values.signer || 'Amy Faradila'),
        'Perihal: ' + (document.getElementById('sr_perihal').value || 'Ketentuan Pengunduran Diri (Resign) dan Penalty Kontrak')
    ].join('\n');

    document.getElementById('isi_surat').value = content;
    document.getElementById('isi_surat_generated').value = content;
    updateSrPreview(values);
}

function updateSrPreview(values = null) {
    if (!isSrSelected()) return;
    values = values || {
        tujuan: document.getElementById('sr_tujuan').value.trim(),
        positions: document.getElementById('sr_positions').value.trim(),
        rigs: document.getElementById('sr_rigs').value.trim()
    };
    document.getElementById('srp_tujuan').textContent = values.tujuan || 'All Crew';
    document.getElementById('srp_positions').textContent = values.positions || '...';
    document.getElementById('srp_rigs').textContent = values.rigs || '...';
    document.getElementById('srp_body').textContent = 'Ketentuan resign dan penalty kontrak untuk ' + (values.tujuan || 'All Crew') + '.';
}

function buildSkkIsi() {
    if (!isSkkSelected()) return;

    const values = {
        tujuan: document.getElementById('skk_tujuan').value.trim(),
        positions: document.getElementById('skk_positions').value.trim(),
        rigs: document.getElementById('skk_rigs').value.trim(),
        name: document.getElementById('skk_name').value.trim(),
        position: document.getElementById('skk_position').value.trim(),
        project: document.getElementById('skk_project').value.trim(),
        startDate: document.getElementById('skk_start_date').value,
        letterDate: document.getElementById('skk_letter_date').value,
        place: document.getElementById('skk_place').value.trim()
    };

    const content = [
        'Kepada: ' + (values.tujuan || 'Crew terkait'),
        'Posisi: ' + (values.positions || values.position || ''),
        'Rigs: ' + (values.rigs || ''),
        'Nama: ' + (values.name || ''),
        'Jabatan: ' + (values.position || ''),
        'Project: ' + (values.project || 'Drilling at PT. Greatwall Drilling Asia Pacific'),
        'Mulai Bekerja: ' + (formatDateId(values.startDate) || ''),
        'Tanggal Surat: ' + (formatDateId(values.letterDate) || formatDateId('<?= date('Y-m-d') ?>')),
        'Tempat Surat: ' + (values.place || 'Duri')
    ].join('\n');

    document.getElementById('isi_surat').value = content;
    document.getElementById('isi_surat_generated').value = content;
    updateSkkPreview(values);
}

function updateSkkPreview(values = null) {
    if (!isSkkSelected()) return;
    values = values || {
        name: document.getElementById('skk_name').value.trim(),
        position: document.getElementById('skk_position').value.trim(),
        project: document.getElementById('skk_project').value.trim()
    };
    document.getElementById('skkp_name').textContent = values.name || '...';
    document.getElementById('skkp_position').textContent = values.position || '...';
    document.getElementById('skkp_project').textContent = values.project || '...';
    document.getElementById('skkp_body').textContent = values.name ? 'Surat keterangan kerja untuk ' + values.name + '.' : 'Pilih crew untuk mengisi otomatis.';
}

function buildBaIsi() {
    if (!isBaSelected()) return;

    const values = {
        tujuan: document.getElementById('ba_tujuan').value.trim(),
        positions: document.getElementById('ba_positions').value.trim(),
        rigs: document.getElementById('ba_rigs').value.trim(),
        name: document.getElementById('ba_name').value.trim(),
        position: document.getElementById('ba_position').value.trim(),
        rig: document.getElementById('ba_rig').value.trim(),
        contract: document.getElementById('ba_contract').value.trim(),
        startOjt: document.getElementById('ba_start_ojt').value,
        endOjt: document.getElementById('ba_end_ojt').value,
        assessmentDate: document.getElementById('ba_assessment_date').value,
        result: document.getElementById('ba_result').value,
        place: document.getElementById('ba_place').value.trim(),
        letterDate: document.getElementById('ba_letter_date').value,
        signerDsr: document.getElementById('ba_signer').value.trim(),
        signerRsm: document.getElementById('ba_signer_rsm').value.trim(),
        signerHse: document.getElementById('ba_signer_hse').value.trim()
    };

    const content = [
        'Kepada: ' + (values.tujuan || 'Crew terkait'),
        'Posisi: ' + (values.positions || values.position || ''),
        'Rigs: ' + (values.rigs || values.rig || ''),
        'Nama: ' + (values.name || ''),
        'Jabatan: ' + (values.position || ''),
        'Rig: ' + (values.rig || ''),
        'No Kontrak: ' + (values.contract || 'SPHR00618A'),
        'Start OJT: ' + (formatDateId(values.startOjt) || ''),
        'End OJT: ' + (formatDateId(values.endOjt) || ''),
        'Tanggal Assessment: ' + (formatDateId(values.assessmentDate) || ''),
        'Hasil: ' + (values.result || 'Passed'),
        'Tempat: ' + (values.place || 'Tanggul'),
        'Tanggal Surat: ' + (formatDateId(values.letterDate || values.assessmentDate) || formatDateId('<?= date('Y-m-d') ?>')),
        'Signer DSR: ' + values.signerDsr,
        'Signer RSM: ' + values.signerRsm,
        'Signer HSE: ' + values.signerHse
    ].join('\n');

    document.getElementById('isi_surat').value = content;
    document.getElementById('isi_surat_generated').value = content;
    updateBaPreview(values);
}

function updateBaPreview(values = null) {
    if (!isBaSelected()) return;
    values = values || {
        name: document.getElementById('ba_name').value.trim(),
        rig: document.getElementById('ba_rig').value.trim(),
        result: document.getElementById('ba_result').value
    };
    document.getElementById('bap_name').textContent = values.name || '...';
    document.getElementById('bap_rig').textContent = values.rig || '...';
    document.getElementById('bap_result').textContent = values.result || 'Passed';
}

function initScPositionSelect() {
    if (!window.jQuery || !jQuery.fn.select2) return;
    jQuery('#mm_positions').select2({
        width: '100%',
        placeholder: 'Pilih satu atau lebih posisi',
        closeOnSelect: false
    }).on('change', buildMmIsi);
}

function getSelectedOptionsText(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return '';
    return Array.from(element.selectedOptions)
        .map(option => option.value.trim())
        .filter(Boolean)
        .join(', ');
}

function syncRigsWithTujuan(tujuanId, rigsId) {
    const tujuan = document.getElementById(tujuanId);
    const rigs = document.getElementById(rigsId);
    if (!tujuan || !rigs) return;
    const allRigLabels = ['all rig', 'all rigs', 'semua rig', 'semua rigs'];
    if (allRigLabels.includes(tujuan.value.trim().toLowerCase()) && scAllRigs.length) {
        rigs.value = scAllRigs.join(', ');
    }
}

function buildSjIsi() {
    if (!isSjSelected()) return;
    const values = {
        date: document.getElementById('sj_date').value,
        p1Name: document.getElementById('sj_p1_name').value.trim(),
        p1Birth: document.getElementById('sj_p1_birth').value.trim(),
        p1Address: document.getElementById('sj_p1_address').value.trim(),
        p1Domicile: document.getElementById('sj_p1_domicile').value.trim(),
        p1Position: document.getElementById('sj_p1_position').value.trim(),
        certName: document.getElementById('sj_cert_name').value.trim(),
        fee: document.getElementById('sj_fee').value.trim(),
        deduction: document.getElementById('sj_deduction').value.trim(),
        workPeriod: document.getElementById('sj_work_period').value.trim()
    };
    const content = [
        'Tanggal Perjanjian: ' + (formatDateId(values.date) || formatDateId('<?= date('Y-m-d') ?>')),
        'Pihak Pertama Nama: ' + values.p1Name,
        'Pihak Pertama TTL: ' + values.p1Birth,
        'Pihak Pertama Alamat: ' + values.p1Address,
        'Pihak Pertama Domisili: ' + values.p1Domicile,
        'Pihak Pertama Jabatan: ' + values.p1Position,
        'Pihak Kedua Nama: Widhia Lukman Hakim',
        'Pihak Kedua TTL: Padang, 03 September 1986',
        'Pihak Kedua Alamat: Jl. Asrama Tribat Gg.Patih No.555 Pematang Pudu Mandau Duri',
        'Pihak Kedua Domisili: Jl. Asrama Tribat Gg.Patih No.555 Pematang Pudu Mandau Duri',
        'Pihak Kedua Jabatan: Direktur Utama PT. ADK Enam Indonesia',
        'Nama Sertifikat: ' + (values.certName || 'Asme Welder'),
        'Biaya Sertifikat: ' + (values.fee || 'Rp. 12.000.000'),
        'Potongan Perbulan: ' + (values.deduction || 'Rp. 2.000.000'),
        'Ikatan Kerja: ' + (values.workPeriod || '1 (satu) bulan')
    ].join('\n');
    document.getElementById('isi_surat').value = content;
    document.getElementById('isi_surat_generated').value = content;
    document.getElementById('sjp_name').textContent = values.p1Name || '...';
    document.getElementById('sjp_cert').textContent = values.certName || 'Asme Welder';
    document.getElementById('sjp_fee').textContent = values.fee || 'Rp. 12.000.000';
}

function toggleMemoModel() {
    const isMemorandum = document.getElementById('mm_model').value === 'Memorandum';
    document.querySelectorAll('.memo-memorandum-only').forEach(el => el.style.display = isMemorandum ? '' : 'none');
    document.getElementById('mmp_title').textContent = isMemorandum ? 'MEMORANDUM' : 'MEMO INTERNAL';
}

function buildMmIsi() {
    if (!isMmSelected()) return;
    syncRigsWithTujuan('mm_tujuan', 'mm_rigs');
    const values = {
        model: document.getElementById('mm_model').value,
        perihal: document.getElementById('mm_perihal').value.trim(),
        lampiran: document.getElementById('mm_lampiran').value.trim(),
        tujuan: document.getElementById('mm_tujuan').value.trim(),
        positions: getSelectedOptionsText('mm_positions'),
        rigs: document.getElementById('mm_rigs').value.trim(),
        date: document.getElementById('mm_letter_date').value,
        place: document.getElementById('mm_place').value.trim(),
        intro: document.getElementById('mm_intro').value.trim(),
        points: document.getElementById('mm_points').value.trim(),
        followup: document.getElementById('mm_followup').value.trim(),
        closing: document.getElementById('mm_closing').value.trim(),
        signerHrd: document.getElementById('mm_signer_hrd').value.trim(),
        signerHrdTitle: document.getElementById('mm_signer_hrd_title').value.trim(),
        signerRig: document.getElementById('mm_signer_rig').value.trim(),
        signerRigTitle: document.getElementById('mm_signer_rig_title').value.trim(),
        signerManager: document.getElementById('mm_signer_manager').value.trim(),
        signerManagerTitle: document.getElementById('mm_signer_manager_title').value.trim()
    };
    const content = [
        'Model Memo: ' + values.model,
        'Judul: ' + (values.model === 'Memorandum' ? 'MEMORANDUM' : 'MEMO INTERNAL'),
        'Perihal: ' + values.perihal,
        'Lampiran: ' + (values.lampiran || '-'),
        'Kepada: ' + (values.tujuan || 'Seluruh Karyawan'),
        'Positions: ' + values.positions,
        'Rigs: ' + values.rigs,
        'Dari: HRD Department',
        'Tanggal Surat: ' + (formatDateId(values.date) || formatDateId('<?= date('Y-m-d') ?>')),
        'Tempat Surat: ' + (values.place || 'Duri'),
        'Isi Pembuka: ' + values.intro,
        'Poin Memo: ' + values.points,
        'Isi Lanjutan: ' + values.followup,
        'Penutup: ' + values.closing,
        'Signer HRD: ' + (values.signerHrd || 'Amy Faradila'),
        'Signer HRD Title: ' + (values.signerHrdTitle || 'HR Department'),
        'Signer Rig: ' + values.signerRig,
        'Signer Rig Title: ' + (values.signerRigTitle || 'Rig Superintendent'),
        'Signer Manager: ' + (values.signerManager || 'Area Manager'),
        'Signer Manager Title: ' + (values.signerManagerTitle || 'Area Manager')
    ].join('\n');
    document.getElementById('isi_surat').value = content;
    document.getElementById('isi_surat_generated').value = content;
    document.getElementById('mmp_model').textContent = values.model;
    document.getElementById('mmp_perihal').textContent = values.perihal || '...';
    document.getElementById('mmp_tujuan').textContent = values.tujuan || '...';
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
