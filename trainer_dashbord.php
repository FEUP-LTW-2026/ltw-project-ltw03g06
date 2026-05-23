<?php
session_start();
require_once 'utils/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'trainer') {
    header('Location: index.php');
    exit;
}

$pageTitle = 'LTW GYM - Trainer Dashboard';
$pageCss = 'dashboard.css';
$activePage = 'profile';

$userId = $_SESSION['user_id'];

try {
    $stmt = $db->prepare("
        SELECT cs.id as session_id, c.title as class_name, cs.start_at as start_time, c.duration, cs.room,
               u.name as member_name, u.email as member_email
        FROM class_sessions cs
        JOIN classes c ON cs.class_id = c.id
        LEFT JOIN session_enrollments ce ON cs.id = ce.session_id
        LEFT JOIN users u ON ce.user_id = u.id
        WHERE c.trainer_id = ?
        ORDER BY cs.start_at ASC, u.name ASC
    ");
    $stmt->execute([$userId]);
    $results = $stmt->fetchAll();

    $sessions = [];
    foreach ($results as $row) {
        $sid = $row['session_id'];
        
        if (!isset($sessions[$sid])) {
            $startDt = new DateTime($row['start_time']);
            $endDt = clone $startDt;
            $endDt->modify('+' . $row['duration'] . ' minutes');

            $sessions[$sid] = [
                'class_name' => $row['class_name'],
                'start_time' => $row['start_time'],
                'end_time' => $endDt->format('Y-m-d H:i:s'),
                'room' => $row['room'],
                'members' => []
            ];
        }
        
        if ($row['member_name']) {
            $sessions[$sid]['members'][] = [
                'name' => $row['member_name'],
                'email' => $row['member_email']
            ];
        }
    }
} catch (PDOException $e) {
    die($e->getMessage());
}

require_once 'templates/header.php';
?>

<main class="dashboard-page">
    <header class="profile-hero">
        <span class="hero-label">Trainer Portal</span>
        <h1>My Classes & Roster</h1>
        <a href="profile.php" class="btn btn--outline u-lift" style="margin-top: 1rem; display: inline-block;">Back to Profile</a>
    </header>

    <div class="dashboard-container">
        <?php if (empty($sessions)): ?>
            <div class="glass-panel" style="text-align: center; padding: 3rem;">
                <h3>No classes assigned yet.</h3>
            </div>
        <?php else: ?>
            <div class="sessions-grid">
                <?php foreach ($sessions as $sid => $session): ?>
                    <div class="glass-panel session-card">
                        <div class="session-header">
                            <h2><?= htmlspecialchars($session['class_name']) ?></h2>
                            <span class="room-badge"><?= htmlspecialchars($session['room']) ?></span>
                        </div>
                        <div class="session-time">
                            <?= htmlspecialchars(date('M d, Y - H:i', strtotime($session['start_time']))) ?> 
                            to 
                            <?= htmlspecialchars(date('H:i', strtotime($session['end_time']))) ?>
                        </div>
                        
                        <h3 class="roster-title">Enrolled Members (<?= count($session['members']) ?>)</h3>
                        <?php if (empty($session['members'])): ?>
                            <p class="empty-roster">No members enrolled yet.</p>
                        <?php else: ?>
                            <ul class="roster-list">
                                <?php foreach ($session['members'] as $member): ?>
                                    <li>
                                        <strong><?= htmlspecialchars($member['name']) ?></strong>
                                        <span><?= htmlspecialchars($member['email']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'templates/footer.php'; ?>