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
        <h1 class="text-2xl font-bold mb-6 text-center">Exercices</h1>

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
            <p class="text-gray-500 text-center">Aucun exercice dans cette session</p>
        <?php else:
            foreach ($exercises as $ex): ?>
                <div class="p-6 mb-6 bg-gray-50 border border-gray-200 rounded-lg shadow-md">
                    <h3 class="font-bold text-xl mb-2"><?= htmlspecialchars($ex['name']) ?></h3>

                    <form action="exercises.php?session_id=<?= $session_id ?>" method="POST" class="space-y-4">
                        <input type="hidden" name="exercise_session_id" value="<?= $ex['exercise_session_id'] ?>">

                        <div>
                            <label for="weight" class="block text-lg font-medium text-gray-700">Poids (kg)</label>
                            <input type="number" name="weight" value="<?= $ex['weight'] ?>" max="999" class="border border-gray-300 rounded-lg p-2 w-full" required />
                        </div>

                        <div>
                            <label for="repetitions" class="block text-lg font-medium text-gray-700">Répétitions</label>
                            <input type="number" name="repetitions" value="<?= $ex['repetitions'] ?>" max="999" class="border border-gray-300 rounded-lg p-2 w-full" required />
                        </div>

                        <div>
                            <label for="sets" class="block text-lg font-medium text-gray-700">Séries</label>
                            <input type="number" name="sets" value="<?= $ex['sets'] ?>" max="999" class="border border-gray-300 rounded-lg p-2 w-full" required />
                        </div>

                        <div>
                            <label for="target_weight" class="block text-lg font-medium text-gray-700">Objectif de poids (kg)</label>
                            <input type="number" name="target_weight" value="<?= $ex['target_weight'] ?>" max="999" class="border border-gray-300 rounded-lg p-2 w-full" required />
                        </div>

                        <div class="flex justify-between items-center">
                            <button type="submit" name="update" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition">Mettre à jour</button>
                            <button type="button" onclick="supprimerExercise(<?= $ex['id'] ?>)" class="text-red-500 hover:text-red-700">Supprimer</button>
                        </div>
                    </form>
                </div>
            <?php endforeach;
        endif; ?>
    </div>

</body>

</html>