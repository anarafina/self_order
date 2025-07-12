<?php
include("config.php"); // Koneksi ke database
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pemesanan Restoran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="./manifest.json">
</head>
<body class="bg-gray-50 font-sans">
    <div id="app" class="relative pb-24">
        <header class="bg-green-600 text-white p-4 shadow-md sticky top-0 z-10">
            <div class="container mx-auto flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold">Resto Delight</h1>
                    <p class="text-xs">Jam Operasional: 10:00 - 22:00</p>
                </div>
                <div class="flex gap-4">
                    <button id="searchBtn"><i class="fas fa-search"></i></button>
                    <button id="menuBtn"><i class="fas fa-bars"></i></button>
                </div>
            </div>
        </header>

        <div id="searchModal" class="fixed inset-0 bg-black bg-opacity-50 z-20 hidden">
            <div class="bg-white p-4 mx-4 rounded-lg mt-16">
                <div class="flex items-center border-b border-gray-200 pb-2">
                    <i class="fas fa-search text-gray-400 mr-2"></i>
                    <input type="text" id="searchInput" placeholder="Cari menu..." class="flex-grow outline-none">                </div>
                <button id="closeSearch" class="text-red-500 mt-4 float-right">Tutup</button>
            </div>
        </div>

        <div id="mobileMenu" class="fixed inset-0 bg-black bg-opacity-50 z-20 hidden">
            <div class="bg-white h-full w-64 p-4 mr-0 ml-auto">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="font-bold">Menu</h2>
                    <button id="closeMenu" class="text-red-500"><i class="fas fa-times"></i></button>
                </div>
                <nav>
                    <ul class="space-y-4">
                        <li><a href="#" class="block py-2 border-b border-gray-100">Profil Restoran</a></li>
                        <li><a href="#" class="block py-2 border-b border-gray-100">Promo</a></li>
                        <li><a href="#" class="block py-2 border-b border-gray-100">Kontak Kami</a></li>
                    </ul>
                </nav>
            </div>
        </div>

        <main class="container mx-auto p-4">
            <div class="mb-6">
                <label for="tableNumber" class="block text-sm font-medium text-gray-700 mb-1">Nomor Meja</label>
                <div class="relative">
                    <input type="number" id="tableNumber" min="1" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Masukkan nomor meja Anda">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-table text-gray-400"></i>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <h2 class="text-lg font-semibold mb-4">Kategori Menu</h2>
                <div class="flex overflow-x-auto gap-2 pb-2 scrollbar-hide">
                    <button class="category-btn px-4 py-2 bg-green-600 text-white rounded-full whitespace-nowrap" data-category="semua">Semua</button>
                    <button class="category-btn px-4 py-2 bg-gray-200 rounded-full whitespace-nowrap" data-category="Makanan">Makanan</button>
                    <button class="category-btn px-4 py-2 bg-gray-200 rounded-full whitespace-nowrap" data-category="Minuman">Minuman</button>
                </div>
            </div>

            <div id="loadingIndicator" class="loading">
                <div class="spinner"></div>
            </div>

            <div id="errorMessage" class="hidden mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <p>Terjadi kesalahan saat memuat menu. Silakan coba lagi nanti.</p>
            </div>

            <div class="mb-8" id="recommendedSection" style="display: none;">
                <h2 class="text-lg font-semibold mb-4">Rekomendasi Menu</h2>
                <div class="flex overflow-x-auto gap-3 pb-2 scrollbar-hide" id="recommendedMenu">
                    </div>
            </div>

            <div id="menuSection" style="display: none;">
                <h2 class="text-lg font-semibold mb-4">Daftar Menu</h2>
                <div id="menuList" class="space-y-2">
                    </div>
            </div>
        </main>

        <div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden flex items-center justify-center p-4">
            <div class="bg-white rounded-lg w-full max-w-md">
                <div class="relative">
                    <img id="productImage" src="" alt="" class="w-full h-48 object-cover rounded-t-lg">
                    <button id="closeProductModal" class="absolute top-2 right-2 bg-white rounded-full p-2 shadow-md">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-4">
                    <div class="flex justify-between items-center mb-2">
                        <h3 id="productName" class="text-xl font-bold"></h3>
                        <span id="productPrice" class="text-lg font-semibold text-green-600"></span>
                    </div>
                    <div class="mb-4">
                        <label for="productNotes" class="block text-sm font-medium text-gray-700 mb-1">Catatan Opsional</label>
                        <input type="text" id="productNotes" class="w-full p-2 border border-gray-300 rounded" placeholder="Contoh: Sedikit gula">
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <button id="decreaseQty" class="bg-gray-200 px-3 py-1 rounded-l-lg">-</button>
                            <span id="productQty" class="bg-gray-100 px-4 py-1">1</span>
                            <button id="increaseQty" class="bg-gray-200 px-3 py-1 rounded-r-lg">+</button>
                        </div>
                        <span id="productSubtotal" class="font-semibold">Rp 0</span>
                    </div>
                    <button id="addToCart" class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold">Tambah Pesanan</button>
                </div>
            </div>
        </div>

        <div id="cart" class="fixed bottom-0 left-0 right-0 bg-white shadow-lg rounded-t-lg slide-out z-10">
            <div class="container mx-auto p-4">
                <div class="flex justify-between items-center mb-2">
                    <div class="flex items-center">
                        <i class="fas fa-shopping-cart text-green-600 mr-2"></i>
                        <span id="cartCount" class="bg-green-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">0</span>
                    </div>
                    <div>
                        <span class="text-gray-600 mr-3">Total:</span>
                        <span id="cartTotal" class="font-semibold">Rp 0</span>
                    </div>
                </div>
                <button id="checkoutBtn" class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold disabled:opacity-50" disabled>CHECK OUT</button>
            </div>
        </div>

        <div id="checkoutPage" class="fixed inset-0 bg-white z-40 hidden overflow-y-auto">
            <header class="bg-green-600 text-white p-4 shadow-md sticky top-0 z-10">
                <div class="container mx-auto flex justify-between items-center">
                    <button id="backToMain"><i class="fas fa-arrow-left"></i></button>
                    <h1 class="text-xl font-bold">Ringkasan Pesanan</h1>
                    <div class="w-6"></div>
                </div>
            </header>

            <main class="container mx-auto p-4">
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h2 class="font-semibold mb-2">Tipe Pemesanan</h2>
                    <p class="text-gray-600 bg-white p-2 rounded">Makan di tempat</p>
                </div>

                <div class="mb-6">
                    <h2 class="font-semibold mb-2">Item yang Dipesan</h2>
                    <div id="orderedItems" class="space-y-3">
                        </div>
                </div>

                <div class="mb-6">
                    <h2 class="font-semibold mb-2">Rincian Pembayaran</h2>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Subtotal (0 menu)</span>
                            <span id="subtotal">Rp 0</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Biaya lainnya</span>
                            <span id="otherFee">Rp 1,000</span>
                        </div>
                        <div class="flex justify-between mt-3 pt-2 border-t border-gray-200">
                            <span class="font-semibold">Total Pembayaran</span>
                            <span id="totalPayment" class="font-semibold text-green-600">Rp 1,000</span>
                        </div>
                    </div>
                </div>

                <button id="proceedPayment" class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold">Lanjut Pembayaran</button>
            </main>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>