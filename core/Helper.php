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

    // Log Error Otomatis ke File (Sangat berguna di Hosting)
    public static function logError($message, $context = []) {
        $logDir = __DIR__ . '/../app/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/app_error.log';
        $timestamp = date('Y-m-d H:i:s');
        $user = $_SESSION['user']['nama'] ?? $_SESSION['user']['username'] ?? 'System';
        $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
        $logLine = "[{$timestamp}] [User: {$user}] [ERROR] {$message}{$contextStr}" . PHP_EOL;
        @file_put_contents($logFile, $logLine, FILE_APPEND);
    }

    // Baca Log Error
    public static function getLogs($lines = 100) {
        $logFile = __DIR__ . '/../app/logs/app_error.log';
        if (!file_exists($logFile)) return [];
        $file = file($logFile);
        return array_slice($file, -$lines);
    }

    /**
     * Generate array halaman dengan ellipsis agar paginasi tidak terlalu panjang.
     * Contoh hasil: [1, '...', 10, 11, 12, 13, 14, '...', 22]
     */
    public static function getPaginationRange($currentPage, $totalPages, $delta = 2) {
        $currentPage = (int)$currentPage;
        $totalPages = (int)$totalPages;
        if ($totalPages <= 1) return [];

        $delta = (int)$delta;
        $range = [];
        $rangeWithEllipsis = [];

        $start = max(2, $currentPage - $delta);
        $end = min($totalPages - 1, $currentPage + $delta);

        $range[] = 1;
        for ($i = $start; $i <= $end; $i++) {
            $range[] = $i;
        }
        if ($totalPages > 1) {
            $range[] = $totalPages;
        }

        $prev = 0;
        foreach ($range as $page) {
            if ($prev > 0) {
                if ($page - $prev === 2) {
                    $rangeWithEllipsis[] = $prev + 1;
                } elseif ($page - $prev > 2) {
                    $rangeWithEllipsis[] = '...';
                }
            }
            $rangeWithEllipsis[] = $page;
            $prev = $page;
        }

        return $rangeWithEllipsis;
    }

    /**
     * Render HTML tombol angka paginasi dengan batas maksimal/ellipsis.
     */
    public static function renderPaginationNumbers($currentPage, $totalPages, $onclickFunc = 'loadPage') {
        $pages = self::getPaginationRange($currentPage, $totalPages);
        $html = '';
        foreach ($pages as $p) {
            if ($p === '...') {
                $html .= '<span class="btn btn-sm btn-page disabled" style="pointer-events:none;opacity:0.5;border:none;background:transparent;padding:4px 6px;">...</span>';
            } else {
                $active = ($p == $currentPage) ? ' active' : '';
                $html .= '<a href="javascript:void(0)" onclick="' . htmlspecialchars($onclickFunc) . '(' . $p . ')" class="btn btn-sm btn-page' . $active . '">' . $p . '</a>';
            }
        }
        return $html;
    }
}
