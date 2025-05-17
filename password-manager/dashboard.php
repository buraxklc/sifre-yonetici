<?php
require_once 'includes/functions.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Kategori filtresi
$category = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Şifreleri getir
$passwords = getUserPasswords($_SESSION['user_id'], $category);

// Kategorileri getir
$categories = [];
$allPasswords = getUserPasswords($_SESSION['user_id']);
foreach ($allPasswords as $pass) {
    if (!empty($pass['category']) && !in_array($pass['category'], $categories)) {
        $categories[] = $pass['category'];
    }
}

// İstatistikler
$stats = [
    'total' => count($allPasswords),
    'strong' => 0,
    'medium' => 0,
    'weak' => 0
];

foreach ($allPasswords as $pass) {
    $strength = checkPasswordStrength($pass['password']);
    if ($strength === 'güçlü') {
        $stats['strong']++;
    } elseif ($strength === 'orta') {
        $stats['medium']++;
    } else {
        $stats['weak']++;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SafeVault</title>
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
        
        .search-box {
            position: relative;
            margin-right: 20px;
        }
        
        .search-input {
            height: 40px;
            padding: 0 40px 0 16px;
            width: 240px;
            border: 1px solid var(--gray-200);
            border-radius: var(--border-radius-sm);
            font-size: 14px;
            color: var(--dark);
            background-color: var(--white);
            transition: var(--transition);
            outline: none;
        }
        
        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        
        .search-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
            font-size: 16px;
        }
        
        .header-actions {
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
        
        .header-action.has-notifications::after {
            content: '';
            position: absolute;
            width: 8px;
            height: 8px;
            background-color: var(--danger);
            border-radius: 50%;
            top: 8px;
            right: 8px;
        }
        
        .content {
            padding: var(--content-padding);
        }
        
        /* Stats */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 36px;
        }
        
        .stat-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            padding: 24px;
            transition: var(--transition);
            cursor: pointer;
        }
        
        .stat-card:hover {
            box-shadow: var(--shadow);
            transform: translateY(-2px);
        }
        
        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        
        .stat-title {
            font-size: 15px;
            font-weight: 500;
            color: var(--gray-600);
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 18px;
        }
        
        .stat-icon-total {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        }
        
        .stat-icon-strong {
            background: linear-gradient(135deg, var(--success) 0%, #68D391 100%);
        }
        
        .stat-icon-medium {
            background: linear-gradient(135deg, var(--warning) 0%, #F6E05E 100%);
        }
        
        .stat-icon-weak {
            background: linear-gradient(135deg, var(--danger) 0%, #FC8181 100%);
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
        }
        
        .stat-footer {
            font-size: 13px;
            color: var(--gray-500);
            margin-top: 8px;
        }
        
        /* Categories */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .section-actions {
            display: flex;
            align-items: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius-sm);
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary:hover {
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
            transform: translateY(-2px);
        }
        
        .btn-icon {
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
            margin-left: 10px;
        }
        
        .btn-icon:hover {
            background-color: var(--gray-200);
            color: var(--dark);
        }
        
        .categories-row {
            display: flex;
            gap: 16px;
            margin-bottom: 36px;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 8px;
        }
        
        .categories-row::-webkit-scrollbar {
            height: 6px;
        }
        
        .categories-row::-webkit-scrollbar-track {
            background: var(--gray-100);
            border-radius: 10px;
        }
        
        .categories-row::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 10px;
        }
        
        .categories-row::-webkit-scrollbar-thumb:hover {
            background: var(--gray-400);
        }
        
        .category-card {
            min-width: 180px;
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: var(--transition);
            cursor: pointer;
            border: 1px solid var(--gray-200);
        }
        
        .category-card:hover {
            box-shadow: var(--shadow);
            transform: translateY(-2px);
        }
        
        .category-card.active {
            border: 2px solid var(--primary);
        }
        
        .category-top {
            height: 6px;
        }
        
        .category-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .category-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            color: var(--white);
            font-size: 20px;
        }
        
        .category-name {
            font-size: 15px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
        }
        
        .category-count {
            font-size: 13px;
            color: var(--gray-500);
        }
        
        /* Passwords */
        .passwords-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }
        
        .password-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            overflow: hidden;
            position: relative;
        }
        
        .password-card:hover {
            box-shadow: var(--shadow);
            transform: translateY(-2px);
        }
        
        .password-top {
            height: 4px;
        }
        
        .password-body {
            padding: 20px;
        }
        
        .password-header {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .site-icon {
            width: 42px;
            height: 42px;
            border-radius: var(--border-radius-sm);
            background-color: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-right: 12px;
        }
        
        .site-icon img {
            width: 16px;
            height: 16px;
            object-fit: contain;
        }
        
        .password-info {
            flex: 1;
        }
        
        .password-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 2px;
        }
        
        .password-username {
            font-size: 14px;
            color: var(--gray-500);
        }
        
        .password-actions {
            position: absolute;
            top: 16px;
            right: 16px;
        }
        
        .password-actions-menu {
            width: 32px;
            height: 32px;
            border-radius: var(--border-radius-sm);
            background-color: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--gray-500);
            transition: var(--transition);
        }
        
        .password-actions-menu:hover {
            background-color: var(--gray-200);
            color: var(--dark);
        }
        
        .password-field {
            margin-bottom: 12px;
        }
        
        .password-label {
            font-size: 13px;
            color: var(--gray-500);
            margin-bottom: 6px;
        }
        
        .password-value {
            position: relative;
        }
        
        .password-input {
            width: 100%;
            height: 40px;
            padding: 0 70px 0 12px;
            border: 1px solid var(--gray-200);
            border-radius: var(--border-radius-sm);
            font-size: 14px;
            color: var(--dark);
            background-color: var(--gray-100);
            font-family: monospace;
            letter-spacing: 2px;
        }
        
        .password-controls {
            position: absolute;
            top: 0;
            right: 0;
            height: 40px;
            display: flex;
        }
        
        .password-control {
            width: 34px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--gray-500);
            transition: var(--transition);
        }
        
        .password-control:hover {
            color: var(--dark);
        }
        
        .password-divider {
            height: 20px;
            width: 1px;
            background-color: var(--gray-200);
            margin: 10px 0;
        }
        
        .password-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 12px;
            border-top: 1px solid var(--gray-200);
            margin-top: 16px;
        }
        
        .password-meta {
            display: flex;
            align-items: center;
        }
        
        .password-date {
            font-size: 13px;
            color: var(--gray-500);
            display: flex;
            align-items: center;
        }
        
        .password-date i {
            margin-right: 6px;
            font-size: 12px;
        }
        
        .password-strength {
            margin-left: 12px;
            font-size: 12px;
            font-weight: 500;
            padding: 2px 8px;
            border-radius: 50px;
        }
        
        .password-strength.strong {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        
        .password-strength.medium {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        
        .password-strength.weak {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }
        
        .password-category {
            font-size: 12px;
            font-weight: 500;
            padding: 2px 8px;
            border-radius: 50px;
            background-color: var(--gray-100);
            color: var(--gray-600);
        }
        
        /* Empty State */
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }
        
        .empty-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: var(--gray-500);
            margin: 0 auto 20px;
        }
        
        .empty-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .empty-message {
            font-size: 15px;
            color: var(--gray-500);
            max-width: 400px;
            margin: 0 auto 24px;
        }
        
        /* Quick Actions */
        .quick-actions {
            margin-bottom: 36px;
        }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        
        .action-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            padding: 20px;
            display: flex;
            align-items: center;
            transition: var(--transition);
            cursor: pointer;
            border: 1px solid var(--gray-200);
        }
        
        .action-card:hover {
            box-shadow: var(--shadow);
            transform: translateY(-2px);
            border-color: var(--primary-light);
        }
        
        .action-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            flex-shrink: 0;
            font-size: 20px;
            color: var(--white);
        }
        
        .action-create {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        }
        
        .action-generate {
            background: linear-gradient(135deg, var(--success) 0%, #68D391 100%);
        }
        
        .action-security {
            background: linear-gradient(135deg, var(--warning) 0%, #F6E05E 100%);
        }
        
        .action-import {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
        }
        
        .action-content {
            flex: 1;
        }
        
        .action-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 2px;
        }
        
        .action-description {
            font-size: 13px;
            color: var(--gray-500);
        }
        
        /* Responsive */
        @media (max-width: 991px) {
            :root {
                --content-padding: 20px;
            }
            
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
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
           .stats-row {
               grid-template-columns: 1fr;
           }
           
           .search-input {
               width: 180px;
           }
           
           .action-grid {
               grid-template-columns: 1fr;
           }
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
       
       /* Toast Notifications */
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
       
       .btn-cancel {
           height: 40px;
           background-color: var(--gray-100);
           color: var(--gray-700);
           border: 1px solid var(--gray-300);
           border-radius: var(--border-radius-sm);
           font-size: 14px;
           font-weight: 500;
           cursor: pointer;
           transition: var(--transition);
           display: flex;
           align-items: center;
           justify-content: center;
           padding: 0 16px;
       }
       
       .btn-cancel:hover {
           background-color: var(--gray-200);
           color: var(--dark);
       }
       
       .btn-confirm {
           height: 40px;
           background: linear-gradient(135deg, var(--danger) 0%, #F87171 100%);
           color: var(--white);
           border: none;
           border-radius: var(--border-radius-sm);
           font-size: 14px;
           font-weight: 500;
           cursor: pointer;
           transition: var(--transition);
           display: flex;
           align-items: center;
           justify-content: center;
           padding: 0 16px;
       }
       
       .btn-confirm:hover {
           box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
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
                           <a href="dashboard.php" class="menu-link active">
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
                               <span class="menu-badge"><?php echo $stats['total']; ?></span>
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
               <div class="page-title">Dashboard</div>
               <div class="header-right">
                   <div class="search-box">
                       <input type="text" class="search-input" id="searchInput" placeholder="Şifre ara...">
                       <div class="search-icon">
                           <i class="fas fa-search"></i>
                       </div>
                   </div>
                   <div class="header-actions">
                       <div class="header-action has-notifications" id="notificationsToggle">
                           <i class="fas fa-bell"></i>
                       </div>
                       <div class="header-action" id="helpToggle">
                           <i class="fas fa-question-circle"></i>
                       </div>
                   </div>
               </div>
           </div>
           
           <!-- Content -->
           <div class="content">
               <!-- Stats -->
               <div class="stats-row">
                   <div class="stat-card">
                       <div class="stat-header">
                           <div class="stat-title">Toplam Şifre</div>
                           <div class="stat-icon stat-icon-total">
                               <i class="fas fa-lock"></i>
                           </div>
                       </div>
                       <div class="stat-value"><?php echo $stats['total']; ?></div>
                       <div class="stat-footer">Tüm kayıtlı şifreleriniz</div>
                   </div>
                   
                   <div class="stat-card">
                       <div class="stat-header">
                           <div class="stat-title">Güçlü Şifreler</div>
                           <div class="stat-icon stat-icon-strong">
                               <i class="fas fa-shield-alt"></i>
                           </div>
                       </div>
                       <div class="stat-value"><?php echo $stats['strong']; ?></div>
                       <div class="stat-footer">Yüksek güvenlik seviyesi</div>
                   </div>
                   
                   <div class="stat-card">
                       <div class="stat-header">
                           <div class="stat-title">Orta Şifreler</div>
                           <div class="stat-icon stat-icon-medium">
                               <i class="fas fa-check-circle"></i>
                           </div>
                       </div>
                       <div class="stat-value"><?php echo $stats['medium']; ?></div>
                       <div class="stat-footer">Kabul edilebilir güvenlik</div>
                   </div>
                   
                   <div class="stat-card">
                       <div class="stat-header">
                           <div class="stat-title">Zayıf Şifreler</div>
                           <div class="stat-icon stat-icon-weak">
                               <i class="fas fa-exclamation-triangle"></i>
                           </div>
                       </div>
                       <div class="stat-value"><?php echo $stats['weak']; ?></div>
                       <div class="stat-footer">Güçlendirilmesi gereken</div>
                   </div>
               </div>
               
               <!-- Quick Actions -->
               <div class="quick-actions">
                   <div class="section-header">
                       <div class="section-title">Hızlı İşlemler</div>
                   </div>
                   
                   <div class="action-grid">
                       <a href="add_password.php" class="action-card">
                           <div class="action-icon action-create">
                               <i class="fas fa-plus"></i>
                           </div>
                           <div class="action-content">
                               <div class="action-title">Yeni Şifre Ekle</div>
                               <div class="action-description">Hesap bilgilerinizi güvenle saklayın</div>
                           </div>
                       </a>
                       
                       <a href="generate_password.php" class="action-card">
                           <div class="action-icon action-generate">
                               <i class="fas fa-dice"></i>
                           </div>
                           <div class="action-content">
                               <div class="action-title">Şifre Oluştur</div>
                               <div class="action-description">Güçlü ve benzersiz şifreler oluşturun</div>
                           </div>
                       </a>
                       
                       <a href="security_check.php" class="action-card">
                           <div class="action-icon action-security">
                               <i class="fas fa-shield-alt"></i>
                           </div>
                           <div class="action-content">
                               <div class="action-title">Güvenlik Kontrolü</div>
                               <div class="action-description">Şifrelerinizin güvenliğini test edin</div>
                           </div>
                       </a>
                       
                       <a href="import_export.php" class="action-card">
                           <div class="action-icon action-import">
                               <i class="fas fa-exchange-alt"></i>
                           </div>
                           <div class="action-content">
                               <div class="action-title">İçe/Dışa Aktar</div>
                               <div class="action-description">Şifrelerinizi taşıyın veya yedekleyin</div>
                           </div>
                       </a>
                   </div>
               </div>
               
               <!-- Categories -->
               <div class="section-header">
                   <div class="section-title">Kategoriler</div>
                   <div class="section-actions">
                       <a href="categories.php" class="btn-primary">
                           <i class="fas fa-plus"></i>
                           Yeni Kategori
                       </a>
                   </div>
               </div>
               
               <div class="categories-row">
                   <div class="category-card <?php echo !$category ? 'active' : ''; ?>">
                       <div class="category-top" style="background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);"></div>
                       <div class="category-body">
                           <div class="category-icon" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);">
                               <i class="fas fa-layer-group"></i>
                           </div>
                           <div class="category-name">Tümü</div>
                           <div class="category-count"><?php echo $stats['total']; ?> şifre</div>
                       </div>
                   </div>
                   
                   <?php foreach ($categories as $cat): ?>
                   <?php 
                       $categoryColor = getCategoryColor($cat);
                       $categoryIcon = getCategoryIcon($cat);
                       $categoryCount = count(array_filter($allPasswords, function($pass) use ($cat) {
                           return $pass['category'] === $cat;
                       }));
                   ?>
                   <div class="category-card <?php echo $category === $cat ? 'active' : ''; ?>">
                       <div class="category-top" style="background: <?php echo $categoryColor; ?>"></div>
                       <div class="category-body">
                           <div class="category-icon" style="background: <?php echo $categoryColor; ?>">
                               <i class="<?php echo $categoryIcon; ?>"></i>
                           </div>
                           <div class="category-name"><?php echo htmlspecialchars($cat); ?></div>
                           <div class="category-count"><?php echo $categoryCount; ?> şifre</div>
                       </div>
                   </div>
                   <?php endforeach; ?>
               </div>
               
               <!-- Passwords -->
               <div class="section-header">
                   <div class="section-title">Son Eklenen Şifreler</div>
                   <div class="section-actions">
                       <a href="add_password.php" class="btn-primary">
                           <i class="fas fa-plus"></i>
                           Yeni Şifre
                       </a>
                       <a href="passwords.php" class="btn-icon">
                           <i class="fas fa-th-list"></i>
                       </a>
                   </div>
               </div>
               
               <?php if (empty($passwords)): ?>
               <div class="empty-state">
                   <div class="empty-icon">
                       <i class="fas fa-lock"></i>
                   </div>
                   <h3 class="empty-title">Henüz şifre eklenmemiş</h3>
                   <p class="empty-message">Güvenli bir şekilde saklamak için şifrelerinizi ekleyin. Tüm bilgileriniz şifrelenerek korunur.</p>
                   <a href="add_password.php" class="btn-primary">
                       <i class="fas fa-plus"></i>
                       İlk Şifreni Ekle
                   </a>
               </div>
               <?php else: ?>
               <div class="passwords-grid">
                   <?php 
                   // En son eklenen 6 şifreyi göster
                   $recentPasswords = array_slice($passwords, 0, 6);
                   foreach ($recentPasswords as $password): 
                       $strength = checkPasswordStrength($password['password']);
                       $strengthClass = '';
                       $strengthText = '';
                       
                       if ($strength === 'güçlü') {
                           $strengthClass = 'strong';
                           $strengthText = 'Güçlü';
                       } elseif ($strength === 'orta') {
                           $strengthClass = 'medium';
                           $strengthText = 'Orta';
                       } else {
                           $strengthClass = 'weak';
                           $strengthText = 'Zayıf';
                       }
                       
                       $categoryColor = getCategoryColor($password['category']);
                   ?>
                   <div class="password-card">
                       <div class="password-top" style="background: <?php echo $categoryColor; ?>"></div>
                       <div class="password-body">
                           <div class="password-header">
                               <div class="site-icon">
                                   <?php if (!empty($password['url'])): ?>
                                   <img src="https://www.google.com/s2/favicons?domain=<?php echo parse_url($password['url'], PHP_URL_HOST); ?>" alt="">
                                   <?php else: ?>
                                   <i class="fas fa-globe"></i>
                                   <?php endif; ?>
                               </div>
                               <div class="password-info">
                                   <div class="password-title"><?php echo htmlspecialchars($password['title']); ?></div>
                                   <div class="password-username"><?php echo htmlspecialchars($password['username']); ?></div>
                               </div>
                               <div class="password-actions">
                                   <div class="password-actions-menu" data-id="<?php echo $password['id']; ?>">
                                       <i class="fas fa-ellipsis-v"></i>
                                   </div>
                               </div>
                           </div>
                           
                           <div class="password-field">
                               <div class="password-label">Şifre</div>
                               <div class="password-value">
                                   <input type="password" class="password-input" value="<?php echo htmlspecialchars($password['password']); ?>" readonly>
                                   <div class="password-controls">
                                       <div class="password-control toggle-password">
                                           <i class="fas fa-eye"></i>
                                       </div>
                                       <div class="password-divider"></div>
                                       <div class="password-control copy-password" data-clipboard-text="<?php echo htmlspecialchars($password['password']); ?>">
                                           <i class="fas fa-copy"></i>
                                       </div>
                                   </div>
                               </div>
                           </div>
                           
                           <div class="password-footer">
                               <div class="password-meta">
                                   <div class="password-date">
                                       <i class="far fa-calendar-alt"></i>
                                       <?php echo date('d.m.Y', strtotime($password['created_at'])); ?>
                                   </div>
                                   <div class="password-strength <?php echo $strengthClass; ?>">
                                       <?php echo $strengthText; ?>
                                   </div>
                               </div>
                               <?php if (!empty($password['category'])): ?>
                               <div class="password-category" style="background: <?php echo $categoryColor; ?>20; color: <?php echo $categoryColor; ?>;">
                                   <?php echo htmlspecialchars($password['category']); ?>
                               </div>
                               <?php endif; ?>
                           </div>
                       </div>
                   </div>
                   <?php endforeach; ?>
               </div>
               <?php endif; ?>
           </div>
       </div>
   </div>
   
   <!-- Delete Confirmation Modal -->
   <div class="modal-backdrop" id="deleteModal">
       <div class="modal">
           <div class="modal-header">
               <div class="modal-title">Şifreyi Sil</div>
               <div class="modal-close" id="closeModal">
                   <i class="fas fa-times"></i>
               </div>
           </div>
           <div class="modal-body">
               <p>Bu şifreyi silmek istediğinize emin misiniz? Bu işlem geri alınamaz.</p>
           </div>
           <div class="modal-footer">
               <button class="btn-cancel" id="cancelDelete">İptal</button>
               <button class="btn-confirm" id="confirmDelete">Evet, Sil</button>
           </div>
       </div>
   </div>
   
   <!-- Toast Notifications -->
   <div class="toast-container" id="toastContainer"></div>
   
   <!-- JavaScript -->
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
       
       // Password visibility toggle
       document.querySelectorAll('.toggle-password').forEach(button => {
           button.addEventListener('click', function() {
               const passwordInput = this.closest('.password-value').querySelector('.password-input');
               const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
               passwordInput.setAttribute('type', type);
               this.querySelector('i').classList.toggle('fa-eye');
               this.querySelector('i').classList.toggle('fa-eye-slash');
           });
       });
       
       // Copy to clipboard
       document.querySelectorAll('.copy-password').forEach(button => {
           button.addEventListener('click', function() {
               const text = this.getAttribute('data-clipboard-text');
               navigator.clipboard.writeText(text).then(() => {
                   showToast('Başarılı', 'Şifre panoya kopyalandı.', 'success');
               }).catch(() => {
                   showToast('Hata', 'Şifre kopyalanamadı.', 'error');
               });
           });
       });
       
       // Delete confirmation modal
       let passwordToDelete = null;
       
       // Show delete modal
       document.querySelectorAll('.password-actions-menu').forEach(button => {
           button.addEventListener('click', function(e) {
               e.stopPropagation();
               const passwordId = this.getAttribute('data-id');
               
               // Create and show dropdown menu
               const dropdown = document.createElement('div');
               dropdown.className = 'dropdown-menu active';
               dropdown.style.position = 'absolute';
               dropdown.style.top = '40px';
               dropdown.style.right = '0';
               dropdown.innerHTML = `
                   <a href="edit_password.php?id=${passwordId}" class="dropdown-item">
                       <i class="fas fa-edit"></i>
                       Düzenle
                   </a>
                   <a href="#" class="dropdown-item copy-username" data-id="${passwordId}">
                       <i class="fas fa-user"></i>
                       Kullanıcı Adını Kopyala
                   </a>
                   <a href="#" class="dropdown-item copy-password-action" data-id="${passwordId}">
                       <i class="fas fa-key"></i>
                       Şifreyi Kopyala
                   </a>
                   <div class="dropdown-divider"></div>
                   <a href="#" class="dropdown-item danger delete-password" data-id="${passwordId}">
                       <i class="fas fa-trash-alt"></i>
                       Sil
                   </a>
               `;
               
               // Remove any existing dropdowns
               document.querySelectorAll('.dropdown-menu.active').forEach(menu => {
                   if (menu !== dropdown) {
                       menu.remove();
                   }
               });
               
               // Add to DOM
               this.appendChild(dropdown);
               
               // Click outside to close
               document.addEventListener('click', function closeDropdown(e) {
                   if (!e.target.closest('.dropdown-menu') && !e.target.closest('.password-actions-menu')) {
                       dropdown.remove();
                       document.removeEventListener('click', closeDropdown);
                   }
               });
               
               // Delete password action
               dropdown.querySelector('.delete-password').addEventListener('click', function(e) {
                   e.preventDefault();
                   passwordToDelete = this.getAttribute('data-id');
                   document.getElementById('deleteModal').classList.add('active');
                   dropdown.remove();
               });
           });
       });
       
       // Cancel delete
       document.getElementById('cancelDelete').addEventListener('click', function() {
           document.getElementById('deleteModal').classList.remove('active');
           passwordToDelete = null;
       });
       
       // Close modal button
       document.getElementById('closeModal').addEventListener('click', function() {
           document.getElementById('deleteModal').classList.remove('active');
           passwordToDelete = null;
       });
       
       // Confirm delete
       document.getElementById('confirmDelete').addEventListener('click', function() {
           if (passwordToDelete) {
               window.location.href = 'delete_password.php?id=' + passwordToDelete;
           }
       });
       
       // Search functionality
       document.getElementById('searchInput').addEventListener('keypress', function(e) {
           if (e.key === 'Enter') {
               const query = this.value.trim();
               if (query) {
                   window.location.href = 'passwords.php?search=' + encodeURIComponent(query);
               }
           }
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
       
       // Category cards click event (redirect)
       document.querySelectorAll('.category-card').forEach(card => {
           card.addEventListener('click', function() {
               const isAllCategory = !this.querySelector('.category-name').textContent.includes('Tümü');
               const categoryName = isAllCategory ? this.querySelector('.category-name').textContent : '';
               
               window.location.href = 'passwords.php' + (isAllCategory ? '?category=' + encodeURIComponent(categoryName) : '');
           });
       });
   </script>
   
   <?php
   // Kategori için ikon belirleme yardımcı fonksiyonu
   function getCategoryIcon($category) {
       $category = strtolower($category);
       
       if (strpos($category, 'sosyal') !== false) return 'fas fa-users';
       if (strpos($category, 'banka') !== false) return 'fas fa-university';
       if (strpos($category, 'mail') !== false || strpos($category, 'e-posta') !== false) return 'fas fa-envelope';
       if (strpos($category, 'alışveriş') !== false) return 'fas fa-shopping-cart';
       if (strpos($category, 'iş') !== false) return 'fas fa-briefcase';
       if (strpos($category, 'eğlence') !== false) return 'fas fa-film';
       if (strpos($category, 'oyun') !== false) return 'fas fa-gamepad';
       if (strpos($category, 'sağlık') !== false) return 'fas fa-heartbeat';
       
       // Varsayılan ikon
       return 'fas fa-folder';
   }

   // Kategori için renk belirleme yardımcı fonksiyonu
   function getCategoryColor($category) {
       $category = strtolower($category);
       
       if (strpos($category, 'sosyal') !== false) return 'linear-gradient(135deg, #4267B2 0%, #5B7EC2 100%)';
       if (strpos($category, 'banka') !== false) return 'linear-gradient(135deg, #10B981 0%, #34D399 100%)';
       if (strpos($category, 'mail') !== false || strpos($category, 'e-posta') !== false) return 'linear-gradient(135deg, #F43F5E 0%, #FB7185 100%)';
       if (strpos($category, 'alışveriş') !== false) return 'linear-gradient(135deg, #F97316 0%, #FB923C 100%)';
       if (strpos($category, 'iş') !== false) return 'linear-gradient(135deg, #0EA5E9 0%, #38BDF8 100%)';
       if (strpos($category, 'eğlence') !== false) return 'linear-gradient(135deg, #EC4899 0%, #F472B6 100%)';
       if (strpos($category, 'oyun') !== false) return 'linear-gradient(135deg, #8B5CF6 0%, #A78BFA 100%)';
       if (strpos($category, 'sağlık') !== false) return 'linear-gradient(135deg, #EF4444 0%, #F87171 100%)';
       
       // Varsayılan renk
       return 'linear-gradient(135deg, #6B7280 0%, #9CA3AF 100%)';
   }
   ?>
</body>
</html>