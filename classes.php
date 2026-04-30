<?php
require_once 'utils/connection.php';

$pageTitle = 'LTW GYM - Classes';
$pageCss = 'classes.css';
$activePage = 'classes';

$stmt = $db->prepare('SELECT * FROM classes');
$stmt->execute();
$classes = $stmt->fetchAll();

require_once 'templates/header.php';
?>

<main class="classes-page">
    <div class="classes-header">
        <h1>Our Classes</h1>
        <p>Push your limits with our expert-led group sessions.</p>
    </div>

    <section class="classes-container">
        <?php foreach ($classes as $class): ?>
            <div class="class-card">
                <div class="class-image" style="background-image: url('<?= htmlspecialchars($class['image_url']) ?>');"></div>
                <div class="class-content">
                    <h2><?= htmlspecialchars($class['title']) ?></h2>
                    <p><?= htmlspecialchars($class['description']) ?></p>
                    <div class="class-tags">
                        <span><?= htmlspecialchars($class['duration']) ?> Min</span>
                        <span><?= htmlspecialchars($class['intensity']) ?></span>
                        <span><?= htmlspecialchars($class['category']) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</main>

<?php require_once 'templates/footer.php'; ?>
