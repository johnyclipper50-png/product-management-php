<?php
session_start();
require_once 'koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$message = '';
$error = '';

// Query Statistik Produk untuk Dashboard Card
$stat_total    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM product"))['total'] ?? 0;
$stat_tersedia = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS tersedia FROM product WHERE LOWER(status) = 'tersedia'"))['tersedia'] ?? 0;
$stat_habis    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS habis FROM product WHERE LOWER(status) = 'habis'"))['habis'] ?? 0;

// ==========================================
// 1. PROSES CRUD (CREATE, UPDATE, DELETE)
// ==========================================

// A. TAMBAH PRODUK (CREATE)
if (isset($_POST['action']) && $_POST['action'] === 'create') {
    $kode     = trim($_POST['kode']);
    $nama     = trim($_POST['nama_produk']);
    $deskripsi= trim($_POST['deskripsi']);
    $harga    = (int)$_POST['harga'];
    $kategori = trim($_POST['kategori']);
    $status   = trim($_POST['status']);

    $stmt = mysqli_prepare($koneksi, "INSERT INTO product (kode, nama_produk, deskripsi, harga, kategori, status) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssiss", $kode, $nama, $deskripsi, $harga, $kategori, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = "Produk berhasil ditambahkan!";
    } else {
        $error = "Gagal menambah produk: " . mysqli_error($koneksi);
    }
}

// B. EDIT PRODUK (UPDATE)
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $kode     = trim($_POST['kode']); // Primary Key / Identifier
    $nama     = trim($_POST['nama_produk']);
    $deskripsi= trim($_POST['deskripsi']);
    $harga    = (int)$_POST['harga'];
    $kategori = trim($_POST['kategori']);
    $status   = trim($_POST['status']);

    $stmt = mysqli_prepare($koneksi, "UPDATE product SET nama_produk=?, deskripsi=?, harga=?, kategori=?, status=? WHERE kode=?");
    mysqli_stmt_bind_param($stmt, "ssisss", $nama, $deskripsi, $harga, $kategori, $status, $kode);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = "Data produk berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui produk: " . mysqli_error($koneksi);
    }
}

// C. HAPUS PRODUK (DELETE)
if (isset($_GET['delete'])) {
    $kode = trim($_GET['delete']);
    
    $stmt = mysqli_prepare($koneksi, "DELETE FROM product WHERE kode = ?");
    mysqli_stmt_bind_param($stmt, "s", $kode);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: dashboard.php?msg=deleted");
        exit;
    } else {
        $error = "Gagal menghapus produk: " . mysqli_error($koneksi);
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $message = "Produk berhasil dihapus!";
}

// ==========================================
// 2. READ & PAGINATION & SEARCH
// ==========================================
$limit = 20;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Count Total
if (!empty($search)) {
    $stmt_total = mysqli_prepare($koneksi, "SELECT COUNT(*) as total FROM product WHERE nama_produk LIKE ? OR kategori LIKE ?");
    $param_search = "%{$search}%";
    mysqli_stmt_bind_param($stmt_total, "ss", $param_search, $param_search);
    mysqli_stmt_execute($stmt_total);
    $res_total = mysqli_stmt_get_result($stmt_total);
} else {
    $res_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM product");
}

$row_total  = mysqli_fetch_assoc($res_total);
$total_data = $row_total['total'];
$total_page = ceil($total_data / $limit);

// Fetch Data
if (!empty($search)) {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM product WHERE nama_produk LIKE ? OR kategori LIKE ? ORDER BY kode ASC LIMIT ? OFFSET ?");
    $param_search = "%{$search}%";
    mysqli_stmt_bind_param($stmt, "ssii", $param_search, $param_search, $limit, $start);
    mysqli_stmt_execute($stmt);
    $query = mysqli_stmt_get_result($stmt);
} else {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM product ORDER BY kode ASC LIMIT ? OFFSET ?");
    mysqli_stmt_bind_param($stmt, "ii", $limit, $start);
    mysqli_stmt_execute($stmt);
    $query = mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pengelolaan Produk</title>
    <!-- CSS Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-4 p-4 bg-white rounded shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Dashboard Pengelolaan Produk</h2>
        <div>
            <span class="me-3">Halo, <strong><?= htmlspecialchars($_SESSION['email']) ?></strong></span>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

  
     <!-- Widget Statistik Produk -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white shadow-sm">
            <div class="card-body text-center">
                <h6 class="card-title text-uppercase mb-2">Total Produk</h6>
                <h2 class="fw-bold mb-0"><?= number_format($stat_total, 0, ',', '.') ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body text-center">
                <h6 class="card-title text-uppercase mb-2">Produk Tersedia</h6>
                <h2 class="fw-bold mb-0"><?= number_format($stat_tersedia, 0, ',', '.') ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white shadow-sm">
            <div class="card-body text-center">
                <h6 class="card-title text-uppercase mb-2">Produk Habis</h6>
                <h2 class="fw-bold mb-0"><?= number_format($stat_habis, 0, ',', '.') ?></h2>
            </div>
        </div>
    </div>
</div>
  <!-- Form Pencarian & Tombol Tambah -->
    <div class="row mb-3">
        <div class="col-md-8">
            <form action="" method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Cari nama produk atau kategori..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">Cari</button>
                <?php if (!empty($search)): ?>
                    <a href="dashboard.php" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah">+ Tambah Produk</button>
        </div>
    </div>

    <!-- Tabel Produk -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Produk</th>
                    <th>Deskripsi</th>
                    <th>Harga</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th text-center style="width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($query) > 0): ?>
                    <?php $no = $start + 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($row['kode']) ?></strong></td>
                            <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                            <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                            <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($row['kategori']) ?></td>
                            <td>
                                <?php if (strtolower($row['status']) === 'tersedia'): ?>
                                    <span class="badge bg-success">Tersedia</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Tombol Edit (Memicu Modal) -->
                                <button class="btn btn-warning btn-sm me-1 btn-edit" 
                                    data-kode="<?= htmlspecialchars($row['kode']) ?>"
                                    data-nama="<?= htmlspecialchars($row['nama_produk']) ?>"
                                    data-deskripsi="<?= htmlspecialchars($row['deskripsi']) ?>"
                                    data-harga="<?= $row['harga'] ?>"
                                    data-kategori="<?= htmlspecialchars($row['kategori']) ?>"
                                    data-status="<?= htmlspecialchars($row['status']) ?>"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEdit">Edit</button>
                                
                                <!-- Tombol Hapus -->
                                <a href="dashboard.php?delete=<?= urlencode($row['kode']) ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">Data produk tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Navigasi Pagination -->
    <?php if ($total_page > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">&laquo; Prev</a></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_page; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_page): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next &raquo;</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- ==========================================
     MODAL TAMBAH PRODUK
     ========================================== -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="" method="POST">
        <input type="hidden" name="action" value="create">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Produk Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label>Kode Produk</label>
                <input type="text" name="kode" class="form-control" placeholder="Contoh: PRD0101" required>
            </div>
            <div class="mb-3">
                <label>Nama Produk</label>
                <input type="text" name="nama_produk" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="2" required></textarea>
            </div>
            <div class="mb-3">
                <label>Harga (Rp)</label>
                <input type="number" name="harga" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Kategori</label>
                <input type="text" name="kategori" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Status Ketersediaan</label>
                <select name="status" class="form-select" required>
                    <option value="tersedia">Tersedia</option>
                    <option value="habis">Habis</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ==========================================
     MODAL EDIT PRODUK
     ========================================== -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="" method="POST">
        <input type="hidden" name="action" value="update">
        <div class="modal-header">
          <h5 class="modal-title">Edit Data Produk</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label>Kode Produk</label>
                <input type="text" id="edit_kode" name="kode" class="form-control bg-light" readonly>
            </div>
            <div class="mb-3">
                <label>Nama Produk</label>
                <input type="text" id="edit_nama" name="nama_produk" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Deskripsi</label>
                <textarea id="edit_deskripsi" name="deskripsi" class="form-control" rows="2" required></textarea>
            </div>
            <div class="mb-3">
                <label>Harga (Rp)</label>
                <input type="number" id="edit_harga" name="harga" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Kategori</label>
                <input type="text" id="edit_kategori" name="kategori" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Status Ketersediaan</label>
                <select id="edit_status" name="status" class="form-select" required>
                    <option value="tersedia">Tersedia</option>
                    <option value="habis">Habis</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- JS Bootstrap & Script Autofill Modal Edit -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Autofill data ke Modal Edit saat tombol 'Edit' diklik
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('edit_kode').value = this.dataset.kode;
            document.getElementById('edit_nama').value = this.dataset.nama;
            document.getElementById('edit_deskripsi').value = this.dataset.deskripsi;
            document.getElementById('edit_harga').value = this.dataset.harga;
            document.getElementById('edit_kategori').value = this.dataset.kategori;
            document.getElementById('edit_status').value = this.dataset.status.toLowerCase();
        });
    });
</script>

</body>
</html>