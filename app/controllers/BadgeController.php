<?php
class BadgeController extends Controller {
    
    public function __construct() {
        $this->requireLogin();
    }
    
    // monintorin badge
    public function index() {
        $badgeModel = $this->model('BadgeModel');
        $rigModel = $this->model('RigModel');
        
        $isSuperAdmin = $this->isSuperAdmin();
        $isAllRig = $this->isAdminAllRig();
        $rigIds = $this->getCurrentRigIds();
        
        $filter_rig = $_GET['rig'] ?? '';
        $filter_status = $_GET['status'] ?? '';
        $filter_search = $_GET['search'] ?? '';
        
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 15;
        
        $totalBadges = $badgeModel->countMonitoring($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search);
        $totalPages = ceil($totalBadges / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $badges = $badgeModel->getMonitoring($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search, $perPage, $offset);
        $rigList = $rigModel->allActive();
        
        $data = [
            'title' => 'Monitoring Badge',
            'currentPage' => 'monitoring',
            'badges' => $badges,
            'rigList' => $rigList,
            'isSuperAdmin' => $isSuperAdmin,
            'filter_rig' => $filter_rig,
            'filter_status' => $filter_status,
            'filter_search' => $filter_search,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalBadges' => $totalBadges,
            'offset' => $offset
        ];
        $this->view('badge/index', $data);
    }
    
    // add
    public function create($id_crew) {
    $badgeModel = $this->model('BadgeModel');
    $lastBadge = $badgeModel->getLastBadge($id_crew);
    
    $data = [
        'title' => 'Perpanjang Badge',
        'currentPage' => 'crew',
        'id_crew' => $id_crew,
        'nomor_badge_lama' => $lastBadge['nomor_badge'] ?? ''
    ];
    $this->view('badge/create', $data);
}
    
    // save
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('crew');
        $badgeModel = $this->model('BadgeModel');
        $id_crew = intval($_POST['id_crew'] ?? 0);
        
        $fileName = null;
        if (!empty($_FILES['file']['name'])) {
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $fileName = 'badge_' . time() . '_' . $id_crew . '.' . $ext;
            move_uploaded_file($_FILES['file']['tmp_name'], 'public/uploads/' . $fileName);
        }
        
        $badgeModel->insert([
            'id_crew' => $id_crew, 'nomor_badge' => $_POST['nomor_badge'] ?? '',
            'tanggal_expired' => $_POST['tanggal_expired'] ?? null, 'file' => $fileName
        ]);
        Helper::setFlash('success', 'Badge berhasil ditambahkan!');
        $this->redirect('crew/detail/' . $id_crew);
    }
    
    // edit
    public function edit($id) {
        $badge = $this->model('BadgeModel')->find($id);
        if (!$badge) $this->redirect('crew');
        $data = ['title' => 'Edit Badge', 'currentPage' => 'crew', 'badge' => $badge];
        $this->view('badge/edit', $data);
    }
    
    // update
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('crew');
        $badgeModel = $this->model('BadgeModel');
        $badge = $badgeModel->find($id);
        
        $fileName = $badge['file'] ?? null;
        if (!empty($_FILES['file']['name'])) {
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $fileName = 'badge_' . time() . '_' . $badge['id_crew'] . '.' . $ext;
            move_uploaded_file($_FILES['file']['tmp_name'], 'public/uploads/' . $fileName);
        }
        
        $badgeModel->update($id, [
            'nomor_badge' => $_POST['nomor_badge'] ?? '',
            'tanggal_expired' => $_POST['tanggal_expired'] ?? null, 'file' => $fileName
        ]);
        Helper::setFlash('success', 'Badge berhasil diupdate!');
        $this->redirect('crew/detail/' . $badge['id_crew']);
    }
    
    // delete
    public function delete($id) {
        $badge = $this->model('BadgeModel')->find($id);
        $id_crew = $badge['id_crew'] ?? 0;
        $this->model('BadgeModel')->delete($id);
        Helper::setFlash('success', 'Badge berhasil dihapus!');
        $this->redirect('crew/detail/' . $id_crew);
    }

    
}