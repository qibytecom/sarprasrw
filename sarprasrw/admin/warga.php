<?php
/**
 * Manajemen Warga - Admin
 * Halaman untuk CRUD data warga
 */

session_start();

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Include database configuration
require_once '../config/database.php';

// Fungsi CRUD Warga
function getAllWarga() {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'warga' ORDER BY nama ASC");
    return $stmt->fetchAll();
}

function addWarga($nama, $alamat, $no_hp, $username, $password) {
    $pdo = getDB();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (nama, alamat, no_hp, username, password, role) VALUES (?, ?, ?, ?, ?, 'warga')");
    return $stmt->execute([$nama, $alamat, $no_hp, $username, $hashed_password]);
}

function updateWarga($id, $nama, $alamat, $no_hp, $username) {
    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE users SET nama = ?, alamat = ?, no_hp = ?, username = ? WHERE id = ? AND role = 'warga'");
    return $stmt->execute([$nama, $alamat, $no_hp, $username, $id]);
}

function deleteWarga($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'warga'");
    return $stmt->execute([$id]);
}

// Proses form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'add') {
        $nama = trim($_POST['nama']);
        $alamat = trim($_POST['alamat']);
        $no_hp = trim($_POST['no_hp']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (addWarga($nama, $alamat, $no_hp, $username, $password)) {
            $message = 'Warga berhasil ditambahkan!';
            $message_type = 'success';
        } else {
            $message = 'Gagal menambahkan warga!';
            $message_type = 'danger';
        }
    } elseif ($action == 'edit') {
        $id = (int)$_POST['id'];
        $nama = trim($_POST['nama']);
        $alamat = trim($_POST['alamat']);
        $no_hp = trim($_POST['no_hp']);
        $username = trim($_POST['username']);

        if (updateWarga($id, $nama, $alamat, $no_hp, $username)) {
            $message = 'Warga berhasil diupdate!';
            $message_type = 'success';
        } else {
            $message = 'Gagal mengupdate warga!';
            $message_type = 'danger';
        }
    } elseif ($action == 'delete') {
        $id = (int)$_POST['id'];

        if (deleteWarga($id)) {
            $message = 'Warga berhasil dihapus!';
            $message_type = 'success';
        } else {
            $message = 'Gagal menghapus warga!';
            $message_type = 'danger';
        }
    }
}

// Ambil data warga
$warga_list = getAllWarga();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Warga - Admin SARPRAS RW</title>
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
            <li><a href="sarpras.php"><i class="fas fa-boxes me-2"></i>Sarpras</a></li>
            <li class="active"><a href="warga.php"><i class="fas fa-users me-2"></i>Warga</a></li>
            <li><a href="peminjaman.php"><i class="fas fa-clipboard-list me-2"></i>Peminjaman</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-users me-2"></i>Manajemen Warga</h4>
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

            <!-- Add Warga Button -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5>Daftar Warga</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWargaModal">
                    <i class="fas fa-plus me-2"></i>Tambah Warga
                </button>
            </div>

            <!-- Warga Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Alamat</th>
                                    <th>No HP</th>
                                    <th>Username</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($warga_list as $warga): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($warga['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($warga['alamat']); ?></td>
                                        <td><?php echo htmlspecialchars($warga['no_hp']); ?></td>
                                        <td><?php echo htmlspecialchars($warga['username']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning me-1" onclick="editWarga(<?php echo $warga['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteWarga(<?php echo $warga['id']; ?>, '<?php echo htmlspecialchars($warga['nama']); ?>')">
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

    <!-- Add Warga Modal -->
    <div class="modal fade" id="addWargaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Tambah Warga</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="nama" name="nama" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="no_hp" class="form-label">No HP *</label>
                                    <input type="text" class="form-control" id="no_hp" name="no_hp" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat *</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
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

    <!-- Edit Warga Modal -->
    <div class="modal fade" id="editWargaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Warga</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_nama" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="edit_nama" name="nama" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_no_hp" class="form-label">No HP *</label>
                                    <input type="text" class="form-control" id="edit_no_hp" name="no_hp" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_alamat" class="form-label">Alamat *</label>
                            <textarea class="form-control" id="edit_alamat" name="alamat" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
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
                    <p>Apakah Anda yakin ingin menghapus warga <strong id="delete_name"></strong>?</p>
                    <p class="text-danger">Data yang dihapus tidak dapat dikembalikan!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" action="" style="display: inline;">
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
        function editWarga(id) {
            fetch(`get_warga.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_nama').value = data.nama;
                    document.getElementById('edit_alamat').value = data.alamat;
                    document.getElementById('edit_no_hp').value = data.no_hp;
                    document.getElementById('edit_username').value = data.username;

                    const modal = new bootstrap.Modal(document.getElementById('editWargaModal'));
                    modal.show();
                })
                .catch(error => console.error('Error:', error));
        }

        function deleteWarga(id, name) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_name').textContent = name;

            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
    </script>
</body>
</html>
