<?php
require 'db.php';

try {
    // Création de la table `users`
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            token VARCHAR(255) DEFAULT NULL,
            token_expiry DATETIME DEFAULT NULL
        )
    ");

    // Création de la table `sessions`
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            user_id INT NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    // Création de la table `exercises`
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS exercises (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id INT NOT NULL,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            weight INT DEFAULT 0,
            sets INT DEFAULT 0,
            repetitions INT DEFAULT 0,
            target_weight INT DEFAULT 0,
            FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    echo "Les tables ont été créées avec succès.";
} catch (PDOException $e) {
    die("Erreur lors de la création des tables : " . $e->getMessage());
}
?>