<?php
class AuthController extends Controller {
    
    // Tampilkan halaman login
    public function login() {
        // Kalau sudah login, redirect ke dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
        }
        
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($email) && empty($password)) {
                $error = 'Silakan masukkan email dan kata sandi Anda!';
            } elseif (empty($email)) {
                $error = 'Alamat email wajib diisi!';
            } elseif (empty($password)) {
                $error = 'Kata sandi wajib diisi!';
            } else {
                $userModel = $this->model('UserModel');
                $user = $userModel->findByEmail($email);
                
                if (!$user) {
                    $error = 'Alamat email tidak terdaftar!';
                } elseif (!password_verify($password, $user['password'])) {
                    $error = 'Kata sandi yang Anda masukkan salah!';
                } else {
                    session_regenerate_id(true);
                    
                    // Ambil list rig
                    $rigIds = $userModel->getRigIds($user['id_user']);
                    
                    $_SESSION['user'] = [
                        'id_user' => $user['id_user'],
                        'nama' => $user['nama'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'rig_ids' => $rigIds,
                        'kode_rig' => $user['kode_rig_list'] ?? ''
                    ];
                    $_SESSION['show_welcome'] = true;
                    
                    // Redirect berdasarkan role
                    if ($user['role'] === 'payroll') {
                        $this->redirect('payroll');
                    } elseif (in_array($user['role'], ['payroll_wa', 'admin_gaji'])) {
                        $this->redirect('admin_gaji');
                    } else {
                        $this->redirect('dashboard');
                    }
                }
            }
        }
        
        $this->view('auth/login', ['error' => $error]);
    }
    
    // Logout
    public function logout() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        $this->redirect('auth/login');
    }

    // HALAMAN EDIT PROFIL SAYA (UNTUK SEMUA USER)
    public function profile() {
        $this->requireLogin();
        $userModel = $this->model('UserModel');
        $id_user = $_SESSION['user']['id_user'] ?? 0;
        $user = $userModel->find($id_user);

        if (!$user) {
            Helper::setFlash('error', 'Data user tidak ditemukan!');
            $this->redirect('dashboard');
        }

        $data = [
            'title' => 'Edit Profil Saya',
            'currentPage' => 'profile',
            'user' => $user
        ];
        $this->view('auth/profile', $data);
    }

    // SIMPAN EDIT PROFIL SAYA
    public function updateProfile() {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('auth/profile');
        }

        $id_user = $_SESSION['user']['id_user'] ?? 0;
        $userModel = $this->model('UserModel');
        $user = $userModel->find($id_user);

        if (!$user) {
            Helper::setFlash('error', 'User tidak ditemukan!');
            $this->redirect('dashboard');
        }

        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $passwordLama = $_POST['password_lama'] ?? '';
        $passwordBaru = $_POST['password_baru'] ?? '';

        if (empty($nama) || empty($email)) {
            Helper::setFlash('error', 'Nama dan Email wajib diisi!');
            $this->redirect('auth/profile');
        }

        $updateData = [
            'nama' => $nama,
            'email' => $email
        ];

        // Jika user ingin mereset/mengganti password
        if (!empty($passwordBaru)) {
            if (empty($passwordLama) || !password_verify($passwordLama, $user['password'])) {
                Helper::setFlash('error', 'Password lama Anda tidak sesuai!');
                $this->redirect('auth/profile');
            }
            $updateData['password'] = password_hash($passwordBaru, PASSWORD_DEFAULT);
        }

        $userModel->update($id_user, $updateData);

        // Update session
        $_SESSION['user']['nama'] = $nama;
        $_SESSION['user']['email'] = $email;

        Helper::setFlash('success', 'Profil dan akun Anda berhasil diperbarui!');
        $this->redirect('auth/profile');
    }
}