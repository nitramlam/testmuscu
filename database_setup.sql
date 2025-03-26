CREATE DATABASE IF NOT EXISTS musculation_db;

USE musculation_db;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

-- Table des séances
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_name VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des exercices
CREATE TABLE IF NOT EXISTS exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    exercise_name VARCHAR(255) NOT NULL,
    weight DECIMAL(10, 2) DEFAULT 0,
    sets INT DEFAULT 0,
    reps INT DEFAULT 0,
    objective_weight DECIMAL(10, 2) DEFAULT 0,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Table intermédiaire pour lier plusieurs séances à un exercice
CREATE TABLE IF NOT EXISTS exercise_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exercise_id INT NOT NULL,
    session_id INT NOT NULL,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Insérer un utilisateur par défaut
INSERT INTO users (name) VALUES ('John Doe');
