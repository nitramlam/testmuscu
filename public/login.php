<?php
session_start();

$host = 'mysql';
$dbname = 'musculation_db';
$username = 'root';
$password = 'muscu1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_name = $_POST['user_name'] ?? null;

        if ($user_name) {
            // Vérifier si l'utilisateur existe dans la base de données
            $stmt = $pdo->prepare("SELECT * FROM users WHERE name = :name");
            $stmt->execute([':name' => $user_name]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Stocker les informations de l'utilisateur dans la session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header("Location: sessions.php"); // Redirige vers la page des sessions après connexion
                exit;
            } else {
                $error = "Utilisateur introuvable. Veuillez réessayer.";
            }
        } else {
            $error = "Veuillez fournir un nom d'utilisateur.";
        }
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
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