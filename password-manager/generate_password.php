<?php
require_once 'includes/functions.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

// Şifre oluşturmak için talep edildiyse
if (isset($_POST['save_password'])) {
    $title = trim($_POST['title']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $url = trim($_POST['url']);
    $notes = trim($_POST['notes']);
    $category = trim($_POST['category']);
    
    // Form doğrulama
    if (empty($title) || empty($username) || empty($password)) {
        $error = "Lütfen en azından başlık, kullanıcı adı ve şifre alanlarını doldurun.";
    } else {
        // Şifre kaydetme
        $result = addPassword($_SESSION['user_id'], $title, $username, $password, $url, $notes, $category);
        
        if ($result === true) {
            $success = "Şifre başarıyla kaydedildi.";
            // Formu temizle
            $_POST = [];
        } else {
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
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Oluşturucu - SafeVault</title>
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
        
        .header-right {
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
        
        /* Password Generator */
        .generator-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 22px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            font-size: 22px;
            color: var(--primary);
            margin-right: 10px;
        }
        
        .section-description {
            color: var(--gray-600);
            font-size: 15px;
            margin-bottom: 24px;
            max-width: 800px;
        }
        
        .generator-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .generator-header {
            padding: 24px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
        }
        
        .generator-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .generator-title i {
            font-size: 22px;
            margin-right: 10px;
        }
        
        .generator-description {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .generator-body {
            padding: 24px;
        }
        
        .generator-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .option-group {
            margin-bottom: 20px;
        }
        
        .option-label {
            font-size: 15px;
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .option-value {
            font-weight: 600;
            background-color: var(--primary-light);
            color: var(--white);
            padding: 2px 8px;
            border-radius: 50px;
            font-size: 14px;
        }
        
        .length-slider {
            width: 100%;
            height: 8px;
            background-color: var(--gray-200);
            border-radius: 10px;
            appearance: none;
            outline: none;
        }
        
        .length-slider::-webkit-slider-thumb {
            appearance: none;
            width: 20px;
            height: 20px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .checkbox-group {
            margin-bottom: 16px;
        }
        
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
            background-color: var(--gray-100);
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
            background-color: var(--gray-200);
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
            font-size: 15px;
            color: var(--dark);
        }
        
        .generator-result {
            position: relative;
            margin-bottom: 24px;
        }
        
        .result-label {
            font-size: 15px;
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .result-input {
            width: 100%;
            height: 60px;
            padding: 0 120px 0 16px;
            border: 2px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 18px;
            color: var(--dark);
            background-color: var(--white);
            font-family: monospace;
            transition: var(--transition);
        }
        
        .result-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
            outline: none;
        }
        
        .result-actions {
            position: absolute;
            right: 10px;
            top: 45px;
            display: flex;
            gap: 8px;
        }
        
        .result-action {
            width: 40px;
            height: 40px;
            border-radius: var(--border-radius-sm);
            background-color: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-600);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .result-action:hover {
            background-color: var(--gray-200);
            color: var(--dark);
        }
        
        .refresh-button {
            background-color: var(--primary-light);
            color: var(--white);
        }
        
        .refresh-button:hover {
            background-color: var(--primary);
            color: var(--white);
        }
        
        .generator-buttons {
            display: flex;
            gap: 16px;
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
            border: none;
            outline: none;
            flex: 1;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(79, 70, 229, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-outline {
            background-color: var(--white);
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-outline:hover {
            background-color: rgba(79, 70, 229, 0.05);
        }
        
        /* Password Strength Meter */
        .strength-meter {
            margin-top: 16px;
        }
        
        .strength-label {
            font-size: 14px;
            color: var(--gray-600);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .strength-text {
            font-weight: 600;
        }
        
        .strength-text.weak {
            color: var(--danger);
        }
        
        .strength-text.medium {
            color: var(--warning);
        }
        
        .strength-text.strong {
            color: var(--success);
        }
        
        .strength-bar {
            height: 8px;
            background-color: var(--gray-200);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        
        .strength-fill.weak {
            width: 33%;
            background: linear-gradient(90deg, var(--danger) 0%, #FDA4AF 100%);
        }
        
        .strength-fill.medium {
            width: 67%;
            background: linear-gradient(90deg, var(--warning) 0%, #FCD34D 100%);
        }
        
        .strength-fill.strong {
            width: 100%;
            background: linear-gradient(90deg, var(--success) 0%, #6EE7B7 100%);
        }
        
        /* Save Form */
        .save-section {
            margin-top: 40px;
        }
        
        .form-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        
        .form-card-body {
            padding: 24px;
        }
        
        .form-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-size: 15px;
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 8px;
            display: inline-block;
        }
        
        .form-control {
            width: 100%;
            height: 48px;
            padding: 0 16px;
            border: 2px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 15px;
            color: var(--dark);
            background-color: var(--white);
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
            outline: none;
        }
        
        .form-control.is-invalid {
            border-color: var(--danger);
        }
        
        .invalid-feedback {
            font-size: 13px;
            color: var(--danger);
            margin-top: 5px;
        }
        
        textarea.form-control {
            height: auto;
            padding: 12px 16px;
            resize: vertical;
            min-height: 100px;
        }
        
        .form-text {
            font-size: 13px;
            color: var(--gray-500);
            margin-top: 5px;
        }
        
        .form-actions {
            margin-top: 30px;
            display: flex;
            gap: 16px;
        }
        
        /* Alerts */
        .alert {
            padding: 16px;
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
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
            flex-shrink: 0;
        }
        
        .alert-message {
            font-size: 15px;
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
            .generator-options {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
        
        /* Toast */
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
            width: 300px;
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
                           <a href="generate_password.php" class="menu-link active">
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
               <div class="page-title">Şifre Oluşturucu</div>
               <div class="header-right">
                   <div class="header-action" id="helpToggle">
                       <i class="fas fa-question-circle"></i>
                   </div>
               </div>
           </div>
           
           <!-- Content -->
           <div class="content">
               <div class="generator-section">
                   <h1 class="section-title">
                       <i class="fas fa-dice"></i>
                       Güçlü Şifre Oluşturucu
                   </h1>
                   <p class="section-description">
                       Güvenli ve eşsiz şifreler oluşturun. Güçlü şifreler büyük-küçük harfler, sayılar ve özel karakterler içerir ve en az 12 karakter uzunluğunda olmalıdır. Şifreleriniz SafeVault'da güvenle saklanır.
                   </p>
                   
                   <?php if (!empty($error)): ?>
                   <div class="alert alert-danger">
                       <div class="alert-icon">
                           <i class="fas fa-exclamation-circle"></i>
                       </div>
                       <div class="alert-message"><?php echo $error; ?></div>
                   </div>
                   <?php endif; ?>
                   
                   <?php if (!empty($success)): ?>
                   <div class="alert alert-success">
                       <div class="alert-icon">
                           <i class="fas fa-check-circle"></i>
                       </div>
                       <div class="alert-message"><?php echo $success; ?></div>
                   </div>
                   <?php endif; ?>
                   
                   <div class="generator-card">
                       <div class="generator-header">
                           <div class="generator-title">
                               <i class="fas fa-key"></i>
                               Şifre Oluşturucu
                           </div>
                           <div class="generator-description">
                               Güçlü ve güvenli şifreler oluşturmak için aşağıdaki ayarları özelleştirin.
                           </div>
                       </div>
                       <div class="generator-body">
                           <div class="generator-options">
                               <div>
                                   <div class="option-group">
                                       <div class="option-label">
                                           Şifre Uzunluğu
                                           <span class="option-value" id="lengthValue">16</span>
                                       </div>
                                       <input type="range" min="8" max="32" value="16" class="length-slider" id="passwordLength">
                                   </div>
                               </div>
                               
                               <div>
                                   <div class="checkbox-group">
                                       <label class="custom-checkbox">
                                           <input type="checkbox" id="uppercaseCheck" checked>
                                           <span class="checkmark"></span>
                                           <span class="checkbox-label">Büyük Harfler (A-Z)</span>
                                       </label>
                                   </div>
                                   <div class="checkbox-group">
                                       <label class="custom-checkbox">
                                           <input type="checkbox" id="lowercaseCheck" checked>
                                           <span class="checkmark"></span>
                                           <span class="checkbox-label">Küçük Harfler (a-z)</span>
                                       </label>
                                   </div>
                                   <div class="checkbox-group">
                                       <label class="custom-checkbox">
                                           <input type="checkbox" id="numbersCheck" checked>
                                           <span class="checkmark"></span>
                                           <span class="checkbox-label">Sayılar (0-9)</span>
                                       </label>
                                   </div>
                                   <div class="checkbox-group">
                                       <label class="custom-checkbox">
                                           <input type="checkbox" id="symbolsCheck" checked>
                                           <span class="checkmark"></span>
                                           <span class="checkbox-label">Özel Karakterler (!@#$%^&*)</span>
                                       </label>
                                   </div>
                               </div>
                           </div>
                           
                           <div class="generator-result">
                               <div class="result-label">Oluşturulan Şifre</div>
                               <input type="text" id="generatedPassword" class="result-input" value="" readonly>
                               <div class="result-actions">
                                   <div class="result-action" id="togglePasswordVisibility" title="Göster/Gizle">
                                       <i class="fas fa-eye"></i>
                                   </div>
                                   <div class="result-action" id="copyPassword" title="Kopyala">
                                       <i class="fas fa-copy"></i>
                                   </div>
                                   <div class="result-action refresh-button" id="refreshPassword" title="Yeni Oluştur">
                                       <i class="fas fa-sync-alt"></i>
                                   </div>
                               </div>
                           </div>
                           
                           <div class="strength-meter">
                               <div class="strength-label">
                                   Şifre Gücü
                                   <span class="strength-text" id="strengthText">Güçlü</span>
                               </div>
                               <div class="strength-bar">
                                   <div class="strength-fill" id="strengthFill"></div>
                               </div>
                           </div>
                           
                           <div class="generator-buttons">
                               <button class="btn btn-primary" id="generateBtn">
                                   <i class="fas fa-sync-alt"></i>
                                   Yeni Şifre Oluştur
                               </button>
                               <button class="btn btn-outline" id="showSaveFormBtn">
                                   <i class="fas fa-save"></i>
                                   Bu Şifreyi Kaydet
                               </button>
                           </div>
                       </div>
                   </div>
               </div>
               
               <div class="save-section" id="saveSection" style="display: none;">
                   <h2 class="section-title">
                       <i class="fas fa-save"></i>
                       Şifreyi Kaydet
                   </h2>
                   <p class="section-description">
                       Oluşturduğunuz şifreyi güvenli bir şekilde saklamak için aşağıdaki bilgileri doldurun.
                   </p>
                   
                   <div class="form-card">
                       <div class="form-card-body">
                           <h3 class="form-title">Şifre Bilgileri</h3>
                           
                           <form method="POST" action="" id="savePasswordForm">
                               <div class="form-row">
                                   <div class="form-group">
                                       <label for="title" class="form-label">Site/Uygulama Adı <span style="color: var(--danger);">*</span></label>
                                       <input type="text" id="title" name="title" class="form-control" placeholder="Örn: Google, Netflix, Instagram" required>
                                   </div>
                                   
                                   <div class="form-group">
                                       <label for="username" class="form-label">Kullanıcı Adı/E-posta <span style="color: var(--danger);">*</span></label>
                                       <input type="text" id="username" name="username" class="form-control" placeholder="Kullanıcı adınız veya e-posta adresiniz" required>
                                   </div>
                               </div>
                               
                               <div class="form-group">
                                   <label for="category" class="form-label">Kategori</label>
                                   <input type="text" id="category" name="category" class="form-control" list="categories" placeholder="Kategori seçin veya yeni ekleyin">
                                   <datalist id="categories">
                                       <?php foreach ($categories as $cat): ?>
                                       <option value="<?php echo htmlspecialchars($cat); ?>">
                                       <?php endforeach; ?>
                                   </datalist>
                                   <div class="form-text">Şifreleri kategorilere ayırmak, daha sonra bulmanızı kolaylaştırır.</div>
                               </div>
                               
                               <div class="form-group">
                                   <label for="password" class="form-label">Şifre <span style="color: var(--danger);">*</span></label>
                                   <input type="password" id="password" name="password" class="form-control" required>
                               </div>
                               
                               <div class="form-group">
                                   <label for="url" class="form-label">Website URL</label>
                                   <input type="url" id="url" name="url" class="form-control" placeholder="https://example.com">
                                   <div class="form-text">Tam URL, site ikonunu otomatik olarak getirir.</div>
                               </div>
                               
                               <div class="form-group">
                                   <label for="notes" class="form-label">Notlar</label>
                                   <textarea id="notes" name="notes" class="form-control" placeholder="İsteğe bağlı notlar ekleyin..."></textarea>
                               </div>
                               
                               <input type="hidden" name="save_password" value="1">
                               
                               <div class="form-actions">
                                   <button type="submit" class="btn btn-primary">
                                       <i class="fas fa-save"></i>
                                       Şifreyi Kaydet
                                   </button>
                                   <button type="button" class="btn btn-outline" id="cancelSaveBtn">
                                       <i class="fas fa-times"></i>
                                       İptal
                                   </button>
                               </div>
                           </form>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </div>
   
   <!-- Toast Notifications -->
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
       
       // Close dropdowns when clicking outside
       document.addEventListener('click', function(e) {
           const userMenu = document.getElementById('userMenu');
           if (userMenu.classList.contains('active') && !e.target.closest('#userMenuToggle')) {
               userMenu.classList.remove('active');
           }
       });
       
       // Password Generator
       const passwordLengthSlider = document.getElementById('passwordLength');
       const lengthValueDisplay = document.getElementById('lengthValue');
       const generateBtn = document.getElementById('generateBtn');
       const refreshBtn = document.getElementById('refreshPassword');
       const generatedPasswordEl = document.getElementById('generatedPassword');
       const copyPasswordBtn = document.getElementById('copyPassword');
       const togglePasswordBtn = document.getElementById('togglePasswordVisibility');
       const strengthText = document.getElementById('strengthText');
       const strengthFill = document.getElementById('strengthFill');
       
       // Update length display
       passwordLengthSlider.addEventListener('input', function() {
           lengthValueDisplay.textContent = this.value;
       });
       
       // Generate password
       function generatePassword() {
           const length = passwordLengthSlider.value;
           const useUppercase = document.getElementById('uppercaseCheck').checked;
           const useLowercase = document.getElementById('lowercaseCheck').checked;
           const useNumbers = document.getElementById('numbersCheck').checked;
           const useSymbols = document.getElementById('symbolsCheck').checked;
           
           let charset = '';
           if (useUppercase) charset += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
           if (useLowercase) charset += 'abcdefghijklmnopqrstuvwxyz';
           if (useNumbers) charset += '0123456789';
           if (useSymbols) charset += '!@#$%^&*()-_=+[]{}|;:,.<>?';
           
           // Ensure at least one character set is selected
           if (!charset) {
               charset = 'abcdefghijklmnopqrstuvwxyz0123456789';
               document.getElementById('lowercaseCheck').checked = true;
               document.getElementById('numbersCheck').checked = true;
           }
           
           let password = '';
           for (let i = 0; i < length; i++) {
               const randomIndex = Math.floor(Math.random() * charset.length);
               password += charset[randomIndex];
           }
           
           generatedPasswordEl.value = password;
           document.getElementById('password').value = password;
           
           // Check password strength
           checkPasswordStrength(password);
       }
       
       function checkPasswordStrength(password) {
           // Calculate password strength score
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
           strengthFill.classList.remove('weak', 'medium', 'strong');
           strengthText.classList.remove('weak', 'medium', 'strong');
           
           if (score < 3) {
               strengthFill.classList.add('weak');
               strengthText.classList.add('weak');
               strengthText.textContent = 'Zayıf';
           } else if (score < 5) {
               strengthFill.classList.add('medium');
               strengthText.classList.add('medium');
               strengthText.textContent = 'Orta';
           } else {
               strengthFill.classList.add('strong');
               strengthText.classList.add('strong');
               strengthText.textContent = 'Güçlü';
           }
       }
       
       // Generate initial password
       generatePassword();
       
       // Generate new password on button click
       generateBtn.addEventListener('click', generatePassword);
       refreshBtn.addEventListener('click', generatePassword);
       
       // Toggle password visibility
       togglePasswordBtn.addEventListener('click', function() {
           const type = generatedPasswordEl.getAttribute('type') === 'password' ? 'text' : 'password';
           generatedPasswordEl.setAttribute('type', type);
           this.querySelector('i').classList.toggle('fa-eye');
           this.querySelector('i').classList.toggle('fa-eye-slash');
       });
       
       // Copy generated password
       copyPasswordBtn.addEventListener('click', function() {
           const password = generatedPasswordEl.value;
           navigator.clipboard.writeText(password).then(() => {
               showToast('Başarılı', 'Şifre panoya kopyalandı.', 'success');
           }).catch(() => {
               showToast('Hata', 'Şifre kopyalanamadı.', 'error');
           });
       });
       
       // Update password when any option changes
       document.getElementById('uppercaseCheck').addEventListener('change', generatePassword);
       document.getElementById('lowercaseCheck').addEventListener('change', generatePassword);
       document.getElementById('numbersCheck').addEventListener('change', generatePassword);
       document.getElementById('symbolsCheck').addEventListener('change', generatePassword);
       passwordLengthSlider.addEventListener('change', generatePassword);
       
       // Show save form
       document.getElementById('showSaveFormBtn').addEventListener('click', function() {
           document.getElementById('saveSection').style.display = 'block';
           document.getElementById('saveSection').scrollIntoView({ behavior: 'smooth' });
       });
       
       // Cancel save
       document.getElementById('cancelSaveBtn').addEventListener('click', function() {
           document.getElementById('saveSection').style.display = 'none';
           window.scrollTo({ top: 0, behavior: 'smooth' });
       });
       
       // Toast notification function
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
       
       // Pre-fill form if password is provided in URL
       <?php if (isset($_GET['password'])): ?>
       window.addEventListener('DOMContentLoaded', function() {
           document.getElementById('password').value = <?php echo json_encode($_GET['password']); ?>;
           document.getElementById('saveSection').style.display = 'block';
       });
       <?php endif; ?>
   </script>
</body>
</html>