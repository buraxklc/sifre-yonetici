<?php
require_once 'includes/functions.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$password_id = $_GET['id'];

// Şifrenin kullanıcıya ait olduğunu doğrula
$conn = connectDB();
$query = "SELECT id FROM passwords WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $password_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    header("Location: dashboard.php");
    exit();
}

// Şifreyi sil
$query = "DELETE FROM passwords WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $password_id, $_SESSION['user_id']);
$stmt->execute();

// Ana sayfaya yönlendir
header("Location: dashboard.php?deleted=success");
exit();
?>