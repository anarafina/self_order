<?php
include("config.php"); // Pastikan config.php sudah berisi koneksi ke database

header('Content-Type: application/json'); // Respons dalam format JSON

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $order_id = $data['order_id'] ?? null;
    $new_status = $data['new_status'] ?? null;

    if (empty($order_id) || empty($new_status)) {
        $response['message'] = 'ID Pesanan atau Status Baru tidak boleh kosong.';
        echo json_encode($response);
        exit();
    }

    // Validasi status yang diizinkan untuk menghindari injeksi yang tidak diinginkan
    $allowed_statuses = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];
    if (!in_array($new_status, $allowed_statuses)) {
        $response['message'] = 'Status yang diminta tidak valid.';
        echo json_encode($response);
        exit();
    }

    try {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Status pesanan berhasil diperbarui menjadi ' . $new_status;
            } else {
                $response['message'] = 'Pesanan tidak ditemukan atau status sudah sama.';
            }
        } else {
            throw new Exception("Gagal memperbarui status: " . $stmt->error);
        }
        $stmt->close();

    } catch (Exception $e) {
        $response['message'] = 'Terjadi kesalahan saat memperbarui status: ' . $e->getMessage();
        error_log("Error updating order status: " . $e->getMessage());
    }

} else {
    $response['message'] = 'Metode request tidak diizinkan.';
}

mysqli_close($conn);
echo json_encode($response);
?>