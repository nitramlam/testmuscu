<?php
include 'header.php'; // Vérifie le token et récupère l'utilisateur connecté

// Récupérer la session et vérifier les droits
$session_id = $_GET['session_id'] ?? null;
$sql = "SELECT s.* FROM sessions s WHERE s.id = ? AND s.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$session_id, $user_id]);
$session = $stmt->fetch();

if (!$session) {
    die("Session non trouvée ou accès refusé.");
}

// Supprimer un exercice seulement de cette session
if (isset($_GET['delete_exercise'])) {
    $exercise_id = (int)$_GET['delete_exercise'];
    
    // Supprime seulement la liaison dans exercises_sessions
    $sql = "DELETE FROM exercises_sessions 
            WHERE exercise_id = ? AND session_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$exercise_id, $session_id]);
    
    header("Location: exercises.php?session_id=$session_id");
    exit();
}

// Récupérer les exercices de CETTE session seulement
$sql = "SELECT e.id, e.name 
        FROM exercises e
        JOIN exercises_sessions es ON e.id = es.exercise_id
        WHERE es.session_id = ?
        ORDER BY e.name";
$stmt = $pdo->prepare($sql);
$stmt->execute([$session_id]);
$exercises = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercices - <?= htmlspecialchars($session['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="max-w-md mx-auto mt-8 p-6 bg-white rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4">
        Exercices: <?= htmlspecialchars($session['name']) ?>
    </h1>
    
    <a href="sessions.php" class="text-blue-500 mb-4 inline-block">← Retour</a>

    <!-- Liste des exercices -->
    <ul class="space-y-2">
        <?php foreach ($exercises as $exercise): ?>
            <li class="flex justify-between items-center p-2 bg-gray-50 rounded">
                <span><?= htmlspecialchars($exercise['name']) ?></span>
                <a href="exercises.php?session_id=<?= $session_id ?>&delete_exercise=<?= $exercise['id'] ?>" 
                   onclick="return confirm('Supprimer cet exercice de votre session ?')"
                   class="text-red-500 hover:text-red-700">
                    Supprimer
                </a>
            </li>
        <?php endforeach; ?>
        
        <?php if (empty($exercises)): ?>
            <li class="text-gray-500">Aucun exercice dans cette session</li>
        <?php endif; ?>
    </ul>
</div>

</body>
</html>