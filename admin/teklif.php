<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teklif Talep Et - Bayi Yönetim Sistemi</title>
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
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">Teklif Talep Et</h1>
            </div>
            <div class="flex items-center space-x-4">
                <button onclick="saveDraft()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Taslak Kaydet
                </button>
            </div>
        </div>
    </header>

    <!-- Ana İçerik -->
    <main class="max-w-6xl mx-auto p-4 md:p-6">
        <!-- Başarı Mesajı -->
        <div id="success-message" class="hidden success-message mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div>
                    <h3 class="text-green-800 font-medium">Teklif talebi başarıyla gönderildi!</h3>
                    <p class="text-green-700 text-sm mt-1">Talebiniz incelemeye alındı. En kısa sürede size dönüş yapılacaktır.</p>
                </div>
            </div>
        </div>

        <form onsubmit="submitQuoteRequest(event)" class="space-y-6">
            <!-- Müşteri Bilgileri -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="p-6 border-b border-gray-200">
                    <div class="form-section">
                        <h2 class="text-lg font-semibold text-gray-900">Müşteri Bilgileri</h2>
                        <p class="text-sm text-gray-600 mt-1">Teklif talep edilecek müşteriyi seçin veya yeni müşteri ekleyin</p>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required-field">Müşteri Seç</label>
                            <div class="relative">
                                <select id="customer" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required onchange="loadCustomerInfo()">
                                    <option value="">Müşteri Seçiniz</option>
                                    <option value="1">ABC Teknoloji Ltd. - Ahmet Yılmaz</option>
                                    <option value="2">XYZ İnşaat A.Ş. - Mehmet Demir</option>
                                    <option value="3">DEF Elektronik - Ayşe Kaya</option>
                                    <option value="4">GHI Lojistik - Fatma Özkan</option>
                                    <option value="new">+ Yeni Müşteri Ekle</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">İletişim Kişisi</label>
                            <input type="text" id="contactPerson" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Yetkili kişi adı" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                            <input type="tel" id="customerPhone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="+90 5XX XXX XX XX" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">E-posta</label>
                            <input type="email" id="customerEmail" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="ornek@email.com" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Proje Bilgileri -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="p-6 border-b border-gray-200">
                    <div class="form-section">
                        <h2 class="text-lg font-semibold text-gray-900">Proje Bilgileri</h2>
                        <p class="text-sm text-gray-600 mt-1">Teklif talep edilecek proje hakkında detaylı bilgi verin</p>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2 required-field">Proje Adı</label>
                            <input type="text" id="projectName" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Proje adını girin" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required-field">Kategori</label>
                            <select id="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">Kategori Seçiniz</option>
                                <option value="yazilim">Yazılım Geliştirme</option>
                                <option value="web">Web Tasarım</option>
                                <option value="mobil">Mobil Uygulama</option>
                                <option value="donanim">Donanım Kurulumu</option>
                                <option value="network">Ağ Altyapısı</option>
                                <option value="danismanlik">Danışmanlık</option>
                                <option value="bakim">Bakım & Destek</option>
                                <option value="egitim">Eğitim</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Öncelik Seviyesi</label>
                            <select id="priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="normal">Normal</option>
                                <option value="yuksek">Yüksek</option>
                                <option value="acil">Acil</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bütçe Aralığı</label>
                            <select id="budget" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Bütçe Seçiniz</option>
                                <option value="0-10k">0 - 10.000₺</option>
                                <option value="10k-25k">10.000 - 25.000₺</option>
                                <option value="25k-50k">25.000 - 50.000₺</option>
                                <option value="50k-100k">50.000 - 100.000₺</option>
                                <option value="100k-250k">100.000 - 250.000₺</option>
                                <option value="250k+">250.000₺+</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tahmini Süre</label>
                            <select id="duration" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Süre Seçiniz</option>
                                <option value="1-hafta">1 Hafta</option>
                                <option value="2-hafta">2 Hafta</option>
                                <option value="1-ay">1 Ay</option>
                                <option value="2-ay">2 Ay</option>
                                <option value="3-ay">3 Ay</option>
                                <option value="6-ay">6 Ay</option>
                                <option value="1-yil">1 Yıl</option>
                                <option value="belirsiz">Belirsiz</option>
                            </select>
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2 required-field">Proje Açıklaması</label>
                            <textarea id="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Proje hakkında detaylı bilgi verin. Ne yapılması gerektiğini, hangi özelliklerin olması gerektiğini açıklayın..." required></textarea>
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Teknik Gereksinimler</label>
                            <textarea id="requirements" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Teknik gereksinimler, kullanılacak teknolojiler, entegrasyonlar vb."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Zaman Çizelgesi -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="p-6 border-b border-gray-200">
                    <div class="form-section">
                        <h2 class="text-lg font-semibold text-gray-900">Zaman Çizelgesi</h2>
                        <p class="text-sm text-gray-600 mt-1">Proje için önemli tarihleri belirleyin</p>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Teklif Son Tarihi</label>
                            <input type="date" id="quoteDeadline" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Proje Başlangıç Tarihi</label>
                            <input type="date" id="startDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Proje Bitiş Tarihi</label>
                            <input type="date" id="endDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ürün/Hizmet Listesi -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="p-6 border-b border-gray-200">
                    <div class="form-section">
                        <h2 class="text-lg font-semibold text-gray-900">Ürün/Hizmet Listesi</h2>
                        <p class="text-sm text-gray-600 mt-1">Talep edilen ürün ve hizmetleri ekleyin</p>
                    </div>
                </div>
                
                <div class="p-6">
                    <div id="product-list" class="space-y-4">
                        <!-- İlk ürün satırı -->
                        <div class="product-item border border-gray-200 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                <div class="md:col-span-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ürün/Hizmet Adı</label>
                                    <input type="text" class="product-name w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ürün veya hizmet adı">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Miktar</label>
                                    <input type="number" class="product-quantity w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="1" min="1">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Birim</label>
                                    <select class="product-unit w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="adet">Adet</option>
                                        <option value="saat">Saat</option>
                                        <option value="gun">Gün</option>
                                        <option value="ay">Ay</option>
                                        <option value="proje">Proje</option>
                                        <option value="lisans">Lisans</option>
                                    </select>
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                                    <input type="text" class="product-description w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ek açıklama">
                                </div>
                                <div class="md:col-span-1">
                                    <button type="button" onclick="removeProduct(this)" class="w-full px-3 py-2 text-red-600 border border-red-300 rounded-lg hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" onclick="addProduct()" class="mt-4 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Ürün/Hizmet Ekle
                    </button>
                </div>
            </div>

            <!-- Ek Seçenekler -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="p-6 border-b border-gray-200">
                    <div class="form-section">
                        <h2 class="text-lg font-semibold text-gray-900">Ek Seçenekler</h2>
                        <p class="text-sm text-gray-600 mt-1">Projeye dahil edilmesini istediğiniz ek hizmetleri seçin</p>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="installation" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="installation" class="ml-3 text-sm text-gray-700">Kurulum ve Konfigürasyon</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="training" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="training" class="ml-3 text-sm text-gray-700">Kullanıcı Eğitimi</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="documentation" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="documentation" class="ml-3 text-sm text-gray-700">Dokümantasyon</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="support1year" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="support1year" class="ml-3 text-sm text-gray-700">1 Yıl Teknik Destek</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="maintenance" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="maintenance" class="ml-3 text-sm text-gray-700">Periyodik Bakım</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="hosting" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="hosting" class="ml-3 text-sm text-gray-700">Hosting/Barındırma</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dosya Ekleri -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="p-6 border-b border-gray-200">
                    <div class="form-section">
                        <h2 class="text-lg font-semibold text-gray-900">Dosya Ekleri</h2>
                        <p class="text-sm text-gray-600 mt-1">Proje ile ilgili dökümanları, şemaları veya referans dosyaları ekleyin</p>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="file-upload-area rounded-lg p-8 text-center" ondrop="handleDrop(event)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <p class="text-gray-600 mb-2">Dosyaları buraya sürükleyin veya</p>
                        <button type="button" onclick="document.getElementById('file-input').click()" class="text-blue-600 hover:text-blue-800 font-medium">
                            Dosya Seç
                        </button>
                        <input type="file" id="file-input" multiple class="hidden" onchange="handleFileSelect(event)">
                        <p class="text-xs text-gray-500 mt-2">PDF, DOC, XLS, JPG, PNG (Maks. 10MB)</p>
                    </div>
                    
                    <div id="file-list" class="mt-4 space-y-2"></div>
                </div>
            </div>

            <!-- Ek Notlar -->
            <div class="bg-white rounded-lg card-shadow">
                <div class="p-6 border-b border-gray-200">
                    <div class="form-section">
                        <h2 class="text-lg font-semibold text-gray-900">Ek Notlar ve Özel İstekler</h2>
                        <p class="text-sm text-gray-600 mt-1">Proje hakkında belirtmek istediğiniz özel durumlar</p>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Referans Projeler</label>
                            <textarea id="references" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Benzer projeler, referans siteler veya uygulamalar..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Özel Notlar</label>
                            <textarea id="notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Özel istekler, kısıtlamalar, tercihler vb."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Butonları -->
            <div class="bg-white rounded-lg card-shadow p-6">
                <div class="flex flex-col md:flex-row justify-end space-y-3 md:space-y-0 md:space-x-4">
                    <button type="button" onclick="goBack()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        İptal
                    </button>
                    <button type="button" onclick="saveDraft()" class="px-6 py-3 border border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50 transition-colors">
                        Taslak Kaydet
                    </button>
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        Teklif Talebini Gönder
                    </button>
                </div>
            </div>
        </form>
    </main>

    <script>
        // Müşteri bilgilerini yükle
        function loadCustomerInfo() {
            const customerSelect = document.getElementById('customer');
            const selectedValue = customerSelect.value;
            
            // Örnek müşteri verileri
            const customers = {
                '1': {
                    name: 'Ahmet Yılmaz',
                    phone: '+90 532 123 45 67',
                    email: 'ahmet@abcteknoloji.com'
                },
                '2': {
                    name: 'Mehmet Demir',
                    phone: '+90 533 987 65 43',
                    email: 'mehmet@xyzinsaat.com'
                },
                '3': {
                    name: 'Ayşe Kaya',
                    phone: '+90 534 456 78 90',
                    email: 'ayse@defelektronik.com'
                },
                '4': {
                    name: 'Fatma Özkan',
                    phone: '+90 535 321 98 76',
                    email: 'fatma@ghilojistik.com'
                }
            };
            
            if (customers[selectedValue]) {
                document.getElementById('contactPerson').value = customers[selectedValue].name;
                document.getElementById('customerPhone').value = customers[selectedValue].phone;
                document.getElementById('customerEmail').value = customers[selectedValue].email;
            } else {
                document.getElementById('contactPerson').value = '';
                document.getElementById('customerPhone').value = '';
                document.getElementById('customerEmail').value = '';
            }
            
            if (selectedValue === 'new') {
                alert('Yeni müşteri ekleme sayfasına yönlendiriliyorsunuz...');
            }
        }

        // Ürün/hizmet ekleme
        function addProduct() {
            const productList = document.getElementById('product-list');
            const newProduct = document.createElement('div');
            newProduct.className = 'product-item border border-gray-200 rounded-lg p-4';
            newProduct.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ürün/Hizmet Adı</label>
                        <input type="text" class="product-name w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ürün veya hizmet adı">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Miktar</label>
                        <input type="number" class="product-quantity w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="1" min="1">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Birim</label>
                        <select class="product-unit w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="adet">Adet</option>
                            <option value="saat">Saat</option>
                            <option value="gun">Gün</option>
                            <option value="ay">Ay</option>
                            <option value="proje">Proje</option>
                            <option value="lisans">Lisans</option>
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                        <input type="text" class="product-description w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ek açıklama">
                    </div>
                    <div class="md:col-span-1">
                        <button type="button" onclick="removeProduct(this)" class="w-full px-3 py-2 text-red-600 border border-red-300 rounded-lg hover:bg-red-50 transition-colors">
                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            productList.appendChild(newProduct);
        }

        // Ürün/hizmet silme
        function removeProduct(button) {
            const productItem = button.closest('.product-item');
            const productList = document.getElementById('product-list');
            
            if (productList.children.length > 1) {
                productItem.remove();
            } else {
                alert('En az bir ürün/hizmet kalmalıdır.');
            }
        }

        // Dosya seçme
        function handleFileSelect(event) {
            const files = event.target.files;
            displayFiles(files);
        }

        // Sürükle bırak
        function handleDragOver(event) {
            event.preventDefault();
            event.currentTarget.classList.add('dragover');
        }

        function handleDragLeave(event) {
            event.currentTarget.classList.remove('dragover');
        }

        function handleDrop(event) {
            event.preventDefault();
            event.currentTarget.classList.remove('dragover');
            const files = event.dataTransfer.files;
            displayFiles(files);
        }

        // Dosyaları göster
        function displayFiles(files) {
            const fileList = document.getElementById('file-list');
            
            for (let file of files) {
                const fileItem = document.createElement('div');
                fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
                fileItem.innerHTML = `
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">${file.name}</p>
                            <p class="text-xs text-gray-500">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                        </div>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                `;
                fileList.appendChild(fileItem);
            }
        }

        // Form gönderme
        function submitQuoteRequest(event) {
            event.preventDefault();
            
            // Form validasyonu
            const requiredFields = ['customer', 'projectName', 'category', 'description'];
            let isValid = true;
            
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    field.classList.add('border-red-500');
                    isValid = false;
                } else {
                    field.classList.remove('border-red-500');
                }
            });
            
            if (!isValid) {
                alert('Lütfen zorunlu alanları doldurun.');
                return;
            }
            
            // Ürün listesi kontrolü
            const products = [];
            document.querySelectorAll('.product-item').forEach(item => {
                const name = item.querySelector('.product-name').value;
                const quantity = item.querySelector('.product-quantity').value;
                const unit = item.querySelector('.product-unit').value;
                const description = item.querySelector('.product-description').value;
                
                if (name.trim()) {
                    products.push({ name, quantity, unit, description });
                }
            });
            
            // Form verilerini topla
            const formData = {
                customer: document.getElementById('customer').value,
                projectName: document.getElementById('projectName').value,
                category: document.getElementById('category').value,
                priority: document.getElementById('priority').value,
                budget: document.getElementById('budget').value,
                duration: document.getElementById('duration').value,
                description: document.getElementById('description').value,
                requirements: document.getElementById('requirements').value,
                quoteDeadline: document.getElementById('quoteDeadline').value,
                startDate: document.getElementById('startDate').value,
                endDate: document.getElementById('endDate').value,
                products: products,
                options: {
                    installation: document.getElementById('installation').checked,
                    training: document.getElementById('training').checked,
                    documentation: document.getElementById('documentation').checked,
                    support1year: document.getElementById('support1year').checked,
                    maintenance: document.getElementById('maintenance').checked,
                    hosting: document.getElementById('hosting').checked
                },
                references: document.getElementById('references').value,
                notes: document.getElementById('notes').value
            };
            
            console.log('Teklif talebi gönderiliyor:', formData);
            
            // Başarı mesajını göster
            const successMessage = document.getElementById('success-message');
            successMessage.classList.remove('hidden');
            
            // Sayfayı yukarı kaydır
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            // 3 saniye sonra formu temizle
            setTimeout(() => {
                if (confirm('Teklif talebiniz başarıyla gönderildi. Yeni bir teklif talebi oluşturmak ister misiniz?')) {
                    location.reload();
                } else {
                    goBack();
                }
            }, 3000);
        }

        // Taslak kaydetme
        function saveDraft() {
            const formData = {
                customer: document.getElementById('customer').value,
                projectName: document.getElementById('projectName').value,
                category: document.getElementById('category').value,
                description: document.getElementById('description').value
            };
            
            localStorage.setItem('quoteRequestDraft', JSON.stringify(formData));
            alert('Taslak başarıyla kaydedildi!');
        }

        // Geri gitme
        function goBack() {
            if (confirm('Değişiklikler kaydedilmeyecek. Çıkmak istediğinizden emin misiniz?')) {
                window.history.back();
            }
        }

        // Sayfa yüklendiğinde taslağı kontrol et
        window.addEventListener('load', function() {
            const draft = localStorage.getItem('quoteRequestDraft');
            if (draft) {
                const data = JSON.parse(draft);
                if (confirm('Kaydedilmiş bir taslağınız var. Yüklemek ister misiniz?')) {
                    document.getElementById('customer').value = data.customer || '';
                    document.getElementById('projectName').value = data.projectName || '';
                    document.getElementById('category').value = data.category || '';
                    document.getElementById('description').value = data.description || '';
                    
                    if (data.customer) {
                        loadCustomerInfo();
                    }
                }
            }
        });

        // Minimum tarih ayarlama
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('quoteDeadline').min = today;
        document.getElementById('startDate').min = today;
        document.getElementById('endDate').min = today;
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'97227683c01bdf5f',t:'MTc1NTY5ODkwOC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
