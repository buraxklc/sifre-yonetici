<?php
require_once 'includes/functions.php';

$error = '';
$success = '';

// Form gönderildiyse
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Giriş kontrolü
    if (empty($username) || empty($password)) {
        $error = "Tüm alanları doldurunuz.";
    } else {
        $result = loginUser($username, $password);
        
        if ($result === true) {
            // Başarılı giriş
            header("Location: dashboard.php");
            exit();
        } else {
            // Hata mesajı
            $error = $result;
        }
    }
}

// Kayıt işlemi başarılıysa
if (isset($_GET['registered']) && $_GET['registered'] == 'success') {
    $success = "Kayıt işlemi başarılı! Şimdi giriş yapabilirsiniz.";
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - SafeVault</title>
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
        
        .login-container {
            width: 100%;
            max-width: 900px;
            background-color: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            display: flex;
            height: auto;
        }
        
        .login-left {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
        }
        
        .login-right {
            width: 45%;
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        /* Logo */
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
        
        /* Form */
        .login-form {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .login-header {
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }
        
        .login-header p {
            color: var(--text-muted);
            font-size: 15px;
        }
        
        .form-group {
            margin-bottom: 24px;
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
        
        /* Remember Me & Forgot Password */
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        
        .form-check {
            display: flex;
            align-items: center;
        }
        
        .form-check-input {
            width: 16px;
            height: 16px;
            margin-right: 8px;
        }
        
        .form-check-label {
            font-size: 14px;
            color: var(--text-muted);
        }
        
        .forgot-link {
            font-size: 14px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .forgot-link:hover {
            text-decoration: underline;
        }
        
        /* Buttons */
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
        
        /* Register Section */
        .register-cta {
            margin-top: auto;
            text-align: center;
            padding-top: 24px;
            border-top: 1px solid var(--border-color);
        }
        
        .register-cta p {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 16px;
        }
        
        .register-link {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }
        
        .register-link:hover {
            text-decoration: underline;
        }
        
        /* Right Side */
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
        
        .feature-list {
            margin-bottom: 40px;
        }
        
        .feature-item {
            display: flex;
            margin-bottom: 20px;
        }
        
        .feature-icon {
            width: 36px;
            height: 36px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            flex-shrink: 0;
        }
        
        .feature-content h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 6px;
        }
        
        .feature-content p {
            font-size: 14px;
            opacity: 0.8;
            line-height: 1.5;
        }
        
        .btn-create-account {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        
        .btn-create-account:hover {
            background-color: rgba(255, 255, 255, 0.3);
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
            .login-container {
                flex-direction: column;
                max-width: 400px;
            }
            
            .login-right {
                width: 100%;
                order: -1;
                padding: 30px;
            }
            
            .login-left {
                padding: 30px;
            }
            
            .welcome-text h2 {
                font-size: 22px;
            }
            
            .login-header h1 {
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
    
    <div class="login-container">
        <!-- Left Side - Login Form -->
        <div class="login-left">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="logo-text">SafeVault</div>
            </div>
            
            <div class="login-form">
                <div class="login-header">
                    <h1>Hesabınıza Giriş Yapın</h1>
                    <p>Şifrelerinize güvenli bir şekilde erişmek için giriş yapın.</p>
                </div>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success; ?></span>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="loginForm">
                    <div class="form-group">
                        <label for="username" class="form-label">Kullanıcı Adı veya E-posta</label>
                        <div class="input-group">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="username" name="username" class="form-control" placeholder="Kullanıcı adı veya e-posta" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Şifre</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Şifrenizi girin" required>
                            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe" name="remember">
                            <label class="form-check-label" for="rememberMe">Beni Hatırla</label>
                        </div>
                        <a href="forgot_password.php" class="forgot-link">Şifremi Unuttum</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block" id="loginBtn">
                        <i class="fas fa-sign-in-alt"></i> Giriş Yap
                    </button>
                </form>
                
                <div class="register-cta">
                    <p>Henüz hesabınız yok mu?</p>
                    <a href="register.php" class="register-link">Hesap Oluştur</a>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Features -->
        <div class="login-right">
            <div class="welcome-text">
                <h2>Şifrelerinizi Güvenle Saklayın</h2>
                <p>SafeVault ile tüm şifreleriniz güvenli bir şekilde saklanır ve her an erişiminize açık olur.</p>
            </div>
            
            <div class="feature-list">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="feature-content">
                        <h4>Güvenli Depolama</h4>
                        <p>Uçtan uca şifreleme teknolojisiyle tüm verileriniz korunur.</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="feature-content">
                        <h4>Güçlü Şifre Üreteci</h4>
                        <p>Tek tıkla güvenli ve benzersiz şifreler oluşturun.</p>
                    </div>
                </div>
            </div>
            
            <a href="register.php" class="btn btn-create-account btn-block">
                <i class="fas fa-user-plus"></i> Ücretsiz Hesap Oluştur
            </a>
        </div>
    </div>
    
    <script>
        // Şifre göster/gizle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // Form submit - loading overlay
        document.getElementById('loginForm').addEventListener('submit', function() {
            document.getElementById('loadingOverlay').classList.add('active');
        });
        
        // Otomatik temizleme - form submit
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) {
                document.getElementById('loadingOverlay').classList.remove('active');
            }
        });
    </script>
</body>
</html>