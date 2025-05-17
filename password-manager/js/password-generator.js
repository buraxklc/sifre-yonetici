/**
 * Şifre Üreteci ve İlgili Yardımcı Fonksiyonlar
 */

// Rastgele şifre oluşturma fonksiyonu
function generateRandomPassword(options = {}) {
    // Varsayılan ayarlar
    const defaults = {
        length: 12,
        includeUppercase: true,
        includeLowercase: true,
        includeNumbers: true,
        includeSymbols: true
    };
    
    // Seçenekleri birleştir
    const settings = {...defaults, ...options};
    
    // Karakter setleri
    const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const lowercase = 'abcdefghijklmnopqrstuvwxyz';
    const numbers = '0123456789';
    const symbols = '!@#$%^&*()-_=+[]{};:,.<>?';
    
    // Karakter havuzu oluştur
    let characters = '';
    if (settings.includeUppercase) characters += uppercase;
    if (settings.includeLowercase) characters += lowercase;
    if (settings.includeNumbers) characters += numbers;
    if (settings.includeSymbols) characters += symbols;
    
    // En az bir karakter seti seçilmeli
    if (characters.length === 0) {
        characters = lowercase + numbers;
    }
    
    // Şifre oluştur
    let password = '';
    const charactersLength = characters.length;
    
    for (let i = 0; i < settings.length; i++) {
        password += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    
    return password;
}

// Şifre karmaşıklığını kontrol etme
function checkPasswordStrength(password) {
    // Boş şifreler 0 puan alır
    if (!password) return 0;
    
    let score = 0;
    
    // Uzunluk puanı (maksimum 2 puan)
    if (password.length >= 8) score += 1;
    if (password.length >= 12) score += 1;
    
    // Karakter çeşitliliği puanı (maksimum 4 puan)
    if (/[A-Z]/.test(password)) score += 1; // Büyük harf
    if (/[a-z]/.test(password)) score += 1; // Küçük harf
    if (/[0-9]/.test(password)) score += 1; // Sayı
    if (/[^A-Za-z0-9]/.test(password)) score += 1; // Özel karakter
    
    return score;
}

// Şifre güç derecesini metin olarak almak
function getPasswordStrengthText(score) {
    if (score < 3) return 'Zayıf';
    if (score < 5) return 'Orta';
    return 'Güçlü';
}

// Şifre güç derecesine göre renk almak
function getPasswordStrengthColor(score) {
    if (score < 3) return 'danger';
    if (score < 5) return 'warning';
    return 'success';
}

// Pano kopyalama fonksiyonu
function copyToClipboard(text, callback) {
    navigator.clipboard.writeText(text).then(() => {
        if (typeof callback === 'function') {
            callback(true);
        }
        
        // 60 saniye sonra panodan sil (güvenlik için)
        setTimeout(() => {
            navigator.clipboard.writeText('');
        }, 60000);
    }).catch(err => {
        console.error('Panoya kopyalama başarısız:', err);
        if (typeof callback === 'function') {
            callback(false);
        }
    });
}