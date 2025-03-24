CREATE DATABASE IF NOT EXISTS musculation_db;

USE musculation_db;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Table des jours
CREATE TABLE IF NOT EXISTS days (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_name VARCHAR(255) NOT NULL
);

-- Table des séances
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_name VARCHAR(255) NOT NULL
);

-- Table des exercices
CREATE TABLE IF NOT EXISTS exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT,
    exercise_name VARCHAR(255) NOT NULL,
    weight DECIMAL(10, 2),
    sets INT,
    reps INT,
    objective_weight DECIMAL(10, 2),
    FOREIGN KEY (session_id) REFERENCES sessions(id)
);

-- Exemple de données
INSERT INTO users (name, email, password) VALUES ('John Doe', 'john@example.com', 'password123');