PRAGMA foreign_keys = ON;

-- Tabela de Usuários (Membros, Treinadores e Admins)
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
    bio TEXT, -- Usado para treinadores
    image_url TEXT
);

-- Tabela de Aulas (Classes)
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

-- Tabela de Inscrições nas aulas (Enrollments)
CREATE TABLE enrollments (
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    class_id INTEGER REFERENCES classes(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, class_id)
);

-- INSERINDO DADOS DE TESTE (Mockup)
-- Nota: As senhas estão em texto plano para facilitar o teste inicial, 
-- mas no PHP você deve usar password_hash() e password_verify().

-- Credenciais do README
INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin', 'p4s5w0rd', 'admin');
INSERT INTO users (name, email, password, role) VALUES ('Member', 'member', '1234', 'member');
INSERT INTO users (name, email, password, role, bio, image_url) VALUES ('John Doe', 'trainer', '1234', 'trainer', 'Expert in functional strength...', 'assets/img/george-dagerotip-mYaf3lj2Vs8-unsplash.jpg');

-- Inserindo as Aulas do seu HTML
INSERT INTO classes (title, description, duration, intensity, category, image_url, trainer_id) VALUES 
('IronPump', 'Barbell and weight training designed to tone and build muscle with the best beats in the gym.', 45, 'High Intensity', 'Strength', 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=600&auto=format&fit=crop', 3),
('ZenFlow Yoga', 'Find your balance. Improve flexibility, posture, and reduce stress with guidance from our expert instructors.', 60, 'Low Intensity', 'Yoga', 'https://images.unsplash.com/photo-1549576490-b0b4831ef60a?q=80&w=600&auto=format&fit=crop', 3),
('Velocity Spin', 'Ride to the limit in this indoor cycling class that will burn calories and test your endurance.', 50, 'High Intensity', 'Cardio', 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=600&auto=format&fit=crop', 3);