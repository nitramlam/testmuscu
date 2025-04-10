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
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Programme Salle de Sport') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind-config.js"></script>
    <link href="custom.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>


<body class="bg-gray-50 min-h-screen flex flex-col font-sans"></body>
    <!-- Header avec vos styles -->
    <header class="nav-header shadow-nav animate-fade-in-down">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4 nav-container">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-dumbbell text-white text-2xl"></i>
                    <span class="text-blue text-xl font-bold">
                        <?= htmlspecialchars($user_name) ?>
                    </span>
                </div>

                <nav class="flex items-center space-x-6 nav-links">
                    <a href="manage_users.php?<?= $query_params ?>" class="nav-link">
                        <i class="fas fa-users mr-2"></i>Utilisateurs
                    </a>
                    <a href="sessions.php?<?= $query_params ?>" class="nav-link">
                        <i class="fas fa-calendar-alt mr-2"></i>Sessions
                    </a>
                    <a href="exercises_page.php" class="nav-link">
                        <i class="fas fa-dumbbell mr-2"></i>Exercices
                    </a>
                    <a href="index.php" class="nav-link text-orange-300">
                        <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                    </a>
                </nav>
            </div>
        </div>
    </header>

</body>

</html>