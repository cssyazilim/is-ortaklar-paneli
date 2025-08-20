<?php
session_start();
$BASE = '/is-ortaklar-paneli/';

if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
  header('Location: /is-ortaklar-paneli/login.php'); exit;
}

if (($_SESSION['user']['role'] ?? null) !== 'admin') {
    http_response_code(403);
    header('Location: /is-ortaklar-paneli/bayi/bayi.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - Bayi Yönetim Sistemi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar-transition {
            transition: all 0.3s ease-in-out;
        }
        .content-transition {
            transition: all 0.3s ease-in-out;
        }
        .card-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .notification-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .sidebar-open {
            transform: translateX(0) !important;
        }
        .sidebar-closed {
            transform: translateX(-100%) !important;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Sol Menü -->
    <div id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-white shadow-lg z-40 sidebar-transition transform -translate-x-full">
        <!-- Logo/Başlık -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2L3 7v11a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V7l-7-5z"/>
                        </svg>
                    </div>
                    <div class="logo-text transition-all duration-300">
                        <h1 class="text-lg font-bold text-gray-900 whitespace-nowrap">Admin Panel</h1>
                        <p class="text-xs text-gray-500 whitespace-nowrap">Bayi Yönetimi</p>
                    </div>
                </div>

            </div>
        </div>

        <!-- Menü İçeriği -->
        <nav class="p-4 space-y-2 overflow-y-auto h-full pb-20">
            <!-- Dashboard -->
            <a href="#" onclick="showModule('dashboard')" class="menu-item flex items-center p-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors bg-blue-50 text-blue-600">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                </svg>
                <span class="menu-text transition-all duration-300 whitespace-nowrap">Dashboard</span>
            </a>

            <!-- Müşterilerim -->
            <a href="#" onclick="showModule('customers')" class="menu-item flex items-center p-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="menu-text transition-all duration-300 whitespace-nowrap">Müşterilerim</span>
                <span class="menu-badge ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full transition-all duration-300">12</span>
            </a>

            <!-- Teklif Talep Et -->
            <a href="#" onclick="showModule('request-quote')" class="menu-item flex items-center p-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span class="menu-text transition-all duration-300 whitespace-nowrap">Teklif Talep Et</span>
            </a>

            <!-- Tekliflerim -->
            <a href="#" onclick="showModule('quotes')" class="menu-item flex items-center p-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="menu-text transition-all duration-300 whitespace-nowrap">Tekliflerim</span>
                <span class="menu-badge ml-auto bg-orange-100 text-orange-600 text-xs px-2 py-1 rounded-full transition-all duration-300">3</span>
            </a>

            <!-- Siparişlerim -->
            <a href="#" onclick="showModule('orders')" class="menu-item flex items-center p-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <span class="menu-text transition-all duration-300 whitespace-nowrap">Siparişlerim</span>
                <span class="menu-badge ml-auto bg-green-100 text-green-600 text-xs px-2 py-1 rounded-full transition-all duration-300">7</span>
            </a>

            <!-- Hakedişlerim -->
            <a href="#" onclick="showModule('earnings')" class="menu-item flex items-center p-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
                <span class="menu-text transition-all duration-300 whitespace-nowrap">Hakedişlerim</span>
                <span class="menu-badge ml-auto bg-purple-100 text-purple-600 text-xs px-2 py-1 rounded-full transition-all duration-300">₺45K</span>
            </a>

            <!-- Faturalar / Cari -->
            <a href="#" onclick="showModule('invoices')" class="menu-item flex items-center p-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <span class="menu-text transition-all duration-300 whitespace-nowrap">Faturalar / Cari</span>
            </a>

            <!-- Bildirimler -->
            <a href="#" onclick="showModule('notifications')" class="menu-item flex items-center p-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11 19H6a2 2 0 01-2-2V7a2 2 0 012-2h5m5 0v5"></path>
                </svg>
                <span class="menu-text transition-all duration-300 whitespace-nowrap">Bildirimler</span>
                <span class="menu-badge ml-auto bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full notification-badge transition-all duration-300">5</span>
            </a>

            <!-- Ayarlar -->
            <a href="#" onclick="showModule('settings')" class="menu-item flex items-center p-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="menu-text transition-all duration-300 whitespace-nowrap">Ayarlar</span>
            </a>
        </nav>
    </div>

    <!-- Ana İçerik Alanı -->
    <div id="main-content" class="content-transition">
        <!-- Üst Bar -->
        <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <!-- Sol Taraf - Menü Toggle ve Başlık -->
                <div class="flex items-center">
                    <button onclick="toggleSidebar()" class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <h2 id="page-title" class="text-xl font-semibold text-gray-900 ml-2">Dashboard</h2>
                </div>

                <!-- Sağ Taraf - Bildirimler ve Kullanıcı -->
                <div class="flex items-center space-x-4">
                    <!-- Arama -->
                    <div class="hidden md:block relative">
                        <input type="text" placeholder="Ara..." class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <!-- Bildirimler -->
                    <div class="relative">
                        <button onclick="toggleNotifications()" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg relative">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11 19H6a2 2 0 01-2-2V7a2 2 0 012-2h5m5 0v5"></path>
                            </svg>
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">5</span>
                        </button>
                        
                        <!-- Bildirim Dropdown -->
                        <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Bildirimler</h3>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <div class="p-3 border-b border-gray-100 hover:bg-gray-50">
                                    <div class="flex items-start">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></div>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">Yeni teklif talebi</p>
                                            <p class="text-xs text-gray-600">ABC Şirketi için yeni teklif talebi oluşturuldu</p>
                                            <p class="text-xs text-gray-400 mt-1">2 saat önce</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 border-b border-gray-100 hover:bg-gray-50">
                                    <div class="flex items-start">
                                        <div class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3"></div>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">Ödeme alındı</p>
                                            <p class="text-xs text-gray-600">XYZ Ltd. şirketinden 15.000₺ ödeme alındı</p>
                                            <p class="text-xs text-gray-400 mt-1">1 gün önce</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 border-b border-gray-100 hover:bg-gray-50">
                                    <div class="flex items-start">
                                        <div class="w-2 h-2 bg-orange-500 rounded-full mt-2 mr-3"></div>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">Bakım hatırlatması</p>
                                            <p class="text-xs text-gray-600">DEF A.Ş. yıllık bakım tarihi yaklaşıyor</p>
                                            <p class="text-xs text-gray-400 mt-1">3 gün önce</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 border-t border-gray-200">
                                <button class="w-full text-center text-sm text-blue-600 hover:text-blue-800">Tümünü Gör</button>
                            </div>
                        </div>
                    </div>

                    <!-- Kullanıcı Menüsü -->
                    <div class="relative">
                        <button onclick="toggleUserMenu()" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100">
                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-medium">AK</span>
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-medium text-gray-900">Ahmet Kaya</p>
                                <p class="text-xs text-gray-600">Admin</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Kullanıcı Dropdown -->
                        <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <div class="p-2">
                                <a href="#" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Profil
                                </a>
                                <a href="#" onclick="showModule('settings')" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Ayarlar
                                </a>
                                <hr class="my-2">
                                <a href="#" onclick="logout()" class="flex items-center px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Çıkış Yap
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Ana İçerik -->
        <main class="p-6">
            <!-- Dashboard İçeriği -->
            <div id="dashboard-content" class="module-content">
                <!-- İstatistik Kartları -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg card-shadow">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Toplam Müşteri</p>
                                <p class="text-2xl font-bold text-gray-900">127</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-green-600 text-sm font-medium">+12%</span>
                            <span class="text-gray-600 text-sm">Bu ay</span>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg card-shadow">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Aylık Ciro</p>
                                <p class="text-2xl font-bold text-gray-900">₺245K</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-green-600 text-sm font-medium">+8%</span>
                            <span class="text-gray-600 text-sm">Geçen aya göre</span>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg card-shadow">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Bekleyen Teklifler</p>
                                <p class="text-2xl font-bold text-gray-900">8</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-orange-600 text-sm font-medium">3 Yeni</span>
                            <span class="text-gray-600 text-sm">Bu hafta</span>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg card-shadow">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Aktif Siparişler</p>
                                <p class="text-2xl font-bold text-gray-900">15</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-purple-600 text-sm font-medium">₺180K</span>
                            <span class="text-gray-600 text-sm">Toplam değer</span>
                        </div>
                    </div>
                </div>

                <!-- Son Aktiviteler ve Grafikler -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Son Aktiviteler -->
                    <div class="bg-white p-6 rounded-lg card-shadow">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Son Aktiviteler</h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Yeni müşteri eklendi</p>
                                    <p class="text-xs text-gray-600">ABC Teknoloji Ltd. Şti. sisteme kaydedildi</p>
                                    <p class="text-xs text-gray-400 mt-1">2 saat önce</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Teklif onaylandı</p>
                                    <p class="text-xs text-gray-600">XYZ A.Ş. için hazırlanan teklif onaylandı</p>
                                    <p class="text-xs text-gray-400 mt-1">4 saat önce</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-orange-500 rounded-full mt-2 mr-3"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Ödeme alındı</p>
                                    <p class="text-xs text-gray-600">DEF Şirketi'nden 25.000₺ ödeme alındı</p>
                                    <p class="text-xs text-gray-400 mt-1">1 gün önce</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hızlı İşlemler -->
                    <div class="bg-white p-6 rounded-lg card-shadow">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Hızlı İşlemler</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <button onclick="showModule('customers')" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <p class="text-sm font-medium text-gray-900">Müşteri Ekle</p>
                            </button>
                            <button onclick="showModule('request-quote')" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <svg class="w-8 h-8 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-sm font-medium text-gray-900">Teklif Talep Et</p>
                            </button>
                            <button onclick="showModule('orders')" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <svg class="w-8 h-8 text-purple-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                <p class="text-sm font-medium text-gray-900">Siparişler</p>
                            </button>
                            <button onclick="showModule('invoices')" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <svg class="w-8 h-8 text-orange-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-sm font-medium text-gray-900">Faturalar</p>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Diğer modül içerikleri buraya eklenecek -->
            <div id="customers-content" class="module-content hidden">
                <div class="bg-white rounded-lg card-shadow">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Müşterilerim</h3>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                + Yeni Müşteri
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600">Müşteri yönetimi modülü burada geliştirilecek...</p>
                    </div>
                </div>
            </div>

            <div id="request-quote-content" class="module-content hidden">
                <div class="bg-white rounded-lg card-shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Teklif Talep Et</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600">Teklif talep modülü burada geliştirilecek...</p>
                    </div>
                </div>
            </div>

            <div id="quotes-content" class="module-content hidden">
                <div class="bg-white rounded-lg card-shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Tekliflerim</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600">Teklifler modülü burada geliştirilecek...</p>
                    </div>
                </div>
            </div>

            <div id="orders-content" class="module-content hidden">
                <div class="bg-white rounded-lg card-shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Siparişlerim</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600">Siparişler modülü burada geliştirilecek...</p>
                    </div>
                </div>
            </div>

            <div id="earnings-content" class="module-content hidden">
                <div class="bg-white rounded-lg card-shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Hakedişlerim</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600">Hakediş modülü burada geliştirilecek...</p>
                    </div>
                </div>
            </div>

            <div id="invoices-content" class="module-content hidden">
                <div class="bg-white rounded-lg card-shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Faturalar / Cari</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600">Faturalar modülü burada geliştirilecek...</p>
                    </div>
                </div>
            </div>

            <div id="notifications-content" class="module-content hidden">
                <div class="bg-white rounded-lg card-shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Bildirimler</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600">Bildirimler modülü burada geliştirilecek...</p>
                    </div>
                </div>
            </div>

            <div id="settings-content" class="module-content hidden">
                <div class="bg-white rounded-lg card-shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Ayarlar</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600">Ayarlar modülü burada geliştirilecek...</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Mobile Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>

    <script>
        let sidebarOpen = false;
        let currentModule = 'dashboard';

        // Modül değiştirme
        function showModule(module) {
            // Tüm modül içeriklerini gizle
            document.querySelectorAll('.module-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Seçili modülü göster
            document.getElementById(module + '-content').classList.remove('hidden');

            // Menü aktif durumunu güncelle
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('bg-blue-50', 'text-blue-600');
                item.classList.add('text-gray-700');
            });

            // Tıklanan menü öğesini aktif yap
            event.target.closest('.menu-item').classList.add('bg-blue-50', 'text-blue-600');
            event.target.closest('.menu-item').classList.remove('text-gray-700');

            // Sayfa başlığını güncelle
            const titles = {
                'dashboard': 'Dashboard',
                'customers': 'Müşterilerim',
                'request-quote': 'Teklif Talep Et',
                'quotes': 'Tekliflerim',
                'orders': 'Siparişlerim',
                'earnings': 'Hakedişlerim',
                'invoices': 'Faturalar / Cari',
                'notifications': 'Bildirimler',
                'settings': 'Ayarlar'
            };
            document.getElementById('page-title').textContent = titles[module];

            currentModule = module;

            // Menü açıksa kapat
            if (sidebarOpen) {
                toggleSidebar();
            }
        }

        // Sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');

            sidebarOpen = !sidebarOpen;
            
            if (sidebarOpen) {
                sidebar.classList.remove('sidebar-closed');
                sidebar.classList.add('sidebar-open');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.remove('sidebar-open');
                sidebar.classList.add('sidebar-closed');
                overlay.classList.add('hidden');
            }
        }

        // Bildirimler dropdown
        function toggleNotifications() {
            const dropdown = document.getElementById('notifications-dropdown');
            dropdown.classList.toggle('hidden');
        }

        // Kullanıcı menüsü dropdown
        function toggleUserMenu() {
            const dropdown = document.getElementById('user-dropdown');
            dropdown.classList.toggle('hidden');
        }

        // Çıkış yap
        function logout() {
            if (confirm('Çıkış yapmak istediğinizden emin misiniz?')) {
                window.location.href = '/is-ortaklar-paneli/auth/logout.php';
            }
        }

        // Responsive kontrol
        window.addEventListener('resize', function() {
            // Menü açıksa ve ekran boyutu değişirse menüyü kapat
            if (sidebarOpen) {
                toggleSidebar();
            }
        });

        // Overlay tıklayınca menüyü kapat
        document.getElementById('mobile-overlay').addEventListener('click', function() {
            if (sidebarOpen) {
                toggleSidebar();
            }
        });

        // Dropdown'ları dışarı tıklayınca kapat
        document.addEventListener('click', function(event) {
            const notificationsDropdown = document.getElementById('notifications-dropdown');
            const userDropdown = document.getElementById('user-dropdown');
            
            if (!event.target.closest('.relative')) {
                notificationsDropdown.classList.add('hidden');
                userDropdown.classList.add('hidden');
            }
        });

        // Sayfa yüklendiğinde menü kapalı olarak başlat
        window.addEventListener('load', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.add('sidebar-closed');
        });
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9710aea340fdb8a1',t:'MTc1NTUxMjQ2NC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
