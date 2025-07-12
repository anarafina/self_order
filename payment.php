<?php
include("config.php"); // Pastikan config.php sudah ada dan berisi koneksi database
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Gaya tambahan yang mungkin relevan dari style.css Anda, jika ada */
        .radio-checked {
            border-color: #16a34a; /* Warna hijau untuk radio yang dipilih, senada dengan green-600 */
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <div class="min-h-screen bg-gray-50">
        <header class="bg-green-600 text-white p-4 shadow-md sticky top-0 z-10">
            <div class="container mx-auto flex items-center">
                <a href="index.php" class="text-white mr-4"><i class="fas fa-arrow-left text-lg"></i></a>
                <h1 class="text-xl font-bold">Pembayaran</h1>
            </div>
        </header>

        <main class="container mx-auto p-4 pb-24"> <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
                <h2 class="font-semibold text-lg text-gray-800 mb-3">Info Kontak</h2>
                <div class="mb-3">
                    <label for="fullName" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap*</label> <div class="relative">
                        <input type="text" id="fullName" class="w-full p-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Masukkan nama lengkap Anda" required>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="phoneNumber" class="block text-sm font-medium text-gray-700 mb-1">Nomor Ponsel (Opsional)</label> <div class="relative">
                        <input type="tel" id="phoneNumber" class="w-full p-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Contoh: 08123456789">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-phone text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Kirim struk ke email (Opsional)</label> <div class="relative">
                        <input type="email" id="email" class="w-full p-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Contoh: nama@example.com">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <label for="tableNumberPayment" class="block text-sm font-medium text-gray-700 mb-1">Nomor Meja*</label>
                    <div class="relative">
                        <input type="text" id="tableNumberPayment" class="w-full p-3 pl-10 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed" value="-" readonly>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-chair text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
                <h2 class="font-semibold text-lg text-gray-800 mb-3">Metode Pembayaran</h2>
                <div class="grid grid-cols-2 gap-3">
                    <button class="payment-method-btn flex flex-col items-center justify-center border border-gray-300 rounded-lg p-3 h-24 text-center text-gray-700 hover:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" data-method="online">
                        <i class="fas fa-globe text-2xl mb-2 text-gray-600"></i>
                        <span class="font-medium text-sm">Pembayaran Online</span>
                    </button>
                    <button class="payment-method-btn flex flex-col items-center justify-center border border-green-600 rounded-lg p-3 h-24 text-center text-green-600 hover:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors radio-checked" data-method="cashier">
                        <i class="fas fa-cash-register text-2xl mb-2 text-green-600"></i>
                        <span class="font-medium text-sm">Bayar di Kasir</span>
                    </button>
                </div>
            </div>

            <div id="onlinePaymentOptions" class="bg-white rounded-lg shadow-sm p-4 mb-4 hidden">
                <h2 class="font-semibold text-lg text-gray-800 mb-3">Selesaikan Pembayaran</h2>
                <div class="flex items-center justify-between border border-gray-300 rounded-lg p-3 mb-3 cursor-pointer">
                    <div class="flex items-center">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e0/QRIS_Quick_Response_Code_Indonesian_Standard.png/1200px-QRIS_Quick_Response_Code_Indonesian_Standard.png" alt="QRIS" class="w-10 h-10 object-contain mr-3">
                        <span class="font-medium text-gray-800">QRIS</span>
                    </div>
                    <input type="radio" name="payment_option" value="qris" class="form-radio text-green-600 h-5 w-5">
                </div>
                <div class="flex items-center justify-between border border-gray-300 rounded-lg p-3 cursor-pointer">
                    <div class="flex items-center">
                        <img src="https://img.icons8.com/color/48/000000/mastercard-logo.png" alt="Kartu Kredit/Debit" class="w-10 h-10 object-contain mr-3">
                        <span class="font-medium text-gray-800">Kartu Kredit/Debit</span>
                    </div>
                    <input type="radio" name="payment_option" value="credit_card" class="form-radio text-green-600 h-5 w-5">
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4 flex justify-between items-center mb-4 cursor-pointer hover:bg-gray-50 transition-colors">
                <div class="flex items-center">
                    <i class="fas fa-percentage text-pink-500 text-xl mr-3"></i>
                    <span class="font-semibold text-pink-500">Tambah Promo atau Voucher</span>
                </div>
                <i class="fas fa-chevron-right text-gray-400"></i>
            </div>
        </main>

        <footer class="fixed bottom-0 left-0 right-0 bg-white shadow-lg p-4 z-20">
            <div class="container mx-auto">
                <div class="flex justify-between items-center mb-3">
                    <span class="font-semibold text-gray-800">Total Pembayaran</span>
                    <span id="finalTotalPayment" class="font-bold text-xl text-green-600">Rp 0</span> </div>
                <button id="payNowBtn" class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors">Bayar</button>
            </div>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const finalTotalPayment = document.getElementById('finalTotalPayment');
            const tableNumberPayment = document.getElementById('tableNumberPayment');
            const payNowBtn = document.getElementById('payNowBtn');
            const paymentMethodBtns = document.querySelectorAll('.payment-method-btn');
            const onlinePaymentOptions = document.getElementById('onlinePaymentOptions');
            const fullNameInput = document.getElementById('fullName');
            const phoneNumberInput = document.getElementById('phoneNumber');
            const emailInput = document.getElementById('email');

            const storedTotal = localStorage.getItem('totalPayment');
            const storedTableNumber = localStorage.getItem('tableNumber');
            const storedCart = JSON.parse(localStorage.getItem('cartItems') || '[]');

            if (storedTotal) {
                finalTotalPayment.textContent = storedTotal;
            } else {
                finalTotalPayment.textContent = 'Rp 0';
            }

            if (storedTableNumber) {
                tableNumberPayment.value = storedTableNumber;
            } else {
                tableNumberPayment.value = '-';
            }

            const updatePaymentOptionsVisibility = (selectedMethod) => {
                if (selectedMethod === 'online') {
                    onlinePaymentOptions.classList.remove('hidden');
                } else {
                    onlinePaymentOptions.classList.add('hidden');
                    document.querySelectorAll('input[name="payment_option"]').forEach(radio => {
                        radio.checked = false;
                    });
                }
            };

            paymentMethodBtns.forEach(button => {
                button.addEventListener('click', () => {
                    paymentMethodBtns.forEach(btn => {
                        btn.classList.remove('border-green-600', 'text-green-600', 'radio-checked');
                        btn.classList.add('border-gray-300', 'text-gray-700');
                        btn.querySelector('i')?.classList.remove('text-green-600');
                        btn.querySelector('i')?.classList.add('text-gray-600');
                    });

                    button.classList.add('border-green-600', 'text-green-600', 'radio-checked');
                    button.classList.remove('border-gray-300', 'text-gray-700');
                    button.querySelector('i')?.classList.add('text-green-600');
                    button.querySelector('i')?.classList.remove('text-gray-600');

                    updatePaymentOptionsVisibility(button.dataset.method);
                });
            });

            updatePaymentOptionsVisibility(document.querySelector('.payment-method-btn.radio-checked')?.dataset.method || 'cashier');

            payNowBtn.addEventListener('click', async () => {
                const selectedPaymentMethod = document.querySelector('.payment-method-btn.radio-checked').dataset.method;
                let finalConfirmationMessage = '';
                
                // --- Validasi Nama (WAJIB) ---
                if (fullNameInput.value.trim() === '') {
                    alert('Mohon masukkan nama lengkap Anda.');
                    fullNameInput.focus();
                    return;
                }
                // --- End Validasi Nama ---

                // Nomor telepon dan email TIDAK WAJIB, jadi tidak perlu validasi di sini
                // Nilai akan diambil apa adanya (bisa string kosong)

                if (selectedPaymentMethod === 'cashier') {
                    finalConfirmationMessage = 'Pesanan Anda akan dikirim ke dapur. Silakan lakukan pembayaran di kasir. Lanjutkan?';
                } else { // Online payment method
                    const selectedOnlineOption = document.querySelector('input[name="payment_option"]:checked');
                    if (!selectedOnlineOption) {
                        alert('Pilih opsi pembayaran online (QRIS/Kartu Kredit) terlebih dahulu.');
                        return;
                    }
                    finalConfirmationMessage = `Anda memilih pembayaran melalui ${selectedOnlineOption.value.toUpperCase()}. Pesanan Anda akan dikirim ke dapur setelah pembayaran selesai. Lanjutkan?`;
                }
                
                if (confirm(finalConfirmationMessage)) {
                    // Kumpulkan semua data pesanan
                    const orderData = {
                        tableNumber: storedTableNumber,
                        customerName: fullNameInput.value.trim(),
                        // Nomor telepon dan email dikirim apa adanya, bisa string kosong jika tidak diisi
                        phoneNumber: phoneNumberInput.value.trim(), 
                        email: emailInput.value.trim(),           
                        totalPayment: storedTotal,
                        cartItems: storedCart
                    };

                    try {
                        const response = await fetch('save_order.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(orderData),
                        });

                        const result = await response.json();

                        if (result.success) {
                            alert('Pesanan berhasil disimpan! ' + result.message);
                            localStorage.removeItem('cartItems');
                            localStorage.removeItem('totalPayment');
                            localStorage.removeItem('tableNumber');
                            
                            window.location.href = 'index.php'; 
                        } else {
                            alert('Gagal menyimpan pesanan: ' + result.message);
                            console.error('Server error:', result.message);
                        }
                    } catch (error) {
                        alert('Terjadi kesalahan saat berkomunikasi dengan server. Silakan coba lagi.');
                        console.error('Fetch error:', error);
                    }
                }
            });
        });
    </script>
</body>
</html>