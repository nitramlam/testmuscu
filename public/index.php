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
    <style>
        /* Personnalisation du formulaire */
        .form-container {
            background: linear-gradient(145deg, #ff6f61, #d13c3c);
            box-shadow: 10px 10px 20px rgba(0, 0, 0, 0.1), -10px -10px 20px rgba(255, 255, 255, 0.3);
        }

        .form-container h1 {
            font-family: 'Arial', sans-serif;
            color: white;
        }

        .form-container input,
        .form-container select,
        .form-container button {
            transition: all 0.3s ease;
        }

        .form-container input:focus,
        .form-container select:focus,
        .form-container button:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .form-container label {
            color: #fff;
            font-weight: 600;
        }

        .form-container input,
        .form-container select {
            background-color: #ffffff;
            border: 2px solid #ddd;
            padding: 12px 15px;
            border-radius: 8px;
            width: 100%;
            margin-top: 8px;
        }

        .form-container button {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            border-radius: 8px;
            padding: 12px;
            border: none;
            width: 100%;
            margin-top: 15px;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #45a049;
        }

        .error-message {
            color: #ff6f61;
            margin-bottom: 15px;
        }
    </style>
</head>

<body class="font-sans text-gray-800">

    <div class="form-container max-w-md mx-auto mt-16 p-8 rounded-xl shadow-lg">
        <h1 class="text-3xl text-center mb-6">Sélectionnez un utilisateur</h1>

        <?php if (!empty($error)): ?>
            <p class="error-message text-center"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <div>
                <label for="user_id" class="block text-xl">Utilisateur :</label>
                <select name="user_id" id="user_id" required>
                    <option value="">-- Sélectionnez un utilisateur --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit">Se connecter</button>
        </form>
    </div>

</body>

</html>