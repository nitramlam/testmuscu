<?php
session_start();

require 'db.php';

// Vérifier si un token est défini dans la session
if (isset($_SESSION['token'])) {
    $token = $_SESSION['token'];

    // Supprimer le token de la base de données
    $sql = "UPDATE users SET token = NULL, token_expiry = NULL WHERE token = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token]);
}

// Détruire la session
session_destroy();

// Rediriger vers la page d'accueil
header("Location: index.php");
exit;
