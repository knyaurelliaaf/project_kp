<?php
class LaporanController extends Controller {

public function __construct() {
    $this->requireLogin();
}

public function index() {
    $laporanParam = $_GET['laporan'] ?? 'dokumen';
    if ($laporanParam === 'turnover') {
        $jenisLaporan = 'turnover';
    } elseif ($laporanParam === 'crew_aktif') {
        $jenisLaporan = 'crew_aktif';
    } else {
        $jenisLaporan = 'dokumen';
    }

    $rigIds = $this->getCurrentRigIds();
    $isAllRig = $this->isAdminAllRig();

    if ($jenisLaporan === 'crew_aktif') {
        $crewModel = $this->model('CrewModel');
        $rigModel = $this->model('RigModel');
        $filterRig = trim($_GET['rig'] ?? '');
        $filterCrewGroup = trim($_GET['crew'] ?? '');

        if (!$isAllRig) $filterRig = '';

        $activeCrewList = $crewModel->getActiveCrewDetailed($rigIds, $isAllRig, $filterRig);
        $totalCrew = $activeCrewList ? $activeCrewList->num_rows : 0;

        $this->view('laporan/index', [
            'title' => 'Laporan Data Crew Aktif',
            'currentPage' => 'laporan',
            'jenis_laporan' => 'crew_aktif',
            'activeCrewList' => $activeCrewList,
            'totalCrew' => $totalCrew,
            'rigList' => $rigModel->allActive(),
            'filter_rig' => $filterRig,
            'filter_crew_group' => $filterCrewGroup,
            'isSuperAdmin' => $this->isSuperAdmin()
        ]);
        return;
    }

    if ($jenisLaporan === 'turnover') {
        $crewModel = $this->model('CrewModel');
        $periode = $_GET['periode'] ?? 'bulan';
        if (!in_array($periode, ['bulan', 'semester', 'tahun'], true)) $periode = 'bulan';
        $tahun = max(2024, min((int) date('Y'), (int) ($_GET['tahun'] ?? date('Y'))));
        $bulan = str_pad((string) max(1, min(12, (int) ($_GET['bulan'] ?? date('m')))), 2, '0', STR_PAD_LEFT);
        $turnover = $crewModel->getTurnOverData($rigIds, $isAllRig, $periode, $tahun, $bulan);

        $activeCrewList = $crewModel->getActiveCrewDetailed($rigIds, $isAllRig);

        $this->view('laporan/index', [
            'title' => 'Laporan Turn Over Karyawan',
            'currentPage' => 'laporan',
            'jenis_laporan' => 'turnover',
            'turnover' => $turnover,
            'activeCrewList' => $activeCrewList,
            'chartData' => $crewModel->getTurnOverChart($rigIds, $isAllRig, $tahun, $periode, $bulan),
            'totalCrew' => $crewModel->countByRig($rigIds, $isAllRig),
            'periode' => $periode,
            'tahun' => $tahun,
            'bulan' => $bulan,
        ]);
        return;
    }

    $laporanModel = $this->model('LaporanModel');
    $rigModel = $this->model('RigModel');

    $filterType = ($_GET['type'] ?? 'expired') === 'warning' ? 'warning' : 'expired';
    $filterRig = trim($_GET['rig'] ?? '');
    $filterDokumen = trim($_GET['dokumen'] ?? '');
    $allowedDokumen = ['badge', 'mcu', 'sertifikat', 'pkwt'];
    if (!in_array($filterDokumen, $allowedDokumen, true)) $filterDokumen = '';

    if (!$isAllRig) $filterRig = ''; // Admin rig tidak dapat memilih rig di luar hak aksesnya.

    $result = $filterType === 'warning'
        ? $laporanModel->getWarning($rigIds, $isAllRig, $filterRig, $filterDokumen)
        : $laporanModel->getExpired($rigIds, $isAllRig, $filterRig, $filterDokumen);

    $this->view('laporan/index', [
        'title' => 'Laporan Dokumen Expired',
        'currentPage' => 'laporan',
        'jenis_laporan' => 'dokumen',
        'isSuperAdmin' => $this->isSuperAdmin(),
        'rigList' => $rigModel->allActive(),
        'filter_type' => $filterType,
        'filter_rig' => $filterRig,
        'filter_dokumen' => $filterDokumen,
        'grouped' => $this->groupDokumen($result->fetch_all(MYSQLI_ASSOC)),
    ]);
}

public function cetak() {
    $laporanModel = $this->model('LaporanModel');
    $filterType = ($_GET['type'] ?? 'expired') === 'warning' ? 'warning' : 'expired';
    $filterRig = trim($_GET['rig'] ?? '');
    $filterDokumen = trim($_GET['dokumen'] ?? '');
    $allowedDokumen = ['badge', 'mcu', 'sertifikat', 'pkwt'];
    if (!in_array($filterDokumen, $allowedDokumen, true)) $filterDokumen = '';

    $isAllRig = $this->isAdminAllRig();
    $rigIds = $this->getCurrentRigIds();
    if (!$isAllRig) $filterRig = '';
    $result = $filterType === 'warning'
        ? $laporanModel->getWarning($rigIds, $isAllRig, $filterRig, $filterDokumen)
        : $laporanModel->getExpired($rigIds, $isAllRig, $filterRig, $filterDokumen);
    $labels = ['badge' => 'Badge', 'mcu' => 'MCU', 'sertifikat' => 'Sertifikat', 'pkwt' => 'PKWT'];

    $this->view('laporan/cetak', [
        'title' => 'Cetak Laporan Dokumen',
        'filter_type' => $filterType,
        'filter_rig' => $filterRig,
        'filter_dokumen' => $filterDokumen,
        'filter_rig_label' => $filterRig ?: 'Semua Rig',
        'filter_dokumen_label' => $filterDokumen ? $labels[$filterDokumen] : 'Semua Dokumen',
        'grouped' => $this->groupDokumen($result->fetch_all(MYSQLI_ASSOC)),
    ]);
}

private function groupDokumen($rows) {
    $grouped = ['Badge' => [], 'MCU' => [], 'Sertifikat' => [], 'PKWT' => []];
    foreach ($rows as $row) {
        if (isset($grouped[$row['jenis_dok']])) $grouped[$row['jenis_dok']][] = $row;
    }
    return $grouped;
}

public function turnover() {
    $crewModel = $this->model('CrewModel');

    $isSuperAdmin = $this->isSuperAdmin();
    $rigIds = $this->getCurrentRigIds();
    $isAllRig = $this->isAdminAllRig();

    // filter periode
    $periode = $_GET['periode'] ?? 'bulan';
    $tahun = $_GET['tahun'] ?? date('Y');
    $bulan = $_GET['bulan'] ?? date('m');

    // Data turn over
    $dataTurnOver = $crewModel->getTurnOverData($rigIds, $isAllRig, $periode, $tahun, $bulan);
    
    // Data chart sesuai periode
    $chartData = $crewModel->getTurnOverChart($rigIds, $isAllRig, $tahun, $periode, $bulan);

    // total crew aktif
    $totalCrew = $crewModel->countByRig($rigIds, $isAllRig);
    $activeCrewList = $crewModel->getActiveCrewDetailed($rigIds, $isAllRig);

    $data = [
        'title' => 'Laporan Turn Over Karyawan',
        'currentPage' => 'laporan',
        'turnover' => $dataTurnOver,
        'activeCrewList' => $activeCrewList,
        'chartData' => $chartData,
        'totalCrew' => $totalCrew,
        'periode' => $periode,
        'tahun' => $tahun,
        'bulan' => $bulan,
    ];

    $this->view('laporan/turnover', $data);
}

    public function cetakTurnover() {
        $crewModel = $this->model('CrewModel');

        $rigIds = $this->getCurrentRigIds();
        $isAdminAllRig = $this->isAdminAllRig();

        // filter periode
        $periode = $_GET['periode'] ?? 'bulan';
        $tahun = $_GET['tahun'] ?? date('Y');
        $bulan = $_GET['bulan'] ?? date('m');

        // Data turn over
        $dataTurnOver = $crewModel->getTurnOverData($rigIds, $isAdminAllRig, $periode, $tahun, $bulan);

        // total crew aktif
        $totalCrew = $crewModel->countByRig($rigIds, $isAdminAllRig);
        $activeCrewList = $crewModel->getActiveCrewDetailed($rigIds, $isAdminAllRig);

        $data = [
            'title' => 'Cetak Laporan Turn Over Karyawan',
            'turnover' => $dataTurnOver,
            'activeCrewList' => $activeCrewList,
            'totalCrew' => $totalCrew,
            'periode' => $periode,
            'tahun' => $tahun,
            'bulan' => $bulan,
        ];

        $this->view('laporan/cetak-turnover', $data);
    }

    public function cetakKepatuhan() {
        $crewModel = $this->model('CrewModel');
        $badgeModel = $this->model('BadgeModel');
        $mcuModel = $this->model('McuModel');
        $sertifikatModel = $this->model('SertifikatModel');
        $pkwtModel = $this->model('PkwtModel');
        $rigModel = $this->model('RigModel');

        $rigIds = $this->getCurrentRigIds();
        $isAllRig = $this->isAdminAllRig();
        $isSuperAdmin = $this->isSuperAdmin();

        // Get all active crew with their document status
        $allCrew = $crewModel->getAllWithStatus($rigIds, $isAllRig);
        $crewList = $allCrew->fetch_all(MYSQLI_ASSOC);

        // Get rig list
        $rigs = $isSuperAdmin ? $rigModel->allActive() : $rigModel->getRigsByIds($rigIds);
        $rigList = $rigs->fetch_all(MYSQLI_ASSOC);

        // Summary stats
        $totalCrew = $crewModel->countByRig($rigIds, $isAllRig);
        $totalCrewAll = $crewModel->countAllWithStatus($rigIds, $isAllRig);
        
        $expiredBadge = $badgeModel->countExpired($rigIds, $isAllRig);
        $expiredMcu = $mcuModel->countExpired($rigIds, $isAllRig);
        $expiredSertifikat = $sertifikatModel->countExpired($rigIds, $isAllRig);
        $expiredPkwt = $pkwtModel->countExpired($rigIds, $isAllRig);
        $totalExpired = $expiredBadge + $expiredMcu + $expiredSertifikat + $expiredPkwt;

        $soonBadge = $badgeModel->countSoon($rigIds, $isAllRig);
        $soonMcu = $mcuModel->countSoon($rigIds, $isAllRig);
        $soonSertifikat = $sertifikatModel->countSoon($rigIds, $isAllRig);
        $soonPkwt = $pkwtModel->countSoon($rigIds, $isAllRig);
        $totalWarning = $soonBadge + $soonMcu + $soonSertifikat + $soonPkwt;

        $activeBadge = $badgeModel->countActive($rigIds, $isAllRig);
        $activeMcu = $mcuModel->countActive($rigIds, $isAllRig);
        $activeSertifikat = $sertifikatModel->countActive($rigIds, $isAllRig);
        $activePkwt = $pkwtModel->countActive($rigIds, $isAllRig);

        $complianceSummary = $crewModel->getComplianceSummary($rigIds, $isAllRig);
        $complyPercent = $totalCrew > 0 ? round(($complianceSummary['compliant'] / $totalCrew) * 100, 1) : 100;

        $this->view('laporan/cetak-kepatuhan', [
            'title' => 'Laporan Kepatuhan Crew',
            'crewList' => $crewList,
            'rigList' => $rigList,
            'totalCrew' => $totalCrew,
            'totalCrewAll' => $totalCrewAll,
            'totalExpired' => $totalExpired,
            'totalWarning' => $totalWarning,
            'expiredBadge' => $expiredBadge,
            'expiredMcu' => $expiredMcu,
            'expiredSertifikat' => $expiredSertifikat,
            'expiredPkwt' => $expiredPkwt,
            'soonBadge' => $soonBadge,
            'soonMcu' => $soonMcu,
            'soonSertifikat' => $soonSertifikat,
            'soonPkwt' => $soonPkwt,
            'activeBadge' => $activeBadge,
            'activeMcu' => $activeMcu,
            'activeSertifikat' => $activeSertifikat,
            'activePkwt' => $activePkwt,
            'complyPercent' => $complyPercent,
            'compliantCrew' => (int) $complianceSummary['compliant'],
            'warningCrew' => (int) $complianceSummary['warning'],
            'nonCompliantCrew' => (int) $complianceSummary['non_compliant'],
        ]);
    }

    public function cetakCrewAktif() {
        $crewModel = $this->model('CrewModel');
        $rigModel = $this->model('RigModel');

        $rigIds = $this->getCurrentRigIds();
        $isAllRig = $this->isAdminAllRig();
        $filterRig = trim($_GET['rig'] ?? '');
        if (!$isAllRig) $filterRig = '';

        $activeCrewList = $crewModel->getActiveCrewDetailed($rigIds, $isAllRig, $filterRig);
        $totalCrew = $activeCrewList ? $activeCrewList->num_rows : 0;

        $data = [
            'title' => 'Cetak Laporan Data Crew Aktif',
            'activeCrewList' => $activeCrewList,
            'totalCrew' => $totalCrew,
            'filter_rig' => $filterRig,
            'rigList' => $rigModel->allActive()
        ];

        $this->view('laporan/cetak-crew-aktif', $data);
    }
}
