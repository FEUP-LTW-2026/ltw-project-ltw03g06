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

INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin', 'p4s5w0rd', 'admin');
INSERT INTO users (name, email, password, role) VALUES ('Member', 'member', '1234', 'member');

INSERT INTO users (name, email, password, role, bio, image_url, specializations)
VALUES ('John Doe', 'trainer', '1234', 'trainer', 'Expert in functional strength training and personalized nutrition coaching to reach peak performance.', 'assets/img/george-dagerotip-mYaf3lj2Vs8-unsplash.jpg', 'Functional Strength, Nutrition Coaching');

INSERT INTO users (name, email, password, role, bio, image_url, specializations)
VALUES ('John Pork', 'pork@ltwgym.com', '1234', 'trainer', 'Master of Olympic lifting techniques and high-intensity endurance protocols.', 'assets/img/mediamodifier-m6Hw3FybWPA-unsplash.jpg', 'Olympic Lifting, Endurance Training');

INSERT INTO users (name, email, password, role, bio, image_url, specializations)
VALUES ('John Cannons', 'cannons@ltwgym.com', '1234', 'trainer', 'Specializes in hypertrophy, contest preparation, and aesthetic muscle development.', 'assets/img/samuel-girven-Ppua0I4VjqE-unsplash.jpg', 'Hypertrophy, Contest Preparation, Body Composition');

INSERT INTO classes (title, description, duration, intensity, category, image_url, trainer_id) VALUES 
('IronPump', 'Barbell and weight training designed to tone and build muscle with the best beats in the gym.', 45, 'High Intensity', 'Strength', 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=600&auto=format&fit=crop', 5),
('ZenFlow Yoga', 'Find your balance. Improve flexibility, posture, and reduce stress with guidance from our expert instructors.', 60, 'Low Intensity', 'Yoga', 'https://images.unsplash.com/photo-1549576490-b0b4831ef60a?q=80&w=600&auto=format&fit=crop', 3),
('Velocity Spin', 'Ride to the limit in this indoor cycling class that will burn calories and test your endurance.', 50, 'High Intensity', 'Cardio', 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=600&auto=format&fit=crop', 4);

INSERT INTO class_sessions (class_id, start_at, capacity, room) VALUES
(1, '2026-05-22 07:00:00', 18, 'Main Floor'),
(1, '2026-05-19 07:00:00', 18, 'Main Floor'),
(2, '2026-05-22 18:30:00', 16, 'Studio A'),
(2, '2026-05-20 18:30:00', 16, 'Studio A'),
(3, '2026-05-23 07:30:00', 20, 'Cycling Hall');

INSERT INTO session_enrollments (user_id, session_id) VALUES
(2, 2),
(2, 4);

INSERT INTO equipment (name, area, total_units, available_units, status, notes) VALUES
('Treadmills', 'Main Training Area', 8, 6, 'available', 'Two units are reserved for interval work.'),
('Rowers', 'Cardio Zone', 5, 2, 'limited', 'High-demand during peak hours.'),
('Dumbbell Rack', 'Strength Floor', 12, 12, 'available', 'Full rack available for free weight work.'),
('Cable Stations', 'Strength Floor', 4, 3, 'limited', 'One station is rotated for maintenance checks.'),
('Battle Ropes', 'Functional Zone', 3, 1, 'maintenance', 'One rope set is temporarily out of service.');

ALTER TABLE users ADD COLUMN active INTEGER DEFAULT 1;

ALTER TABLE users ADD COLUMN created_at TEXT DEFAULT CURRENT_TIMESTAMP;

UPDATE users SET name=?, email=?, role=? WHERE id=?