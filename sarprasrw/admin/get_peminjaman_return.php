<?php
/**
 * Get Peminjaman Return Form
 * API untuk mendapatkan form pengembalian peminjaman
 */

session_start();

// Cek apakah user sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo 'Unauthorized';
    exit();
}

// Include database configuration
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT p.*, u.nama as nama_warga
        FROM peminjaman p
        JOIN users u ON p.user_id = u.id
        WHERE p.id = ? AND p.status = 'disetujui'
    ");
    $stmt->execute([$id]);
    $peminjaman = $stmt->fetch();

    if ($peminjaman) {
        // Get detail sarpras
        $stmt2 = $pdo->prepare("
            SELECT dp.*, s.nama as nama_sarpras, s.kategori
            FROM detail_peminjaman dp
            JOIN sarpras s ON dp.sarpras_id = s.id
            WHERE dp.peminjaman_id = ?
        ");
        $stmt2->execute([$id]);
        $details = $stmt2->fetchAll();

        // Generate HTML form
        $html = '
            <input type="hidden" name="action" value="return">
            <input type="hidden" name="id" value="' . $id . '">
            <div class="mb-3">
                <strong>Warga:</strong> ' . htmlspecialchars($peminjaman['nama_warga']) . '<br>
                <strong>Periode:</strong> ' . date('d/m/Y', strtotime($peminjaman['tanggal_pinjam'])) . ' - ' . date('d/m/Y', strtotime($peminjaman['tanggal_kembali'])) . '
            </div>
            <hr>
            <h6>Detail Sarpras yang Dikembalikan:</h6>';

        foreach ($details as $index => $detail) {
            $html .= '
                <div class="card mb-3">
                    <div class="card-body">
                        <h6>' . htmlspecialchars($detail['nama_sarpras']) . '</h6>
                        <small class="text-muted">Kategori: ' . htmlspecialchars($detail['kategori']) . ' | Jumlah Pinjam: ' . $detail['jumlah_pinjam'] . '</small>

                        <input type="hidden" name="sarpras_id[]" value="' . $detail['sarpras_id'] . '">

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Kondisi Kembali *</label>
                                <select class="form-select" name="kondisi_kembali[]" required>
                                    <option value="baik">Baik</option>
                                    <option value="rusak">Rusak</option>
                                    <option value="hilang">Hilang</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Catatan</label>
                                <textarea class="form-control" name="catatan[]" rows="2" placeholder="Catatan kondisi pengembalian..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>';
        }

        echo $html;
    } else {
        echo '<div class="alert alert-danger">Peminjaman tidak ditemukan atau belum disetujui</div>';
    }
} else {
    echo '<div class="alert alert-warning">ID peminjaman tidak valid</div>';
}
?>
