<?php
session_start();
require_once 'utils/connection.php';
require_once 'utils/csrf.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'trainer') {
    header('Location: index.php');
    exit;
}

$pageTitle = 'LTW GYM - Trainer Dashboard';
$pageCss = 'trainer_dashboard.css';
$activePage = 'profile';

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_die();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'cancel_class') {
            $sessionId = $_POST['session_id'] ?? 0;
            
            $checkStmt = $db->prepare("SELECT c.trainer_id FROM class_sessions cs JOIN classes c ON cs.class_id = c.id WHERE cs.id = ?");
            $checkStmt->execute([$sessionId]);
            if ($checkStmt->fetchColumn() == $userId) {
                $delStmt = $db->prepare("DELETE FROM class_sessions WHERE id = ?");
                $delStmt->execute([$sessionId]);
            }

        } elseif ($action === 'change_time') {
            $sessionId = $_POST['session_id'] ?? 0;
            $newTime = $_POST['new_time'] ?? '';
            
            $checkStmt = $db->prepare("SELECT c.trainer_id FROM class_sessions cs JOIN classes c ON cs.class_id = c.id WHERE cs.id = ?");
            $checkStmt->execute([$sessionId]);
            if ($checkStmt->fetchColumn() == $userId && !empty($newTime)) {
                $formattedTime = date('Y-m-d H:i:s', strtotime($newTime));
                $updStmt = $db->prepare("UPDATE class_sessions SET start_at = ? WHERE id = ?");
                $updStmt->execute([$formattedTime, $sessionId]);
            }

        } elseif ($action === 'remove_member') {
            $sessionId = $_POST['session_id'] ?? 0;
            $memberId = $_POST['member_id'] ?? 0;
            
            $checkStmt = $db->prepare("SELECT c.trainer_id FROM class_sessions cs JOIN classes c ON cs.class_id = c.id WHERE cs.id = ?");
            $checkStmt->execute([$sessionId]);
            if ($checkStmt->fetchColumn() == $userId) {
                $delStmt = $db->prepare("DELETE FROM session_enrollments WHERE session_id = ? AND user_id = ?");
                $delStmt->execute([$sessionId, $memberId]);
            }
        }
        
        header("Location: trainer_dashbord.php");
        exit;

    } catch (PDOException $e) {
        die("Error updating data: " . $e->getMessage());
    }
}
// =========================================================================

try {
    $stmt = $db->prepare("
        SELECT cs.id as session_id, c.title as class_name, cs.start_at as start_time, c.duration, cs.room,
               u.id as member_id, u.name as member_name, u.email as member_email
        FROM class_sessions cs
        JOIN classes c ON cs.class_id = c.id
        LEFT JOIN session_enrollments ce ON cs.id = ce.session_id
        LEFT JOIN users u ON ce.user_id = u.id
        WHERE c.trainer_id = ? AND date(cs.start_at) >= date('now')
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
                'id' => $row['member_id'],
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
                    <!-- Adicionada a classe global hover-card -->
                    <div class="glass-panel session-card hover-card">
                        
                        <div class="session-header" style="margin-bottom: 1.5rem; padding-right: 0;">
                            <h2><?= htmlspecialchars($session['class_name']) ?></h2>
                            <span class="badge badge--gold" style="white-space: nowrap;"><?= htmlspecialchars($session['room']) ?></span>
                        </div>
                        
                        <div class="session-time" style="margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 1.5rem;">
                            <form method="POST" style="display: flex; flex-direction: column; gap: 0.8rem;">
                                <?= csrf_input() ?>
                                <input type="hidden" name="action" value="change_time">
                                <input type="hidden" name="session_id" value="<?= $sid ?>">
                                
                                <div>
                                    <label style="font-size: 0.75rem; color: var(--gold); text-transform: uppercase; font-weight: bold; letter-spacing: 1px; display: block; margin-bottom: 0.4rem;">Start Time</label>
                                    <input type="datetime-local" name="new_time" required 
                                           value="<?= date('Y-m-d\TH:i', strtotime($session['start_time'])) ?>"
                                           style="width: 100%; background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.2); color: white; padding: 0.6rem; border-radius: 4px; font-family: inherit;">
                                </div>
                                
                                <div style="display: flex; gap: 0.5rem; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                                    <button type="submit" class="btn btn--gold" style="padding: 0.6rem 1rem; font-size: 0.75rem; min-height: auto; flex-grow: 1;">Save Time</button>
                                    
                                    <!-- O botão Cancelar passou para aqui e perdeu o position absolute -->
                                    <button type="button" 
                                            class="btn btn--outline" 
                                            style="color: #ff4c4c; border-color: rgba(255, 76, 76, 0.4); padding: 0.6rem 1rem; font-size: 0.75rem; min-height: auto; flex-grow: 1;"
                                            onclick="if(confirm('Are you sure you want to cancel this entire class? This cannot be undone.')) { document.getElementById('cancel-form-<?= $sid ?>').submit(); }">
                                        Cancel Class
                                    </button>
                                </div>
                            </form>
                            
                            <!-- Formulário fantasma para o botão de cancelar funcionar corretamente -->
                            <form id="cancel-form-<?= $sid ?>" method="POST" style="display: none;">
                                <?= csrf_input() ?>
                                <input type="hidden" name="action" value="cancel_class">
                                <input type="hidden" name="session_id" value="<?= $sid ?>">
                            </form>

                            <div style="margin-top: 1rem; font-size: 0.8rem; color: var(--text-muted);">
                                Ends at: <?= htmlspecialchars(date('H:i', strtotime($session['end_time']))) ?>
                            </div>
                        </div>
                        
                        <!-- Substituição pela class global section-label -->
                        <h3 class="section-label" style="margin-bottom: 1rem;">Enrolled Members (<?= count($session['members']) ?>)</h3>
                        
                        <?php if (empty($session['members'])): ?>
                            <p class="empty-roster">No members enrolled yet.</p>
                        <?php else: ?>
                            <ul class="roster-list">
                                <?php foreach ($session['members'] as $member): ?>
                                    <li style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <strong><?= htmlspecialchars($member['name']) ?></strong>
                                            <span style="display: block; font-size: 0.85rem; color: #888;"><?= htmlspecialchars($member['email']) ?></span>
                                        </div>
                                        
                                        <form method="POST" onsubmit="return confirm(<?= json_encode('Remove ' . $member['name'] . ' from this class?') ?>);">
                                            <?= csrf_input() ?>
                                            <input type="hidden" name="action" value="remove_member">
                                            <input type="hidden" name="session_id" value="<?= $sid ?>">
                                            <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                                            <button type="submit" style="background: none; border: none; color: #ff4c4c; text-decoration: none; cursor: pointer; font-size: 0.85rem;">Remove</button>
                                        </form>
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