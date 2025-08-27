(function(){
  /* ========= helpers ========= */
  const Fallback = "—";
  const safe = v => (v==null || v==="") ? Fallback : v;
  const setText = (id,val)=>{ const el=document.getElementById(id); if(el) el.textContent=safe(val); };
  const initialsFrom = s => !s ? "--" : String(s).trim().split(/\s+/).slice(0,2).map(x=>x[0]?.toUpperCase()||"").join("") || "--";
  const LEGAL_TR = { Sirket: "Şirket", Sahis: "Şahıs" };

  function getToken(){
    const raw = (document.querySelector('meta[name="partner-token"]')?.content || '').trim();
    return raw && raw.startsWith('Bearer ') ? raw.slice(7) : raw;
  }
  function getMeUrl(){
    return document.querySelector('meta[name="partners-me-url"]').content;
  }
  function getUpdateUrl(){
    return document.querySelector('meta[name="partners-update-url"]')?.content
           || '/api/partners_update.php';
  }

  function setCompanyCardTitle(lt){
  const el = document.getElementById('company-card-title');
  if (!el) return;
  el.textContent = (lt === 'Sahis') ? 'Şahıs Bilgileri' : 'Şirket Bilgileri';
 }

  // 11 haneli TCKN doğrulayıcı
  function validateTCKN(v){
    v = String(v||'').replace(/\D/g,'');
    if (!/^[1-9]\d{10}$/.test(v)) return false;
    const d = v.split('').map(Number);
    const odd = d[0]+d[2]+d[4]+d[6]+d[8];
    const even = d[1]+d[3]+d[5]+d[7];
    const d10 = ((odd*7) - even) % 10;
    const d11 = (d.slice(0,10).reduce((a,b)=>a+b,0)) % 10;
    return d[9] === d10 && d[10] === d11;
  }

  // küçük rozet boyama
  function paintChipByStatus(el, s){
    if (!el) return;
    el.classList.remove(
      'bg-green-100','text-green-800',
      'bg-yellow-100','text-yellow-800',
      'bg-red-100','text-red-800',
      'bg-orange-100','text-orange-800'
    );
    const st = String(s||'').toLowerCase();
    if (st === 'active')      { el.classList.add('bg-green-100','text-green-800');   el.textContent = 'Onaylandı'; }
    else if (st === 'pending'){ el.classList.add('bg-yellow-100','text-yellow-800'); el.textContent = 'Onay Bekliyor'; }
    else if (st === 'inactive' || st === 'blocked' || st === 'rejected') {
                               el.classList.add('bg-red-100','text-red-800');        el.textContent = 'Onaylanmadı'; }
    else                      { el.classList.add('bg-orange-100','text-orange-800'); el.textContent = 'Bilinmiyor'; }
  }
  function applyGlobalFieldChips(status){
    document.querySelectorAll('.status-chip').forEach(el => paintChipByStatus(el, status));
  }

  // üst statü UI (kapak vs)
  function setStatusUI(status){
    const cover  = document.getElementById('cover-gradient');
    const badge  = document.getElementById('status-badge');
    const text   = document.getElementById('status_text');
    const card   = document.getElementById('status-card');
    const chip   = document.getElementById('status-chip');
    const desc   = document.getElementById('status-desc');
    const avatar = document.getElementById('profile-avatar');

    const rm  = (el, ...cls) => el && el.classList.remove(...cls);
    const add = (el, ...cls) => el && el.classList.add(...cls);

    if (cover){
      rm(cover, 'from-green-600','to-green-800','from-yellow-500','to-yellow-700','from-red-600','to-red-800','from-orange-600','to-red-600');
      add(cover, 'bg-gradient-to-r');
    }
    rm(badge,'bg-green-600','bg-yellow-500','bg-red-600','bg-orange-500','ring-2','ring-offset-2','ring-green-300','ring-yellow-300','ring-red-300','animate-pulse');
    rm(card, 'bg-green-50','bg-yellow-50','bg-red-50','bg-orange-50','border','border-green-200','border-yellow-200','border-red-200','border-orange-200');
    text && rm(text, 'text-green-700','text-yellow-700','text-red-700','text-orange-700');
    desc && rm(desc, 'text-green-600','text-yellow-600','text-red-600','text-orange-600');
    chip && rm(chip, 'bg-green-100','text-green-800','bg-yellow-100','text-yellow-800','bg-red-100','text-red-800','bg-orange-100','text-orange-800');
    if (avatar){
      avatar.style.backgroundImage = 'none';
      avatar.style.background = 'none';
      rm(avatar,'bg-green-600','bg-yellow-500','bg-red-600','bg-orange-500','ring-4','ring-green-300','ring-yellow-300','ring-red-300','ring-orange-300','ring-offset-2','ring-white','shadow-lg');
    }

    const s = String(status||'').toLowerCase();
    let label = '—';
    let help  = 'Hesabınız yönetici onayı beklemektedir. Onaylandıktan sonra tüm özelliklere erişebileceksiniz.';

    if (s === 'active'){
      label = 'Onaylandı'; help = 'Hesabınız onaylandı. Tüm özellikler aktif.';
      add(badge,'bg-green-600','ring-2','ring-offset-2','ring-green-300');
      add(card,'bg-green-50','border','border-green-200'); text && add(text,'text-green-700'); desc && add(desc,'text-green-600');
      chip && add(chip,'bg-green-100','text-green-800');
      add(cover,'from-green-600','to-green-800');
      add(avatar,'bg-green-600','ring-4','ring-green-300','ring-offset-2','ring-white','shadow-lg');
    } else if (s === 'pending'){
      label = 'Onay Bekliyor';
      add(badge,'bg-yellow-500','animate-pulse','ring-2','ring-offset-2','ring-yellow-300');
      add(card,'bg-yellow-50','border','border-yellow-200'); text && add(text,'text-yellow-700'); desc && add(desc,'text-yellow-600');
      chip && add(chip,'bg-yellow-100','text-yellow-800');
      add(cover,'from-yellow-500','to-yellow-700');
      add(avatar,'bg-yellow-500','ring-4','ring-yellow-300','ring-offset-2','ring-white','shadow-lg');
    } else if (s === 'inactive' || s === 'blocked' || s === 'rejected'){
      label = 'Onaylanmadı'; help = 'Hesabınız onaylanmadı. Lütfen destekle iletişime geçin.';
      add(badge,'bg-red-600','ring-2','ring-offset-2','ring-red-300');
      add(card,'bg-red-50','border','border-red-200'); text && add(text,'text-red-700'); desc && add(desc,'text-red-600');
      chip && add(chip,'bg-red-100','text-red-800');
      add(cover,'from-red-600','to-red-800');
      add(avatar,'bg-red-600','ring-4','ring-red-300','ring-offset-2','ring-white','shadow-lg');
    } else {
      label = String(status || 'Bilinmiyor');
      add(badge,'bg-orange-500');
      add(card,'bg-orange-50','border','border-orange-200'); text && add(text,'text-orange-700'); desc && add(desc,'text-orange-600');
      chip && add(chip,'bg-orange-100','text-orange-800');
      add(cover,'from-orange-600','to-red-600');
      add(avatar,'bg-orange-500','ring-4','ring-orange-300','ring-offset-2','ring-white','shadow-lg');
    }

    badge && (badge.textContent = label);
    text  && (text.textContent  = label);
    chip  && (chip.textContent  = label);
    desc  && (desc.textContent  = help);

    applyGlobalFieldChips(s);
  }

  /* ========= MFA cache ========= */
  let _profileCache = null; // { phone, email }
  function getSelectedMFAMethod(){ return document.querySelector('input[name="mfa_method"]:checked')?.value || 'sms'; }
  function updateMFAInput(force=false){
    const method = getSelectedMFAMethod();
    const inp = document.getElementById('mfa_target');
    const lab = document.getElementById('mfa_target_label');
    if (!inp || !lab) return;
    if (method === 'sms'){
      lab.textContent = 'Telefon';
      inp.type = 'tel'; inp.setAttribute('inputmode','tel'); inp.placeholder = '+90 5xx xxx xx xx';
      if (force || !inp.value) inp.value = _profileCache?.phone || '';
    } else {
      lab.textContent = 'E-Mail';
      inp.type = 'email'; inp.setAttribute('inputmode','email'); inp.placeholder = 'ornek@firma.com';
      if (force || !inp.value) inp.value = _profileCache?.email || '';
    }
  }

  /* ========= state ========= */
  let profileData = {};              // API verisi
  let editModes   = { profile:false };

  // API helper: PUT
  async function updateProfile(payload){
    const res = await fetch(getUpdateUrl(), {
      method: 'PUT',
      headers: {
        'Content-Type':'application/json',
        'Accept':'application/json',
        ...(getToken() ? { Authorization: 'Bearer ' + getToken() } : {})
      },
      body: JSON.stringify(payload)
    });
    const data = await res.json().catch(()=> ({}));
    if (!res.ok){
      const msg = data?.detail || data?.message || data?.error || ('Güncelleme başarısız ('+res.status+')');
      throw new Error(msg);
    }
    return data;
  }

  function formatDateTR(value, tz) {
    if (!value) return "—";
    const opts = { year:'numeric', month:'long', day:'2-digit', hour:'2-digit', minute:'2-digit', hour12:false };
    if (tz) opts.timeZone = tz;
    return new Intl.DateTimeFormat('tr-TR', opts).format(new Date(value));
  }

  /* ========= render (READ-ONLY view) ========= */
function renderCompanyContent(){
  const c = document.getElementById('company-content');
  if (!c) return;

  const lt = profileData.legal_type || 'Sirket';   // 'Sirket' | 'Sahis'
  const isSahis = (lt === 'Sahis');
  const LEGAL_TR = { Sirket: 'Şirket', Sahis: 'Şahıs' };

  c.innerHTML = `
    <!-- Şirket Adı -->
    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
      <div class="flex items-center">
        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h2M7 7h10M7 11h10M7 15h10"></path>
          </svg>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-900">Şirket Adı</p>
          <p id="company_name" class="text-gray-600">${safe(profileData.company_name)}</p>
        </div>
      </div>
      <span class="status-chip inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">—</span>
    </div>

    ${!isSahis ? `
      <!-- VKN (yalnız Şirket) -->
      <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-900">Vergi Kimlik No</p>
            <p id="vkn" class="text-gray-600">${safe(profileData.vkn)}</p>
          </div>
        </div>
        <span class="status-chip inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">—</span>
      </div>
    ` : ''}

    <!-- Hukuki Yapı -->
    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
      <div class="flex items-center">
        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4">
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
          </svg>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-900">Hukuki Yapı</p>
          <p id="legal_type" class="text-gray-600">${safe(LEGAL_TR[lt] || lt)}</p>
        </div>
      </div>
      <span class="status-chip inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">—</span>
    </div>

    <!-- Vergi Dairesi -->
    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
      <div class="flex items-center">
        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
          <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 11h14M7 15h10"></path>
          </svg>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-900">Vergi Dairesi</p>
          <p id="tax_office" class="text-gray-600">${safe(profileData.tax_office)}</p>
        </div>
      </div>
      <span class="status-chip inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">—</span>
    </div>

    ${isSahis ? `
      <!-- T.C. Kimlik No -->
      <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-4">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4 7h16v10H4z"></path>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-900">T.C. Kimlik No</p>
            <p id="tckn" class="text-gray-600">${safe(profileData.tckn)}</p>
          </div>
        </div>
        <span class="status-chip inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">—</span>
      </div>
    ` : ''}

    <!-- İletişim Kişisi (BURAYA TAŞINDI) -->
    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
      <div class="flex items-center">
        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
          <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
          </svg>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-900">İletişim Kişisi</p>
          <p class="text-gray-600" id="contact-name">${safe(profileData.contact_name)}</p>
        </div>
      </div>
      <span class="status-chip inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">—</span>
    </div>
  `;

  applyGlobalFieldChips(profileData.status);
}



  function renderContactContent(){
  const c = document.getElementById('contact-content');
  if (!c) return;

  const lt = profileData.legal_type || 'Sirket';
  const isSahis = (lt === 'Sahis');

  c.innerHTML = `
    <div class="space-y-4">
      <!-- Email -->
      <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-900">E-posta Adresi</p>
            <p id="email" class="text-gray-600">${safe(profileData.email)}</p>
          </div>
        </div>
        <span class="status-chip inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">—</span>
      </div>

      <!-- Phone -->
      <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-900">Telefon Numarası</p>
            <p id="phone" class="text-gray-600">${safe(profileData.phone)}</p>
          </div>
        </div>
        <span class="status-chip inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">—</span>
      </div>

      ${isSahis ? `
      <!-- Ad (BURAYA TAŞINDI) -->
      <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-900">Ad</p>
            <p id="first_name" class="text-gray-600">${safe(profileData.first_name)}</p>
          </div>
        </div>
        <span class="status-chip inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">—</span>
      </div>

      <!-- Soyad (BURAYA TAŞINDI) -->
      <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-900">Soyad</p>
            <p id="last_name" class="text-gray-600">${safe(profileData.last_name)}</p>
          </div>
        </div>
        <span class="status-chip inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">—</span>
      </div>
      ` : ''}

      <!-- Address -->
      <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-900">Adres</p>
            <p id="address" class="text-gray-600">${safe(profileData.address)}</p>
          </div>
        </div>
        <span class="status-chip inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">—</span>
      </div>
    </div>
  `;

  applyGlobalFieldChips(profileData.status);
}


  /* ========= edit mode ========= */
  window.editCompany = function(){ editProfile(); }
  window.editContact = function(){ editProfile(); }

  function editProfile(){
  if (editModes.profile) return;
  editModes.profile = true;

  const companyCard = document.getElementById('company-card');
  const contactCard = document.getElementById('contact-card');
  const companyContent = document.getElementById('company-content');
  const contactContent = document.getElementById('contact-content');
  const companyEditBtn = document.getElementById('edit-company-btn');
  const contactEditBtn = document.getElementById('edit-contact-btn');

  companyCard.classList.add('edit-mode');
  contactCard.classList.add('edit-mode');
  companyEditBtn.style.display = 'none';
  contactEditBtn.style.display = 'none';

  const lt = profileData.legal_type || 'Sirket';
  const isSahis = (lt === 'Sahis');

  // === Şirket formu ===
  companyContent.innerHTML = `
    <div class="space-y-4">
      <div class="p-4 bg-white rounded-lg border-2 border-blue-200">
        <label class="block text-sm font-medium text-gray-900 mb-2">Şirket Adı</label>
        <input type="text" id="edit-company-name" value="${safe(profileData.company_name)}" class="edit-input">
      </div>

      ${!isSahis ? `
      <div class="p-4 bg-white rounded-lg border-2 border-blue-200">
        <label class="block text-sm font-medium text-gray-900 mb-2">Vergi Kimlik No</label>
        <input type="text" id="edit-vkn" value="${safe(profileData.vkn)}" class="edit-input" maxlength="10" inputmode="numeric">
      </div>
      ` : ''}

      <!-- Hukuki Yapı (salt-okunur rozet) -->
      <div class="p-4 bg-white rounded-lg border-2 border-blue-200">
        <label class="block text-sm font-medium text-gray-900 mb-2">Hukuki Yapı</label>
        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
          ${safe(LEGAL_TR[lt] || lt)}
        </div>
      </div>

      <div class="p-4 bg-white rounded-lg border-2 border-blue-200">
        <label class="block text-sm font-medium text-gray-900 mb-2">Vergi Dairesi</label>
        <input type="text" id="edit-tax-office" value="${safe(profileData.tax_office)}" class="edit-input" maxlength="100" placeholder="Vergi dairesi adını giriniz">
      </div>

      ${isSahis ? `
      <div id="sahis-wrapper" class="space-y-4">
        <div class="p-4 bg-white rounded-lg border-2 border-blue-200">
          <label class="block text-sm font-medium text-gray-900 mb-2">T.C. Kimlik No</label>
          <input type="text" id="edit-tckn" value="${safe(profileData.tckn)}" class="edit-input" maxlength="11" inputmode="numeric" placeholder="11 haneli TCKN">
          <p class="text-xs text-gray-500 mt-1">Şahıs hesabı için zorunludur.</p>
        </div>
      </div>
      ` : ``}

      <!-- İletişim Kişisi (BURAYA TAŞINDI) -->
      <div class="p-4 bg-white rounded-lg border-2 border-blue-200">
        <label class="block text-sm font-medium text-gray-900 mb-2">İletişim Kişisi</label>
        <input type="text" id="edit-contact-name" value="${safe(profileData.contact_name)}" class="edit-input" placeholder="İletişim kişisi adı">
      </div>
    </div>
  `;

  // === İletişim formu ===
  contactContent.innerHTML = `
    <div class="space-y-4">
      <div class="p-4 bg-white rounded-lg border-2 border-green-200">
        <label class="block text-sm font-medium text-gray-900 mb-2">E-posta Adresi</label>
        <input type="email" id="edit-email" value="${safe(profileData.email)}" class="edit-input">
      </div>

      <div class="p-4 bg-white rounded-lg border-2 border-green-200">
        <label class="block text-sm font-medium text-gray-900 mb-2">Telefon Numarası</label>
        <input type="tel" id="edit-phone" value="${safe(profileData.phone)}" class="edit-input" maxlength="11">
      </div>

      ${isSahis ? `
      <div class="p-4 bg-white rounded-lg border-2 border-green-200">
        <label class="block text-sm font-medium text-gray-900 mb-2">Ad</label>
        <input type="text" id="edit-first-name" value="${safe(profileData.first_name)}" class="edit-input" placeholder="Adınız">
      </div>

      <div class="p-4 bg-white rounded-lg border-2 border-green-200">
        <label class="block text-sm font-medium text-gray-900 mb-2">Soyad</label>
        <input type="text" id="edit-last-name" value="${safe(profileData.last_name)}" class="edit-input" placeholder="Soyadınız">
      </div>
      ` : ``}

      <div class="p-4 bg-white rounded-lg border-2 border-green-200">
        <label class="block text-sm font-medium text-gray-900 mb-2">Adres </label>
       <textarea
        id="edit-address"
        class="edit-input resize-none overflow-hidden leading-6 min-h-[44px] h-[44px]"
        rows="1" maxlength="300"
        placeholder="Detaylı adres bilgisi giriniz"
        >${safe(profileData.address)}</textarea>
        <div class="text-xs text-gray-500 mt-1">Maksimum 300 karakter.</div>
      </div>

      <div class="edit-buttons">
        <button class="btn-save" onclick="saveProfile()">
          <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          Tüm Bilgileri Kaydet
        </button>
        <button class="btn-cancel" onclick="cancelProfileEdit()">
          <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          İptal
        </button>
      </div>
    </div>
  `;

  bindAddressCounter();
}


  /* ========= save ========= */
window.saveProfile = async function () {
  const newCompanyName = document.getElementById('edit-company-name')?.value.trim() || '';
  const newVkn         = document.getElementById('edit-vkn')?.value.trim() || '';
  const newTaxOffice   = document.getElementById('edit-tax-office')?.value.trim() || '';
  const newEmail       = document.getElementById('edit-email')?.value.trim() || '';
  const newPhone       = (document.getElementById('edit-phone')?.value || '').replace(/\D/g,'');
  const newAddress     = document.getElementById('edit-address')?.value.trim() || '';
  const newContactName = document.getElementById('edit-contact-name')?.value.trim() || '';
  const newFirstName   = document.getElementById('edit-first-name')?.value?.trim() || '';
  const newLastName    = document.getElementById('edit-last-name')?.value?.trim() || '';
  const tcknInputEl    = document.getElementById('edit-tckn');
  const candidateTckn  = tcknInputEl ? (tcknInputEl.value || '').trim() : '';

  const lt      = profileData.legal_type || 'Sirket';
  const isSahis = (lt === 'Sahis');

  // ---- Validasyonlar (tip bazlı) ----
  if (!newEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newEmail)) {
    showMessage('Geçerli bir e-posta giriniz!', 'error'); return;
  }
  if (!/^\d{11}$/.test(newPhone)) {
    showMessage('Telefon 11 haneli olmalıdır!', 'error'); return;
  }
  if (newAddress.length > 300) {
    showMessage('Adres en fazla 300 karakter olabilir!', 'error'); return;
  }

  if (isSahis) {
    if (!newFirstName || !newLastName) { showMessage('Ad ve Soyad zorunludur!', 'error'); return; }
    if (!/^\d{11}$/.test(candidateTckn) || !validateTCKN(candidateTckn)) {
      showMessage('Geçerli bir 11 haneli TCKN giriniz.', 'error'); return;
    }
  } else {
    if (!newCompanyName) { showMessage('Şirket için "Şirket Adı" zorunludur!', 'error'); return; }
    if (!newVkn || newVkn.length !== 10 || !/^\d+$/.test(newVkn)) {
      showMessage('Şirket için VKN 10 haneli sayı olmalıdır!', 'error'); return;
    }
  }

  // ---- Tip'e göre AYRI PUT gövdesi ----
  let payload;
  if (isSahis) {
    payload = {
      legal_type: 'Sahis',
      email: newEmail,
      phone: newPhone,
      address: newAddress || null,
      contact_name: newContactName || null,
      tax_office: newTaxOffice || null,
      // şahıs zorunluları
      first_name: newFirstName,
      last_name: newLastName,
      tckn: candidateTckn,
      // şirket alanları kesin NULL
      company_name: null,
      vkn: null
    };
  } else {
    payload = {
      legal_type: 'Sirket',
      // şirket zorunluları
      company_name: newCompanyName,
      vkn: newVkn,
      // ortak opsiyoneller
      tax_office: newTaxOffice || null,
      email: newEmail,
      phone: newPhone,
      address: newAddress || null,
      contact_name: newContactName || null,
      // şahıs alanları kesin NULL
      first_name: null,
      last_name: null,
      tckn: null
    };
  }

  const btn = document.querySelector('.btn-save');
  const prevDisabled = btn?.disabled;
  if (btn) { btn.disabled = true; btn.classList.add('opacity-50','cursor-not-allowed'); }

  try {
    console.log('[PUT] /partners/me payload:', payload);
    const updated = await updateProfile(payload); // updateProfile 'PUT' gönderiyor
    _profileCache = { phone: updated?.phone || newPhone, email: updated?.email || newEmail };
    showMessage('Tüm profil bilgileri başarıyla güncellendi!', 'success');
    await loadPartnerProfile();
    cancelProfileEdit();
  } catch (err) {
    showMessage(err.message || 'Güncelleme hatası', 'error');
  } finally {
    if (btn) { btn.disabled = !!prevDisabled; btn.classList.remove('opacity-50','cursor-not-allowed'); }
  }
};



  function bindAddressCounter(){
  const ta = document.getElementById('edit-address');
  const counter = document.getElementById('addressCounter');
  const saveBtn = document.querySelector('.btn-save');
  if (!ta || !counter) return;

  // >>> EKLENDİ: otomatik yükseklik
  const autoGrow = (el) => {
    el.style.height = 'auto';
    // 240px tavan; istersen artır/azalt
    el.style.height = Math.min(el.scrollHeight, 240) + 'px';
  };

  const update = () => {
    const len = ta.value.length;
    counter.textContent = `${len}/300`;
    autoGrow(ta); // <<< büyüt

    if (len > 300) {
      saveBtn?.setAttribute('disabled','');
      saveBtn?.classList.add('opacity-50','cursor-not-allowed');
    } else {
      saveBtn?.removeAttribute('disabled');
      saveBtn?.classList.remove('opacity-50','cursor-not-allowed');
    }
  };

  ta.addEventListener('input', update);
  // ilk render’da da eşitle
  update();
}


  /* ========= misc ========= */
  window.cancelProfileEdit = function(){
    editModes.profile = false;
    const companyCard = document.getElementById('company-card');
    const contactCard = document.getElementById('contact-card');
    const companyEditBtn = document.getElementById('edit-company-btn');
    const contactEditBtn = document.getElementById('edit-contact-btn');
    companyCard.classList.remove('edit-mode');
    contactCard.classList.remove('edit-mode');
    companyEditBtn.style.display = 'block';
    contactEditBtn.style.display = 'block';
    renderCompanyContent();
    renderContactContent();
  }

  function showMessage(message, type){
    document.querySelectorAll('.message-toast').forEach(x=>x.remove());
    const messageDiv = document.createElement('div');
    messageDiv.className = `message-toast fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${type==='success' ? 'bg-green-500 text-white success-message':'bg-red-500 text-white'}`;
    messageDiv.textContent = message;
    document.body.appendChild(messageDiv);
    setTimeout(()=> messageDiv.remove(), 3000);
  }

  window.manageSecurity = function(){ showMessage('Güvenlik ayarları sayfasına yönlendiriliyorsunuz...', 'success'); }
    window.logout = function(){
    // Çıkış mesajı göstermek istersen önce kısa bir toast atabilirsin
    // showMessage('Çıkış yapılıyor...', 'success');

    // Ardından PHP logout endpointine yönlendir
    window.location.href = '/is-ortaklar-paneli/auth/logout.php';
    };
  async function loadPartnerProfile(){
    try{
      const headers = getToken() ? { Authorization: 'Bearer ' + getToken(), Accept:'application/json' } : { Accept:'application/json' };
      const res = await fetch(getMeUrl(), { method:'GET', headers });
      const raw = await res.text();
      if (!res.ok){ showMessage('Profil bilgileri alınamadı ('+res.status+').', 'error'); return; }

      let data;
      try { data = JSON.parse(raw); } catch(e){ console.error('JSON parse hatası', e, raw); showMessage('Geçersiz yanıt alındı.', 'error'); return; }

      const displayName = data.company_name || [data.first_name, data.last_name].filter(Boolean).join(' ') || data.email || '';
      setText('profile-name', displayName);
      const ini = document.getElementById('profile-initials'); if (ini) ini.textContent = initialsFrom(displayName);
      setText('profile-type', (data.type || 'Bayi') + (data.legal_type ? ' - ' + data.legal_type : ''));
      setStatusUI(data.status);
      applyGlobalFieldChips(data.status);

      setText('account_id', data.id);
      setText('created_at', formatDateTR(data.created_at, 'UTC'));
      setText('updated_at', formatDateTR(data.updated_at, 'UTC'));

      if ('mfa_enabled' in data || 'mfa_method' in data) {
  const t = document.getElementById('mfa_text');
  const b = document.getElementById('mfa_badge');
  if (t && b) {
    const card    = t.closest('.p-4'); 
    const header  = card?.querySelector('.flex.items-center.justify-between.mb-3');
    const iconBox = header?.querySelector('.w-10.h-10'); // KÜÇÜK kutucuk
    const iconSvg = iconBox?.querySelector('svg');       // içteki SVG

    if (data.mfa_enabled) {
      // Yazılar
      t.textContent = data.mfa_method ? `${data.mfa_method} ile etkin` : 'Etkin';
      t.classList.remove('text-red-700');  
      t.classList.add('text-green-700');

      b.textContent = 'Güvende';
      b.classList.remove('bg-red-100','text-red-800');
      b.classList.add('bg-green-100','text-green-800');

      // Panel
      card?.classList.remove('bg-red-50','border-red-200');
      card?.classList.add('bg-green-50','border-green-200');

      // Küçük ikon kutucuğu + svg
      if (iconBox) {
        iconBox.classList.remove('bg-red-100');
        iconBox.classList.add('bg-green-100');
      }
      if (iconSvg) {
        iconSvg.classList.remove('text-red-600');
        iconSvg.classList.add('text-green-600');
      }
    } else {
      t.textContent = 'Devre Dışı';
      t.classList.remove('text-green-700'); 
      t.classList.add('text-red-700');

      b.textContent = 'Güvensiz';
      b.classList.remove('bg-green-100','text-green-800');
      b.classList.add('bg-red-100','text-red-800');

      card?.classList.remove('bg-green-50','border-green-200');
      card?.classList.add('bg-red-50','border-red-200');

      if (iconBox) {
        iconBox.classList.remove('bg-green-100');
        iconBox.classList.add('bg-red-100');
      }
      if (iconSvg) {
        iconSvg.classList.remove('text-green-600');
        iconSvg.classList.add('text-red-600');
      }
    }
  }
}


      _profileCache = { phone: data.phone || '', email: data.email || '' };
      updateMFAInput(true);

      profileData = data || {};
      renderCompanyContent();
      renderContactContent();
      setCompanyCardTitle(profileData.legal_type || 'Sirket');

      console.log('[PROFILE] OK – UI güncellendi');
    }catch(err){
      console.error('[PROFILE] Hata:', err);
      showMessage('Beklenmeyen bir hata oluştu.', 'error');
    }
  }

  window.addEventListener('load', ()=>{
    loadPartnerProfile();
    document.querySelectorAll('input[name="mfa_method"]').forEach(r =>
      r.addEventListener('change', ()=>updateMFAInput(true))
    );
  });
})();
