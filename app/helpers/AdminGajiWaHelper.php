<?php

class AdminGajiWaHelper {
    private static $baseUrl = 'http://localhost:3000';
    private static $apiKey = 'kp_wa_secret_key_2026';

    /**
     * Kirim cURL HTTP Request ke Node.js WA Service
     */
    private static function request($endpoint, $method = 'GET', $data = []) {
        $url = self::$baseUrl . $endpoint;
        
        if ($method === 'GET' && !empty($data)) {
            $data['api_key'] = self::$apiKey;
            $url .= '?' . http_build_query($data);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-KEY: ' . self::$apiKey
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => 'Gagal terhubung ke WhatsApp Service: ' . $error,
                'loading' => false
            ];
        }

        $decoded = json_decode($response, true);
        if (!$decoded) {
            return [
                'success' => false,
                'error' => 'Respon tidak valid dari WhatsApp Service (HTTP ' . $httpCode . ')',
                'loading' => false
            ];
        }

        return $decoded;
    }

    /**
     * Dapatkan status koneksi WA, QR Raw, Pairing Code, dll.
     */
    public static function getStatus() {
        return self::request('/status', 'GET');
    }

    /**
     * Reset sesi WhatsApp (Menghapus session & re-inisialisasi QR baru)
     */
    public static function resetSession() {
        return self::request('/reset-session', 'POST');
    }

    /**
     * Minta kode pairing 8-digit untuk nomor WA tertentu
     */
    public static function requestPairingCode($phone) {
        return self::request('/request-pairing', 'POST', ['nomor_wa' => $phone]);
    }

    /**
     * Trigger pengiriman batch slip gaji untuk periode tertentu (opsional dryRun untuk simulasi/testing)
     */
    public static function sendBatch($periode, $dryRun = false) {
        return self::request('/send-batch', 'POST', [
            'periode' => $periode,
            'dry_run' => (bool)$dryRun
        ]);
    }

    /**
     * Hentikan pengiriman batch yang sedang berlangsung secara manual
     */
    public static function stopSending() {
        return self::request('/stop-send', 'POST');
    }
}
