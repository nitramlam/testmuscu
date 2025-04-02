<?php
include 'header.php';

// 1. Vérification session
$session_id = (int)($_GET['session_id'] ?? 0);
$stmt = $pdo->prepare("SELECT id FROM sessions WHERE id = ? AND user_id = ?");
$stmt->execute([$session_id, $user_id]);
if (!$stmt->fetch()) die("Session invalide");

// 2. Traitement suppression
if (isset($_GET['delete'])) {
    $exercise_id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM exercises_sessions WHERE exercise_id = ? AND session_id = ?")
       ->execute([$exercise_id, $session_id]);
    // Pas de redirection
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Exercices</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    function supprimerExercise(exerciseId) {
        if (confirm("Supprimer cet exercice de la session ?")) {
            window.location.href = `?session_id=<?= $session_id ?>&delete=${exerciseId}`;
        }
    }
    </script>
</head>
<body class="bg-gray-100">

<div class="max-w-md mx-auto mt-8 p-6 bg-white rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4">Exercices</h1>
    
    <?php
    // 3. Affichage des exercices (CORRIGÉ)
    $stmt = $pdo->prepare("SELECT e.id, e.name 
                         FROM exercises e
                         JOIN exercises_sessions es ON e.id = es.exercise_id
                         WHERE es.session_id = ?");
    $stmt->execute([$session_id]);
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($exercises)): ?>
        <p class="text-gray-500">Aucun exercice dans cette session</p>
    <?php else: 
        foreach ($exercises as $ex): ?>
            <div class="flex justify-between items-center p-3 border-b">
                <span><?= htmlspecialchars($ex['name']) ?></span>
                <button onclick="supprimerExercise(<?= $ex['id'] ?>)" 
                        class="text-red-500 hover:text-red-700">
                    × Supprimer
                </button>
            </div>
        <?php endforeach;
    endif; ?>
</div>

</body>
</html>