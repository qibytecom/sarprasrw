<?php
/**
 * Ajukan Peminjaman - Warga
 * Halaman untuk mengajukan peminjaman sarpras
 */

session_start();

// Cek apakah user sudah login dan role warga
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'warga') {
    header('Location: ../auth/login.php');
    exit();
}

// Include database configuration
require_once '../config/database.php';

// Fungsi untuk mendapatkan sarpras
function getAllSarpras() {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM sarpras WHERE status = 'tersedia' ORDER BY nama ASC");
    return $stmt->fetchAll();
}

function getSarprasById($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM sarpras WHERE id = ? AND status = 'tersedia'");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function checkScheduleConflict($sarpras_id, $tanggal_pinjam, $tanggal_kembali) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as conflict_count FROM peminjaman p
        JOIN detail_peminjaman dp ON p.id = dp.peminjaman_id
        WHERE dp.sarpras_id = ? AND p.status IN ('menunggu', 'disetujui')
        AND (
            (p.tanggal_pinjam <= ? AND p.tanggal_kembali >= ?) OR
            (p.tanggal_pinjam <= ? AND p.tanggal_kembali >= ?) OR
            (p.tanggal_pinjam >= ? AND p.tanggal_kembali <= ?)
        )
    ");
    $stmt->execute([$sarpras_id, $tanggal_pinjam, $tanggal_kembali, $tanggal_pinjam, $tanggal_kembali, $tanggal_pinjam, $tanggal_kembali]);
    return $stmt->fetch()['conflict_count'] > 0;
}

function ajukanPeminjaman($user_id, $tanggal_pinjam, $tanggal_kembali, $sarpras_list) {
    $pdo = getDB();

    try {
        $pdo->beginTransaction();

        // Insert peminjaman
        $stmt1 = $pdo->prepare("INSERT INTO peminjaman (user_id, tanggal_pinjam, tanggal_kembali) VALUES (?, ?, ?)");
        $stmt1->execute([$user_id, $tanggal_pinjam, $tanggal_kembali]);
        $peminjaman_id = $pdo->lastInsertId();

        // Insert detail peminjaman
        $stmt2 = $pdo->prepare("INSERT INTO detail_peminjaman (peminjaman_id, sarpras_id, jumlah_pinjam) VALUES (?, ?, ?)");
        foreach ($sarpras_list as $sarpras) {
            $stmt2->execute([$peminjaman_id, $sarpras['id'], $sarpras['jumlah']]);

            // Check stock availability
            $stmt3 = $pdo->prepare("SELECT jumlah FROM sarpras WHERE id = ?");
            $stmt3->execute([$sarpras['id']]);
            $current_stock = $stmt3->fetch()['jumlah'];

            if ($sarpras['jumlah'] > $current_stock) {
                throw new Exception("Stok sarpras tidak mencukupi");
            }
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// Proses form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal_pinjam = $_POST['tanggal_pinjam'];
    $tanggal_kembali = $_POST['tanggal_kembali'];
    $sarpras_items = [];

    // Validasi tanggal
    $today = date('Y-m-d');
    if ($tanggal_pinjam < $today) {
        $message = 'Tanggal pinjam tidak boleh kurang dari hari ini!';
        $message_type = 'danger';
    } elseif ($tanggal_kembali < $tanggal_pinjam) {
        $message = 'Tanggal kembali tidak boleh kurang dari tanggal pinjam!';
        $message_type = 'danger';
    } else {
        // Parse sarpras dari form
        if (isset($_POST['sarpras_id'])) {
            foreach ($_POST['sarpras_id'] as $key => $sarpras_id) {
                $jumlah = (int)$_POST['jumlah'][$key];
                $sarpras = getSarprasById($sarpras_id);

                if ($sarpras) {
                    // Check stock
                    if ($jumlah > $sarpras['jumlah']) {
                        $message = "Jumlah pinjam untuk {$sarpras['nama']} melebihi stok tersedia!";
                        $message_type = 'danger';
                        break;
                    }

                    // Check schedule conflict
                    if (checkScheduleConflict($sarpras_id, $tanggal_pinjam, $tanggal_kembali)) {
                        $message = "Jadwal untuk {$sarpras['nama']} bentrok dengan peminjaman lain!";
                        $message_type = 'danger';
                        break;
                    }

                    $sarpras_items[] = [
                        'id' => $sarpras_id,
                        'jumlah' => $jumlah
                    ];
                }
            }

            if (empty($message) && !empty($sarpras_items)) {
                if (ajukanPeminjaman($_SESSION['user_id'], $tanggal_pinjam, $tanggal_kembali, $sarpras_items)) {
                    $message = 'Peminjaman berhasil diajukan! Menunggu persetujuan admin.';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal mengajukan peminjaman!';
                    $message_type = 'danger';
                }
            }
        } else {
            $message = 'Pilih minimal satu sarpras!';
            $message_type = 'warning';
        }
    }
}

// Ambil data sarpras
$sarpras_list = getAllSarpras();

// Pre-select sarpras if ID is provided in URL
$selected_sarpras = null;
if (isset($_GET['sarpras_id'])) {
    $selected_sarpras = getSarprasById($_GET['sarpras_id']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Peminjaman - Warga SARPRAS RW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-building me-2"></i>SARPRAS RW
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="ajukan_peminjaman.php"><i class="fas fa-plus-circle me-1"></i>Ajukan Peminjaman</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="riwayat.php"><i class="fas fa-history me-1"></i>Riwayat</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['nama']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Ajukan Peminjaman Sarpras</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <!-- Tanggal Pinjam dan Kembali -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="tanggal_pinjam" class="form-label">Tanggal Pinjam *</label>
                                    <input type="date" class="form-control" id="tanggal_pinjam" name="tanggal_pinjam"
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="tanggal_kembali" class="form-label">Tanggal Kembali *</label>
                                    <input type="date" class="form-control" id="tanggal_kembali" name="tanggal_kembali" required>
                                </div>
                            </div>

                            <!-- Pilih Sarpras -->
                            <div class="mb-4">
                                <h6>Pilih Sarpras yang Ingin Dipinjam:</h6>
                                <div class="row">
                                    <?php foreach ($sarpras_list as $sarpras): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <?php if ($sarpras['foto']): ?>
                                                        <img src="../assets/img/<?php echo htmlspecialchars($sarpras['foto']); ?>"
                                                             alt="Foto Sarpras" class="img-fluid mb-2" style="height: 120px; object-fit: cover;">
                                                    <?php endif; ?>
                                                    <h6 class="card-title"><?php echo htmlspecialchars($sarpras['nama']); ?></h6>
                                                    <p class="card-text small text-muted mb-2">
                                                        Kategori: <?php echo htmlspecialchars($sarpras['kategori']); ?><br>
                                                        Stok: <?php echo $sarpras['jumlah']; ?> | Kondisi: <?php echo ucfirst($sarpras['kondisi']); ?>
                                                    </p>
                                                    <div class="form-check">
                                                        <input class="form-check-input sarpras-checkbox" type="checkbox"
                                                               id="sarpras_<?php echo $sarpras['id']; ?>" name="sarpras_id[]"
                                                               value="<?php echo $sarpras['id']; ?>"
                                                               <?php echo ($selected_sarpras && $selected_sarpras['id'] == $sarpras['id']) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="sarpras_<?php echo $sarpras['id']; ?>">
                                                            Pilih Sarpras Ini
                                                        </label>
                                                    </div>
                                                    <div class="mt-2 jumlah-input" style="display: none;">
                                                        <label class="form-label small">Jumlah Pinjam:</label>
                                                        <input type="number" class="form-control form-control-sm"
                                                               name="jumlah[]" min="1" max="<?php echo $sarpras['jumlah']; ?>"
                                                               value="1" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Ajukan Peminjaman
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary btn-lg ms-2">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        // Toggle jumlah input when checkbox is checked
        document.querySelectorAll('.sarpras-checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const jumlahInput = this.closest('.card-body').querySelector('.jumlah-input');
                if (this.checked) {
                    jumlahInput.style.display = 'block';
                } else {
                    jumlahInput.style.display = 'none';
                }
            });
        });

        // Set minimum date for tanggal_kembali based on tanggal_pinjam
        document.getElementById('tanggal_pinjam').addEventListener('change', function() {
            document.getElementById('tanggal_kembali').min = this.value;
            if (document.getElementById('tanggal_kembali').value < this.value) {
                document.getElementById('tanggal_kembali').value = this.value;
            }
        });

        // Initialize jumlah inputs for pre-selected sarpras
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.sarpras-checkbox:checked').forEach(function(checkbox) {
                const jumlahInput = checkbox.closest('.card-body').querySelector('.jumlah-input');
                jumlahInput.style.display = 'block';
            });
        });
    </script>
</body>
</html>
