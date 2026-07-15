<?php
class SertifikatController extends Controller
{

    public function __construct()
    {
        $this->requireLogin();
    }

    public function index()
    {
        $sertModel = $this->model('SertifikatModel');
        $rigModel = $this->model('RigModel');

        $isSuperAdmin = $this->isSuperAdmin();
        $isAllRig = $this->isAdminAllRig();
        $rigIds = $this->getCurrentRigIds();

        $filter_rig = $_GET['rig'] ?? '';
        $filter_status = $_GET['status'] ?? '';
        $filter_search = $_GET['search'] ?? '';

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 15;

        $total = $sertModel->countMonitoring($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search);
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $serts = $sertModel->getMonitoring($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search, $perPage, $offset);
        $rigList = $rigModel->allActive();

        $data = [
            'title' => 'Monitoring Sertifikat',
            'currentPage' => 'monitoring',
            'serts' => $serts,
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
        $this->view('sertifikat/index', $data);
    }

    public function create($id_crew)
    {
        $data = ['title' => 'Tambah Sertifikat', 'currentPage' => 'crew', 'id_crew' => $id_crew];
        $this->view('sertifikat/create', $data);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('crew');
        $sertModel = $this->model('SertifikatModel');
        $id_crew = intval($_POST['id_crew'] ?? 0);

        $fileName = null;
        if (!empty($_FILES['file']['name'])) {
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $fileName = 'sert_' . time() . '_' . $id_crew . '.' . $ext;
            move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . '/../../public/uploads/' . $fileName);
        }

        $sertModel->insert([
            'id_crew' => $id_crew,
            'jenis' => $_POST['jenis'] ?? '',
            'tanggal_expired' => $_POST['tanggal_expired'] ?? null,
            'file' => $fileName
        ]);
        Helper::setFlash('success', 'Sertifikat berhasil ditambahkan!');
        $this->redirect('crew/detail/' . $id_crew);
    }

    public function edit($id)
    {
        $sert = $this->model('SertifikatModel')->find($id);
        if (!$sert) $this->redirect('crew');
        $data = ['title' => 'Edit Sertifikat', 'currentPage' => 'crew', 'sert' => $sert];
        $this->view('sertifikat/edit', $data);
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('crew');

        $sertModel = $this->model('SertifikatModel');
        $sert = $sertModel->find($id);

        if (!$sert) {
            Helper::setFlash('error', 'Sertifikat tidak ditemukan!');
            $this->redirect('crew');
        }

        $fileName = $sert['file'];
        if (!empty($_FILES['file']['name'])) {
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $fileName = 'sert_' . time() . '_' . $sert['id_crew'] . '.' . $ext;
            move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . '/../../public/uploads/' . $fileName);
        }

        $updated = $sertModel->update($id, [
            'jenis' => $_POST['jenis'] ?? '',
            'tanggal_expired' => !empty($_POST['tanggal_expired']) ? $_POST['tanggal_expired'] : null,
            'file' => $fileName
        ]);

        if ($updated) {
            Helper::setFlash('success', 'Sertifikat berhasil diupdate!');
        } else {
            Helper::setFlash('error', 'Gagal mengupdate sertifikat!');
        }
        $this->redirect('crew/detail/' . $sert['id_crew']);
    }

    public function delete($id)
    {
        $sert = $this->model('SertifikatModel')->find($id);
        $id_crew = $sert['id_crew'] ?? 0;
        $this->model('SertifikatModel')->delete($id);
        Helper::setFlash('success', 'Sertifikat berhasil dihapus!');
        $this->redirect('crew/detail/' . $id_crew);
    }
}
