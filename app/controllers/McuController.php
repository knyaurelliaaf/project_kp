<?php
class McuController extends Controller {
    
    public function __construct() {
        $this->requireLogin();
    }
    
    public function index() {
        $mcuModel = $this->model('McuModel');
        $rigModel = $this->model('RigModel');
        
        $isSuperAdmin = $this->isSuperAdmin();
        $isAllRig = $this->isAdminAllRig();
        $rigIds = $this->getCurrentRigIds();
        
        $filter_rig = $_GET['rig'] ?? '';
        $filter_status = $_GET['status'] ?? '';
        $filter_search = $_GET['search'] ?? '';
        
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 15;
        
        $total = $mcuModel->countMonitoring($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search);
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $mcus = $mcuModel->getMonitoring($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search, $perPage, $offset);
        $rigList = $rigModel->allActive();
        
        $data = [
            'title' => 'Monitoring MCU',
            'currentPage' => 'monitoring',
            'mcus' => $mcus,
            'rigList' => $rigList,
            'isSuperAdmin' => $isSuperAdmin,
            'filter_rig' => $filter_rig,
            'filter_status' => $filter_status,
            'filter_search' => $filter_search,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'offset' => $offset
        ];
        $this->view('mcu/index', $data);
    }
    
    public function create($id_crew) {
        $data = ['title' => 'Tambah MCU', 'currentPage' => 'crew', 'id_crew' => $id_crew];
        $this->view('mcu/create', $data);
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('crew');
        $mcuModel = $this->model('McuModel');
        $id_crew = intval($_POST['id_crew'] ?? 0);
        
        $fileName = null;
        if (!empty($_FILES['file']['name'])) {
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $fileName = 'mcu_' . time() . '_' . $id_crew . '.' . $ext;
            move_uploaded_file($_FILES['file']['tmp_name'], 'public/uploads/' . $fileName);
        }
        
        $mcuModel->insert([
        'id_crew' => $id_crew,
        'expired' => !empty($_POST['expired']) ? $_POST['expired'] : null,
        'derajat_kesehatan' => $_POST['derajat_kesehatan'] ?? null,
        'file' => $fileName
    ]);
       
        Helper::setFlash('success', 'MCU berhasil ditambahkan!');
        $this->redirect('crew/detail/' . $id_crew);
    }
    
    public function edit($id) {
        $mcu = $this->model('McuModel')->find($id);
        if (!$mcu) $this->redirect('crew');
        $data = ['title' => 'Edit MCU', 'currentPage' => 'crew', 'mcu' => $mcu];
        $this->view('mcu/edit', $data);
    }
    
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('crew');
        $mcuModel = $this->model('McuModel');
        $mcu = $mcuModel->find($id);
        
        $fileName = $mcu['file'] ?? null;
        if (!empty($_FILES['file']['name'])) {
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $fileName = 'mcu_' . time() . '_' . $mcu['id_crew'] . '.' . $ext;
            move_uploaded_file($_FILES['file']['tmp_name'], 'public/uploads/' . $fileName);
        }
        
        $mcuModel->update($id, [
        'expired' => !empty($_POST['expired']) ? $_POST['expired'] : null,
        'derajat_kesehatan' => $_POST['derajat_kesehatan'] ?? null,
        'file' => $fileName
    ]);
        Helper::setFlash('success', 'MCU berhasil diupdate!');
        $this->redirect('crew/detail/' . $mcu['id_crew']);
    }
    
    public function delete($id) {
        $mcu = $this->model('McuModel')->find($id);
        $id_crew = $mcu['id_crew'] ?? 0;
        $this->model('McuModel')->delete($id);
        Helper::setFlash('success', 'MCU berhasil dihapus!');
        $this->redirect('crew/detail/' . $id_crew);
    }
}