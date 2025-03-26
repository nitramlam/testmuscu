<?php
include 'header.php'; // Assure que la session est démarrée et l'utilisateur est identifié

// Informations de connexion à la base de données
$host = 'mysql';
$dbname = 'musculation_db';
$username = 'root';
$password = 'muscu1234';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Utilisateur non connecté. Veuillez vous connecter.");
    }

    // Récupérer les sessions de l'utilisateur connecté
    session_start();
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Utilisateur non connecté. Veuillez vous connecter.");
    }

    $stmt = $pdo->prepare("
        SELECT id, session_name, user_id 
        FROM sessions
        WHERE user_id = :user_id
    ");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ajouter une session pour cet utilisateur
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_session'])) {
        $session_name = !empty($_POST['session_name']) ? trim($_POST['session_name']) : null;
        if ($session_name) {
            $sql = "INSERT INTO sessions (session_name, user_id) VALUES (:session_name, :user_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':session_name' => $session_name,
                ':user_id' => $_SESSION['user_id']
            ]);
            // Redirection pour éviter la soumission multiple
            header("Location: sessions.php");
            exit;
        } else {
            $error = "Le nom de la session est requis.";
        }
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessions de <?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?></title>
</head>

<body>
    <h1>Sessions de <?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?></h1>

    <?php if (!empty($sessions)): ?>
        <ul>
            <?php foreach ($sessions as $session): ?>
                <li>
                    <strong>Session :</strong> <?= htmlspecialchars($session['session_name']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucune session trouvée pour cet utilisateur.</p>
    <?php endif; ?>

    <h2>Ajouter une session</h2>
    <!-- Formulaire pour ajouter une session -->
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label for="session_name">Nom de la session :</label>
        <input type="text" name="session_name" id="session_name" required><br>
        <button type="submit" name="add_session">Ajouter</button>
    </form>

    <a href="users.php">Retour à la sélection des utilisateurs</a>
</body>

</html>