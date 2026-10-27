<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

// Validasi method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method tidak diizinkan"]);
    exit;
}

// Query statistik produk
$query_total    = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM product");
$query_tersedia = mysqli_query($koneksi, "SELECT COUNT(*) AS tersedia FROM product WHERE LOWER(status) = 'tersedia'");
$query_habis    = mysqli_query($koneksi, "SELECT COUNT(*) AS habis FROM product WHERE LOWER(status) = 'habis'");

$total    = mysqli_fetch_assoc($query_total)['total'] ?? 0;
$tersedia = mysqli_fetch_assoc($query_tersedia)['tersedia'] ?? 0;
$habis    = mysqli_fetch_assoc($query_habis)['habis'] ?? 0;

// Response JSON
echo json_encode([
    "status" => "success",
    "data" => [
        "total_produk"   => (int)$total,
        "produk_tersedia"=> (int)$tersedia,
        "produk_habis"   => (int)$habis
    ]
], JSON_PRETTY_PRINT);
?>