<?php
require_once 'app/config/database.php';
$db = new Database();
$conn = $db->connect();

$files = [];

// 1. Badge
$res = mysqli_query($conn, "SELECT file FROM badge WHERE file IS NOT NULL AND file != ''");
while ($row = mysqli_fetch_assoc($res)) {
    $files[] = $row['file'];
}

// 2. MCU
$res = mysqli_query($conn, "SELECT file FROM mcu WHERE file IS NOT NULL AND file != ''");
while ($row = mysqli_fetch_assoc($res)) {
    $files[] = $row['file'];
}

// 3. Sertifikat
$res = mysqli_query($conn, "SELECT file FROM sertifikat WHERE file IS NOT NULL AND file != ''");
while ($row = mysqli_fetch_assoc($res)) {
    $files[] = $row['file'];
}

// 4. PKWT
$res = mysqli_query($conn, "SELECT file FROM pkwt WHERE file IS NOT NULL AND file != ''");
while ($row = mysqli_fetch_assoc($res)) {
    $files[] = $row['file'];
}

// 5. Crew photo
$res = mysqli_query($conn, "SELECT foto FROM crew WHERE foto IS NOT NULL AND foto != ''");
while ($row = mysqli_fetch_assoc($res)) {
    $files[] = $row['foto'];
}

$files = array_unique($files);
header('Content-Type: application/json');
echo json_encode($files);
