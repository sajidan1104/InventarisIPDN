<?php
require 'function.php';
include 'cek.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Export PDF Data Masuk Barang</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; }
    </style>
</head>
<body onload="window.print()">
    <h2>Laporan Stock Barang Masuk</h2>
    <table>
        <tr>
            <th>No</th>
            <th>ID Masuk</th>
            <th>Tanggal</th>
            <th>Nama Barang</th>
            <th>Keterangan</th>
            <th>Quantity Masuk</th>
        </tr>
        <?php
        $data = mysqli_query($conn, "SELECT m.idmasuk, m.tanggal, m.keterangan, m.qty, s.namabarang FROM masuk m INNER JOIN stock s ON s.idbarang = m.idbarang");
        $no = 1;
        while ($row = mysqli_fetch_array($data)) {
            echo "<tr>
                <td>" . esc($no++) . "</td>
                <td>" . esc($row['idmasuk']) . "</td>
                <td>" . esc($row['tanggal']) . "</td>
                <td>" . esc($row['namabarang']) . "</td>
                <td>" . esc($row['keterangan']) . "</td>
                <td>" . esc($row['qty']) . "</td>
            </tr>";
        }
        ?>
    </table>
</body>
</html>