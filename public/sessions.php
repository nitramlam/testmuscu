<?php
require 'db.php';

if (!isset($_GET['user_id'])) {
    die("Utilisateur non sélectionné.");
}

$user_id = $_GET['user_id'];

// Récupérer l'utilisateur
$sql = "SELECT name FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Utilisateur introuvable.");
}

// Récupérer les sessions de l'utilisateur
$sql = "SELECT * FROM sessions WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sessions de <?= htmlspecialchars($user['name']); ?></title>
</head>
<body>
    <h2>Sessions de <?= htmlspecialchars($user['name']); ?></h2>
    <ul>
        <?php foreach ($sessions as $session): ?>
            <li>
                <a href="exercises.php?session_id=<?= $session['id']; ?>">
                    <?= htmlspecialchars($session['name']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>