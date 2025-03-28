<?php
session_start(); // Démarrage de la session

require 'db.php'; // Connexion à la base de données

if (!isset($_SESSION['token'])) {
    die("Erreur : Aucun utilisateur connecté.");
}

$token = $_SESSION['token'];

// Vérifier le token dans la base de données
$sql = "SELECT id, name, token_expiry FROM users WHERE token = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Erreur : Utilisateur non trouvé ou token invalide.");
}

// Vérifier si le token a expiré
if (empty($user['token_expiry']) || strtotime($user['token_expiry']) < time()) {
    // Détruire la session si le token a expiré
    session_destroy();
    die("Erreur : Token expiré. Veuillez vous reconnecter.");
}

// Stocker les informations de l'utilisateur dans des variables globales
$user_id = $user['id'];
$user_name = $user['name'];

// Régénérer le token et mettre à jour son expiration
$new_token = bin2hex(random_bytes(16));
$new_expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Expire dans 1 heure
$stmt = $pdo->prepare("UPDATE users SET token = ?, token_expiry = ? WHERE id = ?");
$stmt->execute([$new_token, $new_expiry, $user_id]);

// Mettre à jour le token dans la session
$_SESSION['token'] = $new_token;

// Récupérer le session_id depuis l'URL si disponible
$session_id = $_GET['session_id'] ?? null;

// Ajouter les informations de l'utilisateur et de la session dans l'URL pour les liens du header
$query_params = http_build_query(['user_id' => $user_id] + ($session_id ? ['session_id' => $session_id] : []));
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programme Salle de Sport</title>
    <style>
        nav {
            border-bottom: 1px solid #ddd;
            background-color: #f4f4f4;
            padding: 10px;
        }

        nav a {
            color: #333;
            margin-right: 15px;
            text-decoration: none;
            font-weight: bold;
        }

        nav a:hover {
            color: #007BFF;
        }
    </style>
</head>

<body>
    <nav>
        <a href="index.php?<?= $query_params ?>">Accueil</a>
        <a href="sessions.php?<?= $query_params ?>">Sessions</a>
        <a href="exercises.php?<?= $query_params ?>">Exercices</a>
        <a href="index.php">Déconnexion</a>
    </nav>
</body>

</html>