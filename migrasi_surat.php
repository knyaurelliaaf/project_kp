<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'app/config/database.php';

$database = new Database();
$conn = $database->connect();

echo "<h2>Migrasi Data Surat Keluar - PT.ADK-6</h2>";

$filePath = 'data/surat_keluar.csv';

if (!file_exists($filePath)) {
    die("<p style='color:red;'> File tidak ditemukan: {$filePath}</p>");
}

$handle = fopen($filePath, 'r');

// Baca header dengan delimiter ;
$headerLine = fgets($handle);
$header = str_getcsv($headerLine, "\t");
echo "<p>Header: " . implode(' | ', $header) . " (Jumlah: " . count($header) . ")</p>";

$total = 0;
$success = 0;
$skipped = 0;
$duplicates = 0;

while (($line = fgets($handle)) !== false) {
    $total++;
    
    $row = str_getcsv($line, "\t");
    
    if (count($row) < 7) {
        $skipped++;
        continue;
    }
    
    $no = trim($row[0] ?? '');
    $tanggal_moc_raw = trim($row[1] ?? '');
    $tahun = trim($row[2] ?? '');
    $bulan = trim($row[3] ?? '');
    $rig = trim($row[4] ?? '');
    $jenis_kode = trim($row[5] ?? '');
    $nomor_surat = trim($row[6] ?? '');
    $keterangan = trim($row[7] ?? '');
    $link_file = trim($row[8] ?? '');
    $nama_file = trim($row[9] ?? '');
    
    // Skip jika data tidak lengkap
    if (empty($rig) || empty($jenis_kode) || empty($nomor_surat)) {
        $skipped++;
        continue;
    }
    
    // Cek duplikasi nomor surat
    $check = $conn->prepare("SELECT id_surat FROM surat WHERE nomor_surat = ?");
    $check->bind_param("s", $nomor_surat);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $duplicates++;
        $check->close();
        continue; // Skip duplikat
    }
    $check->close();
    
    // Ambil id_rig
    $stmt = $conn->prepare("SELECT id_rig FROM rig WHERE kode_rig = ?");
    $stmt->bind_param("s", $rig);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        $skipped++;
        continue;
    }
    $id_rig = $result->fetch_assoc()['id_rig'];
    
    // Ambil id_jenis
    $stmt = $conn->prepare("SELECT id_jenis FROM jenis_surat WHERE kode = ?");
    $stmt->bind_param("s", $jenis_kode);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        $skipped++;
        continue;
    }
    $id_jenis = $result->fetch_assoc()['id_jenis'];
    
    // Konversi tanggal
    $tanggal_moc = null;
    if (!empty($tanggal_moc_raw)) {
        $tgl = str_replace('/', '-', $tanggal_moc_raw);
        $timestamp = strtotime($tgl);
        if ($timestamp) {
            $tanggal_moc = date('Y-m-d', $timestamp);
        }
    }
    
    // INSERT
    $id_user = 1;
    $stmt = $conn->prepare("INSERT INTO surat (id_rig, id_jenis, id_user, nomor_surat, tahun, bulan, no_urut, tanggal_moc, keterangan, link_file, nama_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisiiissss", $id_rig, $id_jenis, $id_user, $nomor_surat, $tahun, $bulan, $no, $tanggal_moc, $keterangan, $link_file, $nama_file);
    
    if ($stmt->execute()) {
        $success++;
    } else {
        $skipped++;
    }
}

fclose($handle);

echo "<hr>";
echo "<p><strong> Total baris di CSV: {$total}</strong></p>";
echo "<p style='color:green;'><strong> Berhasil diimport: {$success}</strong></p>";
echo "<p style='color:orange;'><strong> Duplikat (skip): {$duplicates}</strong></p>";
echo "<p style='color:red;'><strong> Skip lainnya: {$skipped}</strong></p>";

// Cek hasil
$result = $conn->query("SELECT COUNT(*) as total FROM surat");
$totalSurat = $result->fetch_assoc()['total'];
echo "<p><strong> Total surat di database: {$totalSurat}</strong></p>";

$conn->close();
echo "<h2 style='color:green;'> Migrasi Surat Selesai!</h2>";
?>