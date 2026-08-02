<?php
require 'function.php';
include 'cek.php';

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Data_Barang_Keluar.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table border="1">
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