<?php
require 'db.php';

if (!isset($_GET['session_id'])) {
    die("Session non sélectionnée.");
}

$session_id = $_GET['session_id'];

// Récupérer la session
$sql = "SELECT name FROM sessions WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$session_id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    die("Session introuvable.");
}

// Récupérer les exercices
$sql = "SELECT * FROM exercises WHERE session_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$session_id]);
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exercices de <?= htmlspecialchars($session['name']); ?></title>
</head>
<body>
    <h2>Exercices de <?= htmlspecialchars($session['name']); ?></h2>
    <ul>
        <?php foreach ($exercises as $exercise): ?>
            <li>
                <?= htmlspecialchars($exercise['name']); ?> : 
                <?= $exercise['weight'] ? $exercise['weight'] . " kg" : "Poids libre"; ?>, 
                <?= $exercise['repetitions']; ?> répétitions, 
                <?= $exercise['sets']; ?> séries
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>