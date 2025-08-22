<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bildirimler - Bayi Yönetim Sistemi</title>
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
        .notification-card {
            transition: all 0.2s ease;
        }
        .notification-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .notification-unread {
            border-left: 4px solid #3b82f6;
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.05) 0%, rgba(255, 255, 255, 1) 10%);
        }
        .notification-read {
            opacity: 0.8;
        }
        .pulse-dot {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .tab-active {
            border-bottom: 2px solid #3b82f6;
            color: #3b82f6;
        }
        .priority-high {
            border-left-color: #dc2626;
            background: linear-gradient(90deg, rgba(220, 38, 38, 0.05) 0%, rgba(255, 255, 255, 1) 10%);
        }
        .priority-medium {
            border-left-color: #f59e0b;
            background: linear-gradient(90deg, rgba(245, 158, 11, 0.05) 0%, rgba(255, 255, 255, 1) 10%);
        }
        .priority-low {
            border-left-color: #10b981;
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.05) 0%, rgba(255, 255, 255, 1) 10%);
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
                <div class="flex items-center">
                    <h1 class="text-xl md:text-2xl font-bold text-gray-900">Bildirimler</h1>
                    <span id="unread-count" class="ml-3 px-2 py-1 bg-red-500 text-white text-xs rounded-full">8</span>
                </div>
            </div>
            <div class="flex items-center space-x-2 md:space-x-4">
                <button onclick="markAllAsRead()" class="px-3 md:px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center text-sm md:text-base">
                    <svg class="w-4 h-4 mr-1 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="hidden sm:inline">Tümünü Okundu İşaretle</span>
                    <span class="sm:hidden">Tümü Okundu</span>
                </button>
                <button onclick="openNotificationSettings()" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Ana İçerik -->
    <main class="max-w-4xl mx-auto p-4 md:p-6">
        <!-- Özet Kartları -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6 mb-6">
            <div class="bg-white p-3 md:p-6 rounded-lg card-shadow">
                <div class="flex items-center">
                    <div class="w-8 h-8 md:w-12 md:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 md:w-6 md:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 15h8v-2H4v2zM4 11h10V9H4v2zM4 7h12V5H4v2z"></path>
                        </svg>
                    </div>
                    <div class="ml-2 md:ml-4">
                        <p class="text-xs md:text-sm font-medium text-gray-600">Toplam</p>
                        <p class="text-lg md:text-2xl font-bold text-gray-900">24</p>
                    </div>
                </div>
                <div class="mt-2 md:mt-4">
                    <span class="text-blue-600 text-xs md:text-sm font-medium">Tüm bildirimler</span>
                </div>
            </div>

            <div class="bg-white p-3 md:p-6 rounded-lg card-shadow">
                <div class="flex items-center">
                    <div class="w-8 h-8 md:w-12 md:h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <div class="w-2 h-2 bg-red-500 rounded-full pulse-dot"></div>
                    </div>
                    <div class="ml-2 md:ml-4">
                        <p class="text-xs md:text-sm font-medium text-gray-600">Okunmamış</p>
                        <p class="text-lg md:text-2xl font-bold text-red-600">8</p>
                    </div>
                </div>
                <div class="mt-2 md:mt-4">
                    <span class="text-red-600 text-xs md:text-sm font-medium">Yeni bildirimler</span>
                </div>
            </div>

            <div class="bg-white p-3 md:p-6 rounded-lg card-shadow">
                <div class="flex items-center">
                    <div class="w-8 h-8 md:w-12 md:h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 md:w-6 md:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-2 md:ml-4">
                        <p class="text-xs md:text-sm font-medium text-gray-600">Önemli</p>
                        <p class="text-lg md:text-2xl font-bold text-yellow-600">3</p>
                    </div>
                </div>
                <div class="mt-2 md:mt-4">
                    <span class="text-yellow-600 text-xs md:text-sm font-medium">Yüksek öncelik</span>
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
                        <p class="text-xs md:text-sm font-medium text-gray-600">Bu Hafta</p>
                        <p class="text-lg md:text-2xl font-bold text-green-600">12</p>
                    </div>
                </div>
                <div class="mt-2 md:mt-4">
                    <span class="text-green-600 text-xs md:text-sm font-medium">Yeni bildirimler</span>
                </div>
            </div>
        </div>

        <!-- Filtreler -->
        <div class="bg-white rounded-lg card-shadow mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-4 md:px-6" aria-label="Tabs">
                    <button onclick="switchTab('all')" id="tab-all" class="py-4 px-1 border-b-2 font-medium text-sm tab-active">
                        Tümü
                    </button>
                    <button onclick="switchTab('unread')" id="tab-unread" class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Okunmamış
                    </button>
                    <button onclick="switchTab('important')" id="tab-important" class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Önemli
                    </button>
                    <button onclick="switchTab('system')" id="tab-system" class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Sistem
                    </button>
                </nav>
            </div>

            <div class="p-4 md:p-6 border-b border-gray-200">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900" id="section-title">Tüm Bildirimler</h2>
                        <p class="text-sm text-gray-600 mt-1" id="section-description">Sistem bildirimleri ve güncellemeler</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="relative">
                            <input type="text" id="searchNotifications" placeholder="Bildirim ara..." class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <select id="typeFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" onchange="filterNotifications()">
                            <option value="">Tüm Türler</option>
                            <option value="system">Sistem</option>
                            <option value="payment">Ödeme</option>
                            <option value="project">Proje</option>
                            <option value="commission">Komisyon</option>
                            <option value="update">Güncelleme</option>
                        </select>
                        <select id="priorityFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" onchange="filterNotifications()">
                            <option value="">Tüm Öncelikler</option>
                            <option value="high">Yüksek</option>
                            <option value="medium">Orta</option>
                            <option value="low">Düşük</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bildirimler Listesi -->
        <div id="notifications-container" class="space-y-3 md:space-y-4 mb-6">
        </div>

        <!-- Bildirim Detay Modal -->
        <div id="notification-detail-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-4 md:p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Bildirim Detayları</h3>
                        <button onclick="closeNotificationDetail()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div id="notification-detail-content" class="p-4 md:p-6">
                    <!-- İçerik JavaScript ile doldurulacak -->
                </div>
            </div>
        </div>

        <!-- Bildirim Ayarları Modal -->
        <div id="notification-settings-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-md w-full">
                <div class="p-4 md:p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Bildirim Ayarları</h3>
                        <button onclick="closeNotificationSettings()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-4 md:p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">E-posta Bildirimleri</p>
                                <p class="text-sm text-gray-600">Önemli güncellemeler için e-posta al</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">SMS Bildirimleri</p>
                                <p class="text-sm text-gray-600">Acil durumlar için SMS al</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">Proje Bildirimleri</p>
                                <p class="text-sm text-gray-600">Proje güncellemeleri</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">Ödeme Bildirimleri</p>
                                <p class="text-sm text-gray-600">Ödeme ve fatura bildirimleri</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 pt-6 mt-6 border-t border-gray-200">
                        <button onclick="closeNotificationSettings()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            İptal
                        </button>
                        <button onclick="saveNotificationSettings()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Kaydet
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Örnek bildirim verileri
        const notificationsData = [
            {
                id: 'NOT-2024-001',
                type: 'payment',
                priority: 'high',
                title: 'Ödeme Hatırlatması',
                message: 'FAT-2024-001 numaralı faturanızın vadesi geçmiştir. Lütfen en kısa sürede ödeme yapınız.',
                fullMessage: 'ABC Tekstil Ltd. firması için düzenlenen FAT-2024-001 numaralı faturanızın vadesi 15 gün önce geçmiştir. Toplam tutar: ₺15.000. Lütfen en kısa sürede ödeme yaparak hesabınızı güncel tutunuz.',
                date: '2024-06-25T10:30:00',
                isRead: false,
                actionUrl: '/faturalar',
                actionText: 'Faturayı Görüntüle'
            },
            {
                id: 'NOT-2024-002',
                type: 'commission',
                priority: 'medium',
                title: 'Yeni Komisyon Ödemesi',
                message: 'HE-2024-001 numaralı hak edişiniz için ₺24.000 ödeme yapılmıştır.',
                fullMessage: 'GHI Market Zinciri projesinden elde ettiğiniz HE-2024-001 numaralı hak edişiniz için ₺24.000 tutarında ödeme hesabınıza yatırılmıştır. Ödeme tarihi: 25 Haziran 2024.',
                date: '2024-06-25T09:15:00',
                isRead: false,
                actionUrl: '/hak-edislerim',
                actionText: 'Hak Edişi Görüntüle'
            },
            {
                id: 'NOT-2024-003',
                type: 'project',
                priority: 'medium',
                title: 'Proje Durumu Güncellendi',
                message: 'TLP-2024-004 numaralı projeniz "Tamamlandı" durumuna geçirilmiştir.',
                fullMessage: 'GHI Market Zinciri için yürüttüğünüz TLP-2024-004 numaralı "Perakende ERP Sistemi" projesi başarıyla tamamlanmış ve müşteri tarafından onaylanmıştır. Proje kapanış raporu hazırlanmıştır.',
                date: '2024-06-24T16:45:00',
                isRead: true,
                actionUrl: '/projelerim',
                actionText: 'Projeyi Görüntüle'
            },
            {
                id: 'NOT-2024-004',
                type: 'system',
                priority: 'low',
                title: 'Sistem Bakımı Bildirimi',
                message: 'Sistem bakımı 28 Haziran Cuma günü 02:00-04:00 saatleri arasında yapılacaktır.',
                fullMessage: 'Sevgili bayimiz, sistem performansını artırmak için 28 Haziran 2024 Cuma günü saat 02:00-04:00 arasında planlı bakım yapılacaktır. Bu süre zarfında sisteme erişim sağlanamayacaktır.',
                date: '2024-06-24T14:20:00',
                isRead: false,
                actionUrl: null,
                actionText: null
            },
            {
                id: 'NOT-2024-005',
                type: 'update',
                priority: 'medium',
                title: 'Yeni Özellik: Mobil Uygulama',
                message: 'Bayi yönetim sistemi mobil uygulaması yayınlandı. App Store ve Google Play\'den indirebilirsiniz.',
                fullMessage: 'Bayi yönetim sistemimizin mobil uygulaması artık App Store ve Google Play Store\'da mevcut. Projelerinizi, komisyonlarınızı ve faturalarınızı mobil cihazınızdan takip edebilirsiniz.',
                date: '2024-06-24T11:00:00',
                isRead: false,
                actionUrl: '/mobil-uygulama',
                actionText: 'Uygulamayı İndir'
            },
            {
                id: 'NOT-2024-006',
                type: 'payment',
                priority: 'high',
                title: 'Ödeme Onaylandı',
                message: 'ODE-2024-003 numaralı ödemeniz onaylanmış ve işleme alınmıştır.',
                fullMessage: 'DEF Makina San. için yaptığınız ODE-2024-003 numaralı ₺15.000 tutarındaki ödemeniz muhasebe departmanı tarafından onaylanmış ve hesabınıza işlenmiştir.',
                date: '2024-06-23T15:30:00',
                isRead: true,
                actionUrl: '/faturalar',
                actionText: 'Ödemeyi Görüntüle'
            },
            {
                id: 'NOT-2024-007',
                type: 'project',
                priority: 'low',
                title: 'Yeni Proje Talebi',
                message: 'MNO E-Ticaret firmasından yeni bir proje talebi geldi.',
                fullMessage: 'MNO E-Ticaret firması e-ticaret entegrasyonu için yeni bir proje talebi gönderdi. Proje detayları ve teknik gereksinimler proje yönetim panelinde görüntülenebilir.',
                date: '2024-06-23T13:15:00',
                isRead: false,
                actionUrl: '/projelerim',
                actionText: 'Proje Talebini İncele'
            },
            {
                id: 'NOT-2024-008',
                type: 'commission',
                priority: 'medium',
                title: 'Komisyon Hesaplandı',
                message: 'TLP-2024-007 projesi için komisyonunuz hesaplanmıştır: ₺14.250',
                fullMessage: 'MNO E-Ticaret projesi (TLP-2024-007) için %19 oranında komisyonunuz hesaplanmıştır. Toplam komisyon tutarı: ₺14.250. Ödeme onay sürecindedir.',
                date: '2024-06-22T17:20:00',
                isRead: true,
                actionUrl: '/hak-edislerim',
                actionText: 'Komisyonu Görüntüle'
            }
        ];

        let currentTab = 'all';
        let filteredNotifications = [...notificationsData];

        // Sayfa yüklendiğinde
        window.addEventListener('load', function() {
            renderNotifications();
            setupSearch();
            updateUnreadCount();
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

            // Başlık ve açıklamayı güncelle
            const titles = {
                'all': 'Tüm Bildirimler',
                'unread': 'Okunmamış Bildirimler',
                'important': 'Önemli Bildirimler',
                'system': 'Sistem Bildirimleri'
            };
            
            const descriptions = {
                'all': 'Sistem bildirimleri ve güncellemeler',
                'unread': 'Henüz okunmamış bildirimler',
                'important': 'Yüksek öncelikli bildirimler',
                'system': 'Sistem bakım ve güncelleme bildirimleri'
            };

            document.getElementById('section-title').textContent = titles[tabName];
            document.getElementById('section-description').textContent = descriptions[tabName];

            currentTab = tabName;
            filterNotifications();
        }

        // Bildirimleri render et
        function renderNotifications() {
            const container = document.getElementById('notifications-container');
            container.innerHTML = '';

            if (filteredNotifications.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 15h8v-2H4v2zM4 11h10V9H4v2zM4 7h12V5H4v2z"></path>
                        </svg>
                        <p class="text-gray-500 text-lg">Aradığınız kriterlere uygun bildirim bulunamadı</p>
                    </div>
                `;
                return;
            }

            filteredNotifications.forEach(notification => {
                const notificationCard = createNotificationCard(notification);
                container.appendChild(notificationCard);
            });
        }

        // Bildirim kartı oluştur
        function createNotificationCard(notification) {
            const card = document.createElement('div');
            const baseClasses = 'bg-white rounded-lg card-shadow notification-card cursor-pointer';
            const readClass = notification.isRead ? 'notification-read' : 'notification-unread';
            const priorityClass = `priority-${notification.priority}`;
            
            card.className = `${baseClasses} ${readClass} ${priorityClass}`;
            card.onclick = () => viewNotificationDetail(notification.id);
            
            const typeIcons = {
                'system': '⚙️',
                'payment': '💳',
                'project': '📋',
                'commission': '💰',
                'update': '🔄'
            };

            const priorityColors = {
                'high': 'text-red-600',
                'medium': 'text-yellow-600',
                'low': 'text-green-600'
            };

            const priorityTexts = {
                'high': 'Yüksek',
                'medium': 'Orta',
                'low': 'Düşük'
            };

            card.innerHTML = `
                <div class="p-4 md:p-6">
                    <div class="flex items-start gap-4">
                        <!-- İkon -->
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-lg">
                                ${typeIcons[notification.type] || '📢'}
                            </div>
                        </div>
                        
                        <!-- İçerik -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4 mb-2">
                                <div class="flex-1">
                                    <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-1">
                                        ${notification.title}
                                        ${!notification.isRead ? '<span class="w-2 h-2 bg-blue-500 rounded-full inline-block ml-2"></span>' : ''}
                                    </h3>
                                    <p class="text-sm text-gray-600 line-clamp-2">${notification.message}</p>
                                </div>
                                <div class="flex-shrink-0 text-right">
                                    <span class="text-xs ${priorityColors[notification.priority]} font-medium">
                                        ${priorityTexts[notification.priority]}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">${formatDateTime(notification.date)}</p>
                                </div>
                            </div>
                            
                            <!-- Alt Bilgiler -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                                        ${getTypeText(notification.type)}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    ${notification.actionText ? `
                                        <button onclick="event.stopPropagation(); handleNotificationAction('${notification.id}')" class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                            ${notification.actionText}
                                        </button>
                                    ` : ''}
                                    <button onclick="event.stopPropagation(); toggleReadStatus('${notification.id}')" class="text-gray-400 hover:text-gray-600 p-1">
                                        ${notification.isRead ? 
                                            '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 7.89a2 2 0 002.83 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>' :
                                            '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
                                        }
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            return card;
        }

        // Bildirim detayını görüntüle
        function viewNotificationDetail(notificationId) {
            const notification = notificationsData.find(n => n.id === notificationId);
            if (!notification) return;

            // Okunmamışsa okundu işaretle
            if (!notification.isRead) {
                notification.isRead = true;
                updateUnreadCount();
                renderNotifications();
            }

            const modal = document.getElementById('notification-detail-modal');
            const content = document.getElementById('notification-detail-content');
            
            const typeIcons = {
                'system': '⚙️',
                'payment': '💳',
                'project': '📋',
                'commission': '💰',
                'update': '🔄'
            };

            content.innerHTML = `
                <div class="space-y-6">
                    <!-- Bildirim Başlığı -->
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-xl">
                            ${typeIcons[notification.type] || '📢'}
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">${notification.title}</h3>
                            <div class="flex items-center gap-4 text-sm text-gray-600">
                                <span>${getTypeText(notification.type)}</span>
                                <span>•</span>
                                <span>${formatDateTime(notification.date)}</span>
                                <span>•</span>
                                <span class="font-medium ${getPriorityColor(notification.priority)}">
                                    ${getPriorityText(notification.priority)} Öncelik
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Bildirim İçeriği -->
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed">${notification.fullMessage}</p>
                    </div>

                    <!-- İşlem Butonları -->
                    ${notification.actionText ? `
                        <div class="flex gap-3 pt-6 border-t border-gray-200">
                            <button onclick="handleNotificationAction('${notification.id}')" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                ${notification.actionText}
                            </button>
                            <button onclick="closeNotificationDetail()" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Kapat
                            </button>
                        </div>
                    ` : `
                        <div class="flex justify-end pt-6 border-t border-gray-200">
                            <button onclick="closeNotificationDetail()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Kapat
                            </button>
                        </div>
                    `}
                </div>
            `;
            
            modal.classList.remove('hidden');
        }

        // Tarih formatlama
        function formatDateTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));
            
            if (diffInHours < 1) {
                return 'Az önce';
            } else if (diffInHours < 24) {
                return `${diffInHours} saat önce`;
            } else if (diffInHours < 48) {
                return 'Dün';
            } else {
                return date.toLocaleDateString('tr-TR', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        }

        // Arama fonksiyonu
        function setupSearch() {
            const searchInput = document.getElementById('searchNotifications');
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                filterNotifications(searchTerm);
            });
        }

        // Filtreleme
        function filterNotifications(searchTerm = '') {
            const typeFilter = document.getElementById('typeFilter').value;
            const priorityFilter = document.getElementById('priorityFilter').value;
            
            filteredNotifications = notificationsData.filter(notification => {
                // Sekme filtresi
                let matchesTab = true;
                switch(currentTab) {
                    case 'unread':
                        matchesTab = !notification.isRead;
                        break;
                    case 'important':
                        matchesTab = notification.priority === 'high';
                        break;
                    case 'system':
                        matchesTab = notification.type === 'system';
                        break;
                }
                
                // Arama filtresi
                const matchesSearch = searchTerm === '' || 
                    notification.title.toLowerCase().includes(searchTerm) ||
                    notification.message.toLowerCase().includes(searchTerm);
                
                // Tür filtresi
                const matchesType = typeFilter === '' || notification.type === typeFilter;
                
                // Öncelik filtresi
                const matchesPriority = priorityFilter === '' || notification.priority === priorityFilter;
                
                return matchesTab && matchesSearch && matchesType && matchesPriority;
            });
            
            renderNotifications();
        }

        // Yardımcı fonksiyonlar
        function getTypeText(type) {
            const types = {
                'system': 'Sistem',
                'payment': 'Ödeme',
                'project': 'Proje',
                'commission': 'Komisyon',
                'update': 'Güncelleme'
            };
            return types[type] || type;
        }

        function getPriorityText(priority) {
            const priorities = {
                'high': 'Yüksek',
                'medium': 'Orta',
                'low': 'Düşük'
            };
            return priorities[priority] || priority;
        }

        function getPriorityColor(priority) {
            const colors = {
                'high': 'text-red-600',
                'medium': 'text-yellow-600',
                'low': 'text-green-600'
            };
            return colors[priority] || 'text-gray-600';
        }

        // Okunmamış sayısını güncelle
        function updateUnreadCount() {
            const unreadCount = notificationsData.filter(n => !n.isRead).length;
            const countElement = document.getElementById('unread-count');
            if (unreadCount > 0) {
                countElement.textContent = unreadCount;
                countElement.classList.remove('hidden');
            } else {
                countElement.classList.add('hidden');
            }
        }

        // Okundu durumunu değiştir
        function toggleReadStatus(notificationId) {
            const notification = notificationsData.find(n => n.id === notificationId);
            if (notification) {
                notification.isRead = !notification.isRead;
                updateUnreadCount();
                renderNotifications();
            }
        }

        // Tümünü okundu işaretle
        function markAllAsRead() {
            notificationsData.forEach(notification => {
                notification.isRead = true;
            });
            updateUnreadCount();
            renderNotifications();
            
            // Başarı mesajı göster
            showSuccessMessage('Tüm bildirimler okundu olarak işaretlendi');
        }

        // Bildirim aksiyonunu işle
        function handleNotificationAction(notificationId) {
            const notification = notificationsData.find(n => n.id === notificationId);
            if (notification && notification.actionUrl) {
                alert(`${notification.actionUrl} sayfasına yönlendiriliyorsunuz...`);
                closeNotificationDetail();
            }
        }

        // Modal kapatma fonksiyonları
        function closeNotificationDetail() {
            document.getElementById('notification-detail-modal').classList.add('hidden');
        }

        function openNotificationSettings() {
            document.getElementById('notification-settings-modal').classList.remove('hidden');
        }

        function closeNotificationSettings() {
            document.getElementById('notification-settings-modal').classList.add('hidden');
        }

        function saveNotificationSettings() {
            showSuccessMessage('Bildirim ayarları kaydedildi');
            closeNotificationSettings();
        }

        // Başarı mesajı göster
        function showSuccessMessage(message) {
            const successDiv = document.createElement('div');
            successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg success-message z-50';
            successDiv.textContent = message;
            document.body.appendChild(successDiv);
            
            setTimeout(() => {
                successDiv.remove();
            }, 3000);
        }

        function goBack() {
            window.history.back();
        }

        // Modal dışına tıklayınca kapatma
        document.getElementById('notification-detail-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeNotificationDetail();
            }
        });

        document.getElementById('notification-settings-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeNotificationSettings();
            }
        });
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9722f28c80d1b657',t:'MTc1NTcwMzk4OS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
