<?php
session_start();

require 'db.php'; // Connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = $_POST['user_name'] ?? null;

    if ($user_name) {
        // Vérifier si l'utilisateur existe
        $stmt = $pdo->prepare("SELECT * FROM users WHERE name = :name");
        $stmt->execute([':name' => $user_name]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Générer un token unique
            $token = bin2hex(random_bytes(16));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Expire dans 1 heure

            // Mettre à jour le token et son expiration dans la base de données
            $stmt = $pdo->prepare("UPDATE users SET token = :token, token_expiry = :expiry WHERE id = :id");
            $stmt->execute([':token' => $token, ':expiry' => $expiry, ':id' => $user['id']]);

            // Stocker le token dans la session
            $_SESSION['token'] = $token;

            // Rediriger vers la page des sessions
            header("Location: sessions.php");
            exit;
        } else {
            $error = "Utilisateur introuvable. Veuillez réessayer.";
        }
    } else {
        $error = "Veuillez fournir un nom d'utilisateur.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
</head>

<body>
    <h1>Connexion</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label for="user_name">Nom :</label>
        <input type="text" name="user_name" id="user_name" required><br>
        <button type="submit">Se connecter</button>
    </form>
</body>

</html>