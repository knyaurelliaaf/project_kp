<?php
// Partial form untuk SPM, PHK, dan SPK. Variabel $isEdit dan $sura
$templateIsi = $surat['isi_surat'] ?? '';
if (!function_exists('template_surat_value')) {
    function template_surat_value($text, $label, $default = '') {
        if (preg_match('/^' . preg_quote($label, '/') . '\\s*:\\s*(.+)$/im', $text, $match)) return trim($match[1]);
        return $default;
    }
}
$templateDate = $surat['tanggal_moc'] ?? date('Y-m-d');
?>

<div id="spm-fields" class="template-surat-fields" style="display:none;">
    <input type="hidden" name="tujuan" id="spm_tujuan"><input type="hidden" name="perihal" id="spm_perihal">
    <div class="template-surat-panel">
        <h5>Surat Penunjukan Mentor</h5>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Cari Mentor</label><div class="crew-search-wrap"><input id="spm_mentor_search" class="form-control" placeholder="Ketik nama mentor..." autocomplete="off" onfocus="showCrewDropdown('spm_mentor_search','spm_mentor_options')" oninput="filterCrewOptions('spm_mentor_search','spm_mentor_options')"><div id="spm_mentor_options" class="crew-options" style="display:none;"></div></div><input type="hidden" id="spm_mentor_crew_id"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Cari Karyawan/Mentee</label><div class="crew-search-wrap"><input id="spm_mentee_search" class="form-control" placeholder="Ketik nama karyawan..." autocomplete="off" onfocus="showCrewDropdown('spm_mentee_search','spm_mentee_options')" oninput="filterCrewOptions('spm_mentee_search','spm_mentee_options')"><div id="spm_mentee_options" class="crew-options" style="display:none;"></div></div><input type="hidden" id="spm_mentee_crew_id"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Nama Mentor</label><input name="tujuan" id="spm_mentor" class="form-control" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Mentor')) ?>" oninput="buildTemplateSuratIsi()"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Jabatan Mentor</label><input id="spm_mentor_position" class="form-control" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Jabatan Mentor')) ?>" oninput="buildTemplateSuratIsi()"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Nama Karyawan/Mentee</label><input id="spm_mentee" class="form-control" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Mentee')) ?>" oninput="buildTemplateSuratIsi()"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Jabatan Mentee</label><input id="spm_mentee_position" class="form-control" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Jabatan Mentee')) ?>" oninput="buildTemplateSuratIsi()"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Periode Penugasan</label><input id="spm_period" class="form-control" placeholder="Contoh: 01 Agustus s.d. 31 Oktober 2026" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Periode')) ?>" oninput="buildTemplateSuratIsi()"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Penandatangan</label><input id="spm_signer" class="form-control" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Penandatangan', 'HRD Department')) ?>" oninput="buildTemplateSuratIsi()"></div>
            <div class="col-md-12"><label class="form-label fw-semibold">Ruang Lingkup Pendampingan</label><textarea id="spm_scope" class="form-control" rows="3" oninput="buildTemplateSuratIsi()"><?= htmlspecialchars(template_surat_value($templateIsi, 'Ruang Lingkup', 'Memberikan bimbingan, pendampingan, dan evaluasi kerja kepada karyawan yang ditunjuk.')) ?></textarea></div>
        </div>
        <aside class="template-surat-preview"><h5><i class="fas fa-eye"></i> Preview live</h5><div class="template-preview-paper"><div class="template-preview-title">SURAT PENUNJUKAN MENTOR</div><div class="template-preview-number">Nomor: otomatis dari sistem</div><hr><div><b>Kepada:</b> <span id="spmp_mentor">...</span></div><div><b>Mentee:</b> <span id="spmp_mentee">...</span></div><div><b>Periode:</b> <span id="spmp_period">...</span></div><p id="spmp_scope">Isi ruang lingkup pendampingan akan tampil di sini.</p></div></aside>
    </div>
</div>

<div id="phk-fields" class="template-surat-fields" style="display:none;">
    <input type="hidden" name="tujuan" id="phk_tujuan"><input type="hidden" name="perihal" id="phk_perihal">
    <div class="template-surat-panel">
        <h5>Surat Pemutusan Hubungan Kerja</h5>
        <div class="row g-3">
            <div class="col-md-12"><label class="form-label fw-semibold">Cari Karyawan</label><div class="crew-search-wrap"><input id="phk_crew_search" class="form-control" placeholder="Ketik nama karyawan..." autocomplete="off" onfocus="showCrewDropdown('phk_crew_search','phk_crew_options')" oninput="filterCrewOptions('phk_crew_search','phk_crew_options')"><div id="phk_crew_options" class="crew-options" style="display:none;"></div></div><input type="hidden" id="phk_crew_id"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Nama Karyawan</label><input name="tujuan" id="phk_name" class="form-control" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Nama Karyawan')) ?>" oninput="buildTemplateSuratIsi()"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Jabatan</label><input id="phk_position" class="form-control" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Jabatan')) ?>" oninput="buildTemplateSuratIsi()"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Nomor Badge</label><input id="phk_badge" class="form-control" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Nomor Badge')) ?>" oninput="buildTemplateSuratIsi()"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Efektif</label><input id="phk_effective" type="date" class="form-control" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Tanggal Efektif', $templateDate)) ?>" oninput="buildTemplateSuratIsi()"></div>
            <div class="col-md-12"><label class="form-label fw-semibold">Alasan / Dasar PHK</label><textarea id="phk_reason" class="form-control" rows="3" oninput="buildTemplateSuratIsi()"><?= htmlspecialchars(template_surat_value($templateIsi, 'Alasan')) ?></textarea></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Penandatangan</label><input id="phk_signer" class="form-control" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Penandatangan', 'HRD Department')) ?>" oninput="buildTemplateSuratIsi()"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Jabatan Penandatangan</label><input id="phk_signer_title" class="form-control" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Jabatan Penandatangan', 'Human Resources Department')) ?>" oninput="buildTemplateSuratIsi()"></div>
        </div>
        <aside class="template-surat-preview"><h5><i class="fas fa-eye"></i> Preview live</h5><div class="template-preview-paper"><div class="template-preview-title">SURAT PEMUTUSAN HUBUNGAN KERJA</div><div class="template-preview-number">Nomor: otomatis dari sistem</div><hr><div><b>Kepada:</b> <span id="phkp_name">...</span></div><div><b>Jabatan:</b> <span id="phkp_position">...</span></div><div><b>Efektif:</b> <span id="phkp_effective">...</span></div><p id="phkp_reason">Alasan atau dasar PHK akan tampil di sini.</p></div></aside>
    </div>
</div>

<div id="spk-task-fields" class="template-surat-fields" style="display:none;">
    <input type="hidden" name="tujuan" id="spk_task_tujuan"><input type="hidden" name="perihal" id="spk_task_perihal">
    <div class="template-surat-panel">
        <h5>Surat Tugas</h5>
        <div class="row g-3">
            <div class="col-md-12"><label class="form-label fw-semibold">Cari Personel</label><div class="crew-search-wrap"><input id="spk_task_crew_search" class="form-control" placeholder="Ketik nama personel..." autocomplete="off" onfocus="showCrewDropdown('spk_task_crew_search','spk_task_crew_options')" oninput="filterCrewOptions('spk_task_crew_search','spk_task_crew_options')"><div id="spk_task_crew_options" class="crew-options" style="display:none;"></div></div><input type="hidden" id="spk_task_crew_id"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Nama Personel</label><input name="tujuan" id="spk_task_name" class="form-control" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Nama Personel')) ?>" oninput="buildTemplateSuratIsi()"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Jabatan</label><input id="spk_task_position" class="form-control" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Jabatan')) ?>" oninput="buildTemplateSuratIsi()"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Lokasi / Rig</label><input id="spk_task_location" class="form-control" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Lokasi')) ?>" oninput="buildTemplateSuratIsi()"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Periode Tugas</label><input id="spk_task_period" class="form-control" placeholder="Contoh: 20–25 Juli 2026" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Periode')) ?>" oninput="buildTemplateSuratIsi()"></div>
            <div class="col-md-12"><label class="form-label fw-semibold">Uraian Tugas</label><textarea id="spk_task_description" class="form-control" rows="3" oninput="buildTemplateSuratIsi()"><?= htmlspecialchars(template_surat_value($templateIsi, 'Uraian Tugas')) ?></textarea></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Penandatangan</label><input id="spk_task_signer" class="form-control" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Penandatangan', 'Project Manager')) ?>" oninput="buildTemplateSuratIsi()"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Jabatan Penandatangan</label><input id="spk_task_signer_title" class="form-control" value="<?= htmlspecialchars(template_surat_value($templateIsi, 'Jabatan Penandatangan', 'Project Manager')) ?>" oninput="buildTemplateSuratIsi()"></div>
        </div>
        <aside class="template-surat-preview"><h5><i class="fas fa-eye"></i> Preview live</h5><div class="template-preview-paper"><div class="template-preview-title">SURAT TUGAS</div><div class="template-preview-number">Nomor: otomatis dari sistem</div><hr><div><b>Kepada:</b> <span id="spkp_name">...</span></div><div><b>Jabatan:</b> <span id="spkp_position">...</span></div><div><b>Lokasi/Rig:</b> <span id="spkp_location">...</span></div><div><b>Periode:</b> <span id="spkp_period">...</span></div><p id="spkp_description">Uraian tugas akan tampil di sini.</p></div></aside>
    </div>
</div>

<style>
    .template-surat-panel { display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(310px, .95fr); gap: 20px; border: 0; margin-top: 8px; }
    .template-surat-panel > h5 { grid-column: 1 / -1; margin: 0; }
    .template-surat-panel > .row, .template-surat-preview { border: 1px solid var(--border, #dee2e6); border-radius: 12px; background: #fff; padding: 18px; }
    .template-surat-preview { margin: 0; background: #f8f7fa; border-color: #f0cfe0; line-height: 1.65; padding: 20px; }
    .template-surat-preview > h5 { color: #d85a8d; margin: 0 0 18px; font-size: 1.25rem; }
    .template-preview-paper { min-height: 380px; padding: 28px; border: 1px solid #f0cfe0; border-radius: 12px; background: #fff; color: #3c3540; }
    .template-preview-title { text-align: center; font-weight: 800; letter-spacing: .4px; }
    .template-preview-number { text-align: center; margin: 4px 0 18px; }
    .template-preview-paper hr { margin: 10px 0 18px; border-color: #e6e0e5; }
    .template-preview-paper p { margin: 14px 0 0; color: #5b4751; }
    @media (max-width: 900px) { .template-surat-panel { grid-template-columns: 1fr; } .template-surat-panel > h5 { grid-column: auto; } }
</style>
