<?php
include("config.php"); // Pastikan config.php sudah berisi koneksi ke database

header('Content-Type: application/json'); // Respons dalam format JSON

$response = ['success' => false, 'message' => '', 'debug' => []];

// Tambahkan status koneksi database ke debug info
if ($conn) {
    $response['debug']['db_connection'] = 'Successful';
} else {
    $response['debug']['db_connection'] = 'Failed: ' . mysqli_connect_error();
    $response['message'] = 'Koneksi database gagal. Silakan periksa config.php.';
    echo json_encode($response);
    exit(); // Hentikan eksekusi jika koneksi gagal
}

// Pastikan metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data JSON dari body request
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    $response['debug']['raw_post_data'] = $json_data; // Tampilkan data mentah yang diterima
    $response['debug']['decoded_data'] = $data;     // Tampilkan data yang sudah di-decode

    if ($data) {
        $tableNumber = $data['tableNumber'] ?? null;
        $customerName = $data['customerName'] ?? null;
        $phoneNumber = $data['phoneNumber'] ?? null;
        $email = $data['email'] ?? null;
        // Hapus 'Rp ' dan titik (ribuan), ganti koma desimal ke titik untuk floatval
        $totalPaymentRaw = $data['totalPayment'] ?? 'Rp 0';
        $totalPayment = floatval(str_replace(['Rp ', '.'], '', $totalPaymentRaw));
        $cartItems = $data['cartItems'] ?? [];

        $response['debug']['parsed_variables'] = [
            'tableNumber' => $tableNumber,
            'customerName' => $customerName,
            'phoneNumber' => $phoneNumber,
            'email' => $email,
            'totalPayment' => $totalPayment,
            'cartItems_count' => count($cartItems)
        ];

        // Validasi minimal: Nomor meja dan item keranjang harus ada
        if (empty($tableNumber) || empty($cartItems) || count($cartItems) === 0) {
            $response['message'] = 'Data pesanan tidak lengkap atau keranjang kosong.';
            echo json_encode($response);
            exit();
        }

        mysqli_begin_transaction($conn); // Mulai transaksi

        try {
            // 1. Masukkan data ke tabel 'orders'
            $stmt = $conn->prepare("INSERT INTO orders (table_number, customer_name, phone_number, email, total_amount, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            // Perhatikan: 's' untuk string, 'd' untuk double/float
            $stmt->bind_param("ssssd", $tableNumber, $customerName, $phoneNumber, $email, $totalPayment);
            
            if (!$stmt->execute()) {
                throw new Exception("Gagal menyimpan pesanan utama: " . $stmt->error);
            }
            $order_id = mysqli_insert_id($conn); // Dapatkan ID pesanan yang baru dibuat
            $response['debug']['order_id'] = $order_id;
            $stmt->close();

            // 2. Masukkan data item keranjang ke tabel 'order_items'
            $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, menu_id, nama_menu, quantity, notes, price_at_order) VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($cartItems as $index => $item) {
                $menu_id = $item['id_menu'] ?? null;
                $nama_menu = $item['nama_menu'] ?? null;
                $quantity = $item['quantity'] ?? null;
                $notes = $item['notes'] ?? '';
                $price_at_order = $item['harga'] ?? null;

                // Tambahkan validasi dasar untuk item keranjang
                if (is_null($menu_id) || is_null($nama_menu) || is_null($quantity) || is_null($price_at_order)) {
                    throw new Exception("Data item keranjang tidak lengkap pada indeks " . $index);
                }

                $response['debug']['item_' . $index] = [
                    'menu_id' => $menu_id, 'nama_menu' => $nama_menu, 'quantity' => $quantity,
                    'notes' => $notes, 'price_at_order' => $price_at_order
                ];
                
                // Perhatikan: 'i' untuk integer, 's' untuk string, 'd' untuk double/float
                $stmt_items->bind_param("iisiss", $order_id, $menu_id, $nama_menu, $quantity, $notes, $price_at_order);
                if (!$stmt_items->execute()) {
                    throw new Exception("Gagal menyimpan item pesanan (indeks " . $index . "): " . $stmt_items->error);
                }
            }
            $stmt_items->close();

            mysqli_commit($conn); // Commit transaksi jika semua berhasil
            $response['success'] = true;
            $response['message'] = 'Pesanan berhasil disimpan!';

        } catch (Exception $e) {
            mysqli_rollback($conn); // Rollback transaksi jika terjadi kesalahan
            $response['message'] = 'Terjadi kesalahan saat menyimpan pesanan: ' . $e->getMessage();
            $response['debug']['exception'] = $e->getMessage();
            error_log("Error saving order: " . $e->getMessage()); // Log error ke file log server
        }

    } else {
        $response['message'] = 'Data yang dikirim tidak valid atau kosong.';
    }
} else {
    $response['message'] = 'Metode request tidak diizinkan. Hanya POST.';
}

mysqli_close($conn); // Tutup koneksi database
echo json_encode($response);
?>