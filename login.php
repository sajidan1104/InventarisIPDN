<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require "function.php";

if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header("Location: index.php");
    exit();
}

// --- LOGIKA MEMBACA COOKIES (UNTUK PENGISIAN OTOMATIS INTERNAL) ---
$cookie_username = isset($_COOKIE['remember_username']) ? $_COOKIE['remember_username'] : '';
$cookie_password = isset($_COOKIE['remember_password']) ? $_COOKIE['remember_password'] : '';
$cookie_checked  = isset($_COOKIE['remember_checked'])  ? $_COOKIE['remember_checked']  : '';

if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']); // Cek apakah checkbox dicentang

    // 1. EVALUASI RUTE KHUSUS SUPERADMIN
    if ($username === 'superadmin' && $password === 'superadmin123') {
        $_SESSION['superadmin'] = true;
        
        if ($remember) {
            setcookie('remember_username', $username, time() + (3600 * 24 * 30), "/");
            setcookie('remember_password', $password, time() + (3600 * 24 * 30), "/");
            setcookie('remember_checked', 'checked', time() + (3600 * 24 * 30), "/");
        } else {
            setcookie('remember_username', '', time() - 3600, "/");
            setcookie('remember_password', '', time() - 3600, "/");
            setcookie('remember_checked', '', time() - 3600, "/");
        }

        header("Location: register.php");
        exit();
    }

    // 2. RUTE LOGIN BIASA (DATABASE VERIFICATION)
    $stmt = mysqli_prepare($conn, "SELECT * FROM login WHERE username=? AND password=?");
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user_data = mysqli_fetch_array($result)) {
        $_SESSION['admin'] = true;
        $_SESSION['username'] = $user_data['username']; 
        $_SESSION['role'] = $user_data['role'];         
        $_SESSION['login_success'] = true;             
        
        // PROSES COOKIES
        if ($remember) {
            setcookie('remember_username', $username, time() + (3600 * 24 * 30), "/");
            setcookie('remember_password', $password, time() + (3600 * 24 * 30), "/");
            setcookie('remember_checked', 'checked', time() + (3600 * 24 * 30), "/");
        } else {
            setcookie('remember_username', '', time() - 3600, "/");
            setcookie('remember_password', '', time() - 3600, "/");
            setcookie('remember_checked', '', time() - 3600, "/");
        }

        header("Location: index.php");
        exit();
    } else {
        $error = "Username atau Password salah!";
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Inventaris IPDN - Masuk</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet" crossorigin="anonymous" />
    <!-- Memanggil CSS Khusus Login -->
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" type="image/x-icon" href="assets/img/logo_ipdn.png">
</head>
<body>

    <div class="login-wrapper">
        
        <!-- HEADER LOGIN -->
        <header class="login-header">
            <img src="assets/img/logo_ipdn.png" alt="Logo IPDN" class="login-logo" onerror="this.onerror=null; this.src='https://upload.wikimedia.org/wikipedia/commons/2/27/Lambang_IPDN.png';">
            <h1 class="login-title">Inventaris IPDN</h1>
            <p class="login-subtitle">Portal Administrasi Inventaris Barang</p>
        </header>

        <!-- KARTU FORM MASUK -->
        <main class="login-card">
            
            <div class="welcome-section">
                <h2 class="welcome-title">Selamat Datang 👋</h2>
                <p class="welcome-desc">Masuk untuk mengelola stok dan log barang</p>
            </div>

            <!-- Pesan Error Dinamis -->
            <?php if (isset($error)): ?>
                <div style="background-color: #fef2f2; border: 1.5px solid rgba(239, 68, 68, 0.2); color: #ef4444; padding: 12px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-exclamation-circle"></i> <?= esc($error); ?>
                </div>
            <?php endif; ?>

            <!-- Formulir dengan Semantik Bersih -->
            <form method="post" action="login.php">
                
                <!-- INPUT USERNAME -->
                <div class="form-group">
                    <label class="form-label" for="username">Username Admin / Staf</label>
                    <div class="input-container">
                        <span class="input-icon"><i class="fas fa-user"></i></span>
                        <!-- Menambahkan autocomplete="username" -->
                        <input class="input-field" name="username" id="username" type="text" placeholder="Masukkan username" value="<?= esc($cookie_username); ?>" autocomplete="username" required />
                    </div>
                </div>

                <!-- INPUT PASSWORD -->
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-container">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <!-- Menambahkan autocomplete="current-password" -->
                        <input class="input-field" name="password" id="password" type="password" placeholder="••••••••" value="<?= esc($cookie_password); ?>" autocomplete="current-password" required />
                        <span class="eye-toggle" id="togglePassword"><i class="fas fa-eye"></i></span>
                    </div>
                </div>

                <!-- REMEMBER ME -->
                <label class="remember-container" for="remember">
                    <input type="checkbox" name="remember" id="remember" class="remember-checkbox" <?= $cookie_checked; ?>>
                    <span>Ingat saya di perangkat ini</span>
                </label>

                <!-- SUBMIT BUTTON -->
                <button type="submit" name="login" class="submit-btn">
                    Masuk ke Sistem <i class="fas fa-arrow-right" style="font-size: 12px;"></i>
                </button>

            </form>
        </main>
    </div>

    <!-- SCRIPT INTERAKTIF EYE-TOGGLE (PASSWORD VISIBILITY) -->
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>