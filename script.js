// script.js

// Variabel global untuk menyimpan data menu dari database
let menuData = [];

// State Aplikasi
const state = {
    cart: [],
    currentProduct: null,
    currentCategory: 'semua',
    tableNumber: null,
    otherFee: 1000,
};

// DOM Elements
const elements = {
    // Main Elements
    app: document.getElementById('app'),
    loadingIndicator: document.getElementById('loadingIndicator'),
    errorMessage: document.getElementById('errorMessage'),
    recommendedSection: document.getElementById('recommendedSection'),
    menuSection: document.getElementById('menuSection'),
    
    // Header Elements
    searchBtn: document.getElementById('searchBtn'),
    menuBtn: document.getElementById('menuBtn'),
    searchModal: document.getElementById('searchModal'),
    closeSearch: document.getElementById('closeSearch'),
    mobileMenu: document.getElementById('mobileMenu'),
    closeMenu: document.getElementById('closeMenu'),
    searchInput: document.getElementById('searchInput'), // <-- Elemen input pencarian baru
    
    // Table Number
    tableNumber: document.getElementById('tableNumber'),
    
    // Category Buttons
    categoryBtns: document.querySelectorAll('.category-btn'),
    
    // Menu Sections
    recommendedMenu: document.getElementById('recommendedMenu'),
    menuList: document.getElementById('menuList'),
    
    // Product Modal
    productModal: document.getElementById('productModal'),
    closeProductModal: document.getElementById('closeProductModal'),
    productImage: document.getElementById('productImage'),
    productName: document.getElementById('productName'),
    productPrice: document.getElementById('productPrice'),
    productNotes: document.getElementById('productNotes'),
    decreaseQty: document.getElementById('decreaseQty'),
    increaseQty: document.getElementById('increaseQty'),
    productQty: document.getElementById('productQty'),
    productSubtotal: document.getElementById('productSubtotal'),
    addToCart: document.getElementById('addToCart'),
    
    // Cart Elements
    cart: document.getElementById('cart'),
    cartCount: document.getElementById('cartCount'),
    cartTotal: document.getElementById('cartTotal'),
    checkoutBtn: document.getElementById('checkoutBtn'),
    
    // Checkout Page Elements
    checkoutPage: document.getElementById('checkoutPage'),
    backToMain: document.getElementById('backToMain'),
    orderedItems: document.getElementById('orderedItems'),
    subtotal: document.getElementById('subtotal'),
    otherFee: document.getElementById('otherFee'),
    totalPayment: document.getElementById('totalPayment'),
    proceedPayment: document.getElementById('proceedPayment'),
};

// Fungsi untuk mengambil data menu dari database
const fetchMenuData = async () => {
    try {
        elements.loadingIndicator.style.display = 'flex'; // Tampilkan indikator loading
        elements.errorMessage.classList.add('hidden'); // Sembunyikan pesan error
        elements.recommendedSection.style.display = 'none'; // Sembunyikan bagian rekomendasi
        elements.menuSection.style.display = 'none'; // Sembunyikan bagian menu
        
        const response = await fetch('get_menu.php'); // Ambil data dari get_menu.php
        if (!response.ok) { // Cek apakah respons berhasil
            throw new Error(`HTTP error! status: ${response.status}`); // Lempar error jika tidak ok
        }
        
        const data = await response.json(); // Parse respons sebagai JSON
        
        if (data.error) { // Cek apakah ada error dari server PHP
            throw new Error(data.error); // Lempar error jika ada
        }
        
        menuData = data; // Simpan data menu ke variabel global
        console.log("Menu data loaded:", menuData); // Log data untuk debugging
        
        elements.loadingIndicator.style.display = 'none'; // Sembunyikan indikator loading
        // Pada inisialisasi awal, pastikan recommendedSection ditampilkan jika kategori default adalah 'semua'
        if (state.currentCategory === 'semua') {
            elements.recommendedSection.style.display = 'block'; 
        }
        elements.menuSection.style.display = 'block'; // Tampilkan bagian menu
        
        loadRecommendedMenu(); // Muat menu rekomendasi
        loadMenuByCategory(state.currentCategory); // Muat menu berdasarkan kategori
        
    } catch (error) {
        console.error("Could not fetch menu data:", error); // Log error
        elements.loadingIndicator.style.display = 'none'; // Sembunyikan indikator loading
        elements.errorMessage.classList.remove('hidden'); // Tampilkan pesan error
        elements.recommendedSection.style.display = 'none'; // Sembunyikan bagian rekomendasi
        elements.menuSection.style.display = 'none'; // Sembunyikan bagian menu
    }
};

// Format Currency
const formatCurrency = (amount) => {
    return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

// Load Recommended Menu
const loadRecommendedMenu = () => {
    const recommendedItems = menuData.filter(item => item.rekomendasi === 1); // Filter item yang direkomendasikan
    elements.recommendedMenu.innerHTML = recommendedItems.map(item => `
        <div class="menu-item bg-white rounded-lg overflow-hidden shadow transition-transform duration-200 cursor-pointer" data-id="${item.id_menu}">
            <img src="${item.url_gambar}" alt="${item.nama_menu}" class="w-full h-32 object-cover">
            <div class="p-2">
                <h3 class="font-semibold text-sm">${item.nama_menu}</h3>
                <p class="text-green-600 font-semibold">${formatCurrency(item.harga)}</p>
            </div>
        </div>
    `).join('');

    // Add event listeners to recommended items
    document.querySelectorAll('#recommendedMenu .menu-item').forEach(item => {
        item.addEventListener('click', () => openProductModal(parseInt(item.dataset.id))); // Buka modal produk saat diklik
    });
};

// Load Menu by Category
const loadMenuByCategory = (category) => {
    let filteredMenu = menuData;
    if (category !== 'semua') {
        filteredMenu = menuData.filter(item => item.kategori === category);
    }

    // --- Logika untuk menampilkan/menyembunyikan Rekomendasi Menu ---
    if (category === 'semua') {
        elements.recommendedSection.style.display = 'block';
    } else {
        elements.recommendedSection.style.display = 'none';
    }
    // -----------------------------------------------------------------

    elements.menuList.innerHTML = filteredMenu.map(item => `
        <div class="menu-item flex justify-between items-center bg-white rounded-lg p-4 shadow cursor-pointer" data-id="${item.id_menu}">
            <div class="flex-1 pr-4">
                <h3 class="font-bold text-gray-900 uppercase">${item.nama_menu}</h3>
                <div class="flex items-center text-sm text-gray-500 mb-1">
                    <i class="fas fa-star text-yellow-400 mr-1"></i>
                    <span>4.7</span>
                    <span class="ml-1">(100+ ratings)</span>
                </div>
                <p class="text-green-600 font-bold text-lg">${formatCurrency(item.harga)}</p>
            </div>
            <div class="flex flex-col items-center">
                <img src="${item.url_gambar}" alt="${item.nama_menu}" class="w-24 h-24 object-cover rounded-lg mb-2">
            </div>
        </div>
    `).join('');

    document.querySelectorAll('#menuList .menu-item').forEach(item => {
        item.addEventListener('click', () => openProductModal(parseInt(item.dataset.id)));
    });
};

// Fungsi untuk memfilter dan menampilkan menu berdasarkan input pencarian
const handleSearchInput = () => {
    const query = elements.searchInput.value.toLowerCase(); // Ambil input dan ubah ke huruf kecil

    if (query.length > 0) {
        // Jika ada query pencarian, sembunyikan rekomendasi
        elements.recommendedSection.style.display = 'none';

        // Nonaktifkan semua tombol kategori secara visual
        elements.categoryBtns.forEach(b => {
            b.classList.remove('bg-green-600', 'text-white');
            b.classList.add('bg-gray-200');
        });

        // Filter menu berdasarkan nama
        const filtered = menuData.filter(item =>
            item.nama_menu.toLowerCase().includes(query)
        );

        // Tampilkan hasil filter di menuList
        elements.menuList.innerHTML = filtered.map(item => `
            <div class="menu-item flex justify-between items-center bg-white rounded-lg p-4 shadow cursor-pointer" data-id="${item.id_menu}">
                <div class="flex-1 pr-4">
                    <h3 class="font-bold text-gray-900 uppercase">${item.nama_menu}</h3>
                    <div class="flex items-center text-sm text-gray-500 mb-1">
                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                        <span>4.7</span>
                        <span class="ml-1">(100+ ratings)</span>
                    </div>
                    <p class="text-green-600 font-bold text-lg">${formatCurrency(item.harga)}</p>
                </div>
                <div class="flex flex-col items-center">
                    <img src="${item.url_gambar}" alt="${item.nama_menu}" class="w-24 h-24 object-cover rounded-lg mb-2">
                </div>
            </div>
        `).join('');

        // Tambahkan kembali event listener untuk setiap item menu yang baru dirender
        document.querySelectorAll('#menuList .menu-item').forEach(item => {
            item.addEventListener('click', () => openProductModal(parseInt(item.dataset.id)));
        });

    } else {
        // Jika query kosong, kembalikan tampilan ke kategori yang sedang aktif (biasanya 'semua')
        // Ini juga akan menampilkan kembali rekomendasi jika state.currentCategory adalah 'semua'
        loadMenuByCategory(state.currentCategory);
        // Pastikan tombol kategori yang aktif kembali berwarna hijau
        elements.categoryBtns.forEach(b => {
            if (b.dataset.category === state.currentCategory) {
                b.classList.remove('bg-gray-200');
                b.classList.add('bg-green-600', 'text-white');
            } else {
                b.classList.remove('bg-green-600', 'text-white');
                b.classList.add('bg-gray-200');
            }
        });
    }
};

// Open Product Modal
const openProductModal = (productId) => {
    const product = menuData.find(item => item.id_menu === productId); // Cari produk di menuData
    if (!product) {
        console.error("Product not found with ID:", productId); // Log error jika produk tidak ditemukan
        return;
    }

    state.currentProduct = {
        ...product,
        quantity: 1,
        notes: ''
    };

    elements.productImage.src = product.url_gambar; // Set gambar produk
    elements.productImage.alt = product.nama_menu; // Set alt teks gambar
    elements.productName.textContent = product.nama_menu; // Set nama produk
    elements.productPrice.textContent = formatCurrency(product.harga); // Set harga produk
    elements.productNotes.value = ''; // Kosongkan catatan
    elements.productQty.textContent = '1'; // Set kuantitas ke 1
    elements.productSubtotal.textContent = formatCurrency(product.harga); // Set subtotal

    elements.productModal.classList.remove('hidden'); // Tampilkan modal
    elements.addToCart.textContent = "Tambah Pesanan"; // Pastikan teks tombol adalah "Tambah Pesanan"
};

// Close Product Modal
const closeProductModal = () => {
    elements.productModal.classList.add('hidden'); // Sembunyikan modal
    state.currentProduct = null; // Reset current product
};

// Update Product Quantity
const updateProductQuantity = (change) => {
    if (!state.currentProduct) return; // Hentikan jika tidak ada produk saat ini

    const newQuantity = state.currentProduct.quantity + change; // Hitung kuantitas baru
    if (newQuantity < 1) return; // Jangan biarkan kuantitas kurang dari 1

    state.currentProduct.quantity = newQuantity; // Update kuantitas
    elements.productQty.textContent = newQuantity; // Perbarui tampilan kuantitas
    const subtotal = state.currentProduct.harga * newQuantity; // Hitung subtotal baru
    elements.productSubtotal.textContent = formatCurrency(subtotal); // Perbarui tampilan subtotal
};

// Add Item to Cart
const addItemToCart = () => {
    if (!state.currentProduct) return; // Seharusnya tidak terjadi karena dicek di bawah

    if (!state.tableNumber || state.tableNumber === '') { // Validasi nomor meja
        alert('Silakan masukkan nomor meja terlebih dahulu!'); // Tampilkan peringatan
        elements.tableNumber.focus(); // Fokus ke input nomor meja
        return;
    }

    const existingItemIndex = state.cart.findIndex( // Cari item yang sudah ada di keranjang
        item => item.id_menu === state.currentProduct.id_menu && item.notes === elements.productNotes.value
    );

    if (elements.addToCart.textContent === "Update Pesanan") { // Jika dalam mode edit
        const originalId = state.currentProduct.originalId;
        const originalNotes = state.currentProduct.originalNotes;

        // Hapus item lama dari keranjang
        const oldItemIndex = state.cart.findIndex(item => item.id_menu === originalId && item.notes === originalNotes);
        if (oldItemIndex > -1) {
            state.cart.splice(oldItemIndex, 1);
        }

        // Tambahkan item dengan kuantitas dan catatan yang diperbarui
        state.cart.push({
            id_menu: state.currentProduct.id_menu,
            nama_menu: state.currentProduct.nama_menu,
            harga: state.currentProduct.harga,
            quantity: state.currentProduct.quantity,
            notes: elements.productNotes.value, // Ambil catatan terbaru dari input
            url_gambar: state.currentProduct.url_gambar
        });

    } else if (existingItemIndex >= 0) { // Jika item sudah ada dan bukan mode edit
        state.cart[existingItemIndex].quantity += state.currentProduct.quantity; // Tambah kuantitas
    } else { // Jika item baru
        state.cart.push({
            id_menu: state.currentProduct.id_menu,
            nama_menu: state.currentProduct.nama_menu,
            harga: state.currentProduct.harga,
            quantity: state.currentProduct.quantity,
            notes: elements.productNotes.value, // Ambil catatan dari input
            url_gambar: state.currentProduct.url_gambar
        });
    }

    updateCartUI(); // Perbarui UI keranjang
    closeProductModal(); // Tutup modal produk
    showCart(); // Tampilkan keranjang

    if (elements.addToCart.textContent === "Update Pesanan") { // Kembali ke checkout jika mode edit
        openCheckoutPage();
    }
    elements.addToCart.textContent = "Tambah Pesanan"; // Reset teks tombol
};

// Update Cart UI
const updateCartUI = () => {
    elements.cartCount.textContent = state.cart.reduce((total, item) => total + item.quantity, 0); // Update jumlah item di keranjang
    const total = state.cart.reduce((sum, item) => sum + (item.harga * item.quantity), 0); // Hitung total harga keranjang
    elements.cartTotal.textContent = formatCurrency(total); // Perbarui tampilan total harga
    elements.checkoutBtn.disabled = state.cart.length === 0; // Nonaktifkan tombol checkout jika keranjang kosong
};

// Show Cart
const showCart = () => {
    elements.cart.classList.remove('slide-out'); // Hapus class slide-out
    elements.cart.classList.add('slide-in'); // Tambahkan class slide-in
};

// Hide Cart
const hideCart = () => {
    elements.cart.classList.remove('slide-in'); // Hapus class slide-in
    elements.cart.classList.add('slide-out'); // Tambahkan class slide-out
};

// Open Checkout Page
const openCheckoutPage = () => {
    if (state.cart.length === 0) { // Jika keranjang kosong
        alert("Keranjang belanja kosong!"); // Tampilkan peringatan
        return;
    }
    // Update Ordered Items
    elements.orderedItems.innerHTML = state.cart.map(item => `
        <div class="bg-gray-50 rounded-lg p-3" data-id="${item.id_menu}" data-notes="${item.notes || ''}">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-semibold">${item.nama_menu}</h3>
                <span class="text-green-600 font-semibold">${formatCurrency(item.harga * item.quantity)}</span>
            </div>
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center">
                    <button class="item-decrease bg-gray-200 px-2 py-1 rounded-l-lg" data-id="${item.id_menu}" data-notes="${item.notes || ''}">-</button>
                    <span class="item-qty bg-gray-100 px-3 py-1">${item.quantity}</span>
                    <button class="item-increase bg-gray-200 px-2 py-1 rounded-r-lg" data-id="${item.id_menu}" data-notes="${item.notes || ''}">+</button>
                </div>
                <button class="text-blue-500 text-sm item-edit" data-id="${item.id_menu}" data-notes="${item.notes || ''}">Ubah</button>
            </div>
            <p class="text-xs text-gray-500 ${item.notes ? '' : 'italic'}">${item.notes || 'Belum menambah catatan'}</p>
        </div>
    `).join('');

    // Add event listeners to quantity buttons and edit button
    document.querySelectorAll('#orderedItems .item-decrease').forEach(btn => {
        btn.addEventListener('click', () => updateCartItemQuantity(btn.dataset.id, btn.dataset.notes, -1)); // Listener untuk decrease
    });
    document.querySelectorAll('#orderedItems .item-increase').forEach(btn => {
        btn.addEventListener('click', () => updateCartItemQuantity(btn.dataset.id, btn.dataset.notes, 1)); // Listener untuk increase
    });
    document.querySelectorAll('#orderedItems .item-edit').forEach(btn => {
        btn.addEventListener('click', () => editCartItem(parseInt(btn.dataset.id), btn.dataset.notes)); // Listener untuk edit
    });

    // Update Payment Summary
    const subtotal = state.cart.reduce((sum, item) => sum + (item.harga * item.quantity), 0); // Hitung subtotal
    elements.subtotal.textContent = formatCurrency(subtotal); // Perbarui tampilan subtotal
    elements.otherFee.textContent = formatCurrency(state.otherFee); // Perbarui tampilan biaya lainnya
    elements.totalPayment.textContent = formatCurrency(subtotal + state.otherFee); // Perbarui tampilan total pembayaran

    elements.checkoutPage.classList.remove('hidden'); // Tampilkan halaman checkout
};

// Close Checkout Page
const closeCheckoutPage = () => {
    elements.checkoutPage.classList.add('hidden'); // Sembunyikan halaman checkout
};

// Update Cart Item Quantity from Checkout Page
const updateCartItemQuantity = (id, notes, change) => {
    const itemIndex = state.cart.findIndex(item => item.id_menu == parseInt(id) && item.notes === notes); // Cari item di keranjang
    if (itemIndex < 0) return; // Hentikan jika item tidak ditemukan

    const newQuantity = state.cart[itemIndex].quantity + change; // Hitung kuantitas baru
    if (newQuantity < 1) { // Jika kuantitas kurang dari 1
        state.cart.splice(itemIndex, 1); // Hapus item dari keranjang
    } else {
        state.cart[itemIndex].quantity = newQuantity; // Update kuantitas
    }

    updateCartUI(); // Perbarui UI keranjang
    // Jika keranjang kosong setelah penghapusan, kembali ke halaman utama
    if (state.cart.length === 0) { // Jika keranjang kosong
        closeCheckoutPage(); // Tutup halaman checkout
        hideCart(); // Sembunyikan keranjang
    } else {
        openCheckoutPage(); // Refresh halaman checkout untuk menampilkan perubahan
    }
};

// Edit Cart Item (membuka modal produk lagi dengan data item yang sudah ada di keranjang)
const editCartItem = (id, notes) => {
    const product = menuData.find(item => item.id_menu === id); // Cari produk berdasarkan ID
    if (!product) {
        console.error("Product data not found for editing with ID:", id);
        return;
    }

    state.currentProduct = {
        ...product,
        quantity: state.cart.find(item => item.id_menu === id && item.notes === notes).quantity, // Ambil kuantitas dari item di keranjang
        notes: notes, // Set catatan dari item di keranjang
        originalId: id, // Simpan ID asli untuk referensi saat update
        originalNotes: notes // Simpan catatan asli untuk referensi saat update
    };
    
    elements.productImage.src = product.url_gambar; // Set gambar
    elements.productImage.alt = product.nama_menu; // Set alt teks
    elements.productName.textContent = product.nama_menu; // Set nama
    elements.productPrice.textContent = formatCurrency(product.harga); // Set harga
    elements.productNotes.value = notes; // Isi catatan
    elements.productQty.textContent = state.currentProduct.quantity; // Isi kuantitas
    elements.productSubtotal.textContent = formatCurrency(product.harga * state.currentProduct.quantity); // Hitung subtotal

    elements.addToCart.textContent = "Update Pesanan"; // Ubah teks tombol menjadi "Update Pesanan"
    elements.productModal.classList.remove('hidden'); // Tampilkan modal produk
    closeCheckoutPage(); // Tutup halaman checkout saat modal dibuka
};

// Proceed to Payment
const proceedToPayment = () => {
    if (!state.tableNumber || state.tableNumber === '') { // Validasi nomor meja
        alert('Silakan masukkan nomor meja Anda sebelum melanjutkan pembayaran!');
        elements.tableNumber.focus();
        return;
    }

    if (state.cart.length === 0) { // Jika keranjang kosong
        alert('Keranjang belanja Anda kosong. Silakan pilih menu terlebih dahulu.');
        return;
    }
    
    // Hitung total pembayaran akhir
    const finalTotal = state.cart.reduce((sum, item) => sum + (item.harga * item.quantity), 0) + state.otherFee;

    // Simpan data ke localStorage untuk diakses di halaman pembayaran
    localStorage.setItem('cartItems', JSON.stringify(state.cart));
    localStorage.setItem('totalPayment', formatCurrency(finalTotal));
    localStorage.setItem('tableNumber', state.tableNumber);

    // Arahkan ke halaman pembayaran
    window.location.href = 'payment.php'; 
    
    // Catatan: Setelah redirect, state aplikasi akan direset saat halaman baru dimuat.
    // Jadi, Anda tidak perlu mereset cart, tableNumber, dll. di sini.
};


// Event Listeners
elements.searchBtn.addEventListener('click', () => {
    elements.searchModal.classList.remove('hidden'); // Tampilkan modal pencarian
    elements.searchInput.focus(); // Fokuskan kursor ke input pencarian
});

elements.closeSearch.addEventListener('click', () => {
    elements.searchModal.classList.add('hidden'); // Sembunyikan modal pencarian
    elements.searchInput.value = ''; // Bersihkan input pencarian saat ditutup
    handleSearchInput(); // Panggil ini untuk mengembalikan tampilan menu asli
});

elements.menuBtn.addEventListener('click', () => elements.mobileMenu.classList.remove('hidden')); // Tampilkan menu mobile
elements.closeMenu.addEventListener('click', () => elements.mobileMenu.classList.add('hidden')); // Sembunyikan menu mobile

elements.tableNumber.addEventListener('change', (e) => { // Listener untuk perubahan nomor meja
    state.tableNumber = e.target.value; // Update nomor meja di state
});

// Event listener untuk input pencarian: memicu pencarian saat tombol Enter ditekan
elements.searchInput.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
        event.preventDefault(); // Mencegah perilaku default Enter (misalnya, pengiriman form)
        handleSearchInput(); // Panggil fungsi pencarian
        elements.searchModal.classList.add('hidden'); // Sembunyikan modal pencarian setelah Enter
        elements.searchInput.blur(); // Hapus fokus dari input pencarian
    }
}); 

elements.categoryBtns.forEach(btn => { // Listener untuk tombol kategori
    btn.addEventListener('click', () => {
        state.currentCategory = btn.dataset.category; // Update kategori saat ini
        elements.categoryBtns.forEach(b => b.classList.remove('bg-green-600', 'text-white')); // Hapus style aktif dari semua tombol kategori
        elements.categoryBtns.forEach(b => b.classList.add('bg-gray-200')); // Tambahkan style default ke semua tombol kategori
        btn.classList.remove('bg-gray-200'); // Hapus style default dari tombol yang diklik
        btn.classList.add('bg-green-600', 'text-white'); // Tambahkan style aktif ke tombol yang diklik
        loadMenuByCategory(state.currentCategory); // Muat menu berdasarkan kategori
        // Ketika kategori diklik, pastikan input pencarian dikosongkan dan hasil pencarian direset
        elements.searchInput.value = ''; 
    });
});

elements.closeProductModal.addEventListener('click', closeProductModal); // Listener untuk menutup modal produk
elements.decreaseQty.addEventListener('click', () => updateProductQuantity(-1)); // Listener untuk mengurangi kuantitas
elements.increaseQty.addEventListener('click', () => updateProductQuantity(1)); // Listener untuk menambah kuantitas
elements.addToCart.addEventListener('click', addItemToCart); // Listener untuk menambah/mengupdate ke keranjang

elements.checkoutBtn.addEventListener('click', openCheckoutPage); // Listener untuk tombol checkout
elements.backToMain.addEventListener('click', closeCheckoutPage); // Listener untuk kembali dari halaman checkout
elements.proceedPayment.addEventListener('click', proceedToPayment); // Listener untuk melanjutkan pembayaran


// Inisialisasi Aplikasi
fetchMenuData(); // Panggil fungsi untuk mengambil data menu dari database
elements.categoryBtns[0].classList.remove('bg-gray-200'); // Atur tombol kategori "Semua" menjadi aktif secara default
elements.categoryBtns[0].classList.add('bg-green-600', 'text-white'); 
updateCartUI(); // Perbarui UI keranjang saat startup (misal dari local storage jika ada)
// Registrasi Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('ServiceWorker registration successful with scope: ', registration.scope);
            })
            .catch(err => {
                console.log('ServiceWorker registration failed: ', err);
            });
    });
}