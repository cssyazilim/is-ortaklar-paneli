<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Talepleri - Bayi Yönetim Sistemi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .card-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .success-message {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .order-card {
            transition: all 0.2s ease;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .progress-bar {
            transition: width 0.3s ease;
        }
        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            transition: all 0.2s ease;
        }
        .status-dot.active {
            transform: scale(1.2);
        }
        @media (max-width: 640px) {
            .mobile-scroll {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Üst Bar -->
    <header class="bg-white shadow-sm border-b border-gray-200 px-4 md:px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <button onclick="goBack()" class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">ERP Talepleri</h1>
            </div>
            <div class="flex items-center space-x-2 md:space-x-4">
                <button onclick="createNewOrder()" class="px-3 md:px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center text-sm md:text-base">
                    <svg class="w-4 h-4 mr-1 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="hidden sm:inline">Yeni Talep</span>
                    <span class="sm:hidden">Yeni</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Ana İçerik -->
    <main class="max-w-7xl mx-auto p-4 md:p-6">
        <!-- İstatistik Kartları -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6 mb-6">
            <div class="bg-white p-3 md:p-6 rounded-lg card-shadow">
                <div class="flex items-center">
                    <div class="w-8 h-8 md:w-12 md:h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 md:w-6 md:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-2 md:ml-4">
                        <p class="text-xs md:text-sm font-medium text-gray-600">Yeni Talepler</p>
                        <p class="text-lg md:text-2xl font-bold text-gray-900">5</p>
                    </div>
                </div>
                <div class="mt-2 md:mt-4">
                    <span class="text-yellow-600 text-xs md:text-sm font-medium">₺125K</span>
                </div>
            </div>

            <div class="bg-white p-3 md:p-6 rounded-lg card-shadow">
                <div class="flex items-center">
                    <div class="w-8 h-8 md:w-12 md:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 md:w-6 md:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-2 md:ml-4">
                        <p class="text-xs md:text-sm font-medium text-gray-600">Teklif Hazırlanıyor</p>
                        <p class="text-lg md:text-2xl font-bold text-gray-900">3</p>
                    </div>
                </div>
                <div class="mt-2 md:mt-4">
                    <span class="text-blue-600 text-xs md:text-sm font-medium">₺85K</span>
                </div>
            </div>

            <div class="bg-white p-3 md:p-6 rounded-lg card-shadow">
                <div class="flex items-center">
                    <div class="w-8 h-8 md:w-12 md:h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 md:w-6 md:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-2 md:ml-4">
                        <p class="text-xs md:text-sm font-medium text-gray-600">Satış Yapılan</p>
                        <p class="text-lg md:text-2xl font-bold text-gray-900">8</p>
                    </div>
                </div>
                <div class="mt-2 md:mt-4">
                    <span class="text-green-600 text-xs md:text-sm font-medium">₺340K</span>
                </div>
            </div>

            <div class="bg-white p-3 md:p-6 rounded-lg card-shadow">
                <div class="flex items-center">
                    <div class="w-8 h-8 md:w-12 md:h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 md:w-6 md:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-2 md:ml-4">
                        <p class="text-xs md:text-sm font-medium text-gray-600">Toplam Ciro</p>
                        <p class="text-lg md:text-2xl font-bold text-gray-900">₺550K</p>
                    </div>
                </div>
                <div class="mt-2 md:mt-4">
                    <span class="text-purple-600 text-xs md:text-sm font-medium">Bu ay</span>
                </div>
            </div>
        </div>

        <!-- Filtreler ve Arama -->
        <div class="bg-white rounded-lg card-shadow mb-6">
            <div class="p-4 md:p-6 border-b border-gray-200">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Müşteri Talepleri</h2>
                        <p class="text-sm text-gray-600 mt-1">Toplam 6 ERP talebi</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="relative">
                            <input type="text" id="searchOrders" placeholder="Sipariş ara..." class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" onchange="filterOrders()">
                            <option value="">Tüm Durumlar</option>
                            <option value="yeni-talep">Yeni Talep</option>
                            <option value="teklif-hazirlaniyor">Teklif Hazırlanıyor</option>
                            <option value="teklif-gonderildi">Teklif Gönderildi</option>
                            <option value="satis-yapildi">Satış Yapıldı</option>
                            <option value="iptal-edildi">İptal Edildi</option>
                        </select>
                        <input type="date" id="dateFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" onchange="filterOrders()">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sipariş Kartları -->
        <div id="orders-container" class="space-y-4 md:space-y-6 mb-6">
        </div>

        <!-- Sipariş Detay Modal -->
        <div id="order-detail-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-4 md:p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Sipariş Detayları</h3>
                        <button onclick="closeOrderDetail()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div id="order-detail-content" class="p-4 md:p-6">
                    <!-- İçerik JavaScript ile doldurulacak -->
                </div>
            </div>
        </div>
    </main>

    <script>
        // Örnek ERP talep verileri
        const ordersData = [
            {
                id: 'TLP-2024-001',
                title: 'Tam Entegre ERP Sistemi',
                customer: 'ABC Tekstil Ltd.',
                status: 'yeni-talep',
                amount: 85000,
                orderDate: '2024-06-20',
                requestDate: '2024-06-20',
                estimatedValue: 85000,
                modules: [
                    { name: 'Muhasebe Modülü', included: true, price: 25000 },
                    { name: 'İnsan Kaynakları', included: true, price: 20000 },
                    { name: 'Stok Yönetimi', included: true, price: 18000 },
                    { name: 'CRM Modülü', included: true, price: 15000 },
                    { name: 'Raporlama', included: true, price: 7000 }
                ],
                companyInfo: 'Atatürk Mah. Tekstil Sanayi Sitesi No:45 Bursa',
                contactPerson: 'Ahmet Yılmaz - Genel Müdür',
                phone: '0224 555 0123',
                requirements: 'Şirket büyüdükçe mevcut sistemler yetersiz kalmaya başladı. Tüm departmanları entegre edecek kapsamlı bir ERP sistemi ihtiyacımız var.'
            },
            {
                id: 'TLP-2024-002',
                title: 'Muhasebe ve Finans ERP',
                customer: 'XYZ İnşaat A.Ş.',
                status: 'teklif-hazirlaniyor',
                amount: 45000,
                orderDate: '2024-06-18',
                requestDate: '2024-06-18',
                estimatedValue: 45000,
                modules: [
                    { name: 'Muhasebe Modülü', included: true, price: 25000 },
                    { name: 'Mali Müşavir Entegrasyonu', included: true, price: 12000 },
                    { name: 'Bütçe Planlama', included: true, price: 8000 }
                ],
                companyInfo: 'İstiklal Cad. İş Merkezi A Blok Kat:5 İstanbul',
                contactPerson: 'Fatma Demir - Mali İşler Müdürü',
                phone: '0212 555 0456',
                requirements: 'Muhasebe işlemlerimizi dijitalleştirmek ve mali müşavirimizle entegre çalışabilecek bir sistem istiyoruz.'
            },
            {
                id: 'TLP-2024-003',
                title: 'Üretim ve Stok ERP',
                customer: 'DEF Makina San.',
                status: 'teklif-gonderildi',
                amount: 65000,
                orderDate: '2024-06-15',
                requestDate: '2024-06-15',
                estimatedValue: 65000,
                modules: [
                    { name: 'Üretim Planlama', included: true, price: 30000 },
                    { name: 'Stok Yönetimi', included: true, price: 20000 },
                    { name: 'Kalite Kontrol', included: true, price: 15000 }
                ],
                companyInfo: 'Organize Sanayi Bölgesi 5. Cad. No:78 Kocaeli',
                contactPerson: 'Mehmet Kaya - Üretim Müdürü',
                phone: '0262 555 0789',
                requirements: 'Üretim süreçlerimizi optimize etmek ve stok takibini daha verimli hale getirmek istiyoruz.'
            },
            {
                id: 'TLP-2024-004',
                title: 'Perakende ERP Sistemi',
                customer: 'GHI Market Zinciri',
                status: 'satis-yapildi',
                amount: 120000,
                orderDate: '2024-06-10',
                requestDate: '2024-06-10',
                estimatedValue: 120000,
                modules: [
                    { name: 'POS Entegrasyonu', included: true, price: 35000 },
                    { name: 'Stok Yönetimi', included: true, price: 25000 },
                    { name: 'CRM Modülü', included: true, price: 20000 },
                    { name: 'Muhasebe Modülü', included: true, price: 25000 },
                    { name: 'Raporlama ve BI', included: true, price: 15000 }
                ],
                companyInfo: 'Merkez Mah. Ticaret Cad. No:123 Ankara',
                contactPerson: 'Ayşe Özkan - İT Müdürü',
                phone: '0312 555 0321',
                requirements: 'Tüm mağazalarımızı merkezi bir sistemle yönetmek ve gerçek zamanlı raporlama yapmak istiyoruz.'
            },
            {
                id: 'TLP-2024-005',
                title: 'Lojistik ERP Çözümü',
                customer: 'JKL Lojistik',
                status: 'teklif-hazirlaniyor',
                amount: 55000,
                orderDate: '2024-06-12',
                requestDate: '2024-06-12',
                estimatedValue: 55000,
                modules: [
                    { name: 'Kargo Takip Sistemi', included: true, price: 20000 },
                    { name: 'Depo Yönetimi', included: true, price: 18000 },
                    { name: 'Araç Takip Entegrasyonu', included: true, price: 12000 },
                    { name: 'Müşteri Portalı', included: true, price: 5000 }
                ],
                companyInfo: 'Liman Mah. Lojistik Merkezi No:67 İzmir',
                contactPerson: 'Okan Şen - Operasyon Müdürü',
                phone: '0232 555 0654',
                requirements: 'Kargo operasyonlarımızı dijitalleştirmek ve müşterilerimize daha iyi hizmet vermek istiyoruz.'
            },
            {
                id: 'TLP-2024-006',
                title: 'Sağlık Sektörü ERP',
                customer: 'MNO Özel Hastane',
                status: 'iptal-edildi',
                amount: 95000,
                orderDate: '2024-06-08',
                requestDate: '2024-06-08',
                estimatedValue: 95000,
                modules: [
                    { name: 'Hasta Yönetim Sistemi', included: true, price: 40000 },
                    { name: 'Muhasebe Modülü', included: true, price: 25000 },
                    { name: 'İnsan Kaynakları', included: true, price: 20000 },
                    { name: 'Stok ve Eczane', included: true, price: 10000 }
                ],
                companyInfo: 'Sağlık Mah. Hastane Cad. No:12 Adana',
                contactPerson: 'Dr. Zeynep Aktaş - Başhekim',
                phone: '0322 555 0987',
                requirements: 'Hastane operasyonlarımızı entegre bir sistemle yönetmek istiyoruz.'
            }
        ];

        let filteredOrders = [...ordersData];

        // Sayfa yüklendiğinde siparişleri göster
        window.addEventListener('load', function() {
            renderOrders();
            setupSearch();
        });

        // Siparişleri render et
        function renderOrders() {
            const container = document.getElementById('orders-container');
            container.innerHTML = '';

            if (filteredOrders.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414a1 1 0 00-.707-.293H8"></path>
                        </svg>
                        <p class="text-gray-500 text-lg">Aradığınız kriterlere uygun sipariş bulunamadı</p>
                    </div>
                `;
                return;
            }

            filteredOrders.forEach(order => {
                const orderCard = createOrderCard(order);
                container.appendChild(orderCard);
            });
        }

        // Sipariş kartı oluştur
        function createOrderCard(order) {
            const card = document.createElement('div');
            card.className = 'bg-white rounded-lg card-shadow order-card';
            
            const statusColors = {
                'yeni-talep': 'bg-yellow-100 text-yellow-800',
                'teklif-hazirlaniyor': 'bg-blue-100 text-blue-800',
                'teklif-gonderildi': 'bg-purple-100 text-purple-800',
                'satis-yapildi': 'bg-green-100 text-green-800',
                'iptal-edildi': 'bg-red-100 text-red-800'
            };

            const statusTexts = {
                'yeni-talep': 'Yeni Talep',
                'teklif-hazirlaniyor': 'Teklif Hazırlanıyor',
                'teklif-gonderildi': 'Teklif Gönderildi',
                'satis-yapildi': 'Satış Yapıldı',
                'iptal-edildi': 'İptal Edildi'
            };

            card.innerHTML = `
                <div class="p-4 md:p-6">
                    <!-- Üst Kısım -->
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
                        <div class="flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">${order.title}</h3>
                                <span class="px-2 py-1 text-xs font-medium rounded-full ${statusColors[order.status]} w-fit">
                                    ${statusTexts[order.status]}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mb-1">Talep No: ${order.id}</p>
                            <p class="text-sm text-gray-600 mb-1">${order.customer}</p>
                            <p class="text-xs text-gray-500">Talep Tarihi: ${formatDate(order.requestDate)}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xl md:text-2xl font-bold text-gray-900">₺${order.estimatedValue.toLocaleString()}</p>
                            <p class="text-sm text-gray-600">Tahmini Değer</p>
                        </div>
                    </div>

                    <!-- İletişim Bilgileri -->
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center mb-2">
                            <svg class="w-4 h-4 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">${order.contactPerson}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span class="text-sm text-gray-600">${order.phone}</span>
                        </div>
                    </div>

                    <!-- ERP Modülleri -->
                    <div class="mb-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">${order.modules.length} ERP Modülü</p>
                        <div class="mobile-scroll">
                            <div class="flex gap-2 min-w-max md:min-w-0">
                                ${order.modules.slice(0, 3).map(module => `
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs whitespace-nowrap">
                                        ${module.name}
                                    </span>
                                `).join('')}
                                ${order.modules.length > 3 ? `
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                                        +${order.modules.length - 3} modül daha
                                    </span>
                                ` : ''}
                            </div>
                        </div>
                    </div>

                    <!-- İşlem Butonları -->
                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <button onclick="viewOrderDetail('${order.id}')" class="flex-1 px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Detay Görüntüle
                            </button>
                            ${order.status === 'yeni-talep' ? `
                                <button onclick="prepareBid('${order.id}')" class="flex-1 px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                    Teklif Hazırla
                                </button>
                            ` : ''}
                            ${order.status === 'teklif-hazirlaniyor' ? `
                                <button onclick="sendBid('${order.id}')" class="flex-1 px-4 py-2 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                    Teklif Gönder
                                </button>
                            ` : ''}
                            ${order.status === 'teklif-gonderildi' ? `
                                <button onclick="followUp('${order.id}')" class="flex-1 px-4 py-2 text-sm border border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50 transition-colors">
                                    Takip Et
                                </button>
                            ` : ''}
                            ${order.status === 'satis-yapildi' ? `
                                <button onclick="viewContract('${order.id}')" class="flex-1 px-4 py-2 text-sm border border-green-300 text-green-700 rounded-lg hover:bg-green-50 transition-colors">
                                    Sözleşme Görüntüle
                                </button>
                            ` : ''}
                            <button onclick="contactCustomer('${order.id}')" class="flex-1 px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Müşteriyi Ara
                            </button>
                        </div>
                    </div>
                </div>
            `;

            return card;
        }

        // ERP talep detay modalı için içerik oluştur

        // Tarih formatlama
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('tr-TR', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        // Arama fonksiyonu
        function setupSearch() {
            const searchInput = document.getElementById('searchOrders');
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                filterOrders(searchTerm);
            });
        }

        // Filtreleme
        function filterOrders(searchTerm = '') {
            const statusFilter = document.getElementById('statusFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;
            
            filteredOrders = ordersData.filter(order => {
                const matchesSearch = searchTerm === '' || 
                    order.id.toLowerCase().includes(searchTerm) ||
                    order.title.toLowerCase().includes(searchTerm) ||
                    order.customer.toLowerCase().includes(searchTerm);
                
                const matchesStatus = statusFilter === '' || order.status === statusFilter;
                const matchesDate = dateFilter === '' || order.orderDate === dateFilter;
                
                return matchesSearch && matchesStatus && matchesDate;
            });
            
            renderOrders();
        }

        // Sipariş detayını görüntüle
        function viewOrderDetail(orderId) {
            const order = ordersData.find(o => o.id === orderId);
            if (!order) return;

            const modal = document.getElementById('order-detail-modal');
            const content = document.getElementById('order-detail-content');
            
            content.innerHTML = `
                <div class="space-y-6">
                    <!-- Talep Bilgileri -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Talep Bilgileri</h4>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Talep No:</span>
                                    <p class="text-gray-900">${order.id}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">ERP Sistemi:</span>
                                    <p class="text-gray-900">${order.title}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Durum:</span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(order.status)}">
                                        ${getStatusName(order.status)}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Tahmini Değer:</span>
                                    <p class="text-gray-900 font-bold">₺${order.estimatedValue.toLocaleString()}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Müşteri Bilgileri</h4>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Şirket:</span>
                                    <p class="text-gray-900">${order.customer}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">İletişim:</span>
                                    <p class="text-gray-900">${order.contactPerson}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Telefon:</span>
                                    <p class="text-gray-900">${order.phone}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Adres:</span>
                                    <p class="text-gray-900">${order.companyInfo}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Talep Edilen Modüller -->
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Talep Edilen ERP Modülleri</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full border border-gray-200 rounded-lg">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Modül Adı</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Durum</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Tahmini Fiyat</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    ${order.modules.map(module => `
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">${module.name}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full ${module.included ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                                    ${module.included ? 'Dahil' : 'Opsiyonel'}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">₺${module.price.toLocaleString()}</td>
                                        </tr>
                                    `).join('')}
                                    <tr class="bg-gray-50">
                                        <td colspan="2" class="px-4 py-3 text-sm font-medium text-gray-900">Toplam Tahmini Değer</td>
                                        <td class="px-4 py-3 text-sm font-bold text-gray-900">₺${order.estimatedValue.toLocaleString()}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Müşteri İhtiyaçları -->
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Müşteri İhtiyaçları ve Notlar</h4>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-gray-700">${order.requirements}</p>
                        </div>
                    </div>

                    <!-- Tarihler -->
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Önemli Tarihler</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-600">Talep Tarihi:</span>
                                <p class="text-gray-900">${formatDate(order.requestDate)}</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-600">Son Güncelleme:</span>
                                <p class="text-gray-900">${formatDate(order.orderDate)}</p>
                            </div>
                        </div>
                    </div>

                    <!-- İşlem Butonları -->
                    <div class="flex flex-col md:flex-row gap-3 pt-6 border-t border-gray-200">
                        ${order.status === 'yeni-talep' ? `
                            <button onclick="prepareBid('${order.id}')" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                Teklif Hazırla
                            </button>
                        ` : ''}
                        ${order.status === 'teklif-hazirlaniyor' ? `
                            <button onclick="sendBid('${order.id}')" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                Teklif Gönder
                            </button>
                        ` : ''}
                        ${order.status === 'teklif-gonderildi' ? `
                            <button onclick="followUp('${order.id}')" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Müşteriyi Takip Et
                            </button>
                        ` : ''}
                        ${order.status === 'satis-yapildi' ? `
                            <button onclick="viewContract('${order.id}')" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                Sözleşme Görüntüle
                            </button>
                        ` : ''}
                        <button onclick="contactCustomer('${order.id}')" class="px-6 py-2 border border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50 transition-colors">
                            Müşteriyi Ara
                        </button>
                        <button onclick="downloadBid('${order.id}')" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Teklif İndir
                        </button>
                    </div>
                </div>
            `;
            
            modal.classList.remove('hidden');
        }

        // Modal kapatma
        function closeOrderDetail() {
            document.getElementById('order-detail-modal').classList.add('hidden');
        }

        // Yardımcı fonksiyonlar
        function getStatusName(status) {
            const statuses = {
                'yeni-talep': 'Yeni Talep',
                'teklif-hazirlaniyor': 'Teklif Hazırlanıyor',
                'teklif-gonderildi': 'Teklif Gönderildi',
                'satis-yapildi': 'Satış Yapıldı',
                'iptal-edildi': 'İptal Edildi'
            };
            return statuses[status] || status;
        }

        function getStatusColor(status) {
            const colors = {
                'yeni-talep': 'bg-yellow-100 text-yellow-800',
                'teklif-hazirlaniyor': 'bg-blue-100 text-blue-800',
                'teklif-gonderildi': 'bg-purple-100 text-purple-800',
                'satis-yapildi': 'bg-green-100 text-green-800',
                'iptal-edildi': 'bg-red-100 text-red-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }

        // İşlem fonksiyonları
        function createNewOrder() {
            alert('Yeni ERP talebi oluşturma sayfasına yönlendiriliyorsunuz...');
        }

        function prepareBid(requestId) {
            alert(`${requestId} numaralı talep için teklif hazırlama sayfasına yönlendiriliyorsunuz...`);
        }

        function sendBid(requestId) {
            if (confirm('Teklifi müşteriye göndermek istediğinizden emin misiniz?')) {
                alert(`${requestId} numaralı talep için teklif müşteriye gönderildi.`);
            }
        }

        function followUp(requestId) {
            alert(`${requestId} numaralı talep için müşteri takip sayfasına yönlendiriliyorsunuz...`);
        }

        function viewContract(requestId) {
            alert(`${requestId} numaralı satış için sözleşme görüntüleniyor...`);
        }

        function contactCustomer(requestId) {
            const order = ordersData.find(o => o.id === requestId);
            if (order) {
                alert(`${order.contactPerson} aranıyor...\nTelefon: ${order.phone}`);
            }
        }

        function downloadBid(requestId) {
            alert(`${requestId} numaralı talep için teklif indiriliyor...`);
        }

        function goBack() {
            window.history.back();
        }

        // Modal dışına tıklayınca kapatma
        document.getElementById('order-detail-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderDetail();
            }
        });

        // Responsive davranış için window resize dinleyicisi
        window.addEventListener('resize', function() {
            // Mobil görünümde timeline'ı gizle/göster
            const timelines = document.querySelectorAll('.order-timeline');
            timelines.forEach(timeline => {
                if (window.innerWidth < 768) {
                    timeline.style.display = 'none';
                } else {
                    timeline.style.display = 'block';
                }
            });
        });
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9722a49340046117',t:'MTc1NTcwMDc5NS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
