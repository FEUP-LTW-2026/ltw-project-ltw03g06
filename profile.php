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

try {
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) {
        session_destroy(); 
        header('Location: login.php'); 
        exit;
    }
} catch (PDOException $e) {
    die($e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $birthdate = $_POST['birthdate'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    
    $bio = $_POST['bio'] ?? $user['bio'];
    $specializations = $_POST['specializations'] ?? $user['specializations'];

    if (empty($name) || empty($email) || empty($birthdate) || empty($gender)) {
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
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $newFileName = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
                    if (move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                        $imageUrl = 'assets/uploads/' . $newFileName;
                    }
                } else {
                    $error = 'Invalid file type. Only JPG, PNG, and WEBP allowed.';
                }
            }

            if (empty($error)) {
                $stmt = $db->prepare('UPDATE users SET name = ?, email = ?, phone = ?, birthdate = ?, gender = ?, image_url = ?, bio = ?, specializations = ? WHERE id = ?');
                $stmt->execute([$name, $email, $phone, $birthdate, $gender, $imageUrl, $bio, $specializations, $userId]);

                if (!empty($newPassword)) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $pwdStmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $pwdStmt->execute([$hashedPassword, $userId]);
                }

                $_SESSION['user_name'] = $name;
                $success = 'Profile updated successfully!';
                
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
    
    <header class="profile-hero">
        <span class="hero-label">Member Dashboard</span>
        <h1>Account Settings</h1>
        <p>Manage your personal details, membership status, and security preferences.</p>
        
        <?php if ($user['role'] === 'trainer'): ?>
            <a href="trainer_dashbord.php" class="btn btn--gold u-lift" style="margin-top: 1.5rem; display: inline-block;">Manage Schedule & Roster</a>
        <?php endif; ?>
        
        <?php if ($user['role'] === 'admin'): ?>
            <a href="/admin/dashboard.php" class="btn btn--gold u-lift" style="margin-top: 1.5rem; display: inline-block;">Access Admin Portal</a>
        <?php endif; ?>
    </header>

    <form action="" method="post" enctype="multipart/form-data" class="profile-container">
        
        <input type="file" id="profile_pic" name="profile_pic" accept="image/*" style="display: none;">

        <aside class="profile-card member-info glass-panel">
            <label for="profile_pic" class="avatar-wrapper">
                <img src="<?= !empty($user['image_url']) ? htmlspecialchars($user['image_url']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=1a1a1a&color=ffffff&size=160' ?>" 
                     alt="Profile" class="profile-avatar">
                
                <div class="avatar-overlay">
                    <svg viewBox="0 0 24 24" class="upload-icon">
                        <path d="M19 7h-3V6a4 4 0 0 0-8 0v1H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2Zm-7 9a4 4 0 1 1 4-4 4 4 0 0 1-4 4Zm-2-9V6a2 2 0 0 1 4 0v1H10Z"/>
                    </svg>
                </div>
            </label>
            
            <h2><?= htmlspecialchars($user['name']) ?></h2>
            <p class="membership-badge"><?= htmlspecialchars(ucfirst($user['role'])) ?> <span><?= htmlspecialchars($user['plan'] ?? '') ?></span></p>

            <a href="logout.php" class="btn btn--outline btn--danger u-lift">LOG OUT</a>
        </aside>

        <section class="profile-card member-settings glass-panel">
            
            <?php if ($error): ?>
                <div class="alert alert-error" style="color: var(--error); padding: 1rem; border: 1px solid var(--error); background: rgba(255, 76, 76, 0.1); border-radius: 4px; margin-bottom: 2rem; font-weight: 600; text-align: center;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="color: var(--success); padding: 1rem; border: 1px solid var(--success); background: rgba(75, 181, 67, 0.1); border-radius: 4px; margin-bottom: 2rem; font-weight: 600; text-align: center;"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <h3 class="form-section-title">Personal Information</h3>
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
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
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

            <?php if ($user['role'] === 'trainer'): ?>
            <h3 class="form-section-title">Professional Profile</h3>
            <div class="form-grid" style="grid-template-columns: 1fr;">
                <div class="form-group">
                    <label for="specializations">Specializations</label>
                    <input type="text" id="specializations" name="specializations" value="<?= htmlspecialchars($user['specializations'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="bio">Biography & Certifications</label>
                    <textarea id="bio" name="bio" rows="4" style="width: 100%; padding: 1rem; background: rgba(0, 0, 0, 0.4); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 2px; color: #ffffff; font-family: var(--font-main); resize: vertical;"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                </div>
            </div>
            <?php endif; ?>

            <h3 class="form-section-title">Security & Password</h3>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="new_password" name="new_password" placeholder="Leave blank to keep current password">
                    <button type="button" id="togglePassword" class="icon-btn toggle-pwd-btn" aria-label="Show password">
                        <svg viewBox="0 0 24 24" class="eye-icon">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--gold u-lift">Save Changes</button>
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

    const togglePasswordBtn = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#new_password');
    if(togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.style.color = type === 'text' ? 'var(--gold)' : 'var(--text-muted)';
        });
    }
</script>

<?php require_once 'templates/footer.php'; ?>