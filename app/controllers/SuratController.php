<?php
class SuratController extends Controller
{

    public function __construct()
    {
        $this->requireLogin();
    }

    // daftar surat
    public function index()
    {
        $suratModel = $this->model('SuratModel');
        $jenisModel = $this->model('JenisSuratModel');

        $isSuperAdmin = $this->isSuperAdmin();
        $isAllRig = $this->isAdminAllRig();
        $rigIds = $this->getCurrentRigIds();

        $filter_rig = $_GET['rig'] ?? '';
        $filter_jenis = $_GET['jenis'] ?? '';
        $filter_tahun = $_GET['tahun'] ?? '';
        $filter_search = $_GET['search'] ?? '';

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 10;
        $totalSuratAll = $suratModel->countFiltered($rigIds, $isAllRig, $filter_rig, $filter_jenis, $filter_tahun, $filter_search);
        $totalPages = ceil($totalSuratAll / $perPage);
        $offset = ($page - 1) * $perPage;

        $surat = $suratModel->getFiltered($rigIds, $isAllRig, $filter_rig, $filter_jenis, $filter_tahun, $filter_search, $perPage, $offset);
        $jenis_surat = $jenisModel->all();
        $rigList = $this->model('RigModel')->allActive();

        $data = [
            'title' => 'Surat Keluar',
            'currentPage' => 'surat',
            'surat' => $surat,
            'jenis_surat' => $jenis_surat,
            'rigList' => $rigList,
            'isSuperAdmin' => $isSuperAdmin,
            'filter_rig' => $filter_rig,
            'filter_jenis' => $filter_jenis,
            'filter_tahun' => $filter_tahun,
            'filter_search' => $filter_search,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalSuratAll' => $totalSuratAll,
            'offset' => $offset,
        ];

        $this->view('surat/index', $data);
    }

    // FORM TAMBAH
    public function create()
    {
        $jenisModel = $this->model('JenisSuratModel');
        $rigIds = $this->getCurrentRigIds();
        $kode_rig = $_SESSION['user']['kode_rig'] ?? '';

        $data = [
            'title' => 'Tambah Surat Baru',
            'currentPage' => 'surat',
            'jenis_surat' => $jenisModel->all(),
            'kode_rig' => $kode_rig,
            'isSuperAdmin' => $this->isSuperAdmin(),
            'rigList' => $this->model('RigModel')->allActive(),
            'rigIds' => $rigIds
        ];
        $this->view('surat/create', $data);
    }

    // SIMPAN SURAT BARU
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('surat');
        }

        $suratModel = $this->model('SuratModel');
        $jenisModel = $this->model('JenisSuratModel');
        $rigIds = $this->getCurrentRigIds();

        $id_jenis = intval($_POST['id_jenis']);

        if ($this->isSuperAdmin() && !empty($_POST['id_rig'])) {
            $id_rig = intval($_POST['id_rig']);
        } elseif (!empty($rigIds)) {
            $id_rig = $rigIds[0];
        } else {
            $id_rig = 1;
        }

        $jenis = $jenisModel->find($id_jenis);
        if (!$jenis) {
            die("Jenis surat tidak ditemukan!");
        }

        $rig = $this->model('RigModel')->find($id_rig);

        $tanggal = $_POST['tanggal_moc'] ?? date('Y-m-d');
        $tahun = date('Y', strtotime($tanggal));
        $bulan = date('m', strtotime($tanggal));

        // MCU: nomor urut berdasarkan hitungan record MCU existing + 1
        if ($jenis['kode'] === 'MCU') {
            $mcuResult = $suratModel->query("SELECT COUNT(*) as total FROM surat s JOIN jenis_surat j ON s.id_jenis = j.id_jenis WHERE j.kode = 'MCU'");
            $mcuCount = (int) $mcuResult->fetch_assoc()['total'];
            $no_urut = $mcuCount + 1;
            $no_format = str_pad($no_urut, 3, '0', STR_PAD_LEFT);
            $nomor_surat = 'MCU-' . $no_format;
        } else {
            $lastNumber = $suratModel->getLastNumberByJenis($id_jenis);
            $no_urut = $lastNumber + 1;
            $no_format = str_pad($no_urut, 3, '0', STR_PAD_LEFT);
            $format = $jenis['format_nomor'];
            $rawRig = trim($rig['kode_rig'] ?? '');
            $rigCode = preg_replace('/^RIG-?/i', '', $rawRig);
            if (preg_match('/^\d+$/', $rigCode)) {
                $rigCode = 'GW-' . $rigCode;
            }
            $nomor_surat = str_replace(
                ['{kode}', '{rig}', '{tahun}', '{bulan}', '{no}'],
                [$jenis['kode'], $rigCode, $tahun, $bulan, $no_format],
                $format
            );
        }

        $manualIsiSurat = trim($_POST['isi_surat'] ?? '');
        $generatedIsiSurat = trim($_POST['isi_surat_generated'] ?? '');
        $isiSuratValue = $generatedIsiSurat !== '' ? $generatedIsiSurat : $manualIsiSurat;

        if ($jenis['kode'] === 'PKWT') {
            $pkwtCrewId = (int) ($_POST['pkwt_id_crew'] ?? 0);
            $pkwtCrew = $this->model('CrewModel')->find($pkwtCrewId);
            if (!$pkwtCrew || ($pkwtCrew['status_aktif'] ?? '') !== 'aktif') {
                die('Crew PKWT tidak ditemukan atau sudah nonaktif.');
            }
            if ((int) ($pkwtCrew['id_rig'] ?? 0) !== $id_rig) {
                die('Crew PKWT tidak berada di rig yang dipilih.');
            }
            $isiSuratValue = json_encode([
                'type' => 'pkwt',
                'id_crew' => $pkwtCrewId,
                'template' => trim($_POST['pkwt_template'] ?? ($pkwtCrew['posisi'] ?? '')),
                'tanggal_mulai' => $_POST['pkwt_tanggal_mulai'] ?? $tanggal,
                'tanggal_berakhir' => $_POST['pkwt_tanggal_berakhir'] ?? '',
            ], JSON_UNESCAPED_UNICODE);
            $_POST['tujuan'] = $pkwtCrew['nama'];
            $_POST['perihal'] = 'Perjanjian Kerja Waktu Tertentu - ' . ($pkwtCrew['posisi'] ?? '');
        }

        $suratModel->insert([
            'id_rig' => $id_rig,
            'id_jenis' => $id_jenis,
            'id_user' => $_SESSION['user']['id_user'],
            'nomor_surat' => $nomor_surat,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'no_urut' => $no_urut,
            'tanggal_moc' => !empty($_POST['tanggal_moc']) ? $_POST['tanggal_moc'] : null,
            'keterangan' => $_POST['keterangan'] ?? '',
            'perihal' => $_POST['perihal'] ?? '',
            'isi_surat' => $isiSuratValue,
            'tujuan' => $_POST['tujuan'] ?? '',
            'link_file' => $_POST['link_file'] ?? '',
            'nama_file' => $_POST['nama_file'] ?? ''
        ]);

        Helper::setFlash('success', 'Surat berhasil dibuat!');
        $this->redirect('surat');
    }

    // FORM EDIT
    public function edit($id)
    {
        $suratModel = $this->model('SuratModel');
        $jenisModel = $this->model('JenisSuratModel');
        $crewModel = $this->model('CrewModel');

        $surat = $suratModel->getById($id);
        if (!$surat) $this->redirect('surat');

        $crewSearchOptions = [];
        $crewList = $crewModel->getAllActive();
        while ($crew = $crewList->fetch_assoc()) {
            $label = trim(($crew['nama'] ?? '') . ' - ' . ($crew['posisi'] ?? ''));
            $crewSearchOptions[] = [
                'id' => (int) $crew['id_crew'],
                'label' => $label,
                'rig' => (int) ($crew['id_rig'] ?? 0),
                'nama' => $crew['nama'] ?? '',
                'posisi' => $crew['posisi'] ?? '',
                'alamat' => $crew['alamat'] ?? '',
                'crew' => $crew['crew'] ?? '',
            ];
        }

        $data = [
            'title' => 'Edit Surat',
            'currentPage' => 'surat',
            'surat' => $surat,
            'jenis_surat' => $jenisModel->all(),
            'rigList' => $this->model('RigModel')->allActive(),
            'isSuperAdmin' => $this->isSuperAdmin(),
            'crewSearchOptions' => $crewSearchOptions
        ];
        $this->view('surat/edit', $data);
    }

    // UPDATE SURAT
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('surat');

        $suratModel = $this->model('SuratModel');
        $jenisModel = $this->model('JenisSuratModel');
        $rigModel = $this->model('RigModel');
        $suratLama = $suratModel->getById($id);
        if (!$suratLama) $this->redirect('surat');

        $manualIsiSurat = trim($_POST['isi_surat'] ?? '');
        $generatedIsiSurat = trim($_POST['isi_surat_generated'] ?? '');
        $isiSuratValue = $generatedIsiSurat !== '' ? $generatedIsiSurat : $manualIsiSurat;

        $idJenis = intval($_POST['id_jenis']);
        $idRig = $suratLama['id_rig'];
        if ($this->isSuperAdmin() && !empty($_POST['id_rig'])) {
            $idRig = intval($_POST['id_rig']);
        }

        $data = [
            'id_jenis' => $idJenis,
            'tanggal_moc' => !empty($_POST['tanggal_moc']) ? $_POST['tanggal_moc'] : null,
            'keterangan' => $_POST['keterangan'] ?? '',
            'perihal' => $_POST['perihal'] ?? '',
            'isi_surat' => $isiSuratValue,
            'tujuan' => $_POST['tujuan'] ?? '',
            'link_file' => $_POST['link_file'] ?? '',
            'nama_file' => $_POST['nama_file'] ?? ''
        ];

        if (!empty($_POST['tanggal_moc'])) {
            $data['tahun'] = date('Y', strtotime($_POST['tanggal_moc']));
            $data['bulan'] = date('m', strtotime($_POST['tanggal_moc']));
        }

        if ($this->isSuperAdmin() && !empty($_POST['id_rig'])) {
            $data['id_rig'] = $idRig;
        }

        $jenis = $jenisModel->find($idJenis);
        if ($jenis && $jenis['kode'] === 'PKWT') {
            $pkwtCrewId = (int) ($_POST['pkwt_id_crew'] ?? 0);
            $pkwtCrew = $this->model('CrewModel')->find($pkwtCrewId);
            if (!$pkwtCrew || ($pkwtCrew['status_aktif'] ?? '') !== 'aktif') {
                die('Crew PKWT tidak ditemukan atau sudah nonaktif.');
            }
            if ((int) ($pkwtCrew['id_rig'] ?? 0) !== $idRig) {
                die('Crew PKWT tidak berada di rig yang dipilih.');
            }

            $data['isi_surat'] = json_encode([
                'type' => 'pkwt',
                'id_crew' => $pkwtCrewId,
                'template' => trim($_POST['pkwt_template'] ?? ($pkwtCrew['posisi'] ?? '')),
                'tanggal_mulai' => $_POST['pkwt_tanggal_mulai'] ?? ($data['tanggal_moc'] ?? ''),
                'tanggal_berakhir' => $_POST['pkwt_tanggal_berakhir'] ?? '',
            ], JSON_UNESCAPED_UNICODE);
            $data['tujuan'] = $pkwtCrew['nama'];
            $data['perihal'] = 'Perjanjian Kerja Waktu Tertentu - ' . ($pkwtCrew['posisi'] ?? '');
        }
        $rig = $rigModel->find($idRig);
        if ($jenis && $rig && !empty($jenis['format_nomor'])) {
            $tahun = $data['tahun'] ?? ($suratLama['tahun'] ?? date('Y'));
            $bulan = $data['bulan'] ?? ($suratLama['bulan'] ?? date('m'));
            $noUrut = $suratLama['no_urut'] ?? 0;
            $noFormat = str_pad((int) $noUrut, 3, '0', STR_PAD_LEFT);
            $rawRig = trim($rig['kode_rig'] ?? '');
            $rigCode = preg_replace('/^RIG-?/i', '', $rawRig);
            if (preg_match('/^\d+$/', $rigCode)) {
                $rigCode = 'GW-' . $rigCode;
            }
            $data['nomor_surat'] = str_replace(
                ['{kode}', '{rig}', '{tahun}', '{bulan}', '{no}'],
                [$jenis['kode'], $rigCode, $tahun, $bulan, $noFormat],
                $jenis['format_nomor']
            );
        }

        $suratModel->update($id, $data);
        Helper::setFlash('success', 'Surat berhasil diupdate!');
        $this->redirect('surat');
    }

    // HAPUS SURAT
    public function delete($id)
    {
        $suratModel = $this->model('SuratModel');
        $surat = $suratModel->find($id);

        if ($surat) {
            $suratModel->delete($id);
            Helper::setFlash('success', 'Surat berhasil dihapus!');
        }

        $this->redirect('surat');
    }

    // CETAK SURAT
    public function cetak($id)
    {
        $suratModel = $this->model('SuratModel');
        $surat = $suratModel->getById($id);
        if (!$surat) $this->redirect('surat');

        // getById() mengembalikan kode jenis surat dengan nama kolom `kode_jenis`.
        // Jangan mengandalkan extract() di sini karena tidak ada kolom bernama `kode`.
        $kode = strtoupper(trim($surat['kode_jenis'] ?? ''));

        if ($kode === 'PKWT') {
            $this->cetakPkwt($surat);
            return;
        }
        $viewFile = __DIR__ . '/../../views/surat/cetak/cetak.php';

        if ($kode == 'SPK') {
            $viewFile = __DIR__ . '/../../views/surat/cetak/cetak_spk.php';
        } elseif ($kode == 'SPM') {
            $viewFile = __DIR__ . '/../../views/surat/cetak/cetak_spm.php';
        } elseif ($kode == 'PHK') {
            $viewFile = __DIR__ . '/../../views/surat/cetak/cetak_phk.php';
        } elseif ($kode == 'ST') {
            $viewFile = __DIR__ . '/../../views/surat/cetak/cetak_st.php';
        } elseif ($kode == 'SKA') {
            $viewFile = __DIR__ . '/../../views/surat/cetak/cetak_ska.php';
        } elseif ($kode == 'SC') {
            $viewFile = __DIR__ . '/../../views/surat/cetak/cetak_sc.php';
        } elseif ($kode == 'SP') {
            $viewFile = __DIR__ . '/../../views/surat/cetak/cetak_sp.php';
        } elseif ($kode == 'SPT') {
            $viewFile = __DIR__ . '/../../views/surat/cetak/cetak_spt.php';
        } elseif ($kode == 'SR') {
            $viewFile = __DIR__ . '/../../views/surat/cetak/cetak_sr.php';
        } elseif ($kode == 'SKK') {
            $viewFile = __DIR__ . '/../../views/surat/cetak/cetak_skk.php';
        } elseif ($kode == 'BA') {
            $viewFile = __DIR__ . '/../../views/surat/cetak/cetak_ba.php';
        } elseif ($kode == 'SJ') {
            $viewFile = __DIR__ . '/../../views/surat/cetak/cetak_sj.php';
        } elseif ($kode == 'MM') {
            $viewFile = __DIR__ . '/../../views/surat/cetak/cetak_mm.php';
        } elseif ($kode == 'MCU') {
            $viewFile = __DIR__ . '/../../views/surat/cetak/cetak_mcu.php';
        }

        if (!file_exists($viewFile)) {
            die("Template cetak untuk jenis surat {$kode} belum tersedia. Path: {$viewFile}");
        }

        require_once $viewFile;
        exit;
    }

    private function cetakPkwt(array $surat): void
    {
        $payload = json_decode($surat['isi_surat'] ?? '', true);
        if (!is_array($payload) || ($payload['type'] ?? '') !== 'pkwt') die('Data PKWT tidak valid.');
        $crew = $this->model('CrewModel')->find((int) ($payload['id_crew'] ?? 0));
        if (!$crew || ($crew['status_aktif'] ?? '') !== 'aktif') die('Crew PKWT sudah nonaktif atau tidak ditemukan.');
        
        // Get rig information
        $rig = $this->model('RigModel')->find((int) ($crew['id_rig'] ?? 0));
        $kodeRig = $rig ? strtoupper(str_replace(' ', '_', $rig['kode_rig'] ?? 'RIG')) : 'RIG';
        
        require_once __DIR__ . '/../../views/surat/cetak/cetak_pkwt.php';
        $dir = __DIR__ . '/../../public/uploads/generated';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        
        // Generate filename: PKWT nama posisi rig
        $nama = trim($crew['nama'] ?? 'Karyawan');
        $posisi = trim($crew['posisi'] ?? 'Posisi');
        $baseFileName = "PKWT {$nama} {$posisi} {$kodeRig}";
        $docxFile = $dir . '/' . $baseFileName . '.docx';
        $pdfFile = $dir . '/' . $baseFileName . '.pdf';

        $service = new CetakPkwt();
        $templateKey = trim((string) ($payload['template'] ?? ''));
        if ($templateKey === '') {
            $templateKey = trim((string) ($crew['posisi'] ?? ''));
        }
        if (!$service->generate($templateKey, $crew, [
            'nomor_surat' => $surat['nomor_surat'],
            'tanggal_surat' => $surat['tanggal_moc'] ?? date('Y-m-d'),
            'tanggal_mulai' => $payload['tanggal_mulai'] ?? $surat['tanggal_moc'],
            'tanggal_berakhir' => $payload['tanggal_berakhir'] ?? '',
        ], $docxFile, $rig ? ['kode_rig' => $rig['kode_rig']] : [])) die('Template PKWT tidak tersedia.');

        // Konversi .docx hasil template ke PDF menggunakan MS Word via PowerShell
        $psScript = __DIR__ . '/../services/convert_docx_to_pdf.ps1';
        if (file_exists($psScript)) {
            $cmd = 'powershell -ExecutionPolicy Bypass -File ' . escapeshellarg($psScript) . ' -docxPath ' . escapeshellarg($docxFile) . ' -pdfPath ' . escapeshellarg($pdfFile);
            exec($cmd);
        }

        while (ob_get_level()) {
            ob_end_clean();
        }

        if (file_exists($pdfFile) && filesize($pdfFile) > 0) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($pdfFile) . '"');
            header('Content-Length: ' . filesize($pdfFile));
            readfile($pdfFile);
            exit;
        }

        // Fallback untuk Linux Shared Hosting (InfinityFree) yang tidak memiliki MS Word / PowerShell:
        if (file_exists($docxFile) && filesize($docxFile) > 0) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . basename($docxFile) . '"');
            header('Content-Length: ' . filesize($docxFile));
            readfile($docxFile);
            exit;
        }

        http_response_code(500);
        die('File PKWT gagal dibuat. Pastikan template PKWT (.docx) tersedia di folder data/pkwt_templates/ pada server.');
    }
}
