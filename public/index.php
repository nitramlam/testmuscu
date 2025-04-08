<?php
session_start();

require 'db.php'; // Connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;

    if ($user_id) {
        // Vérifier si l'utilisateur existe
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Générer un token unique
            $token = bin2hex(random_bytes(16));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Expire dans 1 heure

            // Mettre à jour le token et son expiration dans la base de données
            $stmt = $pdo->prepare("UPDATE users SET token = :token, token_expiry = :expiry WHERE id = :id");
            if (!$stmt->execute([':token' => $token, ':expiry' => $expiry, ':id' => $user['id']])) {
                die("Erreur : Impossible de mettre à jour le token.");
            }

            // Stocker le token dans la session
            $_SESSION['token'] = $token;

            // Rediriger vers la page des sessions
            header("Location: sessions.php");
            exit;
        } else {
            $error = "Utilisateur introuvable. Veuillez réessayer.";
        }
    } else {
        $error = "Veuillez sélectionner un utilisateur.";
    }
}

// Récupérer tous les utilisateurs pour les afficher dans le formulaire
$stmt = $pdo->query("SELECT id, name FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sélection d'un utilisateur</title>
    <!-- CDN de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-900 font-sans">

    <div class="max-w-md mx-auto mt-16 p-8 bg-white shadow-lg rounded-lg">
        <h1 class="text-3xl text-center text-gray-800 mb-6">Sélectionnez un utilisateur</h1>

        <?php if (!empty($error)): ?>
            <p class="text-red-500 text-center mb-6"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-6">
                <label for="user_id" class="block text-lg font-medium text-gray-700 mb-2">Utilisateur :</label>
                <select name="user_id" id="user_id" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">-- Sélectionnez un utilisateur --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white p-3 rounded-lg hover:bg-blue-600 transition duration-300">
                Se connecter
            </button>
        </form>
    </div>

</body>

</html>