<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'app/config/database.php';

$database = new Database();
$conn = $database->connect();

echo "<h2>Migrasi Data K3 Crew - PT Sigma Energi Indonesia</h2>";

$files = [
    'GW-336' => 'data/crew_gw336.csv',
    'GW-337' => 'data/crew_gw337.csv',
    'GW-338' => 'data/crew_gw338.csv',
    'GW-339' => 'data/crew_gw339.csv',
];

$totalAll = 0;

foreach ($files as $kode_rig => $filePath) {
    echo "<hr><h3>Proses Rig: {$kode_rig}</h3>";
    
    if (!file_exists($filePath)) {
        echo "<p style='color:red;'> File tidak ditemukan: {$filePath}</p>";
        continue;
    }
    
    // Ambil id_rig
    $stmt = $conn->prepare("SELECT id_rig FROM rig WHERE kode_rig = ?");
    $stmt->bind_param("s", $kode_rig);
    $stmt->execute();
    $id_rig = $stmt->get_result()->fetch_assoc()['id_rig'];
    
    echo "<p>id_rig: {$id_rig}</p>";
    
    // Baca CSV
    $rows = array_map('str_getcsv', file($filePath));
    $delimiter = ';';
    
    // Parse ulang dengan delimiter ;
    $handle = fopen($filePath, 'r');
    
    // Skip baris judul "MANPOWER..."
    $firstLine = fgets($handle);
    echo "<p>Baris 1: " . htmlspecialchars($firstLine) . "</p>";
    
    // Baca header
    $headerLine = fgets($handle);
    $header = str_getcsv($headerLine, ';');
    echo "<p>Header: " . implode(' | ', $header) . "</p>";
    
    $total = 0;
    $success = 0;
    $skipped = 0;
    
    while (($line = fgets($handle)) !== false) {
        $total++;
        
        // Parse baris
        $row = str_getcsv($line, ';');
        
        // Skip jika baris kosong atau kurang dari 5 kolom
        if (count($row) < 5 || empty(trim($row[1] ?? ''))) {
            $skipped++;
            continue;
        }
        
        $nama = trim($row[1] ?? '');      // NAME
        $posisi = trim($row[2] ?? '');    // POSITION
        $no_badge = trim($row[3] ?? '');  // NO BADGE
        $badge_exp = trim($row[4] ?? ''); // BADGE EXPIRED
        $last_mcu = trim($row[6] ?? '');  // LAST MCU DATE
        $derajat = trim($row[8] ?? '');   // Status Derajat Kesehatan
        $next_mcu = trim($row[9] ?? '');  // NEXT MCU DUE DATE PLAN
        $last_pkwt = trim($row[10] ?? '');// LAST PKWT DATE
        $cert1 = trim($row[12] ?? '');    // CERTIFICATE
        $cert1_exp = trim($row[13] ?? '');// EXPIRED I
        $cert2 = trim($row[15] ?? '');    // IADC/TKBTII
        $cert2_exp = trim($row[16] ?? '');// EXPIRED II
        
        if (empty($nama)) {
            $skipped++;
            continue;
        }
        
        // 1. INSERT CREW
        $stmt = $conn->prepare("INSERT INTO crew (id_rig, nama, posisi) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_rig, $nama, $posisi);
        if (!$stmt->execute()) {
            echo "<p style='color:red;'> Gagal insert crew: {$nama} - " . $stmt->error . "</p>";
            continue;
        }
        $id_crew = $conn->insert_id;
        
        // 2. INSERT BADGE
        if (!empty($no_badge) || !empty($badge_exp)) {
            $tgl_exp = !empty($badge_exp) ? date('Y-m-d', strtotime(str_replace('/', '-', $badge_exp))) : null;
            $stmt = $conn->prepare("INSERT INTO badge (id_crew, nomor_badge, tanggal_expired) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $id_crew, $no_badge, $tgl_exp);
            $stmt->execute();
        }
        
        // 3. INSERT MCU
        if (!empty($last_mcu) || !empty($derajat) || !empty($next_mcu)) {
            $tgl_mcu = !empty($last_mcu) ? date('Y-m-d', strtotime(str_replace('/', '-', $last_mcu))) : null;
            $next = !empty($next_mcu) ? date('Y-m-d', strtotime(str_replace('/', '-', $next_mcu))) : null;
            $der = in_array($derajat, ['P1', 'P2', 'P3', 'P4']) ? $derajat : null;
            
            $stmt = $conn->prepare("INSERT INTO mcu (id_crew, tanggal_mcu, derajat_kesehatan, tanggal_mcu_berikutnya) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $id_crew, $tgl_mcu, $der, $next);
            $stmt->execute();
        }
        
        // 4. INSERT PKWT
        if (!empty($last_pkwt)) {
            $tgl_pkwt = date('Y-m-d', strtotime(str_replace('/', '-', $last_pkwt)));
            $stmt = $conn->prepare("INSERT INTO pkwt (id_crew, tanggal_berakhir) VALUES (?, ?)");
            $stmt->bind_param("is", $id_crew, $tgl_pkwt);
            $stmt->execute();
        }
        
        // 5. INSERT SERTIFIKAT 1
        if (!empty($cert1) && !empty($cert1_exp)) {
            $tgl_exp1 = date('Y-m-d', strtotime(str_replace('/', '-', $cert1_exp)));
            $stmt = $conn->prepare("INSERT INTO sertifikat (id_crew, jenis, tanggal_expired) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $id_crew, $cert1, $tgl_exp1);
            $stmt->execute();
        }
        
        // 6. INSERT SERTIFIKAT 2
        if (!empty($cert2) && !empty($cert2_exp)) {
            $tgl_exp2 = date('Y-m-d', strtotime(str_replace('/', '-', $cert2_exp)));
            $stmt = $conn->prepare("INSERT INTO sertifikat (id_crew, jenis, tanggal_expired) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $id_crew, $cert2, $tgl_exp2);
            $stmt->execute();
        }
        
        $success++;
        echo "<p style='color:green;'>✓ {$nama} - {$posisi} (Badge: {$no_badge})</p>";
    }
    
    fclose($handle);
    $totalAll += $success;
    
    echo "<p><strong> Rig {$kode_rig}: {$success} crew berhasil (Total baris: {$total}, Skip: {$skipped})</strong></p>";
}

$conn->close();
echo "<hr><h2 style='color:green;'> MIGRASI SELESAI! Total: {$totalAll} crew</h2>";
echo "<p>Silakan cek database.</p>";
?>