<?php
require_once 'includes/functions.php';

$error = '';
$success = '';

// Form gönderildiyse
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Form doğrulama
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Tüm alanları doldurunuz.";
    } elseif ($password !== $confirm_password) {
        $error = "Şifreler eşleşmiyor.";
    } elseif (strlen($password) < 8) {
        $error = "Şifre en az 8 karakter olmalıdır.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Geçerli bir e-posta adresi giriniz.";
    } else {
        // Kullanıcı kaydı
        $result = registerUser($username, $email, $password);
        
        if ($result === true) {
            // Başarılı kayıt
            header("Location: login.php?registered=success");
            exit();
        } else {
            // Hata mesajı
            $error = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesap Oluştur - SafeVault</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-hover: #3a56d4;
            --secondary: #f72585;
            --text-dark: #212b36;
            --text-muted: #637381;
            --success: #0cbc87;
            --warning: #fab005;
            --danger: #fa3e3e;
            --light-bg: #f9fafc;
            --card-bg: #ffffff;
            --border-color: #eaecf0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            width: 100%;
            max-width: 900px;
            background-color: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            display: flex;
            height: auto;
        }
        
        .register-left {
            width: 45%;
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .register-right {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
        }
        
        /* Left Side */
        .welcome-text {
            margin-bottom: 32px;
        }
        
        .welcome-text h2 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        
        .welcome-text p {
            font-size: 15px;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .testimonial {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
        }
        
        .testimonial-text {
            font-size: 15px;
            line-height: 1.6;
            font-style: italic;
            margin-bottom: 16px;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .testimonial-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 18px;
        }
        
        .testimonial-info {
            font-size: 14px;
        }
        
        .testimonial-name {
            font-weight: 600;
            margin-bottom: 2px;
        }
        
        .testimonial-title {
            opacity: 0.8;
        }
        
        .login-button {
            margin-top: auto;
            text-align: center;
        }
        
        .login-text {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 16px;
        }
        
        .btn-login {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.4);
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        
        .btn-login:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
        
        .btn-login i {
            margin-right: 8px;
        }
        
        /* Right Side / Form */
        .logo {
            display: inline-flex;
            align-items: center;
            margin-bottom: 40px;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background-color: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            margin-right: 12px;
        }
        
        .logo-text {
            font-weight: 700;
            font-size: 22px;
            color: var(--text-dark);
        }
        
        .register-form {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .register-header {
            margin-bottom: 30px;
        }
        
        .register-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }
        
        .register-header p {
            color: var(--text-muted);
            font-size: 15px;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 24px;
            flex: 1;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
            color: var(--text-dark);
        }
        
        .form-control {
            width: 100%;
            height: 48px;
            padding: 0 16px;
            font-size: 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
            outline: none;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }
        
        .input-icon + .form-control {
            padding-left: 45px;
        }
        
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            cursor: pointer;
            z-index: 10;
        }
        
        .password-strength {
            display: none;
            margin-top: 8px;
        }
        
        .strength-meter {
            height: 4px;
            background-color: #e9ecef;
            border-radius: 2px;
            margin-bottom: 6px;
            overflow: hidden;
        }
        
        .strength-meter-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 0.3s ease, background-color 0.3s ease;
            width: 0;
        }
        
        .strength-text {
            font-size: 12px;
            font-weight: 500;
        }
        
        .weak .strength-meter-fill {
            background-color: var(--danger);
            width: 30%;
        }
        
        .medium .strength-meter-fill {
            background-color: var(--warning);
            width: 60%;
        }
        
        .strong .strength-meter-fill {
            background-color: var(--success);
            width: 100%;
        }
        
        .weak .strength-text {
            color: var(--danger);
        }
        
        .medium .strength-text {
            color: var(--warning);
        }
        
        .strong .strength-text {
            color: var(--success);
        }
        
        .password-tip {
            font-size: 12px;
            color: var(--text-muted);
            display: none;
            margin-top: 4px;
        }
        
        .form-check {
            display: flex;
            margin-bottom: 24px;
        }
        
        .form-check-input {
            width: 18px;
            height: 18px;
            margin-right: 12px;
            flex-shrink: 0;
        }
        
        .form-check-label {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.6;
        }
        
        .form-check-label a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .form-check-label a:hover {
            text-decoration: underline;
        }
        
        /* Button */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 48px;
            padding: 0 24px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s ease;
            border: none;
            outline: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
        }
        
        .btn-primary:active {
            transform: translateY(1px);
        }
        
        .btn-block {
            width: 100%;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        /* Alerts */
        .alert {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        .alert i {
            margin-right: 12px;
            font-size: 16px;
        }
        
        .alert-success {
            background-color: rgba(12, 188, 135, 0.1);
            border: 1px solid rgba(12, 188, 135, 0.2);
            color: var(--success);
        }
        
        .alert-danger {
            background-color: rgba(250, 62, 62, 0.1);
            border: 1px solid rgba(250, 62, 62, 0.2);
            color: var(--danger);
        }
        
        /* Loading */
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }
        
        .loading.active {
            opacity: 1;
            visibility: visible;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(67, 97, 238, 0.2);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .register-container {
                flex-direction: column-reverse;
                max-width: 400px;
            }
            
            .register-left {
                width: 100%;
                order: 1;
                padding: 30px;
            }
            
            .register-right {
                padding: 30px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .welcome-text h2 {
                font-size: 22px;
            }
            
            .register-header h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <!-- Loading overlay -->
    <div class="loading" id="loadingOverlay">
        <div class="spinner"></div>
    </div>
    
    <div class="register-container">
        <!-- Left Side - Features & Testimonial -->
        <div class="register-left">
            <div class="welcome-text">
                <h2>SafeVault ile Tanışın</h2>
                <p>Dijital hayatınızı güvenle yönetin. Tüm şifreleriniz tek bir güvenli yerde, her an erişiminize açık.</p>
            </div>
            
            <div class="testimonial">
                <div class="testimonial-text">
                    "SafeVault ile tüm şifrelerimi yönetmek çok kolaylaştı. Artık her siteye farklı ve güçlü şifreler kullanıyorum ve hiçbirini hatırlamak zorunda değilim!"
                </div>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="testimonial-info">
                        <div class="testimonial-name">Ahmet Yılmaz</div>
                        <div class="testimonial-title">Yazılım Geliştirici</div>
                    </div>
                </div>
            </div>
            
            <div class="login-button">
                <p class="login-text">Zaten hesabınız var mı?</p>
                <a href="login.php" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Giriş Yap
                </a>
            </div>
        </div>
        
        <!-- Right Side - Registration Form -->
        <div class="register-right">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="logo-text">SafeVault</div>
            </div>
            
            <div class="register-form">
                <div class="register-header">
                    <h1>Hesap Oluşturun</h1>
                    <p>Şifrelerinizi güvenle saklamak için hesap oluşturun.</p>
                </div>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="registerForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username" class="form-label">Kullanıcı Adı</label>
                            <div class="input-group">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" id="username" name="username" class="form-control" placeholder="Kullanıcı adınız" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">E-posta Adresi</label>
                            <div class="input-group">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" id="email" name="email" class="form-control" placeholder="E-posta adresiniz" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Şifre</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Güçlü bir şifre oluşturun" required>
                            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="strength-meter">
                                <div class="strength-meter-fill"></div>
                            </div>
                            <div class="strength-text">Şifre gücü: <span>Zayıf</span></div>
                        </div>
                        <div class="password-tip" id="passwordTip">
                            En az 8 karakter ve büyük-küçük harf, rakam ve özel karakter içermelidir.
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Şifreyi Tekrar Girin</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Şifrenizi tekrar girin" required>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="termsCheck" name="terms" required>
                        <label class="form-check-label" for="termsCheck">
                            <a href="terms.php" target="_blank">Kullanım Şartları</a> ve <a href="privacy.php" target="_blank">Gizlilik Politikası</a>'nı okudum ve kabul ediyorum.
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block" id="registerBtn">
                        <i class="fas fa-user-plus"></i> Hesap Oluştur
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Password show/hide
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // Password Strength
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthIndicator = document.getElementById('passwordStrength');
            const passwordTip = document.getElementById('passwordTip');
            
            // Show password strength and tip
            if (password.length > 0) {
                strengthIndicator.style.display = 'block';
                passwordTip.style.display = 'block';
            } else {
                strengthIndicator.style.display = 'none';
                passwordTip.style.display = 'none';
                return;
            }
            
            // Check password strength
            let score = 0;
            
            // Length check
            if (password.length >= 8) score += 1;
            if (password.length >= 12) score += 1;
            
            // Character variety check
            if (/[A-Z]/.test(password)) score += 1;
            if (/[a-z]/.test(password)) score += 1;
            if (/[0-9]/.test(password)) score += 1;
            if (/[^A-Za-z0-9]/.test(password)) score += 1;
            
            // Update strength indicator
            strengthIndicator.className = 'password-strength';
            let strengthText = '';
            
            if (score < 3) {
                strengthIndicator.classList.add('weak');
                strengthText = 'Zayıf';
            } else if (score < 5) {
                strengthIndicator.classList.add('medium');
                strengthText = 'Orta';
            } else {
                strengthIndicator.classList.add('strong');
                strengthText = 'Güçlü';
            }
            
            strengthIndicator.querySelector('.strength-text span').textContent = strengthText;
        });
        
        // Password confirmation check
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Şifreler eşleşmiyor');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Form submit - loading overlay
        document.getElementById('registerForm').addEventListener('submit', function() {
            document.getElementById('loadingOverlay').classList.add('active');
        });
        
        // Automatic cleanup on page show
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) {
                document.getElementById('loadingOverlay').classList.remove('active');
            }
        });
    </script>
</body>
</html>