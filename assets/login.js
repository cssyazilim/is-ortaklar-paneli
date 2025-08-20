// assets/login.js
// Giriş ve kayıt sayfası için JavaScript
const BASE_PATH = document.body?.dataset?.base || '/is-ortaklar-paneli/';
const API_PATH  = document.body?.dataset?.api  || (BASE_PATH + 'api/');

const ENDPOINTS = {
  company:    API_PATH + 'register-proxy.php?target=company',
  individual: API_PATH + 'register-proxy.php?target=individual'
};


let selectedUserType = 'admin';
let selectedRegisterType = 'sirket';

// Tabs
const loginTab = document.getElementById('loginTab');
const registerTab = document.getElementById('registerTab');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');

function showLoginForm() {
  loginTab.classList.add('bg-white','shadow-sm','text-blue-600');
  loginTab.classList.remove('text-gray-600');
  registerTab.classList.remove('bg-white','shadow-sm','text-blue-600');
  registerTab.classList.add('text-gray-600');
  loginForm.classList.remove('hidden');
  registerForm.classList.add('hidden');
    // >>> login tabında scroll kapat
  document.body.classList.add('no-scroll');
  document.documentElement.classList.add('no-scroll');
}

function showRegisterForm() {
  registerForm.classList.remove('hidden');
  loginForm.classList.add('hidden');
  registerTab.classList.add('bg-white','shadow-sm','text-blue-600');
  registerTab.classList.remove('text-gray-600');
  loginTab.classList.remove('bg-white','shadow-sm','text-blue-600');
  loginTab.classList.add('text-gray-600');
  // >>> kayıt tabında scroll aç
  document.body.classList.remove('no-scroll');
  document.documentElement.classList.remove('no-scroll');
}

document.addEventListener('DOMContentLoaded', () => {
  document.body.classList.add('no-scroll');
});


loginTab?.addEventListener('click', showLoginForm);
registerTab?.addEventListener('click', showRegisterForm);


// Kullanıcı tipi (giriş)
document.querySelectorAll('.user-type-btn').forEach(btn => {
  btn.addEventListener('click', function (e) {
    e.preventDefault();
    selectedUserType = this.dataset.type;
    const hidden = document.getElementById('userTypeInput');
    if (hidden) hidden.value = selectedUserType;

    document.querySelectorAll('.user-type-btn').forEach(b => {
      b.classList.remove('bg-blue-50','text-blue-600','border-blue-200');
      b.classList.add('bg-gray-50','text-gray-600','border-gray-200');
    });
    this.classList.add('bg-blue-50','text-blue-600','border-blue-200');
    this.classList.remove('bg-gray-50','text-gray-600','border-gray-200');
  });
});

// Kayıt tipi
document.querySelectorAll('.register-type-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    selectedRegisterType = this.dataset.type;
    document.querySelectorAll('.register-type-btn').forEach(b => {
      b.classList.remove('bg-blue-50','text-blue-600','border-blue-200');
      b.classList.add('bg-gray-50','text-gray-600','border-gray-200');
    });
    this.classList.add('bg-blue-50','text-blue-600','border-blue-200');
    this.classList.remove('bg-gray-50','text-gray-600','border-gray-200');

    if (selectedRegisterType === 'sirket') {
      document.getElementById('companyFields')?.classList.remove('hidden');
      document.getElementById('personalFields')?.classList.add('hidden');
    } else {
      document.getElementById('companyFields')?.classList.add('hidden');
      document.getElementById('personalFields')?.classList.remove('hidden');
    }
  });
});

// Şifre görünürlüğü
function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  if (!input) return;
  const button = input.nextElementSibling;
  if (!button) return;

  const eyeOpen   = button.querySelector('.eye-open');
  const eyeClosed = button.querySelector('.eye-closed');

  const show = input.type === 'password';
  input.type = show ? 'text' : 'password';

  if (eyeOpen && eyeClosed) {
    if (show) { eyeOpen.classList.remove('hidden'); eyeClosed.classList.add('hidden'); }
    else { eyeOpen.classList.add('hidden'); eyeClosed.classList.remove('hidden'); }
  }
  button.setAttribute('aria-pressed', show ? 'true' : 'false');
}
window.togglePassword = togglePassword;

// MFA (opsiyonel)
function toggleMFA() {
  const mfaSection = document.getElementById('mfaSection');
  const checkbox = document.getElementById('enableMFA');
  if (!mfaSection || !checkbox) return;
  if (checkbox.checked) mfaSection.classList.remove('hidden'); else mfaSection.classList.add('hidden');
}
window.toggleMFA = toggleMFA;

// Hata yardımcıları
function clearError(fieldId) {
  const errorDiv = document.getElementById(fieldId + '-error');
  const inputField = document.getElementById(fieldId);
  if (errorDiv) errorDiv.classList.add('hidden');
  if (inputField) {
    inputField.classList.remove('border-red-500','bg-red-50');
    inputField.classList.add('border-gray-200','bg-gray-50');
  }
}
window.clearError = clearError;

function showError(fieldId, message='Bu alanın doldurulması zorunludur') {
  const errorDiv = document.getElementById(fieldId + '-error');
  const inputField = document.getElementById(fieldId);
  if (errorDiv) { errorDiv.textContent = message; errorDiv.classList.remove('hidden'); }
  if (inputField) {
    inputField.classList.add('border-red-500','bg-red-50');
    inputField.classList.remove('border-gray-200','bg-gray-50');
  }
}

// Login doğrulama
function validateLoginForm() {
  let isValid = true;
  const email = document.getElementById('loginEmail').value.trim();
  const password = document.getElementById('loginPassword').value;
  const mfaChecked = document.getElementById('enableMFA')?.checked;

  if (!email) { showError('loginEmail'); isValid = false; }
  if (!password) { showError('loginPassword'); isValid = false; }
  if (mfaChecked) {
    const mfaCode = document.getElementById('mfaCode')?.value?.trim();
    if (!mfaCode) { showError('mfaCode'); isValid = false; }
  }
  return isValid;
}
window.validateLoginForm = validateLoginForm;

// Kayıt yardımcıları
function v(id){ return (document.getElementById(id)?.value || '').trim(); }
function checked(id){ return !!document.getElementById(id)?.checked; }

function showRegisterError(msg){
  const box = document.getElementById('registerError');
  if (!box) { alert(msg); return; }
  box.innerHTML = msg;
  box.classList.remove('hidden');
}
function clearRegisterError(){
  const box = document.getElementById('registerError');
  if (box) box.classList.add('hidden');
}

async function postJSON(url, data) {
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify(data)
  });

  let raw = '';
  try { raw = await res.text(); } catch (_) {}
  let body = null;
  if (raw) {
    try { body = JSON.parse(raw); } catch (_) { body = { message: raw }; }
  }

  if (!res.ok) {
    const fieldErrors = body && body.errors;
    const firstFieldError = fieldErrors ? Object.values(fieldErrors).flat()[0] : null;
    const msg = (body && (body.message || body.error)) || firstFieldError || `İstek başarısız (${res.status})`;
    const err = new Error(msg);
    err.status = res.status;
    err.body = body;
    throw err;
  }
  return body;
}

function applyFieldErrors(errors){
  if (!errors) return;
  if (errors.company_title) showError('companyName', errors.company_title[0] || 'Hatalı değer');
  if (errors.name)          showError('companyName', errors.name[0] || 'Hatalı değer');

  if (errors.tax_no) {
    if (selectedRegisterType === 'sirket') showError('taxNumber', errors.tax_no[0] || 'Hatalı değer');
    else                                   showError('tcNumber',  errors.tax_no[0] || 'Hatalı değer');
  }
  if (errors.person_first_name) showError('firstName', errors.person_first_name[0] || 'Hatalı değer');
  if (errors.person_last_name)  showError('lastName',  errors.person_last_name[0]  || 'Hatalı değer');

  if (errors.email)            showError('registerEmail', errors.email[0] || 'Hatalı e-posta');
  if (errors.phone)            showError('phone', errors.phone[0] || 'Hatalı telefon');
  if (errors.password)         showError('registerPassword', errors.password[0] || 'Hatalı şifre');
  if (errors.password_confirm) showError('confirmPassword', errors.password_confirm[0] || 'Eşleşmiyor');
  if (errors.kvkk_accepted)    showError('kvkkCheck', errors.kvkk_accepted[0] || 'Zorunlu');
}

function summarizeErrors(errors){
  if (!errors) return '';
  const lines = [];
  for (const [k,v] of Object.entries(errors)) {
    const text = Array.isArray(v) ? v.join(', ') : String(v);
    lines.push(`• <b>${k}</b>: ${text}`);
  }
  return lines.join('<br>');
}

function buildRegisterPayload() {
  if (selectedRegisterType === 'sirket') {
    const company = v('companyName');
    return {
      company_title: company,
      name: company,
      tax_no: v('taxNumber').replace(/\D/g,''),
      email: v('registerEmail'),
      phone: v('phone').replace(/\s/g,''),
      password: v('registerPassword'),
      password_confirm: v('confirmPassword'),
      kvkk_accepted: checked('kvkkCheck')
    };
  } else {
    return {
      person_first_name: v('firstName'),
      person_last_name:  v('lastName'),
      tax_no: v('tcNumber').replace(/\D/g,''),
      email: v('registerEmail'),
      phone: v('phone').replace(/\s/g,''),
      password: v('registerPassword'),
      password_confirm: v('confirmPassword'),
      kvkk_accepted: checked('kvkkCheck')
    };
  }
}

async function handleRegister() {
  clearRegisterError();
  if (!validateRegisterForm()) return;

  const btn = document.getElementById('registerBtn');
  if (btn) { btn.disabled = true; btn.textContent = 'Gönderiliyor...'; }

  try {
    const type = (selectedRegisterType === 'sirket') ? 'company' : 'individual';
    const url  = ENDPOINTS[type];
    const payload = buildRegisterPayload();

    await postJSON(url, payload); // 201
    showWaitingScreen();
  } catch (err) {
    let msg = err.message || 'Kayıt sırasında bir hata oluştu.';
    if (err.body?.errors) {
      applyFieldErrors(err.body.errors);
      msg += '<br>' + summarizeErrors(err.body.errors);
    }
    if (err.body?.error && !err.body?.errors) {
      msg = err.body.error;
    }
    showRegisterError(msg);
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = 'Kayıt Ol'; }
  }
}
window.handleRegister = handleRegister;

// Modal/helperlar
function showForgotPassword() {
  const email = prompt('Şifre sıfırlama bağlantısının gönderileceği e-posta adresinizi girin:');
  if (email) alert(`Şifre sıfırlama bağlantısı ${email} adresine gönderildi! (Demo)`);
}
window.showForgotPassword = showForgotPassword;

function showKVKK() {
  document.getElementById('modalTitle').textContent = 'KVKK Aydınlatma Metni';
  document.getElementById('modalContent').innerHTML = `
    <p class="mb-3"><strong>Kişisel Verilerin Korunması Hakkında Bilgilendirme</strong></p>
    <p class="mb-2">6698 sayılı KVKK uyarınca, kişisel verileriniz ...</p>`;
  const mo = document.getElementById('modalOverlay');
  mo.classList.remove('hidden'); mo.classList.add('flex');
}
window.showKVKK = showKVKK;

function showContract() {
  document.getElementById('modalTitle').textContent = 'Kullanım Sözleşmesi';
  document.getElementById('modalContent').innerHTML = `
    <p class="mb-3"><strong>Hizmet Kullanım Şartları</strong></p>
    <p class="mb-2">Bu sözleşme ...</p>`;
  const mo = document.getElementById('modalOverlay');
  mo.classList.remove('hidden'); mo.classList.add('flex');
}
window.showContract = showContract;

function showWaitingScreen() {
  document.getElementById('modalTitle').textContent = 'Kayıt Başarılı!';
  document.getElementById('modalContent').innerHTML = `<p>Kaydınız alınmıştır. Yönetici onayından sonra giriş yapabilirsiniz.</p>`;
  const modalOverlay = document.getElementById('modalOverlay');
  const closeButton = modalOverlay.querySelector('button');
  closeButton.textContent = 'Tamam';
  closeButton.onclick = function(){ closeModal(); showLoginForm(); clearAllForms(); };
  modalOverlay.classList.remove('hidden'); modalOverlay.classList.add('flex');
}
window.showWaitingScreen = showWaitingScreen;

function closeModal() {
  const mo = document.getElementById('modalOverlay');
  mo.classList.add('hidden'); mo.classList.remove('flex');
}
window.closeModal = closeModal;

function clearAllForms() {
  // Login
  const loginEmail = document.getElementById('loginEmail');
  const loginPassword = document.getElementById('loginPassword');
  if (loginEmail) loginEmail.value = '';
  if (loginPassword) loginPassword.value = '';
  const mfaCode = document.getElementById('mfaCode');
  if (mfaCode) mfaCode.value = '';

  // Register
  document.querySelectorAll('#registerForm input[type="text"], #registerForm input[type="email"], #registerForm input[type="tel"], #registerForm input[type="password"]').forEach(i => i.value = '');
  document.querySelectorAll('#registerForm input[type="checkbox"]').forEach(c => c.checked = false);

  document.querySelectorAll('[id$="-error"]').forEach(d => d.classList.add('hidden'));
  document.querySelectorAll('input').forEach(i => {
    i.classList.remove('border-red-500','bg-red-50');
    i.classList.add('border-gray-200','bg-gray-50');
  });

  clearRegisterError();
}
window.clearAllForms = clearAllForms;

// Kayıt doğrulama
function validateRegisterForm() {
  let isValid = true;

  if (selectedRegisterType === 'sirket') {
    const companyName = v('companyName');
    const taxNumber = v('taxNumber');
    if (!companyName) { showError('companyName'); isValid = false; }
    if (!taxNumber) { showError('taxNumber'); isValid = false; }
    else if (taxNumber.length !== 10 || !/^\d+$/.test(taxNumber)) { showError('taxNumber','Vergi numarası 10 haneli olmalıdır'); isValid = false; }
  } else {
    const firstName = v('firstName');
    const lastName  = v('lastName');
    const tcNumber  = v('tcNumber');
    if (!firstName) { showError('firstName'); isValid = false; }
    if (!lastName)  { showError('lastName');  isValid = false; }
    if (!tcNumber)  { showError('tcNumber');  isValid = false; }
    else if (tcNumber.length !== 11 || !/^\d+$/.test(tcNumber)) { showError('tcNumber','TC Kimlik No 11 haneli olmalıdır'); isValid = false; }
  }

  const email = v('registerEmail');
  const phone = v('phone');
  const password = v('registerPassword');
  const confirmPassword = v('confirmPassword');

  if (!email) { showError('registerEmail'); isValid = false; }
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showError('registerEmail','Geçerli bir e-posta adresi girin'); isValid = false; }

  if (!phone) { showError('phone'); isValid = false; }
  else if (!/^[\+]?[0-9\s\(\)\-]{10,}$/.test(phone)) { showError('phone','Geçerli bir telefon numarası girin'); isValid = false; }

  if (!password) { showError('registerPassword'); isValid = false; }
  else if (password.length < 6) { showError('registerPassword','Şifre en az 6 karakter olmalıdır'); isValid = false; }

  if (!confirmPassword) { showError('confirmPassword'); isValid = false; }
  if (password && confirmPassword && password !== confirmPassword) { showError('confirmPassword','Şifreler eşleşmiyor'); isValid = false; }

  const kvkkCheck = checked('kvkkCheck');
  const contractCheck = checked('contractCheck');
  if (!kvkkCheck) { showError('kvkkCheck','Bu onayın verilmesi zorunludur'); isValid = false; }
  if (!contractCheck) { showError('contractCheck','Bu onayın verilmesi zorunludur'); isValid = false; }

  return isValid;
}
window.validateRegisterForm = validateRegisterForm;
