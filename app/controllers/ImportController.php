<?php

if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ImportController extends Controller
{
    private $kolomMap = [
        'no_urut'               => 2,
        'nama'                  => 3,
        'jabatan'               => 4,
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
            die("Setup error: field '{$field}' tidak ada di \$kolomMap ImportController.");
        }
        $colLetter = Coordinate::stringFromColumnIndex($this->kolomMap[$field]);
        return $sheet->getCell($colLetter . $row);
    }

    private function ambilNilai($sheet, string $field, int $row)
    {
        return $this->ambilSel($sheet, $field, $row)->getCalculatedValue();
    }

    private function nilaiSel($sheet, string $coordinate)
    {
        $value = $sheet->getCell($coordinate)->getCalculatedValue();
        return is_numeric($value) ? (float) $value : 0;
    }

    private function kunciUrut($noUrut): string
    {
        return rtrim(rtrim(trim((string) $noUrut), '0'), '.') ?: '0';
    }

    /**
     * Ambil nilai final dari setiap sheet SLIP GAJI PRORATE.
     * Nilai pada sheet ini sudah mengikuti rumus payroll untuk new hire,
     * sehingga tidak boleh dihitung ulang dengan rumus SUMMARY SIGMA reguler.
     */
    private function ambilDataProrate($spreadsheet): array
    {
        $hasil = [];

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $namaSheet = strtoupper(trim($sheet->getTitle()));
            if (strpos($namaSheet, 'SLIP GAJI PRORATE') !== 0 || strpos($namaSheet, 'LAMA') !== false) {
                continue;
            }

            $noUrut = trim((string) $sheet->getCell('N3')->getCalculatedValue());
            $nama = trim((string) $sheet->getCell('D6')->getCalculatedValue());
            if ($noUrut === '' || $nama === '') {
                continue;
            }

            $kunciNama = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $nama));
            $kunciProrate = $this->kunciUrut($noUrut) . '_' . $kunciNama;

            $hasil[$kunciProrate] = [
                'gaji_pokok'           => $this->nilaiSel($sheet, 'E15'),
                'tunjangan_jabatan'    => $this->nilaiSel($sheet, 'E16'),
                'rot'                  => $this->nilaiSel($sheet, 'E18'),
                'tunjangan_transport'  => $this->nilaiSel($sheet, 'E19'),
                'tunjangan_perumahan'  => $this->nilaiSel($sheet, 'E20'),
                'uang_makan'           => $this->nilaiSel($sheet, 'E21'),
                'total_over_time'      => $this->nilaiSel($sheet, 'E23'),
                'uang_makan_ot'        => $this->nilaiSel($sheet, 'E24'),
                'pph21'                => $this->nilaiSel($sheet, 'J15'),
                'bpjs_jht'             => $this->nilaiSel($sheet, 'J16'),
                'bpjs_pensiun'         => $this->nilaiSel($sheet, 'J17'),
                'bpjs_kesehatan'       => $this->nilaiSel($sheet, 'J18'),
                'potongan_sertifikasi' => $this->nilaiSel($sheet, 'J19'),
                'potongan_pinjaman'    => $this->nilaiSel($sheet, 'J20'),
                'potongan_alpha'       => $this->nilaiSel($sheet, 'J21'),
                'hari_kerja'           => $this->nilaiSel($sheet, 'J6'),
                'sakit'                => $this->nilaiSel($sheet, 'J7'),
                'izin'                 => $this->nilaiSel($sheet, 'J8'),
                'alpha'                => $this->nilaiSel($sheet, 'J9'),
                'cuti'                 => $this->nilaiSel($sheet, 'J10'),
                'over_time'            => $this->nilaiSel($sheet, 'J11'),
                'total_penghasilan'    => $this->nilaiSel($sheet, 'E26'),
                'total_potongan'       => $this->nilaiSel($sheet, 'J26'),
                'koreksi_bulan_lalu'   => $this->nilaiSel($sheet, 'J27'),
                'gaji_bersih'          => $this->nilaiSel($sheet, 'J29'),
                'sumber_perhitungan'   => 'SLIP GAJI PRORATE',
            ];
        }

        return $hasil;
    }

    public function index()
    {
        $this->formImport();
    }

    public function formImport()
    {
        $slipModel = $this->model('SlipGajiModel');
        $rigModel  = $this->model('RigModel');

        $daftarRig     = $rigModel->allActiveIncludePayroll();
        $historyImport = $slipModel->getHistoryImport();
        $daftarPeriode = $slipModel->getAllPeriodeRig();

        $periodeTerbaru = null;
        $idRigTerbaru   = null;
        if (!empty($daftarPeriode)) {
            $periodeTerbaru = $daftarPeriode[0]['periode'];
            $idRigTerbaru   = $daftarPeriode[0]['id_rig'];
        }

        $errorNotice = null;
        if (!file_exists(__DIR__ . '/../../vendor/autoload.php')) {
            $errorNotice = 'Folder/library vendor (`vendor/autoload.php`) tidak ditemukan di server. Pastikan folder vendor telah di-upload ke server hosting agar fitur import Excel dapat memproses file.';
        }

        $this->view('payroll/import_form', [
            'title'          => 'Import Data Gaji',
            'currentPage'    => 'payroll_import',
            'daftarRig'      => $daftarRig,
            'historyImport'  => $historyImport,
            'periodeTerbaru' => $periodeTerbaru,
            'idRigTerbaru'   => $idRigTerbaru,
            'error'          => $errorNotice,
        ]);
    }

    public function prosesImport()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file_excel'])) {
            header('Location: ' . BASE_URL . '/import/formImport');
            exit;
        }

        $slipModel = $this->model('SlipGajiModel');
        $rigModel  = $this->model('RigModel');

        $daftarRig     = $rigModel->allActiveIncludePayroll();
        $historyImport = $slipModel->getHistoryImport();
        $daftarPeriode = $slipModel->getAllPeriodeRig();

        $periodeTerbaru = null;
        $idRigTerbaru   = null;
        if (!empty($daftarPeriode)) {
            $periodeTerbaru = $daftarPeriode[0]['periode'];
            $idRigTerbaru   = $daftarPeriode[0]['id_rig'];
        }

        $namaPeriode = trim($_POST['nama_periode'] ?? '');
        $idRigSelect = !empty($_POST['id_rig']) ? (int)$_POST['id_rig'] : null;

        $tanggalMulai = date('Y-m-d');
        $tanggalSelesai = $tanggalMulai;

        if (empty($namaPeriode)) {
            $this->view('payroll/import_form', [
                'error'          => 'Nama periode wajib diisi.',
                'daftarRig'      => $daftarRig,
                'historyImport'  => $historyImport,
                'periodeTerbaru' => $periodeTerbaru,
                'idRigTerbaru'   => $idRigTerbaru,
            ]);
            return;
        }

        if (!file_exists(__DIR__ . '/../../vendor/autoload.php')) {
            $this->view('payroll/import_form', [
                'error'          => 'Library PhpSpreadsheet (folder vendor) tidak ditemukan di server. Silakan upload folder vendor ke server hosting.',
                'daftarRig'      => $daftarRig,
                'historyImport'  => $historyImport,
                'periodeTerbaru' => $periodeTerbaru,
                'idRigTerbaru'   => $idRigTerbaru,
            ]);
            return;
        }

        $tmpPath = $_FILES['file_excel']['tmp_name'];

        try {
            $spreadsheet = IOFactory::load($tmpPath);
        } catch (Exception $e) {
            $this->view('payroll/import_form', [
                'error'          => 'File tidak bisa dibaca: ' . $e->getMessage(),
                'daftarRig'      => $daftarRig,
                'historyImport'  => $historyImport,
                'periodeTerbaru' => $periodeTerbaru,
                'idRigTerbaru'   => $idRigTerbaru,
            ]);
            return;
        }

        $sheet = null;

        // Priority 1: Sheet containing both SUMMARY and SIGMA
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $sUpper = strtoupper(trim($sheetName));
            if (strpos($sUpper, 'SUMMARY') !== false && strpos($sUpper, 'SIGMA') !== false) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                break;
            }
        }

        // Priority 2: Sheet containing SIGMA
        if ($sheet === null) {
            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                $sUpper = strtoupper(trim($sheetName));
                if (strpos($sUpper, 'SIGMA') !== false) {
                    $sheet = $spreadsheet->getSheetByName($sheetName);
                    break;
                }
            }
        }

        // Priority 3: Sheet containing SUMMARY but NOT GWDC
        if ($sheet === null) {
            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                $sUpper = strtoupper(trim($sheetName));
                if (strpos($sUpper, 'SUMMARY') !== false && strpos($sUpper, 'GWDC') === false) {
                    $sheet = $spreadsheet->getSheetByName($sheetName);
                    break;
                }
            }
        }

        // Priority 4: Active sheet fallback
        if ($sheet === null) {
            $sheet = $spreadsheet->getActiveSheet();
        }

        $dataProrate = $this->ambilDataProrate($spreadsheet);

        $crewModel = $this->model('CrewModel');

        // Otomatis bersihkan data lama jika periode & rig ini pernah di-import sebelumnya
        // (Agar jika file diunggah ulang, data lama ter-update bersih tanpa duplikasi/terlewati)
        $slipModel->deleteByPeriode($namaPeriode, $idRigSelect);

        $hasilSukses   = [];
        $hasilDuplikat = [];
        $detailImport  = [];

        $highestRow = min(500, (int)$sheet->getHighestRow());
        $offsetKolom = 0;
        $startRow = 15;

        // Auto-detect baris awal & offset kolom (apakah No Urut di Kolom B atau Kolom A)
        for ($r = 1; $r <= min(50, $highestRow); $r++) {
            // Cek standar: Kolom B (2) = No Urut, Kolom C (3) = Nama
            $valB = trim((string)$sheet->getCell('B' . $r)->getCalculatedValue());
            $valC = trim((string)$sheet->getCell('C' . $r)->getCalculatedValue());

            // Nama HARUS mengandung setidaknya 2 huruf (bukan sekadar angka nomor kolom seperti "2")
            if (is_numeric($valB) && (float)$valB > 0 && preg_match('/[a-zA-Z]{2,}/', $valC) && !in_array(strtoupper($valC), ['NAMA', 'NAME', 'TOTAL', 'SUBTOTAL', 'NO', 'JABATAN', 'BADGE'])) {
                $offsetKolom = 0;
                $startRow = $r;
                break;
            }

            // Cek jika tergeser 1 kolom ke kiri: Kolom A (1) = No Urut, Kolom B (2) = Nama
            $valA = trim((string)$sheet->getCell('A' . $r)->getCalculatedValue());
            if (is_numeric($valA) && (float)$valA > 0 && preg_match('/[a-zA-Z]{2,}/', $valB) && !in_array(strtoupper($valB), ['NAMA', 'NAME', 'TOTAL', 'SUBTOTAL', 'NO', 'JABATAN', 'BADGE'])) {
                $offsetKolom = -1;
                $startRow = $r;
                break;
            }
        }

        $rowKosongBerturut = 0;
        $hasStarted = false;

        for ($row = $startRow; $row <= $highestRow; $row++) {
            $colNoUrut = Coordinate::stringFromColumnIndex($this->kolomMap['no_urut'] + $offsetKolom);
            $colNama   = Coordinate::stringFromColumnIndex($this->kolomMap['nama'] + $offsetKolom);

            $noUrutRaw = (string) $sheet->getCell($colNoUrut . $row)->getCalculatedValue();
            $namaRaw   = (string) $sheet->getCell($colNama . $row)->getCalculatedValue();

            $noUrut = trim($noUrutRaw);
            $nama   = trim($namaRaw);

            $namaUpper   = strtoupper($nama);
            $noUrutUpper = strtoupper($noUrut);

            // Berhenti jika sudah mulai membaca data SIGMA dan bertemu header tabel bawah (GWDC / OT CREW)
            if ($hasStarted && (
                strpos($namaUpper, 'GWDC') !== false || 
                strpos($noUrutUpper, 'GWDC') !== false || 
                strpos($namaUpper, 'OT CREW') !== false || 
                strpos($noUrutUpper, 'OT CREW') !== false || 
                strpos($namaUpper, 'OVERTIME') !== false
            )) {
                break; // Berhenti agar data GWDC/OT CREW tidak ter-import ke SIGMA
            }

            // Jika no_urut atau nama kosong
            if (empty($noUrut) || empty($nama)) {
                if ($hasStarted) {
                    $rowKosongBerturut++;
                    if ($rowKosongBerturut >= 5) {
                        break; // Berhenti jika ada 5 baris kosong berturut-turut setelah data dimulai
                    }
                }
                continue;
            }

            // Validasi nama karyawan: Nama HARUS mengandung setidaknya huruf (mengabaikan baris nomor kolom seperti 1, 2, 3, 4)
            if (is_numeric($nama) || !preg_match('/[a-zA-Z]{2,}/', $nama)) {
                continue;
            }

            // Jika no_urut bukan angka (misal header "NO", "TOTAL", "SUBTOTAL")
            if (!is_numeric($noUrut)) {
                if ($hasStarted && (strpos($namaUpper, 'TOTAL') !== false || strpos($noUrutUpper, 'TOTAL') !== false)) {
                    break; // Berhenti jika bertemu baris TOTAL di akhir tabel
                }
                continue;
            }

            // Tandai bahwa pembacaan data karyawan sudah dimulai
            $hasStarted = true;
            $rowKosongBerturut = 0;

            $colBadge   = Coordinate::stringFromColumnIndex($this->kolomMap['badge'] + $offsetKolom);
            $colJabatan = Coordinate::stringFromColumnIndex($this->kolomMap['jabatan'] + $offsetKolom);

            $badge   = trim((string) $sheet->getCell($colBadge . $row)->getCalculatedValue());
            $jabatan = trim((string) $sheet->getCell($colJabatan . $row)->getCalculatedValue());

            // Cek duplikat spesifik berdasarkan nama periode + id_rig + no_urut
            $existing = $slipModel->getByPeriodeRigUrut($namaPeriode, $idRigSelect, $noUrut);
            if ($existing) {
                $hasilDuplikat[] = "No.$noUrut - $nama - sudah ada di periode & rig ini, dilewati";
                continue;
            }

            $payroll = [];
            foreach ($this->kolomMap as $field => $colNum) {
                if (in_array($field, ['no_urut', 'nama', 'jabatan', 'badge'])) continue;
                $cLetter = Coordinate::stringFromColumnIndex($colNum + $offsetKolom);
                $val = $sheet->getCell($cLetter . $row)->getCalculatedValue();
                $payroll[$field] = is_numeric($val) ? (float)$val : 0;
            }
            $payroll['total_penghasilan'] = $payroll['gaji_pokok'] + $payroll['tunjangan_jabatan']
                + $payroll['tunjangan_transport'] + $payroll['tunjangan_perumahan'] + $payroll['uang_makan']
                + $payroll['rot'] + $payroll['total_over_time'] + $payroll['uang_makan_ot'];
            $payroll['total_potongan'] = $payroll['pph21'] + $payroll['bpjs_jht'] + $payroll['bpjs_pensiun']
                + $payroll['bpjs_kesehatan'] + $payroll['potongan_sertifikasi'] + $payroll['potongan_pinjaman']
                + $payroll['potongan_alpha'];
            $payroll['gaji_bersih'] = $payroll['total_penghasilan'] - $payroll['total_potongan'] + $payroll['koreksi_bulan_lalu'];

            // Prioritaskan hasil final dari sheet pro-rate HANYA jika no_urut DAN nama cocok.
            $kunciNama = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $nama));
            $kunciProrate = $this->kunciUrut($noUrut) . '_' . $kunciNama;
            if (isset($dataProrate[$kunciProrate])) {
                $prorateItem = $dataProrate[$kunciProrate];
                foreach ($prorateItem as $pK => $pV) {
                    if ($pK === 'sumber_perhitungan') {
                        $payroll[$pK] = $pV;
                        continue;
                    }
                    if (is_numeric($pV) && (float)$pV > 0) {
                        $payroll[$pK] = (float)$pV;
                    }
                }
            }

            $crew = empty($badge) ? null : $crewModel->getByBadge($badge);
            $idCrew = $crew ? $crew['id_crew'] : null;

            $payroll['no_urut'] = $noUrut;
            $payroll['nama']    = $nama;
            $payroll['jabatan'] = $jabatan;
            $payroll['badge']   = $badge;

            $dataInsert = [
                'id_rig'          => $idRigSelect, // Rig ditentukan langsung dari form import
                'id_crew'         => $idCrew,
                'periode'         => $namaPeriode,
                'tanggal_mulai'   => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'no_urut'         => $noUrut,
                'nama'            => $nama,
                'jabatan'         => $jabatan,
                'badge_excel'     => $badge,
                'gaji_bersih'     => $payroll['gaji_bersih'],
                'data_json'       => json_encode($payroll),
            ];

            $slipModel->create($dataInsert);
            $hasilSukses[] = "No.$noUrut - $nama" . ($idCrew ? '' : ' (belum terhubung ke data crew)');
            $detailImport[] = [
                'no_urut'     => $noUrut,
                'nama'        => $nama,
                'jabatan'     => $jabatan,
                'badge'       => $badge,
                'gaji_bersih' => $payroll['gaji_bersih']
            ];
        }

        $rigInfo = $idRigSelect ? $rigModel->find($idRigSelect) : null;
        $kodeRigDisplay = $rigInfo ? ($rigInfo['nama_rig'] ?? $rigInfo['kode_rig']) : 'Belum Ditentukan';

        $this->view('payroll/import_result', [
            'namaPeriode'    => $namaPeriode,
            'idRigSelect'    => $idRigSelect,
            'kodeRig'        => $kodeRigDisplay,
            'detailImport'   => $detailImport,
            'sukses'         => $hasilSukses,
            'duplikat'       => $hasilDuplikat,
            'periodeTerbaru' => $namaPeriode,
            'idRigTerbaru'   => $idRigSelect,
        ]);
    }

    // TAMBAH RIG BARU LANGSUNG DARI HALAMAN IMPORT
    public function storeRig()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = trim($_POST['nama_rig'] ?? $_POST['kode_rig'] ?? '');

            if (!empty($input)) {
                $kode = preg_replace('/^rig\s+/i', '', $input);
                $nama = (stripos($input, 'Rig') === false) ? 'Rig ' . $input : $input;

                $rigModel = $this->model('RigModel');
                $rigModel->insert([
                    'kode_rig' => $kode,
                    'nama_rig' => $nama,
                    'status'   => 'aktif'
                ]);
                Helper::setFlash('success', "Rig baru ($nama) berhasil ditambahkan!");
            } else {
                Helper::setFlash('error', "Nama Rig wajib diisi!");
            }
        }
        $this->redirect('import/formImport');
    }

    // HAPUS RIG DARI HALAMAN IMPORT
    public function deleteRig($id = null)
    {
        if ($id) {
            $rigModel = $this->model('RigModel');
            $rig = $rigModel->find((int)$id);
            if ($rig) {
                $namaDisplay = !empty($rig['nama_rig']) ? $rig['nama_rig'] : 'Rig ' . $rig['kode_rig'];
                $rigModel->delete((int)$id);
                Helper::setFlash('success', "Rig ($namaDisplay) berhasil dihapus!");
            }
        }
        $this->redirect('import/formImport');
    }
}
