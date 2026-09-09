<?php

class PayrollController extends Controller
{
    public function index()
    {
        $slipModel = $this->model('SlipGajiModel');
        $daftarPeriode = $slipModel->getAllPeriodeRig();
        $stats = $slipModel->getDashboardStats();

        $periodeTerbaru = null;
        $idRigTerbaru   = null;
        $kodeRigTerbaru = null;
        $ringkasan      = null;

        if (!empty($daftarPeriode)) {
            $periodeTerbaru = $daftarPeriode[0]['periode'];
            $idRigTerbaru   = $daftarPeriode[0]['id_rig'];
            $kodeRigTerbaru = $daftarPeriode[0]['kode_rig'];
            $ringkasan = $slipModel->getRingkasan($periodeTerbaru, $idRigTerbaru);
        }

        $this->view('payroll/index', [
            'title'          => 'Dashboard Payroll',
            'currentPage'    => 'payroll_dashboard',
            'daftarPeriode'  => $daftarPeriode,
            'periodeTerbaru' => $periodeTerbaru,
            'idRigTerbaru'   => $idRigTerbaru,
            'kodeRigTerbaru' => $kodeRigTerbaru,
            'ringkasan'      => $ringkasan,
            'stats'          => $stats,
        ]);
    }

    public function pilihRig()
    {
        header('Location: ' . BASE_URL . '/payroll/daftarKaryawan');
        exit;
    }

    public function daftarPeriodeRig()
    {
        $idRig = $_GET['id_rig'] ?? '';
        if (empty($idRig)) {
            header('Location: ' . BASE_URL . '/payroll');
            exit;
        }

        $rigModel  = $this->model('RigModel');
        $slipModel = $this->model('SlipGajiModel');

        $rig = $rigModel->find($idRig);
        $daftarPeriode = $slipModel->getPeriodeByRig($idRig);

        $this->view('payroll/daftar_periode_rig', [
            'title'         => 'Slip Gaji - ' . ($rig['kode_rig'] ?? ''),
            'currentPage'   => 'payroll_slip',
            'idRig'         => $idRig,
            'kodeRig'       => $rig['kode_rig'] ?? '-',
            'daftarPeriode' => $daftarPeriode,
        ]);
    }

    public function daftarKaryawan()
    {
        $periode = $_GET['periode'] ?? '';
        $idRig   = $_GET['id_rig'] ?? '';

        $slipModel = $this->model('SlipGajiModel');

        if (empty($periode)) {
            $daftarPeriode = $slipModel->getAllPeriodeRig();

            $this->view('payroll/daftar_karyawan', [
                'title'          => 'Slip Gaji',
                'currentPage'    => 'payroll_slip',
                'periode'        => null,
                'idRig'          => null,
                'kodeRig'        => null,
                'daftarPeriode'  => $daftarPeriode,
                'daftarKaryawan' => [],
            ]);
            return;
        }

        // Jika periode ada tapi id_rig kosong → tampilkan data dengan rig NULL
        $rigModel = $this->model('RigModel');
        $daftarKaryawan = $slipModel->getByPeriode($periode, $idRig);

        // Cek apakah periode ini memiliki data dengan rig belum ditentukan
        $hasUnassignedRig = $slipModel->hasUnassignedRig($periode);

        // Ambil daftar rig untuk dropdown assign
        $daftarRig = $rigModel->allActiveIncludePayroll();

        $kodeRig = null;
        if (!empty($idRig)) {
            $rig = $rigModel->find((int)$idRig);
            if ($rig && !empty($rig['kode_rig'])) {
                $kodeRig = $rig['kode_rig'];
            }
        }

        $this->view('payroll/daftar_karyawan', [
            'title'              => 'Slip Gaji - ' . $periode,
            'currentPage'        => 'payroll_slip',
            'periode'            => $periode,
            'idRig'              => $idRig,
            'kodeRig'            => $kodeRig,
            'daftarKaryawan'     => $daftarKaryawan,
            'daftarPeriode'      => [],
            'hasUnassignedRig'   => $hasUnassignedRig,
            'daftarRig'          => $daftarRig,
        ]);
    }

    public function cariKaryawanAjax()
    {
        $periode = $_GET['periode'] ?? '';
        $idRig   = $_GET['id_rig'] ?? '';
        $search  = trim($_GET['search'] ?? '');

        if (empty($periode)) {
            echo '<tr><td colspan="5" class="text-center py-4 text-muted">Periode tidak valid</td></tr>';
            return;
        }

        $slipModel = $this->model('SlipGajiModel');
        $daftarKaryawan = empty($search)
            ? $slipModel->getByPeriode($periode, $idRig)
            : $slipModel->getByPeriodeFiltered($periode, $idRig, $search);

        $this->view('payroll/_tabel_rows', [
            'daftarKaryawan' => $daftarKaryawan,
        ]);
    }

    /**
     * Assign rig secara massal untuk seluruh data periode.
     */
    public function assignRig()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/payroll');
            exit;
        }

        $periode = $_POST['periode'] ?? '';
        $idRig   = $_POST['id_rig'] ?? '';

        if (empty($periode) || empty($idRig)) {
            Helper::setFlash('error', 'Periode dan Rig wajib dipilih.');
            header('Location: ' . BASE_URL . '/payroll/daftarKaryawan?periode=' . urlencode($periode));
            exit;
        }

        $slipModel = $this->model('SlipGajiModel');
        $slipModel->assignRigToPeriode($periode, $idRig);

        Helper::setFlash('success', "Rig berhasil ditentukan untuk periode \"$periode\".");
        header('Location: ' . BASE_URL . '/payroll/daftarKaryawan?periode=' . urlencode($periode) . '&id_rig=' . $idRig);
        exit;
    }

    public function konfirmasiHapusPeriode()
    {
        $periode = $_GET['periode'] ?? '';
        $idRig   = $_GET['id_rig'] ?? '';
        if (empty($periode)) {
            header('Location: ' . BASE_URL . '/payroll');
            exit;
        }

        $slipModel = $this->model('SlipGajiModel');
        $rigModel  = $this->model('RigModel');

        $rig = !empty($idRig) ? $rigModel->find($idRig) : null;
        $jumlah = $slipModel->countByPeriode($periode, $idRig);

        $this->view('payroll/konfirmasi_hapus', [
            'title'       => 'Hapus Data Periode',
            'currentPage' => 'payroll_slip',
            'periode'     => $periode,
            'idRig'       => $idRig,
            'kodeRig'     => $rig['kode_rig'] ?? 'Belum Ditentukan',
            'jumlah'      => $jumlah,
        ]);
    }

    public function hapusPeriode()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/payroll');
            exit;
        }

        $periode    = $_POST['periode'] ?? '';
        $idRig      = $_POST['id_rig'] ?? '';
        $konfirmasi = $_POST['konfirmasi'] ?? '';

        if ($konfirmasi !== $periode) {
            Helper::setFlash('error', 'Konfirmasi tidak cocok. Data TIDAK dihapus.');
            header('Location: ' . BASE_URL . '/payroll/konfirmasiHapusPeriode?periode=' . urlencode($periode) . '&id_rig=' . urlencode($idRig));
            exit;
        }

        $slipModel = $this->model('SlipGajiModel');
        $slipModel->deleteByPeriode($periode, $idRig);

        Helper::setFlash('success', "Data periode \"$periode\" berhasil dihapus.");
        header('Location: ' . BASE_URL . '/payroll');
        exit;
    }

    public function cetak($idSlip)
    {
        $slipModel = $this->model('SlipGajiModel');
        $slip = $slipModel->getById($idSlip);

        if (!$slip) {
            die('Slip gaji tidak ditemukan.');
        }

        $this->view('payroll/cetak', [
            'title'       => 'Cetak Slip Gaji',
            'currentPage' => 'payroll_slip',
            'slip'        => $slip,
        ]);
    }
}
