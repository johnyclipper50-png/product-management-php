<?php
header('Content-Type: application/json');

$json_file = __DIR__ . '/data/products.json';

// Membaca data dari JSON
function getProducts() {
    global $json_file;
    if (!file_exists($json_file)) {
        file_put_contents($json_file, json_encode([]));
    }
    $data = file_get_contents($json_file);
    return json_decode($data, true) ?? [];
}

// Menyimpan data ke JSON
function saveProducts($data) {
    global $json_file;
    file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT));
}

// Validasi Token Statis sederhanan
function validateToken() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    // Token statis simulasi: "Bearer token-rahasia-123"
    if ($authHeader !== 'Bearer token-rahasia-123') {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Unauthorized. Token tidak valid."]);
        exit;
    }
}
?>