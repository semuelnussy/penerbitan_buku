<?php
session_start();

// Tampilkan error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Cek file database exists
$db_path = 'config/database.php';
if (!file_exists($db_path)) {
    die("Error: File database.php tidak ditemukan di path: " . $db_path . "<br>Path saat ini: " . getcwd());
}

require_once $db_path;

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Debug: Cek tabel exists
$tables = ['penulis', 'reviewer', 'editor', 'penerbitan'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 0) {
        die("Error: Tabel '$table' tidak ditemukan di database!");
    }
}

// Get statistics dengan error handling
$stats = [];
try {
    $result = $conn->query("SELECT COUNT(*) as total FROM penulis");
    if (!$result) throw new Exception($conn->error);
    $stats['penulis'] = $result->fetch_assoc()['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM reviewer");
    if (!$result) throw new Exception($conn->error);
    $stats['reviewer'] = $result->fetch_assoc()['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM editor");
    if (!$result) throw new Exception($conn->error);
    $stats['editor'] = $result->fetch_assoc()['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM penerbitan");
    if (!$result) throw new Exception($conn->error);
    $stats['penerbitan'] = $result->fetch_assoc()['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM penerbitan WHERE status='Diterima'");
    if (!$result) throw new Exception($conn->error);
    $stats['penerbitan_diterima'] = $result->fetch_assoc()['total'];

    $result = $conn->query("SELECT COUNT(*) as total FROM penerbitan WHERE status='Proses'");
    if (!$result) throw new Exception($conn->error);
    $stats['penerbitan_proses'] = $result->fetch_assoc()['total'];

} catch (Exception $e) {
    die("Error query: " . $e->getMessage());
}

// Get recent publications
$recent_penerbitan = $conn->query("
    SELECT p.*, pen.nama_penulis, r.nama_reviewer, e.nama_editor 
    FROM penerbitan p 
    LEFT JOIN penulis pen ON p.id_penulis = pen.id_penulis 
    LEFT JOIN reviewer r ON p.id_reviewer = r.id_reviewer 
    LEFT JOIN editor e ON p.id_editor = e.id_editor 
    ORDER BY p.created_at DESC LIMIT 5
");

if (!$recent_penerbitan) {
    die("Error query recent: " . $conn->error);
}

// Get monthly data for chart
$monthly_data = $conn->query("
    SELECT DATE_FORMAT(tanggal, '%Y-%m') as bulan, COUNT(*) as jumlah 
    FROM penerbitan 
    WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
    ORDER BY bulan
");

if (!$monthly_data) {
    die("Error query monthly: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Informasi Penerbitan Buku</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-text">
                    <h3>MITRA GROUP</h3>
                    <small>Sistem Penerbitan</small>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <span class="nav-label">Menu Utama</span>
                <ul>
                    <li class="active">
                        <a href="index.php">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <span class="nav-label">Data Master</span>
                <ul>
                    <li><a href="penulis.php"><i class="fas fa-user-edit"></i><span>Data Penulis</span></a></li>
                    <li><a href="reviewer.php"><i class="fas fa-user-check"></i><span>Data Reviewer</span></a></li>
                    <li><a href="editor.php"><i class="fas fa-user-tie"></i><span>Data Editor</span></a></li>
                </ul>
            </div>

            <div class="nav-section">
                <span class="nav-label">Proses</span>
                <ul>
                    <li><a href="penerbitan.php"><i class="fas fa-book"></i><span>Data Penerbitan</span></a></li>
                </ul>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User'); ?></span>
                <span class="user-role"><?php echo $_SESSION['level'] ?? 'Staff'; ?></span>
            </div>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="header">
            <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
            <div class="header-right">
                <span id="currentDate"></span>
            </div>
        </header>

        <div class="content">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Selamat datang di Sistem Informasi Penerbitan Buku</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon"><i class="fas fa-book-open"></i></div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['penerbitan']); ?></h3>
                        <p>Total Penerbitan</p>
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['penerbitan_diterima']); ?></h3>
                        <p>Buku Diterbitkan</p>
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['penerbitan_proses']); ?></h3>
                        <p>Dalam Proses</p>
                    </div>
                </div>

                <div class="stat-card info">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['penulis'] + $stats['reviewer'] + $stats['editor']); ?></h3>
                        <p>Total Personil</p>
                    </div>
                </div>
            </div>

            <!-- Detail Stats -->
            <div class="detail-stats">
                <div class="stat-item">
                    <i class="fas fa-user-edit"></i>
                    <span><?php echo number_format($stats['penulis']); ?> Penulis</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-user-check"></i>
                    <span><?php echo number_format($stats['reviewer']); ?> Reviewer</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-user-tie"></i>
                    <span><?php echo number_format($stats['editor']); ?> Editor</span>
                </div>
            </div>

            <!-- Recent Publications Table -->
            <div class="table-card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Penerbitan Terbaru</h3>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Judul Buku</th>
                                <th>Penulis</th>
                                <th>Reviewer</th>
                                <th>Editor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($row = $recent_penerbitan->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['judul_buku']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_penulis'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_reviewer'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_editor'] ?? '-'); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($row['status']); ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('currentDate').textContent = new Date().toLocaleDateString('id-ID', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
        
        document.getElementById('menuToggle').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>