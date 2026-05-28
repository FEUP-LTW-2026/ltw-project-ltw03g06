<?php

function scheduleDayOptions(): array
{
    return [
        '' => 'Any day',
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
    ];
}

function scheduleTimeOptions(): array
{
    return [
        '' => 'Any time',
        'morning' => 'Morning',
        'afternoon' => 'Afternoon',
        'evening' => 'Evening',
    ];
}

function normalizeScheduleFilters(array $source): array
{
    $day = trim((string) ($source['day'] ?? ''));
    $time = trim((string) ($source['time'] ?? ''));

    if (!array_key_exists($day, scheduleDayOptions())) {
        $day = '';
    }

    if (!array_key_exists($time, scheduleTimeOptions())) {
        $time = '';
    }

    return [
        'search' => trim((string) ($source['search'] ?? '')),
        'type' => trim((string) ($source['type'] ?? '')),
        'trainer' => max(0, (int) ($source['trainer'] ?? 0)),
        'day' => $day,
        'time' => $time,
    ];
}

function buildScheduleQuery(array $filters, int $memberId, string $mode = 'upcoming'): array
{
    $filters = normalizeScheduleFilters($filters);
    if ($mode !== 'past') {
        $mode = 'upcoming';
    }

    $where = [];
    $params = [$memberId, $memberId, $memberId];

    $baseMondayExpr = "date('now', '-' || ((CAST(strftime('%w', 'now') AS INTEGER) + 6) % 7) || ' days')";
    if ($mode === 'past') {
        $where[] = "date(cs.start_at) < " . $baseMondayExpr;
        $where[] = "EXISTS (SELECT 1 FROM session_enrollments se_member WHERE se_member.session_id = cs.id AND se_member.user_id = ?)";
        $params[] = $memberId;
    } else {
        $where[] = "date(cs.start_at) >= " . $baseMondayExpr;
    }

    if ($filters['search'] !== '') {
        $where[] = '(c.title LIKE ? OR c.description LIKE ? OR u.name LIKE ? OR c.category LIKE ?)';
        $like = '%' . $filters['search'] . '%';
        array_push($params, $like, $like, $like, $like);
    }

    if ($filters['type'] !== '') {
        $where[] = 'c.category = ?';
        $params[] = $filters['type'];
    }

    if ($filters['trainer'] > 0) {
        $where[] = 'c.trainer_id = ?';
        $params[] = $filters['trainer'];
    }

    if ($filters['day'] !== '') {
        $dayMap = [
            'sunday' => '0',
            'monday' => '1',
            'tuesday' => '2',
            'wednesday' => '3',
            'thursday' => '4',
            'friday' => '5',
            'saturday' => '6',
        ];

        if (isset($dayMap[$filters['day']])) {
            $where[] = "strftime('%w', cs.start_at) = ?";
            $params[] = $dayMap[$filters['day']];
        }
    }

    if ($filters['time'] !== '') {
        $timeMap = [
            'morning' => ["time(cs.start_at) >= '06:00'", "time(cs.start_at) < '12:00'"],
            'afternoon' => ["time(cs.start_at) >= '12:00'", "time(cs.start_at) < '18:00'"],
            'evening' => ["time(cs.start_at) >= '18:00'", "time(cs.start_at) < '23:59:59'"],
        ];

        if (isset($timeMap[$filters['time']])) {
            $where[] = '(' . implode(' AND ', $timeMap[$filters['time']]) . ')';
        }
    }

    $sql = "SELECT
                cs.id AS session_id,
                cs.start_at,
                cs.capacity,
                cs.room,
                c.id AS class_id,
                c.title,
                c.description,
                c.duration,
                c.intensity,
                c.category,
                c.image_url,
                u.id AS trainer_id,
                u.name AS trainer_name,
                COALESCE(u.specializations, '') AS trainer_specializations,
                (SELECT COUNT(*) FROM session_enrollments se_count WHERE se_count.session_id = cs.id) AS enrolled_count,
                (SELECT COUNT(*) FROM class_reviews rv_count WHERE rv_count.session_id = cs.id) AS review_count,
                (
                    SELECT ROUND(AVG(rv_avg.rating), 1)
                    FROM class_reviews rv_avg
                    JOIN class_sessions cs_avg ON cs_avg.id = rv_avg.session_id
                    WHERE cs_avg.class_id = c.id
                ) AS average_rating,
                EXISTS(SELECT 1 FROM session_enrollments se_member WHERE se_member.session_id = cs.id AND se_member.user_id = ?) AS is_enrolled,
                (SELECT rv_user.rating FROM class_reviews rv_user WHERE rv_user.session_id = cs.id AND rv_user.user_id = ? LIMIT 1) AS my_rating,
                (SELECT rv_user.review FROM class_reviews rv_user WHERE rv_user.session_id = cs.id AND rv_user.user_id = ? LIMIT 1) AS my_review
            FROM class_sessions cs
            JOIN classes c ON c.id = cs.class_id
            LEFT JOIN users u ON u.id = c.trainer_id";

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY cs.start_at ASC';

    return [$sql, $params];
}

function fetchScheduleSessions(PDO $db, array $filters, int $memberId, string $mode = 'upcoming'): array
{
    [$sql, $params] = buildScheduleQuery($filters, $memberId, $mode);
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function scheduleDateLabel(string $startAt): string
{
    $dateTime = new DateTime($startAt);

    return $dateTime->format('D, M j');
}

function scheduleTimeLabel(string $startAt): string
{
    $dateTime = new DateTime($startAt);

    return $dateTime->format('H:i');
}

function scheduleDayLabel(string $startAt): string
{
    $dateTime = new DateTime($startAt);

    return $dateTime->format('l');
}

function renderScheduleSessionCard(array $session, string $mode = 'upcoming', string $actionQuery = ''): string
{
    $remainingSpots = max(0, (int) $session['capacity'] - (int) $session['enrolled_count']);
    $averageRating = $session['average_rating'] !== null ? number_format((float) $session['average_rating'], 1) : 'New';
    $actionLabel = (int) $session['is_enrolled'] === 1 ? 'Cancel enrollment' : 'Enroll now';
    $enrollClass = (int) $session['is_enrolled'] === 1 ? 'btn btn--outline' : 'btn btn--gold';
    $prefilledReview = htmlspecialchars((string) ($session['my_review'] ?? ''), ENT_QUOTES, 'UTF-8');
    $selectedRating = (int) ($session['my_rating'] ?? 0);
    $formAction = 'classes.php' . ($actionQuery !== '' ? '?' . $actionQuery : '');

    ob_start();
    ?>
    <article class="session-card hover-card <?= $mode === 'past' ? 'session-card--past' : '' ?>">
        <div class="session-image" style="background-image: url('<?= htmlspecialchars((string) $session['image_url'], ENT_QUOTES, 'UTF-8') ?>');"></div>
        <div class="session-content">
            <div class="session-kicker">
                <span><?= htmlspecialchars(scheduleDayLabel($session['start_at']), ENT_QUOTES, 'UTF-8') ?></span>
                <span><?= htmlspecialchars(scheduleDateLabel($session['start_at']), ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <h2><?= htmlspecialchars($session['title'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($session['description'], ENT_QUOTES, 'UTF-8') ?></p>

            <div class="session-tags">
                <span class="badge"><?= htmlspecialchars((string) $session['category'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="badge"><?= htmlspecialchars((string) $session['intensity'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="badge"><?= htmlspecialchars((string) $session['duration'], ENT_QUOTES, 'UTF-8') ?> min</span>
                <span class="badge"><?= htmlspecialchars((string) $session['room'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>

            <?php if ($mode === 'upcoming'): ?>
                <dl class="session-meta">
                    <div>
                        <dt>Trainer</dt>
                        <dd><?= htmlspecialchars((string) $session['trainer_name'], ENT_QUOTES, 'UTF-8') ?></dd>
                    </div>
                    <div>
                        <dt>Time</dt>
                        <dd><?= htmlspecialchars(scheduleTimeLabel($session['start_at']), ENT_QUOTES, 'UTF-8') ?></dd>
                    </div>
                    <div>
                        <dt>Capacity</dt>
                        <dd><?= (int) $session['enrolled_count'] ?>/<?= (int) $session['capacity'] ?></dd>
                    </div>
                    <div>
                        <dt>Reviews</dt>
                        <dd><?= htmlspecialchars($averageRating, ENT_QUOTES, 'UTF-8') ?> / 5</dd>
                    </div>
                </dl>

                <div class="session-footer">
                    <span class="badge <?= $remainingSpots === 0 ? 'badge--full' : 'badge--available' ?>">
                        <?= $remainingSpots === 0 ? 'Full' : $remainingSpots . ' spots left' ?>
                    </span>

                    <?php if ((int) $session['is_enrolled'] === 1): ?>
                        <form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" method="post" class="session-action-form">
                            <?= csrf_input() ?>
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="session_id" value="<?= (int) $session['session_id'] ?>">
                            <button type="submit" class="<?= $enrollClass ?>">Cancel enrollment</button>
                        </form>
                    <?php elseif ($remainingSpots > 0): ?>
                        <form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" method="post" class="session-action-form">
                            <?= csrf_input() ?>
                            <input type="hidden" name="action" value="enroll">
                            <input type="hidden" name="session_id" value="<?= (int) $session['session_id'] ?>">
                            <button type="submit" class="<?= $enrollClass ?>"><?= $actionLabel ?></button>
                        </form>
                    <?php else: ?>
                        <button type="button" class="btn btn--outline session-action-btn session-action-btn--disabled" disabled>Full</button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <dl class="session-meta">
                    <div>
                        <dt>Trainer</dt>
                        <dd><?= htmlspecialchars((string) $session['trainer_name'], ENT_QUOTES, 'UTF-8') ?></dd>
                    </div>
                    <div>
                        <dt>Time</dt>
                        <dd><?= htmlspecialchars(scheduleTimeLabel($session['start_at']), ENT_QUOTES, 'UTF-8') ?></dd>
                    </div>
                    <div>
                        <dt>Status</dt>
                        <dd>Completed session</dd>
                    </div>
                    <div>
                        <dt>Reviews</dt>
                        <dd><?= htmlspecialchars($averageRating, ENT_QUOTES, 'UTF-8') ?> / 5</dd>
                    </div>
                </dl>

                <div class="session-footer session-footer--past">
                    <span class="badge badge--archived">Archived</span>
                    <span class="session-lock">Past session</span>
                </div>

                <form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" method="post" class="review-form">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="review">
                    <input type="hidden" name="session_id" value="<?= (int) $session['session_id'] ?>">
                    <div class="review-grid">
                        <label>
                            Rating
                            <select name="rating" required>
                                <option value="" disabled <?= $selectedRating === 0 ? 'selected' : '' ?>>Choose</option>
                                <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                                    <option value="<?= $rating ?>" <?= $selectedRating === $rating ? 'selected' : '' ?>><?= $rating ?> Stars</option>
                                <?php endfor; ?>
                            </select>
                        </label>
                        <label>
                            Review
                            <textarea name="review" rows="3" placeholder="Share what you thought about this class" required><?= $prefilledReview ?></textarea>
                        </label>
                    </div>
                    <div class="review-actions">
                        <button type="submit" class="btn btn--gold">Save review</button>
                        <?php if (!empty($session['my_review'])): ?>
                            <span class="review-note">You can update your feedback anytime.</span>
                        <?php endif; ?>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </article>
    <?php

    return ob_get_clean();
}