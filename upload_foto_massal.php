<?php
require_once 'app/config/database.php';

$database = new Database();
$conn = $database->connect();

$folder = 'data/foto/';

if (!is_dir($folder)) {
    die("<h2>Folder data/foto/ tidak ditemukan!</h2><p>Buat folder: <code>project_kp/data/foto/</code> lalu taruh foto di sana.</p>");
}

$files = scandir($folder);

$success = 0;
$skipped = 0;

echo "<h2>Upload Massal Foto Crew</h2>";
echo "<p>Folder: {$folder} | File: " . (count($files) - 2) . "</p>";

foreach ($files as $file) {
    if ($file == '.' || $file == '..') continue;
    
    $path = $folder . $file;
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $nama = pathinfo($file, PATHINFO_FILENAME);
    
    // Cari crew berdasarkan nama
    $stmt = $conn->prepare("SELECT id_crew, nama FROM crew WHERE nama LIKE ?");
    $like = "%{$nama}%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $crew = $result->fetch_assoc();
        
        // Generate nama file baru
        $newName = 'crew_' . $crew['id_crew'] . '_' . time() . '.' . $ext;
        $dest = 'public/uploads/' . $newName;
        
        // Copy file
        if (copy($path, $dest)) {
            // Update database
            $stmt2 = $conn->prepare("UPDATE crew SET foto = ? WHERE id_crew = ?");
            $stmt2->bind_param("si", $newName, $crew['id_crew']);
            $stmt2->execute();
            
            echo "<p style='color:green;'> {$crew['nama']} → {$newName}</p>";
            $success++;
        } else {
            echo "<p style='color:red;'> Gagal copy: {$file}</p>";
            $skipped++;
        }
    } elseif ($result->num_rows > 1) {
        echo "<p style='color:orange;'>Nama ganda: {$nama} — " . $result->num_rows . " crew ditemukan. Skip.</p>";
        $skipped++;
    } else {
        echo "<p style='color:red;'>Tidak ditemukan: {$nama}</p>";
        $skipped++;
    }
}

echo "<hr>";
echo "<h3> Berhasil: {$success} |  Skip: {$skipped}</h3>";
echo "<p><a href='/project_kp/crew'>Kembali ke Data Crew</a></p>";
?>