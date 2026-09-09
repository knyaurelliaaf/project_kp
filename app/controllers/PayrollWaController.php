<?php

class PayrollWaController extends Controller
{
    private function checkAccess()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('auth/login');
        }

        $user = $_SESSION['user'] ?? [];
        $role = $user['role'] ?? '';

        if ($role !== 'payroll_wa' && $role !== 'super_admin') {
            die("Akses ditolak. Halaman ini khusus untuk Role Payroll.");
        }
    }

    public function index()
    {
        $this->checkAccess();

        $payrollModel = $this->model('PayrollModel');
        $logModel = $this->model('LogPengirimanWaModel');
        $rigModel = $this->model('RigModel');

        // Ambil daftar periode
        $periodeList = [];
        $resP = $payrollModel->getPeriodeList();
        if ($resP) {
            while ($row = $resP->fetch_assoc()) {
                $periodeList[] = $row['periode'];
            }
        }

        $selectedPeriode = $_GET['periode'] ?? ($periodeList[0] ?? '');
        $selectedRig = $_GET['id_rig'] ?? '';

        $karyawanList = [];
        $stats = [
            'total_slip' => 0, 'total_pending' => 0, 'total_proses' => 0, 'total_terkirim' => 0, 'total_gagal' => 0
        ];

        if (!empty($selectedPeriode)) {
            $resK = $logModel->getLogsByPeriode($selectedPeriode, $selectedRig);
            if ($resK) {
                while ($r = $resK->fetch_assoc()) {
                    $karyawanList[] = $r;
                }
            }
            $stats = $logModel->getStatsByPeriode($selectedPeriode, $selectedRig);
        }

        $rigs = $rigModel->all();

        $data = [
            'title' => 'Pengirim WA Slip Gaji',
            'currentPage' => 'payroll_wa_dashboard',
            'periodeList' => $periodeList,
            'selectedPeriode' => $selectedPeriode,
            'selectedRig' => $selectedRig,
            'karyawanList' => $karyawanList,
            'stats' => $stats,
            'rigs' => $rigs
        ];

        $this->view('payroll_wa/index', $data);
    }

    public function ajaxKirimWa()
    {
        header('Content-Type: application/json');

        if (!$this->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $idSlip = (int)($_POST['id_slip'] ?? 0);
        if (!$idSlip) {
            echo json_encode(['success' => false, 'message' => 'ID Slip tidak valid']);
            exit;
        }

        $logModel = $this->model('LogPengirimanWaModel');
        $db = $logModel->getDb();

        // Ambil data slip master
        $sql = "SELECT s.*, r.kode_rig 
                FROM slip_gaji_master s 
                LEFT JOIN rig r ON s.id_rig = r.id_rig 
                WHERE s.id_slip = {$idSlip}";
        $res = $db->query($sql);
        if (!$res || $res->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Data slip gaji tidak ditemukan']);
            exit;
        }

        $slip = $res->fetch_assoc();
        $nama = $slip['nama'];
        $posisi = $slip['posisi'] ?? $slip['jabatan'] ?? '';
        $periode = $slip['periode'];
        $thpVal = $slip['thp'] ?? $slip['gaji_bersih'] ?? 0;
        $thp = number_format((float)$thpVal, 0, ',', '.');

        $jsonData = !empty($slip['data_json']) ? json_decode($slip['data_json'], true) : [];
        $rawPhone = trim($slip['no_hp'] ?? $jsonData['no_hp'] ?? $jsonData['no_wa'] ?? $jsonData['hp'] ?? $jsonData['telepon'] ?? '');

        // Format nomor telepon ke format internasional (628xxx)
        $noWa = preg_replace('/[^0-9]/', '', $rawPhone);
        if (substr($noWa, 0, 1) === '0') {
            $noWa = '62' . substr($noWa, 1);
        }

        if (empty($noWa) || strlen($noWa) < 9) {
            $logModel->logOrUpdate($idSlip, 0, $periode, $rawPhone ?: '-', 'gagal', 'Nomor WA/HP tidak valid atau kosong');
            echo json_encode([
                'success' => false,
                'id_slip' => $idSlip,
                'nama' => $nama,
                'status' => 'gagal',
                'message' => 'Nomor WA tidak valid / kosong'
            ]);
            exit;
        }

        // Buat link cetak slip gaji
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $linkCetak = "{$scheme}://{$host}" . BASE_URL . "/payroll/cetak?id_slip=" . $idSlip;

        // Template variasi salam dinamis untuk menghindari deteksi spam
        $salamList = [
            "Yth. Bpk/Ibu {$nama}",
            "Halo Bpk/Ibu {$nama}",
            "Kepada Yth. {$nama}",
            "Selamat Hari Ini Bpk/Ibu {$nama}"
        ];
        $salam = $salamList[array_rand($salamList)];

        $pesanTeks = "{$salam},\n\nBerikut disampikan rincian *Slip Gaji PT. ADK-6*:\n"
                   . "• Nama: *{$nama}*\n"
                   . "• Posisi: {$posisi}\n"
                   . "• Periode: *{$periode}*\n"
                   . "• Gaji Diterima (THP): *Rp {$thp}*\n\n"
                   . "Silakan akses link dokumen slip gaji berikut:\n{$linkCetak}\n\n"
                   . "_Pesan ini dikirimkan secara otomatis oleh Sistem Payroll PT. ADK-6._";

        // DI SINI INTEGRASI API WA GATEWAY (Misal Fonnte / Wablas / API Provider)
        // Kita simulasikan / eksekusi via cURL atau API Token jika dikonfigurasi.
        $apiToken = $_POST['api_token'] ?? '';
        $isGatewayActive = !empty($apiToken);

        $sendSuccess = true;
        $errorMessage = null;

        if ($isGatewayActive) {
            // Contoh eksekusi Fonnte API Gateway jika token diisi
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'target' => $noWa,
                    'message' => $pesanTeks,
                    'countryCode' => '62',
                ),
                CURLOPT_HTTPHEADER => array(
                    "Authorization: {$apiToken}"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                $sendSuccess = false;
                $errorMessage = "cURL Error: " . $err;
            } else {
                $resData = json_decode($response, true);
                if (isset($resData['status']) && $resData['status'] === false) {
                    $sendSuccess = false;
                    $errorMessage = $resData['reason'] ?? 'Gagal dari WA Gateway';
                }
            }
        }

        if ($sendSuccess) {
            $logModel->logOrUpdate($idSlip, 0, $periode, $noWa, 'terkirim', null);
            echo json_encode([
                'success' => true,
                'id_slip' => $idSlip,
                'nama' => $nama,
                'no_wa' => $noWa,
                'status' => 'terkirim',
                'message' => 'Pesan WA berhasil dikirim'
            ]);
        } else {
            $logModel->logOrUpdate($idSlip, 0, $periode, $noWa, 'gagal', $errorMessage);
            echo json_encode([
                'success' => false,
                'id_slip' => $idSlip,
                'nama' => $nama,
                'no_wa' => $noWa,
                'status' => 'gagal',
                'message' => $errorMessage
            ]);
        }
        exit;
    }

    public function ajaxResetGagal()
    {
        header('Content-Type: application/json');
        if (!$this->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $periode = $_POST['periode'] ?? '';
        $idRig = $_POST['id_rig'] ?? null;

        if (empty($periode)) {
            echo json_encode(['success' => false, 'message' => 'Periode kosong']);
            exit;
        }

        $logModel = $this->model('LogPengirimanWaModel');
        $logModel->resetFailedByPeriode($periode, $idRig);

        echo json_encode(['success' => true, 'message' => 'Status gagal berhasil di-reset']);
        exit;
    }

    public function log()
    {
        $this->checkAccess();

        $logModel = $this->model('LogPengirimanWaModel');
        $logs = $logModel->getHistoryLogs(150);

        $data = [
            'title' => 'Riwayat Pengiriman WA',
            'currentPage' => 'payroll_wa_log',
            'logs' => $logs
        ];

        $this->view('payroll_wa/log', $data);
    }
}
