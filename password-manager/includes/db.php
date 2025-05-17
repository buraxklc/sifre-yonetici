<?php
require_once 'config.php';

// Veritabanı bağlantısı oluşturma
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Bağlantı hatası kontrolü
    if ($conn->connect_error) {
        die("Veritabanı bağlantı hatası: " . $conn->connect_error);
    }
    
    // Türkçe karakter desteği
    $conn->set_charset("utf8");
    
    return $conn;
}

// Veritabanı sorgularını güvenli hale getirme
function sanitizeInput($conn, $data) {
    return $conn->real_escape_string(htmlspecialchars(trim($data)));
}
?>