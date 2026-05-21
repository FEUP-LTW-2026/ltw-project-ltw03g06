<?php
require_once __DIR__ . '/../utils/connection.php';
require_once __DIR__ . '/../utils/schedule.php';

header('Content-Type: application/json; charset=utf-8');

$filters = normalizeScheduleFilters($_GET);
$memberId = currentMemberId($db);
$sessions = fetchScheduleSessions($db, $filters, $memberId, 'upcoming');
$filterQuery = http_build_query(array_filter($filters, static fn ($value) => $value !== '' && $value !== 0));

ob_start();

if (empty($sessions)) {
    echo '<div class="empty-state"><h3>No classes match these filters.</h3><p>Try a different trainer, day, or time window.</p></div>';
} else {
    foreach ($sessions as $session) {
        echo renderScheduleSessionCard($session, 'upcoming', $filterQuery);
    }
}

$html = ob_get_clean();

echo json_encode([
    'html' => $html,
    'count' => count($sessions),
], JSON_UNESCAPED_SLASHES);
