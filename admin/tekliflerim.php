<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tekliflerim - Bayi Yönetim Sistemi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .card-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .form-section {
            border-left: 4px solid #3B82F6;
            padding-left: 1rem;
        }
        .required-field::after {
            content: " *";
            color: #EF4444;
        }
        .success-message {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .product-item {
            transition: all 0.2s ease;
        }
        .product-item:hover {
            background-color: #F9FAFB;
        }
        .file-upload-area {
            border: 2px dashed #D1D5DB;
            transition: all 0.2s ease;
        }
        .file-upload-area:hover {
            border-color: #3B82F6;
            background-color: #F8FAFC;
        }
        .file-upload-area.dragover {
            border-color: #3B82F6;
            background-color: #EBF8FF;
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
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">Tekliflerim</h1>
            </div>
            <div class="flex items-center space-x-4">
                <button onclick="createNewQuote()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Yeni Teklif
                </button>
            </div>
        </div>
    </header>

    <!-- Ana İçerik -->
    <main class="max-w-7xl mx-auto p-4 md:p-6">
        <!-- İstatistik Kartları -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
            <div class="bg-white p-4 md:p-6 rounded-lg card-shadow">
                <div class="flex items-center">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 md:ml-4">
                        <p class="text-xs md:text-sm font-medium text-gray-600">Bekleyen Teklifler</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-900">5</p>
                    </div>
                </div>
                <div class="mt-3 md:mt-4">
                    <span class="text-yellow-600 text-xs md:text-sm font-medium">2 Yeni</span>
                    <span class="text-gray-600 text-xs md:text-sm">Bu hafta</span>
                </div>
            </div>

            <div class="bg-white p-4 md:p-6 rounded-lg card-shadow">
                <div class="flex items-center">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="ml-3 md:ml-4">
                        <p class="text-xs md:text-sm font-medium text-gray-600">Onaylanan</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-900">8</p>
                    </div>
                </div>
                <div class="mt-3 md:mt-4">
                    <span class="text-green-600 text-xs md:text-sm font-medium">₺285K</span>
                    <span class="text-gray-600 text-xs md:text-sm">Toplam değer</span>
                </div>
            </div>

            <div class="bg-white p-4 md:p-6 rounded-lg card-shadow">
                <div class="flex items-center">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div class="ml-3 md:ml-4">
                        <p class="text-xs md:text-sm font-medium text-gray-600">Reddedilen</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-900">2</p>
                    </div>
                </div>
                <div class="mt-3 md:mt-4">
                    <span class="text-red-600 text-xs md:text-sm font-medium">₺45K</span>
                    <span class="text-gray-600 text-xs md:text-sm">Kayıp değer</span>
                </div>
            </div>

            <div class="bg-white p-4 md:p-6 rounded-lg card-shadow">
                <div class="flex items-center">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 md:ml-4">
                        <p class="text-xs md:text-sm font-medium text-gray-600">Başarı Oranı</p>
                        <p class="text-xl md:text-2xl font-bold text-gray-900">80%</p>
                    </div>
                </div>
                <div class="mt-3 md:mt-4">
                    <span class="text-blue-600 text-xs md:text-sm font-medium">+5%</span>
                    <span class="text-gray-600 text-xs md:text-sm">Bu ay</span>
                </div>
            </div>
        </div>

        <!-- Filtreler ve Arama -->
        <div class="bg-white rounded-lg card-shadow mb-6">
            <div class="p-4 md:p-6 border-b border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Teklif Listesi</h2>
                        <p class="text-sm text-gray-600 mt-1">Toplam 15 teklif</p>
                    </div>
                    <div class="flex flex-col md:flex-row gap-3">
                        <div class="relative">
                            <input type="text" id="searchQuotes" placeholder="Teklif ara..." class="w-full md:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="filterQuotes()">
                            <option value="">Tüm Durumlar</option>
                            <option value="beklemede">Beklemede</option>
                            <option value="onaylandi">Onaylandı</option>
                            <option value="reddedildi">Reddedildi</option>
                            <option value="suresi-doldu">Süresi Doldu</option>
                        </select>
                        <select id="categoryFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="filterQuotes()">
                            <option value="">Tüm Kategoriler</option>
                            <option value="yazilim">Yazılım</option>
                            <option value="web">Web Tasarım</option>
                            <option value="mobil">Mobil App</option>
                            <option value="donanim">Donanım</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teklif Kartları -->
        <div id="quotes-container" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-6 mb-6">
        </div>

        <!-- Teklif Detay Modal -->
        <div id="quote-detail-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Teklif Detayları</h3>
                        <button onclick="closeQuoteDetail()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div id="quote-detail-content" class="p-6">
                    <!-- İçerik JavaScript ile doldurulacak -->
                </div>
            </div>
        </div>
    </main>

    <script>
        // Örnek teklif verileri
        const quotesData = [
            {
                id: 1,
                title: 'E-Ticaret Platformu',
                customer: 'ABC Teknoloji Ltd.',
                contact: 'Ahmet Yılmaz',
                category: 'web',
                status: 'beklemede',
                amount: 45000,
                deadline: '2024-06-25',
                createdDate: '2024-06-10',
                priority: 'yuksek',
                description: 'Modern e-ticaret platformu geliştirme projesi. Ödeme entegrasyonları, stok yönetimi ve admin paneli dahil.',
                products: [
                    { name: 'Web Tasarım', quantity: 1, unit: 'proje' },
                    { name: 'Backend Geliştirme', quantity: 120, unit: 'saat' },
                    { name: 'Ödeme Entegrasyonu', quantity: 3, unit: 'adet' }
                ]
            },
            {
                id: 2,
                title: 'CRM Sistemi',
                customer: 'XYZ İnşaat A.Ş.',
                contact: 'Mehmet Demir',
                category: 'yazilim',
                status: 'onaylandi',
                amount: 32500,
                deadline: '2024-06-20',
                createdDate: '2024-06-05',
                priority: 'normal',
                description: 'Müşteri ilişkileri yönetim sistemi. Satış takibi, raporlama ve otomatik bildirimler.',
                products: [
                    { name: 'CRM Yazılımı', quantity: 1, unit: 'proje' },
                    { name: 'Kullanıcı Eğitimi', quantity: 8, unit: 'saat' }
                ]
            },
            {
                id: 3,
                title: 'Mobil Uygulama',
                customer: 'DEF Elektronik',
                contact: 'Ayşe Kaya',
                category: 'mobil',
                status: 'reddedildi',
                amount: 28000,
                deadline: '2024-06-15',
                createdDate: '2024-05-28',
                priority: 'normal',
                description: 'iOS ve Android uyumlu mobil uygulama geliştirme.',
                products: [
                    { name: 'Mobil App Geliştirme', quantity: 1, unit: 'proje' },
                    { name: 'App Store Yayınlama', quantity: 2, unit: 'adet' }
                ]
            },
            {
                id: 4,
                title: 'Kurumsal Web Sitesi',
                customer: 'GHI Lojistik',
                contact: 'Fatma Özkan',
                category: 'web',
                status: 'beklemede',
                amount: 15000,
                deadline: '2024-07-01',
                createdDate: '2024-06-12',
                priority: 'normal',
                description: 'Kurumsal kimliğe uygun responsive web sitesi tasarımı.',
                products: [
                    { name: 'Web Tasarım', quantity: 1, unit: 'proje' },
                    { name: 'İçerik Yönetimi', quantity: 1, unit: 'proje' }
                ]
            },
            {
                id: 5,
                title: 'Stok Yönetim Sistemi',
                customer: 'JKL Ticaret',
                contact: 'Ali Veli',
                category: 'yazilim',
                status: 'onaylandi',
                amount: 38000,
                deadline: '2024-06-30',
                createdDate: '2024-06-08',
                priority: 'yuksek',
                description: 'Depo ve stok yönetimi için özel yazılım çözümü.',
                products: [
                    { name: 'Stok Yazılımı', quantity: 1, unit: 'proje' },
                    { name: 'Barkod Entegrasyonu', quantity: 1, unit: 'proje' }
                ]
            }
        ];

        let filteredQuotes = [...quotesData];

        // Sayfa yüklendiğinde teklifleri göster
        window.addEventListener('load', function() {
            renderQuotes();
            setupSearch();
        });

        // Teklifleri render et
        function renderQuotes() {
            const container = document.getElementById('quotes-container');
            container.innerHTML = '';

            if (filteredQuotes.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500 text-lg">Aradığınız kriterlere uygun teklif bulunamadı</p>
                    </div>
                `;
                return;
            }

            filteredQuotes.forEach(quote => {
                const quoteCard = createQuoteCard(quote);
                container.appendChild(quoteCard);
            });
        }

        // Teklif kartı oluştur
        function createQuoteCard(quote) {
            const card = document.createElement('div');
            card.className = 'bg-white rounded-lg card-shadow hover:shadow-lg transition-shadow cursor-pointer';
            
            const statusColors = {
                'beklemede': 'bg-yellow-100 text-yellow-800',
                'onaylandi': 'bg-green-100 text-green-800',
                'reddedildi': 'bg-red-100 text-red-800',
                'suresi-doldu': 'bg-gray-100 text-gray-800'
            };

            const statusTexts = {
                'beklemede': 'Beklemede',
                'onaylandi': 'Onaylandı',
                'reddedildi': 'Reddedildi',
                'suresi-doldu': 'Süresi Doldu'
            };

            const priorityColors = {
                'acil': 'text-red-600',
                'yuksek': 'text-orange-600',
                'normal': 'text-gray-600'
            };

            const priorityTexts = {
                'acil': 'Acil',
                'yuksek': 'Yüksek',
                'normal': 'Normal'
            };

            card.innerHTML = `
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">${quote.title}</h3>
                            <p class="text-sm text-gray-600">${quote.customer}</p>
                            <p class="text-xs text-gray-500">${quote.contact}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full ${statusColors[quote.status]}">
                            ${statusTexts[quote.status]}
                        </span>
                    </div>

                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Tutar:</span>
                            <span class="font-semibold text-gray-900">₺${quote.amount.toLocaleString()}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Son Tarih:</span>
                            <span class="text-sm text-gray-900">${formatDate(quote.deadline)}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Öncelik:</span>
                            <span class="text-sm font-medium ${priorityColors[quote.priority]}">${priorityTexts[quote.priority]}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Oluşturma:</span>
                            <span class="text-sm text-gray-900">${formatDate(quote.createdDate)}</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex space-x-2">
                            <button onclick="viewQuoteDetail(${quote.id})" class="flex-1 px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Detay Görüntüle
                            </button>
                            ${quote.status === 'beklemede' ? `
                                <button onclick="editQuote(${quote.id})" class="flex-1 px-3 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                    Düzenle
                                </button>
                            ` : ''}
                            ${quote.status === 'onaylandi' ? `
                                <button onclick="createContract(${quote.id})" class="flex-1 px-3 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                    Sözleşme
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;

            return card;
        }

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
            const searchInput = document.getElementById('searchQuotes');
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                filterQuotes(searchTerm);
            });
        }

        // Filtreleme
        function filterQuotes(searchTerm = '') {
            const statusFilter = document.getElementById('statusFilter').value;
            const categoryFilter = document.getElementById('categoryFilter').value;
            
            filteredQuotes = quotesData.filter(quote => {
                const matchesSearch = searchTerm === '' || 
                    quote.title.toLowerCase().includes(searchTerm) ||
                    quote.customer.toLowerCase().includes(searchTerm) ||
                    quote.contact.toLowerCase().includes(searchTerm);
                
                const matchesStatus = statusFilter === '' || quote.status === statusFilter;
                const matchesCategory = categoryFilter === '' || quote.category === categoryFilter;
                
                return matchesSearch && matchesStatus && matchesCategory;
            });
            
            renderQuotes();
        }

        // Teklif detayını görüntüle
        function viewQuoteDetail(quoteId) {
            const quote = quotesData.find(q => q.id === quoteId);
            if (!quote) return;

            const modal = document.getElementById('quote-detail-modal');
            const content = document.getElementById('quote-detail-content');
            
            content.innerHTML = `
                <div class="space-y-6">
                    <!-- Temel Bilgiler -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Proje Bilgileri</h4>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Proje Adı:</span>
                                    <p class="text-gray-900">${quote.title}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Açıklama:</span>
                                    <p class="text-gray-900">${quote.description}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Kategori:</span>
                                    <p class="text-gray-900">${getCategoryName(quote.category)}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Öncelik:</span>
                                    <p class="text-gray-900">${getPriorityName(quote.priority)}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Müşteri Bilgileri</h4>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Şirket:</span>
                                    <p class="text-gray-900">${quote.customer}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">İletişim Kişisi:</span>
                                    <p class="text-gray-900">${quote.contact}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Durum:</span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(quote.status)}">
                                        ${getStatusName(quote.status)}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Toplam Tutar:</span>
                                    <p class="text-xl font-bold text-gray-900">₺${quote.amount.toLocaleString()}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ürün/Hizmet Listesi -->
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Ürün/Hizmet Listesi</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full border border-gray-200 rounded-lg">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Ürün/Hizmet</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Miktar</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Birim</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    ${quote.products.map(product => `
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">${product.name}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900">${product.quantity}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900">${product.unit}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tarihler -->
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Önemli Tarihler</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-600">Oluşturma Tarihi:</span>
                                <p class="text-gray-900">${formatDate(quote.createdDate)}</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-600">Son Teslim Tarihi:</span>
                                <p class="text-gray-900">${formatDate(quote.deadline)}</p>
                            </div>
                        </div>
                    </div>

                    <!-- İşlem Butonları -->
                    <div class="flex flex-col md:flex-row gap-3 pt-6 border-t border-gray-200">
                        ${quote.status === 'beklemede' ? `
                            <button onclick="editQuote(${quote.id})" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Teklifi Düzenle
                            </button>
                            <button onclick="duplicateQuote(${quote.id})" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Kopyala
                            </button>
                        ` : ''}
                        ${quote.status === 'onaylandi' ? `
                            <button onclick="createContract(${quote.id})" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                Sözleşme Oluştur
                            </button>
                        ` : ''}
                        ${quote.status === 'reddedildi' ? `
                            <button onclick="resubmitQuote(${quote.id})" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Yeniden Gönder
                            </button>
                        ` : ''}
                        <button onclick="downloadQuotePDF(${quote.id})" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            PDF İndir
                        </button>
                    </div>
                </div>
            `;
            
            modal.classList.remove('hidden');
        }

        // Modal kapatma
        function closeQuoteDetail() {
            document.getElementById('quote-detail-modal').classList.add('hidden');
        }

        // Yardımcı fonksiyonlar
        function getCategoryName(category) {
            const categories = {
                'yazilim': 'Yazılım Geliştirme',
                'web': 'Web Tasarım',
                'mobil': 'Mobil Uygulama',
                'donanim': 'Donanım'
            };
            return categories[category] || category;
        }

        function getPriorityName(priority) {
            const priorities = {
                'acil': 'Acil',
                'yuksek': 'Yüksek',
                'normal': 'Normal'
            };
            return priorities[priority] || priority;
        }

        function getStatusName(status) {
            const statuses = {
                'beklemede': 'Beklemede',
                'onaylandi': 'Onaylandı',
                'reddedildi': 'Reddedildi',
                'suresi-doldu': 'Süresi Doldu'
            };
            return statuses[status] || status;
        }

        function getStatusColor(status) {
            const colors = {
                'beklemede': 'bg-yellow-100 text-yellow-800',
                'onaylandi': 'bg-green-100 text-green-800',
                'reddedildi': 'bg-red-100 text-red-800',
                'suresi-doldu': 'bg-gray-100 text-gray-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }

        // İşlem fonksiyonları
        function createNewQuote() {
            alert('Yeni teklif oluşturma sayfasına yönlendiriliyorsunuz...');
        }

        function editQuote(quoteId) {
            alert(`Teklif #${quoteId} düzenleme sayfasına yönlendiriliyorsunuz...`);
        }

        function duplicateQuote(quoteId) {
            if (confirm('Bu teklifi kopyalamak istediğinizden emin misiniz?')) {
                alert(`Teklif #${quoteId} kopyalandı ve düzenleme sayfasına yönlendiriliyorsunuz...`);
            }
        }

        function createContract(quoteId) {
            alert(`Teklif #${quoteId} için sözleşme oluşturma sayfasına yönlendiriliyorsunuz...`);
        }

        function resubmitQuote(quoteId) {
            if (confirm('Bu teklifi yeniden göndermek istediğinizden emin misiniz?')) {
                alert(`Teklif #${quoteId} yeniden gönderildi.`);
            }
        }

        function downloadQuotePDF(quoteId) {
            alert(`Teklif #${quoteId} PDF olarak indiriliyor...`);
        }

        function goBack() {
            window.history.back();
        }

        // Modal dışına tıklayınca kapatma
        document.getElementById('quote-detail-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeQuoteDetail();
            }
        });
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'97227f27a018df5f',t:'MTc1NTY5OTI2My4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
