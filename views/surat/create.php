    <?php require_once __DIR__ . '/../layout/header.php'; ?>

    <?php
    $crewSearchOptions = [];
    $crewModel = $this->model('CrewModel');
    $allCrewForSearch = $crewModel->getAllActive();
    while ($c = $allCrewForSearch->fetch_assoc()) {
        $crewSearchOptions[] = [
            'id' => (int) $c['id_crew'],
            'label' => trim(($c['nama'] ?? '') . ' - ' . ($c['posisi'] ?? '')),
            'rig' => (int) ($c['id_rig'] ?? 0),
            'nama' => $c['nama'] ?? '',
            'posisi' => $c['posisi'] ?? '',
            'alamat' => $c['alamat'] ?? '',
            'crew' => $c['crew'] ?? '',
        ];
    }

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
    foreach ($rigListForSc as $r) {
        if (!empty($r['kode_rig'])) {
            $scRigOptions[] = $r['kode_rig'];
        }
    }
    ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fas fa-plus-circle"></i> Buat Surat Baru</h4>
        <a href="<?= BASE_URL ?>/surat" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>

    <div class="card-custom">
        <div class="card-body p-4">
            <form method="POST" action="<?= BASE_URL ?>/surat/store">
                <div class="row">
                    <!-- Jenis Surat -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Jenis Surat</label>
                        <select name="id_jenis" id="id_jenis" class="form-select" required onchange="toggleFields()">
                            <option value="">Pilih Jenis Surat</option>
                            <?php $jenis_surat->data_seek(0);
                            while ($j = $jenis_surat->fetch_assoc()): ?>
                                <option value="<?= $j['id_jenis'] ?>" data-code="<?= htmlspecialchars($j['kode']) ?>"><?= $j['kode'] ?> - <?= $j['nama_surat'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <?php if ($this->isSuperAdmin()): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Rig</label>
                            <select name="id_rig" id="top_rig_select" class="form-select" required>
                                <?php $rigModel = $this->model('RigModel');
                                $rigs = $rigModel->allActive(); ?>
                                <?php foreach ($rigs as $rig): ?>
                                    <option value="<?= $rig['id_rig'] ?>"><?= $rig['kode_rig'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Rig</label>
                            <input type="text" id="top_rig_input" class="form-control" value="<?= $kode_rig ?>" disabled>
                        </div>
                    <?php endif; ?>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Tanggal MOC</label>
                        <input type="date" name="tanggal_moc" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>

                    <input type="hidden" name="isi_surat_generated" id="isi_surat_generated" value="">

                    <?php require __DIR__ . '/partials/template_surat_baru.php'; ?>

                    <!--  FIELD UMUM  -->
                    <div id="umum-fields">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3" placeholder="Isi keterangan surat..."></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Tujuan</label>
                            <input type="text" name="tujuan" class="form-control" placeholder="Kepada Yth...">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Perihal</label>
                            <input type="text" name="perihal" class="form-control" placeholder="Perihal surat...">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Isi Surat</label>
                            <textarea name="isi_surat" id="isi_surat" class="form-control" rows="5" placeholder="Tulis isi surat di sini..."></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Link File (opsional)</label>
                            <input type="url" name="link_file" class="form-control" placeholder="https://gofile.me/...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Nama File (opsional)</label>
                            <input type="text" name="nama_file" class="form-control" placeholder="Nama dokumen...">
                        </div>
                    </div>

                    <!-- FIELD PKWT -->
                    <div id="pkwt-fields" style="display:none;" class="col-12">
                        <div class="pkwt-workspace">
                            <div class="pkwt-panel">
                                <h5><i class="fas fa-file-contract"></i> Data PKWT</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Crew</label>
                                        <div class="crew-search-wrap">
                                            <input type="search" id="pkwt_crew_search" class="form-control" placeholder="Ketik nama atau posisi crew..." autocomplete="off">
                                            <div id="pkwt_crew_options" class="crew-options" style="display:none;"></div>
                                        </div>
                                        <input type="hidden" name="pkwt_id_crew" id="pkwt_id_crew" value="">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Posisi / Template</label>
                                        <select name="pkwt_template" id="pkwt_template" class="form-select">
                                            <option value="">Pilih posisi</option>
                                            <?php foreach (['Accs Control','Asst Derrickman','Asst Driller','Derrickman','Electric','Floorman','Mechanic','Motorman','Mudboy','Room boy','Roustabout','Teknisi Crane','Welder'] as $tpl): ?>
                                                <option value="<?= htmlspecialchars($tpl, ENT_QUOTES) ?>"><?= htmlspecialchars($tpl) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6"><label class="form-label">Tanggal Mulai</label><input type="date" name="pkwt_tanggal_mulai" id="pkwt_tanggal_mulai" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                                    <div class="col-md-6"><label class="form-label">Tanggal Berakhir</label><input type="date" name="pkwt_tanggal_berakhir" id="pkwt_tanggal_berakhir" class="form-control"></div>
                                </div>
                                <div id="pkwt_crew_info" class="pkwt-crew-info mt-3">Pilih rig, crew, dan posisi untuk mengisi data otomatis.</div>
                                <div class="pkwt-crew-details mt-2" id="pkwt_crew_details" style="display:none;">
                                    <small><strong>Crew:</strong> <span id="pkwt_crew_class">-</span></small>
                                </div>
                            </div>
                            <aside class="pkwt-preview-wrap">
                                <h5><i class="fas fa-eye"></i> Preview PKWT</h5>
                                <div class="pkwt-preview">
                                    <div class="st-preview-head"><div class="st-preview-title">PERJANJIAN KERJA WAKTU TERTENTU</div><div>Nomor: otomatis dari sistem</div></div>
                                    <div class="st-preview-meta">
                                        <div><strong>Nama:</strong> <span id="pkwtp_nama">...</span></div>
                                        <div><strong>Jabatan:</strong> <span id="pkwtp_posisi">...</span></div>
                                        <div><strong>Rig:</strong> <span id="pkwtp_rig">...</span></div>
                                        <div><strong>Periode:</strong> <span id="pkwtp_periode">...</span></div>
                                    </div>
                                    <div class="st-preview-body"><p id="pkwtp_body">Pilih crew untuk melihat ringkasan PKWT.</p></div>
                                </div>
                            </aside>
                        </div>
                    </div>

                    <!--  FIELD SPK  -->
                    <div id="spk-fields" style="display:none;">
                        <div class="spk-workspace">
                            <div class="spk-left">
                                <section class="spk-panel">
                                    <h5>1. Pilih dasar surat</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Tujuan Surat</label>
                                        <input type="text" name="tujuan" id="tujuan_spk" class="form-control spk-input" value="DSR Rig GWDC">
                                    </div>
                                    <div>
                                        <label class="form-label">Perihal</label>
                                        <input type="text" name="perihal" id="perihal_spk" class="form-control spk-input" value="Surat Pengantar Crew Pengganti">
                                    </div>
                                </section>

                                <section class="spk-panel">
                                    <h5>2. Data crew</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Cari crew</label>
                                        <div class="crew-search-wrap">
                                            <input type="text" id="crew-search" class="form-control spk-input"
                                                placeholder="Ketik nama crew..." autocomplete="off"
                                                onfocus="showCrewDropdown()" oninput="filterCrewOptions()">
                                            <div id="crew-options" class="crew-options" style="display:none;"></div>
                                        </div>
                                        <input type="hidden" name="id_crew" id="id_crew" value="">
                                    </div>

                                    <div class="spk-chip-row">
                                        <span class="spk-chip" id="chip_jabatan">Jabatan: -</span>
                                        <span class="spk-chip" id="chip_badge">Badge: -</span>
                                        <span class="spk-chip" id="chip_rig">Rig: -</span>
                                        <span class="spk-chip" id="chip_crew">Crew: -</span>
                                    </div>
                                    <small class="text-muted">Terisi otomatis dari data crew.</small>

                                    <input type="hidden" id="rig_crew">
                                    <input type="hidden" id="crew_group">
                                    <input type="hidden" id="nama_crew">
                                    <input type="hidden" id="posisi_crew">
                                    <input type="hidden" id="badge_crew">
                                </section>

                                <section class="spk-panel">
                                    <h5>3. Detail tambahan</h5>
                                    <label class="form-label">Nama yang digantikan</label>
                                    <input type="text" id="posisi_vacant" class="form-control spk-input" placeholder="cth. vacant (posisi sebelumnya) (nama crew sebelumnya)">
                                </section>
                            </div>

                            <aside class="spk-preview-wrap">
                                <h5><i class="fas fa-eye"></i> Preview live</h5>
                                <div class="spk-preview">
                                    <div class="spk-preview-kop">
                                        <span></span>
                                        <strong></strong>
                                    </div>
                                    <div class="spk-preview-address">
                                        <div>Kepada Yth.</div>
                                        <div id="preview_tujuan">DSR Rig GWDC</div>
                                        <div>Di tempat</div>
                                    </div>
                                    <div class="spk-preview-subject">Perihal: <span id="preview_perihal">Surat Pengantar Crew Pengganti</span></div>
                                    <div class="spk-preview-greeting">Dengan hormat</div>
                                    <p id="preview_intro">Bersama ini kami kirimkan pengganti ... dengan data:</p>
                                    <table class="spk-preview-crew">
                                        <tr>
                                            <td>1.</td>
                                            <td>Nama</td>
                                            <td>:</td>
                                            <td id="preview_nama">...</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>Jabatan</td>
                                            <td>:</td>
                                            <td id="preview_jabatan">...</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>No Badge</td>
                                            <td>:</td>
                                            <td id="preview_badge">...</td>
                                        </tr>
                                    </table>
                                    <p id="preview_permanen">Dengan ini, menyatakan bahwa nama yang bersangkutan dipermanenkan di posisi ...</p>
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

                    <!--  FIELD ST  -->
                    <div id="st-fields" style="display:none;">
                        <div class="st-workspace">
                            <div class="st-left">
                                <div class="st-panel">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Cari crew</label>
                                        <div class="crew-search-wrap">
                                            <input type="text" id="st_crew_search" class="form-control spk-input"
                                                placeholder="Ketik nama crew..." autocomplete="off"
                                                onfocus="showCrewDropdown('st_crew_search', 'st_crew_options')"
                                                oninput="filterCrewOptions('st_crew_search', 'st_crew_options')">
                                            <div id="st_crew_options" class="crew-options" style="display:none;"></div>
                                        </div>
                                        <input type="hidden" id="st_id_crew" value="">
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Nama</label>
                                            <input type="text" id="st_name" class="form-control spk-input" placeholder="Nama karyawan" oninput="buildStIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Department</label>
                                            <input type="text" id="st_department" class="form-control spk-input" placeholder="GWDC 338, C" oninput="buildStIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Status Karyawan</label>
                                            <input type="text" id="st_status" class="form-control spk-input" placeholder="Contract / Permanent" oninput="buildStIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Posisi / Jabatan</label>
                                            <input type="text" id="st_position" class="form-control spk-input" placeholder="Posisi" oninput="buildStIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Tanggal Masuk</label>
                                            <input type="date" id="st_date_entry" class="form-control spk-input" oninput="buildStIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Tanggal Evaluasi</label>
                                            <input type="date" id="st_date_eval" class="form-control spk-input" oninput="buildStIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Masa Evaluasi</label>
                                            <input type="text" id="st_eval_months" class="form-control spk-input" placeholder="5" oninput="buildStIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Perusahaan</label>
                                            <input type="text" id="st_company" class="form-control spk-input" placeholder="PT. SIGMA" oninput="buildStIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Diajukan Oleh</label>
                                            <input type="text" id="st_request_by" class="form-control spk-input" placeholder="Nama pengaju" oninput="buildStIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Jabatan Pengaju</label>
                                            <input type="text" id="st_request_title" class="form-control spk-input" placeholder="Jabatan pengaju" oninput="buildStIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Disetujui Oleh</label>
                                            <input type="text" id="st_approved_by" class="form-control spk-input" placeholder="Nama penyetuju" oninput="buildStIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Jabatan Penyetuju</label>
                                            <input type="text" id="st_approved_title" class="form-control spk-input" placeholder="Jabatan penyetuju" oninput="buildStIsi()">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Alasan</label>
                                            <textarea id="st_issue_id" class="form-control spk-input" rows="3" placeholder="Tulis alasan dalam bahasa Indonesia" oninput="buildStIsi()"></textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Alasan English (otomatis)</label>
                                            <textarea id="st_issue_en_preview" class="form-control spk-input" rows="3" readonly></textarea>
                                            <input type="hidden" id="st_issue_en" value="">
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-3">Data ini akan otomatis diisi ke isi surat saat Anda menyimpan.</small>
                                </div>
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

                    <!--  FIELD SKA  -->
                    <div id="ska-fields" style="display:none;">
                        <div class="ska-workspace">
                            <div class="ska-left">
                                <section class="ska-panel">
                                    <h5>1. Data karyawan</h5>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Cari crew</label>
                                        <div class="crew-search-wrap">
                                            <input type="text" id="ska_crew_search" class="form-control spk-input"
                                                placeholder="Ketik nama crew..." autocomplete="off"
                                                onfocus="showCrewDropdown('ska_crew_search', 'ska_crew_options')"
                                                oninput="filterCrewOptions('ska_crew_search', 'ska_crew_options')">
                                            <div id="ska_crew_options" class="crew-options" style="display:none;"></div>
                                        </div>
                                        <input type="hidden" id="ska_id_crew" value="">
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Nama Karyawan</label>
                                            <input type="text" id="ska_employee_name" class="form-control spk-input" placeholder="Nama karyawan" oninput="buildSkaIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Jabatan Karyawan</label>
                                            <input type="text" id="ska_employee_title" class="form-control spk-input" placeholder="Jabatan" oninput="buildSkaIsi()">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Alamat Karyawan</label>
                                            <input type="text" id="ska_employee_address" class="form-control spk-input" placeholder="Alamat karyawan" oninput="buildSkaIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Perusahaan Penempatan</label>
                                            <input type="text" id="ska_company" class="form-control spk-input" value="PT Greatwall Drilling Company" oninput="buildSkaIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Tanggal Mulai</label>
                                            <input type="date" id="ska_start_date" class="form-control spk-input" oninput="buildSkaIsi()">
                                        </div>
                                    </div>
                                </section>

                                <section class="ska-panel">
                                    <h5>2. Penandatangan</h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Nama Penandatangan</label>
                                            <input type="text" id="ska_statement_name" class="form-control spk-input" value="Amy Faradila" oninput="buildSkaIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Jabatan Penandatangan</label>
                                            <input type="text" id="ska_statement_title" class="form-control spk-input" value="HRD Department" oninput="buildSkaIsi()">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Alamat Penandatangan</label>
                                            <input type="text" id="ska_statement_address" class="form-control spk-input" value="Jl. Karang Anyer II, Kec Mandau" oninput="buildSkaIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Tempat Surat</label>
                                            <input type="text" id="ska_place" class="form-control spk-input" value="Duri" oninput="buildSkaIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Tanggal Surat</label>
                                            <input type="date" id="ska_letter_date" class="form-control spk-input" value="<?= date('Y-m-d') ?>" oninput="buildSkaIsi()">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="form-label fw-semibold">Catatan internal</label>
                                        <textarea name="keterangan" id="ska_keterangan" class="form-control spk-input" rows="3" placeholder="Diisi admin, tidak tercetak di surat"></textarea>
                                    </div>
                                    <input type="hidden" name="perihal" id="ska_perihal" value="Surat Keterangan Kerja">
                                </section>
                            </div>

                            <aside class="ska-preview-wrap">
                                <h5><i class="fas fa-eye"></i> Preview live</h5>
                                <div class="ska-preview">
                                    <div class="st-preview-head">
                                        <div class="st-preview-title">SURAT KETERANGAN KERJA</div>
                                        <div>Nomor: otomatis dari sistem</div>
                                    </div>
                                    <div class="st-preview-meta">
                                        <div><strong>Penandatangan:</strong> <span id="skap_statement_name">Amy Faradila</span></div>
                                        <div><strong>Karyawan:</strong> <span id="skap_employee_name">...</span></div>
                                        <div><strong>Jabatan:</strong> <span id="skap_employee_title">...</span></div>
                                        <div><strong>Penempatan:</strong> <span id="skap_company">PT Greatwall Drilling Company</span></div>
                                    </div>
                                    <div class="st-preview-body">
                                        <p id="skap_body">Preview akan muncul di sini saat Anda mengisi data.</p>
                                    </div>
                                </div>
                            </aside>
                        </div>
                    </div>

                    <!--  FIELD SP  -->
                    <div id="sp-fields" style="display:none;">
                        <div class="sp-workspace">
                            <div class="sp-left">
                                <section class="sp-panel">
                                    <h5>1. Data karyawan</h5>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Cari karyawan</label>
                                        <div class="crew-search-wrap">
                                            <input type="text" id="sp_crew_search" class="form-control spk-input"
                                                placeholder="Ketik nama karyawan..." autocomplete="off"
                                                onfocus="showCrewDropdown('sp_crew_search', 'sp_crew_options')"
                                                oninput="filterCrewOptions('sp_crew_search', 'sp_crew_options')">
                                            <div id="sp_crew_options" class="crew-options" style="display:none;"></div>
                                        </div>
                                        <input type="hidden" id="sp_id_crew" value="">
                                    </div>
                                    <div class="spk-chip-row">
                                        <span class="spk-chip" id="sp_chip_nama">Nama: -</span>
                                        <span class="spk-chip" id="sp_chip_badge">Badge: -</span>
                                        <span class="spk-chip" id="sp_chip_posisi">Posisi: -</span>
                                        <span class="spk-chip" id="sp_chip_rig">Rig: -</span>
                                        <span class="spk-chip" id="sp_chip_crew">Crew: -</span>
                                    </div>
                                    <small class="text-muted d-block mb-3">Nama, jabatan, no badge, rig, dan crew terisi otomatis dari data karyawan.</small>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Peringatan Tingkat</label>
                                            <select id="sp_level" class="form-select spk-input" onchange="buildSpIsi()">
                                                <option value="Pertama">Pertama</option>
                                                <option value="Kedua">Kedua</option>
                                                <option value="Ketiga">Ketiga</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Penandatangan</label>
                                            <input type="text" id="sp_signer" class="form-control spk-input" value="Amy Faradila" oninput="buildSpIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Jabatan Penandatangan</label>
                                            <input type="text" id="sp_signer_title" class="form-control spk-input" value="HRD Dept" oninput="buildSpIsi()">
                                        </div>
                                    </div>
                                    <input type="hidden" id="sp_employee_name" value="">
                                    <input type="hidden" id="sp_badge" value="">
                                    <input type="hidden" id="sp_position" value="">
                                    <input type="hidden" id="sp_rig" value="">
                                    <input type="hidden" id="sp_crew" value="">
                                </section>

                                <section class="sp-panel">
                                    <h5>2. Detail peringatan</h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Hari / Tanggal Surat</label>
                                            <input type="date" id="sp_letter_date" class="form-control spk-input" value="<?= date('Y-m-d') ?>" oninput="buildSpIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Masa Berlaku Mulai</label>
                                            <input type="date" id="sp_effective_start" class="form-control spk-input" value="<?= date('Y-m-d') ?>" oninput="buildSpIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Masa Berlaku Sampai</label>
                                            <input type="date" id="sp_effective_end" class="form-control spk-input" oninput="buildSpIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Dikeluarkan Oleh</label>
                                            <input type="text" id="sp_place" class="form-control spk-input" value="Duri" oninput="buildSpIsi()">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Judul Pelanggaran</label>
                                            <input type="text" id="sp_violation_title" class="form-control spk-input" value="TENTANG KEDISIPLINAN, MELANGGAR ATURAN PERUSAHAAN, ETIKA KINERJA YANG SIGNIFIKAN" oninput="buildSpIsi()">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Temuan / Pelanggaran</label>
                                            <textarea id="sp_violation_detail" class="form-control spk-input" rows="3" oninput="buildSpIsi()">Sehubungan dengan terjadinya pelanggaran yang dilakukan karyawan berupa temuan finding cctv dilokasi.</textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Tindakan Disiplin</label>
                                            <textarea id="sp_discipline_action" class="form-control spk-input" rows="3" oninput="buildSpIsi()">Maka dari itu perusahaan melakukan tindakan disiplin Coaching Diyard selama 3 hari dan menonaktifkan absensi kehadiran/Alfa serta memotong gaji karyawan yang melanggar aturan.</textarea>
                                        </div>
                                    </div>
                                    <input type="hidden" name="perihal" id="sp_perihal" value="Surat Peringatan">
                                    <input type="hidden" name="tujuan" id="sp_tujuan" value="">
                                </section>
                            </div>

                            <aside class="sp-preview-wrap">
                                <h5><i class="fas fa-eye"></i> Preview live</h5>
                                <div class="sp-preview">
                                    <div class="st-preview-head">
                                        <div class="st-preview-title" id="spp_title">SURAT PERINGATAN PERTAMA</div>
                                        <div>No: otomatis dari sistem</div>
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

                    <!--  FIELD SPT  -->
                    <div id="spt-fields" style="display:none;">
                        <div class="spt-workspace">
                            <div class="spt-left">
                                <section class="spt-panel">
                                    <h5>1. Tujuan surat</h5>
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Kepada</label>
                                            <input type="text" name="tujuan" id="spt_tujuan" class="form-control spk-input"
                                                list="spt-tujuan-list" value="All Crew" oninput="buildSptIsi()">
                                            <datalist id="spt-tujuan-list">
                                                <option value="All Rig"></option>
                                                <option value="Crew terkait"></option>
                                            </datalist>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Posisi</label>
                                            <select id="spt_positions" class="form-select spk-input" multiple data-placeholder="Pilih satu atau lebih posisi" onchange="buildSptIsi()">
                                                <?php foreach ($scPositionOptions as $posisiOption): ?>
                                                    <option value="<?= htmlspecialchars($posisiOption) ?>"><?= htmlspecialchars($posisiOption) ?></option>
                                                <?php endforeach; ?>
                                                <option value="Roustabout">Roustabout</option>
                                                <option value="Roomboy">Roomboy</option>
                                                <option value="Accs.Control">Accs.Control</option>
                                                <option value="Floorman">Floorman</option>
                                                <option value="Driller">Driller</option>
                                                <option value="Assistant Driller">Assistant Driller</option>
                                                <option value="Rig Manager">Rig Manager</option>
                                                <option value="Mechanic">Mechanic</option>
                                                <option value="Electrician">Electrician</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Rig</label>
                                            <input type="text" id="spt_rigs" class="form-control spk-input" list="spt-rig-list" oninput="buildSptIsi()">
                                            <datalist id="spt-rig-list">
                                                <?php foreach ($scRigOptions as $rigOption): ?>
                                                    <option value="<?= htmlspecialchars($rigOption) ?>"></option>
                                                <?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Perihal</label>
                                            <input type="text" name="perihal" id="spt_perihal" class="form-control spk-input" value="Pemberitahuan" oninput="buildSptIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Lampiran</label>
                                            <input type="text" id="spt_lampiran" class="form-control spk-input" value="1" oninput="buildSptIsi()">
                                        </div>
                                    </div>
                                </section>

                                <section class="spt-panel">
                                    <h5>2. Isi pemberitahuan</h5>
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Paragraf 1</label>
                                            <textarea id="spt_paragraph_1" class="form-control spk-input" rows="3" oninput="buildSptIsi()">Diberitahukan kepada seluruh karyawan GWAP339 yang sudah menanda tangani PKWT diwajibkan hadir bekerja sesuai dengan schedule yang sudah diterbitkan oleh management, bagi yang berhalangan sakit agar dapat melampirkan bukti surat sakit dari klinik maupun tempat berobat.</textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Paragraf 2</label>
                                            <textarea id="spt_paragraph_2" class="form-control spk-input" rows="3" oninput="buildSptIsi()">Untuk Rig Admin masing-masing Crew agar melaporkan setiap kekurangan crew yang ada dilapangan kepada Tool Pusher, RSM dan Rig Superintendent yang bertugas.</textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Paragraf 3</label>
                                            <textarea id="spt_paragraph_3" class="form-control spk-input" rows="3" oninput="buildSptIsi()">Bagi karyawan yang masih proses CCPM agar tetap hadir dilapangan sesuai dengan schedule kerja masing-masing, tidak ada alasan lagi untuk tidak hadir karena kita sudah memberikan toleransi waktu untuk menyelesaikan schedule kerja diperusahaan lama.</textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Penutup</label>
                                            <textarea id="spt_closing" class="form-control spk-input" rows="2" oninput="buildSptIsi()">Demikian kami sampaikan atas perhatian dan kerjasamanya kami ucapkan terimakasih.</textarea>
                                        </div>
                                    </div>
                                </section>

                                <section class="spt-panel">
                                    <h5>3. Penandatangan</h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Tanggal Surat</label>
                                            <input type="date" id="spt_letter_date" class="form-control spk-input" value="<?= date('Y-m-d') ?>" oninput="buildSptIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Nama Penandatangan</label>
                                            <input type="text" id="spt_signer" class="form-control spk-input" value="Y. Chandra" oninput="buildSptIsi()">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Jabatan</label>
                                            <input type="text" id="spt_signer_title" class="form-control spk-input" value="Project Manager" oninput="buildSptIsi()">
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <aside class="spt-preview-wrap">
                                <h5><i class="fas fa-eye"></i> Preview live</h5>
                                <div class="spt-preview">
                                    <div class="st-preview-head">
                                        <div class="st-preview-title">PEMBERITAHUAN</div>
                                        <div>Nomor: otomatis dari sistem</div>
                                    </div>
                                    <div class="st-preview-meta">
                                        <div><strong>Kepada:</strong> <span id="sptp_tujuan">Crew GWAP 339</span></div>
                                        <div><strong>Lampiran:</strong> <span id="sptp_lampiran">1</span></div>
                                        <div><strong>Tanggal:</strong> <span id="sptp_date">...</span></div>
                                    </div>
                                    <div class="st-preview-body">
                                        <p id="sptp_body">Preview akan muncul di sini saat Anda mengisi data.</p>
                                    </div>
                                </div>
                            </aside>
                        </div>
                    </div>

                    <!--  FIELD SR  -->
                    <div id="sr-fields" style="display:none;">
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
                                            <input type="text" name="tujuan" id="sr_tujuan" class="form-control spk-input" list="sr-tujuan-list" value="All Crew" oninput="buildSrIsi()">
                                            <datalist id="sr-tujuan-list">
                                                
                                                <option value="All Rig"></option>
                                                <option value="Semua Rig"></option>
                                                <option value="Crew terkait"></option>
                                            </datalist>
                                        </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Posisi</label>
                        <select id="sr_positions" class="form-select spk-input" multiple data-placeholder="Pilih satu atau lebih posisi" onchange="buildSrIsi()">
                            <?php foreach ($scPositionOptions as $posisiOption): ?>
                                <option value="<?= htmlspecialchars($posisiOption) ?>"><?= htmlspecialchars($posisiOption) ?></option>
                            <?php endforeach; ?>
                        </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Rig</label>
                                            <input type="text" id="sr_rigs" class="form-control spk-input" list="sr-rig-list" oninput="buildSrIsi()">
                                            <datalist id="sr-rig-list">
                                                <?php foreach ($scRigOptions as $rigOption): ?>
                                                    <option value="<?= htmlspecialchars($rigOption) ?>"></option>
                                                <?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        <input type="hidden" name="perihal" id="sr_perihal" value="Ketentuan Pengunduran Diri (Resign) dan Penalty Kontrak">
                                    </div>
                                </section>
                                <section class="spt-panel">
                                    <h5>2. Detail memo</h5>
                                    <div class="row g-3">
                                        <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Surat</label><input type="date" id="sr_letter_date" class="form-control spk-input" value="<?= date('Y-m-d') ?>" oninput="buildSrIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Tempat Surat</label><input type="text" id="sr_place" class="form-control spk-input" value="Duri" oninput="buildSrIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Lampiran</label><input type="text" id="sr_lampiran" class="form-control spk-input" value="1" oninput="buildSrIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Penandatangan</label><input type="text" id="sr_signer" class="form-control spk-input" value="Amy Faradila" oninput="buildSrIsi()"></div>
                                    </div>
                                </section>
                            </div>
                            <aside class="spt-preview-wrap">
                                <h5><i class="fas fa-eye"></i> Preview live</h5>
                                <div class="spt-preview">
                                    <div class="st-preview-head"><div class="st-preview-title">MEMO INTERNAL</div><div>Nomor: otomatis dari sistem</div></div>
                                    <div class="st-preview-meta">
                                        <div><strong>Kepada:</strong> <span id="srp_tujuan">All Crew</span></div>
                                        <div><strong>Rig:</strong> <span id="srp_rigs">...</span></div>
                                        <div><strong>Posisi:</strong> <span id="srp_positions">...</span></div>
                                    </div>
                                    <div class="st-preview-body"><p id="srp_body">Ketentuan resign dan penalty kontrak akan dibuat otomatis.</p></div>
                                </div>
                            </aside>
                        </div>
                    </div>

                    <!--  FIELD SKK  -->
                    <div id="skk-fields" style="display:none;">
                        <div class="spt-workspace">
                            <div class="spt-left">
                                <section class="spt-panel">
                                    <h5>1. Tujuan surat</h5>
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Kepada</label>
                                            <input type="text" name="tujuan" id="skk_tujuan" class="form-control spk-input"
                                                list="skk-tujuan-list" value="Crew terkait" oninput="buildSkkIsi()">
                                            <datalist id="skk-tujuan-list">
                                               
                                                <option value="All Rig"></option>
                                                <option value="Semua Rig"></option>
                                                <option value="Crew terkait"></option>
                                            </datalist>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Posisi</label>
                                            <select id="skk_positions" class="form-select spk-input" multiple data-placeholder="Pilih satu atau lebih posisi" onchange="buildSkkIsi()">
                                                <?php foreach ($scPositionOptions as $posisiOption): ?>
                                                    <option value="<?= htmlspecialchars($posisiOption) ?>"><?= htmlspecialchars($posisiOption) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Rig</label>
                                            <input type="text" id="skk_rigs" class="form-control spk-input" list="skk-rig-list" oninput="buildSkkIsi()">
                                            <datalist id="skk-rig-list">
                                                <?php foreach ($scRigOptions as $rigOption): ?>
                                                    <option value="<?= htmlspecialchars($rigOption) ?>"></option>
                                                <?php endforeach; ?>
                                            </datalist>
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
                                        <div class="col-md-6"><label class="form-label fw-semibold">Nama</label><input type="text" id="skk_name" class="form-control spk-input" readonly oninput="buildSkkIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Jabatan</label><input type="text" id="skk_position" class="form-control spk-input" readonly oninput="buildSkkIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Proyek & Lokasi</label><input type="text" id="skk_project" class="form-control spk-input" value="Drilling at PT. Greatwall Drilling Asia Pacific" oninput="buildSkkIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Mulai Bekerja</label><input type="date" id="skk_start_date" class="form-control spk-input" oninput="buildSkkIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Surat</label><input type="date" id="skk_letter_date" class="form-control spk-input" value="<?= date('Y-m-d') ?>" oninput="buildSkkIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Tempat Surat</label><input type="text" id="skk_place" class="form-control spk-input" value="Duri" oninput="buildSkkIsi()"></div>
                                    </div>
                                    <input type="hidden" name="perihal" id="skk_perihal" value="Surat Keterangan Kerja">
                                </section>
                            </div>
                            <aside class="spt-preview-wrap">
                                <h5><i class="fas fa-eye"></i> Preview live</h5>
                                <div class="spt-preview">
                                    <div class="st-preview-head"><div class="st-preview-title">SURAT KETERANGAN KERJA</div><div>Nomor: otomatis dari sistem</div></div>
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

                    <!--  FIELD BA  -->
                    <div id="ba-fields" style="display:none;">
                        <div class="spt-workspace">
                            <div class="spt-left">
                                <section class="spt-panel">
                                    <h5>1. Tujuan surat</h5>
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Kepada</label>
                                            <input type="text" name="tujuan" id="ba_tujuan" class="form-control spk-input"
                                                list="ba-tujuan-list" value="Crew terkait" oninput="buildBaIsi()">
                                            <datalist id="ba-tujuan-list">
                                            
                                                <option value="All Rig"></option>
                                                <option value="Semua Rig"></option>
                                                <option value="Crew terkait"></option>
                                            </datalist>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Posisi</label>
                                            <select id="ba_positions" class="form-select spk-input" multiple data-placeholder="Pilih satu atau lebih posisi" onchange="buildBaIsi()">
                                                <?php foreach ($scPositionOptions as $posisiOption): ?>
                                                    <option value="<?= htmlspecialchars($posisiOption) ?>"><?= htmlspecialchars($posisiOption) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Rig</label>
                                            <input type="text" id="ba_rigs" class="form-control spk-input" list="ba-rig-list" oninput="buildBaIsi()">
                                            <datalist id="ba-rig-list">
                                                <?php foreach ($scRigOptions as $rigOption): ?>
                                                    <option value="<?= htmlspecialchars($rigOption) ?>"></option>
                                                <?php endforeach; ?>
                                            </datalist>
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
                                        <div class="col-md-6"><label class="form-label fw-semibold">Nama Crew</label><input type="text" id="ba_name" class="form-control spk-input" readonly oninput="buildBaIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Posisi</label><input type="text" id="ba_position" class="form-control spk-input" readonly oninput="buildBaIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Rig</label><input type="text" id="ba_rig" class="form-control spk-input" readonly oninput="buildBaIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Nomor Kontrak</label><input type="text" id="ba_contract" class="form-control spk-input" value="SPHR00618A" oninput="buildBaIsi()"></div>
                                        <div class="col-md-4"><label class="form-label fw-semibold">Start OJT</label><input type="date" id="ba_start_ojt" class="form-control spk-input" oninput="buildBaIsi()"></div>
                                        <div class="col-md-4"><label class="form-label fw-semibold">End OJT</label><input type="date" id="ba_end_ojt" class="form-control spk-input" oninput="buildBaIsi()"></div>
                                        <div class="col-md-4"><label class="form-label fw-semibold">Date Assessment</label><input type="date" id="ba_assessment_date" class="form-control spk-input" value="<?= date('Y-m-d') ?>" oninput="buildBaIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Hasil</label><select id="ba_result" class="form-select spk-input" onchange="buildBaIsi()"><option value="Passed">Passed</option><option value="Failed">Failed</option></select></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Tempat</label><input type="text" id="ba_place" class="form-control spk-input" value="Tanggul" oninput="buildBaIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Surat</label><input type="date" id="ba_letter_date" class="form-control spk-input" value="<?= date('Y-m-d') ?>" oninput="buildBaIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Nama Signer (DSR)</label><input type="text" id="ba_signer" class="form-control spk-input" placeholder="Nama DSR" oninput="buildBaIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Nama Signer (RSM)</label><input type="text" id="ba_signer_rsm" class="form-control spk-input" placeholder="Nama RSM" oninput="buildBaIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Nama Signer (HSE)</label><input type="text" id="ba_signer_hse" class="form-control spk-input" placeholder="Nama HSE" oninput="buildBaIsi()"></div>
                                    </div>
                                    <input type="hidden" name="perihal" id="ba_perihal" value="Berita Acara Evaluasi Hasil Assessment & OJT">
                                </section>
                            </div>
                            <aside class="spt-preview-wrap">
                                <h5><i class="fas fa-eye"></i> Preview live</h5>
                                <div class="spt-preview">
                                    <div class="st-preview-head"><div class="st-preview-title">BERITA ACARA ASSESSMENT & OJT</div><div>Nomor: otomatis dari sistem</div></div>
                                    <div class="st-preview-meta">
                                        <div><strong>Nama:</strong> <span id="bap_name">...</span></div>
                                        <div><strong>Rig:</strong> <span id="bap_rig">...</span></div>
                                        <div><strong>Hasil:</strong> <span id="bap_result">Passed</span></div>
                                    </div>
                                    <div class="st-preview-body"><p id="bap_body">Pilih crew untuk mengisi otomatis.</p></div>
                                </div>
                            </aside>
                        </div>
                    </div>
                </div>

                    <!--  FIELD SJ  -->
                    <div id="sj-fields" style="display:none;">
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
                                        <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Perjanjian</label><input type="date" id="sj_date" class="form-control spk-input" value="<?= date('Y-m-d') ?>" oninput="buildSjIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Nama</label><input type="text" id="sj_p1_name" class="form-control spk-input" oninput="buildSjIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Tempat/tgl lahir</label><input type="text" id="sj_p1_birth" class="form-control spk-input" placeholder="Duri, 01 Januari 1990" oninput="buildSjIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Jabatan</label><input type="text" id="sj_p1_position" class="form-control spk-input" oninput="buildSjIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Alamat</label><input type="text" id="sj_p1_address" class="form-control spk-input" oninput="buildSjIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Domisili</label><input type="text" id="sj_p1_domicile" class="form-control spk-input" oninput="buildSjIsi()"></div>
                                    </div>
                                </section>
                                <section class="spt-panel">
                                    <h5>2. Detail perjanjian</h5>
                                    <div class="row g-3">
                                        <div class="col-md-6"><label class="form-label fw-semibold">Nama Sertifikat</label><input type="text" id="sj_cert_name" class="form-control spk-input" value="Asme Welder" oninput="buildSjIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Biaya Sertifikat</label><input type="text" id="sj_fee" class="form-control spk-input" value="Rp. 12.000.000" oninput="buildSjIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Potongan Perbulan</label><input type="text" id="sj_deduction" class="form-control spk-input" value="Rp. 2.000.000" oninput="buildSjIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Ikatan Kerja</label><input type="text" id="sj_work_period" class="form-control spk-input" value="1 (satu) bulan" oninput="buildSjIsi()"></div>
                                    </div>
                                    <input type="hidden" name="perihal" id="sj_perihal" value="Surat Perjanjian Antara Karyawan dan Perusahaan">
                                </section>
                            </div>
                            <aside class="spt-preview-wrap">
                                <h5><i class="fas fa-eye"></i> Preview live</h5>
                                <div class="spt-preview">
                                    <div class="st-preview-head"><div class="st-preview-title">SURAT PERJANJIAN</div><div>Nomor: otomatis dari sistem</div></div>
                                    <div class="st-preview-meta">
                                        <div><strong>Pihak Pertama:</strong> <span id="sjp_name">...</span></div>
                                        <div><strong>Sertifikat:</strong> <span id="sjp_cert">Asme Welder</span></div>
                                        <div><strong>Biaya:</strong> <span id="sjp_fee">Rp. 12.000.000</span></div>
                                    </div>
                                </div>
                            </aside>
                        </div>
                    </div>

                    <!--  FIELD MM  -->
                    <div id="mm-fields" style="display:none;">
                        <div class="spt-workspace">
                            <div class="spt-left">
                                <section class="spt-panel">
                                    <h5>1. Model memo</h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Model</label>
                                            <select id="mm_model" class="form-select spk-input" onchange="toggleMemoModel(); buildMmIsi();">
                                                <option value="Memo Internal">Memo Internal</option>
                                                <option value="Memorandum">Memorandum</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Tanggal Surat</label><input type="date" id="mm_letter_date" class="form-control spk-input" value="<?= date('Y-m-d') ?>" oninput="buildMmIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Tempat Surat</label><input type="text" id="mm_place" class="form-control spk-input" value="Duri" oninput="buildMmIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Lampiran</label><input type="text" id="mm_lampiran" class="form-control spk-input" value="-" oninput="buildMmIsi()"></div>
                                        <div class="col-md-12"><label class="form-label fw-semibold">Perihal</label><input type="text" name="perihal" id="mm_perihal" class="form-control spk-input" value="Informasi Kebijakan Kontrak dan Hak Karyawan" oninput="buildMmIsi()"></div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Kepada</label>
                                            <input type="text" name="tujuan" id="mm_tujuan" class="form-control spk-input" list="mm-tujuan-list" value="All Crew" oninput="buildMmIsi()">
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
                                                    <option value="<?= htmlspecialchars($posisiOption) ?>"><?= htmlspecialchars($posisiOption) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Rig</label>
                                            <input type="text" id="mm_rigs" class="form-control spk-input" list="mm-rig-list" oninput="buildMmIsi()">
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
                                        <div class="col-md-12"><label class="form-label fw-semibold">Isi Pembuka</label><textarea id="mm_intro" class="form-control spk-input" rows="4" oninput="buildMmIsi()">Diberitahukan kepada seluruh Crew Drilling, terkait issue yang sedang berkembang di lokasi perihal isi dari PKWT Periode 2026-2027, maka kami sampaikan penjelasan sebagai berikut:</textarea></div>
                                        <div class="col-md-12"><label class="form-label fw-semibold">Poin Memo</label><textarea id="mm_points" class="form-control spk-input" rows="14" oninput="buildMmIsi()">1. Kenaikan UMK 2026
Kenaikan UMK tahun 2026 saat ini masih dalam proses pengajuan dan menunggu persetujuan dari GDAP Jakarta. Apabila pengajuan tersebut telah disetujui, maka penyesuaian gaji akan dilakukan secara paralel dan akan diinformasikan kembali melalui memo resmi selanjutnya.
2. Pesangon / Kompensasi PKWT
Ketentuan pesangon/kompensasi tidak dicantumkan dalam draft PKWT. Namun demikian, secara aktual:
- Pemberian uang kompensasi tetap akan diberikan seperti periode sebelumnya.
- Pembayaran kompensasi PKWT untuk periode 2025–2026 akan dilakukan paling lambat 2 (dua) bulan setelah pengajuan pencairan, terhitung sejak tanggal PKWT close.
3. Uang Makan
Uang makan tidak dicantumkan dalam PKWT karena mengacu pada peraturan PHR yang mewajibkan penyediaan catering bagi crew di lokasi kerja.
Namun secara aktual:
- Pemberian uang makan tetap berjalan sesuai ketentuan yang berlaku saat ini.
4. Hak dan Uang Cuti
Hak dan uang cuti tidak dicantumkan dalam draft PKWT, namun secara aktual:
- Hak cuti tetap berlaku.
- Pengajuan cuti dilakukan sesuai prosedur, yaitu melalui persetujuan Area Manager, Leader lokasi, dan Crew Coordinator menggunakan Form Cuti.
- Uang cuti periode 2024–2025 akan dibayarkan paling lambat akhir April 2026.
- Uang cuti periode 2025–2026 saat ini masih dalam proses pengajuan pencairan ke GDAP Jakarta.
5. Masa Probation (3 Bulan)
Kebijakan probation selama 3 bulan diberlakukan sebagai bentuk evaluasi dan pembinaan terhadap karyawan. Hal ini bertujuan agar karyawan dapat melakukan perbaikan dalam aspek:
- Kinerja
- Kedisiplinan
- Loyalitas
- Kerja sama tim
- Kepatuhan terhadap peraturan perusahaan
- Hubungan kerja dan komunikasi yang baik dengan manajemen dan leader di lokasi
Apabila selama masa probation karyawan menunjukkan komitmen dan perbaikan yang baik, maka akan dilakukan evaluasi untuk perpanjangan kontrak berikutnya.</textarea></div>
                                        <div class="col-md-12"><label class="form-label fw-semibold">Isi Lanjutan (khusus Memorandum)</label><textarea id="mm_followup" class="form-control spk-input" rows="10" oninput="buildMmIsi()"></textarea></div>
                                        <div class="col-md-12"><label class="form-label fw-semibold">Penutup</label><textarea id="mm_closing" class="form-control spk-input" rows="2" oninput="buildMmIsi()">Demikian memo ini disampaikan untuk dapat dipahami dengan baik. Atas perhatian dan kerja samanya kami ucapkan terima kasih.</textarea></div>
                                    </div>
                                </section>
                                <section class="spt-panel">
                                    <h5>3. Tanda tangan</h5>
                                    <div class="row g-3">
                                        <div class="col-md-6"><label class="form-label fw-semibold">Signer HRD</label><input type="text" id="mm_signer_hrd" class="form-control spk-input" value="Amy Faradila" oninput="buildMmIsi()"></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Jabatan HRD</label><input type="text" id="mm_signer_hrd_title" class="form-control spk-input" value="HR Department" oninput="buildMmIsi()"></div>
                                        <div class="col-md-6 memo-memorandum-only"><label class="form-label fw-semibold">Signer Rig</label><input type="text" id="mm_signer_rig" class="form-control spk-input" placeholder="Nama signer rig" oninput="buildMmIsi()"></div>
                                        <div class="col-md-6 memo-memorandum-only"><label class="form-label fw-semibold">Jabatan Rig</label><input type="text" id="mm_signer_rig_title" class="form-control spk-input" value="Rig Superintendent" oninput="buildMmIsi()"></div>
                                        <div class="col-md-6 memo-memorandum-only"><label class="form-label fw-semibold">Signer Manager</label><input type="text" id="mm_signer_manager" class="form-control spk-input" value="Area Manager" oninput="buildMmIsi()"></div>
                                        <div class="col-md-6 memo-memorandum-only"><label class="form-label fw-semibold">Jabatan Manager</label><input type="text" id="mm_signer_manager_title" class="form-control spk-input" value="Area Manager" oninput="buildMmIsi()"></div>
                                    </div>
                                </section>
                            </div>
                            <aside class="spt-preview-wrap">
                                <h5><i class="fas fa-eye"></i> Preview live</h5>
                                <div class="spt-preview">
                                    <div class="st-preview-head"><div class="st-preview-title" id="mmp_title">MEMO INTERNAL</div><div>Nomor: otomatis dari sistem</div></div>
                                    <div class="st-preview-meta">
                                        <div><strong>Perihal:</strong> <span id="mmp_perihal">...</span></div>
                                        <div><strong>Kepada:</strong> <span id="mmp_tujuan">...</span></div>
                                        <div><strong>Model:</strong> <span id="mmp_model">Memo Internal</span></div>
                                    </div>
                                </div>
                            </aside>
                        </div>
                    </div>

                <!--  FIELD MCU  -->
                <div id="mcu-fields" style="display:none;">
                    <div class="mcu-workspace">
                        <div class="mcu-left">
                            <section class="mcu-panel">
                                <h5>1. Data Karyawan</h5>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Filter Crew</label>
                                        <select id="mcu_filter_crew" class="form-select spk-input" onchange="filterMcuCrewByGroup()">
                                            <option value="">Semua Crew</option>
                                            <option value="A">Crew A</option>
                                            <option value="B">Crew B</option>
                                            <option value="C">Crew C</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">Cari crew</label>
                                        <div class="crew-search-wrap">
                                            <input type="text" id="mcu_crew_search" class="form-control spk-input"
                                                placeholder="Ketik nama crew..." autocomplete="off"
                                                onfocus="showCrewDropdown('mcu_crew_search', 'mcu_crew_options')"
                                                oninput="filterCrewOptions('mcu_crew_search', 'mcu_crew_options')">
                                            <div id="mcu_crew_options" class="crew-options" style="display:none;"></div>
                                        </div>
                                        <input type="hidden" id="mcu_id_crew" value="">
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Nama / Name</label>
                                        <input type="text" id="mcu_nama" class="form-control spk-input" placeholder="Nama karyawan" oninput="buildMcuIsi()">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Umur / Age</label>
                                        <input type="text" id="mcu_umur" class="form-control spk-input" placeholder="_____ Thn" oninput="buildMcuIsi()">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Jenis Kelamin / Sex</label>
                                        <select id="mcu_jenis_kelamin" class="form-select spk-input" onchange="buildMcuIsi()">
                                            <option value="Laki-Laki">Laki-Laki</option>
                                            <option value="Perempuan">Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Pekerjaan / Job Title</label>
                                        <input type="text" id="mcu_pekerjaan" class="form-control spk-input" placeholder="Jabatan" oninput="buildMcuIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Perusahaan / Company</label>
                                        <input type="text" id="mcu_perusahaan" class="form-control spk-input" value="PT SIGMA / Greatwall Drilling Asia Pasific" oninput="buildMcuIsi()">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Alamat / Address</label>
                                        <input type="text" id="mcu_alamat" class="form-control spk-input" placeholder="Alamat" oninput="buildMcuIsi()">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Blood type</label>
                                        <input type="text" id="mcu_blood_type" class="form-control spk-input" value="-" oninput="buildMcuIsi()">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">NIK ID Card</label>
                                        <input type="text" id="mcu_nik" class="form-control spk-input" placeholder="NIK" oninput="buildMcuIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">MCU Requirement</label>
                                        <select id="mcu_requirement" class="form-select spk-input" onchange="buildMcuIsi()">
                                            <option value="MCU PHR (Pre-Employee)">MCU PHR (Pre-Employee)</option>
                                            <option value="MCU PHR (Annual-Employee)">MCU PHR (Annual-Employee)</option>
                                        </select>
                                    </div>
                                </div>
                            </section>

                            <section class="mcu-panel">
                                <h5>2. Detail MCU</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Tanggal Surat</label>
                                        <input type="date" id="mcu_tgl_surat" class="form-control spk-input" value="<?= date('Y-m-d') ?>" oninput="buildMcuIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Date MCU</label>
                                        <input type="date" id="mcu_date_mcu" class="form-control spk-input" value="<?= date('Y-m-d') ?>" oninput="buildMcuIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Location MCU</label>
                                        <input type="text" id="mcu_location_mcu" class="form-control spk-input" value="RS Mutia Sari Duri" oninput="buildMcuIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Location Job</label>
                                        <input type="text" id="mcu_location_job" class="form-control spk-input" value="Field Worker / Pekerja Rig" oninput="buildMcuIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Rig Location</label>
                                        <input type="text" id="mcu_rig_location" class="form-control spk-input" readonly placeholder="Auto dari crew" oninput="buildMcuIsi()">
                                        <small class="text-muted d-block">Terisi otomatis dari data crew.</small>
                                    </div>
                                </div>
                            </section>

                            <section class="mcu-panel">
                                <h5>3. Keluhan & Pemeriksaan</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Keluhan / Complaint</label>
                                        <textarea id="mcu_keluhan" class="form-control spk-input" rows="2" oninput="buildMcuIsi()">-</textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Pemeriksaan Fisik atau Penunjang / Physical or Supporting Examination</label>
                                        <textarea id="mcu_pemeriksaan" class="form-control spk-input" rows="2" oninput="buildMcuIsi()">-</textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Diagnosa Sementara / Working Diagnosis</label>
                                        <textarea id="mcu_diagnosa" class="form-control spk-input" rows="2" oninput="buildMcuIsi()">-</textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Pengobatan & Tindakan Sementara / Medicine & Supportive Treatment</label>
                                        <textarea id="mcu_pengobatan" class="form-control spk-input" rows="2" oninput="buildMcuIsi()">-</textarea>
                                    </div>
                                </div>
                            </section>

                            <section class="mcu-panel">
                                <h5>4. Penandatangan</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Nama Penandatangan</label>
                                        <input type="text" id="mcu_signer" class="form-control spk-input" value="Rifqi Haidi" oninput="buildMcuIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Jabatan Penandatangan</label>
                                        <input type="text" id="mcu_signer_title" class="form-control spk-input" value="HSE Coordinator PT GDAP" oninput="buildMcuIsi()">
                                    </div>
                                </div>
                                <input type="hidden" name="perihal" id="mcu_perihal" value="Surat Pengajuan MCU Crew">
                            </section>
                        </div>

                        <aside class="mcu-preview-wrap">
                            <h5><i class="fas fa-eye"></i> Preview live</h5>
                            <div class="mcu-preview">
                                <div class="st-preview-head">
                                    <div class="st-preview-title">SURAT PENGANTAR MCU</div>
                                    <div>No: <span id="mcup_nomor">auto</span></div>
                                </div>
                                <div class="st-preview-meta">
                                    <div><strong>Nama:</strong> <span id="mcup_nama">...</span></div>
                                    <div><strong>Pekerjaan:</strong> <span id="mcup_pekerjaan">...</span></div>
                                    <div><strong>Rig:</strong> <span id="mcup_rig">...</span></div>
                                    <div><strong>MCU Type:</strong> <span id="mcup_type">Pre-Employee</span></div>
                                </div>
                                <div class="st-preview-body">
                                    <p id="mcup_body">Preview akan muncul di sini saat Anda mengisi data.</p>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>

                <!--  FIELD SC  -->
                <div id="sc-fields" style="display:none;">
                    <div class="sc-workspace">
                        <div class="sc-left">
                            <section class="sc-panel">
                                <h5>1. Tujuan panggilan</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Kepada</label>
                                        <input type="text" name="tujuan" id="sc_tujuan" class="form-control spk-input"
                                            list="sc-tujuan-list" oninput="buildScIsi()">
                                        <datalist id="sc-tujuan-list">
                                           
                                            <option value="All Rig"></option>
                                            <option value="Semua Rig"></option>
                                            <option value="Crew terkait"></option>
                                        </datalist>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Posisi</label>
                                        <select id="sc_positions" class="form-select spk-input" multiple data-placeholder="Pilih satu atau lebih posisi" onchange="buildScIsi()">
                                            <?php foreach ($scPositionOptions as $posisiOption): ?>
                                                <option value="<?= htmlspecialchars($posisiOption) ?>">
                                                    <?= htmlspecialchars($posisiOption) ?>
                                                </option>
                                            <?php endforeach; ?>
                                            <option value="Roustabout">Roustabout</option>
                                            <option value="Roomboy">Roomboy</option>
                                            <option value="Accs.Control">Accs.Control</option>
                                            <option value="Floorman">Floorman</option>
                                            <option value="Driller">Driller</option>
                                            <option value="Assistant Driller">Assistant Driller</option>
                                            <option value="Rig Manager">Rig Manager</option>
                                            <option value="Mechanic">Mechanic</option>
                                            <option value="Electrician">Electrician</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Rig</label>
                                        <input type="text" id="sc_rigs" class="form-control spk-input" list="sc-rig-list" oninput="buildScIsi()">
                                        <datalist id="sc-rig-list">
                                            <?php foreach ($scRigOptions as $rigOption): ?>
                                                <option value="<?= htmlspecialchars($rigOption) ?>"></option>
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Maksud Panggilan</label>
                                        <input type="text" id="sc_purpose" class="form-control spk-input" value="menandatangani ulang perjanjian kontrak kerja (Revisi PKWT yang lama), karena terdapat perubahan pada struktur gaji baru dan hal lainnya" oninput="buildScIsi()">
                                    </div>
                                </div>
                            </section>

                            <section class="sc-panel">
                                <h5>2. Jadwal hadir</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Tanggal Panggilan</label>
                                        <input type="date" id="sc_call_date" class="form-control spk-input" oninput="buildScIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Jam</label>
                                        <input type="time" id="sc_call_time" class="form-control spk-input" value="10:00" oninput="buildScIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Tanggal Surat</label>
                                        <input type="date" id="sc_letter_date" class="form-control spk-input" value="<?= date('Y-m-d') ?>" oninput="buildScIsi()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Tempat Surat</label>
                                        <input type="text" id="sc_place" class="form-control spk-input" value="Duri" oninput="buildScIsi()">
                                    </div>
                                </div>
                                <input type="hidden" name="perihal" id="sc_perihal" value="Surat Panggilan">
                            </section>
                        </div>

                        <aside class="sc-preview-wrap">
                            <h5><i class="fas fa-eye"></i> Preview live</h5>
                            <div class="sc-preview">
                                <div class="st-preview-head">
                                    <div class="st-preview-title">SURAT PANGGILAN</div>
                                    <div>No: <span id="scp_nomor">auto</span></div>
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

                <div class="surat-form-actions" id="surat-form-actions">
                    <button type="submit" class="btn btn-sigma btn-lg" id="submit-surat">
                        <i class="fas fa-save"></i> Simpan & Generate Nomor
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .pkwt-workspace { display:grid; grid-template-columns:minmax(0, 1fr) minmax(280px, .72fr); gap:20px; }
        .pkwt-panel, .pkwt-preview-wrap { border:1px solid var(--border); border-radius:12px; background:#fff; padding:20px; }
        .pkwt-preview-wrap { align-self:start; position:sticky; top:16px; background:#f8f8fb; }
        .pkwt-preview { border:1px solid var(--border); border-radius:10px; background:#fff; padding:22px; font-size:13px; }
        .pkwt-preview p { margin:0; text-align:justify; }
        @media (max-width: 992px) { .pkwt-workspace { grid-template-columns:1fr; } .pkwt-preview-wrap { position:static; } }
        .spk-workspace,
        .mcu-workspace {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(310px, .95fr);
            gap: 20px;
            margin-top: 6px;
        }

        .spk-left,
        .st-left,
        .sc-left,
        .ska-left,
        .sp-left,
        .spt-left,
        .mcu-left {
            display: grid;
            gap: 16px;
        }

        .spk-workspace,
        .st-workspace,
        .sc-workspace,
        .ska-workspace,
        .sp-workspace,
        .spt-workspace,
        .mcu-workspace {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(310px, .95fr);
            gap: 20px;
            margin-top: 6px;
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
        .spt-preview-wrap,
        .mcu-panel,
        .mcu-preview-wrap {
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

        .spk-preview-wrap,
        .st-preview-wrap,
        .sc-preview-wrap,
        .ska-preview-wrap,
        .sp-preview-wrap,
        .spt-preview-wrap {
            align-self: start;
            position: sticky;
            top: 16px;
            background: #ffffff;
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

        .sc-panel .select2-container {
            width: 100% !important;
        }

        .sc-panel .select2-container--default .select2-selection--multiple {
            min-height: 48px;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 4px 8px;
        }

        .sc-panel .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: var(--pk);
            box-shadow: 0 0 0 .2rem rgba(196, 44, 44, .12);
        }

        .sc-panel .select2-container--default .select2-selection--multiple .select2-selection__choice {
            border: 0;
            border-radius: 999px;
            background: var(--pk4);
            color: var(--pk);
            padding: 4px 9px 4px 22px;
            font-size: 13px;
        }

        .sc-panel .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            border-right: 0;
            color: var(--pk);
            padding-left: 6px;
        }

        .spk-preview-kop {
            margin-bottom: 22px;
            border-bottom: 1px solid #d9dee8;
            padding-bottom: 12px;
        }

        .spk-preview-kop span,
        .spk-preview-kop strong {
            display: block;
            height: 9px;
            border-radius: 2px;
            background: #edf0f5;
        }

        .spk-preview-kop span {
            width: 72%;
            margin-bottom: 6px;
        }

        .spk-preview-kop strong {
            width: 45%;
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

        .spk-panel .form-label,
        .st-panel .form-label,
        .sc-panel .form-label,
        .ska-panel .form-label,
        .sp-panel .form-label,
        .spt-panel .form-label {
            font-weight: 600;
        }

        .spk-panel h5,
        .st-panel h5,
        .sc-panel h5,
        .ska-panel h5,
        .sp-panel h5,
        .spt-panel h5,
        .spk-preview-wrap h5,
        .st-preview-wrap h5,
        .sc-preview-wrap h5,
        .ska-preview-wrap h5,
        .sp-preview-wrap h5,
        .spt-preview-wrap h5 {
            margin: 0 0 14px;
            color: var(--text);
            font-size: 15px;
            font-weight: 700;
        }

        .surat-form-actions {
            display: flex;
            justify-content: flex-start;
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }

        .surat-form-actions .btn {
            min-width: 230px;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            line-height: 1.2;
        }

        .surat-form-actions.is-spk {
            width: calc((100% - 20px) * 0.512);
            border-top: 0;
            padding-top: 0;
            margin-top: 16px;
        }

        .surat-form-actions.is-spk .btn {
            width: 100%;
            min-width: 0;
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

            .surat-form-actions.is-spk {
                width: 100%;
            }
        }
    </style>

    <script>
        let selectedCrewData = null;
        let crewSearchOptions = <?= json_encode($crewSearchOptions) ?>;
        let scAllRigs = <?= json_encode($scRigOptions) ?>;

        // Toggle field
        document.addEventListener('DOMContentLoaded', () => {
            toggleFields();
            updateSpkPreview();
            buildAllCrewDropdowns();
            initScPositionSelect();
        });

        document.getElementById('tujuan_spk').addEventListener('input', updateSpkPreview);
        document.getElementById('perihal_spk').addEventListener('input', updateSpkPreview);

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.crew-search-wrap')) {
                    ['crew-options', 'pkwt_crew_options', 'st_crew_options', 'ska_crew_options', 'sp_crew_options', 'sr_crew_options', 'skk_crew_options', 'ba_crew_options', 'sj_crew_options', 'mcu_crew_options'].forEach(optionsId => {
                    const options = document.getElementById(optionsId);
                    if (options) options.style.display = 'none';
                });
            }
        });

        function toggleFields() {
            const jenis = document.getElementById('id_jenis');
            const selectedOption = jenis.options[jenis.selectedIndex];
            const code = selectedOption?.dataset.code || '';
            const submitSurat = document.getElementById('submit-surat');
            const formActions = document.getElementById('surat-form-actions');
            const isSpk = code === 'SPK';
            const isSpm = code === 'SPM';
            const isPhk = code === 'PHK';
            const isSt = code === 'ST';
            const isSc = code === 'SC';
            const isSka = code === 'SKA';
            const isSp = code === 'SP';
            const isSpt = code === 'SPT';
            const isSr = code === 'SR';
            const isSkk = code === 'SKK';
            const isBa = code === 'BA';
            const isSj = code === 'SJ';
            const isMm = code === 'MM';
            const isMcu = code === 'MCU';
            const isPkwt = code === 'PKWT';
            const specialCodes = ['SPK', 'SPM', 'PHK', 'ST', 'SC', 'SKA', 'SP', 'SPT', 'SR', 'SKK', 'BA', 'SJ', 'MM', 'MCU', 'PKWT'];
            const fieldIds = ['pkwt-fields', 'spm-fields', 'phk-fields', 'spk-task-fields', 'spk-fields', 'st-fields', 'sc-fields', 'ska-fields', 'sp-fields', 'spt-fields', 'sr-fields', 'skk-fields', 'ba-fields', 'sj-fields', 'mm-fields', 'mcu-fields'];

            document.querySelectorAll('#umum-fields input, #umum-fields textarea, #umum-fields select').forEach(el => {
                el.disabled = specialCodes.includes(code);
            });

            fieldIds.forEach(id => {
                const isActive = (id === code.toLowerCase() + '-fields') || (code === 'SPK' && id === 'spk-task-fields');
                const isLegacySpk = code === 'SPK' && id === 'spk-fields';
                const fields = document.getElementById(id);
                if (!fields) return;
                fields.style.display = (isActive && !isLegacySpk) ? 'block' : 'none';
                fields.querySelectorAll('input, textarea, select').forEach(el => {
                    el.disabled = !isActive || isLegacySpk;
                });
            });

            document.getElementById('umum-fields').style.display = specialCodes.includes(code) ? 'none' : 'block';
            formActions.classList.toggle('is-spk', isSpk);

            if (isSpk || isSpm || isPhk) {
                submitSurat.innerHTML = '<i class="fas fa-file-signature"></i> Simpan & Generate Nomor';
                buildTemplateSuratIsi();
            } else if (isSt) {
                submitSurat.innerHTML = '<i class="fas fa-file-signature"></i> Simpan & Generate Nomor';
                if (selectedCrewData) {
                    applySelectedCrewToForms(selectedCrewData);
                } else {
                    buildStIsi();
                }
            } else if (isSc) {
                submitSurat.innerHTML = '<i class="fas fa-envelope-open-text"></i> Simpan & Generate Nomor';
                buildScIsi();
            } else if (isSka) {
                submitSurat.innerHTML = '<i class="fas fa-file-alt"></i> Simpan & Generate Nomor';
                if (selectedCrewData) {
                    applySelectedCrewToForms(selectedCrewData);
                } else {
                    buildSkaIsi();
                }
            } else if (isSp) {
                submitSurat.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Simpan & Generate Nomor';
                if (selectedCrewData) {
                    applySelectedCrewToForms(selectedCrewData);
                } else {
                    buildSpIsi();
                }
            } else if (isSpt) {
                submitSurat.innerHTML = '<i class="fas fa-bullhorn"></i> Simpan & Generate Nomor';
                buildSptIsi();
            } else if (isSr) {
                submitSurat.innerHTML = '<i class="fas fa-file-contract"></i> Simpan & Generate Nomor';
                buildSrIsi();
            } else if (isSkk) {
                submitSurat.innerHTML = '<i class="fas fa-id-card"></i> Simpan & Generate Nomor';
                if (selectedCrewData) applySelectedCrewToForms(selectedCrewData);
                buildSkkIsi();
            } else if (isBa) {
                submitSurat.innerHTML = '<i class="fas fa-clipboard-check"></i> Simpan & Generate Nomor';
                if (selectedCrewData) applySelectedCrewToForms(selectedCrewData);
                buildBaIsi();
            } else if (isSj) {
                submitSurat.innerHTML = '<i class="fas fa-file-signature"></i> Simpan & Generate Nomor';
                if (selectedCrewData) applySelectedCrewToForms(selectedCrewData);
                buildSjIsi();
            } else if (isMm) {
                submitSurat.innerHTML = '<i class="fas fa-envelope-open-text"></i> Simpan & Generate Nomor';
                toggleMemoModel();
                buildMmIsi();
            } else if (isPkwt) {
                submitSurat.innerHTML = '<i class="fas fa-file-contract"></i> Simpan PKWT';
                updatePkwtCrew();
            } else {
                submitSurat.innerHTML = '<i class="fas fa-save"></i> Simpan & Generate Nomor';
            }
        }

        function updatePkwtCrew() {
            const crewId = document.getElementById('pkwt_id_crew')?.value || '';
            const crew = crewSearchOptions.find(item => String(item.id) === crewId);
            const position = crew?.posisi || '';
            const template = document.getElementById('pkwt_template');
            if (template && position) {
                const match = [...template.options].find(o => o.value.toLowerCase() === position.toLowerCase());
                if (match) template.value = match.value;
            }
            const info = document.getElementById('pkwt_crew_info');
            if (info) info.textContent = crew ? `${crew.label} — Alamat: ${crew.alamat || '-'}` : 'Pilih rig, crew, dan posisi untuk mengisi data otomatis.';
            
            // Display crew classification (A, B, or C)
            const crewClass = document.getElementById('pkwt_crew_class');
            const crewDetails = document.getElementById('pkwt_crew_details');
            if (crewClass && crewDetails) {
                if (crew && crew.crew) {
                    crewClass.textContent = 'Crew ' + crew.crew;
                    crewDetails.style.display = 'block';
                } else {
                    crewClass.textContent = '-';
                    crewDetails.style.display = 'none';
                }
            }
            
            document.getElementById('pkwtp_nama').textContent = crew?.nama || '...';
            document.getElementById('pkwtp_posisi').textContent = crew?.posisi || '...';
            document.getElementById('pkwtp_rig').textContent = document.getElementById('top_rig_select')?.selectedOptions[0]?.text || '...';
            updatePkwtPreview();
        }
        document.getElementById('top_rig_select')?.addEventListener('change', filterPkwtCrewByRig);
        function filterPkwtCrewByRig() {
            const rig = document.getElementById('top_rig_select')?.value;
            const keyword = (document.getElementById('pkwt_crew_search')?.value || '').toLowerCase().trim();
            const options = document.getElementById('pkwt_crew_options');
            if (!rig || !options) return;
            const results = crewSearchOptions.filter(item => String(item.rig) === String(rig) && (!keyword || item.label.toLowerCase().includes(keyword)));
            options.innerHTML = '';
            results.forEach(item => {
                const button = document.createElement('button');
                button.type = 'button'; button.className = 'crew-option'; button.textContent = item.label;
                button.addEventListener('click', () => {
                    document.getElementById('pkwt_id_crew').value = item.id;
                    document.getElementById('pkwt_crew_search').value = item.label;
                    options.style.display = 'none';
                    updatePkwtCrew();
                });
                options.appendChild(button);
            });
            options.style.display = results.length ? 'block' : 'none';
        }
        filterPkwtCrewByRig();
        document.getElementById('pkwt_crew_search')?.addEventListener('focus', filterPkwtCrewByRig);
        document.getElementById('pkwt_crew_search')?.addEventListener('input', () => {
            document.getElementById('pkwt_id_crew').value = '';
            filterPkwtCrewByRig();
        });
        ['pkwt_tanggal_mulai', 'pkwt_tanggal_berakhir'].forEach(id => document.getElementById(id)?.addEventListener('input', updatePkwtPreview));
        function updatePkwtPreview() {
            const mulai = document.getElementById('pkwt_tanggal_mulai')?.value || '...';
            const berakhir = document.getElementById('pkwt_tanggal_berakhir')?.value || '...';
            document.getElementById('pkwtp_periode').textContent = `${mulai} s/d ${berakhir}`;
            const nama = document.getElementById('pkwtp_nama')?.textContent || 'crew';
            document.getElementById('pkwtp_body').textContent = `PKWT akan dibuat untuk ${nama} sesuai posisi dan periode kerja yang dipilih.`;
        }

        function buildTemplateSuratIsi() {
            const code = document.getElementById('id_jenis')?.options[document.getElementById('id_jenis').selectedIndex]?.dataset.code || '';
            const val = id => (document.getElementById(id)?.value || '').trim();
            let content = '', subject = '', recipient = '';
            if (code === 'SPM') {
                subject = 'Surat Penunjukan Mentor'; recipient = val('spm_mentor');
                content = ['Mentor: ' + val('spm_mentor'), 'Jabatan Mentor: ' + val('spm_mentor_position'), 'Mentee: ' + val('spm_mentee'), 'Jabatan Mentee: ' + val('spm_mentee_position'), 'Periode: ' + val('spm_period'), 'Ruang Lingkup: ' + val('spm_scope'), 'Penandatangan: ' + val('spm_signer')].join('\n');
            } else if (code === 'PHK') {
                subject = 'Surat Pemutusan Hubungan Kerja'; recipient = val('phk_name');
                content = ['Nama Karyawan: ' + val('phk_name'), 'Jabatan: ' + val('phk_position'), 'Nomor Badge: ' + val('phk_badge'), 'Tanggal Efektif: ' + val('phk_effective'), 'Alasan: ' + val('phk_reason'), 'Penandatangan: ' + val('phk_signer'), 'Jabatan Penandatangan: ' + val('phk_signer_title')].join('\n');
            } else if (code === 'SPK') {
                subject = 'Surat Tugas'; recipient = val('spk_task_name');
                content = ['Nama Personel: ' + val('spk_task_name'), 'Jabatan: ' + val('spk_task_position'), 'Lokasi: ' + val('spk_task_location'), 'Periode: ' + val('spk_task_period'), 'Uraian Tugas: ' + val('spk_task_description'), 'Penandatangan: ' + val('spk_task_signer'), 'Jabatan Penandatangan: ' + val('spk_task_signer_title')].join('\n');
            } else return;
            document.getElementById('isi_surat_generated').value = content;
            const isi = document.getElementById('isi_surat'); if (isi) isi.value = content;
            const prefix = code === 'SPK' ? 'spk_task' : code.toLowerCase();
            const tujuan = document.getElementById(prefix + '_tujuan'); if (tujuan) tujuan.value = recipient;
            const perihal = document.getElementById(prefix + '_perihal'); if (perihal) perihal.value = subject;
            updateTemplateSuratPreview(code, val);
        }

        function updateTemplateSuratPreview(code, val) {
            const put = (id, value, fallback = '...') => { const el = document.getElementById(id); if (el) el.textContent = value || fallback; };
            if (code === 'SPM') { put('spmp_mentor', val('spm_mentor')); put('spmp_mentee', val('spm_mentee')); put('spmp_period', val('spm_period')); put('spmp_scope', val('spm_scope'), 'Isi ruang lingkup pendampingan akan tampil di sini.'); }
            if (code === 'PHK') { put('phkp_name', val('phk_name')); put('phkp_position', val('phk_position')); put('phkp_effective', val('phk_effective')); put('phkp_reason', val('phk_reason'), 'Alasan atau dasar PHK akan tampil di sini.'); }
            if (code === 'SPK') { put('spkp_name', val('spk_task_name')); put('spkp_position', val('spk_task_position')); put('spkp_location', val('spk_task_location')); put('spkp_period', val('spk_task_period')); put('spkp_description', val('spk_task_description'), 'Uraian tugas akan tampil di sini.'); }
        }

        function buildAllCrewDropdowns() {
            buildCrewDropdown('crew-options');
            buildCrewDropdown('st_crew_options');
            buildCrewDropdown('ska_crew_options');
            buildCrewDropdown('sp_crew_options');
            buildCrewDropdown('sr_crew_options');
            buildCrewDropdown('skk_crew_options');
            buildCrewDropdown('ba_crew_options');
            buildCrewDropdown('sj_crew_options');
            buildCrewDropdown('spm_mentor_options');
            buildCrewDropdown('spm_mentee_options');
            buildCrewDropdown('phk_crew_options');
            buildCrewDropdown('spk_task_crew_options');
            buildCrewDropdown('mcu_crew_options');
        }

        function buildCrewDropdown(optionsId = 'crew-options') {
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
                    ba_crew_options: ['ba_crew_search', 'ba_id_crew'],
                    sj_crew_options: ['sj_crew_search', 'sj_id_crew'],
                    spm_mentor_options: ['spm_mentor_search', 'spm_mentor_crew_id'],
                    spm_mentee_options: ['spm_mentee_search', 'spm_mentee_crew_id'],
                    phk_crew_options: ['phk_crew_search', 'phk_crew_id'],
                    spk_task_crew_options: ['spk_task_crew_search', 'spk_task_crew_id'],
                    mcu_crew_options: ['mcu_crew_search', 'mcu_id_crew']
                };
                const target = targetMap[optionsId] || ['crew-search', 'id_crew'];
                button.addEventListener('click', () => selectCrewOption(item.id, item.label, target[0], optionsId, target[1]));
                wrap.appendChild(button);
            });
        }

        function showCrewDropdown(inputId = 'crew-search', optionsId = 'crew-options') {
            const wrap = document.getElementById(optionsId);
            if (!wrap) return;
            filterCrewOptions(inputId, optionsId);
            wrap.style.display = 'block';
        }

        function filterCrewOptions(inputId = 'crew-search', optionsId = 'crew-options') {
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

        function selectCrewOption(id, label, inputId = 'crew-search', optionsId = 'crew-options', hiddenId = 'id_crew') {
            const inputEl = document.getElementById(inputId);
            const optionsEl = document.getElementById(optionsId);
            const hiddenEl = document.getElementById(hiddenId);
            if (inputEl) inputEl.value = label;
            if (optionsEl) optionsEl.style.display = 'none';
            if (hiddenEl) hiddenEl.value = id;

            const genericHidden = document.getElementById('id_crew');
            if (genericHidden && hiddenId !== 'id_crew') {
                genericHidden.value = id;
            }
            const stHidden = document.getElementById('st_id_crew');
            if (stHidden && hiddenId !== 'st_id_crew') {
                stHidden.value = id;
            }
            const skaHidden = document.getElementById('ska_id_crew');
            if (skaHidden && hiddenId !== 'ska_id_crew') {
                skaHidden.value = id;
            }
            const spHidden = document.getElementById('sp_id_crew');
            if (spHidden && hiddenId !== 'sp_id_crew') {
                spHidden.value = id;
            }
            const srHidden = document.getElementById('sr_id_crew');
            if (srHidden && hiddenId !== 'sr_id_crew') {
                srHidden.value = id;
            }
            const skkHidden = document.getElementById('skk_id_crew');
            if (skkHidden && hiddenId !== 'skk_id_crew') {
                skkHidden.value = id;
            }
            const baHidden = document.getElementById('ba_id_crew');
            if (baHidden && hiddenId !== 'ba_id_crew') {
                baHidden.value = id;
            }
            const sjHidden = document.getElementById('sj_id_crew');
            if (sjHidden && hiddenId !== 'sj_id_crew') {
                sjHidden.value = id;
            }

            getCrewData(id, optionsId);
        }

        function getCrewData(id_crew, sourceOptionsId = '') {
            if (!id_crew) {
                selectedCrewData = null;
                document.getElementById('crew-search').value = '';
                document.getElementById('id_crew').value = '';
                document.getElementById('nama_crew').value = '';
                document.getElementById('posisi_crew').value = '';
                document.getElementById('rig_crew').value = '';
                document.getElementById('crew_group').value = '';
                document.getElementById('badge_crew').value = '';
                document.getElementById('isi_surat_generated').value = '';
                setCrewChips();
                setSpCrewChips();
                updateSpkPreview();
                return;
            }
            fetch('<?= BASE_URL ?>/ajax/getCrewData/' + id_crew)
                .then(res => res.json())
                .then(data => {
                    selectedCrewData = data;
                    document.getElementById('nama_crew').value = data.nama;
                    document.getElementById('posisi_crew').value = data.posisi;
                    document.getElementById('rig_crew').value = data.kode_rig;
                    document.getElementById('crew_group').value = data.crew ? 'Crew ' + data.crew : '';
                    document.getElementById('badge_crew').value = data.nomor_badge;
                    setCrewChips(data);
                    applySelectedCrewToForms(data, sourceOptionsId);
                });
        }

        function applySelectedCrewToForms(data, sourceOptionsId = '') {
            const selectedCode = document.getElementById('id_jenis')?.options[document.getElementById('id_jenis').selectedIndex]?.dataset.code || '';
            if (selectedCode === 'SPK') {
                const name = document.getElementById('spk_task_name');
                const position = document.getElementById('spk_task_position');
                const location = document.getElementById('spk_task_location');
                if (name) name.value = data.nama || '';
                if (position) position.value = data.posisi || '';
                if (location) location.value = data.kode_rig || '';
                buildTemplateSuratIsi();
            } else if (selectedCode === 'PHK') {
                const name = document.getElementById('phk_name');
                const position = document.getElementById('phk_position');
                const badge = document.getElementById('phk_badge');
                if (name) name.value = data.nama || '';
                if (position) position.value = data.posisi || '';
                if (badge) badge.value = data.nomor_badge || '';
                buildTemplateSuratIsi();
            } else if (selectedCode === 'SPM') {
                if (sourceOptionsId === 'spm_mentee_options') {
                    document.getElementById('spm_mentee').value = data.nama || '';
                    document.getElementById('spm_mentee_position').value = data.posisi || '';
                } else {
                    document.getElementById('spm_mentor').value = data.nama || '';
                    document.getElementById('spm_mentor_position').value = data.posisi || '';
                }
                buildTemplateSuratIsi();
            } else if (selectedCode === 'ST') {
                const stName = document.getElementById('st_name');
                const stDepartment = document.getElementById('st_department');
                const stPosition = document.getElementById('st_position');
                if (stName) stName.value = data.nama || '';
                if (stDepartment) stDepartment.value = formatStDepartment(data.kode_rig, data.crew);
                if (stPosition) stPosition.value = data.posisi || '';
                buildStIsi();
            } else if (selectedCode === 'SKA') {
                const skaName = document.getElementById('ska_employee_name');
                const skaPosition = document.getElementById('ska_employee_title');
                if (skaName) skaName.value = data.nama || '';
                if (skaPosition) skaPosition.value = data.posisi || '';
                buildSkaIsi();
            } else if (selectedCode === 'SP') {
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
                buildSpIsi();
            } else if (selectedCode === 'SR') {
                const srTujuan = document.getElementById('sr_tujuan');
                const srPositions = document.getElementById('sr_positions');
                const srRigs = document.getElementById('sr_rigs');
                if (srTujuan) srTujuan.value = data.nama || '';
                if (srPositions && srPositions.tagName === 'SELECT') {
                    Array.from(srPositions.options).forEach(option => {
                        option.selected = option.value === (data.posisi || '');
                    });
                }
                if (srRigs) srRigs.value = data.kode_rig || '';
                buildSrIsi();
            } else if (selectedCode === 'SKK') {
                const skkName = document.getElementById('skk_name');
                const skkPosition = document.getElementById('skk_position');
                const skkTujuan = document.getElementById('skk_tujuan');
                if (skkName) skkName.value = data.nama || '';
                if (skkPosition) skkPosition.value = data.posisi || '';
                if (skkTujuan) skkTujuan.value = data.nama || '';
                buildSkkIsi();
            } else if (selectedCode === 'BA') {
                const baName = document.getElementById('ba_name');
                const baPosition = document.getElementById('ba_position');
                const baRig = document.getElementById('ba_rig');
                const baTujuan = document.getElementById('ba_tujuan');
                if (baName) baName.value = data.nama || '';
                if (baPosition) baPosition.value = data.posisi || '';
                if (baRig) baRig.value = data.kode_rig || '';
                if (baTujuan) baTujuan.value = data.nama || '';
                buildBaIsi();
            } else if (selectedCode === 'SJ') {
                const sjName = document.getElementById('sj_p1_name');
                const sjPosition = document.getElementById('sj_p1_position');
                const sjAddress = document.getElementById('sj_p1_address');
                const sjDomicile = document.getElementById('sj_p1_domicile');
                if (sjName) sjName.value = data.nama || '';
                if (sjPosition) sjPosition.value = data.posisi || '';
                if (sjAddress) sjAddress.value = data.alamat || '';
                if (sjDomicile) sjDomicile.value = data.alamat || '';
                buildSjIsi();
            } else if (selectedCode === 'MCU') {
                const mcuNama = document.getElementById('mcu_nama');
                const mcuUmur = document.getElementById('mcu_umur');
                const mcuPekerjaan = document.getElementById('mcu_pekerjaan');
                const mcuAlamat = document.getElementById('mcu_alamat');
                const mcuNik = document.getElementById('mcu_nik');
                const mcuRigLocation = document.getElementById('mcu_rig_location');
                const mcuSigner = document.getElementById('mcu_signer');
                const mcuSignerTitle = document.getElementById('mcu_signer_title');
                if (mcuNama) mcuNama.value = data.nama || '';
                if (mcuUmur) mcuUmur.value = data.umur || '';
                // Format EYD: Posisi huruf pertama kapital (contoh: Floorman)
                const posisiEyd = (data.posisi || '').toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
                // Ambil hanya angka dari kode rig (contoh: GW339 → 339)
                const rigOnly = (data.kode_rig || '').replace(/^[A-Za-z\s\-]+/, '');
                if (mcuPekerjaan) mcuPekerjaan.value = posisiEyd + ' ' + rigOnly;
                if (mcuAlamat) mcuAlamat.value = data.alamat || '';
                if (mcuNik) mcuNik.value = data.nik_ktp || '';
                if (mcuRigLocation) mcuRigLocation.value = 'Drilling ' + (data.kode_rig || '');
                // Auto-fill signer dengan nama crew
                if (mcuSigner) mcuSigner.value = data.nama || '';
                if (mcuSignerTitle) mcuSignerTitle.value = posisiEyd || '';
                // Auto-fill rig di bagian atas form
                const topRigSelect = document.getElementById('top_rig_select');
                const topRigInput = document.getElementById('top_rig_input');
                if (topRigSelect && data.id_rig) {
                    topRigSelect.value = data.id_rig;
                }
                if (topRigInput) {
                    topRigInput.value = data.kode_rig || '';
                }
                buildMcuIsi();
            }
        }

        function formatStDepartment(rig, crew) {
            const rigValue = (rig || '').trim();
            const crewValue = (crew || '').toString().trim();
            return [rigValue, crewValue].filter(Boolean).join(', ');
        }

        document.getElementById('posisi_vacant').addEventListener('input', () => {
            buildSpkIsi();
            updateSpkPreview();
        });

        function setCrewChips(data = null) {
            document.getElementById('chip_jabatan').textContent = 'Jabatan: ' + ((data && data.posisi) || '-');
            document.getElementById('chip_badge').textContent = 'Badge: ' + ((data && data.nomor_badge) || '-');
            document.getElementById('chip_rig').textContent = 'Rig: ' + ((data && data.kode_rig) || '-');
            document.getElementById('chip_crew').textContent = 'Crew: ' + ((data && data.crew) || '-');
        }

        function setSpCrewChips(data = null) {
            const values = data || {};
            document.getElementById('sp_chip_nama').textContent = 'Nama: ' + (values.nama || '-');
            document.getElementById('sp_chip_badge').textContent = 'Badge: ' + (values.nomor_badge || '-');
            document.getElementById('sp_chip_posisi').textContent = 'Posisi: ' + (values.posisi || '-');
            document.getElementById('sp_chip_rig').textContent = 'Rig: ' + (values.kode_rig || '-');
            document.getElementById('sp_chip_crew').textContent = 'Crew: ' + (values.crew || '-');
        }

        function buildSpkIsi() {
            if (!selectedCrewData) {
                updateSpkPreview();
                return;
            }

            const data = selectedCrewData;
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

        function buildStIsi() {
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
                return isNaN(d) ? value : d.toLocaleDateString('en-US', {
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric'
                });
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
            const values = {
                name: document.getElementById('st_name').value.trim() || '...',
                department: document.getElementById('st_department').value.trim() || '...',
                status: document.getElementById('st_status').value.trim() || '...',
                position: document.getElementById('st_position').value.trim() || '...',
                issueId: document.getElementById('st_issue_id').value.trim() || '...',
                issueEn: document.getElementById('st_issue_en').value.trim() || '...'
            };

            document.getElementById('stp_name').textContent = values.name;
            document.getElementById('stp_department').textContent = values.department;
            document.getElementById('stp_status').textContent = values.status;
            document.getElementById('stp_position').textContent = values.position;
            document.getElementById('stp_body').textContent = values.issueId || values.issueEn;
        }

        function buildSkaIsi() {
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

        function formatDateId(value, withDay = false) {
            if (!value) return '';
            const d = new Date(value + 'T00:00:00');
            if (isNaN(d)) return value;
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const dateText = d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
            return withDay ? days[d.getDay()] + ', ' + dateText : dateText;
        }

        function initScPositionSelect() {
            if (!window.jQuery || !jQuery.fn.select2) return;

            [
                ['#sc_positions', buildScIsi],
        ['#mm_positions', buildMmIsi],
        ['#spt_positions', buildSptIsi],
        ['#sr_positions', buildSrIsi],
        ['#skk_positions', buildSkkIsi],
        ['#ba_positions', buildBaIsi]
            ].forEach(([selector, handler]) => {
                if (!document.querySelector(selector)) return;
                jQuery(selector).select2({
                    width: '100%',
                    placeholder: 'Pilih satu atau lebih posisi',
                    closeOnSelect: false
                }).on('change', handler);
            });
        }

        function buildScIsi() {
            syncScRigsWithTujuan();

            const values = {
                tujuan: document.getElementById('sc_tujuan').value.trim(),
                positions: getScSelectedPositions(),
                rigs: document.getElementById('sc_rigs').value.trim(),
                purpose: document.getElementById('sc_purpose').value.trim(),
                callDate: document.getElementById('sc_call_date').value,
                callTime: document.getElementById('sc_call_time').value,
                letterDate: document.getElementById('sc_letter_date').value,
                place: document.getElementById('sc_place').value.trim()
            };

            const callTime = values.callTime ? values.callTime.replace(':', '.') + ' Wib' : '10.00 Wib';
            const content = [
                'Kepada: ' + (values.tujuan || 'All crew'),
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

        function getScSelectedPositions() {
            return getSelectedOptionsText('sc_positions');
        }

        function getSelectedOptionsText(elementId) {
            const positionsEl = document.getElementById(elementId);
            if (!positionsEl) return '';

            return Array.from(positionsEl.selectedOptions)
                .map(option => option.value.trim())
                .filter(Boolean)
                .join(', ');
        }

        function syncScRigsWithTujuan() {
            syncRigsWithTujuan('sc_tujuan', 'sc_rigs');
        }

        function syncRigsWithTujuan(tujuanId, rigsId) {
            const tujuanEl = document.getElementById(tujuanId);
            const rigsEl = document.getElementById(rigsId);
            if (!tujuanEl || !rigsEl) return;

            const tujuan = tujuanEl.value.trim().toLowerCase();
            const allRigLabels = ['all rig', 'all rigs', 'semua rig', 'semua rigs'];
            if (allRigLabels.includes(tujuan) && Array.isArray(scAllRigs) && scAllRigs.length) {
                rigsEl.value = scAllRigs.join(', ');
            }
        }

        function updateScPreview(values = null, callTime = null) {
            values = values || {
                tujuan: document.getElementById('sc_tujuan').value.trim(),
                positions: getScSelectedPositions(),
                rigs: document.getElementById('sc_rigs').value.trim(),
                purpose: document.getElementById('sc_purpose').value.trim(),
                callDate: document.getElementById('sc_call_date').value
            };
            callTime = callTime || ((document.getElementById('sc_call_time').value || '10:00').replace(':', '.') + ' Wib');

            document.getElementById('scp_tujuan').textContent = values.tujuan || 'All crew';
            document.getElementById('scp_positions').textContent = values.positions || '...';
            document.getElementById('scp_rigs').textContent = values.rigs || '...';
            document.getElementById('scp_schedule').textContent = (formatDateId(values.callDate, true) || '...') + ' / ' + callTime;
            document.getElementById('scp_body').textContent =
                'Bersama surat ini kami menyampaikan kepada saudara posisi yang disebutkan diatas agar dapat hadir kekantor, untuk ' +
                (values.purpose || '...') + '. Maka dari itu dihimbau untuk dapat hadir ke kantor pada:';
        }

        function buildSptIsi() {
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

        function buildSpIsi() {
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

        //  SR - Surat Resign 
        function syncSrRigsWithTujuan() {
            syncRigsWithTujuan('sr_tujuan', 'sr_rigs');
        }

        function buildSrIsi() {
            syncSrRigsWithTujuan();

            const values = {
                tujuan: document.getElementById('sr_tujuan').value.trim(),
                positions: getSrSelectedPositions(),
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

        function getSrSelectedPositions() {
            return getSelectedOptionsText('sr_positions');
        }

        function updateSrPreview(values = null) {
            values = values || {
                tujuan: document.getElementById('sr_tujuan').value.trim(),
                positions: getSrSelectedPositions(),
                rigs: document.getElementById('sr_rigs').value.trim()
            };

            document.getElementById('srp_tujuan').textContent = values.tujuan || 'All Crew';
            document.getElementById('srp_positions').textContent = values.positions || '...';
            document.getElementById('srp_rigs').textContent = values.rigs || '...';

            const bodyText = 'Bersama surat ini kami sampaikan ketentuan pengunduran diri (resign) dan penalty kontrak bagi karyawan ' + (values.positions || 'seluruh posisi') + ' di rig ' + (values.rigs || 'seluruh rig') + '.';
            document.getElementById('srp_body').textContent = bodyText;
        }

        //  SKK - Surat Keterangan Kerja 
        function syncSkkRigsWithTujuan() {
            syncRigsWithTujuan('skk_tujuan', 'skk_rigs');
        }

        function getSkkSelectedPositions() {
            return getSelectedOptionsText('skk_positions');
        }

        function buildSkkIsi() {
            syncSkkRigsWithTujuan();

            const values = {
                tujuan: document.getElementById('skk_tujuan').value.trim(),
                positions: getSkkSelectedPositions(),
                rigs: document.getElementById('skk_rigs').value.trim(),
                name: document.getElementById('skk_name').value.trim(),
                position: document.getElementById('skk_position').value.trim(),
                project: document.getElementById('skk_project').value.trim(),
                startDate: document.getElementById('skk_start_date').value,
                letterDate: document.getElementById('skk_letter_date').value,
                place: document.getElementById('skk_place').value.trim()
            };

            const displayName = values.name ? values.name : (values.tujuan ? values.tujuan : 'Crew terkait');
            const displayPos = values.position ? values.position : (values.positions ? values.positions : '');

            const content = [
                'Kepada: ' + (values.tujuan || 'Crew terkait'),
                'Posisi: ' + (values.positions || values.position || ''),
                'Rigs: ' + (values.rigs || ''),
                'Nama: ' + displayName,
                'Jabatan: ' + displayPos,
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
            values = values || {
                tujuan: document.getElementById('skk_tujuan') ? document.getElementById('skk_tujuan').value.trim() : '',
                positions: getSkkSelectedPositions(),
                name: document.getElementById('skk_name').value.trim(),
                position: document.getElementById('skk_position').value.trim(),
                project: document.getElementById('skk_project').value.trim()
            };

            const displayName = values.name ? values.name : (values.tujuan ? values.tujuan : 'Crew terkait');
            const displayPos = values.position ? values.position : (values.positions ? values.positions : '-');

            document.getElementById('skkp_name').textContent = displayName;
            document.getElementById('skkp_position').textContent = displayPos;
            document.getElementById('skkp_project').textContent = values.project || '...';
            document.getElementById('skkp_body').textContent = 'Surat keterangan kerja untuk ' + displayName + (displayPos && displayPos !== '-' ? ' (' + displayPos + ')' : '') + ' di ' + (values.project || 'PT. Greatwall Drilling Asia Pacific') + '.';
        }

        //  BA - Berita Acara 
        function syncBaRigsWithTujuan() {
            syncRigsWithTujuan('ba_tujuan', 'ba_rigs');
        }

        function getBaSelectedPositions() {
            return getSelectedOptionsText('ba_positions');
        }

        function buildBaIsi() {
            syncBaRigsWithTujuan();

            const values = {
                tujuan: document.getElementById('ba_tujuan').value.trim(),
                positions: getBaSelectedPositions(),
                rigs: document.getElementById('ba_rigs').value.trim(),
                name: document.getElementById('ba_name').value.trim(),
                position: document.getElementById('ba_position').value.trim(),
                rig: document.getElementById('ba_rig').value.trim(),
                contract: document.getElementById('ba_contract').value.trim(),
                startOjt: document.getElementById('ba_start_ojt').value,
                endOjt: document.getElementById('ba_end_ojt').value,
                assessmentDate: document.getElementById('ba_assessment_date').value,
                result: document.getElementById('ba_result').value,
                place: document.getElementById('ba_place').value.trim()
            };

            const displayName = values.name ? values.name : (values.tujuan ? values.tujuan : 'Crew terkait');
            const displayPos = values.position ? values.position : (values.positions ? values.positions : '');
            const displayRig = values.rig ? values.rig : (values.rigs ? values.rigs : '');

            const contractNo = values.contract || 'SPHR00618A';
            const placeText = values.place || 'Tanggul';
            const resultText = values.result || 'Passed';
            const letterDateVal = document.getElementById('ba_letter_date') ? document.getElementById('ba_letter_date').value : '';
            // Signers (DSR, RSM, HSE)
            const signerDsr = document.getElementById('ba_signer') ? document.getElementById('ba_signer').value.trim() : '';
            const signerRsm = document.getElementById('ba_signer_rsm') ? document.getElementById('ba_signer_rsm').value.trim() : '';
            const signerHse = document.getElementById('ba_signer_hse') ? document.getElementById('ba_signer_hse').value.trim() : '';

            const content = [
                'Kepada: ' + (values.tujuan || 'Crew terkait'),
                'Posisi: ' + (values.positions || values.position || ''),
                'Rigs: ' + (values.rigs || values.rig || ''),
                'Nama: ' + displayName,
                'Jabatan: ' + displayPos,
                'Rig: ' + displayRig,
                'No Kontrak: ' + contractNo,
                'Start OJT: ' + (formatDateId(values.startOjt) || ''),
                'End OJT: ' + (formatDateId(values.endOjt) || ''),
                'Tanggal Assessment: ' + (formatDateId(values.assessmentDate) || ''),
                'Hasil: ' + resultText,
                'Tempat: ' + placeText,
                'Tanggal Surat: ' + (formatDateId(letterDateVal || values.assessmentDate) || formatDateId('<?= date('Y-m-d') ?>')),
                'Signer DSR: ' + signerDsr,
                'Signer RSM: ' + signerRsm,
                'Signer HSE: ' + signerHse
            ].join('\n');

            document.getElementById('isi_surat').value = content;
            document.getElementById('isi_surat_generated').value = content;
            updateBaPreview(values);
        }

        function updateBaPreview(values = null) {
            values = values || {
                tujuan: document.getElementById('ba_tujuan') ? document.getElementById('ba_tujuan').value.trim() : '',
                name: document.getElementById('ba_name').value.trim(),
                rig: document.getElementById('ba_rig').value.trim(),
                rigs: document.getElementById('ba_rigs') ? document.getElementById('ba_rigs').value.trim() : '',
                result: document.getElementById('ba_result').value
            };

            const displayName = values.name ? values.name : (values.tujuan ? values.tujuan : 'Crew terkait');
            const displayRig = values.rig ? values.rig : (values.rigs ? values.rigs : '-');

            document.getElementById('bap_name').textContent = displayName;
            document.getElementById('bap_rig').textContent = displayRig;
            document.getElementById('bap_result').textContent = values.result || 'Passed';
        }

        //  SJ - Surat Perjanjian 
        function buildSjIsi() {
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

        //  MM - Memo 
        function toggleMemoModel() {
            const model = document.getElementById('mm_model')?.value || 'Memo Internal';
            const isMemorandum = model === 'Memorandum';
            document.querySelectorAll('.memo-memorandum-only').forEach(el => {
                el.style.display = isMemorandum ? '' : 'none';
            });
            const title = document.getElementById('mmp_title');
            if (title) title.textContent = isMemorandum ? 'MEMORANDUM' : 'MEMO INTERNAL';
            const perihal = document.getElementById('mm_perihal');
            if (perihal && !perihal.dataset.touched) {
                perihal.value = isMemorandum ? 'Tidak Perpanjang Kontrak' : 'Informasi Kebijakan Kontrak dan Hak Karyawan';
            }
            if (isMemorandum) applyMemorandumDefaults();
        }

        function applyMemorandumDefaults() {
            const intro = document.getElementById('mm_intro');
            if (!intro || intro.dataset.memorandumLoaded) return;

            document.getElementById('mm_perihal').value = 'Pemberitahuan Evaluasi Kinerja dan Tidak Dilakukannya Perpanjangan Kontrak';
            document.getElementById('mm_tujuan').value = 'Seluruh Leadership dan Tim Operasional';
            document.getElementById('mm_rigs').value = 'RIG GW-339';
            document.getElementById('mm_lampiran').value = '-';
            document.getElementById('mm_letter_date').value = '2025-12-30';
            intro.value = 'Melalui memorandum ini, kami sampaikan kepada seluruh Leadership dan Tim Operasional RIG GW-339 terkait pelaksanaan evaluasi kinerja crew under Sigma yang dilaksanakan paralel mulai bulan Desember 2025, sebagai bagian dari proses perpanjangan Badge, MCU, dan Kontrak Kerja karyawan.\n\nSebagaimana telah diatur dalam Perjanjian Kerja Waktu Tertentu (PKWT), setiap karyawan wajib mematuhi kebijakan dan ketentuan perusahaan, antara lain sebagai berikut:';
            document.getElementById('mm_points').value = '1. Pasal 1.4\nKaryawan bersedia bekerja lembur atas perintah Pimpinan Perusahaan PT ADK Enam Indonesia dan/atau perwakilan perusahaan di wilayah operasi sesuai prosedur yang berlaku.\n2. Pasal 1.6\nKaryawan menyetujui untuk dilakukan review dan penilaian kinerja oleh Perusahaan dan Pemberi Kerja (PHR) selama masa kontrak berjalan.\n3. Pasal 1.8\nKaryawan wajib mematuhi seluruh peraturan perusahaan dan menerima konsekuensi atas pelanggaran yang dilakukan sesuai ketentuan yang berlaku dan peraturan ketenagakerjaan.\n4. Pasal 5.1\nKedua belah pihak berhak untuk melakukan pemutusan hubungan kerja atau tidak melakukan perpanjangan kontrak dengan pemberitahuan tertulis terlebih dahulu.\n5. Pasal 5.4\nPerjanjian Kerja Waktu Tertentu dapat diakhiri apabila PHR dan Perusahaan menilai karyawan tidak memenuhi standar kinerja yang ditetapkan.';
            document.getElementById('mm_followup').value = 'Berdasarkan hasil evaluasi kinerja selama 12 (dua belas) bulan terakhir, Manajemen PT ADK6 Sigma yang menaungi dan mengelola Crew GW-339 menetapkan beberapa nama crew yang tidak direkomendasikan untuk perpanjangan kontrak kerja periode 2026, sebagai berikut:\n1. Habib Mughni Arasyd - Roustabout\n2. Rangga Saputra - Access Control\n3. Aldy Hudri - Access Control\n4. Syafwan - Floorman\n5. Andre - Roomboy\n\nAdapun hasil evaluasi menunjukkan tidak adanya peningkatan kinerja yang signifikan, khususnya dari aspek:\n- Kedisiplinan kerja, Rekap Kehadiran/Absensi\n- Efektivitas dan tanggung jawab pekerjaan\n- Loyalitas terhadap perusahaan\n- Kepatuhan terhadap peraturan perusahaan\n\nSehubungan dengan hal tersebut, Manajemen mengajukan permohonan persetujuan dan dukungan dari Team Leader di lokasi serta Manajemen GWDC untuk melakukan pergantian crew terhadap nama-nama yang tercantum di atas.\n\nSelanjutnya, PT ADK6-Sigma akan mengakhiri hubungan kerja karyawan yang bersangkutan sesuai dengan masa berlaku PKWT yang telah ditandatangani sebelumnya. Selama masa kontrak masih berjalan, karyawan wajib tetap melaksanakan tugas kerja hingga kontrak berakhir, guna mendukung kelancaran proses administrasi pemutihan CCPM serta pembayaran seluruh hak karyawan sesuai ketentuan yang berlaku.';
            document.getElementById('mm_closing').value = 'Demikian memorandum ini kami sampaikan untuk dapat dipahami dan dilaksanakan sebagaimana mestinya. Atas perhatian dan kerja sama yang baik, kami ucapkan terima kasih.';
            intro.dataset.memorandumLoaded = 'true';
        }

        //  MCU - Surat Pengajuan MCU 
        function filterMcuCrewByGroup() {
            const group = document.getElementById('mcu_filter_crew')?.value || '';
            const wrap = document.getElementById('mcu_crew_options');
            if (!wrap) return;
            const items = wrap.querySelectorAll('.crew-option');
            items.forEach(item => {
                const label = item.textContent || '';
                // Cari data crew dari array crewSearchOptions
                const crewItem = crewSearchOptions.find(c => c.label === label || item.dataset.id == c.id);
                const crewGroup = crewItem?.crew || '';
                if (!group || crewGroup === group) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function buildMcuIsi() {
            const values = {
                nama: document.getElementById('mcu_nama').value.trim(),
                umur: document.getElementById('mcu_umur').value.trim(),
                jenisKelamin: document.getElementById('mcu_jenis_kelamin').value,
                pekerjaan: document.getElementById('mcu_pekerjaan').value.trim(),
                perusahaan: document.getElementById('mcu_perusahaan').value.trim(),
                alamat: document.getElementById('mcu_alamat').value.trim(),
                bloodType: document.getElementById('mcu_blood_type').value.trim(),
                nik: document.getElementById('mcu_nik').value.trim(),
                requirement: document.getElementById('mcu_requirement').value,
                tglSurat: document.getElementById('mcu_tgl_surat').value,
                dateMcu: document.getElementById('mcu_date_mcu').value,
                locationMcu: document.getElementById('mcu_location_mcu').value.trim(),
                locationJob: document.getElementById('mcu_location_job').value.trim(),
                rigLocation: document.getElementById('mcu_rig_location').value.trim(),
                keluhan: document.getElementById('mcu_keluhan').value.trim(),
                pemeriksaan: document.getElementById('mcu_pemeriksaan').value.trim(),
                diagnosa: document.getElementById('mcu_diagnosa').value.trim(),
                pengobatan: document.getElementById('mcu_pengobatan').value.trim(),
                signer: document.getElementById('mcu_signer').value.trim(),
                signerTitle: document.getElementById('mcu_signer_title').value.trim()
            };

            const content = [
                'Nama: ' + (values.nama || '___________________'),
                'Umur: ' + (values.umur || '______ Tahun'),
                'Jenis Kelamin: ' + values.jenisKelamin,
                'Pekerjaan: ' + (values.pekerjaan || '___________________'),
                'Perusahaan: ' + (values.perusahaan || 'PT SIGMA / Greatwall Drilling Asia Pasific'),
                'Alamat: ' + (values.alamat || '___________________'),
                'Blood Type: ' + (values.bloodType || '-'),
                'NIK: ' + (values.nik || '___________________'),
                'MCU Requirement: ' + values.requirement,
                'Date MCU: ' + (formatDateId(values.dateMcu) || formatDateId(values.tglSurat)),
                'Location MCU: ' + (values.locationMcu || 'RS Mutia Sari Duri'),
                'Location Job: ' + (values.locationJob || 'Field Worker / Pekerja Rig'),
                'Rig Location: ' + (values.rigLocation || 'Drilling GW _____'),
                'Keluhan: ' + (values.keluhan || '-'),
                'Pemeriksaan: ' + (values.pemeriksaan || '-'),
                'Diagnosa: ' + (values.diagnosa || '-'),
                'Pengobatan: ' + (values.pengobatan || '-'),
                'Signer: ' + (values.signer || 'Rifqi Haidi'),
                'Signer Title: ' + (values.signerTitle || 'HSE Coordinator PT GDAP')
            ].join('\n');

            document.getElementById('isi_surat').value = content;
            document.getElementById('isi_surat_generated').value = content;
            updateMcuPreview(values);
        }

        function updateMcuPreview(values = null) {
            values = values || {
                nama: document.getElementById('mcu_nama').value.trim(),
                pekerjaan: document.getElementById('mcu_pekerjaan').value.trim(),
                rigLocation: document.getElementById('mcu_rig_location').value.trim(),
                requirement: document.getElementById('mcu_requirement').value
            };

            document.getElementById('mcup_nama').textContent = values.nama || '...';
            document.getElementById('mcup_pekerjaan').textContent = values.pekerjaan || '...';
            document.getElementById('mcup_rig').textContent = values.rigLocation || '...';
            document.getElementById('mcup_type').textContent = values.requirement === 'MCU PHR (Pre-Employee)' ? 'Pre-Employee' : 'Annual-Employee';
            document.getElementById('mcup_body').textContent = 'Surat pengantar MCU untuk ' + (values.nama || '...') + ' sebagai ' + (values.pekerjaan || '...') + ' di ' + (values.rigLocation || '...');
        }

        function buildMmIsi() {
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
