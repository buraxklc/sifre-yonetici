<?php
require_once 'includes/functions.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';
$password_data = null;

// ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$password_id = $_GET['id'];

// Şifre verilerini getir ve kullanıcıya ait mi kontrol et
$conn = connectDB();
$query = "SELECT * FROM passwords WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $password_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    header("Location: dashboard.php");
    exit();
}

$password_data = $result->fetch_assoc();

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
        // Şifreyi güncelle
        $query = "UPDATE passwords SET title = ?, username = ?, password = ?, url = ?, notes = ?, category = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssii", $title, $username, $password, $url, $notes, $category, $password_id, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $success = "Şifre başarıyla güncellendi.";
            // Verileri güncelle
            $password_data['title'] = $title;
            $password_data['username'] = $username;
            $password_data['password'] = $password;
            $password_data['url'] = $url;
            $password_data['notes'] = $notes;
            $password_data['category'] = $category;
        } else {
            $error = "Şifre güncellenirken bir hata oluştu.";
        }
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Şifre Düzenle</h1>
    <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Geri Dön</a>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="title" class="form-label">Site/Uygulama Adı <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($password_data['title']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="category" class="form-label">Kategori</label>
                    <input type="text" class="form-control" id="category" name="category" value="<?php echo htmlspecialchars($password_data['category']); ?>" placeholder="Örn: Sosyal Medya, Bankacılık">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="username" class="form-label">Kullanıcı Adı/E-posta <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($password_data['username']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Şifre <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" value="<?php echo htmlspecialchars($password_data['password']); ?>" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-primary" type="button" id="generatePassword">
                            <i class="fas fa-magic"></i> Oluştur
                        </button>
                    </div>
                    <div class="mt-2" id="passwordStrength"></div>
                </div>
            </div>
            <div class="mb-3">
                <label for="url" class="form-label">Website URL</label>
                <input type="url" class="form-control" id="url" name="url" value="<?php echo htmlspecialchars($password_data['url']); ?>" placeholder="https://example.com">
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label">Notlar</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($password_data['notes']); ?></textarea>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- Şifre Oluşturucu Modal (add_password.php ile aynı) -->
<div class="modal fade" id="passwordGeneratorModal" tabindex="-1" aria-hidden="true">
    <!-- add_password.php'deki aynı kodları buraya ekleyin -->
</div>

<script>
// add_password.php'deki JavaScript kodlarının aynısını buraya ekleyin
</script>

<?php include 'includes/footer.php'; ?>