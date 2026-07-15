<?php
/**
 * app/controllers/ImportController.php
 *
 * Import data gaji bulanan dari file Excel (sheet "SUMMARY SIGMA")
 * ke tabel slip_gaji_master.
 *
 * PENTING — install dulu library PhpSpreadsheet via composer:
 *   composer require phpoffice/phpspreadsheet
 *
 * Mapping kolom di bawah ini SUDAH TERVALIDASI langsung dari formula
 * VLOOKUP asli di sheet "SLIP GAJI BULANAN" (dicocokkan manual dengan
 * data karyawan "Riyan Alfian" no urut 54 — semua nilai cocok 100%).
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ImportController extends Controller
{
    private $kolomMap = [
        'no_urut'               => 2,   
        'nama'                  => 3,   
        'jabatan'                => 4,   
        'badge'                 => 5,   
        'gaji_pokok'            => 6,  
        'tunjangan_jabatan'     => 7,   
        'tunjangan_transport'   => 8,   
        'tunjangan_perumahan'   => 9,   
        'hari_kerja'            => 25,  
        'uang_makan'            => 27,  
        'rot'                   => 29,  
        'over_time'             => 30,  
        'uang_makan_ot'         => 32,  
        'total_over_time'       => 33,  
        'bpjs_jht'              => 41,  
        'bpjs_pensiun'          => 42,  
        'bpjs_kesehatan'        => 43, 
        'pph21'                 => 59,  
        'potongan_sertifikasi'  => 60,  
        'potongan_pinjaman'     => 61,  
        'potongan_alpha'        => 62,  
        'koreksi_bulan_lalu'    => 64,  
        'sakit'                 => 65,  
        'izin'                  => 66,  
        'alpha'                 => 67, 
        'cuti'                  => 68,  
    ];

    private function ambilSel($sheet, string $field, int $row)
    {
        if (!isset($this->kolomMap[$field])) {
            die("Setup error: field '{$field}' tidak ada di \$kolomMap ImportController. Cek kembali daftar mapping-nya.");
        }
        $colLetter = Coordinate::stringFromColumnIndex($this->kolomMap[$field]);
        return $sheet->getCell($colLetter . $row);
    }

    private function ambilNilai($sheet, string $field, int $row)
    {
        return $this->ambilSel($sheet, $field, $row)->getCalculatedValue();
    }

    public function formImport()
    {
        $this->view('payroll/import_form');
    }

    public function prosesImport()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file_excel'])) {
            header('Location: ' . BASE_URL . '/payroll/import');
            exit;
        }

        $namaPeriode    = trim($_POST['nama_periode']);
        $tanggalMulai   = $_POST['tanggal_mulai'];
        $tanggalSelesai = $_POST['tanggal_selesai'];

        if (empty($namaPeriode) || empty($tanggalMulai) || empty($tanggalSelesai)) {
            $this->view('payroll/import_form', ['error' => 'Nama periode & tanggal wajib diisi.']);
            return;
        }

        $tmpPath = $_FILES['file_excel']['tmp_name'];

        try {
            $spreadsheet = IOFactory::load($tmpPath);
        } catch (Exception $e) {
            $this->view('payroll/import_form', ['error' => 'File tidak bisa dibaca: ' . $e->getMessage()]);
            return;
        }

        $sheet = null;
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            if (stripos(trim($sheetName), 'SUMMARY') === 0) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                break;
            }
        }

        if ($sheet === null) {
            $this->view('payroll/import_form', ['error' => 'Sheet "SUMMARY SIGMA" tidak ditemukan di file ini.']);
            return;
        }

        $crewModel    = $this->model('CrewModel');
        $slipModel    = $this->model('SlipGajiModel');
        $pendingModel = $this->model('SlipGajiPendingModel');

        $hasilSukses   = [];
        $hasilGagal    = []; 
        $hasilDuplikat = [];   

        $rowKosongBerturut = 0;
        $row = 15; 

        while ($rowKosongBerturut < 5) { 
            $noUrut = trim((string) $this->ambilNilai($sheet, 'no_urut', $row));

            if (empty($noUrut)) {
                $rowKosongBerturut++;
                $row++;
                continue;
            }
            $rowKosongBerturut = 0;

            $nama    = trim((string) $this->ambilNilai($sheet, 'nama', $row));
            $badge   = trim((string) $this->ambilNilai($sheet, 'badge', $row));
            $jabatan = trim((string) $this->ambilNilai($sheet, 'jabatan', $row));

            if (empty($nama) || stripos($nama, 'VACANT') !== false) {
                $row++;
                continue;
            }

            $payroll = [];
            foreach ($this->kolomMap as $field => $colNum) {
                if (in_array($field, ['no_urut', 'nama', 'jabatan', 'badge'])) continue;
                $val = $this->ambilNilai($sheet, $field, $row);
                $payroll[$field] = is_numeric($val) ? $val : 0;
            }
            $payroll['total_penghasilan'] = $payroll['gaji_pokok'] + $payroll['tunjangan_jabatan']
                + $payroll['tunjangan_transport'] + $payroll['tunjangan_perumahan'] + $payroll['uang_makan']
                + $payroll['rot'] + $payroll['total_over_time'] + $payroll['uang_makan_ot'];
            $payroll['total_potongan'] = $payroll['pph21'] + $payroll['bpjs_jht'] + $payroll['bpjs_pensiun']
                + $payroll['bpjs_kesehatan'] + $payroll['potongan_sertifikasi'] + $payroll['potongan_pinjaman']
                + $payroll['potongan_alpha'];
            $payroll['gaji_bersih'] = $payroll['total_penghasilan'] - $payroll['total_potongan'] + $payroll['koreksi_bulan_lalu'];

            $crew = empty($badge) ? null : $crewModel->getByBadge($badge);

            if (!$crew) {
                $pendingExisting = $pendingModel->getByPeriodeAndUrut($namaPeriode, $noUrut);
                if ($pendingExisting) {
                    $hasilDuplikat[] = "No.$noUrut - $nama - sudah ada dalam daftar pending, dilewati";
                    $row++;
                    continue;
                }

                $pendingModel->create([
                    'periode'         => $namaPeriode,
                    'tanggal_mulai'   => $tanggalMulai,
                    'tanggal_selesai' => $tanggalSelesai,
                    'no_urut'         => $noUrut,
                    'nama'            => $nama,
                    'jabatan'         => $jabatan,
                    'badge_excel'     => $badge, // bisa string 
                    'data_json'       => json_encode($payroll),
                ]);

                $keterangan = empty($badge)
                    ? "badge belum terbit dari pusat"
                    : "badge ($badge) belum terdaftar di data crew";
                $hasilGagal[] = "No.$noUrut - $nama - $keterangan, disimpan sementara";
                $row++;
                continue;
            }

            $existing = $slipModel->getByPeriodeAndCrew($namaPeriode, $crew['id_crew']);
            if ($existing) {
                $hasilDuplikat[] = "No.$noUrut - $nama - sudah ada di periode ini, dilewati";
                $row++;
                continue;
            }

            $data = array_merge($payroll, [
                'periode'         => $namaPeriode,
                'tanggal_mulai'   => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'no_urut'         => $noUrut,
                'id_crew'         => $crew['id_crew'],
                'nama'            => $nama,
                'jabatan'         => $jabatan,
                'badge'           => $badge,
            ]);

            $slipModel->create($data);
            $hasilSukses[] = "No.$noUrut - $nama";

            $row++;
        }

        $this->view('payroll/import_result', [
            'sukses'   => $hasilSukses,
            'gagal'    => $hasilGagal,
            'duplikat' => $hasilDuplikat,
        ]);
    }

    public function daftarPending()
    {
        $pendingModel = $this->model('SlipGajiPendingModel');
        $crewModel    = $this->model('CrewModel');

        $pending    = $pendingModel->getAllPending();
        $daftarCrew = $crewModel->getAllActive()->fetch_all(MYSQLI_ASSOC);

        foreach ($pending as &$item) {
            $saran = $crewModel->getByName($item['nama']);
            $item['saran_id_crew'] = $saran ? $saran['id_crew'] : null;
        }
        unset($item);

        $this->view('payroll/pending_list', [
            'pending'    => $pending,
            'daftarCrew' => $daftarCrew,
        ]);
    }

    public function resolvePending()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/import/daftarPending');
            exit;
        }

        $idPending = $_POST['id_pending'];
        $idCrew    = $_POST['id_crew'];

        $pendingModel = $this->model('SlipGajiPendingModel');
        $crewModel    = $this->model('CrewModel');
        $slipModel    = $this->model('SlipGajiModel');

        $pending = $pendingModel->getById($idPending);
        $crew    = $crewModel->find($idCrew);

        if (!$pending || !$crew) {
            die('Data pending atau crew tidak ditemukan.');
        }

        $payroll = json_decode($pending['data_json'], true);

        $data = array_merge($payroll, [
            'periode'         => $pending['periode'],
            'tanggal_mulai'   => $pending['tanggal_mulai'],
            'tanggal_selesai' => $pending['tanggal_selesai'],
            'no_urut'         => $pending['no_urut'],
            'id_crew'         => $crew['id_crew'],
            'nama'            => $pending['nama'],
            'jabatan'         => $pending['jabatan'],
            'badge'           => $pending['badge_excel'],
        ]);

        $slipModel->create($data);
        $pendingModel->deleteById($idPending);

        header('Location: ' . BASE_URL . '/import/daftarPending');
        exit;
    }
}
