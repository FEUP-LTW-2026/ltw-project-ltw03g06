PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS class_reviews;
DROP TABLE IF EXISTS session_enrollments;
DROP TABLE IF EXISTS class_sessions;
DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS equipment;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
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
    image_url TEXT,
    specializations TEXT
);

CREATE TABLE classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    duration INTEGER NOT NULL,
    intensity TEXT NOT NULL,
    category TEXT NOT NULL,
    image_url TEXT,
    trainer_id INTEGER REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE enrollments (
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    class_id INTEGER REFERENCES classes(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, class_id)
);

CREATE TABLE class_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL REFERENCES classes(id) ON DELETE CASCADE,
    start_at TEXT NOT NULL,
    capacity INTEGER NOT NULL,
    room TEXT NOT NULL,
    UNIQUE(class_id, start_at)
);

CREATE TABLE session_enrollments (
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    session_id INTEGER NOT NULL REFERENCES class_sessions(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, session_id)
);

CREATE TABLE class_reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    session_id INTEGER NOT NULL REFERENCES class_sessions(id) ON DELETE CASCADE,
    rating INTEGER NOT NULL CHECK(rating BETWEEN 1 AND 5),
    review TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, session_id)
);

CREATE TABLE equipment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    area TEXT NOT NULL,
    total_units INTEGER NOT NULL,
    available_units INTEGER NOT NULL,
    status TEXT NOT NULL CHECK(status IN ('available', 'limited', 'maintenance')),
    notes TEXT,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (id, name, email, password, role, bio, image_url, specializations) VALUES
(1, 'Admin', 'admin', 'p4s5w0rd', 'admin', NULL, NULL, NULL),
(2, 'Member', 'member', '1234', 'member', NULL, NULL, NULL),
(3, 'Marcus Reed', 'reed@ltwgym.com', '1234', 'trainer', 'Functional strength coach focused on clean movement patterns, core stability, and disciplined nutrition.', 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?q=80&w=600&auto=format&fit=crop', 'Functional Strength, Nutrition Coaching'),
(4, 'Elena Vega', 'elena@ltwgym.com', '1234', 'trainer', 'High-energy cycling and conditioning specialist with a focus on endurance and athletic rhythm.', 'assets/img/trainers/elena-vega.jpg', 'Cycling, Endurance Training'),
(5, 'Dante Cross', 'cannons@ltwgym.com', '1234', 'trainer', 'Hypertrophy coach who blends intensity blocks with structured recovery to build size and strength.', 'assets/img/trainers/dante-cross.jpg', 'Hypertrophy, Contest Preparation, Body Composition'),
(6, 'Nora Kim', 'nora@ltwgym.com', '1234', 'trainer', 'Flow and mobility coach with a calm, technical approach to movement and recovery.', 'assets/img/trainers/nora-kim.jpg', 'Yoga, Mobility, Recovery'),
(7, 'Leo Santos', 'leo@ltwgym.com', '1234', 'trainer', 'Boxing and metabolic conditioning coach bringing sharp timing and explosive cardio work.', 'assets/img/trainers/leo-santos.jpg', 'Boxing, Conditioning, HIIT'),
(8, 'Mila Hart', 'mila@ltwgym.com', '1234', 'trainer', 'Pilates and recovery-focused coach who keeps classes controlled, precise, and joint-friendly.', 'assets/img/trainers/mila-hart.jpg', 'Pilates, Recovery, Core Stability'),
(9, 'Alex Morgan', 'alex@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(10, 'Sophia Lane', 'sophia@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(11, 'Daniel Cruz', 'daniel@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(12, 'Maria Silva', 'maria@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(13, 'Noah Bennett', 'noah@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(14, 'Ava Brooks', 'ava@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(15, 'Luca Martins', 'luca@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(16, 'Zoe Turner', 'zoe@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(17, 'Max Oliveira', 'max@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(18, 'Iris Costa', 'iris@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(19, 'Henry Scott', 'henry@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(20, 'Ella Mendes', 'ella@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(21, 'Olivia Ward', 'olivia@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(22, 'Ethan Stone', 'ethan@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(23, 'Grace Hall', 'grace@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(24, 'Felix Costa', 'felix@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(25, 'Maya Pereira', 'maya@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(26, 'Julian Fox', 'julian@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(27, 'Ruby Evans', 'ruby@ltwgym.com', '1234', 'member', NULL, NULL, NULL),
(28, 'Adam Reed', 'adam@ltwgym.com', '1234', 'member', NULL, NULL, NULL);

INSERT INTO classes (id, title, description, duration, intensity, category, image_url, trainer_id) VALUES
(1, 'IronPump', 'Barbell and weight training designed to build strength with a high-energy pace.', 45, 'High Intensity', 'Strength', 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=600&auto=format&fit=crop', 3),
(2, 'ZenFlow Yoga', 'Find your balance. Improve flexibility, posture, and reduce stress with mindful flow.', 60, 'Low Intensity', 'Yoga', 'https://images.unsplash.com/photo-1549576490-b0b4831ef60a?q=80&w=600&auto=format&fit=crop', 6),
(3, 'Velocity Spin', 'Indoor cycling intervals built to push endurance and keep the heart rate high.', 50, 'High Intensity', 'Cardio', 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=600&auto=format&fit=crop', 4),
(4, 'CoreForge Pilates', 'Low-impact precision work for posture, stability, and deep core control.', 55, 'Low Intensity', 'Pilates', 'https://images.unsplash.com/photo-1518611012118-696072aa579a?q=80&w=600&auto=format&fit=crop', 8),
(5, 'PulseBox', 'Boxing combinations paired with conditioning rounds for explosive cardio.', 45, 'High Intensity', 'Boxing', 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=600&auto=format&fit=crop', 7),
(6, 'Mobility Reset', 'A mobility and recovery session to unlock tight joints and restore movement quality.', 40, 'Low Intensity', 'Mobility', 'https://images.unsplash.com/photo-1518611012118-696072aa579a?q=80&w=600&auto=format&fit=crop', 6),
(7, 'HIIT Forge', 'Fast-paced intervals that combine strength, speed, and brutal conditioning blocks.', 35, 'High Intensity', 'HIIT', 'https://images.unsplash.com/photo-1518611012118-696072aa579a?q=80&w=600&auto=format&fit=crop', 7),
(8, 'Recovery Lab', 'Breathing, stretching, and decompression work for a better recovery cycle.', 30, 'Low Intensity', 'Recovery', 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?q=80&w=600&auto=format&fit=crop', 8);

INSERT INTO class_sessions (id, class_id, start_at, capacity, room) VALUES
(1, 1, '2026-05-22 07:00:00', 12, 'Main Floor'),
(2, 1, '2026-06-01 18:00:00', 6, 'Main Floor'),
(3, 2, '2026-05-22 18:30:00', 14, 'Studio A'),
(4, 2, '2026-05-20 18:30:00', 14, 'Studio A'),
(5, 3, '2026-05-23 07:30:00', 16, 'Cycling Hall'),
(6, 3, '2026-05-16 07:30:00', 16, 'Cycling Hall'),
(7, 3, '2026-06-03 19:00:00', 8, 'Cycling Hall'),
(8, 4, '2026-05-27 09:00:00', 10, 'Studio B'),
(9, 4, '2026-05-21 09:00:00', 12, 'Studio B'),
(10, 5, '2026-05-23 17:30:00', 14, 'Fight Zone'),
(11, 5, '2026-06-04 17:30:00', 14, 'Fight Zone'),
(12, 6, '2026-05-27 12:15:00', 5, 'Recovery Studio'),
(13, 6, '2026-05-15 12:15:00', 10, 'Recovery Studio'),
(14, 7, '2026-05-28 06:45:00', 18, 'Main Floor'),
(15, 7, '2026-06-05 06:45:00', 4, 'Main Floor'),
(16, 8, '2026-05-31 10:00:00', 10, 'Recovery Studio'),
(17, 8, '2026-05-18 10:00:00', 10, 'Recovery Studio'),
(18, 6, '2026-06-07 12:15:00', 10, 'Recovery Studio');

INSERT INTO session_enrollments (user_id, session_id) VALUES
(2, 2),
(2, 4),
(9, 2),
(10, 2),
(11, 2),
(12, 2),
(13, 2),
(9, 1),
(10, 1),
(11, 1),
(12, 3),
(13, 3),
(14, 5),
(15, 5),
(14, 7),
(15, 7),
(17, 7),
(18, 7),
(19, 7),
(20, 7),
(21, 7),
(16, 7),
(17, 8),
(18, 8),
(19, 9),
(20, 10),
(21, 10),
(22, 11),
(23, 12),
(24, 12),
(11, 12),
(12, 12),
(13, 12),
(25, 14),
(26, 15),
(14, 15),
(15, 15),
(27, 16),
(28, 17),
(9, 18);

INSERT INTO class_reviews (user_id, session_id, rating, review, created_at) VALUES
(9, 17, 4, 'Great recovery session with plenty of useful stretches.', '2026-05-18 12:30:00'),
(10, 17, 5, 'Exactly the kind of reset I wanted midweek.', '2026-05-18 12:31:00'),
(11, 9, 4, 'Heavy but clean. Good tempo and a proper finisher.', '2026-05-21 10:15:00'),
(12, 9, 4, 'Strong session with good coaching cues throughout.', '2026-05-21 10:17:00'),
(13, 13, 5, 'Calm, focused, and exactly what I needed after a heavy week.', '2026-05-15 14:00:00'),
(14, 13, 4, 'Smooth pacing and a very solid recovery flow.', '2026-05-15 14:02:00'),
(15, 6, 4, 'Fast, sweaty, and a good push on the climbs.', '2026-05-16 08:20:00'),
(16, 6, 4, 'Excellent playlist and a brutal but fun interval set.', '2026-05-16 08:22:00'),
(17, 4, 4, 'Precise coaching and a strong core burn without beating me up.', '2026-05-20 19:10:00'),
(18, 4, 5, 'Great control work and a surprisingly tough session.', '2026-05-20 19:12:00'),
(19, 10, 5, 'Sharp combinations and a conditioning block that kept me honest.', '2026-05-23 18:10:00'),
(20, 10, 4, 'A little savage, but the structure made it easy to follow.', '2026-05-23 18:12:00'),
(21, 1, 4, 'Brutal but clean. Good tempo and a proper finisher.', '2026-05-22 08:00:00'),
(22, 1, 4, 'Strong session with good coaching cues throughout.', '2026-05-22 08:02:00'),
(23, 5, 5, 'Brutal conditioning, but the pacing was spot on.', '2026-05-23 08:30:00'),
(24, 5, 4, 'Hard session with clear structure and good recovery breaks.', '2026-05-23 08:32:00');

INSERT INTO equipment (name, area, total_units, available_units, status, notes) VALUES
('Treadmills', 'Main Training Area', 8, 6, 'available', 'Two units are reserved for interval work.'),
('Rowers', 'Cardio Zone', 5, 2, 'limited', 'High-demand during peak hours.'),
('Dumbbell Rack', 'Strength Floor', 12, 12, 'available', 'Full rack available for free weight work.'),
('Cable Stations', 'Strength Floor', 4, 3, 'limited', 'One station is rotated for maintenance checks.'),
('Battle Ropes', 'Functional Zone', 3, 1, 'maintenance', 'One rope set is temporarily out of service.'),
('Bench Press Stations', 'Strength Floor', 4, 4, 'available', 'Popular during peak strength sessions.'),
('Assault Bikes', 'Cardio Zone', 6, 5, 'available', 'Great for sprint intervals and warmups.'),
('Kettlebell Corner', 'Functional Zone', 14, 11, 'limited', 'Heavier bells rotate quickly during class blocks.'),
('Stretch Mats', 'Mobility Studio', 20, 17, 'available', 'Keep one set aside for guided mobility work.'),
('SkiErgs', 'Cardio Zone', 2, 1, 'limited', 'One unit is shared with endurance classes.'),
('Smith Machines', 'Strength Floor', 2, 2, 'available', 'Allocated for accessory work and beginner sets.');