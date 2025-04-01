<?php
session_start();

require 'db.php';

if (!isset($_SESSION['token'])) {
    header("Location: index.php");
    exit;
}

$token = $_SESSION['token'];

// Vérifier le token dans la base de données
$sql = "SELECT id, name, token_expiry FROM users WHERE token = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: index.php");
    exit;
}

// Vérifier si le token a expiré
if (empty($user['token_expiry']) || strtotime($user['token_expiry']) < time()) {
    // Détruire la session si le token a expiré
    session_destroy();
    header("Location: index.php");
    exit;
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

    <!-- CDN de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-900 font-sans">

    <!-- Navigation -->
    <nav class="bg-blue-600 p-4">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div class="text-white text-2xl font-semibold">
                <span class="font-bold">Bienvenue</span> <?= htmlspecialchars($user_name); ?>
            </div>

            <div class="space-x-6">
                <a href="manage_users.php?<?= $query_params ?>"
                    class="text-white hover:text-blue-300 transition duration-300">Utilisateurs</a>
                <a href="sessions.php?<?= $query_params ?>"
                    class="text-white hover:text-blue-300 transition duration-300">Sessions</a>
                <a href="logout.php" class="text-white hover:text-blue-300 transition duration-300">Déconnexion</a>
            </div>
        </div>
    </nav>

    <!-- Contenu principal de la page -->
    <div class="container mx-auto p-8">
        <!-- Page content here -->
    </div>

</body>

</html>