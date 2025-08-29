<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Teklif Oluştur - ERP Yönetim Sistemi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        }
        .card-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .form-input {
            transition: all 0.2s ease;
        }
        .form-input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .item-row {
            transition: all 0.3s ease;
        }
        .item-row.removing {
            opacity: 0;
            transform: translateX(-100%);
        }
        .success-message {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .loading-spinner {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="min-h-screen gradient-bg">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h1 class="text-xl font-semibold text-gray-900">Yeni Teklif Oluştur</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="goBack()" class="text-gray-500 hover:text-gray-700 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <form id="quoteForm" class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg card-shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Temel Bilgiler
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Customer Selection -->
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Müşteri <span class="text-red-500">*</span>
                        </label>
                        <select id="customer_id" name="customer_id" required class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">Müşteri seçiniz</option>
                            <option value="1">ABC Teknoloji Ltd.</option>
                            <option value="2">Demir İnşaat A.Ş.</option>
                            <option value="3">Kaya Otomotiv</option>
                            <option value="4">Şahin Tekstil</option>
                            <option value="5">Özkan Gıda San.</option>
                        </select>
                    </div>

                    <!-- Currency -->
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
                            Para Birimi <span class="text-red-500">*</span>
                        </label>
                        <select id="currency" name="currency" required class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="TRY">TRY - Türk Lirası</option>
                            <option value="USD">USD - Amerikan Doları</option>
                            <option value="EUR">EUR - Euro</option>
                        </select>
                    </div>

                    <!-- Validity Date -->
                    <div>
                        <label for="validity_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Geçerlilik Tarihi
                        </label>
                        <input type="date" id="validity_date" name="validity_date" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>

                    <!-- Status (for display only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
                        <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Taslak
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mt-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notlar
                    </label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Teklif ile ilgili notlarınızı buraya yazabilirsiniz..." class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none"></textarea>
                </div>
            </div>

            <!-- Quote Items -->
            <div class="bg-white rounded-lg card-shadow p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        Teklif Kalemleri
                    </h2>
                    <button type="button" onclick="addItem()" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Kalem Ekle
                    </button>
                </div>

                <!-- Items Table Header -->
                <div class="hidden md:grid grid-cols-12 gap-4 mb-4 p-3 bg-gray-50 rounded-lg text-sm font-medium text-gray-700">
                    <div class="col-span-4">Ürün</div>
                    <div class="col-span-2">Miktar</div>
                    <div class="col-span-2">Birim Fiyat</div>
                    <div class="col-span-2">İndirim</div>
                    <div class="col-span-1">Toplam</div>
                    <div class="col-span-1">İşlem</div>
                </div>

                <!-- Items Container -->
                <div id="itemsContainer" class="space-y-4">
                    <!-- Items will be added here dynamically -->
                </div>

                <!-- Add First Item Button (shown when no items) -->
                <div id="noItemsMessage" class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                    <svg class="mx-auto w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <p class="text-gray-500 mb-4">Henüz teklif kalemi eklenmemiş</p>
                    <button type="button" onclick="addItem()" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        İlk Kalemi Ekle
                    </button>
                </div>

                <!-- Total Section -->
                <div id="totalSection" class="hidden mt-6 pt-6 border-t border-gray-200">
                    <div class="flex justify-end">
                        <div class="w-full md:w-1/3">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex justify-between items-center text-lg font-semibold text-gray-900">
                                    <span>Genel Toplam:</span>
                                    <span id="grandTotal">0.00 TRY</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-end">
                <button type="button" onclick="goBack()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    İptal
                </button>
                <button type="submit" id="submitBtn" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center">
                    <span id="submitText">Teklif Oluştur</span>
                    <svg id="submitSpinner" class="hidden loading-spinner w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>
        </form>
    </main>

    <script>
        // Sample products data
        const products = [
            { id: 1, name: "Laptop Dell XPS 13", price: 25000 },
            { id: 2, name: "iPhone 15 Pro", price: 45000 },
            { id: 3, name: "Samsung Galaxy S24", price: 35000 },
            { id: 4, name: "MacBook Air M2", price: 40000 },
            { id: 5, name: "iPad Pro 12.9", price: 30000 },
            { id: 6, name: "AirPods Pro", price: 8000 },
            { id: 7, name: "Apple Watch Series 9", price: 12000 },
            { id: 8, name: "Sony WH-1000XM5", price: 6000 }
        ];

        let itemCounter = 0;
        let items = [];

        // Initialize page
        function initializePage() {
            // Set default validity date (30 days from now)
            const validityDate = new Date();
            validityDate.setDate(validityDate.getDate() + 30);
            document.getElementById('validity_date').value = validityDate.toISOString().split('T')[0];
            
            updateDisplay();
        }

        // Add new item
        function addItem() {
            itemCounter++;
            const itemId = `item_${itemCounter}`;
            
            const itemHtml = `
                <div class="item-row border border-gray-200 rounded-lg p-4" data-item-id="${itemId}">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <!-- Product Selection -->
                        <div class="md:col-span-4">
                            <label class="block md:hidden text-sm font-medium text-gray-700 mb-1">Ürün</label>
                            <select name="product_id" onchange="updateItemPrice('${itemId}')" required class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                <option value="">Ürün seçiniz</option>
                                ${products.map(p => `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`).join('')}
                            </select>
                        </div>

                        <!-- Quantity -->
                        <div class="md:col-span-2">
                            <label class="block md:hidden text-sm font-medium text-gray-700 mb-1">Miktar</label>
                            <input type="number" name="qty" min="1" step="1" value="1" onchange="calculateItemTotal('${itemId}')" required class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>

                        <!-- Unit Price -->
                        <div class="md:col-span-2">
                            <label class="block md:hidden text-sm font-medium text-gray-700 mb-1">Birim Fiyat</label>
                            <input type="number" name="unit_price" min="0" step="0.01" onchange="calculateItemTotal('${itemId}')" required class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>

                        <!-- Discount -->
                        <div class="md:col-span-2">
                            <label class="block md:hidden text-sm font-medium text-gray-700 mb-1">İndirim</label>
                            <input type="number" name="discount" min="0" step="0.01" value="0" onchange="calculateItemTotal('${itemId}')" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>

                        <!-- Total -->
                        <div class="md:col-span-1">
                            <label class="block md:hidden text-sm font-medium text-gray-700 mb-1">Toplam</label>
                            <div class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                                <span class="item-total font-medium">0.00</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="md:col-span-1">
                            <label class="block md:hidden text-sm font-medium text-gray-700 mb-1">İşlem</label>
                            <button type="button" onclick="removeItem('${itemId}')" class="w-full md:w-auto px-3 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', itemHtml);
            updateDisplay();
        }

        // Remove item
        function removeItem(itemId) {
            const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
            if (itemElement) {
                itemElement.classList.add('removing');
                setTimeout(() => {
                    itemElement.remove();
                    updateDisplay();
                    calculateGrandTotal();
                }, 300);
            }
        }

        // Update item price when product is selected
        function updateItemPrice(itemId) {
            const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
            const productSelect = itemElement.querySelector('select[name="product_id"]');
            const priceInput = itemElement.querySelector('input[name="unit_price"]');
            
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            if (selectedOption && selectedOption.dataset.price) {
                priceInput.value = selectedOption.dataset.price;
                calculateItemTotal(itemId);
            }
        }

        // Calculate item total
        function calculateItemTotal(itemId) {
            const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
            const qty = parseFloat(itemElement.querySelector('input[name="qty"]').value) || 0;
            const unitPrice = parseFloat(itemElement.querySelector('input[name="unit_price"]').value) || 0;
            const discount = parseFloat(itemElement.querySelector('input[name="discount"]').value) || 0;
            
            const subtotal = qty * unitPrice;
            const total = subtotal - discount;
            
            itemElement.querySelector('.item-total').textContent = total.toFixed(2);
            calculateGrandTotal();
        }

        // Calculate grand total
        function calculateGrandTotal() {
            const itemTotals = document.querySelectorAll('.item-total');
            let grandTotal = 0;
            
            itemTotals.forEach(totalElement => {
                grandTotal += parseFloat(totalElement.textContent) || 0;
            });
            
            const currency = document.getElementById('currency').value;
            document.getElementById('grandTotal').textContent = `${grandTotal.toFixed(2)} ${currency}`;
        }

        // Update display based on items count
        function updateDisplay() {
            const itemsContainer = document.getElementById('itemsContainer');
            const noItemsMessage = document.getElementById('noItemsMessage');
            const totalSection = document.getElementById('totalSection');
            
            const hasItems = itemsContainer.children.length > 0;
            
            noItemsMessage.classList.toggle('hidden', hasItems);
            totalSection.classList.toggle('hidden', !hasItems);
            
            if (hasItems) {
                calculateGrandTotal();
            }
        }

        // Handle currency change
        document.getElementById('currency').addEventListener('change', calculateGrandTotal);

        // Handle form submission
        document.getElementById('quoteForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');
            
            // Disable submit button
            submitBtn.disabled = true;
            submitText.textContent = 'Oluşturuluyor...';
            submitSpinner.classList.remove('hidden');
            
            try {
                // Collect form data
                const formData = new FormData(this);
                const quoteData = {
                    customer_id: parseInt(formData.get('customer_id')),
                    currency: formData.get('currency'),
                    validity_date: formData.get('validity_date') || null,
                    notes: formData.get('notes') || null,
                    items: []
                };
                
                // Collect items data
                const itemElements = document.querySelectorAll('.item-row');
                itemElements.forEach(itemElement => {
                    const productId = parseInt(itemElement.querySelector('select[name="product_id"]').value);
                    const qty = parseFloat(itemElement.querySelector('input[name="qty"]').value);
                    const unitPrice = parseFloat(itemElement.querySelector('input[name="unit_price"]').value);
                    const discount = parseFloat(itemElement.querySelector('input[name="discount"]').value) || 0;
                    
                    if (productId && qty && unitPrice) {
                        quoteData.items.push({
                            product_id: productId,
                            qty: qty,
                            unit_price: unitPrice,
                            discount: discount,
                            meta_params: null
                        });
                    }
                });
                
                // Validate
                if (!quoteData.customer_id) {
                    throw new Error('Müşteri seçimi zorunludur');
                }
                
                if (quoteData.items.length === 0) {
                    throw new Error('En az bir teklif kalemi eklemelisiniz');
                }
                
                // Simulate API call
                console.log('Sending quote data:', quoteData);
                
                // Simulate API response
                await new Promise(resolve => setTimeout(resolve, 2000));
                
                // Show success message
                showMessage('Teklif başarıyla oluşturuldu!', 'success');
                
                // Reset form after success
                setTimeout(() => {
                    if (confirm('Yeni teklif oluşturmak ister misiniz?')) {
                        location.reload();
                    } else {
                        goBack();
                    }
                }, 2000);
                
            } catch (error) {
                console.error('Quote creation error:', error);
                showMessage(error.message || 'Teklif oluşturulurken bir hata oluştu', 'error');
            } finally {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitText.textContent = 'Teklif Oluştur';
                submitSpinner.classList.add('hidden');
            }
        });

        // Utility functions
        function showMessage(message, type) {
            const existingMessages = document.querySelectorAll('.message-toast');
            existingMessages.forEach(msg => msg.remove());

            const messageDiv = document.createElement('div');
            messageDiv.className = `message-toast fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white success-message' : 'bg-red-500 text-white'
            }`;
            messageDiv.textContent = message;
            
            document.body.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }

        function goBack() {
            if (confirm('Değişiklikler kaydedilmeyecek. Çıkmak istediğinizden emin misiniz?')) {
                alert('Teklif listesi sayfasına yönlendirilecek');
            }
        }

        // Initialize page when loaded
        window.addEventListener('load', initializePage);
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'976b4ad394061b42',t:'MTc1NjQ2MjU3OS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
