<?php
ob_start(); // 🔥 Permet d'utiliser header() plus tard sans erreur
include 'header.php';

// 1. Vérification session
$session_id = (int) ($_GET['session_id'] ?? 0);

$stmt = $pdo->prepare("SELECT id, name FROM sessions WHERE id = ? AND user_id = ?");
$stmt->execute([$session_id, $user_id]);
$session = $stmt->fetch();

if (!$session)
    die("Session invalide");

$session_name = htmlspecialchars($session['name']);

// 2. Suppression
if (isset($_GET['delete'])) {
    $exercise_id = (int) $_GET['delete'];
    $pdo->prepare("DELETE FROM exercises_sessions WHERE exercise_id = ? AND session_id = ?")
        ->execute([$exercise_id, $session_id]);
    header("Location: exercises.php?session_id=$session_id");
    exit();
}

// 3. Modification
if (isset($_POST['update'])) {
    $exercise_session_id = (int) $_POST['exercise_session_id'];
    $weight = (float) $_POST['weight'];
    $repetitions = (int) $_POST['repetitions'];
    $sets = (int) $_POST['sets'];
    $target_weight = (float) $_POST['target_weight'];

    $stmt = $pdo->prepare("UPDATE exercises_sessions SET 
                           weight = ?, repetitions = ?, sets = ?, target_weight = ? 
                           WHERE id = ?");
    $stmt->execute([$weight, $repetitions, $sets, $target_weight, $exercise_session_id]);

    // Redirection propre
    header("Location: exercises.php?session_id=$session_id");
    exit();
}

// 4. Récupération des exercices
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Exercices - Session #<?= $session_id ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        function supprimerExercise(exerciseId) {
            if (confirm("Supprimer cet exercice de la session ?")) {
                window.location.href = "exercises.php?session_id=<?= $session_id ?>&delete=" + exerciseId;
            }
        }
    </script>
</head>

<body class="bg-gradient-to-br from-primary-50 to-primary-100 min-h-screen p-4 md:p-8">
    <div class="max-w-4xl mx-auto">
        <div class="rounded-xl shadow p-6 mb-6 bg-white">
            <h1 class="text-3xl font-bold text-primary-800 mb-2 text-center">
                <i class="fas fa-dumbbell mr-2"></i> Exercices <?= $session_name ?>
            </h1>
          
            
        </div>

        <?php if (empty($exercises)): ?>
            <div class="rounded-xl p-8 text-center bg-white shadow">
                <i class="fas fa-dumbbell text-5xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-medium text-gray-700 mb-2">Aucun exercice dans cette session</h3>
                <a href="exercises_page.php" class="bg-blue-500 text-white px-4 py-2 rounded inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i> Ajouter des exercices
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($exercises as $ex): ?>
                    <div class="rounded-xl shadow bg-white overflow-hidden">
                        <div class="bg-blue-600 text-white p-4">
                            <h3 class="text-xl font-semibold"><i class="fas fa-dumbbell mr-2"></i><?= htmlspecialchars($ex['name']) ?></h3>
                        </div>
                        <form action="exercises.php?session_id=<?= $session_id ?>" method="POST" class="p-6 space-y-4">
                            <input type="hidden" name="exercise_session_id" value="<?= $ex['exercise_session_id'] ?>">

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1"><i class="fas fa-weight-hanging mr-1"></i> Poids (kg)</label>
                                    <input type="number" name="weight" value="<?= $ex['weight'] ?>" class="w-full border p-2 rounded" step="0.1" required />
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1"><i class="fas fa-bullseye mr-1"></i> Objectif (kg)</label>
                                    <input type="number" name="target_weight" value="<?= $ex['target_weight'] ?>" class="w-full border p-2 rounded" step="0.1" required />
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1"><i class="fas fa-redo mr-1"></i> Répétitions</label>
                                    <input type="number" name="repetitions" value="<?= $ex['repetitions'] ?>" class="w-full border p-2 rounded" required />
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700 mb-1"><i class="fas fa-layer-group mr-1"></i> Séries</label>
                                    <input type="number" name="sets" value="<?= $ex['sets'] ?>" class="w-full border p-2 rounded" required />
                                </div>
                            </div>

                            <div class="flex justify-between pt-4">
                                <button type="submit" name="update" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded flex items-center">
                                    <i class="fas fa-sync-alt mr-2"></i> Mettre à jour
                                </button>
                                <button type="button" onclick="supprimerExercise(<?= $ex['id'] ?>)" class="text-red-500 hover:text-red-700 flex items-center">
                                    <i class="fas fa-trash-alt mr-1"></i> Supprimer
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>