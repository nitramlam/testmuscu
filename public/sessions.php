<?php
require 'db.php';
include 'header.php'; // Vérifie le token et récupère l'utilisateur connecté

// Récupérer les sessions de l'utilisateur connecté via le token
$sql = "SELECT * FROM sessions WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]); // $user_id est défini dans header.php
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Sessions de <?= htmlspecialchars($user_name); ?></title>
</head>

<body>
    <h2>Sessions de <?= htmlspecialchars($user_name); ?></h2>
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