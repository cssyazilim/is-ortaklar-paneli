<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Doğrulama - Bayi Yönetim Sistemi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-shadow {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .success-message {
            animation: slideIn 0.3s ease-out;
        }
        .error-message {
            animation: shake 0.5s ease-in-out;
        }
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        @keyframes slideIn {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .code-input {
            transition: all 0.2s ease;
        }
        .code-input:focus {
            transform: scale(1.05);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }
        .success-checkmark {
            animation: checkmark 0.6s ease-in-out;
        }
        @keyframes checkmark {
            0% { transform: scale(0) rotate(45deg); }
            50% { transform: scale(1.2) rotate(45deg); }
            100% { transform: scale(1) rotate(45deg); }
        }
    </style>
</head>
<body class="min-h-screen gradient-bg flex items-center justify-center p-4">
    <!-- Ana Container -->
    <div class="w-full max-w-md">
        <!-- Logo ve Başlık -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-white rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">SMS Doğrulama</h1>
            <p class="text-blue-100">Telefonunuza gönderilen kodu girin</p>
        </div>

        <!-- Verification Card -->
        <div class="bg-white rounded-2xl p-8 card-shadow">
            <!-- Header -->
            <div class="text-center mb-8">
                <div id="phoneIcon" class="w-16 h-16 bg-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4 pulse-animation">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">SMS Kodunu Girin</h2>
                <p class="text-gray-600 mb-4">6 haneli doğrulama kodunu girin</p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <p class="text-sm text-blue-800">
                        <span class="font-medium">+90 532 *** **67</span> numarasına SMS gönderildi
                    </p>
                </div>
            </div>

            <!-- Verification Form -->
            <form id="verificationForm">
                <!-- Code Input -->
                <div class="mb-8">
                    <label class="block text-sm font-medium text-gray-700 mb-4 text-center">SMS Doğrulama Kodu</label>
                    <div class="flex justify-center space-x-3">
                        <input type="text" id="code1" maxlength="1" class="code-input w-12 h-12 text-center text-xl font-bold border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" oninput="moveToNext(this, 'code2')" onkeydown="handleBackspace(this, event)" onpaste="handlePaste(event)">
                        <input type="text" id="code2" maxlength="1" class="code-input w-12 h-12 text-center text-xl font-bold border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" oninput="moveToNext(this, 'code3')" onkeydown="handleBackspace(this, event)">
                        <input type="text" id="code3" maxlength="1" class="code-input w-12 h-12 text-center text-xl font-bold border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" oninput="moveToNext(this, 'code4')" onkeydown="handleBackspace(this, event)">
                        <input type="text" id="code4" maxlength="1" class="code-input w-12 h-12 text-center text-xl font-bold border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" oninput="moveToNext(this, 'code5')" onkeydown="handleBackspace(this, event)">
                        <input type="text" id="code5" maxlength="1" class="code-input w-12 h-12 text-center text-xl font-bold border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" oninput="moveToNext(this, 'code6')" onkeydown="handleBackspace(this, event)">
                        <input type="text" id="code6" maxlength="1" class="code-input w-12 h-12 text-center text-xl font-bold border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" oninput="checkComplete()" onkeydown="handleBackspace(this, event)">
                    </div>
                </div>

                <!-- Verify Button -->
                <button type="submit" id="verifyBtn" class="w-full bg-green-600 text-white py-3 px-6 rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors font-medium text-lg shadow-lg disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <span id="verifyBtnText">SMS Kodunu Doğrula</span>
                    <svg id="verifySpinner" class="hidden animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>

            <!-- Resend Section -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600 mb-3">SMS almadınız mı?</p>
                <button id="resendBtn" onclick="resendSMS()" class="text-blue-600 hover:text-blue-500 font-medium text-sm transition-colors disabled:text-gray-400 disabled:cursor-not-allowed">
                    <span id="resendText">Yeniden Gönder</span>
                    <span id="resendTimer" class="hidden">(<span id="countdown">60</span>s)</span>
                </button>
            </div>

            <!-- Help Section -->
            <div class="mt-8 bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm">
                        <p class="text-gray-700 font-medium mb-1">SMS gelmiyor mu?</p>
                        <ul class="text-gray-600 space-y-1">
                            <li>• Spam klasörünüzü kontrol edin</li>
                            <li>• Telefon numaranızın doğru olduğundan emin olun</li>
                            <li>• Ağ bağlantınızı kontrol edin</li>
                            <li>• 60 saniye sonra yeniden gönderebilirsiniz</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Back to Login -->
            <div class="mt-6 text-center border-t pt-6">
                <p class="text-sm text-gray-600">
                    Farklı telefon numarası mı kullanmak istiyorsunuz? 
                    <a href="#" class="text-blue-600 hover:text-blue-500 font-medium">Geri dön</a>
                </p>
            </div>
        </div>

        <!-- Security Info -->
        <div class="mt-6 text-center">
            <div class="flex items-center justify-center text-white text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <span>SMS kodunuz güvenli şekilde şifrelenmektedir</span>
            </div>
        </div>
    </div>

    <script>
        let resendTimer = 60;
        let timerInterval;

        // Form submission
        document.getElementById('verificationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            handleVerification();
        });

        // Move to next input
        function moveToNext(current, nextId) {
            if (current.value.length === 1) {
                const nextInput = document.getElementById(nextId);
                if (nextInput) {
                    nextInput.focus();
                }
                checkComplete();
            }
        }

        // Handle backspace
        function handleBackspace(current, event) {
            if (event.key === 'Backspace' && current.value === '') {
                const currentId = current.id;
                const currentNum = parseInt(currentId.replace('code', ''));
                if (currentNum > 1) {
                    const prevInput = document.getElementById(`code${currentNum - 1}`);
                    if (prevInput) {
                        prevInput.focus();
                    }
                }
            }
        }

        // Handle paste
        function handlePaste(event) {
            event.preventDefault();
            const paste = (event.clipboardData || window.clipboardData).getData('text');
            const digits = paste.replace(/\D/g, '').slice(0, 6);
            
            for (let i = 0; i < digits.length; i++) {
                const input = document.getElementById(`code${i + 1}`);
                if (input) {
                    input.value = digits[i];
                }
            }
            
            if (digits.length === 6) {
                document.getElementById('code6').focus();
                checkComplete();
            }
        }

        // Check if all inputs are filled
        function checkComplete() {
            const inputs = ['code1', 'code2', 'code3', 'code4', 'code5', 'code6'];
            const allFilled = inputs.every(id => document.getElementById(id).value.length === 1);
            
            const verifyBtn = document.getElementById('verifyBtn');
            verifyBtn.disabled = !allFilled;
            
            if (allFilled) {
                // Auto-submit after a short delay
                setTimeout(() => {
                    handleVerification();
                }, 500);
            }
        }

        // Handle verification
        async function handleVerification() {
            const code = getEnteredCode();
            
            if (code.length !== 6) {
                showError('Lütfen 6 haneli SMS kodunu tam olarak girin');
                return;
            }

            setVerifyLoading(true);

            try {
                // Simulate API call
                await new Promise(resolve => setTimeout(resolve, 2000));

                // Demo code check
                if (code === '123456') {
                    showSuccess();
                } else {
                    showError('SMS kodu hatalı. Lütfen tekrar deneyin.');
                    clearInputs();
                }
                
            } catch (error) {
                showError('Doğrulama sırasında bir hata oluştu. Lütfen tekrar deneyin.');
            } finally {
                setVerifyLoading(false);
            }
        }

        // Get entered code
        function getEnteredCode() {
            const inputs = ['code1', 'code2', 'code3', 'code4', 'code5', 'code6'];
            return inputs.map(id => document.getElementById(id).value).join('');
        }

        // Clear inputs
        function clearInputs() {
            const inputs = ['code1', 'code2', 'code3', 'code4', 'code5', 'code6'];
            inputs.forEach(id => {
                document.getElementById(id).value = '';
            });
            document.getElementById('code1').focus();
            document.getElementById('verifyBtn').disabled = true;
        }

        // Resend SMS
        async function resendSMS() {
            const resendBtn = document.getElementById('resendBtn');
            if (resendBtn.disabled) return;

            try {
                // Simulate API call
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                showMessage('SMS yeniden gönderildi!', 'success');
                startResendTimer();
                clearInputs();
                
            } catch (error) {
                showMessage('SMS gönderilirken hata oluştu. Lütfen tekrar deneyin.', 'error');
            }
        }

        // Start resend timer
        function startResendTimer() {
            const resendBtn = document.getElementById('resendBtn');
            const resendText = document.getElementById('resendText');
            const resendTimer = document.getElementById('resendTimer');
            const countdown = document.getElementById('countdown');
            
            resendBtn.disabled = true;
            resendText.classList.add('hidden');
            resendTimer.classList.remove('hidden');
            
            resendTimer = 60;
            countdown.textContent = resendTimer;
            
            timerInterval = setInterval(() => {
                resendTimer--;
                countdown.textContent = resendTimer;
                
                if (resendTimer <= 0) {
                    clearInterval(timerInterval);
                    resendBtn.disabled = false;
                    resendText.classList.remove('hidden');
                    resendTimer.classList.add('hidden');
                }
            }, 1000);
        }

        // Loading state
        function setVerifyLoading(loading) {
            const btn = document.getElementById('verifyBtn');
            const text = document.getElementById('verifyBtnText');
            const spinner = document.getElementById('verifySpinner');
            
            if (loading) {
                btn.disabled = true;
                text.textContent = 'Doğrulanıyor...';
                spinner.classList.remove('hidden');
            } else {
                text.textContent = 'SMS Kodunu Doğrula';
                spinner.classList.add('hidden');
                checkComplete(); // Re-enable if code is complete
            }
        }

        // Success animation
        function showSuccess() {
            const phoneIcon = document.getElementById('phoneIcon');
            phoneIcon.innerHTML = `
                <svg class="w-8 h-8 text-white success-checkmark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            `;
            phoneIcon.className = 'w-16 h-16 bg-green-500 rounded-2xl flex items-center justify-center mx-auto mb-4';
            
            showMessage('SMS doğrulama başarılı! Hesabınız aktifleştirildi.', 'success');
            
            setTimeout(() => {
                alert('Doğrulama başarılı! Ana sayfaya yönlendiriliyorsunuz...');
                // window.location.href = '/dashboard';
            }, 2000);
        }

        // Utility functions
        function showMessage(message, type) {
            const existingMessages = document.querySelectorAll('.message-toast');
            existingMessages.forEach(msg => msg.remove());

            const messageDiv = document.createElement('div');
            messageDiv.className = `message-toast fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white success-message' : 'bg-red-500 text-white error-message'
            }`;
            messageDiv.textContent = message;
            
            document.body.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }

        function showError(message) {
            showMessage(message, 'error');
        }

        // Initialize
        window.addEventListener('load', function() {
            document.getElementById('code1').focus();
            startResendTimer();
            
            // Console log for demo
            console.log('Demo SMS Code: 123456');
        });

        // Prevent non-numeric input
        document.querySelectorAll('.code-input').forEach(input => {
            input.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'974c9572c24eb0de',t:'MTc1NjE0MDU3Ny4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
