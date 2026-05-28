<?php
require_once 'utils/connection.php';

$pageTitle = 'LTW GYM - Trainers';
$pageCss = 'trainers.css';
$activePage = 'trainers';

$trainerStmt = $db->prepare("SELECT * FROM users WHERE role = 'trainer' AND email <> 'trainer@ltwgym.com' ORDER BY name");
$trainerStmt->execute();
$trainers = $trainerStmt->fetchAll();

require_once 'templates/header.php';
?>

<main class="trainers-page">
    <section class="trainers-header">
        <p class="section-label">Trainers</p>
        <h1>Meet the trainers behind the schedule</h1>
        <p>Each profile includes the coach's focus areas and the classes they currently teach, so members can jump from the trainer list straight back into the live schedule.</p>
    </section>

    <div class="trainer-grid">
        <?php foreach ($trainers as $trainer): ?>
            <?php
                $classStmt = $db->prepare('SELECT title, category FROM classes WHERE trainer_id = ? ORDER BY title');
                $classStmt->execute([$trainer['id']]);
                $classes = $classStmt->fetchAll();

                $sessionStmt = $db->prepare('SELECT c.title, c.category, cs.start_at, cs.room
                    FROM class_sessions cs
                    JOIN classes c ON c.id = cs.class_id
                    WHERE c.trainer_id = ? AND cs.start_at >= datetime("now")
                    ORDER BY cs.start_at');
                $sessionStmt->execute([$trainer['id']]);
                $upcomingSessions = $sessionStmt->fetchAll();

                $specializations = array_filter(array_map('trim', explode(',', (string) ($trainer['specializations'] ?? ''))));
                if (empty($specializations) && !empty($trainer['bio'])) {
                    $specializations = array_filter(array_map('trim', explode(',', (string) $trainer['bio'])));
                }
            ?>
            <article class="trainer-card hover-card">
                <div class="trainer-image" style="background-image: url('<?= htmlspecialchars((string) $trainer['image_url']) ?>');"></div>
                <div class="trainer-info">
                    <div class="trainer-header">
                        <div class="trainer-name-row">
                            <h2><?= htmlspecialchars($trainer['name']) ?></h2>
                            <span class="trainer-role">Trainer profile</span>
                        </div>
                        <p><?= htmlspecialchars((string) $trainer['bio']) ?></p>
                    </div>

                    <div class="trainer-details">
                        <span class="section-label">Specializations</span>
                        <div class="trainer-specializations">
                            <?php if (empty($specializations)): ?>
                                <span class="badge">No specializations listed</span>
                            <?php else: ?>
                                <?php foreach ($specializations as $specialization): ?>
                                    <span class="badge"><?= htmlspecialchars($specialization) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="trainer-details">
                        <span class="section-label">Classes taught</span>
                        <div class="trainer-classes">
                            <?php if (empty($classes)): ?>
                                <span class="badge">No classes assigned</span>
                            <?php else: ?>
                                <?php foreach ($classes as $class): ?>
                                    <span class="badge"><?= htmlspecialchars($class['title']) ?> &bull; <?= htmlspecialchars($class['category']) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="trainer-details">
                        <span class="section-label">Upcoming sessions</span>
                        <div class="trainer-classes">
                            <?php if (empty($upcomingSessions)): ?>
                                <span class="badge">No upcoming sessions</span>
                            <?php else: ?>
                                <?php foreach ($upcomingSessions as $session): ?>
                                    <span class="badge"><?= htmlspecialchars($session['title']) ?> &bull; <?= htmlspecialchars(date('D j M, H:i', strtotime($session['start_at']))) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <a class="btn btn--outline trainer-link" href="classes.php?trainer=<?= (int) $trainer['id'] ?>">View schedule</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>

<?php require_once 'templates/footer.php'; ?>