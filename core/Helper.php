<?php
class Helper {
    
    // Sanitize input
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }

        if ($input === null) {
            return '';
        }

        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    // Generate CSRF token
    public static function csrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Validasi CSRF token
    public static function validateCsrf($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    // Flash message
    public static function setFlash($type, $message) {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    // Get flash message
    public static function getFlash() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    // Format tanggal Indonesia
    public static function formatDate($date) {
        if (!$date) return '-';
        $months = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $timestamp = strtotime($date);
        return date('d', $timestamp) . ' ' . $months[date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    }

    // Kalkulasi sisa hari
    public static function sisaHari($tanggal) {
        if (!$tanggal) return null;
        $now = new DateTime();
        $exp = new DateTime($tanggal);
        $diff = $now->diff($exp);
        return $exp > $now ? $diff->days : -$diff->days;
    }

    // Indikator warna K3
    public static function statusColor($tanggalExpired) {
        if (!$tanggalExpired) return 'secondary';
        $sisa = self::sisaHari($tanggalExpired);
        if ($sisa < 0) return 'danger';   // Merah - Expired
        if ($sisa <= 60) return 'warning'; // Kuning
        return 'success';                   // Hijau
    }

    // Status text
    public static function statusText($tanggalExpired) {
        if (!$tanggalExpired) return 'Tidak Ada Data';
        $sisa = self::sisaHari($tanggalExpired);
        if ($sisa < 0) return 'EXPIRED (' . abs($sisa) . ' hari)';
        if ($sisa <= 60) return $sisa . ' hari lagi';
        return $sisa . ' hari lagi';
    }

    // Debug
    public static function dd($data) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        die();
    }
}
