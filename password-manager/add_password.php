<?php
require_once 'includes/functions.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Form gönderildiyse
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $url = trim($_POST['url']);
    $notes = trim($_POST['notes']);
    $category = trim($_POST['category']);
    
    // Form doğrulama
    if (empty($title) || empty($username) || empty($password)) {
        $error = "Zorunlu alanları doldurunuz.";
    } else {
        // Şifre ekleme
        $result = addPassword($_SESSION['user_id'], $title, $username, $password, $url, $notes, $category);
        
        if ($result === true) {
            // Başarılı
            $success = "Şifre başarıyla kaydedildi.";
            // Formu temizle
            $_POST = [];
        } else {
            // Hata mesajı
            $error = $result;
        }
    }
}

// Kategorileri getir
$categories = [];
$allPasswords = getUserPasswords($_SESSION['user_id']);
foreach ($allPasswords as $pass) {
    if (!empty($pass['category']) && !in_array($pass['category'], $categories)) {
        $categories[] = $pass['category'];
    }
}

// URL'den şifre parametresini al (şifre oluşturucu sayfasından yönlendirme için)
$generatedPassword = isset($_GET['password']) ? $_GET['password'] : '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Şifre Ekle - SafeVault</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #818cf8;
            --secondary: #f97316;
            --secondary-light: #fdba74;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --white: #ffffff;
            
            --sidebar-width: 280px;
            --sidebar-collapsed: 80px;
            --header-height: 70px;
            --content-padding: 30px;
            
            --border-radius-sm: 6px;
            --border-radius: 10px;
            --border-radius-lg: 16px;
            
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            
            --transition: all 0.25s ease-in-out;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        a {
            text-decoration: none;
            color: inherit;
        }
        
        ul {
            list-style: none;
        }
        
        /* Layout */
        .dashboard {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--white);
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            border-right: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
        }
        
        .sidebar-collapsed .sidebar {
            width: var(--sidebar-collapsed);
        }
        
        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--gray-200);
            height: var(--header-height);
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .logo-text {
            font-weight: 700;
            font-size: 20px;
            color: var(--dark);
            margin-left: 12px;
            transition: var(--transition);
        }
        
        .sidebar-collapsed .logo-text {
            display: none;
        }
        
        .sidebar-toggle {
            margin-left: auto;
            width: 36px;
            height: 36px;
            border-radius: var(--border-radius-sm);
            background-color: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--gray-600);
            transition: var(--transition);
        }
        
        .sidebar-toggle:hover {
            background-color: var(--gray-200);
            color: var(--dark);
        }
        
        .sidebar-collapsed .sidebar-toggle {
            transform: rotate(180deg);
        }
        
        .sidebar-menu {
            flex: 1;
            padding: 20px 0;
            overflow-y: auto;
        }
        
        .menu-section {
            margin-bottom: 24px;
        }
        
        .menu-header {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--gray-500);
            padding: 0 20px;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
            transition: var(--transition);
        }
        
        .sidebar-collapsed .menu-header {
            opacity: 0;
            height: 0;
            margin: 0;
        }
        
        .menu-items {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .menu-item {
            position: relative;
        }
        
        .menu-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--gray-600);
            font-weight: 500;
            font-size: 15px;
            transition: var(--transition);
            position: relative;
            border-left: 3px solid transparent;
        }
        
        .menu-link:hover {
            background-color: var(--gray-100);
            color: var(--dark);
        }
        
        .menu-link.active {
            color: var(--primary);
            background-color: rgba(79, 70, 229, 0.08);
            border-left-color: var(--primary);
        }
        
        .menu-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            flex-shrink: 0;
            font-size: 18px;
        }
        
        .menu-text {
            transition: var(--transition);
        }
        
        .sidebar-collapsed .menu-text {
            opacity: 0;
            width: 0;
            display: none;
        }
        
        .menu-badge {
            margin-left: auto;
            background-color: var(--primary);
            color: var(--white);
            font-size: 12px;
            font-weight: 500;
            padding: 2px 8px;
            border-radius: 50px;
            transition: var(--transition);
        }
        
        .sidebar-collapsed .menu-badge {
            opacity: 0;
            width: 0;
            display: none;
        }
        
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: var(--border-radius-sm);
            background-color: var(--primary-light);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 600;
            flex-shrink: 0;
        }
        
        .user-info {
            margin-left: 12px;
            transition: var(--transition);
        }
        
        .sidebar-collapsed .user-info {
            opacity: 0;
            width: 0;
            display: none;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 15px;
            color: var(--dark);
            margin-bottom: 2px;
        }
        
        .user-role {
            font-size: 13px;
            color: var(--gray-500);
        }
        
        .user-menu {
            margin-left: auto;
            width: 36px;
            height: 36px;
            border-radius: var(--border-radius-sm);
            background-color: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--gray-600);
            transition: var(--transition);
        }
        
        .user-menu:hover {
            background-color: var(--gray-200);
            color: var(--dark);
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            padding-left: var(--sidebar-width);
            transition: var(--transition);
        }
        
        .sidebar-collapsed .main-content {
            padding-left: var(--sidebar-collapsed);
        }
        
        .header {
            height: var(--header-height);
            background-color: var(--white);
            border-bottom: 1px solid var(--gray-200);
            padding: 0 var(--content-padding);
            display: flex;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 99;
        }
        
        .page-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .header-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
        }
        
        .header-action {
            width: 40px;
            height: 40px;
            border-radius: var(--border-radius-sm);
            background-color: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--gray-600);
            transition: var(--transition);
            margin-left: 10px;
            position: relative;
        }
        
        .header-action:hover {
            background-color: var(--gray-200);
            color: var(--dark);
        }
        
        .content {
            padding: var(--content-padding);
        }
        
        /* Form Card */
        .form-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .form-header {
            padding: 24px;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .form-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        .form-subtitle {
            font-size: 14px;
            color: var(--gray-500);
        }
        
        .form-body {
            padding: 24px;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px dashed var(--gray-200);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .form-group {
            margin-bottom: 24px;
            position: relative;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
            color: var(--dark);
        }
        
        .form-label .required {
            color: var(--danger);
            margin-left: 3px;
        }
        
        .form-control {
            width: 100%;
            height: 48px;
            padding: 0 16px;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 15px;
            color: var(--dark);
            background-color: var(--white);
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }
        
        .form-control-icon {
            padding-left: 45px;
        }
        
        .form-icon {
            position: absolute;
            top: 40px;
            left: 15px;
            color: var(--gray-400);
            font-size: 16px;
        }
        
        .form-control:focus + .form-icon {
            color: var(--primary);
        }
        
        .input-group {
            position: relative;
            display: flex;
        }
        
        .input-group .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            flex: 1;
        }
        
        .input-group-append {
            display: flex;
        }
        
        .input-group-text {
            height: 48px;
            display: flex;
            align-items: center;
            padding: 0 16px;
            background-color: var(--gray-100);
            border: 1px solid var(--gray-300);
            border-left: none;
            border-top-right-radius: var(--border-radius);
            border-bottom-right-radius: var(--border-radius);
            color: var(--gray-600);
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .input-group-text:hover {
            background-color: var(--gray-200);
            color: var(--dark);
        }
        
        .input-group-divider {
            width: 1px;
            background-color: var(--gray-300);
            margin: 8px 0;
        }
        
        textarea.form-control {
            height: auto;
            min-height: 120px;
            resize: vertical;
            padding: 12px 16px;
        }
        
        .form-text {
            margin-top: 6px;
            font-size: 13px;
            color: var(--gray-500);
        }
        
        /* Checkboxes */
        .custom-checkbox {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .custom-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }
        
        .checkmark {
            height: 22px;
            width: 22px;
            background-color: var(--white);
            border: 2px solid var(--gray-300);
            border-radius: var(--border-radius-sm);
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: var(--transition);
        }
        
        .custom-checkbox:hover .checkmark {
            border-color: var(--primary);
        }
        
        .custom-checkbox input:checked ~ .checkmark {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .checkmark::after {
            content: "\f00c";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            color: var(--white);
            font-size: 12px;
            display: none;
        }
        
        .custom-checkbox input:checked ~ .checkmark::after {
            display: block;
        }
        
        .checkbox-label {
            font-size: 14px;
            color: var(--dark);
        }
        
        /* Password Strength */
        .password-strength {
            margin-top: 10px;
        }
        
        .strength-meter {
            height: 6px;
            background-color: var(--gray-200);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 6px;
        }
        
        .strength-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        
        .strength-text {
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .strength-label {
            color: var(--gray-600);
        }
        
        .strength-value {
            font-weight: 600;
        }
        
        .strength-value.weak {
            color: var(--danger);
        }
        
        .strength-value.medium {
            color: var(--warning);
        }
        
        .strength-value.strong {
            color: var(--success);
        }
        
        .strength-fill.weak {
            width: 33%;
            background: linear-gradient(90deg, var(--danger) 0%, #F87171 100%);
        }
        
        .strength-fill.medium {
            width: 66%;
            background: linear-gradient(90deg, var(--warning) 0%, #FBBF24 100%);
        }
        
        .strength-fill.strong {
            width: 100%;
            background: linear-gradient(90deg, var(--success) 0%, #34D399 100%);
        }
        
        /* Generate Button */
        .btn-generate {
            background: linear-gradient(135deg, var(--success) 0%, #34D399 100%);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius-sm);
            height: 48px;
            padding: 0 20px;
            font-size: 15px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn-generate:hover {
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
            transform: translateY(-2px);
        }
        
        /* Form Footer */
        .form-footer {
            padding: 24px;
            border-top: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .form-info {
            font-size: 14px;
            color: var(--gray-500);
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
        }
        
        .btn {
            height: 48px;
            padding: 0 24px;
            font-size: 15px;
            font-weight: 600;
            border-radius: var(--border-radius);
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            border: none;
        }
        
        .btn-primary:hover {
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: var(--white);
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
        }
        
        .btn-secondary:hover {
            background-color: var(--gray-100);
            border-color: var(--gray-400);
        }
        
        /* Alert */
        .alert {
            padding: 16px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
        }
        
        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: var(--success);
        }
        
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }
        
        .alert-icon {
            font-size: 18px;
            margin-right: 12px;
            margin-top: 2px;
        }
        
        .alert-content {
            flex: 1;
        }
        
        .alert-title {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .alert-message {
            font-size: 14px;
        }
        
        /* Page Header */
        .page-header {
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .page-header-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .page-header-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--border-radius-sm);
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        
        .page-header-text h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
        }
        
        .page-header-text p {
            font-size: 14px;
            color: var(--gray-500);
            margin-bottom: 0;
        }
        
        .page-header-actions {
            display: flex;
            gap: 12px;
            margin-left: auto;
        }
        
        /* Responsive */
        @media (max-width: 991px) {
            :root {
                --content-padding: 20px;
            }
            
            .dashboard {
                height: auto;
                min-height: 100vh;
            }
            
            .sidebar {
                transform: translateX(-100%);
                box-shadow: var(--shadow);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .sidebar-collapsed .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                padding-left: 0 !important;
            }
            
            .mobile-toggle {
                display: block !important;
            }
            
            .form-footer {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .form-actions {
                width: 100%;
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
        
        .mobile-toggle {
            width: 40px;
            height: 40px;
            border-radius: var(--border-radius-sm);
            background-color: var(--gray-100);
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--gray-600);
            transition: var(--transition);
            margin-right: 16px;
        }
        
        .mobile-toggle:hover {
            background-color: var(--gray-200);
            color: var(--dark);
        }
        
        @media (max-width: 767px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .page-header-title {
                width: 100%;
            }
        }
        
        /* Modal */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal-backdrop.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 500px;
            transform: translateY(20px);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .modal-backdrop.active .modal {
            transform: translateY(0);
            opacity: 1;
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .modal-title i {
            color: var(--primary);
        }
        
        .modal-close {
            width: 36px;
            height: 36px;
            border-radius: var(--border-radius-sm);
            background-color: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--gray-500);
            transition: var(--transition);
        }
        
        .modal-close:hover {
            background-color: var(--gray-200);
            color: var(--dark);
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 20px;
            border-top: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
        }
        
        /* Toast Notification */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .toast {
           background-color: var(--white);
           border-radius: var(--border-radius-sm);
           box-shadow: var(--shadow-lg);
           padding: 16px;
           margin-bottom: 10px;
           display: flex;
           align-items: center;
           width: 320px;
           animation: slideIn 0.3s ease-out forwards;
           transform: translateX(120%);
       }
       
       @keyframes slideIn {
           to { transform: translateX(0); }
       }
       
       @keyframes slideOut {
           to { transform: translateX(120%); }
       }
       
       .toast.hide {
           animation: slideOut 0.3s ease-in forwards;
       }
       
       .toast-icon {
           width: 36px;
           height: 36px;
           border-radius: 50%;
           display: flex;
           align-items: center;
           justify-content: center;
           margin-right: 12px;
           flex-shrink: 0;
       }
       
       .toast-success .toast-icon {
           background-color: rgba(16, 185, 129, 0.1);
           color: var(--success);
       }
       
       .toast-error .toast-icon {
           background-color: rgba(239, 68, 68, 0.1);
           color: var(--danger);
       }
       
       .toast-info .toast-icon {
           background-color: rgba(79, 70, 229, 0.1);
           color: var(--primary);
       }
       
       .toast-content {
           flex: 1;
       }
       
       .toast-title {
           font-size: 15px;
           font-weight: 600;
           color: var(--dark);
           margin-bottom: 4px;
       }
       
       .toast-message {
           font-size: 13px;
           color: var(--gray-600);
       }
       
       .toast-close {
           width: 24px;
           height: 24px;
           border-radius: 50%;
           background-color: var(--gray-100);
           display: flex;
           align-items: center;
           justify-content: center;
           cursor: pointer;
           color: var(--gray-500);
           margin-left: 12px;
           flex-shrink: 0;
           font-size: 12px;
       }
       
       .toast-close:hover {
           background-color: var(--gray-200);
           color: var(--gray-700);
       }
       
       /* Dropdown Menu */
       .dropdown-menu {
           position: absolute;
           top: 100%;
           right: 0;
           background-color: var(--white);
           border-radius: var(--border-radius-sm);
           box-shadow: var(--shadow);
           width: 200px;
           z-index: 100;
           overflow: hidden;
           display: none;
       }
       
       .dropdown-menu.active {
           display: block;
       }
       
       .dropdown-item {
           padding: 12px 16px;
           font-size: 14px;
           color: var(--gray-700);
           display: flex;
           align-items: center;
           transition: var(--transition);
       }
       
       .dropdown-item:hover {
           background-color: var(--gray-100);
       }
       
       .dropdown-item i {
           margin-right: 10px;
           font-size: 16px;
           color: var(--gray-500);
       }
       
       .dropdown-divider {
           height: 1px;
           background-color: var(--gray-200);
           margin: 6px 0;
       }
       
       .dropdown-item.danger {
           color: var(--danger);
       }
       
       .dropdown-item.danger i {
           color: var(--danger);
       }
   </style>
</head>
<body>
   <div class="dashboard">
       <!-- Sidebar -->
       <div class="sidebar">
           <div class="sidebar-header">
               <div class="logo">
                   <div class="logo-icon">
                       <i class="fas fa-shield-alt"></i>
                   </div>
                   <div class="logo-text">SafeVault</div>
               </div>
               <div class="sidebar-toggle" id="sidebarToggle">
                   <i class="fas fa-chevron-left"></i>
               </div>
           </div>
           
           <div class="sidebar-menu">
               <div class="menu-section">
                   <div class="menu-header">Ana Menü</div>
                   <ul class="menu-items">
                       <li class="menu-item">
                           <a href="dashboard.php" class="menu-link">
                               <div class="menu-icon">
                                   <i class="fas fa-tachometer-alt"></i>
                               </div>
                               <span class="menu-text">Dashboard</span>
                           </a>
                       </li>
                       <li class="menu-item">
                           <a href="passwords.php" class="menu-link">
                               <div class="menu-icon">
                                   <i class="fas fa-key"></i>
                               </div>
                               <span class="menu-text">Şifrelerim</span>
                           </a>
                       </li>
                       <li class="menu-item">
                           <a href="generate_password.php" class="menu-link">
                               <div class="menu-icon">
                                   <i class="fas fa-dice"></i>
                               </div>
                               <span class="menu-text">Şifre Oluşturucu</span>
                           </a>
                       </li>
                       <li class="menu-item">
                           <a href="categories.php" class="menu-link">
                               <div class="menu-icon">
                                   <i class="fas fa-folder"></i>
                               </div>
                               <span class="menu-text">Kategoriler</span>
                           </a>
                       </li>
                   </ul>
               </div>
               
               <div class="menu-section">
                   <div class="menu-header">Araçlar</div>
                   <ul class="menu-items">
                       <li class="menu-item">
                           <a href="security_check.php" class="menu-link">
                               <div class="menu-icon">
                                   <i class="fas fa-shield-alt"></i>
                               </div>
                               <span class="menu-text">Güvenlik Kontrol</span>
                           </a>
                       </li>
                       <li class="menu-item">
                           <a href="import_export.php" class="menu-link">
                               <div class="menu-icon">
                                   <i class="fas fa-exchange-alt"></i>
                               </div>
                               <span class="menu-text">İçe/Dışa Aktar</span>
                           </a>
                       </li>
                       <li class="menu-item">
                           <a href="trash.php" class="menu-link">
                               <div class="menu-icon">
                                   <i class="fas fa-trash-alt"></i>
                               </div>
                               <span class="menu-text">Çöp Kutusu</span>
                           </a>
                       </li>
                   </ul>
               </div>
               
               <div class="menu-section">
                   <div class="menu-header">Hesap</div>
                   <ul class="menu-items">
                       <li class="menu-item">
                           <a href="profile.php" class="menu-link">
                               <div class="menu-icon">
                                   <i class="fas fa-user-circle"></i>
                               </div>
                               <span class="menu-text">Profil</span>
                           </a>
                       </li>
                       <li class="menu-item">
                           <a href="settings.php" class="menu-link">
                               <div class="menu-icon">
                                   <i class="fas fa-cog"></i>
                               </div>
                               <span class="menu-text">Ayarlar</span>
                           </a>
                       </li>
                   </ul>
               </div>
           </div>
           
           <div class="sidebar-footer">
               <div class="user-avatar">
                   <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
               </div>
               <div class="user-info">
                   <div class="user-name"><?php echo $_SESSION['username']; ?></div>
                   <div class="user-role">Ücretsiz Hesap</div>
               </div>
               <div class="user-menu" id="userMenuToggle">
                   <i class="fas fa-ellipsis-v"></i>
               </div>
               <div class="dropdown-menu" id="userMenu">
                   <a href="profile.php" class="dropdown-item">
                       <i class="fas fa-user-circle"></i>
                       Profil
                   </a>
                   <a href="settings.php" class="dropdown-item">
                       <i class="fas fa-cog"></i>
                       Ayarlar
                   </a>
                   <div class="dropdown-divider"></div>
                   <a href="help.php" class="dropdown-item">
                       <i class="fas fa-question-circle"></i>
                       Yardım
                   </a>
                   <a href="logout.php" class="dropdown-item danger">
                       <i class="fas fa-sign-out-alt"></i>
                       Çıkış Yap
                   </a>
               </div>
           </div>
       </div>
       
       <!-- Main Content -->
       <div class="main-content" id="mainContent">
           <!-- Header -->
           <div class="header">
               <div class="mobile-toggle" id="mobileToggle">
                   <i class="fas fa-bars"></i>
               </div>
               <div class="page-title">Yeni Şifre Ekle</div>
               <div class="header-actions">
                   <a href="generate_password.php" class="header-action" title="Şifre Oluştur">
                       <i class="fas fa-dice"></i>
                   </a>
                   <div class="header-action" id="helpToggle" title="Yardım">
                       <i class="fas fa-question-circle"></i>
                   </div>
               </div>
           </div>
           
           <!-- Content -->
           <div class="content">
               <!-- Page Header -->
               <div class="page-header">
                   <div class="page-header-title">
                       <div class="page-header-icon">
                           <i class="fas fa-plus"></i>
                       </div>
                       <div class="page-header-text">
                           <h1>Yeni Şifre Ekle</h1>
                           <p>Hesap bilgilerinizi güvenle saklamak için bilgilerinizi doldurun.</p>
                       </div>
                   </div>
                   
                   <div class="page-header-actions">
                       <a href="dashboard.php" class="btn btn-secondary">
                           <i class="fas fa-arrow-left"></i>
                           Dashboard'a Dön
                       </a>
                   </div>
               </div>
               
               <?php if (!empty($error)): ?>
               <div class="alert alert-danger">
                   <div class="alert-icon">
                       <i class="fas fa-exclamation-circle"></i>
                   </div>
                   <div class="alert-content">
                       <div class="alert-title">Hata</div>
                       <div class="alert-message"><?php echo $error; ?></div>
                   </div>
               </div>
               <?php endif; ?>
               
               <?php if (!empty($success)): ?>
               <div class="alert alert-success">
                   <div class="alert-icon">
                       <i class="fas fa-check-circle"></i>
                   </div>
                   <div class="alert-content">
                       <div class="alert-title">Başarılı</div>
                       <div class="alert-message"><?php echo $success; ?></div>
                   </div>
               </div>
               <?php endif; ?>
               
               <!-- Form Card -->
               <div class="form-card">
                   <div class="form-header">
                       <div class="form-title">Şifre Bilgileri</div>
                       <div class="form-subtitle">Şifrenizi güvenle saklamak için aşağıdaki bilgileri doldurun.</div>
                   </div>
                   
                   <form method="POST" action="" id="passwordForm">
                       <div class="form-body">
                           <div class="form-section">
                               <div class="section-title">Genel Bilgiler</div>
                               
                               <div class="form-row">
                                   <div class="form-group">
                                       <label for="title" class="form-label">Site/Uygulama Adı <span class="required">*</span></label>
                                       <input type="text" id="title" name="title" class="form-control form-control-icon" placeholder="Örn: Google, Netflix, Instagram" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                                       <i class="fas fa-globe form-icon"></i>
                                   </div>
                                   
                                   <div class="form-group">
                                       <label for="category" class="form-label">Kategori</label>
                                       <input type="text" id="category" name="category" class="form-control form-control-icon" placeholder="Örn: Sosyal Medya, Bankacılık" list="categories" value="<?php echo isset($_POST['category']) ? htmlspecialchars($_POST['category']) : ''; ?>">
                                       <i class="fas fa-folder form-icon"></i>
                                       <datalist id="categories">
                                           <?php foreach ($categories as $cat): ?>
                                           <option value="<?php echo htmlspecialchars($cat); ?>">
                                           <?php endforeach; ?>
                                       </datalist>
                                       <div class="form-text">Şifrelerinizi kategorilere ayırmak, daha sonra bulmanızı kolaylaştırır.</div>
                                   </div>
                               </div>
                               
                               <div class="form-group">
                                   <label for="url" class="form-label">Website URL</label>
                                   <input type="url" id="url" name="url" class="form-control form-control-icon" placeholder="https://example.com" value="<?php echo isset($_POST['url']) ? htmlspecialchars($_POST['url']) : ''; ?>">
                                   <i class="fas fa-link form-icon"></i>
                                   <div class="form-text">Tam URL, site ikonunu otomatik olarak getirir.</div>
                               </div>
                           </div>
                           
                           <div class="form-section">
                               <div class="section-title">Kimlik Bilgileri</div>
                               
                               <div class="form-group">
                                   <label for="username" class="form-label">Kullanıcı Adı/E-posta <span class="required">*</span></label>
                                   <input type="text" id="username" name="username" class="form-control form-control-icon" placeholder="Kullanıcı adınız veya e-posta adresiniz" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                   <i class="fas fa-user form-icon"></i>
                               </div>
                               
                               <div class="form-group">
                                   <label for="password" class="form-label">Şifre <span class="required">*</span></label>
                                   <div class="input-group">
                                       <input type="password" id="password" name="password" class="form-control" placeholder="Güçlü bir şifre girin" value="<?php echo !empty($generatedPassword) ? htmlspecialchars($generatedPassword) : (isset($_POST['password']) ? htmlspecialchars($_POST['password']) : ''); ?>" required>
                                       <div class="input-group-append">
                                           <span class="input-group-text toggle-password" title="Göster/Gizle">
                                               <i class="fas fa-eye"></i>
                                           </span>
                                           <div class="input-group-divider"></div>
                                           <span class="input-group-text" id="generatePasswordBtn" title="Oluştur">
                                               <i class="fas fa-dice"></i>
                                           </span>
                                       </div>
                                   </div>
                                   
                                   <div class="password-strength" id="passwordStrength">
                                       <div class="strength-meter">
                                           <div class="strength-fill" id="strengthFill"></div>
                                       </div>
                                       <div class="strength-text">
                                           <span class="strength-label">Şifre Gücü:</span>
                                           <span class="strength-value" id="strengthValue">Güçlü</span>
                                       </div>
                                   </div>
                               </div>
                           </div>
                           
                           <div class="form-section">
                               <div class="section-title">Ek Bilgiler</div>
                               
                               <div class="form-group">
                                   <label for="notes" class="form-label">Notlar</label>
                                   <textarea id="notes" name="notes" class="form-control" placeholder="İsterseniz şifre hakkında notlar ekleyebilirsiniz..."><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                               </div>
                               
                               <div class="form-group">
                                   <label class="custom-checkbox">
                                       <input type="checkbox" id="favoriteCheck" name="favorite">
                                       <span class="checkmark"></span>
                                       <span class="checkbox-label">Favorilere Ekle</span>
                                   </label>
                               </div>
                           </div>
                       </div>
                       
                       <div class="form-footer">
                           <div class="form-info">
                               <i class="fas fa-shield-alt text-primary"></i>
                               Bilgileriniz uçtan uca şifrelenerek güvenle saklanır.
                           </div>
                           
                           <div class="form-actions">
                               <button type="reset" class="btn btn-secondary">
                                   <i class="fas fa-redo"></i>
                                   Temizle
                               </button>
                               <button type="submit" class="btn btn-primary">
                                   <i class="fas fa-save"></i>
                                   Şifreyi Kaydet
                               </button>
                           </div>
                       </div>
                   </form>
               </div>
           </div>
       </div>
   </div>
   
   <!-- Password Generator Modal -->
   <div class="modal-backdrop" id="passwordGeneratorModal">
       <div class="modal">
           <div class="modal-header">
               <div class="modal-title">
                   <i class="fas fa-dice"></i>
                   Güçlü Şifre Oluştur
               </div>
               <div class="modal-close" id="closeGeneratorModal">
                   <i class="fas fa-times"></i>
               </div>
           </div>
           <div class="modal-body">
               <div class="form-group">
                   <label for="passwordLength" class="form-label">Şifre Uzunluğu: <span id="lengthValue">16</span></label>
                   <input type="range" id="passwordLength" min="8" max="32" value="16" class="form-control">
               </div>
               
               <div class="form-group">
                   <div class="form-check">
                       <label class="custom-checkbox">
                           <input type="checkbox" id="includeUppercase" checked>
                           <span class="checkmark"></span>
                           <span class="checkbox-label">Büyük Harfler (A-Z)</span>
                       </label>
                   </div>
                   <div class="form-check">
                       <label class="custom-checkbox">
                           <input type="checkbox" id="includeLowercase" checked>
                           <span class="checkmark"></span>
                           <span class="checkbox-label">Küçük Harfler (a-z)</span>
                       </label>
                   </div>
                   <div class="form-check">
                       <label class="custom-checkbox">
                           <input type="checkbox" id="includeNumbers" checked>
                           <span class="checkmark"></span>
                           <span class="checkbox-label">Sayılar (0-9)</span>
                       </label>
                   </div>
                   <div class="form-check">
                       <label class="custom-checkbox">
                           <input type="checkbox" id="includeSymbols" checked>
                           <span class="checkmark"></span>
                           <span class="checkbox-label">Özel Karakterler (!@#$%^&*)</span>
                       </label>
                   </div>
               </div>
               
               <div class="form-group">
                   <label class="form-label">Oluşturulan Şifre</label>
                   <div class="input-group">
                       <input type="text" id="generatedPassword" class="form-control" readonly>
                       <div class="input-group-append">
                           <span class="input-group-text" id="copyGeneratedPassword" title="Kopyala">
                               <i class="fas fa-copy"></i>
                           </span>
                       </div>
                   </div>
               </div>
           </div>
           <div class="modal-footer">
               <button class="btn btn-secondary" id="regeneratePassword">
                   <i class="fas fa-sync-alt"></i>
                   Yeniden Oluştur
               </button>
               <button class="btn btn-primary" id="useGeneratedPassword">
                   <i class="fas fa-check"></i>
                   Bu Şifreyi Kullan
               </button>
           </div>
       </div>
   </div>
   
   <!-- Toast Container -->
   <div class="toast-container" id="toastContainer"></div>
   
   <script>
       // Sidebar toggle
       document.getElementById('sidebarToggle').addEventListener('click', function() {
           document.body.classList.toggle('sidebar-collapsed');
       });
       
       // Mobile menu toggle
       document.getElementById('mobileToggle').addEventListener('click', function() {
           document.querySelector('.sidebar').classList.toggle('active');
       });
       
       // User menu toggle
       document.getElementById('userMenuToggle').addEventListener('click', function(e) {
           e.stopPropagation();
           document.getElementById('userMenu').classList.toggle('active');
       });
       
       // Close dropdown when clicking outside
       document.addEventListener('click', function(e) {
           const userMenu = document.getElementById('userMenu');
           if (userMenu.classList.contains('active') && !e.target.closest('#userMenuToggle')) {
               userMenu.classList.remove('active');
           }
       });
       
       // Password toggle
       document.querySelector('.toggle-password').addEventListener('click', function() {
           const passwordInput = document.getElementById('password');
           const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
           passwordInput.setAttribute('type', type);
           this.querySelector('i').classList.toggle('fa-eye');
           this.querySelector('i').classList.toggle('fa-eye-slash');
       });
       
       // Password strength check
       document.getElementById('password').addEventListener('input', checkPasswordStrength);
       
       function checkPasswordStrength() {
           const password = document.getElementById('password').value;
           const strengthFill = document.getElementById('strengthFill');
           const strengthValue = document.getElementById('strengthValue');
           
           if (!password) {
               strengthFill.className = 'strength-fill';
               strengthValue.className = 'strength-value';
               strengthValue.textContent = '';
               return;
           }
           
           // Calculate strength score
           let score = 0;
           
           // Length check
           if (password.length >= 8) score += 1;
           if (password.length >= 12) score += 1;
           
           // Character variety
           if (/[A-Z]/.test(password)) score += 1;
           if (/[a-z]/.test(password)) score += 1;
           if (/[0-9]/.test(password)) score += 1;
           if (/[^A-Za-z0-9]/.test(password)) score += 1;
           
           // Update strength indicator
           strengthFill.className = 'strength-fill';
           strengthValue.className = 'strength-value';
           
           if (score < 3) {
               strengthFill.classList.add('weak');
               strengthValue.classList.add('weak');
               strengthValue.textContent = 'Zayıf';
           } else if (score < 5) {
               strengthFill.classList.add('medium');
               strengthValue.classList.add('medium');
               strengthValue.textContent = 'Orta';
           } else {
               strengthFill.classList.add('strong');
               strengthValue.classList.add('strong');
               strengthValue.textContent = 'Güçlü';
           }
       }
       
       // Check password strength on page load
       window.addEventListener('DOMContentLoaded', function() {
           checkPasswordStrength();
       });
       
       // Password Generator Modal
       const generatorModal = document.getElementById('passwordGeneratorModal');
       
       document.getElementById('generatePasswordBtn').addEventListener('click', function() {
           generatorModal.classList.add('active');
           generatePassword();
       });
       
       document.getElementById('closeGeneratorModal').addEventListener('click', function() {
           generatorModal.classList.remove('active');
       });
       
       // Generate password
       function generatePassword() {
           const length = document.getElementById('passwordLength').value;
           const useUppercase = document.getElementById('includeUppercase').checked;
           const useLowercase = document.getElementById('includeLowercase').checked;
           const useNumbers = document.getElementById('includeNumbers').checked;
           const useSymbols = document.getElementById('includeSymbols').checked;
           
           let charset = '';
           if (useUppercase) charset += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
           if (useLowercase) charset += 'abcdefghijklmnopqrstuvwxyz';
           if (useNumbers) charset += '0123456789';
           if (useSymbols) charset += '!@#$%^&*()-_=+[]{}|;:,.<>?';
           
           // Ensure at least one character set is selected
           if (!charset) {
               charset = 'abcdefghijklmnopqrstuvwxyz0123456789';
               document.getElementById('includeLowercase').checked = true;
               document.getElementById('includeNumbers').checked = true;
           }
           
           let password = '';
           for (let i = 0; i < length; i++) {
               const randomIndex = Math.floor(Math.random() * charset.length);
               password += charset[randomIndex];
           }
           
           document.getElementById('generatedPassword').value = password;
       }
       
       // Update length display
       document.getElementById('passwordLength').addEventListener('input', function() {
           document.getElementById('lengthValue').textContent = this.value;
       });
       
       // Regenerate password
       document.getElementById('regeneratePassword').addEventListener('click', generatePassword);
       
       // Copy generated password
       document.getElementById('copyGeneratedPassword').addEventListener('click', function() {
           const password = document.getElementById('generatedPassword').value;
           navigator.clipboard.writeText(password).then(() => {
               showToast('Başarılı', 'Şifre panoya kopyalandı.', 'success');
           }).catch(() => {
               showToast('Hata', 'Şifre kopyalanamadı.', 'error');
           });
       });
       
       // Use generated password
       document.getElementById('useGeneratedPassword').addEventListener('click', function() {
           const password = document.getElementById('generatedPassword').value;
           document.getElementById('password').value = password;
           generatorModal.classList.remove('active');
           checkPasswordStrength();
       });
       
       // Password generator option changes
       document.getElementById('includeUppercase').addEventListener('change', generatePassword);
       document.getElementById('includeLowercase').addEventListener('change', generatePassword);
       document.getElementById('includeNumbers').addEventListener('change', generatePassword);
       document.getElementById('includeSymbols').addEventListener('change', generatePassword);
       document.getElementById('passwordLength').addEventListener('change', generatePassword);
       
       // Toast notification
       function showToast(title, message, type = 'info') {
           const toastContainer = document.getElementById('toastContainer');
           
           const toast = document.createElement('div');
           toast.className = `toast toast-${type}`;
           toast.innerHTML = `
               <div class="toast-icon">
                   <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation' : 'info'}-circle"></i>
               </div>
               <div class="toast-content">
                   <div class="toast-title">${title}</div>
                   <div class="toast-message">${message}</div>
               </div>
               <div class="toast-close">
                   <i class="fas fa-times"></i>
               </div>
           `;
           
           toastContainer.appendChild(toast);
           
           // Auto close after 5 seconds
           setTimeout(() => {
               toast.classList.add('hide');
               setTimeout(() => {
                   toast.remove();
               }, 300);
           }, 5000);
           
           // Close button
           toast.querySelector('.toast-close').addEventListener('click', function() {
               toast.classList.add('hide');
               setTimeout(() => {
                   toast.remove();
               }, 300);
           });
       }
   </script>
</body>
</html>