<?php
/**
 * Manajemen Sarpras - Admin
 * Halaman untuk CRUD sarpras
 */

session_start();

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Include database configuration
require_once '../config/database.php';

// Fungsi CRUD Sarpras
function getAllSarpras() {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM sarpras ORDER BY nama ASC");
    return $stmt->fetchAll();
}

function addSarpras($nama, $kategori, $jumlah, $kondisi, $status, $foto = null) {
    $pdo = getDB();
    $stmt = $pdo->prepare("INSERT INTO sarpras (nama, kategori, jumlah, kondisi, status, foto) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$nama, $kategori, $jumlah, $kondisi, $status, $foto]);
}

function updateSarpras($id, $nama, $kategori, $jumlah, $kondisi, $status, $foto = null) {
    $pdo = getDB();
    if ($foto) {
        $stmt = $pdo->prepare("UPDATE sarpras SET nama = ?, kategori = ?, jumlah = ?, kondisi = ?, status = ?, foto = ? WHERE id = ?");
        return $stmt->execute([$nama, $kategori, $jumlah, $kondisi, $status, $foto, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE sarpras SET nama = ?, kategori = ?, jumlah = ?, kondisi = ?, status = ? WHERE id = ?");
        return $stmt->execute([$nama, $kategori, $jumlah, $kondisi, $status, $id]);
    }
}

function deleteSarpras($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM sarpras WHERE id = ?");
    return $stmt->execute([$id]);
}

// Proses form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'add') {
        $nama = trim($_POST['nama']);
        $kategori = trim($_POST['kategori']);
        $jumlah = (int)$_POST['jumlah'];
        $kondisi = $_POST['kondisi'];
        $status = $_POST['status'];

        // Handle file upload
        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $target_dir = "../assets/img/";
            $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $file_name;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                $foto = $file_name;
            }
        }

        if (addSarpras($nama, $kategori, $jumlah, $kondisi, $status, $foto)) {
            $message = 'Sarpras berhasil ditambahkan!';
            $message_type = 'success';
        } else {
            $message = 'Gagal menambahkan sarpras!';
            $message_type = 'danger';
        }
    } elseif ($action == 'edit') {
        $id = (int)$_POST['id'];
        $nama = trim($_POST['nama']);
        $kategori = trim($_POST['kategori']);
        $jumlah = (int)$_POST['jumlah'];
        $kondisi = $_POST['kondisi'];
        $status = $_POST['status'];

        // Handle file upload
        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $target_dir = "../assets/img/";
            $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $file_name;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                $foto = $file_name;
            }
        }

        if (updateSarpras($id, $nama, $kategori, $jumlah, $kondisi, $status, $foto)) {
            $message = 'Sarpras berhasil diupdate!';
            $message_type = 'success';
        } else {
            $message = 'Gagal mengupdate sarpras!';
            $message_type = 'danger';
        }
    } elseif ($action == 'delete') {
        $id = (int)$_POST['id'];

        if (deleteSarpras($id)) {
            $message = 'Sarpras berhasil dihapus!';
            $message_type = 'success';
        } else {
            $message = 'Gagal menghapus sarpras!';
            $message_type = 'danger';
        }
    }
}

// Ambil data sarpras
$sarpras_list = getAllSarpras();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Sarpras - Admin SARPRAS RW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-building me-2"></i>SARPRAS RW</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
            <li class="active"><a href="sarpras.php"><i class="fas fa-boxes me-2"></i>Sarpras</a></li>
            <li><a href="warga.php"><i class="fas fa-users me-2"></i>Warga</a></li>
            <li><a href="peminjaman.php"><i class="fas fa-clipboard-list me-2"></i>Peminjaman</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-boxes me-2"></i>Manajemen Sarpras</h4>
                <div>
                    <span class="text-muted">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Add Sarpras Button -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5>Daftar Sarpras</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSarprasModal">
                    <i class="fas fa-plus me-2"></i>Tambah Sarpras
                </button>
            </div>

            <!-- Sarpras Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Foto</th>
                                    <th>Nama</th>
                                    <th>Kategori</th>
                                    <th>Jumlah</th>
                                    <th>Kondisi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($sarpras_list as $sarpras): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <?php if ($sarpras['foto']): ?>
                                                <img src="../assets/img/<?php echo htmlspecialchars($sarpras['foto']); ?>"
                                                     alt="Foto Sarpras" class="img-thumbnail" style="width: 50px; height: 50px;">
                                            <?php else: ?>
                                                <i class="fas fa-image fa-2x text-muted"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($sarpras['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($sarpras['kategori']); ?></td>
                                        <td><?php echo $sarpras['jumlah']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $sarpras['kondisi'] == 'baik' ? 'success' : ($sarpras['kondisi'] == 'rusak' ? 'danger' : 'warning'); ?>">
                                                <?php echo ucfirst($sarpras['kondisi']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $sarpras['status'] == 'tersedia' ? 'success' : ($sarpras['status'] == 'dipinjam' ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($sarpras['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning me-1" onclick="editSarpras(<?php echo $sarpras['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteSarpras(<?php echo $sarpras['id']; ?>, '<?php echo htmlspecialchars($sarpras['nama']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Sarpras Modal -->
    <div class="modal fade" id="addSarprasModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Tambah Sarpras</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Sarpras *</label>
                                    <input type="text" class="form-control" id="nama" name="nama" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kategori" class="form-label">Kategori *</label>
                                    <select class="form-select" id="kategori" name="kategori" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="Furniture">Furniture</option>
                                        <option value="Elektronik">Elektronik</option>
                                        <option value="Outdoor">Outdoor</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jumlah" class="form-label">Jumlah *</label>
                                    <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kondisi" class="form-label">Kondisi *</label>
                                    <select class="form-select" id="kondisi" name="kondisi" required>
                                        <option value="baik">Baik</option>
                                        <option value="rusak">Rusak</option>
                                        <option value="perlu_perbaikan">Perlu Perbaikan</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="tersedia">Tersedia</option>
                                        <option value="dipinjam">Dipinjam</option>
                                        <option value="rusak">Rusak</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="foto" class="form-label">Foto Sarpras</label>
                                    <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Sarpras Modal -->
    <div class="modal fade" id="editSarprasModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Sarpras</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_nama" class="form-label">Nama Sarpras *</label>
                                    <input type="text" class="form-control" id="edit_nama" name="nama" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_kategori" class="form-label">Kategori *</label>
                                    <select class="form-select" id="edit_kategori" name="kategori" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="Furniture">Furniture</option>
                                        <option value="Elektronik">Elektronik</option>
                                        <option value="Outdoor">Outdoor</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_jumlah" class="form-label">Jumlah *</label>
                                    <input type="number" class="form-control" id="edit_jumlah" name="jumlah" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_kondisi" class="form-label">Kondisi *</label>
                                    <select class="form-select" id="edit_kondisi" name="kondisi" required>
                                        <option value="baik">Baik</option>
                                        <option value="rusak">Rusak</option>
                                        <option value="perlu_perbaikan">Perlu Perbaikan</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_status" class="form-label">Status *</label>
                                    <select class="form-select" id="edit_status" name="status" required>
                                        <option value="tersedia">Tersedia</option>
                                        <option value="dipinjam">Dipinjam</option>
                                        <option value="rusak">Rusak</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_foto" class="form-label">Foto Sarpras (Opsional)</label>
                                    <input type="file" class="form-control" id="edit_foto" name="foto" accept="image/*">
                                </div>
                            </div>
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus sarpras <strong id="delete_name"></strong>?</p>
                    <p class="text-danger">Data yang dihapus tidak dapat dikembalikan!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        function editSarpras(id) {
            fetch(`get_sarpras.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_nama').value = data.nama;
                    document.getElementById('edit_kategori').value = data.kategori;
                    document.getElementById('edit_jumlah').value = data.jumlah;
                    document.getElementById('edit_kondisi').value = data.kondisi;
                    document.getElementById('edit_status').value = data.status;

                    const modal = new bootstrap.Modal(document.getElementById('editSarprasModal'));
                    modal.show();
                })
                .catch(error => console.error('Error:', error));
        }

        function deleteSarpras(id, name) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_name').textContent = name;

            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
    </script>
</body>
</html>
