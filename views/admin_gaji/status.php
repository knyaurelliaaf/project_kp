<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="ag-header-title"><i class="fas fa-list-check ag-header-icon me-2"></i> Status Pengiriman Slip Gaji</h4>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/admin_gaji/stopWorker" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin MENGHENTIKAN pengiriman yang sedang berjalan?')">
            <i class="fas fa-hand-paper me-1"></i> Hentikan Pengiriman
        </a>
        <a href="<?= BASE_URL ?>/admin_gaji/retryAllGagal<?= !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : '' ?>" class="btn btn-sm btn-warning text-dark fw-semibold" onclick="return confirm('Kirim ulang seluruh slip gaji yang berstatus GAGAL / PENDING?')">
            <i class="fas fa-redo me-1"></i> Kirim Ulang Semua Gagal
        </a>
        <button onclick="location.reload()" class="btn btn-sm btn-ag-primary"><i class="fas fa-sync-alt me-1"></i> Refresh</button>
    </div>
</div>

<?php $flash = Helper::getFlash(); if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] == 'error' ? 'danger' : 'success' ?> alert-dismissible fade show mb-3" role="alert">
        <?= $flash['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- QR / LOADING / READY — ditangani sepenuhnya oleh AJAX polling -->
<div id="wa-panel"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
const BASE = '<?= BASE_URL ?>';
let pollTimer = null;
let pollCount = 0;
const MAX_POLL = 120; // max ~4 menit total polling

let currentPanelState = '';
let currentQrRaw = '';
let currentPairingCode = '';

function renderPanel(data) {
    const panel = document.getElementById('wa-panel');

    if (data.error) {
        clearTimeout(pollTimer);
        const errKey = 'error_' + data.error;
        if (currentPanelState !== errKey) {
            currentPanelState = errKey;
            currentQrRaw = '';
            panel.innerHTML = `
            <div class="alert alert-danger d-flex justify-content-between align-items-center mb-4" role="alert">
                <div><strong>Koneksi WhatsApp gagal.</strong><br><small>${escapeHtml(data.error)}</small></div>
                <a href="${BASE}/admin_gaji/resetWaSession" class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Reset sesi WhatsApp dan buat QR Code baru?')">Coba tautkan ulang</a>
            </div>`;
        }
        return;
    }

    if (data.status === 'CONNECTED' || data.status === 'AUTHENTICATED' || data.authenticated) {
        clearTimeout(pollTimer);
        if (currentPanelState !== 'connected') {
            currentPanelState = 'connected';
            currentQrRaw = '';
            panel.innerHTML = `
            <div class="alert alert-info d-flex justify-content-between align-items-center mb-4" role="alert"
                 style="background:#C0DDDA!important;border:2px solid #775537!important;color:#775537!important;border-radius:14px;">
                <div><i class="fab fa-whatsapp fs-5 me-2"></i> <strong>Sesi WhatsApp Aktif / Siap.</strong>
                WhatsApp sudah terhubung ke sistem.</div>
                <a href="${BASE}/admin_gaji/resetWaSession" class="btn btn-sm btn-ag-primary"
                   onclick="return confirm('Reset sesi WhatsApp dan buat QR Code baru?')">
                    <i class="fas fa-qrcode me-1"></i> Scan QR Baru / Ganti WA
                </a>
            </div>`;
        }
        return;
    }

    if (data.pairingCode) {
        if (currentPanelState !== 'pairing' || currentPairingCode !== data.pairingCode) {
            currentPanelState = 'pairing';
            currentPairingCode = data.pairingCode;
            currentQrRaw = '';
            panel.innerHTML = `
            <div class="card-custom mb-4 p-4 text-center" style="background:#FBE29D!important;border:3px solid #775537!important;border-radius:18px;">
                <h5 class="fw-bold mb-2" style="color:#775537;"><i class="fas fa-key me-2"></i> KODE PAIRING WHATSAPP (8-DIGIT)</h5>
                <p class="text-dark mb-3 mx-auto" style="max-width:650px;">
                    Buka WhatsApp HP <i class="fas fa-chevron-right mx-1" style="font-size:12px;"></i>
                    <strong>Perangkat Tertaut</strong> <i class="fas fa-chevron-right mx-1" style="font-size:12px;"></i>
                    <strong>Tautkan Perangkat</strong> <i class="fas fa-chevron-right mx-1" style="font-size:12px;"></i>
                    klik <strong>"Tautkan dengan nomor telepon saja"</strong>, masukkan kode:
                </p>
                <div class="d-inline-block px-5 py-3 bg-white rounded-3 shadow mb-3" style="border:2.5px solid #775537;">
                    <span class="display-4 fw-bold" style="color:#775537;letter-spacing:8px;font-family:monospace;">${data.pairingCode}</span>
                </div>
                <div><span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="fas fa-spinner fa-spin me-1"></i> Masukkan kode ini di HP — halaman akan otomatis update saat terhubung.</span></div>
            </div>`;
        }
        schedulePoll(3000);
        return;
    }

    if (data.qrRaw) {
        if (currentPanelState !== 'qr') {
            currentPanelState = 'qr';
            currentQrRaw = data.qrRaw;
            panel.innerHTML = `
            <div class="card-custom mb-4 p-4 text-center" style="background:#FBE29D!important;border:2.5px solid #775537!important;border-radius:18px;">
                <h5 class="fw-bold mb-2" style="color:#775537;"><i class="fab fa-whatsapp me-2"></i> Pindai / Scan QR Code WhatsApp</h5>
                <p class="text-muted mb-2 mx-auto" style="max-width:650px;">
                    Buka WhatsApp HP <i class="fas fa-chevron-right mx-1 text-dark" style="font-size:12px;"></i>
                    <strong>Perangkat Tertaut (Linked Devices)</strong> <i class="fas fa-chevron-right mx-1 text-dark" style="font-size:12px;"></i>
                    <strong>Tautkan Perangkat</strong> dan arahkan kamera HP ke QR Code:
                </p>
                <div id="qr-canvas-wrap" class="d-inline-block p-3 bg-white rounded-3 shadow-sm mb-2" style="border:2px solid #775537;"></div>
                <div class="mb-2">
                    <button type="button" onclick="manualRefreshQr()" class="btn btn-sm btn-outline-dark me-2">
                        <i class="fas fa-sync-alt me-1"></i> Refresh QR Code
                    </button>
                    <span class="badge bg-secondary text-white px-2 py-1"><i class="fas fa-info-circle me-1"></i> Scan QR dengan kamera WhatsApp HP</span>
                </div>
                <hr class="my-3 mx-auto" style="max-width:500px;border-color:#775537;">
                <h6 class="fw-bold mb-2" style="color:#775537;"><i class="fas fa-mobile-alt me-1"></i> Tidak bisa scan? Gunakan Kode 8-Digit:</h6>
                <form id="pairing-form" onsubmit="submitPairingCode(event)" class="d-flex justify-content-center align-items-center gap-2 mx-auto" style="max-width:480px;">
                    <input type="text" id="pairing-phone" name="nomor_wa" class="form-control" placeholder="Nomor WA (Contoh: 628123456789)" required>
                    <button type="submit" class="btn btn-ag-primary text-nowrap"><i class="fas fa-key me-1"></i> Dapat Kode</button>
                </form>
                <small class="text-muted">Format: 62 + nomor HP (contoh: 6281234567890)</small>
            </div>`;
            renderQrCanvas(data.qrRaw);
        } else if (currentQrRaw !== data.qrRaw) {
            currentQrRaw = data.qrRaw;
            renderQrCanvas(data.qrRaw);
        }

        schedulePoll(4000);
        return;
    }

    if (data.status === 'INITIALIZING' || data.loading) {
        if (currentPanelState !== 'loading') {
            currentPanelState = 'loading';
            currentQrRaw = '';
            panel.innerHTML = `
            <div class="card-custom mb-4 p-4 text-center" style="background:#FBE29D!important;border:2.5px solid #775537!important;border-radius:18px;">
                <h5 class="fw-bold mb-2" style="color:#775537;"><i class="fas fa-spinner fa-spin me-2"></i> Memuat WhatsApp & Membuat QR Code...</h5>
                <p class="text-dark mb-0">Sedang memproses QR Code baru (memerlukan <strong>10–30 detik</strong>). QR Code akan tampil otomatis di sini...</p>
                <div class="py-3"><div class="spinner-border" style="color:#775537;width:2.5rem;height:2.5rem;" role="status"></div></div>
            </div>`;
        }
        schedulePoll(2000);
        return;
    }

    // Default disconnected state -> Show prompt to generate QR / Pair
    if (currentPanelState !== 'disconnected_prompt') {
        currentPanelState = 'disconnected_prompt';
        panel.innerHTML = `
        <div class="card-custom mb-4 p-4 text-center" style="background:#FBE29D!important;border:3px solid #775537!important;border-radius:18px;">
            <h5 class="fw-bold mb-2" style="color:#775537;"><i class="fab fa-whatsapp me-2"></i> KONEKSI WHATSAPP BELUM TERHUBUNG</h5>
            <p class="text-dark mb-3 mx-auto" style="max-width:650px;">
                Status WhatsApp saat ini belum terhubung. Klik tombol di bawah ini untuk memuat QR Code atau Kode Pairing 8-digit.
            </p>
            <a href="${BASE}/admin_gaji/resetWaSession" class="btn btn-ag-primary btn-lg fw-bold px-4" onclick="return confirm('Buat QR Code baru untuk menautkan WhatsApp HP Anda?')">
                <i class="fas fa-qrcode me-2"></i> Klik di Sini untuk Scan QR Code / Tautkan WA
            </a>
        </div>`;
    }
    schedulePoll(3000);
}

function renderQrCanvas(qrString) {
    const wrap = document.getElementById('qr-canvas-wrap');
    if (!wrap || !qrString) return;
    wrap.innerHTML = '';
    new QRCode(wrap, {
        text: qrString,
        width: 220,
        height: 220,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
    });
}

function escapeHtml(value) {
    const el = document.createElement('div');
    el.textContent = value || '';
    return el.innerHTML;
}

function manualRefreshQr() {
    pollCount = 0;
    doPoll();
}

function submitPairingCode(e) {
    e.preventDefault();
    const phoneRaw = document.getElementById('pairing-phone').value.trim();
    const phone = phoneRaw.replace(/[^0-9]/g, '');
    if (phone.length < 10) {
        alert('Format nomor tidak valid! Gunakan format: 628xxxxxxxxx\n(Contoh: 6281234567890)');
        return;
    }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `${BASE}/admin_gaji/requestPairingCode`;
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'nomor_wa';
    input.value = phone;
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

function schedulePoll(ms) {
    if (pollCount >= MAX_POLL) return;
    clearTimeout(pollTimer);
    pollTimer = setTimeout(doPoll, ms);
}

function doPoll() {
    pollCount++;
    fetch(`${BASE}/admin_gaji/getQrStatus`)
        .then(r => r.json())
        .then(data => renderPanel(data))
        .catch(() => schedulePoll(3000));
}

// Run once on page load to check current state
doPoll();
</script>

<!-- KARTU SUMMARY & HISTORY AKSES BULANAN -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card-custom p-3 text-center h-100" style="background:#C0DDDA!important;border:2px solid #775537!important;border-radius:14px;">
            <small class="text-dark fw-bold text-uppercase d-block mb-1"><i class="fas fa-archive me-1"></i> Total Slip Gaji</small>
            <h3 class="fw-bold mb-0" style="color:#775537;"><?= $totalCount ?? 0 ?> <small class="fs-6">File</small></h3>
            <small class="text-muted"><?= !empty($selectedPeriode) ? 'Periode ' . htmlspecialchars($selectedPeriode) : 'Semua Periode' ?></small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 text-center h-100" style="background:#D1E7DD!important;border:2px solid #0F5132!important;border-radius:14px;">
            <small class="text-success fw-bold text-uppercase d-block mb-1"><i class="fas fa-check-circle me-1"></i> Terkirim Sukses</small>
            <h3 class="fw-bold mb-0 text-success"><?= $totalTerkirim ?? 0 ?></h3>
            <small class="text-success font-monospace">Pesan WA Terkirim</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 text-center h-100" style="background:#FFF3CD!important;border:2px solid #664D03!important;border-radius:14px;">
            <small class="text-warning text-dark fw-bold text-uppercase d-block mb-1"><i class="fas fa-hourglass-half me-1"></i> Pending Antrean</small>
            <h3 class="fw-bold mb-0 text-dark"><?= $totalPending ?? 0 ?></h3>
            <small class="text-muted">Menunggu Antrean</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 text-center h-100" style="background:#F8D7DA!important;border:2px solid #842029!important;border-radius:14px;">
            <small class="text-danger fw-bold text-uppercase d-block mb-1"><i class="fas fa-exclamation-triangle me-1"></i> Gagal Kirim</small>
            <h3 class="fw-bold mb-0 text-danger"><?= $totalGagal ?? 0 ?></h3>
            <small class="text-danger">Nomor Tidak Valid / Error</small>
        </div>
    </div>
</div>

<!-- FILTER BAR & LOG TOOLS -->
<div class="card-custom mb-4 p-3" style="background: #C0DDDA !important; border: 2px solid #775537 !important;">
    <form method="GET" action="<?= BASE_URL ?>/admin_gaji/status" class="row g-2 align-items-center">
        <div class="col-md-3">
            <label class="form-label fw-bold small text-dark mb-1"><i class="fas fa-calendar-alt me-1"></i> Filter Periode</label>
            <select name="periode" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Periode Gaji</option>
                <?php if (!empty($daftarPeriode)): ?>
                    <?php foreach ($daftarPeriode as $p): ?>
                        <option value="<?= htmlspecialchars($p) ?>" <?= $selectedPeriode == $p ? 'selected' : '' ?>>
                            Periode <?= htmlspecialchars($p) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold small text-dark mb-1"><i class="fas fa-info-circle me-1"></i> Filter Status</label>
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Status Pengiriman</option>
                <option value="terkirim" <?= $selectedStatus == 'terkirim' ? 'selected' : '' ?>>🟢 Terkirim</option>
                <option value="pending" <?= $selectedStatus == 'pending' ? 'selected' : '' ?>>🟡 Pending (Antrean)</option>
                <option value="gagal" <?= $selectedStatus == 'gagal' ? 'selected' : '' ?>>🔴 Gagal</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold small text-dark mb-1"><i class="fas fa-search me-1"></i> Cari Nama / File / No. WA</label>
            <div class="input-group input-group-sm">
                <input type="text" name="q" class="form-control" placeholder="Cari nama karyawan..." value="<?= htmlspecialchars($search ?? '') ?>">
                <button type="submit" class="btn btn-ag-primary"><i class="fas fa-search"></i> Filter</button>
            </div>
        </div>
        <div class="col-md-2 text-end pt-3">
            <a href="<?= BASE_URL ?>/admin_gaji/clearLogs" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Apakah Anda yakin ingin membersihkan semua log pengiriman? (Data tes akan dihapus)')">
                <i class="fas fa-trash-alt me-1"></i> Reset Log Tes
            </a>
        </div>
    </form>
</div>

<div class="table-card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Penerima</th>
                        <th>No. WA</th>
                        <th>Rig / Crew</th>
                        <th>Posisi</th>
                        <th>Periode</th>
                        <th>File Slip</th>
                        <th>Status</th>
                        <th>Pesan Error</th>
                        <th>Waktu Dikirim</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="status-tbody">
                    <?php if ($log && $log->num_rows > 0): $no = ($offset ?? 0) + 1; ?>
                        <?php while ($l = $log->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong style="color: #775537;"><?= htmlspecialchars($l['nama']) ?></strong></td>
                            <td><?= htmlspecialchars($l['nomor_wa']) ?></td>
                            <td>
                                <?php if (!empty($l['kode_rig']) || !empty($l['crew'])): ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars(($l['kode_rig'] ?? '') . ($l['crew'] ? ' / ' . $l['crew'] : '')) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($l['posisi'] ?? '-') ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($l['periode']) ?></span></td>
                            <td>
                                <code><?= htmlspecialchars($l['nama_file']) ?></code>
                                <a href="<?= BASE_URL ?>/admin_gaji/previewPdf?periode=<?= urlencode($l['periode']) ?>&file=<?= urlencode($l['nama_file']) ?>" target="_blank" class="badge bg-light text-primary border border-primary ms-1 text-decoration-none" title="Buka / Lihat File PDF">
                                    <i class="fas fa-file-pdf text-danger me-1"></i> Lihat PDF
                                </a>
                            </td>
                            <td>
                                <?php
                                if ($l['status'] == 'terkirim') echo '<span class="badge bg-success">Terkirim</span>';
                                elseif ($l['status'] == 'pending') echo '<span class="badge bg-warning">Pending</span>';
                                else echo '<span class="badge bg-danger">Gagal</span>';
                                ?>
                            </td>
                            <td class="small text-muted"><?= htmlspecialchars($l['pesan_error'] ?? '-') ?></td>
                            <td class="small text-muted"><?= htmlspecialchars($l['dikirim_pada'] ?? '-') ?></td>
                            <td class="text-center">
                                <?php if ($l['status'] == 'terkirim'): ?>
                                    <span class="badge bg-light text-success border border-success" title="Sudah terkirim — dikunci agar tidak dikirim ganda"><i class="fas fa-check-circle me-1"></i> Aman</span>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>/admin_gaji/retryKirim/<?= $l['id_log'] ?>" class="btn btn-sm btn-outline-warning text-dark fw-bold" title="Kirim Ulang">
                                        <i class="fas fa-redo me-1"></i> Kirim Ulang
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="11" class="text-center py-4 text-muted">Belum ada data pengiriman</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <!-- Pagination -->
    <div class="pagination-container" id="status-pagination">
        <?php if (isset($totalPages) && $totalPages > 1): ?>
            <a href="javascript:void(0)" onclick="loadStatusPage(<?= max(1, ($page ?? 1) - 1) ?>)" class="btn btn-sm btn-prev <?= ($page ?? 1) <= 1 ? 'disabled' : '' ?>">&laquo;</a>
            <span class="pagination-numbers">
                <?= Helper::renderPaginationNumbers($page ?? 1, $totalPages ?? 1, 'loadStatusPage') ?>
            </span>
            <a href="javascript:void(0)" onclick="loadStatusPage(<?= min($totalPages, ($page ?? 1) + 1) ?>)" class="btn btn-sm btn-next <?= ($page ?? 1) >= $totalPages ? 'disabled' : '' ?>">&raquo;</a>
        <?php endif; ?>
    </div>
</div>

<script>
let debounceStatusTimer;

function loadStatusPage(page = 1) {
    const q = document.querySelector('input[name="q"]')?.value || '';
    const status = document.querySelector('select[name="status"]')?.value || '';
    const periode = document.querySelector('select[name="periode"]')?.value || '';
    const params = new URLSearchParams({ page, q, status, periode });

    fetch('<?= BASE_URL ?>/admin_gaji/statusTable?' + params.toString())
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('status-tbody');
            const pag = document.getElementById('status-pagination');
            if (tbody) tbody.innerHTML = data.html;
            if (pag) pag.innerHTML = data.pagination;

            const newUrl = window.location.pathname + '?' + params.toString();
            window.history.replaceState({ path: newUrl }, '', newUrl);
        })
        .catch(err => console.error('Error live search status:', err));
}

document.addEventListener('DOMContentLoaded', function() {
    const qInput = document.querySelector('input[name="q"]');
    const statusSelect = document.querySelector('select[name="status"]');
    const periodeSelect = document.querySelector('select[name="periode"]');

    if (qInput) {
        qInput.addEventListener('input', function() {
            clearTimeout(debounceStatusTimer);
            debounceStatusTimer = setTimeout(() => {
                loadStatusPage(1);
            }, 250);
        });
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            loadStatusPage(1);
        });
    }

    if (periodeSelect) {
        periodeSelect.addEventListener('change', function() {
            loadStatusPage(1);
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
