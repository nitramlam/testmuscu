CREATE DATABASE IF NOT EXISTS musculation_db;

USE musculation_db;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255)
);

-- Table des jours
CREATE TABLE IF NOT EXISTS days (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_name VARCHAR(255)
);

-- Table des séances
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_name VARCHAR(255),
    user_id INT,
    day_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (day_id) REFERENCES days(id)
);

-- Table des exercices
CREATE TABLE IF NOT EXISTS exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT,
    exercise_name VARCHAR(255),
    weight DECIMAL(10, 2),
    sets INT,
    reps INT,
    objective_weight DECIMAL(10, 2),
    FOREIGN KEY (session_id) REFERENCES sessions(id)
);

-- Table intermédiaire pour lier plusieurs séances à un exercice
CREATE TABLE IF NOT EXISTS exercise_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exercise_id INT,
    session_id INT,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id),
    FOREIGN KEY (session_id) REFERENCES sessions(id)
);

INSERT INTO users (name) VALUES ('John Doe');

-- Ajout de jours d'exemple
INSERT INTO days (day_name) VALUES ('Lundi'), ('Mardi'), ('Mercredi'), ('Jeudi'), ('Vendredi'), ('Samedi'), ('Dimanche');