<?php
class Controller {
    // Load view dengan data
    protected function view($view, $data = []) {
        $defaults = [
            'totalCrew' => 0,
            'totalCrewAll' => 0,
            'totalSurat' => 0,
            'totalExpired' => 0,
            'totalWarning' => 0,
            'complyPercent' => 100,
            'warningPercent' => 0,
            'nonComplyPercent' => 0,
            'currentPage' => '',
            'title' => '',
            'page' => 1,
            'totalPages' => 1,
            'offset' => 0,
        ];

        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $data)) {
                $data[$key] = $value;
            }
        }

        extract($data);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View {$view} tidak ditemukan. Path: {$viewFile}");
        }
    }

    // Load model
    protected function model($model) {
        require_once __DIR__ . '/../app/models/' . $model . '.php';
        return new $model();
    }

    // Redirect
    protected function redirect($url) {
        header("Location: " . BASE_URL . '/' . $url);
        exit();
    }

    protected function isLoggedIn() {
        return isset($_SESSION['user']);
    }

    protected function isSuperAdmin() {
        return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'super_admin'; 
    }

    protected function isAdminRig() {
        return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin_rig'; 
    }

    protected function getCurrentRigIds() {
        return $_SESSION['user']['rig_ids'] ?? [];
    }

    protected function isAdminAllRig() {
        return $this->isSuperAdmin() || empty($this->getCurrentRigIds());
    }
    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            $this->redirect('auth/login');
        }
    }

    protected function requireSuperAdmin() {
        $this->requireLogin();
        if (!$this->isSuperAdmin()) {
            die("Akses ditolak. Hanya Super Admin yang bisa mengakses halaman ini.");
        }
    }
}