<?php

// Paramètres de connexion
$host = 'mysql'; // Nom du service MySQL dans Docker
$dbname = 'musculation_db'; // Nom de ta base de données
$user = 'root'; // Nom d'utilisateur MySQL
$password = 'muscu1234'; // Mot de passe MySQL

try {
    // Création de la connexion avec PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    // Configuration des options PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

?>