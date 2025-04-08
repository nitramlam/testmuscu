<?php
include 'header.php';

// 1. Vérification session
$session_id = (int) ($_GET['session_id'] ?? 0);
$stmt = $pdo->prepare("SELECT id FROM sessions WHERE id = ? AND user_id = ?");
$stmt->execute([$session_id, $user_id]);
if (!$stmt->fetch())
    die("Session invalide");

// 2. Traitement suppression
if (isset($_GET['delete'])) {
    $exercise_id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM exercises_sessions WHERE exercise_id = ? AND session_id = ?")
        ->execute([$exercise_id, $session_id]);
    // Pas de redirection
}

// 3. Traitement modification des exercices
if (isset($_POST['update'])) {
    $exercise_session_id = (int) $_POST['exercise_session_id'];
    $weight = (float) $_POST['weight'];
    $repetitions = (int) $_POST['repetitions'];
    $sets = (int) $_POST['sets'];
    $target_weight = (float) $_POST['target_weight'];

    // Mise à jour des informations de l'exercice pour cette session
    $stmt = $pdo->prepare("UPDATE exercises_sessions SET 
                           weight = ?, repetitions = ?, sets = ?, target_weight = ? 
                           WHERE id = ?");
    $stmt->execute([$weight, $repetitions, $sets, $target_weight, $exercise_session_id]);

    // Confirmation et retour
   
    exit();
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
                window.location.href = "exercises.php?session_id=<?= $session_id ?>&delete=" + exerciseId;
            }
        }
    </script>
</head>

<body class="bg-gray-100">

    <div class="max-w-4xl mx-auto mt-8 p-6 bg-white rounded-lg shadow">
        <h1 class="text-2xl font-bold mb-4">Exercices</h1>

        <?php
        // 4. Affichage des exercices
        $stmt = $pdo->prepare("SELECT es.id AS exercise_session_id, e.id, e.name, 
                                      COALESCE(es.weight, 0) AS weight, 
                                      COALESCE(es.repetitions, 0) AS repetitions, 
                                      COALESCE(es.sets, 0) AS sets, 
                                      COALESCE(es.target_weight, 0) AS target_weight
                               FROM exercises e
                               JOIN exercises_sessions es ON e.id = es.exercise_id
                               WHERE es.session_id = ?");
        $stmt->execute([$session_id]);
        $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($exercises)): ?>
            <p class="text-gray-500">Aucun exercice dans cette session</p>
        <?php else:
            foreach ($exercises as $ex): ?>
                <div class="p-3 border-b">
                    <h3 class="font-bold"><?= htmlspecialchars($ex['name']) ?></h3>
                    <p>Poids : <?= htmlspecialchars($ex['weight']) ?> kg</p>
                    <p>Répétitions : <?= htmlspecialchars($ex['repetitions']) ?></p>
                    <p>Séries : <?= htmlspecialchars($ex['sets']) ?></p>
                    <p>Objectif de poids : <?= htmlspecialchars($ex['target_weight']) ?> kg</p>

                    <!-- Formulaire de modification -->
                    <form action="exercises.php?session_id=<?= $session_id ?>" method="POST" class="mt-4">
                        <input type="hidden" name="exercise_session_id" value="<?= $ex['exercise_session_id'] ?>">

                        <label for="weight" class="block">Poids (kg)</label>
                        <input type="number" name="weight" value="<?= $ex['weight'] ?>" class="border p-2 mb-2 w-full" required />

                        <label for="repetitions" class="block">Répétitions</label>
                        <input type="number" name="repetitions" value="<?= $ex['repetitions'] ?>" class="border p-2 mb-2 w-full" required />

                        <label for="sets" class="block">Séries</label>
                        <input type="number" name="sets" value="<?= $ex['sets'] ?>" class="border p-2 mb-2 w-full" required />

                        <label for="target_weight" class="block">Objectif de poids (kg)</label>
                        <input type="number" name="target_weight" value="<?= $ex['target_weight'] ?>" class="border p-2 mb-2 w-full" required />

                        <button type="submit" name="update" class="bg-blue-500 text-white p-2 rounded mt-4">Mettre à jour</button>
                    </form>

                    <button onclick="supprimerExercise(<?= $ex['id'] ?>)" class="text-red-500 hover:text-red-700 mt-2">
                        × Supprimer
                    </button>
                </div>
            <?php endforeach;
        endif; ?>
    </div>

</body>

</html>