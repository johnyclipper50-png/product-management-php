<?php
require_once 'config.php';

// Validasi token untuk akses API produk
validateToken();

$method = $_SERVER['REQUEST_METHOD'];
$products = getProducts();

switch ($method) {
    // 1. READ (GET) - Daftar produk, pencarian & filter
    case 'GET':
        $search = $_GET['search'] ?? null;
        $status = $_GET['status'] ?? null;

        $result = array_filter($products, function($item) use ($search, $status) {
            $matchSearch = true;
            $matchStatus = true;

            if ($search) {
                $matchSearch = (stripos($item['name'], $search) !== false) || 
                              (stripos($item['category'], $search) !== false);
            }

            if ($status) {
                $matchStatus = (strtolower($item['status']) === strtolower($status));
            }

            return $matchSearch && $matchStatus;
        });

        echo json_encode([
            "status" => "success",
            "data" => array_values($result)
        ]);
        break;

    // 2. CREATE (POST) - Tambah Produk Baru
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['name'], $input['description'], $input['price'], $input['category'], $input['status'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
            break;
        }

        $newProduct = [
            "id" => count($products) > 0 ? end($products)['id'] + 1 : 1,
            "name" => $input['name'],
            "description" => $input['description'],
            "price" => (int)$input['price'],
            "category" => $input['category'],
            "status" => $input['status'] // 'tersedia' atau 'habis'
        ];

        $products[] = $newProduct;
        saveProducts($products);

        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Produk berhasil ditambahkan", "data" => $newProduct]);
        break;

    // 3. UPDATE (PUT) - Edit Data Produk
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "ID produk wajib diisi"]);
            break;
        }

        $foundIndex = -1;
        foreach ($products as $index => $item) {
            if ($item['id'] == $id) {
                $foundIndex = $index;
                break;
            }
        }

        if ($foundIndex !== -1) {
            $products[$foundIndex] = array_merge($products[$foundIndex], $input);
            saveProducts($products);
            echo json_encode(["status" => "success", "message" => "Produk berhasil diperbarui", "data" => $products[$foundIndex]]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Produk tidak ditemukan"]);
        }
        break;

    // 4. DELETE (DELETE) - Hapus Produk
    case 'DELETE':
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "ID produk wajib diisi"]);
            break;
        }

        $newProducts = array_filter($products, function($item) use ($id) {
            return $item['id'] != $id;
        });

        if (count($products) === count($newProducts)) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Produk tidak ditemukan"]);
        } else {
            saveProducts(array_values($newProducts));
            echo json_encode(["status" => "success", "message" => "Produk berhasil dihapus"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method tidak diizinkan"]);
        break;
}
?>