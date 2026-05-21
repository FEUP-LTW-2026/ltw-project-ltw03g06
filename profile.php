<?php
session_start();
require_once 'utils/connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'LTW GYM - Profile';
$pageCss = 'profile.css';
$activePage = 'profile';

$error = '';
$success = '';
$userId = $_SESSION['user_id'];

// Carregar os dados
try {
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) {
        session_destroy(); header('Location: login.php'); exit;
    }
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}

// Lógica de Atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $birthdate = $_POST['birthdate'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    if (empty($name) || empty($email) || empty($phone) || empty($birthdate) || empty($gender)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $imageUrl = $user['image_url'];

            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
                $fileName = $_FILES['profile_pic']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $uploadDir = __DIR__ . '/assets/uploads/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    
                    $newFileName = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
                    if (move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                        $imageUrl = 'assets/uploads/' . $newFileName;
                    }
                } else {
                    $error = 'Invalid file type. Only JPG, PNG, and WEBP allowed.';
                }
            }

            if (empty($error)) {
                $stmt = $db->prepare('UPDATE users SET name = ?, email = ?, phone = ?, birthdate = ?, gender = ?, image_url = ? WHERE id = ?');
                $stmt->execute([$name, $email, $phone, $birthdate, $gender, $imageUrl, $userId]);

                if (!empty($newPassword)) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $pwdStmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $pwdStmt->execute([$hashedPassword, $userId]);
                }

                $_SESSION['user_name'] = $name;
                $success = 'Profile updated successfully!';
                
                // Recarregar os dados
                $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
            }
        } catch (PDOException $e) {
            $error = ($e->getCode() == 23000) ? 'Email is already in use.' : 'Error updating profile.';
        }
    }
}

require_once 'templates/header.php';
?>

<main class="profile-page">
    <form action="" method="post" enctype="multipart/form-data" class="profile-container">
        
        <input type="file" id="profile_pic" name="profile_pic" accept="image/*" style="display: none;">

        <aside class="profile-sidebar">
            <label for="profile_pic" class="avatar-wrapper">
                <img src="<?= !empty($user['image_url']) ? htmlspecialchars($user['image_url']) : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png' ?>" 
                     alt="Profile" class="profile-avatar">
                
                <div class="avatar-overlay">
                    <svg viewBox="0 0 24 24" class="upload-icon">
                        <path d="M19 7h-3V6a4 4 0 0 0-8 0v1H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2Zm-7 9a4 4 0 1 1 4-4 4 4 0 0 1-4 4Zm-2-9V6a2 2 0 0 1 4 0v1H10Z"/>
                    </svg>
                </div>
            </label>
            
            <h2><?= htmlspecialchars($user['name']) ?></h2>
            <p class="membership-badge">Membership: <span><?= htmlspecialchars($user['plan'] ?? 'No Active Plan') ?></span></p>

            <a href="logout.php" class="btn btn--outline btn--danger u-lift">LOG OUT</a>
        </aside>

        <section class="profile-content">
            <div class="content-header">
                <h2>Account Settings</h2>
                <p>Manage your personal details and security.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error" style="color: var(--error); margin-bottom: 1.5rem; font-weight: bold;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success" style="color: var(--success); margin-bottom: 1.5rem; font-weight: bold;"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="signup-form"> <h3 class="form-section-title">Personal Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($user['name']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="birthdate">Birthdate</label>
                        <input type="date" id="birthdate" name="birthdate" required value="<?= htmlspecialchars($user['birthdate'] ?? '') ?>">
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="male" <?= ($user['gender'] === 'male') ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($user['gender'] === 'female') ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= ($user['gender'] === 'other') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>

                <h3 class="form-section-title">Security</h3>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Leave blank to keep current password">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn--gold u-lift">Save Changes</button>
                </div>
            </div>
        </section>
    </form>
</main>

<script>
    document.getElementById('profile_pic').addEventListener('change', function(event) {
        if(event.target.files.length > 0){
            var src = URL.createObjectURL(event.target.files[0]);
            var preview = document.querySelector('.profile-avatar');
            preview.src = src;
        }
    });
</script>

<?php require_once 'templates/footer.php'; ?>