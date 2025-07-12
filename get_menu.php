<?php
include("config.php"); // Koneksi ke database

header('Content-Type: application/json'); // Mengatur header agar respons berupa JSON

$sql = "SELECT id_menu, nama_menu, harga, kategori, rekomendasi, url_gambar FROM daftar_menu";
$result = mysqli_query($conn, $sql);

$menu = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Konversi tipe data untuk konsistensi
        $row['id_menu'] = (int)$row['id_menu'];
        $row['harga'] = (int)$row['harga'];
        $row['rekomendasi'] = (int)$row['rekomendasi'];
        $menu[] = $row;
    }
} else {
    // Tangani error query jika diperlukan
    echo json_encode(["error" => "Query database gagal: " . mysqli_error($conn)]);
    exit();
}

mysqli_close($conn);
echo json_encode($menu);
?>