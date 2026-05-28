<?php
session_start();
require_once 'utils/connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'trainer') {
    header('Location: index.php');
    exit;
}

$pageTitle = 'LTW GYM - Trainer Dashboard';
$pageCss = 'trainer_dashboard.css';
$activePage = 'profile';

$userId = $_SESSION['user_id'];

// === PROCESSAMENTO DE AÇÕES (CANCELAR, ALTERAR HORA, REMOVER MEMBRO) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'cancel_class') {
            $sessionId = $_POST['session_id'] ?? 0;
            
            // Segurança: Verificar se a aula pertence a este treinador
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
                // Converter data do formato HTML (datetime-local) para formato da Base de Dados
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
        
        // Fazer refresh à página para evitar re-submissões de formulário ao fazer F5
        header("Location: trainer_dashbord.php");
        exit;

    } catch (PDOException $e) {
        die("Error updating data: " . $e->getMessage());
    }
}
// =========================================================================

try {
    // Nota: Adicionei "u.id as member_id" para podermos apagar as inscrições corretamente
    $stmt = $db->prepare("
        SELECT cs.id as session_id, c.title as class_name, cs.start_at as start_time, c.duration, cs.room,
               u.id as member_id, u.name as member_name, u.email as member_email
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
                    <div class="glass-panel session-card" style="position: relative;">
                        
                        <form method="POST" style="position: absolute; top: 1.5rem; right: 1.5rem;" onsubmit="return confirm('Are you sure you want to cancel this entire class? This cannot be undone.');">
                            <input type="hidden" name="action" value="cancel_class">
                            <input type="hidden" name="session_id" value="<?= $sid ?>">
                            <button type="submit" class="btn btn--outline" style="color: #ff4c4c; border-color: #ff4c4c; padding: 0.5rem 1rem; font-size: 0.8rem;">Cancel Class</button>
                        </form>

                        <div class="session-header" style="margin-bottom: 1rem; padding-right: 7rem;">
                            <h2><?= htmlspecialchars($session['class_name']) ?></h2>
                            <span class="room-badge"><?= htmlspecialchars($session['room']) ?></span>
                        </div>
                        
                        <div class="session-time" style="margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1rem;">
                            <form method="POST" style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                <input type="hidden" name="action" value="change_time">
                                <input type="hidden" name="session_id" value="<?= $sid ?>">
                                <label style="font-size: 0.9rem; color: #ccc;">Start Time:</label>
                                <input type="datetime-local" name="new_time" required 
                                       value="<?= date('Y-m-d\TH:i', strtotime($session['start_time'])) ?>"
                                       style="background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.2); color: white; padding: 0.4rem; border-radius: 4px; font-family: inherit;">
                                <button type="submit" class="btn btn--gold" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Save Time</button>
                            </form>
                            <div style="margin-top: 0.5rem; font-size: 0.85rem; color: #888;">
                                Ends at: <?= htmlspecialchars(date('H:i', strtotime($session['end_time']))) ?>
                            </div>
                        </div>
                        
                        <h3 class="roster-title">Enrolled Members (<?= count($session['members']) ?>)</h3>
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
                                        
                                        <form method="POST" onsubmit="return confirm('Remove <?= htmlspecialchars($member['name']) ?> from this class?');">
                                            <input type="hidden" name="action" value="remove_member">
                                            <input type="hidden" name="session_id" value="<?= $sid ?>">
                                            <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                                            <button type="submit" style="background: none; border: none; color: #ff4c4c; text-decoration: underline; cursor: pointer; font-size: 0.85rem;">Remove</button>
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