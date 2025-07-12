<?php
include("config.php"); // Pastikan config.php sudah berisi koneksi ke database

header('Content-Type: application/json'); // Respons dalam format JSON

$response = ['success' => false, 'message' => '', 'orders' => []];

try {
    // Ambil pesanan yang berstatus 'pending' atau 'preparing', diurutkan berdasarkan waktu pesanan terbaru
    $stmt = $conn->prepare("SELECT id, table_number, customer_name, order_time, total_amount, status FROM orders WHERE status IN ('pending', 'preparing') ORDER BY order_time ASC");
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($order = $result->fetch_assoc()) {
        $order_id = $order['id'];

        // Format total_amount
        $order['total_amount_formatted'] = 'Rp ' . number_format($order['total_amount'], 0, ',', '.');

        // Ambil item-item untuk setiap pesanan
        $stmt_items = $conn->prepare("SELECT nama_menu, quantity, notes FROM order_items WHERE order_id = ?");
        $stmt_items->bind_param("i", $order_id);
        $stmt_items->execute();
        $items_result = $stmt_items->get_result();

        $items = [];
        while ($item = $items_result->fetch_assoc()) {
            $items[] = $item;
        }
        $order['items'] = $items;
        $stmt_items->close();

        $orders[] = $order;
    }
    $stmt->close();

    $response['success'] = true;
    $response['orders'] = $orders;

} catch (Exception $e) {
    $response['message'] = 'Terjadi kesalahan saat mengambil pesanan: ' . $e->getMessage();
    error_log("Error fetching kitchen orders: " . $e->getMessage());
}

mysqli_close($conn);
echo json_encode($response);
?>