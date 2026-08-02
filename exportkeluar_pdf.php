<?php
require 'function.php';
include 'cek.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Export PDF Data Barang Keluar</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; }
    </style>
</head>
<body onload="window.print()">
    <h2>Laporan Stock Barang Keluar</h2>
    <table>
        <tr>
            <th>No</th>
            <th>ID Keluar</th>
            <th>Tanggal</th>
            <th>Nama Barang</th>
            <th>Penerima</th>
            <th>Quantity</th>
        </tr>
        <?php
        $data = mysqli_query($conn, "SELECT k.idkeluar, k.tanggal, k.penerima, k.qty, s.namabarang FROM keluar k INNER JOIN stock s ON s.idbarang = k.idbarang");
        $no = 1;
        while ($row = mysqli_fetch_array($data)) {
            echo "<tr>
                <td>" . esc($no++) . "</td>
                <td>" . esc($row['idkeluar']) . "</td>
                <td>" . esc($row['tanggal']) . "</td>
                <td>" . esc($row['namabarang']) . "</td>
                <td>" . esc($row['penerima']) . "</td>
                <td>" . esc($row['qty']) . "</td>
            </tr>";
        }
        ?>
    </table>
</body>
</html>