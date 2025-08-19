<?php
session_start();
$BASE = '/is-ortaklar-paneli/';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'bayi') { header('Location: '.$BASE.'login.php'); exit; }
?><h1>Bayi Panel</h1><p><?= htmlspecialchars($_SESSION['email']??'') ?></p>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bayi Yönetim Sistemi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .status-draft { @apply bg-gray-100 text-gray-800; }
        .status-sent { @apply bg-blue-100 text-blue-800; }
        .status-review { @apply bg-yellow-100 text-yellow-800; }
        .status-ready { @apply bg-green-100 text-green-800; }
        .status-approved { @apply bg-emerald-100 text-emerald-800; }
        .status-rejected { @apply bg-red-100 text-red-800; }
        .status-pending { @apply bg-orange-100 text-orange-800; }
        .status-completed { @apply bg-green-100 text-green-800; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-indigo-600">Bayi Yönetim Sistemi</h1>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <button onclick="showSection('dashboard')" class="nav-btn text-indigo-600 border-b-2 border-indigo-500 px-1 pt-1 pb-4 text-sm font-medium">Dashboard</button>
                        <button onclick="showSection('registration')" class="nav-btn text-gray-500 hover:text-gray-700 px-1 pt-1 pb-4 text-sm font-medium">Bayi Kayıt</button>
                        <button onclick="showSection('quotes')" class="nav-btn text-gray-500 hover:text-gray-700 px-1 pt-1 pb-4 text-sm font-medium">Teklifler</button>
                        <button onclick="showSection('orders')" class="nav-btn text-gray-500 hover:text-gray-700 px-1 pt-1 pb-4 text-sm font-medium">Siparişler</button>
                        <button onclick="showSection('billing')" class="nav-btn text-gray-500 hover:text-gray-700 px-1 pt-1 pb-4 text-sm font-medium">Faturalandırma</button>
                    </div>
                </div>
                <div class="flex items-center">
                    <button class="md:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100" onclick="toggleMobileMenu()">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <span class="hidden sm:block text-xs sm:text-sm text-gray-600">Hoş geldiniz, <strong>Ahmet Bayi</strong></span>
                </div>
            </div>
            <!-- Mobile menu -->
            <div id="mobile-menu" class="md:hidden hidden">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-gray-50">
                    <button onclick="showSection('dashboard'); toggleMobileMenu()" class="mobile-nav-btn block px-3 py-2 text-base font-medium text-indigo-600 bg-indigo-50 rounded-md w-full text-left">Dashboard</button>
                    <button onclick="showSection('registration'); toggleMobileMenu()" class="mobile-nav-btn block px-3 py-2 text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-md w-full text-left">Bayi Kayıt</button>
                    <button onclick="showSection('quotes'); toggleMobileMenu()" class="mobile-nav-btn block px-3 py-2 text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-md w-full text-left">Teklifler</button>
                    <button onclick="showSection('orders'); toggleMobileMenu()" class="mobile-nav-btn block px-3 py-2 text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-md w-full text-left">Siparişler</button>
                    <button onclick="showSection('billing'); toggleMobileMenu()" class="mobile-nav-btn block px-3 py-2 text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-md w-full text-left">Faturalandırma</button>
                </div>
                <div class="pt-4 pb-3 border-t border-gray-200">
                    <div class="px-5">
                        <p class="text-sm text-gray-600">Hoş geldiniz, <strong>Ahmet Bayi</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-3 sm:py-6 px-2 sm:px-4 lg:px-8">
        <!-- Dashboard Section -->
        <div id="dashboard" class="section">
            <div class="mb-4 sm:mb-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Dashboard</h2>
                <p class="text-sm sm:text-base text-gray-600">Genel durum ve önemli bilgiler</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-4 sm:mb-8">
                <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-2 sm:p-3 rounded-full bg-blue-100">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-xs sm:text-sm font-medium text-gray-600">Aktif Teklifler</p>
                            <p class="text-xl sm:text-2xl font-semibold text-gray-900">12</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-2 sm:p-3 rounded-full bg-green-100">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-xs sm:text-sm font-medium text-gray-600">Bu Ay Siparişler</p>
                            <p class="text-xl sm:text-2xl font-semibold text-gray-900">8</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-2 sm:p-3 rounded-full bg-yellow-100">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-xs sm:text-sm font-medium text-gray-600">Bekleyen Hakediş</p>
                            <p class="text-lg sm:text-2xl font-semibold text-gray-900">₺45,250</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-2 sm:p-3 rounded-full bg-purple-100">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <p class="text-xs sm:text-sm font-medium text-gray-600">Bu Ay Gelir</p>
                            <p class="text-lg sm:text-2xl font-semibold text-gray-900">₺128,500</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 border border-gray-100">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Son Aktiviteler</h3>
                <div class="space-y-3 sm:space-y-4">
                    <div class="flex items-center p-2 sm:p-3 bg-blue-50 rounded-lg">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mr-2 sm:mr-3 flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-900 truncate">Yeni teklif talebi alındı</p>
                            <p class="text-xs text-gray-500">ABC Şirketi - 2 saat önce</p>
                        </div>
                    </div>
                    <div class="flex items-center p-2 sm:p-3 bg-green-50 rounded-lg">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2 sm:mr-3 flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-900 truncate">Sipariş tamamlandı</p>
                            <p class="text-xs text-gray-500">XYZ Ltd - 4 saat önce</p>
                        </div>
                    </div>
                    <div class="flex items-center p-2 sm:p-3 bg-yellow-50 rounded-lg">
                        <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2 sm:mr-3 flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-900 truncate">Ödeme bekliyor</p>
                            <p class="text-xs text-gray-500">DEF A.Ş - 1 gün önce</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registration Section -->
        <div id="registration" class="section hidden">
            <div class="mb-4 sm:mb-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Bayi Kayıt Süreci</h2>
                <p class="text-sm sm:text-base text-gray-600">Yeni bayi kayıt işlemleri ve onay durumu</p>
            </div>

            <!-- Registration Process Steps -->
            <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 border border-gray-100 mb-4 sm:mb-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Kayıt Süreci Adımları</h3>
                <div class="flex items-center justify-between overflow-x-auto pb-2">
                    <div class="flex flex-col items-center flex-shrink-0">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-semibold text-sm sm:text-base">1</div>
                        <p class="text-xs sm:text-sm mt-2 text-center whitespace-nowrap">Form<br class="hidden sm:block">Doldurma</p>
                    </div>
                    <div class="flex-1 h-1 bg-green-500 mx-1 sm:mx-2 min-w-[20px]"></div>
                    <div class="flex flex-col items-center flex-shrink-0">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-semibold text-sm sm:text-base">2</div>
                        <p class="text-xs sm:text-sm mt-2 text-center whitespace-nowrap">Operasyon<br class="hidden sm:block">İnceleme</p>
                    </div>
                    <div class="flex-1 h-1 bg-yellow-400 mx-1 sm:mx-2 min-w-[20px]"></div>
                    <div class="flex flex-col items-center flex-shrink-0">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-yellow-400 rounded-full flex items-center justify-center text-white font-semibold text-sm sm:text-base">3</div>
                        <p class="text-xs sm:text-sm mt-2 text-center whitespace-nowrap">Ek Evrak<br class="hidden sm:block">Bekleniyor</p>
                    </div>
                    <div class="flex-1 h-1 bg-gray-300 mx-1 sm:mx-2 min-w-[20px]"></div>
                    <div class="flex flex-col items-center flex-shrink-0">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-semibold text-sm sm:text-base">4</div>
                        <p class="text-xs sm:text-sm mt-2 text-center whitespace-nowrap">Onay</p>
                    </div>
                    <div class="flex-1 h-1 bg-gray-300 mx-1 sm:mx-2 min-w-[20px]"></div>
                    <div class="flex flex-col items-center flex-shrink-0">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-semibold text-sm sm:text-base">5</div>
                        <p class="text-xs sm:text-sm mt-2 text-center whitespace-nowrap">Giriş<br class="hidden sm:block">Aktif</p>
                    </div>
                </div>
            </div>

            <!-- Registration Form -->
            <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 border border-gray-100">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Yeni Bayi Kayıt Formu</h3>
                <form class="space-y-3 sm:space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Şirket Adı</label>
                            <input type="text" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="ABC Teknoloji Ltd.">
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Vergi Numarası</label>
                            <input type="text" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="1234567890">
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Yetkili Kişi</label>
                            <input type="text" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ahmet Yılmaz">
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Telefon</label>
                            <input type="tel" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="0532 123 45 67">
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">E-posta</label>
                            <input type="email" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="info@abcteknoloji.com">
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Şehir</label>
                            <select class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option>İstanbul</option>
                                <option>Ankara</option>
                                <option>İzmir</option>
                                <option>Bursa</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Adres</label>
                        <textarea class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" rows="3" placeholder="Tam adres bilgisi..."></textarea>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="button" onclick="submitRegistration()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 sm:px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                            Kayıt Başvurusu Gönder
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quotes Section -->
        <div id="quotes" class="section hidden">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Teklif Yönetimi</h2>
                <p class="text-gray-600">Teklif talepleri, hazırlama ve takip işlemleri</p>
            </div>

            <!-- Quote Actions -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Teklif İşlemleri</h3>
                    <button onclick="createNewQuote()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Yeni Teklif Oluştur
                    </button>
                </div>
            </div>

            <!-- Quotes List -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Teklif Listesi</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[600px]">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teklif No</th>
                                <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Müşteri</th>
                                <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutar</th>
                                <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                                <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Tarih</th>
                                <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">TKF-2024-001</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">ABC Şirketi</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">₺25,000</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full status-ready">Teklif Hazır</span>
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden sm:table-cell">15.01.2024</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium">
                                    <div class="flex flex-col sm:flex-row gap-1 sm:gap-3">
                                        <button onclick="sendQuoteToCustomer('TKF-2024-001')" class="text-indigo-600 hover:text-indigo-900">Müşteriye Gönder</button>
                                        <button onclick="editQuote('TKF-2024-001')" class="text-gray-600 hover:text-gray-900">Düzenle</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">TKF-2024-002</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">XYZ Ltd</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">₺45,500</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full status-approved">Müşteri Onayladı</span>
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden sm:table-cell">12.01.2024</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium">
                                    <div class="flex flex-col sm:flex-row gap-1 sm:gap-3">
                                        <button onclick="convertToOrder('TKF-2024-002')" class="text-green-600 hover:text-green-900">Siparişe Dönüştür</button>
                                        <button onclick="viewQuote('TKF-2024-002')" class="text-gray-600 hover:text-gray-900">Görüntüle</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">TKF-2024-003</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">DEF A.Ş</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">₺18,750</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full status-review">İncelemede</span>
                                </td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden sm:table-cell">10.01.2024</td>
                                <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium">
                                    <div class="flex flex-col sm:flex-row gap-1 sm:gap-3">
                                        <button onclick="editQuote('TKF-2024-003')" class="text-indigo-600 hover:text-indigo-900">Düzenle</button>
                                        <button onclick="viewQuote('TKF-2024-003')" class="text-gray-600 hover:text-gray-900">Görüntüle</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Orders Section -->
        <div id="orders" class="section hidden">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Sipariş Yönetimi</h2>
                <p class="text-gray-600">Sipariş takibi, teslimat ve ödeme durumu</p>
            </div>

            <!-- Orders List -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Sipariş Listesi</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sipariş No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Müşteri</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sipariş Durumu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ödeme Durumu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hakediş</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">SIP-2024-001</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">ABC Şirketi</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₺45,500</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full status-completed">Tamamlandı</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full status-completed">Tamamlandı</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full status-completed">Tahsil Edildi</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="viewOrder('SIP-2024-001')" class="text-indigo-600 hover:text-indigo-900">Görüntüle</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">SIP-2024-002</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">XYZ Ltd</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₺32,000</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full status-pending">Teslimat/Kurulum</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full status-pending">Kısmi</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full status-pending">Tahsilat Bekliyor</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="updateOrderStatus('SIP-2024-002')" class="text-green-600 hover:text-green-900 mr-3">Durumu Güncelle</button>
                                    <button onclick="viewOrder('SIP-2024-002')" class="text-indigo-600 hover:text-indigo-900">Görüntüle</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">SIP-2024-003</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">DEF A.Ş</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₺28,750</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full status-draft">Oluşturuldu</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full status-pending">Bekliyor</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full status-draft">Hesaplandı</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="startDelivery('SIP-2024-003')" class="text-blue-600 hover:text-blue-900 mr-3">Teslimat Başlat</button>
                                    <button onclick="viewOrder('SIP-2024-003')" class="text-indigo-600 hover:text-indigo-900">Görüntüle</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Billing Section -->
        <div id="billing" class="section hidden">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Otomatik Faturalandırma</h2>
                <p class="text-gray-600">Abonelik faturaları ve periyodik ödemeler</p>
            </div>

            <!-- Billing Summary -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6 mb-4 sm:mb-8">
                <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 border border-gray-100">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Aylık Server Hizmetleri</h3>
                    <div class="space-y-2 sm:space-y-3">
                        <div class="flex justify-between">
                            <span class="text-xs sm:text-sm text-gray-600">Aktif Abonelik</span>
                            <span class="text-xs sm:text-sm font-medium">15 Müşteri</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs sm:text-sm text-gray-600">Aylık Toplam</span>
                            <span class="text-xs sm:text-sm font-medium">₺18,750</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs sm:text-sm text-gray-600">Sonraki Fatura</span>
                            <span class="text-xs sm:text-sm font-medium">01.02.2024</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 border border-gray-100">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Yıllık Bakım Hizmetleri</h3>
                    <div class="space-y-2 sm:space-y-3">
                        <div class="flex justify-between">
                            <span class="text-xs sm:text-sm text-gray-600">Aktif Sözleşme</span>
                            <span class="text-xs sm:text-sm font-medium">8 Müşteri</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs sm:text-sm text-gray-600">Yıllık Toplam</span>
                            <span class="text-xs sm:text-sm font-medium">₺96,000</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs sm:text-sm text-gray-600">Sonraki Fatura</span>
                            <span class="text-xs sm:text-sm font-medium">15.03.2024</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 border border-gray-100 sm:col-span-2 lg:col-span-1">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Bekleyen Tahsilatlar</h3>
                    <div class="space-y-2 sm:space-y-3">
                        <div class="flex justify-between">
                            <span class="text-xs sm:text-sm text-gray-600">Bekleyen Fatura</span>
                            <span class="text-xs sm:text-sm font-medium">3 Adet</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs sm:text-sm text-gray-600">Toplam Tutar</span>
                            <span class="text-xs sm:text-sm font-medium text-red-600">₺12,500</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs sm:text-sm text-gray-600">Ortalama Vade</span>
                            <span class="text-xs sm:text-sm font-medium">8 Gün</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Automatic Billing List -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Otomatik Faturalandırma Listesi</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Müşteri</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hizmet Türü</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periyot</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Son Fatura</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sonraki Fatura</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">ABC Şirketi</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Server Hizmeti</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Aylık</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₺1,250</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">01.01.2024</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">01.02.2024</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full status-completed">Aktif</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">XYZ Ltd</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Yıllık Bakım</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Yıllık</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₺12,000</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">15.01.2023</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">15.01.2024</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full status-pending">Fatura Kesildi</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">DEF A.Ş</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Server Hizmeti</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Aylık</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₺2,100</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">01.01.2024</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">01.02.2024</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full status-pending">Ödeme Bekliyor</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu functionality
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        }

        // Navigation functionality
        function showSection(sectionName) {
            // Hide all sections
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => section.classList.add('hidden'));
            
            // Show selected section
            document.getElementById(sectionName).classList.remove('hidden');
            
            // Update desktop navigation buttons
            const navButtons = document.querySelectorAll('.nav-btn');
            navButtons.forEach(btn => {
                btn.classList.remove('text-indigo-600', 'border-b-2', 'border-indigo-500');
                btn.classList.add('text-gray-500', 'hover:text-gray-700');
            });
            
            // Update mobile navigation buttons
            const mobileNavButtons = document.querySelectorAll('.mobile-nav-btn');
            mobileNavButtons.forEach(btn => {
                btn.classList.remove('text-indigo-600', 'bg-indigo-50');
                btn.classList.add('text-gray-700', 'hover:text-gray-900', 'hover:bg-gray-100');
            });
            
            // Highlight active button (desktop)
            if (event && event.target.classList.contains('nav-btn')) {
                event.target.classList.remove('text-gray-500', 'hover:text-gray-700');
                event.target.classList.add('text-indigo-600', 'border-b-2', 'border-indigo-500');
            }
            
            // Highlight active button (mobile)
            if (event && event.target.classList.contains('mobile-nav-btn')) {
                event.target.classList.remove('text-gray-700', 'hover:text-gray-900', 'hover:bg-gray-100');
                event.target.classList.add('text-indigo-600', 'bg-indigo-50');
            }
        }

        // Registration functionality
        function submitRegistration() {
            alert('Bayi kayıt başvurunuz başarıyla gönderildi! Operasyon ekibimiz en kısa sürede başvurunuzu inceleyecektir.');
        }

        // Quote functionality
        function createNewQuote() {
            alert('Yeni teklif oluşturma sayfasına yönlendiriliyorsunuz...');
        }

        function sendQuoteToCustomer(quoteId) {
            alert(`${quoteId} numaralı teklif müşteriye e-posta ile gönderildi. Siz de CC olarak bilgilendirildiniz.`);
        }

        function editQuote(quoteId) {
            alert(`${quoteId} numaralı teklif düzenleme sayfasına yönlendiriliyorsunuz...`);
        }

        function viewQuote(quoteId) {
            alert(`${quoteId} numaralı teklif detayları görüntüleniyor...`);
        }

        function convertToOrder(quoteId) {
            if (confirm(`${quoteId} numaralı teklifi siparişe dönüştürmek istediğinizden emin misiniz?`)) {
                alert('Teklif başarıyla siparişe dönüştürüldü! Sipariş numarası: SIP-2024-004');
            }
        }

        // Order functionality
        function viewOrder(orderId) {
            alert(`${orderId} numaralı sipariş detayları görüntüleniyor...`);
        }

        function updateOrderStatus(orderId) {
            alert(`${orderId} numaralı siparişin durumu güncelleniyor...`);
        }

        function startDelivery(orderId) {
            if (confirm(`${orderId} numaralı sipariş için teslimat sürecini başlatmak istediğinizden emin misiniz?`)) {
                alert('Teslimat süreci başlatıldı! Müşteri bilgilendirildi.');
            }
        }

        // Simulate automatic billing process
        function simulateAutoBilling() {
            // This would normally be handled by backend systems
            console.log('Otomatik faturalandırma sistemi çalışıyor...');
            console.log('Aylık server hizmet faturaları oluşturuluyor...');
            console.log('Yıllık bakım faturaları kontrol ediliyor...');
            console.log('Hakediş hesaplamaları yapılıyor...');
        }

        // Initialize the system
        document.addEventListener('DOMContentLoaded', function() {
            // Simulate automatic processes
            setInterval(simulateAutoBilling, 60000); // Check every minute
            
            // Show dashboard by default
            showSection('dashboard');
        });
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'97187e1a2157e343',t:'MTc1NTU5NDM2Mi4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
