<?php
class CrewController extends Controller
{

    public function __construct()
    {
        $this->requireLogin();
    }

    // list creww
    public function index()
    {
        $crewModel = $this->model('CrewModel');
        $rigModel = $this->model('RigModel');

        $isSuperAdmin = $this->isSuperAdmin();
        $isAllRig = $this->isAdminAllRig();
        $rigIds = $this->getCurrentRigIds();

        $filter_rig = $_GET['rig'] ?? '';
        $filter_search = $_GET['search'] ?? '';
        $filter_status = $_GET['status'] ?? '';
        $filter_crew = $_GET['crew'] ?? '';

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 10;

        $sort = $_GET['sort'] ?? 'nama';
        $order = $_GET['order'] ?? 'ASC';

        $totalCrew = $crewModel->countFiltered($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search, $filter_crew);
        $totalPages = ceil($totalCrew / $perPage);
        $offset = ($page - 1) * $perPage;

        $crew = $crewModel->getFiltered($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search, $filter_crew, $perPage, $offset, $sort, $order);
        $rigList = $rigModel->allActive();

        $data = [
            'title' => 'Data Crew',
            'currentPage' => 'crew',
            'crew' => $crew,
            'rigList' => $rigList,
            'isSuperAdmin' => $isSuperAdmin,
            'filter_rig' => $filter_rig,
            'filter_search' => $filter_search,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalCrew' => $totalCrew,
            'offset' => $offset,
            'sort' => $sort,
            'order' => $order,
            'filter_status' => $filter_status,
            'filter_crew' => $filter_crew
        ];

        $this->view('crew/index', $data);
    }

    // add
    public function create()
    {
        $rigModel = $this->model('RigModel');
        $data = [
            'title' => 'Tambah Crew',
            'currentPage' => 'crew',
            'rigs' => $rigModel->allActive(),
            'isSuperAdmin' => $this->isSuperAdmin(),
            'rigIds' => $this->getCurrentRigIds(),
            'kode_rig' => $_SESSION['user']['kode_rig'] ?? ''
        ];
        $this->view('crew/create', $data);
    }

    // savee
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('crew');

        $crewModel = $this->model('CrewModel');
        $id_rig = $this->isSuperAdmin() ? intval($_POST['id_rig'] ?? 0) : ($this->getCurrentRigIds()[0] ?? 1);
        $nama = trim($_POST['nama'] ?? '');
        $posisi = trim($_POST['posisi'] ?? '');

        if (!empty($nama)) {
            $crewModel->insert([
                'id_rig' => $id_rig,
                'nama' => $nama,
                'posisi' => $posisi,
                'crew' => trim($_POST['crew'] ?? '')
            ]);
            Helper::setFlash('success', 'Crew berhasil ditambahkan!');
        }
        $this->redirect('crew');
    }


    // edit
    public function edit($id)
    {
        $crewModel = $this->model('CrewModel');
        $rigModel = $this->model('RigModel');
        $crew = $crewModel->find($id);
        if (!$crew) $this->redirect('crew');

        $data = [
            'title' => 'Edit Crew',
            'currentPage' => 'crew',
            'crew' => $crew,
            'rigs' => $rigModel->allActive(),
            'isSuperAdmin' => $this->isSuperAdmin()
        ];
        $this->view('crew/edit', $data);
    }

    // update
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('crew');

        $crewModel = $this->model('CrewModel');
        $data = [
            'nama' => trim($_POST['nama'] ?? ''),
            'posisi' => trim($_POST['posisi'] ?? ''),
            'alamat' => trim($_POST['alamat'] ?? ''),
            'nik_ktp' => trim($_POST['nik_ktp'] ?? ''),
            'no_telp' => trim($_POST['no_telp'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'crew' => trim($_POST['crew'] ?? ''),
            'status_aktif' => $_POST['status_aktif'] ?? 'aktif'
        ];

        // Upload foto
        if (!empty($_FILES['foto']['name'])) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto = 'crew_' . $id . '_' . time() . '.' . $ext;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/../../public/uploads/' . $foto)) {
                $data['foto'] = $foto;
            } else {
                die("GAGAL UPLOAD. Path: " . __DIR__ . '/../../public/uploads/' . $foto);
            }
        }

        if ($this->isSuperAdmin() && !empty($_POST['id_rig'])) {
            $data['id_rig'] = intval($_POST['id_rig']);
        }
        $crewModel->update($id, $data);
        Helper::setFlash('success', 'Crew berhasil diupdate!');
        $this->redirect('crew/detail/' . $id);
    }

    // toggle status aktif/nonaktif
    public function toggleStatus($id)
    {
        $crewModel = $this->model('CrewModel');
        $crew = $crewModel->find($id);

        if ($crew) {
            $newStatus = ($crew['status_aktif'] == 'aktif') ? 'nonaktif' : 'aktif';
            $crewModel->update($id, ['status_aktif' => $newStatus]);
            $msg = ($newStatus == 'nonaktif') ? 'dinonaktifkan' : 'diaktifkan';
            Helper::setFlash('success', 'Crew berhasil ' . $msg . '!');
        }

        $this->redirect('crew');
    }

    // detail crew
    public function detail($id)
    {
        $crewModel = $this->model('CrewModel');
        $badgeModel = $this->model('BadgeModel');
        $mcuModel = $this->model('McuModel');
        $sertifikatModel = $this->model('SertifikatModel');
        $pkwtModel = $this->model('PkwtModel');

        $crew = $crewModel->getDetail($id);
        if (!$crew) $this->redirect('crew');

        $data = [
            'title' => 'Detail Crew',
            'currentPage' => 'crew',
            'crew' => $crew,
            'badges' => $badgeModel->getByCrew($id, 'tanggal_expired', 'ASC'),
            'mcus' => $mcuModel->getByCrew($id, 'expired', 'ASC'),
            'sertifikats' => $sertifikatModel->getByCrew($id, 'tanggal_expired', 'ASC'),
            'pkwts' => $pkwtModel->getByCrew($id, 'tanggal_berakhir', 'ASC')
        ];
        $this->view('crew/detail', $data);
    }
}
