<?php
class AdminController extends Controller {
    
    public function __construct() {
        $this->requireSuperAdmin();
    }
    
    public function users() {
        $userModel = $this->model('UserModel');
        $users = $userModel->all();
        
        $data = [
            'title' => 'Kelola User',
            'currentPage' => 'admin',
            'users' => $users
        ];
        $this->view('admin/users', $data);
    }
    
    public function createUser() {
        $rigModel = $this->model('RigModel');
        $data = [
            'title' => 'Tambah User',
            'currentPage' => 'admin',
            'rigs' => $rigModel->allActive()
        ];
        $this->view('admin/create_user', $data);
    }
    
    public function storeUser() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('admin/users');
        }
        
        $userModel = $this->model('UserModel');
        
        $nama = $_POST['nama'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'] ?? 'admin_rig';
        $rigIds = $_POST['id_rig'] ?? [];
        
        // Insert user
        $id_user = $userModel->insert([
            'nama' => $nama,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ]);
        
        // Simpan rig untuk user (kecuali super admin)
        if ($role == 'admin_rig' && !empty($rigIds)) {
            $userModel->saveUserRigs($id_user, $rigIds);
        }
        
        Helper::setFlash('success', 'User berhasil ditambahkan!');
        $this->redirect('admin/users');
    }

    // UPDATE USER (GANTI EMAIL & RESET PASSWORD)
    public function updateUser($id) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('admin/users');
        }
        
        $userModel = $this->model('UserModel');
        $user = $userModel->find($id);
        
        if (!$user) {
            Helper::setFlash('error', 'User tidak ditemukan!');
            $this->redirect('admin/users');
        }
        
        $nama = $_POST['nama'] ?? $user['nama'];
        $email = $_POST['email'] ?? $user['email'];
        $role = $_POST['role'] ?? $user['role'];
        
        $updateData = [
            'nama' => $nama,
            'email' => $email,
            'role' => $role
        ];
        
        // Reset password jika diisi
        if (!empty($_POST['password'])) {
            $updateData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        
        $userModel->update($id, $updateData);
        
        Helper::setFlash('success', "Akun '{$nama}' (Email & Password) berhasil diperbarui!");
        $this->redirect('admin/users');
    }

    // HAPUS USER
    public function deleteUser($id) {
        $this->requireSuperAdmin();
        $userModel = $this->model('UserModel');
        $userModel->delete($id);
        Helper::setFlash('success', 'User berhasil dihapus!');
        $this->redirect('admin/users');
    }

    // Kelola Master Data
public function master() {
    $this->requireSuperAdmin();
    
    $jenisSuratModel = $this->model('JenisSuratModel');
    $rigModel = $this->model('RigModel');
    
    $data = [
        'title' => 'Master Data',
        'currentPage' => 'admin',
        'jenis_surat' => $jenisSuratModel->all(),
        'rigs' => $rigModel->allActive()
    ];
    $this->view('admin/master', $data);
}

// Tambah Jenis Surat Baru
public function storeJenisSurat() {
    $this->requireSuperAdmin();
    
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        $this->redirect('admin/master');
    }
    
    $jenisSuratModel = $this->model('JenisSuratModel');
    
    $kode = trim($_POST['kode'] ?? '');
    $nama = trim($_POST['nama_surat'] ?? '');
    $format = trim($_POST['format_nomor'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    
    if (!empty($kode) && !empty($nama) && !empty($format)) {
        $jenisSuratModel->insert([
            'kode' => $kode,
            'nama_surat' => $nama,
            'format_nomor' => $format,
            'keterangan' => $keterangan
        ]);
        Helper::setFlash('success', 'Jenis surat berhasil ditambahkan!');
    }
    
    $this->redirect('admin/master');
}

    // Hapus Jenis Surat
    public function deleteJenisSurat($id) {
        $this->requireSuperAdmin();
        
        $jenisSuratModel = $this->model('JenisSuratModel');
        $jenisSuratModel->delete($id);
        
        Helper::setFlash('success', 'Jenis surat berhasil dihapus!');
        $this->redirect('admin/master');
    }
}