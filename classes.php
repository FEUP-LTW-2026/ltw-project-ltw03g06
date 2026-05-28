<?php
require_once 'utils/connection.php';
require_once 'utils/csrf.php';
require_once 'utils/schedule.php';

$pageTitle = 'LTW GYM - Schedule';
$pageCss = 'classes.css';
$pageScript = 'classes.js';
$activePage = 'classes';

$memberId = currentMemberId($db);
$filters = normalizeScheduleFilters($_GET);
$filterQuery = http_build_query(array_filter($filters, static fn ($value) => $value !== '' && $value !== 0));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_die();
    if ($memberId <= 0) {
        $_SESSION['flash_message'] = 'Please log in to enroll, cancel, or review classes.';
        $_SESSION['flash_type'] = 'error';
        header('Location: login.php');
        exit;
    }

    $action = $_POST['action'] ?? '';
    $sessionId = (int) ($_POST['session_id'] ?? 0);
    $message = 'Unable to complete that action.';
    $messageType = 'error';

    if ($sessionId > 0) {
        $sessionStmt = $db->prepare("SELECT cs.id, cs.start_at, cs.capacity, c.title
            FROM class_sessions cs
            JOIN classes c ON c.id = cs.class_id
            WHERE cs.id = ?");
        $sessionStmt->execute([$sessionId]);
        $session = $sessionStmt->fetch();

        if (!$session) {
            $message = 'That class session does not exist.';
        } else {
            $isPast = new DateTime($session['start_at']) < new DateTime('now');
            $enrollmentStmt = $db->prepare('SELECT 1 FROM session_enrollments WHERE user_id = ? AND session_id = ?');
            $enrollmentStmt->execute([$memberId, $sessionId]);
            $isEnrolled = (bool) $enrollmentStmt->fetchColumn();

            if ($action === 'enroll') {
                if ($isPast) {
                    $message = 'Past classes cannot accept new enrollments.';
                } elseif ($isEnrolled) {
                    $message = 'You are already enrolled in this class.';
                    $messageType = 'success';
                } else {
                    $capacityStmt = $db->prepare('SELECT COUNT(*) FROM session_enrollments WHERE session_id = ?');
                    $capacityStmt->execute([$sessionId]);
                    $enrolledCount = (int) $capacityStmt->fetchColumn();

                    if ($enrolledCount >= (int) $session['capacity']) {
                        $message = 'This class is already full.';
                    } else {
                        $insertStmt = $db->prepare('INSERT INTO session_enrollments (user_id, session_id) VALUES (?, ?)');
                        $insertStmt->execute([$memberId, $sessionId]);
                        $message = 'You are enrolled in ' . $session['title'] . '.';
                        $messageType = 'success';
                    }
                }
            } elseif ($action === 'cancel') {
                if (!$isEnrolled) {
                    $message = 'You are not enrolled in this class.';
                } else {
                    $deleteStmt = $db->prepare('DELETE FROM session_enrollments WHERE user_id = ? AND session_id = ?');
                    $deleteStmt->execute([$memberId, $sessionId]);
                    $message = 'Your enrollment was cancelled.';
                    $messageType = 'success';
                }
            } elseif ($action === 'review') {
                $rating = (int) ($_POST['rating'] ?? 0);
                $review = trim((string) ($_POST['review'] ?? ''));

                if (!$isPast || !$isEnrolled) {
                    $message = 'You can only review classes you have attended.';
                } elseif ($rating < 1 || $rating > 5 || $review === '') {
                    $message = 'Please provide a rating and review text.';
                } else {
                    $reviewStmt = $db->prepare("INSERT INTO class_reviews (user_id, session_id, rating, review)
                        VALUES (?, ?, ?, ?)
                        ON CONFLICT(user_id, session_id) DO UPDATE SET
                            rating = excluded.rating,
                            review = excluded.review,
                            created_at = CURRENT_TIMESTAMP");
                    $reviewStmt->execute([$memberId, $sessionId, $rating, $review]);
                    $message = 'Your review has been saved.';
                    $messageType = 'success';
                }
            }
        }
    }

    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $messageType;

    $redirectFilters = array_filter($filters, static fn ($value) => $value !== '' && $value !== 0);
    header('Location: classes.php' . (!empty($redirectFilters) ? '?' . http_build_query($redirectFilters) : ''));
    exit;
}

$typeOptions = $db->query('SELECT DISTINCT category FROM classes ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);
$trainerOptions = $db->query("SELECT id, name FROM users WHERE role = 'trainer' ORDER BY name")->fetchAll();
$upcomingSessions = fetchScheduleSessions($db, $filters, $memberId, 'upcoming');
$attendedSessions = fetchScheduleSessions($db, [], $memberId, 'past');

$flashMessage = $_SESSION['flash_message'] ?? null;
$flashType = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

require_once 'templates/header.php';
?>

<main class="classes-page">
    <section class="schedule-hero">
        <p class="section-label">Schedule</p>
        <h1>Browse the live class schedule</h1>
        <p>Filter by class type, trainer, day, time, or a live search term. Enroll in open sessions, cancel upcoming bookings, and review the classes you have attended.</p>
    </section>

    <?php if ($flashMessage !== null): ?>
        <div class="empty-state <?= $flashType === 'success' ? 'session-badge--available' : 'session-badge--full' ?>" style="margin-bottom: 1.25rem;">
            <strong><?= htmlspecialchars($flashMessage) ?></strong>
        </div>
    <?php endif; ?>

    <form class="schedule-filters" data-schedule-filters method="get" action="classes.php">
        <div class="filter-grid">
            <label>
                Search
                <input type="search" name="search" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Class name, trainer, or category">
            </label>

            <label>
                Type
                <select name="type">
                    <option value="">Any type</option>
                    <?php foreach ($typeOptions as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= $filters['type'] === $type ? 'selected' : '' ?>><?= htmlspecialchars($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Trainer
                <select name="trainer">
                    <option value="">Any trainer</option>
                    <?php foreach ($trainerOptions as $trainer): ?>
                        <option value="<?= (int) $trainer['id'] ?>" <?= $filters['trainer'] === (int) $trainer['id'] ? 'selected' : '' ?>><?= htmlspecialchars($trainer['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Day
                <select name="day">
                    <?php foreach (scheduleDayOptions() as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $filters['day'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Time
                <select name="time">
                    <?php foreach (scheduleTimeOptions() as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $filters['time'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="filter-submit">
                <button type="submit" class="btn btn--gold u-lift">Apply filters</button>
            </div>
        </div>
    </form>

    <section class="results-summary">
        <h2>Upcoming classes</h2>
        <p data-schedule-count><?= count($upcomingSessions) ?> class<?= count($upcomingSessions) === 1 ? '' : 'es' ?> found</p>
    </section>

    <section class="sessions-grid" data-schedule-results>
        <?php if (empty($upcomingSessions)): ?>
            <div class="empty-state">
                <h3>No classes match these filters.</h3>
                <p>Try a different trainer, day, or time window.</p>
            </div>
        <?php else: ?>
            <?php foreach ($upcomingSessions as $session): ?>
                <?= renderScheduleSessionCard($session, 'upcoming', $filterQuery) ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="member-section">
        <h2>Your attended classes</h2>
        <p>These sessions are already in your history, so you can leave or update a review.</p>

        <div class="sessions-grid" style="margin-top: 1rem;">
            <?php if (empty($attendedSessions)): ?>
                <div class="empty-state">
                    <h3>No past attended classes yet.</h3>
                    <p>Enroll in an upcoming class and come back here after the session starts.</p>
                </div>
            <?php else: ?>
                <?php foreach ($attendedSessions as $session): ?>
                    <?= renderScheduleSessionCard($session, 'past', $filterQuery) ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once 'templates/footer.php'; ?>