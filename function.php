<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = mysqli_connect("localhost", "root", "", "stockbarang");

if (!$conn) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}

// Helper function untuk mencegah XSS
function esc($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Proteksi Sisi Server: Superadmin otomatis terhitung sebagai bagian dari Admin (Hirarki Level)
function isAdmin() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'superadmin');
}

// Tambah barang baru
if (isset($_POST['addnewbarang'])) {
    if (!isAdmin()) {
        header("location:index.php?tab=stok&error=unauthorized");
        exit();
    }

    $namabarang = $_POST['namabarang'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $stock = intval($_POST['stock'] ?? 0);

    $stmt = mysqli_prepare($conn, "INSERT INTO stock (namabarang, keterangan, stock) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssi", $namabarang, $keterangan, $stock);
    
    if (mysqli_stmt_execute($stmt)) {
        header("location:index.php?tab=stok");
        exit();
    } else {
        header("location:index.php?tab=stok");
        exit();
    }
}

// Update stock barang
if (isset($_POST['updatebarang'])) {
    if (!isAdmin()) {
        header("location:index.php?tab=stok&error=unauthorized");
        exit();
    }

    $idb = intval($_POST['idb']);
    $namabarang = $_POST['namabarang'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';

    $stmt = mysqli_prepare($conn, "UPDATE stock SET namabarang=?, keterangan=? WHERE idbarang=?");
    mysqli_stmt_bind_param($stmt, "ssi", $namabarang, $keterangan, $idb);
    
    if (mysqli_stmt_execute($stmt)) {
        header("location:index.php?tab=stok");
        exit();
    } else {
        header("location:index.php?tab=stok");
        exit();
    }
}

// Menghapus barang dari stock
if (isset($_POST['hapusbarang'])) {
    if (!isAdmin()) {
        header("location:index.php?tab=stok&error=unauthorized");
        exit();
    }

    $idb = intval($_POST['idb']);
    
    $stmt = mysqli_prepare($conn, "DELETE FROM stock WHERE idbarang=?");
    mysqli_stmt_bind_param($stmt, "i", $idb);
    
    if (mysqli_stmt_execute($stmt)) {
        header("location:index.php?tab=stok");
        exit();
    } else {
        header("location:index.php?tab=stok");
        exit();
    }
}

// Barang masuk
if (isset($_POST['barangmasuk'])) {
    if (!isAdmin()) {
        header("location:index.php?tab=masuk&error=unauthorized");
        exit();
    }

    $barangnya = intval($_POST['barangnya']);
    $penerima = $_POST['keterangan'] ?? '';
    $qty = intval($_POST['qty'] ?? 0);

    $stmt_stock = mysqli_prepare($conn, "SELECT stock FROM stock WHERE idbarang=?");
    mysqli_stmt_bind_param($stmt_stock, "i", $barangnya);
    mysqli_stmt_execute($stmt_stock);
    $res = mysqli_stmt_get_result($stmt_stock);
    
    if ($ambildatanya = mysqli_fetch_array($res)) {
        $stocksekarang = $ambildatanya['stock'];
        $tambahkanstocksekarangdenganquantity = $stocksekarang + $qty;

        $stmt_ins = mysqli_prepare($conn, "INSERT INTO masuk (idbarang, keterangan, qty) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt_ins, "isi", $barangnya, $penerima, $qty);

        $stmt_upd = mysqli_prepare($conn, "UPDATE stock SET stock=? WHERE idbarang=?");
        mysqli_stmt_bind_param($stmt_upd, "ii", $tambahkanstocksekarangdenganquantity, $barangnya);

        if (mysqli_stmt_execute($stmt_ins) && mysqli_stmt_execute($stmt_upd)) {
            header('location:index.php?tab=masuk');
            exit();
        } else {
            header('location:index.php?tab=masuk');
            exit();
        }
    }
}

// Update barang masuk
if (isset($_POST['updatebarangmasuk'])) {
    if (!isAdmin()) {
        header("location:index.php?tab=masuk&error=unauthorized");
        exit();
    }

    $idb = intval($_POST['idb']);
    $idm = intval($_POST['idm']);
    $keterangan = $_POST['keterangan'] ?? '';
    $qty = intval($_POST['qty'] ?? 0);

    $stmt_stock = mysqli_prepare($conn, "SELECT stock FROM stock WHERE idbarang=?");
    mysqli_stmt_bind_param($stmt_stock, "i", $idb);
    mysqli_stmt_execute($stmt_stock);
    $res_stock = mysqli_stmt_get_result($stmt_stock);
    $stock_row = mysqli_fetch_array($res_stock);
    $stockskrg = $stock_row['stock'] ?? 0;

    $stmt_masuk = mysqli_prepare($conn, "SELECT qty FROM masuk WHERE idmasuk=?");
    mysqli_stmt_bind_param($stmt_masuk, "i", $idm);
    mysqli_stmt_execute($stmt_masuk);
    $res_masuk = mysqli_stmt_get_result($stmt_masuk);
    $masuk_row = mysqli_fetch_array($res_masuk);
    $qtyskrg = $masuk_row['qty'] ?? 0;

    $new_stock = $stockskrg - $qtyskrg + $qty;

    $stmt_up_stock = mysqli_prepare($conn, "UPDATE stock SET stock=? WHERE idbarang=?");
    mysqli_stmt_bind_param($stmt_up_stock, "ii", $new_stock, $idb);

    $stmt_up_masuk = mysqli_prepare($conn, "UPDATE masuk SET qty=?, keterangan=? WHERE idmasuk=?");
    mysqli_stmt_bind_param($stmt_up_masuk, "isi", $qty, $keterangan, $idm);

    if (mysqli_stmt_execute($stmt_up_stock) && mysqli_stmt_execute($stmt_up_masuk)) {
        header('location:index.php?tab=masuk');
        exit();
    } else {
        header('location:index.php?tab=masuk');
        exit();
    }
}

// Menghapus barang masuk
if (isset($_POST['hapusbarangmasuk'])) {
    if (!isAdmin()) {
        header("location:index.php?tab=masuk&error=unauthorized");
        exit();
    }

    $idb = intval($_POST['idb']);
    $qty = intval($_POST['qty']);
    $idm = intval($_POST['idm']);

    $stmt_stock = mysqli_prepare($conn, "SELECT stock FROM stock WHERE idbarang=?");
    mysqli_stmt_bind_param($stmt_stock, "i", $idb);
    mysqli_stmt_execute($stmt_stock);
    $res_stock = mysqli_stmt_get_result($stmt_stock);
    
    if ($data = mysqli_fetch_array($res_stock)) {
        $stok = $data['stock'];
        $selisih = $stok - $qty;

        $stmt_up_stock = mysqli_prepare($conn, "UPDATE stock SET stock=? WHERE idbarang=?");
        mysqli_stmt_bind_param($stmt_up_stock, "ii", $selisih, $idb);

        $stmt_del = mysqli_prepare($conn, "DELETE FROM masuk WHERE idmasuk=?");
        mysqli_stmt_bind_param($stmt_del, "i", $idm);

        if (mysqli_stmt_execute($stmt_up_stock) && mysqli_stmt_execute($stmt_del)) {
            header("location:index.php?tab=masuk");
            exit();
        } else {
            header("location:index.php?tab=masuk");
            exit();
        }
    }
}

// Barang keluar
if (isset($_POST['addbarangkeluar'])) {
    if (!isAdmin()) {
        header("location:index.php?tab=keluar&error=unauthorized");
        exit();
    }

    $barangnya = intval($_POST['barangnya']);
    $penerima = $_POST['penerima'] ?? '';
    $qty = intval($_POST['qty'] ?? 0);

    $stmt_stock = mysqli_prepare($conn, "SELECT stock FROM stock WHERE idbarang=?");
    mysqli_stmt_bind_param($stmt_stock, "i", $barangnya);
    mysqli_stmt_execute($stmt_stock);
    $res_stock = mysqli_stmt_get_result($stmt_stock);
    
    if ($ambildatanya = mysqli_fetch_array($res_stock)) {
        $stocksekarang = $ambildatanya['stock'];

        if ($stocksekarang >= $qty) {
            $tambahkanstocksekarangdenganquantity = $stocksekarang - $qty;

            $stmt_ins = mysqli_prepare($conn, "INSERT INTO keluar (idbarang, penerima, qty) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt_ins, "isi", $barangnya, $penerima, $qty);

            $stmt_upd = mysqli_prepare($conn, "UPDATE stock SET stock=? WHERE idbarang=?");
            mysqli_stmt_bind_param($stmt_upd, "ii", $tambahkanstocksekarangdenganquantity, $barangnya);

            if (mysqli_stmt_execute($stmt_ins) && mysqli_stmt_execute($stmt_upd)) {
                echo '<script> alert("Stock berhasil keluar"); window.location.href="index.php?tab=keluar"; </script>';
                exit();
            } else {
                echo '<script> alert("Gagal memproses data"); window.location.href="index.php?tab=keluar"; </script>';
                exit();
            }
        } else {
            echo '<script> alert("Stock saat ini tidak mencukupi"); window.location.href="index.php?tab=keluar"; </script>';
            exit();
        }
    }
}

// Update barang keluar
if (isset($_POST['updatebarangkeluar'])) {
    if (!isAdmin()) {
        header("location:index.php?tab=keluar&error=unauthorized");
        exit();
    }

    $idb = intval($_POST['idb']);
    $idk = intval($_POST['idk']);
    $penerima = $_POST['penerima'] ?? '';
    $qty = intval($_POST['qty'] ?? 0);

    $stmt_stock = mysqli_prepare($conn, "SELECT stock FROM stock WHERE idbarang=?");
    mysqli_stmt_bind_param($stmt_stock, "i", $idb);
    mysqli_stmt_execute($stmt_stock);
    $res_stock = mysqli_stmt_get_result($stmt_stock);
    $stock_row = mysqli_fetch_array($res_stock);
    $stockskrg = $stock_row['stock'] ?? 0;

    $stmt_keluar = mysqli_prepare($conn, "SELECT qty FROM keluar WHERE idkeluar=?");
    mysqli_stmt_bind_param($stmt_keluar, "i", $idk);
    mysqli_stmt_execute($stmt_keluar);
    $res_keluar = mysqli_stmt_get_result($stmt_keluar);
    $keluar_row = mysqli_fetch_array($res_keluar);
    $qtyskrg = $keluar_row['qty'] ?? 0;

    if (($stockskrg + $qtyskrg) >= $qty) {
        $new_stock = $stockskrg + $qtyskrg - $qty;

        $stmt_up_stock = mysqli_prepare($conn, "UPDATE stock SET stock=? WHERE idbarang=?");
        mysqli_stmt_bind_param($stmt_up_stock, "ii", $new_stock, $idb);

        $stmt_up_keluar = mysqli_prepare($conn, "UPDATE keluar SET qty=?, penerima=? WHERE idkeluar=?");
        mysqli_stmt_bind_param($stmt_up_keluar, "isi", $qty, $penerima, $idk);

        if (mysqli_stmt_execute($stmt_up_stock) && mysqli_stmt_execute($stmt_up_keluar)) {
            header('location:index.php?tab=keluar');
            exit();
        } else {
            header('location:index.php?tab=keluar');
            exit();
        }
    } else {
        echo '<script> alert("Stock tidak mencukupi untuk melakukan update ini!"); window.location.href="index.php?tab=keluar"; </script>';
        exit();
    }
}

// Menghapus barang keluar
if (isset($_POST['hapusbarangkeluar'])) {
    if (!isAdmin()) {
        header("location:index.php?tab=keluar&error=unauthorized");
        exit();
    }

    $idb = intval($_POST['idb']);
    $qty = intval($_POST['qty']);
    $idk = intval($_POST['idk']);

    $stmt_stock = mysqli_prepare($conn, "SELECT stock FROM stock WHERE idbarang=?");
    mysqli_stmt_bind_param($stmt_stock, "i", $idb);
    mysqli_stmt_execute($stmt_stock);
    $res_stock = mysqli_stmt_get_result($stmt_stock);
    
    if ($data = mysqli_fetch_array($res_stock)) {
        $stok = $data['stock'];
        $selisih = $stok + $qty;

        $stmt_up_stock = mysqli_prepare($conn, "UPDATE stock SET stock=? WHERE idbarang=?");
        mysqli_stmt_bind_param($stmt_up_stock, "ii", $selisih, $idb);

        $stmt_del = mysqli_prepare($conn, "DELETE FROM keluar WHERE idkeluar=?");
        mysqli_stmt_bind_param($stmt_del, "i", $idk);

        if (mysqli_stmt_execute($stmt_up_stock) && mysqli_stmt_execute($stmt_del)) {
            header("location:index.php?tab=keluar");
            exit();
        } else {
            header("location:index.php?tab=keluar");
            exit();
        }
    }
}
?>