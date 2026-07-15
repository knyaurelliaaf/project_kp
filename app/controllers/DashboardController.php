<?php
class DashboardController extends Controller {
    
    public function __construct() {
        $this->requireLogin();
    }
    
        public function index() {
        $crewModel = $this->model('CrewModel');
        $suratModel = $this->model('SuratModel');
        $badgeModel = $this->model('BadgeModel');
        $mcuModel = $this->model('McuModel');
        $sertifikatModel = $this->model('SertifikatModel');
        $pkwtModel = $this->model('PkwtModel');
        
        $isSuperAdmin = $this->isSuperAdmin();
        $isAllRig = $this->isAdminAllRig();
        $rigIds = $this->getCurrentRigIds();
        
        // Total
        $totalCrew = $crewModel->countByRig($rigIds, $isAllRig);
        $totalSurat = $suratModel->countByRig($rigIds, $isAllRig);
        
        // Expired (record terbaru)
        $expiredBadge = $badgeModel->countExpired($rigIds, $isAllRig);
        $expiredMcu = $mcuModel->countExpired($rigIds, $isAllRig);
        $expiredSertifikat = $sertifikatModel->countExpired($rigIds, $isAllRig);
        $expiredPkwt = $pkwtModel->countExpired($rigIds, $isAllRig);
        $totalExpired = $expiredBadge + $expiredMcu + $expiredSertifikat + $expiredPkwt;
        
        // Soon (record terbaru)
        $soonBadge = $badgeModel->countSoon($rigIds, $isAllRig);
        $soonMcu = $mcuModel->countSoon($rigIds, $isAllRig);
        $soonSertifikat = $sertifikatModel->countSoon($rigIds, $isAllRig);
        $soonPkwt = $pkwtModel->countSoon($rigIds, $isAllRig);
        $totalSoon = $soonBadge + $soonMcu + $soonSertifikat + $soonPkwt;
        
        // Active (record terbaru)
        $activeBadge = $badgeModel->countActive($rigIds, $isAllRig);
        $activeMcu = $mcuModel->countActive($rigIds, $isAllRig);
        $activeSertifikat = $sertifikatModel->countActive($rigIds, $isAllRig);
        $activePkwt = $pkwtModel->countActive($rigIds, $isAllRig);
        
        // Compliance percentage per kategori
        $badgeTotal = $totalCrew > 0 ? round(($activeBadge / $totalCrew) * 100) : 100;
        $mcuTotal = $totalCrew > 0 ? round(($activeMcu / $totalCrew) * 100) : 100;
        $sertTotal = $totalCrew > 0 ? round(($activeSertifikat / $totalCrew) * 100) : 100;
        $pkwtTotalPercent = $totalCrew > 0 ? round(($activePkwt / $totalCrew) * 100) : 100;
        
        // Overall compliance
        $complyPercent = $totalCrew > 0 ? round(($totalCrew - $totalExpired) / $totalCrew * 100, 1) : 100;
        $warningPercent = $totalCrew > 0 ? round($totalSoon / $totalCrew * 100, 1) : 0;
        $nonComplyPercent = $totalCrew > 0 ? round($totalExpired / $totalCrew * 100, 1) : 0;
        
        // Crew expired list
        $crewExpired = $crewModel->getExpiredCrew($rigIds, $isAllRig);
        
        // Rig stats
        $rigStats = [];
        if ($isSuperAdmin) {
            $rigModel = $this->model('RigModel');
            $rigs = $rigModel->allActive();
           while ($rig = $rigs->fetch_assoc()) {
            $rId = [$rig['id_rig']];
            $rigStats[] = [
                'kode_rig' => $rig['kode_rig'],
                'total_crew' => $crewModel->countByRig($rId, false),
                'expired' => $badgeModel->countExpired($rId, false) + $mcuModel->countExpired($rId, false) + $sertifikatModel->countExpired($rId, false) + $pkwtModel->countExpired($rId, false),
                'warning' => $badgeModel->countSoon($rId, false) + $mcuModel->countSoon($rId, false) + $sertifikatModel->countSoon($rId, false) + $pkwtModel->countSoon($rId, false),
                'badge_expired' => $badgeModel->countExpired($rId, false),
                'badge_warning' => $badgeModel->countSoon($rId, false),
                'badge_valid'   => $badgeModel->countActive($rId, false),
                'mcu_expired'   => $mcuModel->countExpired($rId, false),
                'mcu_warning'   => $mcuModel->countSoon($rId, false),
                'mcu_valid'     => $mcuModel->countActive($rId, false),
                'sert_expired'  => $sertifikatModel->countExpired($rId, false),
                'sert_warning'  => $sertifikatModel->countSoon($rId, false),
                'sert_valid'    => $sertifikatModel->countActive($rId, false),
            ];
        }
        }
        
        // Pagination
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 10;
        $totalCrewAll = $crewModel->countAllWithStatus($rigIds, $isAllRig);
        $totalPages = ceil($totalCrewAll / $perPage);
        $offset = ($page - 1) * $perPage;
        $allCrew = $crewModel->getAllWithStatusPaginated($rigIds, $isAllRig, $perPage, $offset);
        
        $data = [
            'title' => 'Dashboard',
            'currentPage' => 'dashboard',
            'totalCrew' => $totalCrew,
            'totalSurat' => $totalSurat,
            'expiredBadge' => $expiredBadge,
            'expiredMcu' => $expiredMcu,
            'expiredSertifikat' => $expiredSertifikat,
            'expiredPkwt' => $expiredPkwt,
            'totalExpired' => $totalExpired,
            'totalWarning' => $totalSoon,
            'badgeTotal' => $badgeTotal,
            'mcuTotal' => $mcuTotal,
            'sertTotal' => $sertTotal,
            'pkwtTotal' => $pkwtTotalPercent,
            'complyPercent' => $complyPercent,
            'warningPercent' => $warningPercent,
            'nonComplyPercent' => $nonComplyPercent,
            'crewExpired' => $crewExpired,
            'rigStats' => $rigStats,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalCrewAll' => $totalCrewAll,
            'offset' => $offset,
            'allCrew' => $allCrew
        ];
        
        $this->view('dashboard/index', $data);
    }
}

