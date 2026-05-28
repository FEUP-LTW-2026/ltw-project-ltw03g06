<?php
// Iniciar a sessão e conectar à base de dados
session_start();
require_once 'utils/connection.php';
require_once 'utils/csrf.php';

$pageTitle = 'LTW GYM - Sign Up';
$pageCss = 'signup.css';
$activePage = 'signup';

$error = '';
$success = '';

// Verificar se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_die();
    // Recolher os dados do formulário
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // <-- Adicionado!
    $phone = trim($_POST['phone'] ?? '');
    $birthdate = $_POST['birthdate'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $plan = $_POST['plan'] ?? '';
    $terms = isset($_POST['terms']) ? true : false;

    // Validação básica
    if (empty($name) || empty($email) || empty($password) || empty($phone) || empty($birthdate) || empty($gender) || empty($plan) || !$terms) {
        $error = 'Please fill in all fields and agree to the terms.';
    } else {
        // Encriptar a password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Inserir os dados todos na base de dados
            $stmt = $db->prepare('INSERT INTO users (name, email, password, role, phone, birthdate, gender, plan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, $hashedPassword, 'member', $phone, $birthdate, $gender, $plan]);
            
            $success = 'Account created successfully! You can now log in.';
            
        } catch (PDOException $e) {
            // Verifica se o email já existe
            if ($e->getCode() == 23000) {
                $error = 'This email is already registered.';
            } else {
                $error = 'An error occurred. Please try again later.';
            }
        }
    }
}

require_once 'templates/header.php';
?>

<main class="signup-container">
    <div class="signup-form">
        <h1>Join LTW GYM</h1>
        <p>Start your elite fitness journey today</p>

        <form action="" method="post">
            <?= csrf_input() ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error" style="color: var(--error); margin-bottom: 1rem; text-align: center; font-weight: bold;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="color: var(--success); margin-bottom: 1rem; text-align: center; font-weight: bold;">
                    <?= htmlspecialchars($success) ?>
                    <br><a href="login.php" style="color: var(--gold);">Click here to Login</a>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="John Doe" required 
                       value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="john@example.com" required
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                    
                    <button type="button" id="togglePassword" class="icon-btn toggle-pwd-btn" aria-label="Show password">
                        <svg viewBox="0 0 24 24" class="eye-icon">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="+351 ..." required
                       value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="birthdate">Birthdate</label>
                <input type="date" id="birthdate" name="birthdate" required
                       value="<?= isset($_POST['birthdate']) ? htmlspecialchars($_POST['birthdate']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="" disabled <?= empty($_POST['gender']) ? 'selected' : '' ?>>Select</option>
                    <option value="male" <?= (isset($_POST['gender']) && $_POST['gender'] == 'male') ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= (isset($_POST['gender']) && $_POST['gender'] == 'female') ? 'selected' : '' ?>>Female</option>
                    <option value="other" <?= (isset($_POST['gender']) && $_POST['gender'] == 'other') ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="plan">Membership Plan</label>
                <select id="plan" name="plan" required>
                    <option value="" disabled <?= empty($_POST['plan']) ? 'selected' : '' ?>>Choose a plan</option>
                    <option value="monthly" <?= (isset($_POST['plan']) && $_POST['plan'] == 'monthly') ? 'selected' : '' ?>>Monthly - €29.99</option>
                    <option value="quarterly" <?= (isset($_POST['plan']) && $_POST['plan'] == 'quarterly') ? 'selected' : '' ?>>Quarterly - €79.99</option>
                    <option value="annual" <?= (isset($_POST['plan']) && $_POST['plan'] == 'annual') ? 'selected' : '' ?>>Annual - €299.99</option>
                </select>
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" id="terms" name="terms" required <?= isset($_POST['terms']) ? 'checked' : '' ?>>
                <label for="terms">I agree with the <a href="#" target="_blank">terms and conditions</a></label>
            </div>

            <button type="submit" class="btn btn--gold u-lift full-width-btn">Create Account</button>
        </form>
    </div>
</main>

<script>
    const togglePasswordBtn = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#password');

    togglePasswordBtn.addEventListener('click', function () {
        // Verifica o tipo atual e alterna
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Opcional: Mudar a cor do ícone para dar feedback de que está ativo
        if (type === 'text') {
            this.style.color = 'var(--gold)';
        } else {
            this.style.color = 'var(--text-muted)';
        }
    });
</script>

<?php require_once 'templates/footer.php'; ?>
