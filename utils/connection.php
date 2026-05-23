<?php

$dbPath = __DIR__ . '/../database/database.db';

function tableExists(PDO $db, string $tableName): bool
{
    $stmt = $db->prepare("SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = ?");
    $stmt->execute([$tableName]);

    return (bool) $stmt->fetchColumn();
}

function columnExists(PDO $db, string $tableName, string $columnName): bool
{
    $stmt = $db->query("PRAGMA table_info($tableName)");

    foreach ($stmt->fetchAll() as $column) {
        if ($column['name'] === $columnName) {
            return true;
        }
    }

    return false;
}

function currentMemberId(PDO $db): int
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        $stmt = $db->prepare("SELECT id FROM users WHERE role = 'member' ORDER BY id LIMIT 1");
        $stmt->execute();
        $_SESSION['user_id'] = (int) $stmt->fetchColumn();
        $_SESSION['user_role'] = 'member';
    }

    return (int) $_SESSION['user_id'];
}

function upsertUserSeed(PDO $db, array $user): int
{
    $selectStmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $selectStmt->execute([$user['email']]);
    $userId = (int) $selectStmt->fetchColumn();

    $fields = [
        'name' => $user['name'],
        'password' => password_hash($user['password'], PASSWORD_DEFAULT), 
        'role' => $user['role'],
        'phone' => $user['phone'] ?? null,
        'birthdate' => $user['birthdate'] ?? null,
        'gender' => $user['gender'] ?? null,
        'plan' => $user['plan'] ?? null,
        'bio' => $user['bio'] ?? null,
        'image_url' => $user['image_url'] ?? null,
        'specializations' => $user['specializations'] ?? null,
    ];

    if ($userId > 0) {
        $updateStmt = $db->prepare('UPDATE users SET name = ?, password = ?, role = ?, phone = ?, birthdate = ?, gender = ?, plan = ?, bio = ?, image_url = ?, specializations = ? WHERE email = ?');
        $updateStmt->execute([
            $fields['name'],
            $fields['password'],
            $fields['role'],
            $fields['phone'],
            $fields['birthdate'],
            $fields['gender'],
            $fields['plan'],
            $fields['bio'],
            $fields['image_url'],
            $fields['specializations'],
            $user['email'],
        ]);

        return $userId;
    }

    $insertStmt = $db->prepare('INSERT INTO users (name, email, password, role, phone, birthdate, gender, plan, bio, image_url, specializations) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $insertStmt->execute([
        $fields['name'],
        $user['email'],
        $fields['password'],
        $fields['role'],
        $fields['phone'],
        $fields['birthdate'],
        $fields['gender'],
        $fields['plan'],
        $fields['bio'],
        $fields['image_url'],
        $fields['specializations'],
    ]);

    return (int) $db->lastInsertId();
}

function upsertClassSeed(PDO $db, array $class, int $trainerId): int
{
    $selectStmt = $db->prepare('SELECT id FROM classes WHERE title = ?');
    $selectStmt->execute([$class['title']]);
    $classId = (int) $selectStmt->fetchColumn();

    if ($classId > 0) {
        $updateStmt = $db->prepare('UPDATE classes SET description = ?, duration = ?, intensity = ?, category = ?, image_url = ?, trainer_id = ? WHERE title = ?');
        $updateStmt->execute([
            $class['description'],
            $class['duration'],
            $class['intensity'],
            $class['category'],
            $class['image_url'],
            $trainerId,
            $class['title'],
        ]);

        return $classId;
    }

    $insertStmt = $db->prepare('INSERT INTO classes (title, description, duration, intensity, category, image_url, trainer_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $insertStmt->execute([
        $class['title'],
        $class['description'],
        $class['duration'],
        $class['intensity'],
        $class['category'],
        $class['image_url'],
        $trainerId,
    ]);

    return (int) $db->lastInsertId();
}

function scheduleSeedStart(int $weekdayIndex, int $weekOffset, string $time): string
{
    $weekStart = new DateTimeImmutable('monday this week');
    $date = $weekStart->modify(sprintf('%+d week', $weekOffset))->modify(sprintf('+%d days', $weekdayIndex));

    return $date->format('Y-m-d') . ' ' . $time;
}

function seedSessionEnrollmentCount(PDO $db, int $sessionId, array $memberIds, int $targetCount, int $excludeMemberId = 0): void
{
    $eligibleMemberIds = array_values(array_filter($memberIds, static fn ($memberId) => $memberId !== $excludeMemberId));

    if ($excludeMemberId > 0) {
        $removeStmt = $db->prepare('DELETE FROM session_enrollments WHERE session_id = ? AND user_id = ?');
        $removeStmt->execute([$sessionId, $excludeMemberId]);
    }

    $countStmt = $db->prepare('SELECT COUNT(*) FROM session_enrollments WHERE session_id = ?');
    $countStmt->execute([$sessionId]);
    $currentCount = (int) $countStmt->fetchColumn();

    if ($currentCount >= $targetCount) {
        return;
    }

    $occupiedStmt = $db->prepare('SELECT user_id FROM session_enrollments WHERE session_id = ?');
    $occupiedStmt->execute([$sessionId]);
    $occupiedMemberIds = array_map('intval', $occupiedStmt->fetchAll(PDO::FETCH_COLUMN));

    $availableMemberIds = array_values(array_filter($eligibleMemberIds, static fn ($memberId) => !in_array($memberId, $occupiedMemberIds, true)));
    $insertStmt = $db->prepare('INSERT INTO session_enrollments (user_id, session_id) VALUES (?, ?)');
    foreach (array_slice($availableMemberIds, 0, $targetCount - $currentCount) as $memberId) {
        $insertStmt->execute([$memberId, $sessionId]);
    }
}

function bootstrapGymData(PDO $db): void
{
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'member' CHECK(role IN ('admin', 'member', 'trainer')),
        phone TEXT,
        birthdate DATE,
        gender TEXT,
        plan TEXT,
        bio TEXT,
        image_url TEXT
    )");

    if (!columnExists($db, 'users', 'specializations')) {
        $db->exec('ALTER TABLE users ADD COLUMN specializations TEXT');
    }

    $db->exec("CREATE TABLE IF NOT EXISTS classes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT NOT NULL,
        duration INTEGER NOT NULL,
        intensity TEXT NOT NULL,
        category TEXT NOT NULL,
        image_url TEXT,
        trainer_id INTEGER REFERENCES users(id) ON DELETE SET NULL
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS enrollments (
        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        class_id INTEGER REFERENCES classes(id) ON DELETE CASCADE,
        PRIMARY KEY (user_id, class_id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS class_sessions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        class_id INTEGER NOT NULL REFERENCES classes(id) ON DELETE CASCADE,
        start_at TEXT NOT NULL,
        capacity INTEGER NOT NULL,
        room TEXT NOT NULL,
        UNIQUE(class_id, start_at)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS session_enrollments (
        user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        session_id INTEGER NOT NULL REFERENCES class_sessions(id) ON DELETE CASCADE,
        PRIMARY KEY (user_id, session_id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS class_reviews (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        session_id INTEGER NOT NULL REFERENCES class_sessions(id) ON DELETE CASCADE,
        rating INTEGER NOT NULL CHECK(rating BETWEEN 1 AND 5),
        review TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(user_id, session_id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS equipment (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        area TEXT NOT NULL,
        total_units INTEGER NOT NULL,
        available_units INTEGER NOT NULL,
        status TEXT NOT NULL CHECK(status IN ('available', 'limited', 'maintenance')),
        notes TEXT,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");

    $trainerSeeds = [
        [
            'email' => 'reed@ltwgym.com',
            'name' => 'Marcus Reed',
            'password' => '1234',
            'role' => 'trainer',
            'bio' => 'Functional strength coach focused on clean movement patterns, core stability, and disciplined nutrition.',
            'image_url' => 'assets/img/trainers/marcus-reed.jpg',
            'specializations' => 'Functional Strength, Nutrition Coaching',
        ],
        [
            'email' => 'elena@ltwgym.com',
            'name' => 'Elena Vega',
            'password' => '1234',
            'role' => 'trainer',
            'bio' => 'High-energy cycling and conditioning specialist with a focus on endurance and athletic rhythm.',
            'image_url' => 'assets/img/trainers/elena-vega.jpg',
            'specializations' => 'Cycling, Endurance Training',
        ],
        [
            'email' => 'cannons@ltwgym.com',
            'name' => 'Dante Cross',
            'password' => '1234',
            'role' => 'trainer',
            'bio' => 'Hypertrophy coach who blends intensity blocks with structured recovery to build size and strength.',
            'image_url' => 'assets/img/trainers/dante-cross.jpg',
            'specializations' => 'Hypertrophy, Contest Preparation, Body Composition',
        ],
        [
            'email' => 'nora@ltwgym.com',
            'name' => 'Nora Kim',
            'password' => '1234',
            'role' => 'trainer',
            'bio' => 'Flow and mobility coach with a calm, technical approach to movement and recovery.',
            'image_url' => 'assets/img/trainers/nora-kim.jpg',
            'specializations' => 'Yoga, Mobility, Recovery',
        ],
        [
            'email' => 'leo@ltwgym.com',
            'name' => 'Leo Santos',
            'password' => '1234',
            'role' => 'trainer',
            'bio' => 'Boxing and metabolic conditioning coach bringing sharp timing and explosive cardio work.',
            'image_url' => 'assets/img/trainers/leo-santos.jpg',
            'specializations' => 'Boxing, Conditioning, HIIT',
        ],
        [
            'email' => 'mila@ltwgym.com',
            'name' => 'Mila Hart',
            'password' => '1234',
            'role' => 'trainer',
            'bio' => 'Pilates and recovery-focused coach who keeps classes controlled, precise, and joint-friendly.',
            'image_url' => 'assets/img/trainers/mila-hart.jpg',
            'specializations' => 'Pilates, Recovery, Core Stability',
        ],
    ];

    $trainerIds = [];
    foreach ($trainerSeeds as $trainerSeed) {
        $trainerIds[$trainerSeed['email']] = upsertUserSeed($db, $trainerSeed);
    }

    $memberSeeds = [
        ['email' => 'alex@ltwgym.com', 'name' => 'Alex Morgan', 'password' => '1234', 'role' => 'member'],
        ['email' => 'sophia@ltwgym.com', 'name' => 'Sophia Lane', 'password' => '1234', 'role' => 'member'],
        ['email' => 'daniel@ltwgym.com', 'name' => 'Daniel Cruz', 'password' => '1234', 'role' => 'member'],
        ['email' => 'maria@ltwgym.com', 'name' => 'Maria Silva', 'password' => '1234', 'role' => 'member'],
        ['email' => 'noah@ltwgym.com', 'name' => 'Noah Bennett', 'password' => '1234', 'role' => 'member'],
        ['email' => 'ava@ltwgym.com', 'name' => 'Ava Brooks', 'password' => '1234', 'role' => 'member'],
        ['email' => 'luca@ltwgym.com', 'name' => 'Luca Martins', 'password' => '1234', 'role' => 'member'],
        ['email' => 'zoe@ltwgym.com', 'name' => 'Zoe Turner', 'password' => '1234', 'role' => 'member'],
        ['email' => 'max@ltwgym.com', 'name' => 'Max Oliveira', 'password' => '1234', 'role' => 'member'],
        ['email' => 'iris@ltwgym.com', 'name' => 'Iris Costa', 'password' => '1234', 'role' => 'member'],
        ['email' => 'henry@ltwgym.com', 'name' => 'Henry Scott', 'password' => '1234', 'role' => 'member'],
        ['email' => 'ella@ltwgym.com', 'name' => 'Ella Mendes', 'password' => '1234', 'role' => 'member'],
        ['email' => 'olivia@ltwgym.com', 'name' => 'Olivia Ward', 'password' => '1234', 'role' => 'member'],
        ['email' => 'ethan@ltwgym.com', 'name' => 'Ethan Stone', 'password' => '1234', 'role' => 'member'],
        ['email' => 'grace@ltwgym.com', 'name' => 'Grace Hall', 'password' => '1234', 'role' => 'member'],
        ['email' => 'felix@ltwgym.com', 'name' => 'Felix Costa', 'password' => '1234', 'role' => 'member'],
        ['email' => 'maya@ltwgym.com', 'name' => 'Maya Pereira', 'password' => '1234', 'role' => 'member'],
        ['email' => 'julian@ltwgym.com', 'name' => 'Julian Fox', 'password' => '1234', 'role' => 'member'],
        ['email' => 'ruby@ltwgym.com', 'name' => 'Ruby Evans', 'password' => '1234', 'role' => 'member'],
        ['email' => 'adam@ltwgym.com', 'name' => 'Adam Reed', 'password' => '1234', 'role' => 'member'],
    ];

    $memberIds = [];
    foreach ($memberSeeds as $memberSeed) {
        $memberIds[] = upsertUserSeed($db, $memberSeed);
    }

    $classSeeds = [
        [
            'title' => 'IronPump',
            'description' => 'Barbell and weight training designed to build strength with a high-energy pace.',
            'duration' => 45,
            'intensity' => 'High Intensity',
            'category' => 'Strength',
            'image_url' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=600&auto=format&fit=crop',
            'trainer_email' => 'reed@ltwgym.com',
        ],
        [
            'title' => 'ZenFlow Yoga',
            'description' => 'Find your balance. Improve flexibility, posture, and reduce stress with mindful flow.',
            'duration' => 60,
            'intensity' => 'Low Intensity',
            'category' => 'Yoga',
            'image_url' => 'https://images.unsplash.com/photo-1549576490-b0b4831ef60a?q=80&w=600&auto=format&fit=crop',
            'trainer_email' => 'nora@ltwgym.com',
        ],
        [
            'title' => 'Velocity Spin',
            'description' => 'Indoor cycling intervals built to push endurance and keep the heart rate high.',
            'duration' => 50,
            'intensity' => 'High Intensity',
            'category' => 'Cardio',
            'image_url' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=600&auto=format&fit=crop',
            'trainer_email' => 'elena@ltwgym.com',
        ],
        [
            'title' => 'CoreForge Pilates',
            'description' => 'Low-impact precision work for posture, stability, and deep core control.',
            'duration' => 55,
            'intensity' => 'Low Intensity',
            'category' => 'Pilates',
            'image_url' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?q=80&w=600&auto=format&fit=crop',
            'trainer_email' => 'mila@ltwgym.com',
        ],
        [
            'title' => 'PulseBox',
            'description' => 'Boxing combinations paired with conditioning rounds for explosive cardio.',
            'duration' => 45,
            'intensity' => 'High Intensity',
            'category' => 'Boxing',
            'image_url' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=600&auto=format&fit=crop',
            'trainer_email' => 'leo@ltwgym.com',
        ],
        [
            'title' => 'Mobility Reset',
            'description' => 'A mobility and recovery session to unlock tight joints and restore movement quality.',
            'duration' => 40,
            'intensity' => 'Low Intensity',
            'category' => 'Mobility',
            'image_url' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?q=80&w=600&auto=format&fit=crop',
            'trainer_email' => 'nora@ltwgym.com',
        ],
        [
            'title' => 'HIIT Forge',
            'description' => 'Fast-paced intervals that combine strength, speed, and brutal conditioning blocks.',
            'duration' => 35,
            'intensity' => 'High Intensity',
            'category' => 'HIIT',
            'image_url' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?q=80&w=600&auto=format&fit=crop',
            'trainer_email' => 'leo@ltwgym.com',
        ],
        [
            'title' => 'Recovery Lab',
            'description' => 'Breathing, stretching, and decompression work for a better recovery cycle.',
            'duration' => 30,
            'intensity' => 'Low Intensity',
            'category' => 'Recovery',
            'image_url' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?q=80&w=600&auto=format&fit=crop',
            'trainer_email' => 'mila@ltwgym.com',
        ],
    ];

    $classIds = [];
    foreach ($classSeeds as $classSeed) {
        $classIds[$classSeed['title']] = upsertClassSeed($db, $classSeed, $trainerIds[$classSeed['trainer_email']]);
    }

    $sessionSeeds = [
        ['title' => 'IronPump', 'weekOffset' => 0, 'weekday' => 4, 'time' => '07:00:00', 'capacity' => 12, 'occupied' => 12, 'room' => 'Main Floor'],
        ['title' => 'IronPump', 'weekOffset' => 1, 'weekday' => 0, 'time' => '18:00:00', 'capacity' => 12, 'occupied' => 11, 'room' => 'Main Floor'],
        ['title' => 'ZenFlow Yoga', 'weekOffset' => 0, 'weekday' => 4, 'time' => '18:30:00', 'capacity' => 14, 'occupied' => 9, 'room' => 'Studio A'],
        ['title' => 'ZenFlow Yoga', 'weekOffset' => -1, 'weekday' => 2, 'time' => '18:30:00', 'capacity' => 14, 'occupied' => 13, 'room' => 'Studio A'],
        ['title' => 'Velocity Spin', 'weekOffset' => 0, 'weekday' => 5, 'time' => '07:30:00', 'capacity' => 16, 'occupied' => 15, 'room' => 'Cycling Hall'],
        ['title' => 'Velocity Spin', 'weekOffset' => -1, 'weekday' => 5, 'time' => '07:30:00', 'capacity' => 16, 'occupied' => 12, 'room' => 'Cycling Hall'],
        ['title' => 'Velocity Spin', 'weekOffset' => 1, 'weekday' => 2, 'time' => '19:00:00', 'capacity' => 16, 'occupied' => 16, 'room' => 'Cycling Hall'],
        ['title' => 'CoreForge Pilates', 'weekOffset' => 0, 'weekday' => 1, 'time' => '09:00:00', 'capacity' => 12, 'occupied' => 8, 'room' => 'Studio B'],
        ['title' => 'CoreForge Pilates', 'weekOffset' => -1, 'weekday' => 3, 'time' => '09:00:00', 'capacity' => 12, 'occupied' => 12, 'room' => 'Studio B'],
        ['title' => 'PulseBox', 'weekOffset' => 0, 'weekday' => 5, 'time' => '17:30:00', 'capacity' => 14, 'occupied' => 14, 'room' => 'Fight Zone'],
        ['title' => 'PulseBox', 'weekOffset' => 1, 'weekday' => 3, 'time' => '17:30:00', 'capacity' => 14, 'occupied' => 11, 'room' => 'Fight Zone'],
        ['title' => 'Mobility Reset', 'weekOffset' => 0, 'weekday' => 1, 'time' => '12:15:00', 'capacity' => 10, 'occupied' => 6, 'room' => 'Recovery Studio'],
        ['title' => 'Mobility Reset', 'weekOffset' => -1, 'weekday' => 4, 'time' => '12:15:00', 'capacity' => 10, 'occupied' => 10, 'room' => 'Recovery Studio'],
        ['title' => 'HIIT Forge', 'weekOffset' => 0, 'weekday' => 2, 'time' => '06:45:00', 'capacity' => 18, 'occupied' => 17, 'room' => 'Main Floor'],
        ['title' => 'HIIT Forge', 'weekOffset' => 1, 'weekday' => 4, 'time' => '06:45:00', 'capacity' => 18, 'occupied' => 18, 'room' => 'Main Floor'],
        ['title' => 'Recovery Lab', 'weekOffset' => 0, 'weekday' => 6, 'time' => '10:00:00', 'capacity' => 10, 'occupied' => 7, 'room' => 'Recovery Studio'],
        ['title' => 'Recovery Lab', 'weekOffset' => -1, 'weekday' => 0, 'time' => '10:00:00', 'capacity' => 10, 'occupied' => 9, 'room' => 'Recovery Studio'],
    ];

    $sessionIds = [];
    $insertOrUpdateSession = $db->prepare('INSERT OR IGNORE INTO class_sessions (class_id, start_at, capacity, room) VALUES (?, ?, ?, ?)');
    $updateSession = $db->prepare('UPDATE class_sessions SET capacity = ?, room = ? WHERE class_id = ? AND start_at = ?');
    $currentMemberSeedId = $memberIds[0] ?? 0;

    foreach ($sessionSeeds as $sessionSeed) {
        $startAt = scheduleSeedStart((int) $sessionSeed['weekday'], (int) $sessionSeed['weekOffset'], $sessionSeed['time']);
        $classId = $classIds[$sessionSeed['title']];
        $insertOrUpdateSession->execute([$classId, $startAt, $sessionSeed['capacity'], $sessionSeed['room']]);
        $updateSession->execute([$sessionSeed['capacity'], $sessionSeed['room'], $classId, $startAt]);

        $lookupStmt = $db->prepare('SELECT id FROM class_sessions WHERE class_id = ? AND start_at = ?');
        $lookupStmt->execute([$classId, $startAt]);
        $sessionIds[$sessionSeed['title'] . '|' . $startAt] = (int) $lookupStmt->fetchColumn();
    }

    $sessionLookupStmt = $db->prepare('SELECT id FROM class_sessions WHERE class_id = ? AND start_at = ?');
    foreach ($sessionSeeds as $sessionSeed) {
        $startAt = scheduleSeedStart((int) $sessionSeed['weekday'], (int) $sessionSeed['weekOffset'], $sessionSeed['time']);
        $sessionLookupStmt->execute([$classIds[$sessionSeed['title']], $startAt]);
        $sessionId = (int) $sessionLookupStmt->fetchColumn();
        if ($sessionId > 0) {
            $excludeMemberId = $sessionSeed['weekOffset'] >= 0 ? $currentMemberSeedId : 0;
            seedSessionEnrollmentCount($db, $sessionId, $memberIds, (int) $sessionSeed['occupied'], $excludeMemberId);
        }
    }

    $reviewSeedStmt = $db->prepare('INSERT OR REPLACE INTO class_reviews (user_id, session_id, rating, review, created_at) VALUES (?, ?, ?, ?, ?)');
    $reviewSeeds = [
        ['title' => 'IronPump', 'weekOffset' => -1, 'weekday' => 1, 'time' => '07:00:00', 'reviews' => [
            ['memberIndex' => 1, 'rating' => 4, 'review' => 'Heavy but clean. Good tempo and a proper finisher.'],
            ['memberIndex' => 2, 'rating' => 4, 'review' => 'Strong session with good coaching cues throughout.'],
        ]],
        ['title' => 'ZenFlow Yoga', 'weekOffset' => -1, 'weekday' => 2, 'time' => '18:30:00', 'reviews' => [
            ['memberIndex' => 3, 'rating' => 5, 'review' => 'Calm, focused, and exactly what I needed after a heavy week.'],
            ['memberIndex' => 4, 'rating' => 4, 'review' => 'Smooth pacing and a very solid recovery flow.'],
        ]],
        ['title' => 'Velocity Spin', 'weekOffset' => -1, 'weekday' => 5, 'time' => '07:30:00', 'reviews' => [
            ['memberIndex' => 5, 'rating' => 4, 'review' => 'Fast, sweaty, and a good push on the climbs.'],
            ['memberIndex' => 6, 'rating' => 4, 'review' => 'Excellent playlist and a brutal but fun interval set.'],
        ]],
        ['title' => 'CoreForge Pilates', 'weekOffset' => -1, 'weekday' => 3, 'time' => '09:00:00', 'reviews' => [
            ['memberIndex' => 7, 'rating' => 4, 'review' => 'Precise coaching and a strong core burn without beating me up.'],
            ['memberIndex' => 8, 'rating' => 5, 'review' => 'Great control work and a surprisingly tough session.'],
        ]],
        ['title' => 'PulseBox', 'weekOffset' => -1, 'weekday' => 5, 'time' => '17:30:00', 'reviews' => [
            ['memberIndex' => 9, 'rating' => 5, 'review' => 'Sharp combinations and a conditioning block that kept me honest.'],
            ['memberIndex' => 10, 'rating' => 4, 'review' => 'A little savage, but the structure made it easy to follow.'],
        ]],
        ['title' => 'Mobility Reset', 'weekOffset' => -1, 'weekday' => 4, 'time' => '12:15:00', 'reviews' => [
            ['memberIndex' => 11, 'rating' => 4, 'review' => 'Great recovery session with plenty of useful stretches.'],
            ['memberIndex' => 12, 'rating' => 5, 'review' => 'Exactly the kind of reset I wanted midweek.'],
        ]],
        ['title' => 'HIIT Forge', 'weekOffset' => -1, 'weekday' => 4, 'time' => '06:45:00', 'reviews' => [
            ['memberIndex' => 13, 'rating' => 5, 'review' => 'Brutal conditioning, but the pacing was spot on.'],
            ['memberIndex' => 14, 'rating' => 4, 'review' => 'Hard session with clear structure and good recovery breaks.'],
        ]],
        ['title' => 'Recovery Lab', 'weekOffset' => -1, 'weekday' => 0, 'time' => '10:00:00', 'reviews' => [
            ['memberIndex' => 15, 'rating' => 4, 'review' => 'Perfect reset session to finish the week.'],
            ['memberIndex' => 16, 'rating' => 5, 'review' => 'Calm, useful, and exactly the right pace for recovery.'],
        ]],
    ];

    foreach ($reviewSeeds as $reviewSeed) {
        $startAt = scheduleSeedStart((int) $reviewSeed['weekday'], (int) $reviewSeed['weekOffset'], $reviewSeed['time']);
        $sessionLookupStmt->execute([$classIds[$reviewSeed['title']], $startAt]);
        $sessionId = (int) $sessionLookupStmt->fetchColumn();
        if ($sessionId > 0) {
            foreach ($reviewSeed['reviews'] as $reviewData) {
                $memberId = $memberIds[$reviewData['memberIndex']] ?? 0;
                if ($memberId > 0) {
                    $reviewSeedStmt->execute([$memberId, $sessionId, $reviewData['rating'], $reviewData['review'], date('Y-m-d H:i:s')]);
                }
            }
        }
    }

    if ((int) $db->query('SELECT COUNT(*) FROM equipment')->fetchColumn() === 0) {
        $equipmentSeed = [
            ['Treadmills', 'Main Training Area', 8, 6, 'available', 'Two units are reserved for interval work.'],
            ['Rowers', 'Cardio Zone', 5, 2, 'limited', 'High-demand during peak hours.'],
            ['Dumbbell Rack', 'Strength Floor', 12, 12, 'available', 'Full rack available for free weight work.'],
            ['Cable Stations', 'Strength Floor', 4, 3, 'limited', 'One station is rotated for maintenance checks.'],
            ['Battle Ropes', 'Functional Zone', 3, 1, 'maintenance', 'One rope set is temporarily out of service.'],
        ];

        $insertEquipment = $db->prepare('INSERT INTO equipment (name, area, total_units, available_units, status, notes) VALUES (?, ?, ?, ?, ?, ?)');
        foreach ($equipmentSeed as $item) {
            $insertEquipment->execute($item);
        }
    }
}

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec('PRAGMA foreign_keys = ON;');
        
    if (!tableExists($db, 'users')) {
        bootstrapGymData($db);
    }
} catch (PDOException $e) {
    die('Erro ao conectar com a base de dados: ' . $e->getMessage());
}