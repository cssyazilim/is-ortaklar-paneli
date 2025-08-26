/* ------- küçük yardımcılar ------- */
window.openModal = function(title, html){
  const o = document.getElementById('modalOverlay');
  const t = document.getElementById('modalTitle');
  const c = document.getElementById('modalContent');
  if(!o||!t||!c){ alert((title?title+': ':'')+String(html||'').replace(/<[^>]+>/g,'')); return; }
  t.textContent = title || '';
  c.innerHTML   = html || '';
  o.classList.remove('hidden'); o.classList.add('flex');
};
window.closeModal = function(){
  const o = document.getElementById('modalOverlay');
  if(o){ o.classList.add('hidden'); o.classList.remove('flex'); }
};
window.escapeHtml = (s)=>String(s||'').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m]));
window.showError = function(id,msg){
  const el = document.getElementById(id+'-error');
  if(el){ if(msg) el.textContent = msg; el.classList.remove('hidden'); }
};
window.clearError = function(id){
  const el = document.getElementById(id+'-error');
  if(el){ el.classList.add('hidden'); }
};

/* ------- Tab switch (Giriş/Kayıt) ------- */
(function initTabs(){
  const loginTab = document.getElementById('loginTab');
  const registerTab = document.getElementById('registerTab');
  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');
  if(!loginTab || !registerTab || !loginForm || !registerForm) return;

  function activate(which){
    if(which==='login'){
      loginTab.classList.add('text-blue-600','bg-white','shadow-sm');
      registerTab.classList.remove('text-blue-600','bg-white','shadow-sm');
      loginForm.classList.remove('hidden');
      registerForm.classList.add('hidden');
    }else{
      registerTab.classList.add('text-blue-600','bg-white','shadow-sm');
      loginTab.classList.remove('text-blue-600','bg-white','shadow-sm');
      registerForm.classList.remove('hidden');
      loginForm.classList.add('hidden');
    }
  }
  loginTab.addEventListener('click', ()=>activate('login'));
  registerTab.addEventListener('click', ()=>activate('register'));
})();

/* ------- Şirket/Şahıs toggle ------- */
(function initRegisterTypeToggle(){
  const typeBtns = document.querySelectorAll('.register-type-btn');
  const companyFields  = document.getElementById('companyFields');
  const personalFields = document.getElementById('personalFields');

  function setActive(btn){
    typeBtns.forEach(b=>{
      b.classList.remove('bg-blue-50','text-blue-600','border-blue-200');
      b.classList.add('bg-gray-50','text-gray-600','border-gray-200');
    });
    btn.classList.add('bg-blue-50','text-blue-600','border-blue-200');
    btn.classList.remove('bg-gray-50','text-gray-600','border-gray-200');

    if (btn.dataset.type === 'sahis') {
      personalFields?.classList.remove('hidden');
      companyFields?.classList.add('hidden');
    } else {
      companyFields?.classList.remove('hidden');
      personalFields?.classList.add('hidden');
    }
  }

  const initiallyActive = Array.from(typeBtns).find(b => b.classList.contains('bg-blue-50')) || typeBtns[0];
  if (initiallyActive) setActive(initiallyActive);
  typeBtns.forEach(btn=> btn.addEventListener('click', ()=> setActive(btn)));
})();

/* ------- Giriş formu doğrulama ------- */
window.validateLoginForm = function(){
  const email = document.getElementById('loginEmail').value.trim();
  const pass  = document.getElementById('loginPassword').value;
  let ok = true;
  if(!email){ showError('loginEmail'); ok=false; }
  if(!pass){ showError('loginPassword'); ok=false; }
  return ok;
};

/* ------- Şifre göz butonu ------- */
window.togglePassword = function(inputId){
  const input = document.getElementById(inputId);
  if(!input) return;
  const btn = input.parentElement.querySelector('button[onclick^="togglePassword"]');
  const openIcon   = btn?.querySelector('.eye-open');
  const closedIcon = btn?.querySelector('.eye-closed');

  if (input.type === 'password') {
    input.type = 'text';
    openIcon && openIcon.classList.remove('hidden');
    closedIcon && closedIcon.classList.add('hidden');
  } else {
    input.type = 'password';
    openIcon && openIcon.classList.add('hidden');
    closedIcon && closedIcon.classList.remove('hidden');
  }
};

/* ------- KVKK / Sözleşme modalları ------- */
window.showKVKK = function(){
  openModal('KVKK Aydınlatma Metni', '<p>KVKK metni burada yer alacaktır.</p>');
};
window.showContract = function(){
  openModal('Kullanım Sözleşmesi', '<p>Kullanım sözleşmesi burada yer alacaktır.</p>');
};

/* ------- Kayıt gönderimi (JSON → bu dosyanın kendisi) ------- */
window.handleRegister = async function () {
  try {
    const activeTypeBtn = Array.from(document.querySelectorAll('.register-type-btn'))
      .find(b => b.classList.contains('bg-blue-50'));
    const regType   = activeTypeBtn?.dataset?.type || 'sirket';
    const legalType = regType === 'sahis' ? 'Sahis' : 'Sirket';
    const mfaMethod = document.getElementById('mfaSms')?.checked ? 'sms' : 'email';

    const email   = document.getElementById('registerEmail')?.value.trim() || '';
    const phone   = document.getElementById('phone')?.value.trim() || '';
    const address = document.getElementById('address')?.value.trim() || '';
    const pass1   = document.getElementById('registerPassword')?.value || '';
    const pass2   = document.getElementById('confirmPassword')?.value || '';

    let valid = true;
    if (!email){ showError('registerEmail'); valid=false; }
    if (!phone){ showError('phone'); valid=false; }
    if (!address){ showError('address'); valid=false; }
    if (!pass1){ showError('registerPassword'); valid=false; }
    if (!pass2){ showError('confirmPassword'); valid=false; }
    if (pass1 && pass2 && pass1 !== pass2){ showError('confirmPassword','Şifreler uyuşmuyor'); valid=false; }
    if (!document.getElementById('kvkkCheck')?.checked){ showError('kvkkCheck'); valid=false; }
    if (!document.getElementById('contractCheck')?.checked){ showError('contractCheck'); valid=false; }
    if (!valid) return;

    const payload = { type:'Bayi', legal_type:legalType, email, password:pass1, phone, address, mfa_method:mfaMethod };

    if (legalType === 'Sirket') {
      const company_name = document.getElementById('companyName')?.value.trim() || '';
      const vkn = (document.getElementById('taxNumber')?.value || '').replace(/\D/g,'');
      if (!company_name){ showError('companyName'); return; }
      if (!/^\d{10}$/.test(vkn)){ showError('taxNumber','10 haneli VKN girin'); return; }
      payload.company_name = company_name;
      payload.vkn = vkn;
    } else {
      const first_name = document.getElementById('firstName')?.value.trim() || '';
      const last_name  = document.getElementById('lastName')?.value.trim() || '';
      const tckn       = (document.getElementById('tcNumber')?.value || '').replace(/\D/g,'');
      if (!first_name){ showError('firstName'); return; }
      if (!last_name){ showError('lastName'); return; }
      if (!/^\d{11}$/.test(tckn)){ showError('tcNumber','11 haneli TCKN girin'); return; }
      payload.first_name = first_name;
      payload.last_name  = last_name;
      payload.tckn       = tckn;
    }

    const endpoint = window.location.pathname;
    const res = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type':'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await res.json().catch(()=> ({}));

    if (!res.ok || !data?.success) {
      openModal('Kayıt Hatası', `<p class="text-red-600">${escapeHtml(data?.message || `HTTP ${res.status}`)}</p>`);
      return;
    }

    if (data.next === 'verify' && data.redirect) {
      openModal('Doğrulama Gerekli', `
        <p>${escapeHtml(data.message || 'E-posta ile doğrulama kodu gönderildi.')}</p>
        ${data.dev_otp ? `<p class="mt-2 text-xs">DEV OTP: <b>${escapeHtml(String(data.dev_otp))}</b></p>` : ''}
        <p class="mt-3">Kod sayfasına yönlendiriliyorsunuz…</p>
      `);
      setTimeout(()=>{ location.href = data.redirect; }, 700);
      return;
    }

    openModal('Kayıt Başarılı', `<p>${escapeHtml(data.message || 'Kayıt oluşturuldu.')}</p><p class="mt-3">Devam etmek için giriş yapabilirsiniz.</p>`);
    const loginTab = document.getElementById('loginTab');
    if (loginTab) setTimeout(()=> loginTab.click(), 800);

  } catch (err) {
    openModal('Kayıt Hatası', `<p class="text-red-600">${escapeHtml(err?.message || String(err))}</p>`);
  }
};
