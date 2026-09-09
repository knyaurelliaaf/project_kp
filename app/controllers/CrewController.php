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

        // Manual reset filter
        if (isset($_GET['reset'])) {
            unset($_SESSION['crew_filters']);
            $this->redirect('crew');
        }

        // Restore filters from session if GET has no filter parameters
        if (!isset($_GET['rig']) && !isset($_GET['search']) && !isset($_GET['status']) && !isset($_GET['crew']) && !isset($_GET['page']) && !empty($_SESSION['crew_filters'])) {
            $filter_rig = $_SESSION['crew_filters']['rig'] ?? '';
            $filter_search = $_SESSION['crew_filters']['search'] ?? '';
            $filter_status = $_SESSION['crew_filters']['status'] ?? '';
            $filter_crew = $_SESSION['crew_filters']['crew'] ?? '';
            $page = $_SESSION['crew_filters']['page'] ?? 1;
            $sort = $_SESSION['crew_filters']['sort'] ?? 'nama';
            $order = $_SESSION['crew_filters']['order'] ?? 'ASC';
        } else {
            $filter_rig = $_GET['rig'] ?? '';
            $filter_search = $_GET['search'] ?? '';
            $filter_status = $_GET['status'] ?? '';
            $filter_crew = $_GET['crew'] ?? '';
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $sort = $_GET['sort'] ?? 'nama';
            $order = $_GET['order'] ?? 'ASC';

            $_SESSION['crew_filters'] = [
                'rig' => $filter_rig,
                'status' => $filter_status,
                'crew' => $filter_crew,
                'search' => $filter_search,
                'sort' => $sort,
                'order' => $order,
                'page' => $page
            ];
        }

        $perPage = 10;
        $totalCrew = $crewModel->countFiltered($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search, $filter_crew);
        $totalPages = max(1, ceil($totalCrew / $perPage));
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        $crew = $crewModel->getFiltered($rigIds, $isAllRig, $filter_rig, $filter_status, $filter_search, $filter_crew, $perPage, $offset, $sort, $order);
        $rigList = $rigModel->allActive();

        $filterParams = array_filter([
            'rig' => $filter_rig,
            'status' => $filter_status,
            'crew' => $filter_crew,
            'search' => $filter_search,
            'sort' => $sort !== 'nama' ? $sort : null,
            'order' => $order !== 'ASC' ? $order : null,
            'page' => $page > 1 ? $page : null
        ]);
        $filterQuery = http_build_query($filterParams);

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
            'filter_crew' => $filter_crew,
            'filterQuery' => $filterQuery
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
        $regu = trim($_POST['regu'] ?? ($_POST['crew'] ?? ''));
        $tanggal_masuk = !empty($_POST['tanggal_masuk']) ? $_POST['tanggal_masuk'] : date('Y-m-d');

        if (!empty($nama)) {
            $crewModel->insert([
                'id_rig' => $id_rig,
                'nama' => $nama,
                'posisi' => $posisi,
                'crew' => $regu,
                'nik_ktp' => trim($_POST['nik_ktp'] ?? ''),
                'no_telp' => trim($_POST['no_telp'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'alamat' => trim($_POST['alamat'] ?? ''),
                'tempat_lahir' => trim($_POST['tempat_lahir'] ?? ''),
                'tanggal_lahir' => !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null,
                'tanggal_masuk' => $tanggal_masuk,
                'bank' => trim($_POST['bank'] ?? ''),
                'no_rekening' => trim($_POST['no_rekening'] ?? ''),
                'ptkp' => trim($_POST['ptkp'] ?? ''),
                'status_aktif' => 'aktif'
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

        $queryParams = $_GET;
        unset($queryParams['url']);
        if (empty($queryParams) && !empty($_SESSION['crew_filters'])) {
            $queryParams = array_filter($_SESSION['crew_filters']);
        }
        $filterQuery = !empty($queryParams) ? http_build_query(array_filter($queryParams)) : '';

        $data = [
            'title' => 'Edit Crew',
            'currentPage' => 'crew',
            'crew' => $crew,
            'rigs' => $rigModel->allActive(),
            'isSuperAdmin' => $this->isSuperAdmin(),
            'filterQuery' => $filterQuery
        ];
        $this->view('crew/edit', $data);
    }

    // update
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('crew');

        $crewModel = $this->model('CrewModel');
        $status_aktif = $_POST['status_aktif'] ?? 'aktif';

        $data = [
            'nama' => trim($_POST['nama'] ?? ''),
            'posisi' => trim($_POST['posisi'] ?? ''),
            'alamat' => trim($_POST['alamat'] ?? ''),
            'nik_ktp' => trim($_POST['nik_ktp'] ?? ''),
            'no_telp' => trim($_POST['no_telp'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'crew' => trim($_POST['crew'] ?? ''),
            'tempat_lahir' => trim($_POST['tempat_lahir'] ?? ''),
            'tanggal_lahir' => !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null,
            'tanggal_masuk' => !empty($_POST['tanggal_masuk']) ? $_POST['tanggal_masuk'] : null,
            'tanggal_nonaktif' => ($status_aktif === 'nonaktif') ? (!empty($_POST['tanggal_nonaktif']) ? $_POST['tanggal_nonaktif'] : date('Y-m-d')) : null,
            'bank' => trim($_POST['bank'] ?? ''),
            'no_rekening' => trim($_POST['no_rekening'] ?? ''),
            'ptkp' => trim($_POST['ptkp'] ?? ''),
            'status_aktif' => $status_aktif
        ];

        // Upload foto
        if (!empty($_FILES['foto']['name'])) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto = 'crew_' . $id . '_' . time() . '.' . $ext;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], dirname(__DIR__, 2) . '/public/uploads/' . $foto)) {
                $data['foto'] = $foto;
            } else {
                die("GAGAL UPLOAD. Path: " . dirname(__DIR__, 2) . '/public/uploads/' . $foto);
            }
        }

        if ($this->isSuperAdmin() && !empty($_POST['id_rig'])) {
            $data['id_rig'] = intval($_POST['id_rig']);
        }
        $crewModel->update($id, $data);
        Helper::setFlash('success', 'Crew berhasil diupdate!');

        $queryParams = $_GET;
        unset($queryParams['url']);
        if (empty($queryParams) && !empty($_SESSION['crew_filters'])) {
            $queryParams = array_filter($_SESSION['crew_filters']);
        }
        $filterQuery = !empty($queryParams) ? '?' . http_build_query(array_filter($queryParams)) : '';

        $this->redirect('crew/detail/' . $id . $filterQuery);
    }

    // toggle status aktif/nonaktif
    public function toggleStatus($id)
    {
        $crewModel = $this->model('CrewModel');
        $crew = $crewModel->find($id);

        if ($crew) {
            $newStatus = ($crew['status_aktif'] == 'aktif') ? 'nonaktif' : 'aktif';
            $data = ['status_aktif' => $newStatus];

            // Catat tanggal nonaktif secara otomatis saat dinonaktifkan
            if ($newStatus == 'nonaktif') {
                $data['tanggal_nonaktif'] = !empty($_POST['tanggal_nonaktif']) ? $_POST['tanggal_nonaktif'] : date('Y-m-d');
            } else {
                $data['tanggal_nonaktif'] = null;
            }

            $crewModel->update($id, $data);
            $msg = ($newStatus == 'nonaktif') ? 'dinonaktifkan' : 'diaktifkan';
            Helper::setFlash('success', 'Crew berhasil ' . $msg . '!');
        }

        $filterQuery = !empty($_SESSION['crew_filters']) ? '?' . http_build_query(array_filter($_SESSION['crew_filters'])) : '';
        $this->redirect('crew' . $filterQuery);
    }

    // detail crew
    public function cetakExpired()
    {
        $crewModel = $this->model('CrewModel');
        $isAllRig = $this->isAdminAllRig();
        $rigIds = $this->getCurrentRigIds();

        // Filter dokumen yang dipilih (default: semua)
        $filterDokumen = $_GET['filter'] ?? ['badge', 'mcu', 'sertifikat', 'pkwt'];
        if (!is_array($filterDokumen)) {
            $filterDokumen = ['badge', 'mcu', 'sertifikat', 'pkwt'];
        }

        $result = $crewModel->getExpiredCrew($rigIds, $isAllRig, $filterDokumen);
        $daftar = $result->fetch_all(MYSQLI_ASSOC);

        $this->view('crew/cetak_expired', [
            'title'         => 'Cetak Laporan Expired',
            'daftar'        => $daftar,
            'filterDokumen' => $filterDokumen,
        ]);
    }

    public function detail($id)
    {
        $crewModel = $this->model('CrewModel');
        $badgeModel = $this->model('BadgeModel');
        $mcuModel = $this->model('McuModel');
        $sertifikatModel = $this->model('SertifikatModel');
        $pkwtModel = $this->model('PkwtModel');

        $crew = $crewModel->getDetail($id);
        if (!$crew) $this->redirect('crew');

        $queryParams = $_GET;
        unset($queryParams['url']);
        $source = $queryParams['from'] ?? '';
        unset($queryParams['from']);

        if (empty($queryParams) && !empty($_SESSION['crew_filters'])) {
            $queryParams = array_filter($_SESSION['crew_filters']);
        }

        $queryString = http_build_query(array_filter($queryParams));

        $backTargets = [
            'pkwt' => ['url' => 'pkwt', 'label' => 'Kembali ke PKWT'],
            'badge' => ['url' => 'badge', 'label' => 'Kembali ke Badge'],
            'sertifikat' => ['url' => 'sertifikat', 'label' => 'Kembali ke Sertifikat'],
            'mcu' => ['url' => 'mcu', 'label' => 'Kembali ke MCU'],
        ];
        $backTarget = $backTargets[$source] ?? ['url' => 'crew', 'label' => 'Kembali'];
        $backUrl = BASE_URL . '/' . $backTarget['url'] . (!empty($queryString) ? '?' . $queryString : '');

        $data = [
            'title' => 'Detail Crew',
            'currentPage' => 'crew',
            'crew' => $crew,
            'backUrl' => $backUrl,
            'backLabel' => $backTarget['label'],
            'filterQuery' => $queryString,
            'badges' => $badgeModel->getByCrew($id, 'tanggal_expired', 'ASC'),
            'mcus' => $mcuModel->getByCrew($id, 'expired', 'ASC'),
            'sertifikats' => $sertifikatModel->getByCrew($id, 'tanggal_expired', 'ASC'),
            'pkwts' => $pkwtModel->getByCrew($id, 'tanggal_berakhir', 'ASC')
        ];
        $this->view('crew/detail', $data);
    }
}
