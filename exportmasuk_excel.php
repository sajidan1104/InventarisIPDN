<?php
require 'function.php';
include 'cek.php';

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Data_Barang_Masuk.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table border="1">
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