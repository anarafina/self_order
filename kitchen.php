<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dapur - Sistem Pemesanan Restoran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Animasi untuk kartu pesanan saat dihapus/diselesaikan */
        .order-card {
            transition: all 0.5s ease-out; /* Transisi lebih halus */
            transform: translateX(0);
            opacity: 1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); /* Shadow konsisten */
        }
        .order-card.removed {
            transform: translateX(-150%); /* Geser lebih jauh saat dihapus */
            opacity: 0;
            height: 0; /* Sembunyikan tinggi */
            padding: 0 !important;
            margin-bottom: 0 !important; /* Hilangkan margin bawah */
            border: none !important;
            pointer-events: none; /* Nonaktifkan interaksi */
        }
        /* Style untuk tombol status agar memiliki efek hover yang baik */
        .status-btn {
            @apply transition-colors duration-200;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans"> <div class="min-h-screen">
        <header class="bg-green-600 text-white p-4 shadow-md sticky top-0 z-10">
            <div class="container mx-auto flex justify-between items-center">
                <h1 class="text-xl font-bold">Dapur Restoran</h1> <button id="refreshOrdersBtn" class="bg-green-700 hover:bg-green-800 text-white font-semibold py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i> Segarkan Pesanan
                </button>
            </div>
        </header>

        <main class="container mx-auto p-4 py-8"> <div id="loadingIndicator" class="text-center text-gray-600 mb-4 hidden">
                <i class="fas fa-spinner fa-spin text-4xl text-green-600"></i> <p class="mt-2">Memuat pesanan...</p>
            </div>
            <div id="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative hidden" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline" id="errorText"></span>
            </div>
            <div id="noOrdersMessage" class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative text-center hidden" role="alert">
                <p><i class="fas fa-utensils fa-lg mr-2"></i> Belum ada pesanan yang perlu diproses saat ini.</p>
            </div>

            <div id="ordersContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                </div>
        </main>
    </div>

    <script>
        const ordersContainer = document.getElementById('ordersContainer');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');
        const noOrdersMessage = document.getElementById('noOrdersMessage');
        const refreshOrdersBtn = document.getElementById('refreshOrdersBtn');

        let allOrders = []; // Menyimpan semua pesanan yang sedang ditampilkan

        // Fungsi untuk mengambil pesanan dari backend
        async function fetchOrders() {
            loadingIndicator.classList.remove('hidden');
            errorMessage.classList.add('hidden');
            // noOrdersMessage.classList.add('hidden'); // Jangan sembunyikan ini sampai ada data
            ordersContainer.innerHTML = ''; // Bersihkan kontainer sebelum memuat ulang

            try {
                const response = await fetch('get_kitchen_orders.php');
                if (!response.ok) { // Check for HTTP errors (e.g., 404, 500)
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                const result = await response.json();

                if (result.success) {
                    allOrders = result.orders; // Simpan pesanan yang diambil
                    renderOrders(allOrders);
                    if (allOrders.length === 0) {
                        noOrdersMessage.classList.remove('hidden');
                    } else {
                        noOrdersMessage.classList.add('hidden');
                    }
                } else {
                    errorText.textContent = result.message || 'Gagal mengambil pesanan dari server.';
                    errorMessage.classList.remove('hidden');
                }
            } catch (error) {
                errorText.textContent = 'Terjadi kesalahan saat memuat pesanan: ' + error.message;
                errorMessage.classList.remove('hidden');
                console.error('Error fetching orders:', error);
            } finally {
                loadingIndicator.classList.add('hidden');
            }
        }

        // Fungsi untuk merender (menampilkan) pesanan di UI
        function renderOrders(ordersToRender) {
            ordersContainer.innerHTML = ''; // Bersihkan container

            if (ordersToRender.length === 0) {
                noOrdersMessage.classList.remove('hidden');
                return;
            } else {
                noOrdersMessage.classList.add('hidden');
            }

            ordersToRender.forEach(order => {
                const orderCard = document.createElement('div');
                orderCard.id = `order-${order.id}`;
                orderCard.dataset.orderId = order.id;
                orderCard.classList.add(
                    'order-card', 'bg-white', 'rounded-lg', 'shadow-md', 'p-6', 'flex', 'flex-col', 'justify-between', 'border-t-4'
                );

                // Warna border berdasarkan status
                let borderColorClass = '';
                let statusBgClass = '';
                let statusText = '';
                let buttonClasses = '';
                let buttonText = '';
                let nextStatus = '';

                switch (order.status) {
                    case 'pending':
                        borderColorClass = 'border-blue-500'; // Biru untuk pending
                        statusBgClass = 'bg-blue-500';
                        statusText = 'Menunggu Proses';
                        buttonClasses = 'bg-green-600 hover:bg-green-700'; // Gunakan warna hijau utama untuk aksi pertama
                        buttonText = 'Mulai Proses';
                        nextStatus = 'preparing';
                        break;
                    case 'preparing':
                        borderColorClass = 'border-yellow-500'; // Kuning untuk sedang diproses
                        statusBgClass = 'bg-yellow-500';
                        statusText = 'Sedang Diproses';
                        buttonClasses = 'bg-orange-500 hover:bg-orange-600'; // Oranye untuk siap diambil (tahap selanjutnya)
                        buttonText = 'Siap Diambil';
                        nextStatus = 'ready';
                        break;
                    case 'ready':
                        borderColorClass = 'border-green-500'; // Hijau untuk siap diambil
                        statusBgClass = 'bg-green-500';
                        statusText = 'Siap Diambil';
                        buttonClasses = 'bg-gray-500 hover:bg-gray-600'; // Abu-abu untuk selesai/arsipkan
                        buttonText = 'Selesai / Arsipkan';
                        nextStatus = 'completed'; // Atau bisa langsung dihapus dari view
                        break;
                    default:
                        borderColorClass = 'border-gray-300';
                        statusBgClass = 'bg-gray-500';
                        statusText = 'Status Tidak Dikenal';
                        buttonClasses = 'bg-gray-500 hover:bg-gray-600';
                        buttonText = 'Update Status';
                        nextStatus = ''; // Tidak ada aksi default
                        break;
                }
                orderCard.classList.add(borderColorClass);

                const orderTime = new Date(order.order_time).toLocaleString('id-ID', {
                    day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
                });

                orderCard.innerHTML = `
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-2xl font-bold text-gray-800">Meja: ${order.table_number}</h3>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold text-white ${statusBgClass}">
                                ${statusText}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">Waktu: ${orderTime}</p>
                        ${order.customer_name ? `<p class="text-sm text-gray-600 mb-2">Nama: ${order.customer_name}</p>` : ''}
                        <hr class="my-4 border-gray-200">
                        <h4 class="text-lg font-semibold text-gray-700 mb-3">Daftar Item:</h4>
                        <ul class="list-disc pl-5 text-gray-800 mb-4 space-y-2">
                            ${order.items.map(item => `
                                <li>
                                    ${item.quantity}x ${item.nama_menu}
                                    ${item.notes ? `<span class="text-sm text-gray-500 italic"> (${item.notes})</span>` : ''}
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                    <div class="mt-4">
                        ${nextStatus !== '' ? `
                            <button data-order-id="${order.id}" data-current-status="${order.status}" data-next-status="${nextStatus}"
                                class="status-btn w-full text-white py-2 rounded-lg font-semibold ${buttonClasses}">
                                ${buttonText}
                            </button>
                        ` : `
                            <button class="status-btn w-full text-white py-2 rounded-lg font-semibold bg-gray-400 cursor-not-allowed">
                                Tidak Ada Aksi
                            </button>
                        `}
                    </div>
                `;
                ordersContainer.appendChild(orderCard);
            });

            // Tambahkan event listener untuk tombol status setelah semua kartu dirender
            document.querySelectorAll('.status-btn').forEach(button => {
                button.addEventListener('click', handleStatusUpdate);
            });
        }

        // Fungsi untuk menangani pembaruan status
        async function handleStatusUpdate(event) {
            const button = event.currentTarget;
            const orderId = button.dataset.orderId;
            const currentStatus = button.dataset.currentStatus;
            const nextStatus = button.dataset.nextStatus;

            let confirmationMessage = '';
            if (nextStatus === 'preparing') {
                confirmationMessage = `Yakin ingin memulai proses pesanan Meja ${orderId}?`;
            } else if (nextStatus === 'ready') {
                confirmationMessage = `Yakin pesanan Meja ${orderId} sudah siap diambil?`;
            } else if (nextStatus === 'completed') {
                confirmationMessage = `Yakin pesanan Meja ${orderId} sudah selesai dan ingin diarsipkan?`;
            } else {
                confirmationMessage = `Yakin ingin mengubah status pesanan Meja ${orderId} ke ${nextStatus}?`;
            }

            if (!confirm(confirmationMessage)) {
                return;
            }

            button.disabled = true; // Nonaktifkan tombol saat proses
            button.textContent = 'Memperbarui...';

            try {
                const response = await fetch('update_order_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ order_id: orderId, new_status: nextStatus }),
                });
                const result = await response.json();

                if (result.success) {
                    alert('Status pesanan berhasil diperbarui!');
                    if (nextStatus === 'completed') {
                        // Hilangkan kartu pesanan dari tampilan jika statusnya 'completed'
                        const orderCard = document.getElementById(`order-${orderId}`);
                        if (orderCard) {
                            orderCard.classList.add('removed'); // Tambahkan kelas untuk animasi
                            orderCard.addEventListener('transitionend', () => {
                                orderCard.remove(); // Hapus elemen dari DOM setelah animasi
                                // Cek jika tidak ada pesanan lagi setelah penghapusan
                                if (ordersContainer.children.length === 0) {
                                    noOrdersMessage.classList.remove('hidden');
                                }
                            }, { once: true });
                        }
                    } else {
                        // Muat ulang pesanan untuk memperbarui status di UI
                        fetchOrders();
                    }
                } else {
                    alert('Gagal memperbarui status: ' + (result.message || 'Terjadi kesalahan tidak diketahui.'));
                    button.disabled = false;
                    button.textContent = 'Coba Lagi';
                }
            } catch (error) {
                alert('Terjadi kesalahan jaringan saat memperbarui status.');
                console.error('Error updating status:', error);
                button.disabled = false;
                button.textContent = 'Coba Lagi';
            }
        }

        // Event listener untuk tombol segarkan
        refreshOrdersBtn.addEventListener('click', fetchOrders);

        // Muat pesanan saat halaman pertama kali dimuat
        fetchOrders();
        // Set interval untuk refresh otomatis setiap 30 detik
        setInterval(fetchOrders, 30000); // Refresh setiap 30 detik
    </script>
</body>
</html>