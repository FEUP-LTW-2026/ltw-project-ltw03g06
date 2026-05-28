<?php
// 1. Iniciar a sessão e conectar à base de dados
session_start();
require_once 'utils/connection.php';

// Se o utilizador já estiver ligado, redireciona diretamente para o perfil
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

$pageTitle = 'LTW GYM - Login';
// Reutilizamos o teu CSS de signup para manter o mesmo estilo visual limpo
$pageCss = 'signup.css'; 
$activePage = 'login';

$error = '';

// 2. Verificar se o formulário de login foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            // 3. Procurar o utilizador pelo email
            $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // 4. Verificar se o utilizador existe e se a password está correta
            // A função password_verify compara o texto limpo com o hash encriptado
            if ($user && ($user['password'] === $password || password_verify($password, $user['password']))) {
                
                // 5. Iniciar as variáveis de sessão (o browser "lembra-se" do utilizador)
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role']; // útil para permissões de admin/treinador

                if ($user['role'] === 'admin') {
                    header('Location: /admin/dashboard.php');
                    exit;
                }

                if ($user['role'] === 'trainer') {
                header('Location: trainer_dashboard.php'); 
                exit;
                }

                header('Location: profile.php');
                exit;

            } else {
                // Por segurança, usamos uma mensagem genérica para não revelar se o email existe ou não
                $error = 'Invalid email address or password.';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again later.';
        }
    }
}

require_once 'templates/header.php';
?>

<main class="signup-container">
    <div class="signup-form">
        <h1>Welcome Back</h1>
        <p>Log in to access your elite training portal</p>

        <form action="" method="post">
            
            <?php if ($error): ?>
                <div class="alert alert-error" style="color: var(--error); margin-bottom: 1rem; text-align: center; font-weight: bold;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="john@example.com" required
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    
                    <button type="button" id="togglePassword" class="icon-btn toggle-pwd-btn" aria-label="Show password">
                        <svg viewBox="0 0 24 24" class="eye-icon">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn--gold u-lift full-width-btn">Log In</button>
            
            <p style="text-align: center; margin-top: 1.5rem; font-size: 0.8rem; color: var(--text-muted);">
                Not a member yet? <a href="signup.php" style="color: var(--gold); text-decoration: none;">Create an account here</a>.
            </p>
        </form>
    </div>
</main>

<script>
    const togglePasswordBtn = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#password');

    togglePasswordBtn.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        if (type === 'text') {
            this.style.color = 'var(--gold)';
        } else {
            this.style.color = 'var(--text-muted)';
        }
    });
</script>

<?php require_once 'templates/footer.php'; ?>