<?php
require_once 'utils/connection.php';

$pageTitle = 'LTW GYM - Trainers';
$pageCss = 'trainers.css';
$activePage = 'trainers';

$stmt = $db->prepare("SELECT * FROM users WHERE role = 'trainer'");
$stmt->execute();
$trainers = $stmt->fetchAll();

require_once 'templates/header.php';
?>

<main class="trainers-page">
    <div class="trainers-header">
        <h1>Meet our trainers</h1>
        <p>Expert guidance from the city's most elite athletes.</p>
    </div>
    
    <div class="trainer-grid">
        <?php foreach ($trainers as $trainer): ?>
            <article class="trainer-card">
                <div class="trainer-image" style="background-image: url('<?= htmlspecialchars($trainer['image_url']) ?>');"></div>
                <div class="trainer-info">
                    <h2><?= htmlspecialchars($trainer['name']) ?></h2>
                    <p><?= htmlspecialchars($trainer['bio']) ?></p>
                    <div class="trainer-classes">
                        <?php
                        // Busca as categorias de aulas associadas a este treinador
                        $classStmt = $db->prepare("SELECT category FROM classes WHERE trainer_id = ?");
                        $classStmt->execute([$trainer['id']]);
                        // Puxa apenas a coluna 'category' e remove categorias duplicadas
                        $categories = array_unique($classStmt->fetchAll(PDO::FETCH_COLUMN));
                        
                        if (empty($categories)) {
                            echo "<span>No classes assigned</span>";
                        } else {
                            foreach ($categories as $category):
                        ?>
                                <span><?= htmlspecialchars($category) ?></span>
                        <?php 
                            endforeach; 
                        }
                        ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>

<?php 
require_once 'templates/footer.php'; 
?>