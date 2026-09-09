<?php
class PkwtController extends Controller {
    
    public function __construct() {
        $this->requireLogin();
    }
    
    public function index() {
        $pkwtModel = $this->model('PkwtModel');
        $rigModel = $this->model('RigModel');
        
        $isSuperAdmin = $this->isSuperAdmin();
        $isAllRig = $this->isAdminAllRig();
        $rigIds = $this->getCurrentRigIds();
        
        $filter_rig = $_GET['rig'] ?? '';
        $filter_status = $_GET['status'] ?? '';
        $filter_search = $_GET['search'] ?? '';
        
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 15;
        
        $total = $pkwtModel->countMonitoring($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search);
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $pkwts = $pkwtModel->getMonitoring($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search, $perPage, $offset);
        $rigList = $rigModel->allActive();
        
        $data = [
            'title' => 'Monitoring PKWT',
            'currentPage' => 'monitoring',
            'pkwts' => $pkwts,
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
        $this->view('pkwt/index', $data);
    }
    
    public function create($id_crew) {
        $data = ['title' => 'Tambah PKWT', 'currentPage' => 'crew', 'id_crew' => $id_crew];
        $this->view('pkwt/create', $data);
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('crew');
        $pkwtModel = $this->model('PkwtModel');
        $id_crew = intval($_POST['id_crew'] ?? 0);
        
        $fileName = null;
        if (!empty($_FILES['file']['name'])) {
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $fileName = 'pkwt_' . time() . '_' . $id_crew . '.' . $ext;
            move_uploaded_file($_FILES['file']['tmp_name'], dirname(__DIR__, 2) . '/public/uploads/' . $fileName);
        }
        
        $pkwtModel->insert([
            'id_crew' => $id_crew,
            'tanggal_mulai' => !empty($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : null,
            'tanggal_berakhir' => $_POST['tanggal_berakhir'] ?? null,
            'file' => $fileName
        ]);
        Helper::setFlash('success', 'PKWT berhasil ditambahkan!');
        $this->redirect('crew/detail/' . $id_crew);
    }
    
    public function edit($id) {
        $pkwt = $this->model('PkwtModel')->find($id);
        if (!$pkwt) $this->redirect('crew');
        $data = ['title' => 'Edit PKWT', 'currentPage' => 'crew', 'pkwt' => $pkwt];
        $this->view('pkwt/edit', $data);
    }
    
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('crew');
        $pkwtModel = $this->model('PkwtModel');
        $pkwt = $pkwtModel->find($id);
        
        $fileName = $pkwt['file'] ?? null;
        if (!empty($_FILES['file']['name'])) {
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $fileName = 'pkwt_' . time() . '_' . $pkwt['id_crew'] . '.' . $ext;
            move_uploaded_file($_FILES['file']['tmp_name'], dirname(__DIR__, 2) . '/public/uploads/' . $fileName);
        }
        
        $pkwtModel->update($id, [
            'tanggal_mulai' => !empty($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : null,
            'tanggal_berakhir' => $_POST['tanggal_berakhir'] ?? null,
            'file' => $fileName
        ]);
        Helper::setFlash('success', 'PKWT berhasil diupdate!');
        $this->redirect('crew/detail/' . $pkwt['id_crew']);
    }
    
    public function delete($id) {
        $pkwt = $this->model('PkwtModel')->find($id);
        $id_crew = $pkwt['id_crew'] ?? 0;
        $this->model('PkwtModel')->delete($id);
        Helper::setFlash('success', 'PKWT berhasil dihapus!');
        $this->redirect('crew/detail/' . $id_crew);
    }
}