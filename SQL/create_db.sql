-- Création de la base de données
CREATE DATABASE IF NOT EXISTS musculation_db;
USE musculation_db;

-- Table des utilisateurs
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL
);

-- Table des sessions (chaque utilisateur a ses propres sessions)
CREATE TABLE sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des exercices (chaque session contient plusieurs exercices)
CREATE TABLE exercises (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    weight DECIMAL(5,2),
    repetitions INT,
    sets INT,
    target_weight DECIMAL(5,2), -- Objectif de poids
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
);

-- Ajout de quelques utilisateurs
INSERT INTO users (name) VALUES ('Martin'), ('Olivia');

-- Ajout de sessions pour chaque utilisateur
INSERT INTO sessions (user_id, name) VALUES
(1, 'Bras'),
(1, 'Jambes'),
(2, 'Abdos'),
(2, 'Jambes');

-- Ajout d'exercices pour chaque session
INSERT INTO exercises (session_id, name, weight, repetitions, sets, target_weight) VALUES
-- Exercices pour Martin
(1, 'Curl biceps', 12.5, 10, 3, 15.0),
(1, 'Pompes', NULL, 15, 3, NULL),
(2, 'Squat', 50, 10, 4, 60),
(2, 'Presse', 80, 12, 3, 100),
-- Exercices pour Olivia
(3, 'Crunch', NULL, 20, 3, NULL),
(3, 'Planche', NULL, 30, 3, NULL),
(4, 'Fentes', 20, 12, 3, 25),
(4, 'Presse', 60, 15, 3, 80);