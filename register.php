<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi Keamanan: Hanya izinkan akses jika sesi superadmin aktif
if (!isset($_SESSION['superadmin']) || $_SESSION['superadmin'] !== true) {
    header("Location: login.php");
    exit();
}

require "function.php";

// --- PROSES 1: DAFTAR PENGGUNA BARU ---
if (isset($_POST['register'])) {
    $new_user = $_POST['username'] ?? '';
    $new_pass = $_POST['password'] ?? '';
    $new_role = $_POST['role'] ?? 'user';

    // Validasi apakah username sudah pernah terdaftar
    $stmt_check = mysqli_prepare($conn, "SELECT iduser FROM login WHERE username=?");
    mysqli_stmt_bind_param($stmt_check, "s", $new_user);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);
    $exists = mysqli_stmt_num_rows($stmt_check);
    mysqli_stmt_close($stmt_check);

    if ($exists > 0) {
        $error = "Username '{$new_user}' sudah terdaftar!";
    } else {
        // Simpan ke database menggunakan Prepared Statement
        $stmt_ins = mysqli_prepare($conn, "INSERT INTO login (username, password, role) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt_ins, "sss", $new_user, $new_pass, $new_role);
        
        if (mysqli_stmt_execute($stmt_ins)) {
            $success = "Pengguna baru '{$new_user}' berhasil didaftarkan!";
        } else {
            $error = "Gagal mendaftarkan pengguna baru.";
        }
        mysqli_stmt_close($stmt_ins);
    }
}

// --- PROSES 2: HAPUS PENGGUNA DENGAN PROTEKSI SUPERADMIN ---
if (isset($_GET['delete_user'])) {
    $id_to_delete = intval($_GET['delete_user']);
    
    // Proteksi Backend: Cek terlebih dahulu apakah ID yang akan dihapus memiliki username 'superadmin'
    $stmt_check_admin = mysqli_prepare($conn, "SELECT username FROM login WHERE iduser=?");
    mysqli_stmt_bind_param($stmt_check_admin, "i", $id_to_delete);
    mysqli_stmt_execute($stmt_check_admin);
    $res_check_admin = mysqli_stmt_get_result($stmt_check_admin);
    $row_check_admin = mysqli_fetch_assoc($res_check_admin);
    mysqli_stmt_close($stmt_check_admin);

    if ($row_check_admin && $row_check_admin['username'] === 'superadmin') {
        // Blokir penghapusan secara mutlak
        $error = "Akun 'superadmin' dilindungi oleh sistem keamanan dan tidak dapat dihapus!";
    } else {
        $stmt_del = mysqli_prepare($conn, "DELETE FROM login WHERE iduser=?");
        mysqli_stmt_bind_param($stmt_del, "i", $id_to_delete);
        
        if (mysqli_stmt_execute($stmt_del)) {
            $success = "Pengguna berhasil dihapus dari sistem.";
        } else {
            $error = "Gagal menghapus pengguna.";
        }
        mysqli_stmt_close($stmt_del);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>IPDN - Registrasi Staf/Admin Baru</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <!-- Relasi CSS dengan login.css -->
    <link rel="stylesheet" href="css/login.css">
    
    <style>
        body {
            justify-content: flex-start;
            padding-top: 50px;
        }
        .register-container {
            width: 100%;
            max-width: 1050px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .columns-wrapper {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 30px;
            width: 100%;
            align-items: start;
        }
        .list-card {
            background-color: #ffffff;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 12px 30px rgba(0, 40, 85, 0.05);
            border-top: 5px solid var(--ipdn-gold);
            min-height: 480px;
        }
        .table-custom {
            font-size: 13px;
        }
        .table-custom th {
            color: var(--ipdn-navy);
            font-weight: 700;
            background-color: #f1f5f9;
            border: none;
        }
        .table-custom td {
            vertical-align: middle;
        }
        .role-badge {
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            text-transform: uppercase;
        }
        .role-admin {
            background-color: #e0f2fe;
            color: #0369a1;
        }
        .role-user {
            background-color: #f1f5f9;
            color: #475569;
        }
        
        /* Tombol Halaman Login */
        .login-btn-header {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #ffffff;
            border: 2px solid var(--ipdn-gold);
            color: var(--ipdn-navy) !important;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            transition: all 0.2s;
        }
        .login-btn-header:hover {
            background-color: var(--ipdn-gold-light);
            text-decoration: none;
            transform: translateY(-1px);
        }

        .logout-superadmin {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--ipdn-navy);
            color: #ffffff !important;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            transition: all 0.2s;
            border: 1px solid transparent;
        }
        .logout-superadmin:hover {
            background-color: #ef4444;
            color: #ffffff !important;
            text-decoration: none;
            transform: translateY(-1px);
        }
        @media (max-width: 900px) {
            .columns-wrapper {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <div class="register-container">
        
        <!-- HEADER KONTROL UTAMA -->
        <div class="d-flex justify-content-between align-items-center w-100 mb-3 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <img src="assets/img/logo_ipdn.png" alt="Logo IPDN" style="max-width: 45px;">
                <h4 class="mb-0 font-weight-bold" style="color: var(--ipdn-navy);">Superadmin Portal</h4>
            </div>
            <!-- Tombol Navigasi Berdampingan -->
            <div class="d-flex align-items-center">
                <a href="login.php" class="login-btn-header"><i class="fas fa-sign-in-alt"></i> Halaman Login</a>
                <a href="logout.php" class="logout-superadmin ml-2"><i class="fas fa-sign-out-alt"></i> Keluar Superadmin</a>
            </div>
        </div>

        <!-- NOTIFIKASI DINAMIS -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" style="border-radius: 12px; font-weight: 600; font-size: 13px;">
                <i class="fas fa-exclamation-circle mr-2"></i> <?= esc($error); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success" style="border-radius: 12px; font-weight: 600; font-size: 13px;">
                <i class="fas fa-check-circle mr-2"></i> <?= esc($success); ?>
            </div>
        <?php endif; ?>

        <div class="columns-wrapper">
            
            <!-- KOLOM KIRI: FORM REGISTRASI -->
            <section class="login-card m-0" style="width: 100%;">
                <div class="welcome-section">
                    <h2 class="welcome-title">Registrasi Akun 👋</h2>
                    <p class="welcome-desc">Daftarkan administrator atau staf baru ke dalam sistem</p>
                </div>

                <form method="post">
                    <!-- INPUT USERNAME -->
                    <div class="form-group">
                        <label class="form-label">Username Baru</label>
                        <div class="input-container">
                            <span class="input-icon"><i class="fas fa-user"></i></span>
                            <input class="input-field" name="username" type="text" placeholder="Masukkan username baru" required />
                        </div>
                    </div>

                    <!-- INPUT PASSWORD -->
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-container">
                            <span class="input-icon"><i class="fas fa-lock"></i></span>
                            <input class="input-field" name="password" type="text" placeholder="Masukkan password" required />
                        </div>
                    </div>

                    <!-- PILIH PERAN/ROLE -->
                    <div class="form-group">
                        <label class="form-label">Peran Pengguna (Role)</label>
                        <select class="form-control" name="role" style="border-radius: 12px !important; height: auto; padding: 12px 15px;" required>
                            <option value="user" selected>User Biasa (Read-Only & Export)</option>
                            <option value="admin">Administrator (Akses Modifikasi Penuh)</option>
                        </select>
                    </div>

                    <!-- TOMBOL REGISTRASI -->
                    <button type="submit" name="register" class="submit-btn mt-3">
                        <i class="fas fa-user-plus"></i> Daftarkan Pengguna
                    </button>
                </form>
            </section>

            <!-- KOLOM KANAN: LIST PENGGUNA TERDAFTAR -->
            <section class="list-card">
                <h4 class="font-weight-bold mb-3" style="color: var(--ipdn-navy); font-size: 18px;"><i class="fas fa-users mr-2"></i> Daftar Pengguna Sistem</h4>
                <p class="text-muted mb-4" style="font-size: 13px;">Berikut adalah daftar akun yang memiliki akses ke sistem. Akun <code>superadmin</code> dikunci di urutan pertama dan dilindungi oleh sistem.</p>
                
                <div class="table-responsive">
                    <table class="table table-striped table-custom">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Hak Akses / Role</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query Urutan Khusus: superadmin selalu paling atas (No.1), data baru diletakkan paling bawah (iduser ASC)
                            $query_users = "SELECT * FROM login ORDER BY CASE WHEN username = 'superadmin' THEN 0 ELSE 1 END, iduser ASC";
                            $get_users = mysqli_query($conn, $query_users);
                            $no = 1;
                            while ($row = mysqli_fetch_array($get_users)) {
                                $id_user = $row['iduser'];
                                $user_name = $row['username'];
                                $user_pass = $row['password'];
                                $user_role = $row['role'];
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><strong><?= esc($user_name); ?></strong></td>
                                <td><code><?= esc($user_pass); ?></code></td>
                                <td>
                                    <span class="role-badge <?= $user_role === 'admin' ? 'role-admin' : 'role-user'; ?>">
                                        <?= esc($user_role); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <!-- Proteksi Tombol Hapus Sisi Klien untuk akun superadmin -->
                                    <?php if ($user_name === 'superadmin'): ?>
                                        <span class="badge badge-pill badge-secondary px-3 py-1" style="font-weight: 600; font-size: 11px;">
                                            <i class="fas fa-lock"></i> Terkunci
                                        </span>
                                    <?php else: ?>
                                        <a href="register.php?delete_user=<?= $id_user; ?>" class="btn btn-sm btn-danger text-white px-3 py-1" onclick="return confirm('Apakah Anda yakin ingin menghapus akun <?= esc($user_name); ?>?');">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php }; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </div>

    </div>

</body>
</html>