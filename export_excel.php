<?php
require 'function.php';
include 'cek.php';

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Data_Stock_Barang.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table border="1">
    <tr>
        <th>No</th>
        <th>ID Barang</th>
        <th>Nama Barang</th>
        <th>Keterangan</th>
        <th>Stock</th>
    </tr>
    <?php
    $data = mysqli_query($conn, "SELECT * FROM stock");
    $no = 1;
    while ($row = mysqli_fetch_array($data)) {
        echo "<tr>
            <td>" . esc($no++) . "</td>
            <td>" . esc($row['idbarang']) . "</td>
            <td>" . esc($row['namabarang']) . "</td>
            <td>" . esc($row['keterangan']) . "</td>
            <td>" . esc($row['stock']) . "</td>
        </tr>";
    }
    ?>
</table>