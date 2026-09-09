<?php
require_once __DIR__ . '/../helpers/AdminGajiWaHelper.php';

class AdminGajiController extends Controller {
    
    public function __construct() {
        // Skip session check for updateStatus API callback from node script
        $url = $_GET['url'] ?? '';
        if (strpos($url, 'updateStatus') !== false) {
            return;
        }

        $this->requireLogin();
        $role = $_SESSION['user']['role'] ?? '';
        if (!in_array($role, ['admin_gaji', 'payroll_wa'])) {
            die("Akses ditolak. Halaman ini khusus untuk Role Payroll.");
        }
    }
    
    // DASHBOARD
    public function index() {
        $penerimaModel = $this->model('PenerimaSlipModel');
        $kirimModel = $this->model('KirimLogModel');
        
        $data = [
            'title' => 'Dashboard Kirim Slip',
            'currentPage' => 'gaji_dashboard',
            'total_penerima' => $penerimaModel->countAktif(),
            'terkirim' => $kirimModel->countByStatus('terkirim'),
            'pending' => $kirimModel->countByStatus('pending'),
            'gagal' => $kirimModel->countByStatus('gagal')
        ];
        $this->view('admin_gaji/index', $data);
    }
    
    // KELOLA PENERIMA
    public function penerima() {
        $penerimaModel = $this->model('PenerimaSlipModel');
        
        $search = trim($_GET['q'] ?? $_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $crew = trim($_GET['crew'] ?? '');
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 10;

        $totalPenerima = $penerimaModel->countFiltered($search, $status, $crew);
        $totalPages = max(1, ceil($totalPenerima / $perPage));
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        $penerima = $penerimaModel->getPaginated($search, $status, $perPage, $offset, 'nama', 'ASC', $crew);
        $allowedRigKodes = ['GW-363', 'GW-364', 'GW-365', 'GW-366', 'GW-367', 'GW-Welder'];
        $resRigs = $this->model('RigModel')->all('kode_rig', 'ASC');
        $allRigs = ($resRigs && $resRigs instanceof \mysqli_result) ? $resRigs->fetch_all(MYSQLI_ASSOC) : (is_array($resRigs) ? $resRigs : []);
        $rigList = array_values(array_filter($allRigs, function($r) use ($allowedRigKodes) {
            return in_array($r['kode_rig'], $allowedRigKodes);
        }));

        $resPos = $this->model('MasterPosisiModel')->all('nama_posisi', 'ASC');
        $posisiList = ($resPos && $resPos instanceof \mysqli_result) ? $resPos->fetch_all(MYSQLI_ASSOC) : (is_array($resPos) ? $resPos : []);
        
        $data = [
            'title' => 'Kelola Penerima',
            'currentPage' => 'gaji_penerima',
            'penerima' => $penerima,
            'rigList' => $rigList,
            'posisiList' => $posisiList,
            'search' => $search,
            'status' => $status,
            'crew' => $crew,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'totalPenerima' => $totalPenerima,
            'offset' => $offset
        ];
        $this->view('admin_gaji/penerima', $data);
    }

    private function redirectWithFilters($path = 'admin_gaji/penerima') {
        $search = $_GET['q'] ?? $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $crew = $_GET['crew'] ?? '';
        $page = $_GET['page'] ?? 1;

        $params = [];
        if (!empty($search)) $params[] = 'q=' . urlencode($search);
        if (!empty($status)) $params[] = 'status=' . urlencode($status);
        if (!empty($crew)) $params[] = 'crew=' . urlencode($crew);
        if (!empty($page) && (int)$page > 1) $params[] = 'page=' . (int)$page;

        $cleanPath = ltrim($path, '/');
        if (!empty($params)) {
            $cleanPath .= (strpos($cleanPath, '?') !== false ? '&' : '?') . implode('&', $params);
        }
        $this->redirect($cleanPath);
    }

    // TAMBAH PENERIMA
    public function storePenerima() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirectWithFilters('admin_gaji/penerima');
        
        $id_rig = !empty($_POST['id_rig']) ? intval($_POST['id_rig']) : null;
        $kode_rig = null;
        if ($id_rig) {
            $r = $this->model('RigModel')->find($id_rig);
            $kode_rig = $r['kode_rig'] ?? null;
        }

        $this->model('PenerimaSlipModel')->insert([
            'nama' => $_POST['nama'] ?? '',
            'nomor_wa' => $_POST['nomor_wa'] ?? '',
            'id_rig' => $id_rig,
            'kode_rig' => $kode_rig,
            'crew' => $_POST['crew'] ?? null,
            'posisi' => $_POST['posisi'] ?? null
        ]);

        $this->model('PenerimaSlipModel')->syncWithCrewMaster();
        
        Helper::setFlash('success', 'Penerima berhasil ditambahkan!');
        $this->redirectWithFilters('admin_gaji/penerima');
    }
    
    // EDIT PENERIMA
    public function updatePenerima($id) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirectWithFilters('admin_gaji/penerima');
        
        $id_rig = !empty($_POST['id_rig']) ? intval($_POST['id_rig']) : null;
        $kode_rig = null;
        if ($id_rig) {
            $r = $this->model('RigModel')->find($id_rig);
            $kode_rig = $r['kode_rig'] ?? null;
        }

        $this->model('PenerimaSlipModel')->update($id, [
            'nama' => $_POST['nama'] ?? '',
            'nomor_wa' => $_POST['nomor_wa'] ?? '',
            'id_rig' => $id_rig,
            'kode_rig' => $kode_rig,
            'crew' => $_POST['crew'] ?? null,
            'posisi' => $_POST['posisi'] ?? null
        ]);
        
        Helper::setFlash('success', 'Penerima berhasil diupdate!');
        $this->redirectWithFilters('admin_gaji/penerima');
    }

    // MIGRASI / IMPORT DATA PENERIMA DARI FILE ATAU DATABASE CREW
    public function importPenerima() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('admin_gaji/penerima');
        
        $penerimaModel = $this->model('PenerimaSlipModel');
        $source = $_POST['source'] ?? 'file';

        // Opsi 1: Import langsung dari Database Master Crew
        if ($source === 'crew_db') {
            $sql = "INSERT INTO penerima_slip (nama, id_rig, kode_rig, crew, posisi, status_aktif)
                    SELECT c.nama, c.id_rig, r.kode_rig, c.crew, c.posisi, 'aktif'
                    FROM crew c
                    LEFT JOIN rig r ON c.id_rig = r.id_rig
                    WHERE c.status_aktif = 'aktif'
                      AND NOT EXISTS (
                          SELECT 1 FROM penerima_slip p WHERE LOWER(TRIM(p.nama)) = LOWER(TRIM(c.nama))
                      )";
            $penerimaModel->query($sql);
            $penerimaModel->syncWithCrewMaster();
            
            Helper::setFlash('success', '🎉 Migrasi berhasil! Seluruh data karyawan dari Master Crew telah diimpor & dicocokkan.');
            $this->redirect('admin_gaji/penerima');
            return;
        }

        // Opsi 2: Upload File (Excel, CSV, TXT, ZIP)
        if (!empty($_FILES['import_file']['name'])) {
            $tmpPath = $_FILES['import_file']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION));
            $namesFound = [];

            if ($ext === 'xlsx' || $ext === 'xls') {
                $namesFound = $this->parseExcelForPenerima($tmpPath);
            } elseif ($ext === 'zip') {
                $zip = new ZipArchive();
                if ($zip->open($tmpPath) === TRUE) {
                    $extractDir = sys_get_temp_dir() . '/import_penerima_' . time() . '/';
                    if (!is_dir($extractDir)) @mkdir($extractDir, 0777, true);
                    $zip->extractTo($extractDir);
                    $zip->close();

                    $iterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($extractDir, RecursiveDirectoryIterator::SKIP_DOTS)
                    );

                    foreach ($iterator as $fileinfo) {
                        if ($fileinfo->isFile()) {
                            $fileExt = strtolower($fileinfo->getExtension());
                            $filePath = $fileinfo->getPathname();

                            if ($fileExt === 'xlsx' || $fileExt === 'xls') {
                                $parsed = $this->parseExcelForPenerima($filePath);
                                $namesFound = array_merge($namesFound, $parsed);
                            } elseif ($fileExt === 'pdf') {
                                $baseName = pathinfo($fileinfo->getFilename(), PATHINFO_FILENAME);
                                $cleanName = trim(explode('-', $baseName)[0]);
                                if (!empty($cleanName)) {
                                    $namesFound[] = ['nama' => $cleanName, 'nomor_wa' => ''];
                                }
                            } elseif ($fileExt === 'csv' || $fileExt === 'txt') {
                                $handle = fopen($filePath, 'r');
                                if ($handle) {
                                    while (($line = fgets($handle)) !== false) {
                                        $line = trim($line);
                                        if (empty($line)) continue;
                                        $parts = preg_split('/[,;\t]/', $line);
                                        $name = trim($parts[0] ?? '');
                                        if (!empty($name) && strtolower($name) !== 'nama' && strtolower($name) !== 'name') {
                                            $wa = trim($parts[1] ?? '');
                                            $namesFound[] = ['nama' => $name, 'nomor_wa' => $wa];
                                        }
                                    }
                                    fclose($handle);
                                }
                            }
                        }
                    }
                }
            } elseif ($ext === 'csv' || $ext === 'txt') {
                $handle = fopen($tmpPath, 'r');
                if ($handle) {
                    while (($line = fgets($handle)) !== false) {
                        $line = trim($line);
                        if (empty($line)) continue;
                        $parts = preg_split('/[,;\t]/', $line);
                        $name = trim($parts[0] ?? '');
                        if (!empty($name) && strtolower($name) !== 'nama' && strtolower($name) !== 'name') {
                            $wa = trim($parts[1] ?? '');
                            $namesFound[] = ['nama' => $name, 'nomor_wa' => $wa];
                        }
                    }
                    fclose($handle);
                }
            }

            $importedCount = 0;
            $updatedCount = 0;
            $rigModel = $this->model('RigModel');

            foreach ($namesFound as $item) {
                $nama = trim($item['nama'] ?? '');
                $wa = trim($item['nomor_wa'] ?? $item['wa'] ?? '');
                $kode_rig = !empty($item['kode_rig']) ? trim($item['kode_rig']) : null;
                $crew = !empty($item['crew']) ? trim($item['crew']) : null;
                $posisi = !empty($item['posisi']) ? trim($item['posisi']) : null;

                if (empty($nama)) continue;

                $id_rig = null;
                if (!empty($kode_rig)) {
                    $r = $rigModel->findByKode($kode_rig);
                    if ($r) $id_rig = $r['id_rig'];
                }

                $existing = $penerimaModel->getByNamaOrNik($nama);
                if (!$existing) {
                    $penerimaModel->insert([
                        'nama' => $nama,
                        'nomor_wa' => $wa,
                        'id_rig' => $id_rig,
                        'kode_rig' => $kode_rig,
                        'crew' => $crew,
                        'posisi' => $posisi,
                        'status_aktif' => 'aktif'
                    ]);
                    $importedCount++;
                } else {
                    $updateData = [];
                    if (!empty($wa) && empty($existing['nomor_wa'])) {
                        $updateData['nomor_wa'] = $wa;
                    }
                    if (!empty($kode_rig) && empty($existing['kode_rig'])) {
                        $updateData['kode_rig'] = $kode_rig;
                        if (empty($existing['id_rig']) && $id_rig) {
                            $updateData['id_rig'] = $id_rig;
                        }
                    }
                    if (!empty($crew) && empty($existing['crew'])) {
                        $updateData['crew'] = $crew;
                    }
                    if (!empty($posisi) && empty($existing['posisi'])) {
                        $updateData['posisi'] = $posisi;
                    }

                    if (!empty($updateData)) {
                        $penerimaModel->update($existing['id_penerima'], $updateData);
                        $updatedCount++;
                    }
                }
            }

            // Sync all with crew master data
            $penerimaModel->syncWithCrewMaster();

            Helper::setFlash('success', "🎉 Migrasi berhasil! {$importedCount} data karyawan baru dari file diimpor dan {$updatedCount} data berhasil diperbarui & dicocokkan.");
        } else {
            Helper::setFlash('error', 'Silakan pilih berkas file yang akan diunggah untuk migrasi!');
        }

        $this->redirect('admin_gaji/penerima');
    }

    // HELPER: PARSE EXCEL FILE (.xlsx / .xls) DENGAN BANYAK SHEET (RIG / CREW)
    private function parseExcelForPenerima($filePath) {
        if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
            if (file_exists($autoloadPath)) {
                require_once $autoloadPath;
            }
        }
        
        $results = [];
        if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            return $results;
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetTitle = trim($sheet->getTitle());
                
                // Cek nama Rig & Crew dari judul Sheet (misal: "GW-336", "GW-338 Crew A", "Rig GW-340")
                $sheetRig = null;
                $sheetCrew = null;
                
                if (preg_match('/(GW-\d+|RIG[-\s]*\d+)/i', $sheetTitle, $mRig)) {
                    $sheetRig = strtoupper(str_replace(' ', '', $mRig[1]));
                    if (strpos($sheetRig, 'RIG') === 0 && strpos($sheetRig, 'GW-') === false) {
                        $sheetRig = str_replace('RIG', 'GW-', $sheetRig);
                    }
                }
                if (preg_match('/Crew\s*([A-C])/i', $sheetTitle, $mCrew)) {
                    $sheetCrew = 'Crew ' . strtoupper($mCrew[1]);
                }

                $rows = $sheet->toArray(null, true, true, true);
                if (empty($rows)) continue;

                // Cari baris header kolom
                $headerRowIndex = null;
                $colNama = null;
                $colWa = null;
                $colRig = null;
                $colCrew = null;
                $colPosisi = null;

                foreach ($rows as $rowIndex => $row) {
                    foreach ($row as $colKey => $cellValue) {
                        $cellTxt = strtolower(trim((string)$cellValue));
                        if (empty($cellTxt)) continue;

                        if ($colNama === null && (strpos($cellTxt, 'nama') !== false || strpos($cellTxt, 'karyawan') !== false || $cellTxt === 'name')) {
                            $colNama = $colKey;
                            $headerRowIndex = $rowIndex;
                        } elseif ($colWa === null && (strpos($cellTxt, 'wa') !== false || strpos($cellTxt, 'whatsapp') !== false || strpos($cellTxt, 'hp') !== false || strpos($cellTxt, 'telepon') !== false || strpos($cellTxt, 'telp') !== false)) {
                            $colWa = $colKey;
                        } elseif ($colRig === null && (strpos($cellTxt, 'rig') !== false)) {
                            $colRig = $colKey;
                        } elseif ($colCrew === null && (strpos($cellTxt, 'crew') !== false)) {
                            $colCrew = $colKey;
                        } elseif ($colPosisi === null && (strpos($cellTxt, 'posisi') !== false || strpos($cellTxt, 'jabatan') !== false)) {
                            $colPosisi = $colKey;
                        }
                    }
                    if ($colNama !== null) break;
                }

                // Fallback: Jika tidak ada baris header yang terdeteksi secara eksplisit
                if ($colNama === null) {
                    $colNama = 'A';
                    $colWa = 'B';
                    $startRow = 1;
                } else {
                    $startRow = $headerRowIndex + 1;
                }

                foreach ($rows as $rowIndex => $row) {
                    if ($rowIndex < $startRow) continue;

                    $namaVal = trim((string)($row[$colNama] ?? ''));
                    if (empty($namaVal) || strtolower($namaVal) === 'nama' || strtolower($namaVal) === 'total' || is_numeric($namaVal)) {
                        continue;
                    }

                    $waVal = $colWa ? trim((string)($row[$colWa] ?? '')) : '';
                    $waClean = preg_replace('/[^0-9]/', '', $waVal);
                    if (!empty($waClean) && strpos($waClean, '08') === 0) {
                        $waClean = '62' . substr($waClean, 1);
                    }

                    $rigVal = $colRig ? trim((string)($row[$colRig] ?? '')) : ($sheetRig ?? '');
                    $crewVal = $colCrew ? trim((string)($row[$colCrew] ?? '')) : ($sheetCrew ?? '');
                    $posisiVal = $colPosisi ? trim((string)($row[$colPosisi] ?? '')) : '';

                    $results[] = [
                        'nama' => $namaVal,
                        'nomor_wa' => $waClean,
                        'kode_rig' => $rigVal,
                        'crew' => $crewVal,
                        'posisi' => $posisiVal
                    ];
                }
            }
        } catch (\Exception $e) {
            // Exception handling
        }

        return $results;
    }

    // SINKRONKAN DENGAN MASTER CREW
    public function syncPenerimaCrew() {
        $this->model('PenerimaSlipModel')->syncWithCrewMaster();
        Helper::setFlash('success', '✅ Data Rig, Crew, dan Posisi penerima berhasil disinkronkan dengan Master Crew!');
        $this->redirectWithFilters('admin_gaji/penerima');
    }
    
    // TOGGLE STATUS PENERIMA
    public function togglePenerima($id) {
        $model = $this->model('PenerimaSlipModel');
        $p = $model->find($id);
        if ($p) {
            $newStatus = ($p['status_aktif'] == 'aktif') ? 'nonaktif' : 'aktif';
            $model->update($id, ['status_aktif' => $newStatus]);
            Helper::setFlash('success', 'Status berhasil diubah!');
        }
        $this->redirectWithFilters('admin_gaji/penerima');
    }

    // HAPUS PENERIMA
    public function deletePenerima($id) {
        $penerimaModel = $this->model('PenerimaSlipModel');
        $penerimaModel->delete($id);
        Helper::setFlash('success', 'Data penerima berhasil dihapus!');
        $this->redirectWithFilters('admin_gaji/penerima');
    }

    // UPLOAD & KIRIM
    public function kirim() {
        $selectedPeriode = $_GET['periode'] ?? date('Y-m');
        $kirimModel = $this->model('KirimLogModel');

        $qrFile = __DIR__ . '/../../wa_service/qr.txt';
        $qrCode = (file_exists($qrFile)) ? trim(file_get_contents($qrFile)) : null;

        $codeFile = __DIR__ . '/../../wa_service/pairing_code.txt';
        $pairingCode = (file_exists($codeFile)) ? trim(file_get_contents($codeFile)) : null;

        $logs = $kirimModel->getStatusPaginated($selectedPeriode, null, null, 15, 0);
        $totalPending = $kirimModel->countStatusFiltered($selectedPeriode, 'pending');
        $totalTerkirim = $kirimModel->countStatusFiltered($selectedPeriode, 'terkirim');
        $totalGagal = $kirimModel->countStatusFiltered($selectedPeriode, 'gagal');

        $data = [
            'title' => 'Upload & Kirim Slip',
            'currentPage' => 'gaji_kirim',
            'selectedPeriode' => $selectedPeriode,
            'logs' => $logs,
            'totalPending' => $totalPending,
            'totalTerkirim' => $totalTerkirim,
            'totalGagal' => $totalGagal,
            'qrCode' => $qrCode,
            'pairingCode' => $pairingCode
        ];
        $this->view('admin_gaji/kirim', $data);
    }

    // STATUS PENGIRIMAN (WITH FILTER PERIODE, STATUS, SEARCH & PAGINATION)
    public function status() {
        $kirimModel = $this->model('KirimLogModel');
        $qrFile = __DIR__ . '/../../wa_service/qr.txt';
        $qrCode = (file_exists($qrFile)) ? trim(file_get_contents($qrFile)) : null;

        $codeFile = __DIR__ . '/../../wa_service/pairing_code.txt';
        $pairingCode = (file_exists($codeFile)) ? trim(file_get_contents($codeFile)) : null;
        
        $selectedPeriode = $_GET['periode'] ?? '';
        $selectedStatus = $_GET['status'] ?? '';
        $search = trim($_GET['q'] ?? $_GET['search'] ?? '');
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 10;

        $totalLog = $kirimModel->countStatusFiltered($selectedPeriode, $selectedStatus, $search);
        $totalPages = max(1, ceil($totalLog / $perPage));
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        $log = $kirimModel->getStatusPaginated($selectedPeriode, $selectedStatus, $search, $perPage, $offset);

        $totalCount = $kirimModel->countStatusFiltered($selectedPeriode, null, null);
        $totalTerkirim = $kirimModel->countStatusFiltered($selectedPeriode, 'terkirim', null);
        $totalPending = $kirimModel->countStatusFiltered($selectedPeriode, 'pending', null);
        $totalGagal = $kirimModel->countStatusFiltered($selectedPeriode, 'gagal', null);

        $data = [
            'title' => 'Status Pengiriman',
            'currentPage' => 'gaji_status',
            'qrCode' => $qrCode,
            'pairingCode' => $pairingCode,
            'daftarPeriode' => $kirimModel->getDaftarPeriodeLog(),
            'selectedPeriode' => $selectedPeriode,
            'selectedStatus' => $selectedStatus,
            'search' => $search,
            'log' => $log,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'totalLog' => $totalLog,
            'totalCount' => $totalCount,
            'totalTerkirim' => $totalTerkirim,
            'totalPending' => $totalPending,
            'totalGagal' => $totalGagal,
            'offset' => $offset
        ];
        $this->view('admin_gaji/status', $data);
    }

    // API: AJAX LIVE SEARCH PENERIMA
    public function penerimaTable() {
        $penerimaModel = $this->model('PenerimaSlipModel');
        
        $search = trim($_GET['q'] ?? $_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $crew = trim($_GET['crew'] ?? '');
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 10;

        $totalPenerima = $penerimaModel->countFiltered($search, $status, $crew);
        $totalPages = max(1, ceil($totalPenerima / $perPage));
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        $penerima = $penerimaModel->getPaginated($search, $status, $perPage, $offset, 'nama', 'ASC', $crew);

        ob_start();
        if ($penerima && $penerima->num_rows > 0):
            $no = $offset + 1;
            while ($p = $penerima->fetch_assoc()):
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><strong style="color: #775537;"><?= htmlspecialchars($p['nama']) ?></strong></td>
                <td><?= htmlspecialchars($p['nomor_wa']) ?></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($p['kode_rig'] ?? '-') ?></span></td>
                <td><span class="badge bg-outline-dark text-dark border"><?= htmlspecialchars($p['crew'] ?? '-') ?></span></td>
                <td><?= htmlspecialchars($p['posisi'] ?? '-') ?></td>
                <td>
                    <?= $p['status_aktif'] == 'aktif' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' ?>
                </td>
                <td class="text-end">
                    <?php
                    $qsParams = [];
                    if (!empty($search)) $qsParams[] = 'q=' . urlencode($search);
                    if (!empty($status)) $qsParams[] = 'status=' . urlencode($status);
                    if (!empty($crew)) $qsParams[] = 'crew=' . urlencode($crew);
                    if (!empty($page) && $page > 1) $qsParams[] = 'page=' . (int)$page;
                    $filterQs = !empty($qsParams) ? '?' . implode('&', $qsParams) : '';
                    ?>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editPenerima(<?= $p['id_penerima'] ?>, '<?= addslashes(htmlspecialchars($p['nama'])) ?>', '<?= addslashes(htmlspecialchars($p['nomor_wa'])) ?>', '<?= $p['id_rig'] ?? '' ?>', '<?= addslashes(htmlspecialchars($p['crew'] ?? '')) ?>', '<?= addslashes(htmlspecialchars($p['posisi'] ?? '')) ?>')" title="Edit Data"><i class="fas fa-edit"></i></button>
                    <a href="<?= BASE_URL ?>/admin_gaji/togglePenerima/<?= $p['id_penerima'] ?><?= $filterQs ?>" class="btn btn-sm btn-outline-warning me-1" title="Ubah Status Aktif"><i class="fas fa-power-off"></i></a>
                    <a href="<?= BASE_URL ?>/admin_gaji/deletePenerima/<?= $p['id_penerima'] ?><?= $filterQs ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus penerima ini?')" title="Hapus Penerima"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php
            endwhile;
        else:
            ?>
            <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada data penerima</td></tr>
            <?php
        endif;
        $html = ob_get_clean();

        ob_start();
        if ($totalPages > 1):
            $prevPage = max(1, $page - 1);
            $nextPage = min($totalPages, $page + 1);
            $prevDisabled = $page <= 1 ? 'disabled' : '';
            $nextDisabled = $page >= $totalPages ? 'disabled' : '';
            ?>
            <a href="javascript:void(0)" onclick="loadPenerimaPage(<?= $prevPage ?>)" class="btn btn-sm btn-prev <?= $prevDisabled ?>">&laquo;</a>
            <span class="pagination-numbers">
                <?= Helper::renderPaginationNumbers($page, $totalPages, 'loadPenerimaPage') ?>
            </span>
            <a href="javascript:void(0)" onclick="loadPenerimaPage(<?= $nextPage ?>)" class="btn btn-sm btn-next <?= $nextDisabled ?>">&raquo;</a>
            <?php
        endif;
        $paginationHtml = ob_get_clean();

        header('Content-Type: application/json');
        echo json_encode([
            'html' => $html,
            'pagination' => $paginationHtml,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $totalPenerima
        ]);
        exit;
    }

    // API: AJAX LIVE SEARCH STATUS
    public function statusTable() {
        $kirimModel = $this->model('KirimLogModel');
        
        $selectedPeriode = $_GET['periode'] ?? '';
        $selectedStatus = $_GET['status'] ?? '';
        $search = trim($_GET['q'] ?? $_GET['search'] ?? '');
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 10;

        $totalLog = $kirimModel->countStatusFiltered($selectedPeriode, $selectedStatus, $search);
        $totalPages = max(1, ceil($totalLog / $perPage));
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        $log = $kirimModel->getStatusPaginated($selectedPeriode, $selectedStatus, $search, $perPage, $offset);

        ob_start();
        if ($log && $log->num_rows > 0):
            $no = $offset + 1;
            while ($l = $log->fetch_assoc()):
            ?>
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
                <td><code><?= htmlspecialchars($l['nama_file']) ?></code></td>
                <td>
                    <?php
                    if ($l['status'] == 'terkirim') echo '<span class="badge bg-success">Terkirim</span>';
                    elseif ($l['status'] == 'pending') echo '<span class="badge bg-warning">Pending</span>';
                    else echo '<span class="badge bg-danger">Gagal</span>';
                    ?>
                </td>
                <td class="small text-muted"><?= htmlspecialchars($l['pesan_error'] ?? '-') ?></td>
                <td class="small text-muted"><?= htmlspecialchars($l['dikirim_pada'] ?? '-') ?></td>
            </tr>
            <?php
            endwhile;
        else:
            ?>
            <tr><td colspan="10" class="text-center py-4 text-muted">Belum ada data pengiriman</td></tr>
            <?php
        endif;
        $html = ob_get_clean();

        ob_start();
        if ($totalPages > 1):
            $prevPage = max(1, $page - 1);
            $nextPage = min($totalPages, $page + 1);
            $prevDisabled = $page <= 1 ? 'disabled' : '';
            $nextDisabled = $page >= $totalPages ? 'disabled' : '';
            ?>
            <a href="javascript:void(0)" onclick="loadStatusPage(<?= $prevPage ?>)" class="btn btn-sm btn-prev <?= $prevDisabled ?>">&laquo;</a>
            <span class="pagination-numbers">
                <?= Helper::renderPaginationNumbers($page, $totalPages, 'loadStatusPage') ?>
            </span>
            <a href="javascript:void(0)" onclick="loadStatusPage(<?= $nextPage ?>)" class="btn btn-sm btn-next <?= $nextDisabled ?>">&raquo;</a>
            <?php
        endif;
        $paginationHtml = ob_get_clean();

        header('Content-Type: application/json');
        echo json_encode([
            'html' => $html,
            'pagination' => $paginationHtml,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $totalLog
        ]);
        exit;
    }

    // REQUEST PAIRING CODE VIA PHONE NUMBER
    public function requestPairingCode() {
        $phone = trim($_POST['nomor_wa'] ?? $_GET['nomor_wa'] ?? '');
        if (empty($phone)) {
            Helper::setFlash('error', 'Masukkan nomor WhatsApp Anda terlebih dahulu!');
            $this->redirect('admin_gaji/status');
            return;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanPhone) < 10) {
            Helper::setFlash('error', 'Format nomor WA tidak valid! Contoh: 628123456789');
            $this->redirect('admin_gaji/status');
            return;
        }

        $res = AdminGajiWaHelper::requestPairingCode($cleanPhone);
        if (isset($res['success']) && !$res['success']) {
            Helper::setFlash('error', $res['error'] ?? 'Gagal meminta kode pairing!');
        } else {
            Helper::setFlash('success', '🔑 Kode Pairing 8-Digit sedang disiapkan! Halaman akan otomatis memperbarui statusnya...');
        }
        $this->redirect('admin_gaji/status');
    }

    // BERSIHKAN LOG PENGIRIMAN
    public function clearLogs() {
        // Stop background sending worker in Node.js first
        AdminGajiWaHelper::stopSending();
        
        $kirimModel = $this->model('KirimLogModel');
        $kirimModel->query("TRUNCATE TABLE kirim_log");
        Helper::setFlash('success', '🧹 Log status pengiriman berhasil dibersihkan & pengiriman dihentikan!');
        $this->redirect('admin_gaji/status');
    }
    
    // PROSES UPLOAD ZIP
    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('admin_gaji/kirim');
        
        // AUTO-STOP any old sending process when new ZIP is uploaded!
        AdminGajiWaHelper::stopSending();

        $periode = $_POST['periode'] ?? date('Y-m');
        
        if (!empty($_FILES['zip_file']['name'])) {
            $zip = new ZipArchive();
            $uploadDir = __DIR__ . '/../../wa_service_v2/uploads/' . $periode . '/';
            
            if (is_dir($uploadDir)) {
                // Purge old files and mapping from upload directory so old files never mix with new upload
                $oldItems = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($uploadDir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($oldItems as $item) {
                    if ($item->isFile()) {
                        @unlink($item->getPathname());
                    } elseif ($item->isDir()) {
                        @rmdir($item->getPathname());
                    }
                }
            } else {
                mkdir($uploadDir, 0777, true);
            }
            
            if ($zip->open($_FILES['zip_file']['tmp_name']) === TRUE) {
                $zip->extractTo($uploadDir);
                $zip->close();
                
                $penerimaModel = $this->model('PenerimaSlipModel');
                $kirimModel = $this->model('KirimLogModel');
                
                // Recursively find all PDF files even if inside subfolders in the ZIP
                $files = [];
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($uploadDir, RecursiveDirectoryIterator::SKIP_DOTS)
                );
                
                foreach ($iterator as $fileinfo) {
                    if ($fileinfo->isFile() && strtolower($fileinfo->getExtension()) === 'pdf') {
                        $targetPath = $uploadDir . $fileinfo->getFilename();
                        if ($fileinfo->getPathname() !== $targetPath) {
                            @rename($fileinfo->getPathname(), $targetPath);
                        }
                        $files[] = $targetPath;
                    }
                }
                
                $count = 0;
                $mapping = [];
                $unmatchedFiles = [];
                
                foreach ($files as $file) {
                    $rawFilename = pathinfo($file, PATHINFO_FILENAME);
                    
                    // Strip common prefixes like "SLIP GAJI", "SLIP GAJI WOWS", "SLIP", "GAJI"
                    $cleanFilename = preg_replace('/^(SLIP\s*GAJI(\s*WOWS)?|SLIP|GAJI)\s+/i', '', trim($rawFilename));
                    
                    // Extract name and optional position if format is "NAMA - POSISI"
                    $extractedName = $cleanFilename;
                    $extractedPosisi = null;

                    if (strpos($cleanFilename, '-') !== false) {
                        $parts = explode('-', $cleanFilename);
                        if (count($parts) >= 2) {
                            $extractedName = trim($parts[0]);
                            $extractedPosisi = trim($parts[count($parts) - 1]);
                        }
                    }

                    // Try finding existing recipient
                    $penerima = $penerimaModel->getByNamaOrNik($extractedName);
                    if (!$penerima) {
                        $penerima = $penerimaModel->getByNamaOrNik($cleanFilename);
                    }
                    if (!$penerima) {
                        $penerima = $penerimaModel->getByNamaOrNik($rawFilename);
                    }

                    // If recipient does not exist yet, auto-create into penerima_slip & auto-match with crew master
                    if (!$penerima) {
                        $resCrew = $penerimaModel->query("SELECT c.*, r.kode_rig FROM crew c LEFT JOIN rig r ON c.id_rig = r.id_rig WHERE LOWER(TRIM(c.nama)) = LOWER(?) LIMIT 1", [$extractedName]);
                        $crewMatch = ($resCrew && $resCrew->num_rows > 0) ? $resCrew->fetch_assoc() : null;

                        $newPenerimaData = [
                            'nama' => $extractedName,
                            'nomor_wa' => '',
                            'id_rig' => $crewMatch['id_rig'] ?? null,
                            'kode_rig' => $crewMatch['kode_rig'] ?? null,
                            'crew' => $crewMatch['crew'] ?? null,
                            'posisi' => !empty($extractedPosisi) ? $extractedPosisi : ($crewMatch['posisi'] ?? null),
                            'status_aktif' => 'aktif'
                        ];

                        $newId = $penerimaModel->insert($newPenerimaData);
                        if ($newId) {
                            $penerima = $penerimaModel->find($newId);
                        }
                    } else {
                        // Update position if available and empty
                        if (!empty($extractedPosisi) && empty($penerima['posisi'])) {
                            $penerimaModel->update($penerima['id_penerima'], ['posisi' => $extractedPosisi]);
                        }
                    }

                    if ($penerima && $penerima['status_aktif'] == 'aktif') {
                        $existLog = $kirimModel->query("SELECT id_log, status FROM kirim_log WHERE id_penerima = ? AND periode = ? AND nama_file = ? LIMIT 1", [
                            $penerima['id_penerima'],
                            $periode,
                            basename($file)
                        ]);
                        $logStatus = 'pending';
                        if ($existLog && $existLog->num_rows > 0) {
                            $rowLog = $existLog->fetch_assoc();
                            $logStatus = $rowLog['status'] ?? 'pending';
                            // STRICT ANTI-DUPLICATE: Only reset if not already terkirim!
                            if ($logStatus !== 'terkirim') {
                                $kirimModel->update($rowLog['id_log'], [
                                    'status' => 'pending',
                                    'pesan_error' => null
                                ]);
                                $logStatus = 'pending';
                            }
                        } else {
                            $kirimModel->insert([
                                'id_penerima' => $penerima['id_penerima'],
                                'periode' => $periode,
                                'nama_file' => basename($file),
                                'status' => 'pending'
                            ]);
                        }
                        $count++;
                        
                        $mapping[$rawFilename] = [
                            'nama' => $penerima['nama'],
                            'nomor_wa' => $penerima['nomor_wa'],
                            'status' => $logStatus
                        ];
                    }
                }

                // Auto-sync all recipients with master crew
                $penerimaModel->syncWithCrewMaster();

                // Clean up stale pending/gagal log entries for this period that are not part of the newly uploaded ZIP file list
                if (!empty($files)) {
                    $validBasenames = array_map(function($f) use ($kirimModel) {
                        return "'" . $kirimModel->getDb()->real_escape_string(basename($f)) . "'";
                    }, $files);
                    $inList = implode(',', $validBasenames);
                    $pEsc = $kirimModel->getDb()->real_escape_string($periode);
                    $kirimModel->query("DELETE FROM kirim_log WHERE periode = '{$pEsc}' AND status != 'terkirim' AND nama_file NOT IN ({$inList})");
                }
                
                // Write mapping.json so Node.js service can match PDF filename to recipient
                $mappingFile = $uploadDir . 'mapping.json';
                file_put_contents($mappingFile, json_encode($mapping, JSON_PRETTY_PRINT));
                
                if ($count > 0) {
                    Helper::setFlash('success', "🚀 {$count} slip gaji berhasil disiapkan! Silakan klik tombol 'Mulai Kirim WA Otomatis' di bawah ini.");
                } else {
                    $totalPdf = count($files);
                    if ($totalPdf === 0) {
                        Helper::setFlash('error', '⚠️ Tidak ditemukan file PDF di dalam file ZIP yang diunggah. Pastikan file ZIP berisi file slip gaji berformat .pdf!');
                    } else {
                        $sampleNames = implode(', ', array_slice($unmatchedFiles, 0, 3));
                        Helper::setFlash('error', "⚠️ Ditemukan {$totalPdf} file PDF di dalam ZIP, tetapi 0 yang cocok dengan data penerima di database! (Contoh file di ZIP: \"{$sampleNames}\"). Pastikan nama penerima sudah didaftarkan di menu 'Penerima'!");
                    }
                }
            } else {
                Helper::setFlash('error', 'Gagal extract ZIP!');
            }
        }
        
        $this->redirect('admin_gaji/kirim?periode=' . urlencode($periode));
    }
    
    // LAUNCHER FOR NODE.JS WORKER VIA HTTP
    public function startWorker() {
        $periode = $_POST['periode'] ?? $_GET['periode'] ?? date('Y-m');
        $res = AdminGajiWaHelper::sendBatch($periode);
        if (isset($res['success']) && $res['success']) {
            Helper::setFlash('success', '🚀 Pengiriman slip gaji periode ' . htmlspecialchars($periode) . ' dimulai!');
        } else {
            Helper::setFlash('error', $res['error'] ?? 'Gagal mengaktifkan pengiriman slip gaji!');
        }
        $this->redirect('admin_gaji/status');
    }

    // STOP WORKER MANUALLY
    public function stopWorker() {
        $res = AdminGajiWaHelper::stopSending();
        if (isset($res['success']) && $res['success']) {
            Helper::setFlash('success', '🛑 Pengiriman slip gaji berhasil dihentikan!');
        } else {
            Helper::setFlash('error', $res['error'] ?? 'Gagal menghentikan pengiriman!');
        }
        $this->redirectWithFilters('admin_gaji/status');
    }

    // RETRY SINGLE FAILED / PENDING LOG ITEM
    public function retryKirim($id_log) {
        $kirimModel = $this->model('KirimLogModel');
        $log = $kirimModel->find($id_log);
        if (!$log) {
            Helper::setFlash('error', 'Log pengiriman tidak ditemukan!');
            $this->redirectWithFilters('admin_gaji/status');
            return;
        }

        // STRICT ANTI-DUPLICATE: If already sent, NEVER allow resetting to pending!
        if ($log['status'] === 'terkirim') {
            Helper::setFlash('error', '⚠️ Slip gaji ini SUDAH TERKIRIM. Pengiriman ulang ditolak untuk mencegah pesan ganda.');
            $this->redirectWithFilters('admin_gaji/status');
            return;
        }

        // Reset status to pending
        $kirimModel->update($id_log, [
            'status' => 'pending',
            'pesan_error' => null
        ]);

        // Trigger sendBatch for the log's period
        $res = AdminGajiWaHelper::sendBatch($log['periode']);
        if (isset($res['success']) && $res['success']) {
            Helper::setFlash('success', '🚀 Pengiriman ulang untuk ' . htmlspecialchars($log['nama_file']) . ' dimulai!');
        } else {
            Helper::setFlash('error', $res['error'] ?? 'Gagal memproses pengiriman ulang!');
        }
        $this->redirectWithFilters('admin_gaji/status');
    }

    // RETRY ALL FAILED LOG ITEMS FOR A PERIOD
    public function retryAllGagal() {
        $periode = $_GET['periode'] ?? date('Y-m');
        $kirimModel = $this->model('KirimLogModel');

        // Reset all failed logs (STRICTLY excluding terkirim) back to pending
        $pEsc = $kirimModel->getDb()->real_escape_string($periode);
        $sql = "UPDATE kirim_log SET status = 'pending', pesan_error = NULL WHERE status = 'gagal'";
        if (!empty($periode)) {
            $sql .= " AND periode = '{$pEsc}'";
        }
        $kirimModel->query($sql);

        $res = AdminGajiWaHelper::sendBatch($periode);
        if (isset($res['success']) && $res['success']) {
            Helper::setFlash('success', '🚀 Pengiriman ulang untuk item yang gagal dimulai!');
        } else {
            Helper::setFlash('error', $res['error'] ?? 'Gagal mengaktifkan pengiriman ulang!');
        }
        $this->redirectWithFilters('admin_gaji/status');
    }

    // RESET WA SESSION & GENERATE FRESH QR CODE
    public function resetWaSession() {
        $res = AdminGajiWaHelper::resetSession();
        if (isset($res['success']) && $res['success']) {
            Helper::setFlash('success', '🔄 Sesi WhatsApp telah di-reset bersih! QR Code baru sedang dibuat.');
        } else {
            Helper::setFlash('error', $res['error'] ?? 'Gagal mereset sesi WhatsApp!');
        }
        $this->redirect('admin_gaji/status');
    }

    // API: CHECK QR STATUS FOR AJAX POLLING
    public function getQrStatus() {
        header('Content-Type: application/json');
        $statusData = AdminGajiWaHelper::getStatus();

        $qrCode = $statusData['qrRaw'] ?? null;
        $pairingCode = $statusData['pairingCode'] ?? null;
        $loading = $statusData['loading'] ?? false;
        $error = $statusData['error'] ?? null;

        echo json_encode([
            'status'        => $statusData['status'] ?? null,
            'authenticated' => $statusData['authenticated'] ?? false,
            'qr'            => $qrCode ? urlencode($qrCode) : null,
            'qrRaw'         => $qrCode ?: null,
            'pairingCode'   => $pairingCode,
            'loading'       => $loading,
            'error'         => $error,
            'sending'       => $statusData['sending'] ?? false,
            'progress'      => $statusData['progress'] ?? null
        ]);
        exit;
    }

    // API UPDATE STATUS (CALLBACK FROM NODE.JS SCRIPT)
    public function updateStatus() {
        header('Content-Type: application/json');
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (!$data) {
            $data = $_POST;
        }

        $identifier = $data['nik'] ?? $data['identifier'] ?? '';
        $periode = $data['periode'] ?? '';
        $status = $data['status'] ?? 'pending';
        $error = $data['error'] ?? null;

        if (empty($identifier)) {
            echo json_encode(['success' => false, 'message' => 'Identifier required']);
            exit;
        }

        $kirimModel = $this->model('KirimLogModel');
        $updated = $kirimModel->updateStatusByIdentifier($identifier, $periode, $status, $error);

        // Also sync mapping.json on disk if present
        if (!empty($periode)) {
            $cleanPeriode = preg_replace('/[^0-9\-]/', '', $periode);
            $uploadDir = __DIR__ . '/../../wa_service_v2/uploads/' . $cleanPeriode . '/';
            $mappingFile = $uploadDir . 'mapping.json';
            if (file_exists($mappingFile)) {
                $mappingData = json_decode(file_get_contents($mappingFile), true);
                if (is_array($mappingData) && isset($mappingData[$identifier])) {
                    $mappingData[$identifier]['status'] = $status;
                    if ($error) {
                        $mappingData[$identifier]['error'] = $error;
                    }
                    file_put_contents($mappingFile, json_encode($mappingData, JSON_PRETTY_PRINT));
                }
            }
        }

        echo json_encode(['success' => (bool)$updated]);
        exit;
    }

    // PREVIEW / DOWNLOAD UPLOADED SLIP PDF FILE
    public function previewPdf() {
        $periode = $_GET['periode'] ?? date('Y-m');
        $fileName = $_GET['file'] ?? '';

        if (empty($fileName)) {
            die('Nama file tidak valid.');
        }

        $cleanFileName = basename($fileName);
        $cleanPeriode = preg_replace('/[^0-9\-]/', '', $periode);
        $filePath = __DIR__ . '/../../wa_service_v2/uploads/' . $cleanPeriode . '/' . $cleanFileName;

        if (!file_exists($filePath)) {
            die('File PDF tidak ditemukan di server: ' . htmlspecialchars($cleanFileName));
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $cleanFileName . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}
