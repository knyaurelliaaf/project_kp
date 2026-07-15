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
            
            $userModel = $this->model('UserModel');
            $user = $userModel->findByEmail($email);
            
                if ($user && password_verify($password, $user['password'])) {
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
            
            $this->redirect('dashboard');
        } else {
                $error = 'Email atau password salah!';
            }
        }
        
        $this->view('auth/login', ['error' => $error]);
    }
    
    // Logout
    public function logout() {
        session_destroy();
        $this->redirect('auth/login');
    }
}