<?php
// Ana sayfa - Giriş yapmadıysa login sayfasına, yaptıysa dashboard'a yönlendir
require_once 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>