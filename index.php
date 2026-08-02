<?php
include "function.php";
include "cek.php";

// Deteksi penanda login sukses untuk memicu popup selamat datang
$show_welcome = false;
if (isset($_SESSION['login_success'])) {
    $show_welcome = true;
    unset($_SESSION['login_success']); 
}

// Ambil data statistik dari database secara aman
$query_total_stock = mysqli_query($conn, "SELECT SUM(stock) AS total FROM stock");
$data_stock = mysqli_fetch_assoc($query_total_stock);
$total_stock = isset($data_stock['total']) ? intval($data_stock['total']) : 0;

$query_jenis_barang = mysqli_query($conn, "SELECT COUNT(*) AS total FROM stock");
$data_jenis = mysqli_fetch_assoc($query_jenis_barang);
$total_jenis = isset($data_jenis['total']) ? intval($data_jenis['total']) : 0;

$query_barang_masuk = mysqli_query($conn, "SELECT SUM(qty) AS total FROM masuk");
$data_masuk = mysqli_fetch_assoc($query_barang_masuk);
$total_masuk = isset($data_masuk['total']) ? intval($data_masuk['total']) : 0;

$query_barang_keluar = mysqli_query($conn, "SELECT SUM(qty) AS total FROM keluar");
$data_keluar = mysqli_fetch_assoc($query_barang_keluar);
$total_keluar = isset($data_keluar['total']) ? intval($data_keluar['total']) : 0;

// Deteksi parameter tab aktif dari URL
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : '';

// Periksa Hak Akses Peran Akun Aktif
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$current_username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Portal Sistem Informasi Inventaris IPDN</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="icon" type="image/x-icon" href="assets/img/logo_ipdn.png">
</head>
<body>

    <!-- POPUP MODAL SELAMAT DATANG (NATIVE POPUP) -->
    <div class="welcome-modal-overlay" id="welcomeModal">
        <div class="welcome-modal-card">
            <div class="welcome-badge-icon">👋</div>
            <h3 class="welcome-modal-title">Selamat Datang, <?= esc($current_username); ?></h3>
            <p class="welcome-modal-text">Anda telah berhasil masuk ke Sistem Informasi Inventaris Barang IPDN dengan hak akses <strong><?= $is_admin ? 'Administrator' : 'Staf Biasa (Read-Only)'; ?></strong>.</p>
            <button type="button" class="welcome-close-btn" id="closeWelcomeBtn">Buka Dashboard</button>
        </div>
    </div>

    <!-- FLOATING TOP BUTTON -->
    <div class="floating-top-btn" id="scrollTopBtn">
        <i class="fas fa-chevron-up"></i>
    </div>

    <div class="dashboard-container">
        
        <!-- SAPAAN WAKTU DINAMIS ATAS KIRI -->
        <div class="greeting-container" id="greetingContainer">
            <span id="greetingText">Selamat Hari, <?= esc($current_username); ?></span>
        </div>

        <!-- CONTROLLER KANAN ATAS (THEME SWITCHER + LOGOUT) -->
        <div class="control-top-right">
            <div class="theme-switch-wrapper">
                <label class="theme-switch" for="checkboxTheme">
                    <input type="checkbox" id="checkboxTheme" />
                    <div class="slider">
                        <i class="fas fa-sun"></i>
                        <i class="fas fa-moon"></i>
                    </div>
                </label>
            </div>
            <a href="logout.php" class="logout-top-btn" id="logoutBtn">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>

        <!-- HEADER SEKTOR PROFIL IPDN -->
        <header class="header-section">
            <img src="assets/img/logo_ipdn.png" alt="Logo Utama IPDN" class="logo-ipdn" onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/2/27/Lambang_IPDN.png';">
            <h1 class="instansi-title">Institut Pemerintahan Dalam Negeri<br>(IPDN)</h1>
            <p class="instansi-desc">Pusat Pendidikan Tinggi Kepemerintahan di Lingkungan Kementerian Dalam Negeri Republik Indonesia</p>
            <p class="instansi-address"><i class="fas fa-map-marker-alt"></i> Kampus Utama Jatinangor: Jl. Raya Bandung-Sumedang KM. 20, Jatinangor, Kabupaten Sumedang, Jawa Barat</p>
        </header>

        <!-- CONTROLLER DROPDOWN FILTER
        <div class="filter-container">
            <select class="filter-select">
                <option value="all">Tampilkan Data: Semua Periode</option>
                <option value="2026" selected>Tampilkan Data: Tahun 2026</option>
            </select>
        </div> -->

        <!-- HERO CARD: STOK INTEGRAL -->
        <div class="hero-card">
            <div class="hero-label">
                <span>Total Stok Barang Tersedia</span>
                <span class="badge-status">Real-time</span>
            </div>
            <div class="hero-value">
                <?= esc(number_format($total_stock, 0, ',', '.')); ?> Unit
            </div>
        </div>

        <!-- TOMBOL UTAMA -->
        <div class="action-button-container">
            <div onclick="switchTab('stok')" class="action-link">
                <span class="icon-box">
                    <i class="fas fa-boxes"></i>
                </span>
                Lihat Persediaan Barang
            </div>
        </div>

        <!-- GRID KARTU INTERAKTIF NAVIGASI TAB -->
        <section class="stats-grid">
            
            <!-- CARD 1: JENIS BARANG -->
            <div id="card-stok" onclick="switchTab('stok')" class="stat-card">
                <div>
                    <div class="stat-card-header">
                        <div class="icon-container icon-blue">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="badge-percentage badge-neutral">Stok Barang</div>
                    </div>
                    <div class="stat-card-body">
                        <p class="stat-label">Jenis dan Data Barang</p>
                        <h3 class="stat-value"><?= esc($total_jenis); ?> Jenis</h3>
                    </div>
                </div>
                <div class="stat-card-footer">Lihat stok barang saat ini &rsaquo;</div>
            </div>

            <!-- CARD 2: BARANG MASUK -->
            <div id="card-masuk" onclick="switchTab('masuk')" class="stat-card">
                <div>
                    <div class="stat-card-header">
                        <div class="icon-container icon-gold">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="badge-percentage badge-up">Barang Masuk</div>
                    </div>
                    <div class="stat-card-body">
                        <p class="stat-label">Total Barang Masuk</p>
                        <h3 class="stat-value"><?= esc(number_format($total_masuk, 0, ',', '.')); ?> Unit</h3>
                    </div>
                </div>
                <div class="stat-card-footer">Catatan transaksi barang masuk &rsaquo;</div>
            </div>

            <!-- CARD 3: BARANG KELUAR -->
            <div id="card-keluar" onclick="switchTab('keluar')" class="stat-card">
                <div>
                    <div class="stat-card-header">
                        <div class="icon-container icon-light-blue">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <div class="badge-percentage badge-neutral">Barang Keluar</div>
                    </div>
                    <div class="stat-card-body">
                        <p class="stat-label">Total Barang Keluar</p>
                        <h3 class="stat-value"><?= esc(number_format($total_keluar, 0, ',', '.')); ?> Unit</h3>
                    </div>
                </div>
                <div class="stat-card-footer">Detail distribusi barang keluar &rsaquo;</div>
            </div>

        </section>

        <!-- --- AREA KERJA TERPADU --- -->
        <section class="management-area" id="management-area">

            <!-- Pesan Error Hak Akses Sisi Server -->
            <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-exclamation-triangle mr-2"></i> ERROR: Peran Anda tidak memiliki hak akses untuk mengubah data ini!
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- ==================== TAB 1: STOK BARANG ==================== -->
            <div id="section-stok" class="tab-content">
                <div class="card mb-5">
                    <div class="card-header">
                        <h4 class="font-weight-bold mb-0" style="color: var(--ipdn-blue-dark); font-size: 18px;"><i class="fas fa-boxes mr-2"></i> Manajemen Stok Barang</h4>
                        <div>
                            <!-- Sembunyikan Tombol Tambah untuk User Biasa -->
                            <?php if ($is_admin): ?>
                                <button type="button" class="btn btn-primary text-white mr-1" data-toggle="modal" data-target="#tambahmodal">
                                    <i class="fas fa-plus mr-1"></i> Tambah Barang
                                </button>
                            <?php endif; ?>
                            <a href="export_excel.php" class="btn btn-warning text-white">Export Excel</a>
                            <a href="export_pdf.php" target="_blank" class="btn btn-danger">Export PDF</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered dynamic-table" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Nomor</th>
                                        <th>Nama Barang</th>
                                        <th>Keterangan</th>
                                        <th>Stok Barang</th>
                                        <?php if ($is_admin): ?>
                                            <th>Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ambilsemuadatastock = mysqli_query($conn,"SELECT * FROM stock");
                                    $i = 1;
                                    while($data=mysqli_fetch_array($ambilsemuadatastock)){
                                        $namabarang = $data['namabarang'];
                                        $keterangan = $data['keterangan'];
                                        $stock = $data['stock'];
                                        $idb = $data['idbarang'];
                                    ?>
                                    <tr>
                                        <td><?=$i++; ?></td>
                                        <td><strong><?=esc($namabarang);?></strong></td>
                                        <td><?=esc($keterangan);?></td>
                                        <td><span class="badge badge-pill badge-primary px-3 py-2"><?=esc($stock);?> Unit</span></td>
                                        
                                        <!-- Sembunyikan Kolom Aksi untuk User Biasa -->
                                        <?php if ($is_admin): ?>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm text-white mr-1" data-toggle="modal" data-target="#edit<?=$idb;?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm text-white" data-toggle="modal" data-target="#delete<?=$idb;?>">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </td>
                                        <?php endif; ?>
                                    </tr>

                                    <?php if ($is_admin): ?>
                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="edit<?=$idb;?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content text-left">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Barang</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form method="post">
                                                        <div class="modal-body">
                                                            <label class="large mb-1">Nama Barang</label>
                                                            <input type="text" name="namabarang" value="<?=esc($namabarang);?>" class="form-control" required>
                                                            <br>
                                                            <label class="large mb-1">Keterangan</label>
                                                            <input type="text" name="keterangan" value="<?=esc($keterangan);?>" class="form-control" required>
                                                            <input type="hidden" name="idb" value="<?=$idb;?>">
                                                            <br>
                                                            <button type="submit" class="btn btn-warning" name="updatebarang"> Update </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="delete<?=$idb;?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content text-left">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Delete Barang</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form method="post">
                                                        <div class="modal-body">
                                                            Apakah yakin ingin menghapus barang <strong><?=esc($namabarang)?></strong> ?
                                                            <input type="hidden" name="idb" value="<?=$idb;?>">
                                                            <br><br>
                                                            <button type="submit" class="btn btn-danger" name="hapusbarang"> Hapus </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php }; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== TAB 2: BARANG MASUK ==================== -->
            <div id="section-masuk" class="tab-content">
                <div class="card mb-5">
                    <div class="card-header">
                        <h4 class="font-weight-bold mb-0" style="color: var(--ipdn-blue-dark); font-size: 18px;"><i class="fas fa-download mr-2"></i> Log Transaksi Barang Masuk</h4>
                        <div>
                            <?php if ($is_admin): ?>
                                <button type="button" class="btn btn-primary text-white mr-1" data-toggle="modal" data-target="#barangMasuk">
                                    <i class="fas fa-download mr-1"></i> Input Barang Masuk
                                </button>
                            <?php endif; ?>
                            <a href="exportmasuk_excel.php" class="btn btn-warning text-white">Export Excel</a>
                            <a href="exportmasuk_pdf.php" target="_blank" class="btn btn-danger">Export PDF</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered dynamic-table" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nama Barang</th>
                                        <th>Keterangan</th>
                                        <th>Jumlah Masuk</th>
                                        <?php if ($is_admin): ?>
                                            <th>Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ambilsemuadatastock = mysqli_query($conn,"SELECT m.idmasuk, m.idbarang, m.tanggal, m.keterangan, m.qty, s.namabarang FROM masuk m, stock s WHERE s.idbarang = m.idbarang");
                                    while($data=mysqli_fetch_array($ambilsemuadatastock)){
                                        $idm = $data['idmasuk'];
                                        $idb = $data['idbarang'];
                                        $tanggal = $data['tanggal'];
                                        $namabarang = $data['namabarang'];
                                        $qty = $data['qty'];
                                        $keterangan = $data['keterangan'];
                                    ?>
                                    <tr>
                                        <td><?=esc($tanggal);?></td>
                                        <td><strong><?=esc($namabarang);?></strong></td>
                                        <td><?=esc($keterangan);?></td>
                                        <td><span class="badge badge-pill badge-info px-3 py-2">+<?=esc($qty);?> Unit</span></td>
                                        
                                        <?php if ($is_admin): ?>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm text-white mr-1" data-toggle="modal" data-target="#editmasuk<?=$idm;?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm text-white" data-toggle="modal" data-target="#deletemasuk<?=$idm;?>">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </td>  
                                        <?php endif; ?>
                                    </tr>

                                    <?php if ($is_admin): ?>
                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editmasuk<?=$idm;?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content text-left">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Barang Masuk</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form method="post">
                                                        <div class="modal-body">
                                                            <label class="large mb-1">Keterangan</label>
                                                            <input type="text" name="keterangan" value="<?=esc($keterangan);?>" class="form-control" required>
                                                            <br>
                                                            <label class="large mb-1">Jumlah</label>
                                                            <input type="number" name="qty" value="<?=esc($qty);?>" class="form-control" required>
                                                            <input type="hidden" name="idb" value="<?=$idb;?>">
                                                            <input type="hidden" name="idm" value="<?=$idm;?>">
                                                            <br>
                                                            <button type="submit" class="btn btn-warning" name="updatebarangmasuk"> Update </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deletemasuk<?=$idm;?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content text-left">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Hapus Barang Masuk</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form method="post">
                                                        <div class="modal-body">
                                                            Apakah yakin ingin menghapus transaksi barang masuk <strong><?=esc($namabarang)?></strong> sebanyak <strong><?=esc($qty);?></strong>?
                                                            <input type="hidden" name="idb" value="<?=$idb;?>">
                                                            <input type="hidden" name="idm" value="<?=$idm;?>">
                                                            <input type="hidden" name="qty" value="<?=$qty;?>">
                                                            <br><br>
                                                            <button type="submit" class="btn btn-danger" name="hapusbarangmasuk"> Delete </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php }; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== TAB 3: BARANG KELUAR ==================== -->
            <div id="section-keluar" class="tab-content">
                <div class="card mb-5">
                    <div class="card-header">
                        <h4 class="font-weight-bold mb-0" style="color: var(--ipdn-blue-dark); font-size: 18px;"><i class="fas fa-warehouse mr-2"></i> Log Transaksi Barang Keluar</h4>
                        <div>
                            <?php if ($is_admin): ?>
                                <button type="button" class="btn btn-primary text-white mr-1" data-toggle="modal" data-target="#barangKeluar">
                                    <i class="fas fa-warehouse mr-1"></i> Input Barang Keluar
                                </button>
                            <?php endif; ?>
                            <a href="exportkeluar_excel.php" class="btn btn-warning text-white">Export Excel</a>
                            <a href="exportkeluar_pdf.php" target="_blank" class="btn btn-danger">Export PDF</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered dynamic-table" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nama Barang</th>
                                        <th>Jumlah Keluar</th>
                                        <th>Penerima</th>
                                        <?php if ($is_admin): ?>
                                            <th>Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ambilsemuadatastock = mysqli_query($conn,"SELECT k.idkeluar, k.idbarang, k.tanggal, k.penerima, k.qty, s.namabarang FROM keluar k, stock s WHERE s.idbarang = k.idbarang");
                                    while($data=mysqli_fetch_array($ambilsemuadatastock)){
                                        $idk = $data['idkeluar'];
                                        $idb = $data['idbarang'];
                                        $tanggal = $data['tanggal'];
                                        $namabarang = $data['namabarang'];
                                        $qty = $data['qty'];
                                        $penerima = $data['penerima'];
                                    ?>
                                    <tr>
                                        <td><?=esc($tanggal);?></td>
                                        <td><strong><?=esc($namabarang);?></strong></td>
                                        <td><span class="badge badge-pill badge-primary px-3 py-2">-<?=esc($qty);?> Unit</span></td>
                                        <td><?=esc($penerima);?></td>
                                        
                                        <?php if ($is_admin): ?>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm text-white mr-1" data-toggle="modal" data-target="#editkeluar<?=$idk;?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm text-white" data-toggle="modal" data-target="#deletekeluar<?=$idk;?>">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </td>  
                                        <?php endif; ?>
                                    </tr>

                                    <?php if ($is_admin): ?>
                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editkeluar<?=$idk;?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content text-left">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Barang Keluar</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form method="post">
                                                        <div class="modal-body">
                                                            <label class="large mb-1">Penerima</label>
                                                            <input type="text" name="penerima" value="<?=esc($penerima);?>" class="form-control" required>
                                                            <br>
                                                            <label class="large mb-1">Jumlah</label>
                                                            <input type="number" name="qty" value="<?=esc($qty);?>" class="form-control" required>
                                                            <input type="hidden" name="idb" value="<?=$idb;?>">
                                                            <input type="hidden" name="idk" value="<?=$idk;?>">
                                                            <br>
                                                            <button type="submit" class="btn btn-warning" name="updatebarangkeluar"> Update </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deletekeluar<?=$idk;?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content text-left">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Hapus Barang Keluar</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <form method="post">
                                                        <div class="modal-body">
                                                            Apakah yakin ingin menghapus data pengeluaran barang <strong><?=esc($namabarang)?></strong> sebanyak <strong><?=esc($qty);?></strong>?
                                                            <input type="hidden" name="idb" value="<?=$idb;?>">
                                                            <input type="hidden" name="idk" value="<?=$idk;?>">
                                                            <input type="hidden" name="qty" value="<?=$qty;?>">
                                                            <br><br>
                                                            <button type="submit" class="btn btn-danger" name="hapusbarangkeluar"> Delete </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php }; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </section>

        <!-- --- FOOTER PERPUSTAKAAN IPDN --- -->
        <footer class="dashboard-footer">
            Copyright &copy; 2026 Perpustakaan IPDN
        </footer>

    </div>

    <!-- Hanya Memuat Modul Modal Jika Akun Adalah Admin -->
    <?php if ($is_admin): ?>
        <!-- GLOBAL DIALOG MODAL TAMBAH -->
        <div class="modal fade" id="tambahmodal">
            <div class="modal-dialog" role="document">
                <div class="modal-content text-left">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Barang Baru</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="post">
                        <div class="modal-body">
                            <label class="large mb-1">Nama Barang</label>
                            <input type="text" name="namabarang" placeholder="Nama Barang" class="form-control" required>
                            <br>
                            <label class="large mb-1">Keterangan</label>
                            <input type="text" name="keterangan" placeholder="Keterangan Barang" class="form-control" required>
                            <br>
                            <label class="large mb-1">Jumlah Barang</label>
                            <input type="number" name="stock" class="form-control" placeholder="1" required>
                            <br>
                            <button type="submit" class="btn btn-primary" name="addnewbarang"> Submit </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- GLOBAL DIALOG MODAL INPUT MASUK -->
        <div class="modal fade" id="barangMasuk">
            <div class="modal-dialog">
                <div class="modal-content text-left">
                    <div class="modal-header">
                        <h5 class="modal-title">Input Barang Masuk</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form method="post">
                        <div class="modal-body">
                            <label class="large mb-1">Pilih Barang</label>
                            <select name="barangnya" class="form-control">
                                <?php 
                                    $ambildata = mysqli_query($conn,"SELECT * FROM stock");
                                    while($fetcharray = mysqli_fetch_array($ambildata)){
                                        $namabarangnya = $fetcharray['namabarang'];
                                        $idbarangnya = $fetcharray['idbarang'];
                                ?>
                                <option value="<?=$idbarangnya;?>"><?=$namabarangnya;?> </option>
                                <?php }; ?>
                            </select>
                            <br>
                            <label class="large mb-1">Keterangan / Penerima</label>
                            <input type="text" name="keterangan" class="form-control" placeholder="Penerima atau Detail Masuk" required>
                            <br>
                            <label class="large mb-1">Quantity</label>
                            <input type="number" name="qty" class="form-control" placeholder="0" required>
                            <br>
                            <button type="submit" class="btn btn-primary" name="barangmasuk"> Submit </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- GLOBAL DIALOG MODAL INPUT KELUAR -->
        <div class="modal fade" id="barangKeluar">
            <div class="modal-dialog">
                <div class="modal-content text-left">
                    <div class="modal-header">
                        <h5 class="modal-title">Input Barang Keluar</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form method="post">
                        <div class="modal-body">
                            <label class="large mb-1">Pilih Barang</label>
                            <select name="barangnya" class="form-control">
                                <?php 
                                    $ambildata = mysqli_query($conn,"SELECT * FROM stock");
                                    while($fetcharray = mysqli_fetch_array($ambildata)){
                                        $namabarangnya = $fetcharray['namabarang'];
                                        $idbarangnya = $fetcharray['idbarang'];
                                ?>
                                <option value="<?=$idbarangnya;?>"><?=$namabarangnya;?> </option>
                                <?php }; ?>
                            </select>
                            <br>
                            <label class="large mb-1">Penerima</label>
                            <input type="text" name="penerima" class="form-control" placeholder="Instansi / Unit Penerima" required>
                            <br>
                            <label class="large mb-1">Quantity</label>
                            <input type="number" name="qty" class="form-control" placeholder="0" required>
                            <br>
                            <button type="submit" class="btn btn-primary" name="addbarangkeluar"> Submit </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <!-- JS Dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>

    <!-- Passing PHP Sesi ke JavaScript secara Aman -->
    <script>
        window.PHP_FLAGS = {
            showWelcome: <?= json_encode($show_welcome); ?>,
            activeTab: <?= json_encode($active_tab); ?>,
            currentUsername: <?= json_encode($current_username); ?>
        };
    </script>
    <!-- Berkas Logika JS Eksternal -->
    <script src="js/dashboard.js"></script>
</body>
</html>