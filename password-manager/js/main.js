// Ana JavaScript dosyası
document.addEventListener('DOMContentLoaded', function() {
    // Timeout Otomatik Çıkış Fonksiyonu
    let inactivityTimer;
    const inactivityTimeout = 15 * 60 * 1000; // 15 dakika
    
    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(function() {
            alert("Güvenlik nedeniyle oturumunuz sonlandırılıyor.");
            window.location.href = "logout.php";
        }, inactivityTimeout);
    }
    
    // Kullanıcı etkinliğini takip et
    document.addEventListener('mousemove', resetInactivityTimer);
    document.addEventListener('keypress', resetInactivityTimer);
    document.addEventListener('click', resetInactivityTimer);
    
    // İlk timer'ı başlat
    resetInactivityTimer();
    
    // Şifre kartlarında göster/gizle düğmeleri
    document.querySelectorAll('.toggle-password').forEach(function(button) {
        button.addEventListener('click', function() {
            const passwordField = this.parentNode.querySelector('input');
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });
    
    // Panoya kopyalama fonksiyonu
    document.querySelectorAll('.copy-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const textToCopy = this.getAttribute('data-clipboard-text');
            navigator.clipboard.writeText(textToCopy).then(() => {
                // Kopyalama başarılı
                const originalHTML = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i>';
                button.classList.add('copy-animation');
                
                setTimeout(() => {
                    this.innerHTML = originalHTML;
                    button.classList.remove('copy-animation');
                }, 1500);
                
                // 60 saniye sonra panodan sil (güvenlik için)
                setTimeout(() => {
                    // Panoya boş metin kopyalayarak önceki içeriği siler
                    // Bu tam olarak güvenli değildir, ancak basit bir önlemdir
                    navigator.clipboard.writeText('');
                }, 60000);
            });
        });
    });
    
    // Arama filtreleme fonksiyonu
    const searchBox = document.getElementById('searchPasswords');
    if (searchBox) {
        searchBox.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const passwordCards = document.querySelectorAll('.password-card');
            
            passwordCards.forEach(function(card) {
                const title = card.querySelector('.card-title').textContent.toLowerCase();
                const username = card.querySelector('.username-field').value.toLowerCase();
                const category = card.querySelector('.category-badge') ? 
                                 card.querySelector('.category-badge').textContent.toLowerCase() : '';
                const notes = card.querySelector('.notes-content') ? 
                             card.querySelector('.notes-content').textContent.toLowerCase() : '';
                
                if (title.includes(searchTerm) || 
                    username.includes(searchTerm) || 
                    category.includes(searchTerm) || 
                    notes.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    // Şifre güç derecesini kontrol etme
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }
            
            // Şifre güç kontrolü
            let score = 0;
            
            // Uzunluk kontrolü
            if (password.length >= 8) score += 1;
            if (password.length >= 12) score += 1;
            
            // Karakter çeşitliliği kontrolü
            if (/[A-Z]/.test(password)) score += 1;
            if (/[a-z]/.test(password)) score += 1;
            if (/[0-9]/.test(password)) score += 1;
            if (/[^A-Za-z0-9]/.test(password)) score += 1;
            
            // Güç derecesi
            let strength, color;
            if (score < 3) {
                strength = 'Zayıf';
                color = 'danger';
            } else if (score < 5) {
                strength = 'Orta';
                color = 'warning';
            } else {
                strength = 'Güçlü';
                color = 'success';
            }
            
            strengthDiv.innerHTML = `<div class="progress">
                <div class="progress-bar bg-${color}" role="progressbar" style="width: ${(score/6)*100}%" aria-valuenow="${score}" aria-valuemin="0" aria-valuemax="6"></div>
            </div>
            <small class="text-${color}">${strength} Şifre</small>`;
        });
    }
});