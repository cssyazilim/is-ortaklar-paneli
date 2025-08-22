<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faturalar & Cari - Bayi Yönetim Sistemi</title>
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
        .invoice-card {
            transition: all 0.2s ease;
        }
        .invoice-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .balance-positive {
            color: #059669;
        }
        .balance-negative {
            color: #dc2626;
        }
        .tab-active {
            border-bottom: 2px solid #3b82f6;
            color: #3b82f6;
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
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">Faturalar & Cari</h1>
            </div>
            <div class="flex items-center space-x-2 md:space-x-4">
                <button onclick="exportInvoices()" class="px-3 md:px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center text-sm md:text-base">
                    <svg class="w-4 h-4 mr-1 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="hidden sm:inline">Rapor Al</span>
                    <span class="sm:hidden">Rapor</span>
                </button>
                <button onclick="makePayment()" class="px-3 md:px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center text-sm md:text-base">
                    <svg class="w-4 h-4 mr-1 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="hidden sm:inline">Ödeme Yap</span>
                    <span class="sm:hidden">Ödeme</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Ana İçerik -->
    <main class="max-w-7xl mx-auto p-4 md:p-6">
        <!-- Cari Hesap Özeti -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-3 md:gap-6 mb-6">
            <div class="bg-white p-4 md:p-6 rounded-lg card-shadow">
                <div class="flex items-center">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 md:ml-4">
                        <p class="text-xs md:text-sm font-medium text-gray-600">Cari Bakiye</p>
                        <p class="text-lg md:text-2xl font-bold balance-negative">-₺25,750</p>
                    </div>
                </div>
                <div class="mt-3 md:mt-4">
                    <span class="text-red-600 text-xs md:text-sm font-medium">Borç bakiyesi</span>
                </div>
            </div>

            <div class="bg-white p-4 md:p-6 rounded-lg card-shadow">
                <div class="flex items-center">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 md:ml-4">
                        <p class="text-xs md:text-sm font-medium text-gray-600">Vadesi Geçen</p>
                        <p class="text-lg md:text-2xl font-bold text-red-600">₺12,500</p>
                    </div>
                </div>
                <div class="mt-3 md:mt-4">
                    <span class="text-red-600 text-xs md:text-sm font-medium">3 fatura</span>
                </div>
            </div>

            <div class="bg-white p-4 md:p-6 rounded-lg card-shadow">
                <div class="flex items-center">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 md:ml-4">
                        <p class="text-xs md:text-sm font-medium text-gray-600">Bu Ay Vadesi Gelen</p>
                        <p class="text-lg md:text-2xl font-bold text-yellow-600">₺18,200</p>
                    </div>
                </div>
                <div class="mt-3 md:mt-4">
                    <span class="text-yellow-600 text-xs md:text-sm font-medium">5 fatura</span>
                </div>
            </div>

            <div class="bg-white p-4 md:p-6 rounded-lg card-shadow">
                <div class="flex items-center">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 md:ml-4">
                        <p class="text-xs md:text-sm font-medium text-gray-600">Bu Ay Ödenen</p>
                        <p class="text-lg md:text-2xl font-bold text-green-600">₺42,300</p>
                    </div>
                </div>
                <div class="mt-3 md:mt-4">
                    <span class="text-green-600 text-xs md:text-sm font-medium">8 ödeme</span>
                </div>
            </div>
        </div>

        <!-- Sekmeler -->
        <div class="bg-white rounded-lg card-shadow mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-4 md:px-6" aria-label="Tabs">
                    <button onclick="switchTab('invoices')" id="tab-invoices" class="py-4 px-1 border-b-2 font-medium text-sm tab-active">
                        Faturalar
                    </button>
                    <button onclick="switchTab('payments')" id="tab-payments" class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Ödemeler
                    </button>
                    <button onclick="switchTab('account-statement')" id="tab-account-statement" class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Cari Ekstre
                    </button>
                </nav>
            </div>

            <!-- Filtreler -->
            <div class="p-4 md:p-6 border-b border-gray-200">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900" id="section-title">Faturalar</h2>
                        <p class="text-sm text-gray-600 mt-1" id="section-description">Tüm fatura ve ödeme bilgileriniz</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Fatura ara..." class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" onchange="filterData()">
                            <option value="">Tüm Durumlar</option>
                            <option value="odendi">Ödendi</option>
                            <option value="beklemede">Beklemede</option>
                            <option value="vadesi-gecmis">Vadesi Geçmiş</option>
                        </select>
                        <input type="month" id="monthFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" onchange="filterData()">
                    </div>
                </div>
            </div>
        </div>

        <!-- İçerik Alanı -->
        <div id="content-area">
            <!-- Faturalar sekmesi içeriği -->
            <div id="invoices-content" class="space-y-4 md:space-y-6">
            </div>

            <!-- Ödemeler sekmesi içeriği -->
            <div id="payments-content" class="hidden space-y-4 md:space-y-6">
            </div>

            <!-- Cari ekstre sekmesi içeriği -->
            <div id="account-statement-content" class="hidden">
            </div>
        </div>

        <!-- Fatura Detay Modal -->
        <div id="invoice-detail-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-4 md:p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Fatura Detayları</h3>
                        <button onclick="closeInvoiceDetail()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div id="invoice-detail-content" class="p-4 md:p-6">
                    <!-- İçerik JavaScript ile doldurulacak -->
                </div>
            </div>
        </div>

        <!-- Ödeme Modal -->
        <div id="payment-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-md w-full">
                <div class="p-4 md:p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Ödeme Yap</h3>
                        <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-4 md:p-6">
                    <form id="payment-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ödeme Tutarı</label>
                            <input type="number" id="payment-amount" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ödeme Yöntemi</label>
                            <select id="payment-method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="havale">Havale/EFT</option>
                                <option value="kredi-karti">Kredi Kartı</option>
                                <option value="nakit">Nakit</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                            <textarea id="payment-description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ödeme açıklaması..."></textarea>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" onclick="closePaymentModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                İptal
                            </button>
                            <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Ödeme Yap
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Örnek fatura verileri
        const invoicesData = [
            {
                id: 'FAT-2024-001',
                type: 'fatura',
                description: 'ERP Lisans Ücreti - ABC Tekstil',
                amount: 15000,
                status: 'vadesi-gecmis',
                issueDate: '2024-05-15',
                dueDate: '2024-06-15',
                paymentDate: null,
                customer: 'ABC Tekstil Ltd.',
                items: [
                    { name: 'ERP Temel Lisans', quantity: 1, unitPrice: 12000, total: 12000 },
                    { name: 'Kurulum ve Eğitim', quantity: 1, unitPrice: 3000, total: 3000 }
                ]
            },
            {
                id: 'FAT-2024-002',
                type: 'fatura',
                description: 'Aylık Destek Ücreti - XYZ İnşaat',
                amount: 2500,
                status: 'odendi',
                issueDate: '2024-06-01',
                dueDate: '2024-06-30',
                paymentDate: '2024-06-25',
                customer: 'XYZ İnşaat A.Ş.',
                items: [
                    { name: 'Teknik Destek', quantity: 1, unitPrice: 2000, total: 2000 },
                    { name: 'Sistem Bakımı', quantity: 1, unitPrice: 500, total: 500 }
                ]
            },
            {
                id: 'FAT-2024-003',
                type: 'fatura',
                description: 'ERP Modül Eklentisi - DEF Makina',
                amount: 8500,
                status: 'beklemede',
                issueDate: '2024-06-10',
                dueDate: '2024-07-10',
                paymentDate: null,
                customer: 'DEF Makina San.',
                items: [
                    { name: 'Üretim Modülü', quantity: 1, unitPrice: 6000, total: 6000 },
                    { name: 'Entegrasyon Hizmeti', quantity: 1, unitPrice: 2500, total: 2500 }
                ]
            },
            {
                id: 'FAT-2024-004',
                type: 'fatura',
                description: 'Yıllık Lisans Yenileme - GHI Market',
                amount: 25000,
                status: 'beklemede',
                issueDate: '2024-06-20',
                dueDate: '2024-07-20',
                paymentDate: null,
                customer: 'GHI Market Zinciri',
                items: [
                    { name: 'ERP Yıllık Lisans', quantity: 1, unitPrice: 20000, total: 20000 },
                    { name: 'Premium Destek', quantity: 1, unitPrice: 5000, total: 5000 }
                ]
            },
            {
                id: 'FAT-2024-005',
                type: 'fatura',
                description: 'Özelleştirme Hizmeti - JKL Lojistik',
                amount: 12000,
                status: 'vadesi-gecmis',
                issueDate: '2024-05-01',
                dueDate: '2024-06-01',
                paymentDate: null,
                customer: 'JKL Lojistik',
                items: [
                    { name: 'Özel Rapor Modülü', quantity: 1, unitPrice: 8000, total: 8000 },
                    { name: 'API Entegrasyonu', quantity: 1, unitPrice: 4000, total: 4000 }
                ]
            }
        ];

        // Örnek ödeme verileri
        const paymentsData = [
            {
                id: 'ODE-2024-001',
                type: 'odeme',
                description: 'Havale - FAT-2024-002 Ödemesi',
                amount: 2500,
                status: 'tamamlandi',
                paymentDate: '2024-06-25',
                method: 'Havale/EFT',
                invoiceId: 'FAT-2024-002',
                customer: 'XYZ İnşaat A.Ş.'
            },
            {
                id: 'ODE-2024-002',
                type: 'odeme',
                description: 'Kredi Kartı - Kısmi Ödeme',
                amount: 5000,
                status: 'tamamlandi',
                paymentDate: '2024-06-20',
                method: 'Kredi Kartı',
                invoiceId: 'FAT-2024-001',
                customer: 'ABC Tekstil Ltd.'
            },
            {
                id: 'ODE-2024-003',
                type: 'odeme',
                description: 'Havale - Cari Hesap Ödemesi',
                amount: 15000,
                status: 'beklemede',
                paymentDate: '2024-06-22',
                method: 'Havale/EFT',
                invoiceId: null,
                customer: 'DEF Makina San.'
            }
        ];

        // Cari ekstre verileri
        const accountStatementData = [
            ...invoicesData.map(inv => ({...inv, type: 'borc', date: inv.issueDate})),
            ...paymentsData.map(pay => ({...pay, type: 'alacak', date: pay.paymentDate, amount: -pay.amount}))
        ].sort((a, b) => new Date(b.date) - new Date(a.date));

        let currentTab = 'invoices';
        let filteredData = [...invoicesData];

        // Sayfa yüklendiğinde
        window.addEventListener('load', function() {
            renderContent();
            setupSearch();
        });

        // Sekme değiştirme
        function switchTab(tabName) {
            // Sekme butonlarını güncelle
            document.querySelectorAll('[id^="tab-"]').forEach(tab => {
                tab.classList.remove('tab-active');
                tab.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            });
            
            document.getElementById(`tab-${tabName}`).classList.add('tab-active');
            document.getElementById(`tab-${tabName}`).classList.remove('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');

            // İçerik alanlarını gizle/göster
            document.querySelectorAll('[id$="-content"]').forEach(content => {
                content.classList.add('hidden');
            });
            
            document.getElementById(`${tabName}-content`).classList.remove('hidden');

            // Başlık ve açıklamayı güncelle
            const titles = {
                'invoices': 'Faturalar',
                'payments': 'Ödemeler',
                'account-statement': 'Cari Ekstre'
            };
            
            const descriptions = {
                'invoices': 'Tüm fatura bilgileriniz',
                'payments': 'Ödeme geçmişiniz',
                'account-statement': 'Hesap hareketleriniz'
            };

            document.getElementById('section-title').textContent = titles[tabName];
            document.getElementById('section-description').textContent = descriptions[tabName];

            currentTab = tabName;
            renderContent();
        }

        // İçeriği render et
        function renderContent() {
            switch(currentTab) {
                case 'invoices':
                    renderInvoices();
                    break;
                case 'payments':
                    renderPayments();
                    break;
                case 'account-statement':
                    renderAccountStatement();
                    break;
            }
        }

        // Faturaları render et
        function renderInvoices() {
            const container = document.getElementById('invoices-content');
            container.innerHTML = '';

            if (filteredData.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500 text-lg">Aradığınız kriterlere uygun fatura bulunamadı</p>
                    </div>
                `;
                return;
            }

            filteredData.forEach(invoice => {
                const invoiceCard = createInvoiceCard(invoice);
                container.appendChild(invoiceCard);
            });
        }

        // Fatura kartı oluştur
        function createInvoiceCard(invoice) {
            const card = document.createElement('div');
            card.className = 'bg-white rounded-lg card-shadow invoice-card';
            
            const statusColors = {
                'odendi': 'bg-green-100 text-green-800',
                'beklemede': 'bg-yellow-100 text-yellow-800',
                'vadesi-gecmis': 'bg-red-100 text-red-800'
            };

            const statusTexts = {
                'odendi': 'Ödendi',
                'beklemede': 'Beklemede',
                'vadesi-gecmis': 'Vadesi Geçmiş'
            };

            const statusIcons = {
                'odendi': '✅',
                'beklemede': '⏳',
                'vadesi-gecmis': '❌'
            };

            const isOverdue = invoice.status === 'vadesi-gecmis';
            const daysOverdue = isOverdue ? Math.floor((new Date() - new Date(invoice.dueDate)) / (1000 * 60 * 60 * 24)) : 0;

            card.innerHTML = `
                <div class="p-4 md:p-6">
                    <!-- Üst Kısım -->
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
                        <div class="flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">${invoice.description}</h3>
                                <span class="px-2 py-1 text-xs font-medium rounded-full ${statusColors[invoice.status]} w-fit">
                                    ${statusIcons[invoice.status]} ${statusTexts[invoice.status]}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mb-1">Fatura No: ${invoice.id}</p>
                            <p class="text-sm text-gray-600 mb-1">${invoice.customer}</p>
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 text-xs text-gray-500">
                                <span>Düzenleme: ${formatDate(invoice.issueDate)}</span>
                                <span class="hidden sm:inline">•</span>
                                <span>Vade: ${formatDate(invoice.dueDate)}</span>
                                ${isOverdue ? `
                                    <span class="hidden sm:inline">•</span>
                                    <span class="text-red-600 font-medium">${daysOverdue} gün gecikme</span>
                                ` : ''}
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xl md:text-2xl font-bold ${isOverdue ? 'text-red-600' : 'text-gray-900'}">₺${invoice.amount.toLocaleString()}</p>
                            ${invoice.paymentDate ? `
                                <p class="text-sm text-green-600">Ödendi: ${formatDate(invoice.paymentDate)}</p>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Fatura Kalemleri Özeti -->
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-700 mb-2">${invoice.items.length} kalem</p>
                        <div class="mobile-scroll">
                            <div class="flex gap-2 min-w-max md:min-w-0">
                                ${invoice.items.slice(0, 2).map(item => `
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs whitespace-nowrap">
                                        ${item.name}
                                    </span>
                                `).join('')}
                                ${invoice.items.length > 2 ? `
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                                        +${invoice.items.length - 2} kalem daha
                                    </span>
                                ` : ''}
                            </div>
                        </div>
                    </div>

                    <!-- İşlem Butonları -->
                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <button onclick="viewInvoiceDetail('${invoice.id}')" class="flex-1 px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Detay Görüntüle
                            </button>
                            ${invoice.status !== 'odendi' ? `
                                <button onclick="payInvoice('${invoice.id}')" class="flex-1 px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                    Ödeme Yap
                                </button>
                            ` : ''}
                            <button onclick="downloadInvoice('${invoice.id}')" class="flex-1 px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                PDF İndir
                            </button>
                        </div>
                    </div>
                </div>
            `;

            return card;
        }

        // Ödemeleri render et
        function renderPayments() {
            const container = document.getElementById('payments-content');
            container.innerHTML = '';

            paymentsData.forEach(payment => {
                const paymentCard = createPaymentCard(payment);
                container.appendChild(paymentCard);
            });
        }

        // Ödeme kartı oluştur
        function createPaymentCard(payment) {
            const card = document.createElement('div');
            card.className = 'bg-white rounded-lg card-shadow invoice-card';
            
            const statusColors = {
                'tamamlandi': 'bg-green-100 text-green-800',
                'beklemede': 'bg-yellow-100 text-yellow-800',
                'iptal': 'bg-red-100 text-red-800'
            };

            const statusTexts = {
                'tamamlandi': 'Tamamlandı',
                'beklemede': 'Beklemede',
                'iptal': 'İptal'
            };

            card.innerHTML = `
                <div class="p-4 md:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
                        <div class="flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">${payment.description}</h3>
                                <span class="px-2 py-1 text-xs font-medium rounded-full ${statusColors[payment.status]} w-fit">
                                    ${statusTexts[payment.status]}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mb-1">Ödeme No: ${payment.id}</p>
                            <p class="text-sm text-gray-600 mb-1">${payment.customer}</p>
                            <p class="text-xs text-gray-500">Ödeme Tarihi: ${formatDate(payment.paymentDate)}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xl md:text-2xl font-bold text-green-600">₺${payment.amount.toLocaleString()}</p>
                            <p class="text-sm text-gray-600">${payment.method}</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <button onclick="viewPaymentDetail('${payment.id}')" class="flex-1 px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Detay Görüntüle
                            </button>
                            <button onclick="downloadReceipt('${payment.id}')" class="flex-1 px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Makbuz İndir
                            </button>
                        </div>
                    </div>
                </div>
            `;

            return card;
        }

        // Cari ekstre render et
        function renderAccountStatement() {
            const container = document.getElementById('account-statement-content');
            
            let balance = 0;
            const statementHtml = accountStatementData.map(item => {
                balance += item.type === 'borc' ? item.amount : item.amount;
                
                return `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">${formatDate(item.date)}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${item.description}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${item.id}</td>
                        <td class="px-4 py-3 text-sm text-right ${item.type === 'borc' ? 'text-red-600' : 'text-green-600'}">
                            ${item.type === 'borc' ? '₺' + item.amount.toLocaleString() : '-'}
                        </td>
                        <td class="px-4 py-3 text-sm text-right ${item.type === 'alacak' ? 'text-green-600' : 'text-red-600'}">
                            ${item.type === 'alacak' ? '₺' + Math.abs(item.amount).toLocaleString() : '-'}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-medium ${balance >= 0 ? 'text-green-600' : 'text-red-600'}">
                            ₺${Math.abs(balance).toLocaleString()} ${balance >= 0 ? 'A' : 'B'}
                        </td>
                    </tr>
                `;
            }).join('');

            container.innerHTML = `
                <div class="bg-white rounded-lg card-shadow">
                    <div class="p-4 md:p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Cari Hesap Ekstresi</h3>
                        <p class="text-sm text-gray-600 mt-1">Tüm hesap hareketleriniz</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Tarih</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Açıklama</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Belge No</th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-600">Borç</th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-600">Alacak</th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-600">Bakiye</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                ${statementHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        // Fatura detayını görüntüle
        function viewInvoiceDetail(invoiceId) {
            const invoice = invoicesData.find(inv => inv.id === invoiceId);
            if (!invoice) return;

            const modal = document.getElementById('invoice-detail-modal');
            const content = document.getElementById('invoice-detail-content');
            
            const itemsHtml = invoice.items.map(item => `
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-900">${item.name}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 text-center">${item.quantity}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 text-right">₺${item.unitPrice.toLocaleString()}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">₺${item.total.toLocaleString()}</td>
                </tr>
            `).join('');

            content.innerHTML = `
                <div class="space-y-6">
                    <!-- Fatura Bilgileri -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Fatura Bilgileri</h4>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Fatura No:</span>
                                    <p class="text-gray-900">${invoice.id}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Müşteri:</span>
                                    <p class="text-gray-900">${invoice.customer}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Düzenleme Tarihi:</span>
                                    <p class="text-gray-900">${formatDate(invoice.issueDate)}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Vade Tarihi:</span>
                                    <p class="text-gray-900">${formatDate(invoice.dueDate)}</p>
                                </div>
                                ${invoice.paymentDate ? `
                                    <div>
                                        <span class="text-sm font-medium text-gray-600">Ödeme Tarihi:</span>
                                        <p class="text-gray-900">${formatDate(invoice.paymentDate)}</p>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Ödeme Bilgileri</h4>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Durum:</span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full ${getInvoiceStatusColor(invoice.status)}">
                                        ${getInvoiceStatusName(invoice.status)}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Toplam Tutar:</span>
                                    <p class="text-gray-900 font-bold text-lg">₺${invoice.amount.toLocaleString()}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fatura Kalemleri -->
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Fatura Kalemleri</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full border border-gray-200 rounded-lg">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Ürün/Hizmet</th>
                                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-600">Miktar</th>
                                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-600">Birim Fiyat</th>
                                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-600">Toplam</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    ${itemsHtml}
                                    <tr class="bg-gray-50">
                                        <td colspan="3" class="px-4 py-3 text-sm font-medium text-gray-900">Genel Toplam</td>
                                        <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">₺${invoice.amount.toLocaleString()}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- İşlem Butonları -->
                    <div class="flex flex-col md:flex-row gap-3 pt-6 border-t border-gray-200">
                        ${invoice.status !== 'odendi' ? `
                            <button onclick="payInvoice('${invoice.id}')" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                Ödeme Yap
                            </button>
                        ` : ''}
                        <button onclick="downloadInvoice('${invoice.id}')" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            PDF İndir
                        </button>
                        <button onclick="printInvoice('${invoice.id}')" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Yazdır
                        </button>
                    </div>
                </div>
            `;
            
            modal.classList.remove('hidden');
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
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                filterData(searchTerm);
            });
        }

        // Filtreleme
        function filterData(searchTerm = '') {
            const statusFilter = document.getElementById('statusFilter').value;
            const monthFilter = document.getElementById('monthFilter').value;
            
            let dataToFilter = currentTab === 'invoices' ? invoicesData : paymentsData;
            
            filteredData = dataToFilter.filter(item => {
                const matchesSearch = searchTerm === '' || 
                    item.id.toLowerCase().includes(searchTerm) ||
                    item.description.toLowerCase().includes(searchTerm) ||
                    item.customer.toLowerCase().includes(searchTerm);
                
                const matchesStatus = statusFilter === '' || item.status === statusFilter;
                
                let matchesMonth = true;
                if (monthFilter) {
                    const itemMonth = (currentTab === 'invoices' ? item.issueDate : item.paymentDate).substring(0, 7);
                    matchesMonth = itemMonth === monthFilter;
                }
                
                return matchesSearch && matchesStatus && matchesMonth;
            });
            
            renderContent();
        }

        // Yardımcı fonksiyonlar
        function getInvoiceStatusName(status) {
            const statuses = {
                'odendi': 'Ödendi',
                'beklemede': 'Beklemede',
                'vadesi-gecmis': 'Vadesi Geçmiş'
            };
            return statuses[status] || status;
        }

        function getInvoiceStatusColor(status) {
            const colors = {
                'odendi': 'bg-green-100 text-green-800',
                'beklemede': 'bg-yellow-100 text-yellow-800',
                'vadesi-gecmis': 'bg-red-100 text-red-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }

        // Modal kapatma fonksiyonları
        function closeInvoiceDetail() {
            document.getElementById('invoice-detail-modal').classList.add('hidden');
        }

        function closePaymentModal() {
            document.getElementById('payment-modal').classList.add('hidden');
        }

        // İşlem fonksiyonları
        function exportInvoices() {
            alert('Fatura ve ödeme raporu Excel formatında indiriliyor...');
        }

        function makePayment() {
            document.getElementById('payment-modal').classList.remove('hidden');
        }

        function payInvoice(invoiceId) {
            const invoice = invoicesData.find(inv => inv.id === invoiceId);
            if (invoice) {
                document.getElementById('payment-amount').value = invoice.amount;
                document.getElementById('payment-modal').classList.remove('hidden');
            }
        }

        function downloadInvoice(invoiceId) {
            alert(`${invoiceId} numaralı fatura PDF olarak indiriliyor...`);
        }

        function printInvoice(invoiceId) {
            alert(`${invoiceId} numaralı fatura yazdırılıyor...`);
        }

        function viewPaymentDetail(paymentId) {
            alert(`${paymentId} numaralı ödeme detayları görüntüleniyor...`);
        }

        function downloadReceipt(paymentId) {
            alert(`${paymentId} numaralı ödeme makbuzu indiriliyor...`);
        }

        function goBack() {
            window.history.back();
        }

        // Ödeme formu submit
        document.getElementById('payment-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const amount = document.getElementById('payment-amount').value;
            const method = document.getElementById('payment-method').value;
            const description = document.getElementById('payment-description').value;
            
            if (!amount || amount <= 0) {
                alert('Lütfen geçerli bir tutar girin.');
                return;
            }
            
            alert(`₺${parseFloat(amount).toLocaleString()} tutarında ödeme işlemi başlatılıyor...\nYöntem: ${method}`);
            closePaymentModal();
        });

        // Modal dışına tıklayınca kapatma
        document.getElementById('invoice-detail-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeInvoiceDetail();
            }
        });

        document.getElementById('payment-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePaymentModal();
            }
        });
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9722ec2fc52bb657',t:'MTc1NTcwMzcyOC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
