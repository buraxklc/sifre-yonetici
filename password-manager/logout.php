<?php
require_once 'includes/config.php';

// Oturumu sonlandır
session_unset();
session_destroy();

// Login sayfasına yönlendir
header("Location: login.php");
exit();
?>