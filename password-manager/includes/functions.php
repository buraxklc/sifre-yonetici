<?php
require_once 'db.php';

// Kullanıcı kaydı oluşturma fonksiyonu
function registerUser($username, $email, $password) {
    $conn = connectDB();
    
    // Kullanıcı adı ve e-posta kontrolü
    $checkQuery = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return "Bu kullanıcı adı veya e-posta zaten kullanılıyor.";
    }
    
    // Şifreyi güvenli şekilde hashle
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Kullanıcıyı kaydet
    $query = "INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $username, $email, $hashedPassword);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return "Kayıt işlemi sırasında bir hata oluştu.";
    }
}

// Kullanıcı girişi fonksiyonu
function loginUser($username, $password) {
    $conn = connectDB();
    
    $query = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Şifre doğru, oturum bilgilerini kaydet
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        } else {
            return "Şifre yanlış.";
        }
    } else {
        return "Kullanıcı bulunamadı.";
    }
}

// Şifre ekleme fonksiyonu
function addPassword($userId, $title, $username, $password, $url, $notes, $category) {
    $conn = connectDB();
    
    // Şifreyi veritabanına ekle
    $query = "INSERT INTO passwords (user_id, title, username, password, url, notes, category, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issssss", $userId, $title, $username, $password, $url, $notes, $category);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return "Şifre eklenirken bir hata oluştu.";
    }
}

// Kullanıcının şifrelerini getirme fonksiyonu
function getUserPasswords($userId, $category = null) {
    $conn = connectDB();
    
    if ($category) {
        $query = "SELECT * FROM passwords WHERE user_id = ? AND category = ? ORDER BY title ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $userId, $category);
    } else {
        $query = "SELECT * FROM passwords WHERE user_id = ? ORDER BY title ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $passwords = [];
    while ($row = $result->fetch_assoc()) {
        $passwords[] = $row;
    }
    
    return $passwords;
}

// Rastgele güçlü şifre oluşturma fonksiyonu
function generatePassword($length = 12, $useUpper = true, $useLower = true, $useNumbers = true, $useSymbols = true) {
    $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lower = 'abcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $symbols = '!@#$%^&*()-_=+[]{};:,.<>?';
    
    $chars = '';
    if ($useUpper) $chars .= $upper;
    if ($useLower) $chars .= $lower;
    if ($useNumbers) $chars .= $numbers;
    if ($useSymbols) $chars .= $symbols;
    
    // En az bir karakter seti kullanılmalı
    if (empty($chars)) {
        $chars = $lower . $numbers;
    }
    
    $password = '';
    $charsLength = strlen($chars);
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, $charsLength - 1)];
    }
    
    return $password;
}

// Şifre güç derecesini kontrol etme
function checkPasswordStrength($password) {
    $score = 0;
    
    // Uzunluk kontrolü
    $length = strlen($password);
    if ($length >= 8) $score += 1;
    if ($length >= 12) $score += 1;
    
    // Karakter çeşitliliği kontrolü
    if (preg_match('/[A-Z]/', $password)) $score += 1;
    if (preg_match('/[a-z]/', $password)) $score += 1;
    if (preg_match('/[0-9]/', $password)) $score += 1;
    if (preg_match('/[^A-Za-z0-9]/', $password)) $score += 1;
    
    // Güç derecesi döndürme
    if ($score < 3) return 'zayıf';
    if ($score < 5) return 'orta';
    return 'güçlü';
}
?>