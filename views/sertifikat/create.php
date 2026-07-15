<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-certificate"></i> Tambah Sertifikat</h4>
    <a href="<?= BASE_URL ?>/crew/detail/<?= $id_crew ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card-custom">
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/sertifikat/store" enctype="multipart/form-data">
            <input type="hidden" name="id_crew" value="<?= $id_crew ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Jenis Sertifikat</label>
                    <select name="jenis" class="form-select" required>
                        <option value="">Pilih Jenis</option>
                        <option value="IADC">IADC</option>
                        <option value="TKBT 1">TKBT 1</option>
                        <option value="TKBT 2">TKBT 2</option>
                        <option value="OLB">OLB</option>
                        <option value="OMB">OMB</option>
                        <option value="JB">JB</option>
                        <option value="ASME WELDER">ASME WELDER</option>
                        <option value="K3 TEKNISI">K3 TEKNISI</option>
                        <option value="LAINNYA">Lainnya</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Tanggal Expired</label>
                    <input type="date" name="tanggal_expired" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">File (Scan Sertifikat)</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                </div>
            </div>
            <button type="submit" class="btn btn-sigma"><i class="fas fa-save"></i> Simpan Sertifikat</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>