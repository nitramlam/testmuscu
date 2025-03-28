<?php
require 'db.php';
include 'header.php'; // Vérifie le token et récupère l'utilisateur connecté

// Récupération de l'ID de la session en cours depuis l'URL
$session_id = $_GET['session_id'] ?? null;

if (!$session_id) {
    die("Erreur : Aucun ID de session fourni. Vérifiez l'URL.");
}

// Ajouter un exercice
$message = '';
$refresh = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exercise_name'])) {
    $exercise_name = trim($_POST['exercise_name']);
    $weight = $_POST['weight'] ?? 0;
    $sets = $_POST['sets'] ?? 0;
    $reps = $_POST['reps'] ?? 0;
    $objective_weight = $_POST['objective_weight'] ?? 0;

    if (!empty($exercise_name)) {
        $sql = "INSERT INTO exercises (session_id, user_id, name, weight, sets, repetitions, target_weight) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$session_id, $user_id, $exercise_name, $weight, $sets, $reps, $objective_weight]);
        $message = "Exercice ajouté avec succès.";
        $refresh = true;
    } else {
        $message = "Le nom de l'exercice ne peut pas être vide.";
    }
}

// Supprimer un exercice
if (isset($_POST['delete_exercise_id'])) {
    $delete_exercise_id = (int) $_POST['delete_exercise_id'];
    $sql = "DELETE FROM exercises WHERE id = ? AND session_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$delete_exercise_id, $session_id]);
    $message = "Exercice supprimé avec succès.";
    $refresh = true;
}

// Rafraîchir la page après une action
if ($refresh) {
    echo '<script>window.location.href="exercises.php?session_id=' . htmlspecialchars($session_id) . '";</script>';
    exit;
}

// Récupérer les exercices pour la session en cours
$sql = "SELECT * FROM exercises WHERE session_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$session_id]);
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Gestion des Exercices</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/exercises.css">
    <script src="js/global.js" defer></script>
    <script src="js/exercises.js" defer></script>
    <script>
        // Validation pour limiter les champs numériques à 3 chiffres
        function validateNumericInput(event) {
            const input = event.target;
            if (input.value.length > 3) {
                input.value = input.value.slice(0, 3);
            }
        }
    </script>
</head>

<body>
    <h2>Ajouter un exercice à la session en cours</h2>

    <?php if (!empty($message)): ?>
        <p><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Formulaire pour ajouter un exercice -->
    <form method="POST" action="exercises.php?session_id=<?= htmlspecialchars($session_id); ?>">
        <input type="text" name="exercise_name" placeholder="Nom de l'exercice" maxlength="15" required>
        <input type="number" name="weight" placeholder="Poids" required oninput="validateNumericInput(event)">
        <input type="number" name="sets" placeholder="Séries" required oninput="validateNumericInput(event)">
        <input type="number" name="reps" placeholder="Répétitions" required oninput="validateNumericInput(event)">
        <input type="number" name="objective_weight" placeholder="Poids Objectif" required
            oninput="validateNumericInput(event)">
        <button type="submit">Ajouter</button>
    </form>

    <h2>Liste des exercices pour la session en cours</h2>
    <ul>
        <?php foreach ($exercises as $exercise): ?>
            <li>
                <?= htmlspecialchars($exercise['name']); ?> (Poids : <?= htmlspecialchars($exercise['weight']); ?>, Séries :
                <?= htmlspecialchars($exercise['sets']); ?>, Répétitions :
                <?= htmlspecialchars($exercise['repetitions']); ?>, Poids Objectif :
                <?= htmlspecialchars($exercise['target_weight']); ?>)
                <!-- Formulaire pour supprimer un exercice -->
                <form method="POST" action="exercises.php?session_id=<?= htmlspecialchars($session_id); ?>"
                    style="display:inline;">
                    <input type="hidden" name="delete_exercise_id" value="<?= $exercise['id']; ?>">
                    <button type="submit"
                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet exercice ?');">Supprimer</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <a href="sessions.php">Retour aux sessions</a>
</body>

</html>