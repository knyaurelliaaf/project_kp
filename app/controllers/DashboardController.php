<?php
class DashboardController extends Controller {
    
    public function __construct() {
        $this->requireLogin();
        $role = $_SESSION['user']['role'] ?? '';
        if ($role === 'payroll') {
            $this->redirect('payroll');
        } elseif (in_array($role, ['admin_gaji', 'payroll_wa'])) {
            $this->redirect('admin_gaji');
        }
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
        
        // Total Active Crew
        $totalCrew = $crewModel->countByRig($rigIds, $isAllRig);
        
        // Total Surat
        $totalSurat = $suratModel->countByRig($rigIds, $isAllRig);
        
        // Expired documents count (sum of all expired documents)
        $expiredBadge = $badgeModel->countExpired($rigIds, $isAllRig);
        $expiredMcu = $mcuModel->countExpired($rigIds, $isAllRig);
        $expiredSertifikat = $sertifikatModel->countExpired($rigIds, $isAllRig);
        $expiredPkwt = $pkwtModel->countExpired($rigIds, $isAllRig);
        $totalExpired = $expiredBadge + $expiredMcu + $expiredSertifikat + $expiredPkwt;
        
        // Soon documents count (sum of all warning documents)
        $soonBadge = $badgeModel->countSoon($rigIds, $isAllRig);
        $soonMcu = $mcuModel->countSoon($rigIds, $isAllRig);
        $soonSertifikat = $sertifikatModel->countSoon($rigIds, $isAllRig);
        $soonPkwt = $pkwtModel->countSoon($rigIds, $isAllRig);
        $totalWarning = $soonBadge + $soonMcu + $soonSertifikat + $soonPkwt;
        
        // Active document counts per category (for ring cards)
        $activeBadge = $badgeModel->countActive($rigIds, $isAllRig);
        $activeMcu = $mcuModel->countActive($rigIds, $isAllRig);
        $activeSertifikat = $sertifikatModel->countActive($rigIds, $isAllRig);
        $activePkwt = $pkwtModel->countActive($rigIds, $isAllRig);
        
        // Compliance percentage per kategori (based on total active crew)
        $badgeTotal = $totalCrew > 0 ? round(($activeBadge / $totalCrew) * 100) : 100;
        $mcuTotal = $totalCrew > 0 ? round(($activeMcu / $totalCrew) * 100) : 100;
        $sertTotal = $totalCrew > 0 ? round(($activeSertifikat / $totalCrew) * 100) : 100;
        $pkwtTotalPercent = $totalCrew > 0 ? round(($activePkwt / $totalCrew) * 100) : 100;
        
        // Overall compliance dihitung per crew
        $complianceSummary = $crewModel->getComplianceSummary($rigIds, $isAllRig);
        $complyPercent = $totalCrew > 0 ? round(($complianceSummary['compliant'] / $totalCrew) * 100, 1) : 100;
        $warningPercent = $totalCrew > 0 ? round(($complianceSummary['warning'] / $totalCrew) * 100, 1) : 0;
        $nonComplyPercent = $totalCrew > 0 ? round(($complianceSummary['non_compliant'] / $totalCrew) * 100, 1) : 0;
        $compliantCrew = (int) $complianceSummary['compliant'];
        
        // Crew expired list
        $crewExpired = $crewModel->getExpiredCrew($rigIds, $isAllRig);
        
        // Rig stats
        $rigStats = [];
        if ($isSuperAdmin) {
            $rigModel = $this->model('RigModel');
            $rigs = $rigModel->allActive();
            foreach ($rigs as $rig) {
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
                    'pkwt_expired'  => $pkwtModel->countExpired($rId, false),
                    'pkwt_warning'  => $pkwtModel->countSoon($rId, false),
                    'pkwt_valid'    => $pkwtModel->countActive($rId, false),
                ];
            }
        }
        
        // If not superadmin, build rigStats for accessible rigs only
        if (!$isSuperAdmin && !empty($rigIds)) {
            $rigModel = $this->model('RigModel');
            $rigs = $rigModel->getRigsByIds($rigIds);
            foreach ($rigs as $rig) {
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
                    'pkwt_expired'  => $pkwtModel->countExpired($rId, false),
                    'pkwt_warning'  => $pkwtModel->countSoon($rId, false),
                    'pkwt_valid'    => $pkwtModel->countActive($rId, false),
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
        
        // Full document list for dashboard alert cards
        $laporanModel = $this->model('LaporanModel');
        $listExpiredDocs = $laporanModel->getExpired($rigIds, $isAllRig)->fetch_all(MYSQLI_ASSOC);
        $listWarningDocs = $laporanModel->getWarning($rigIds, $isAllRig)->fetch_all(MYSQLI_ASSOC);
        
        $data = [
            'title' => 'Dashboard',
            'currentPage' => 'dashboard',
            'totalCrew' => $totalCrew,
            'totalCrewAll' => $totalCrewAll,
            'totalSurat' => $totalSurat,
            'totalExpired' => count($listExpiredDocs),
            'totalWarning' => count($listWarningDocs),
            'badgeTotal' => $badgeTotal,
            'mcuTotal' => $mcuTotal,
            'sertTotal' => $sertTotal,
            'pkwtTotal' => $pkwtTotalPercent,
            'complyPercent' => $complyPercent,
            'warningPercent' => $warningPercent,
            'nonComplyPercent' => $nonComplyPercent,
            'compliantCrew' => $compliantCrew,
            'crewExpired' => $crewExpired,
            'listExpiredDocs' => $listExpiredDocs,
            'listWarningDocs' => $listWarningDocs,
            'rigStats' => $rigStats,
            'page' => $page,
            'totalPages' => $totalPages,
            'offset' => $offset,
            'allCrew' => $allCrew
        ];
        
        $this->view('dashboard/index', $data);
    }
}

