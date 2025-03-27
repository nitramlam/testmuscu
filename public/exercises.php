<?php
require 'db.php'; // Inclusion du fichier de connexion à la base de données

// Ajout d'un exercice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_exercise'])) {
    $name = $_POST['name'] ?? '';
    $weight = $_POST['weight'] ?? 0;
    $sets = $_POST['sets'] ?? 0;
    $reps = $_POST['reps'] ?? 0;
    $objective_weight = $_POST['objective_weight'] ?? 0;
    $session_id = $_POST['session_id'] ?? 1; // Valeur par défaut si non sélectionné

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO exercises (session_id, name, weight, sets, repetitions, target_weight) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$session_id, $name, $weight, $sets, $reps, $objective_weight]);
    }
}

// Suppression d'un exercice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_exercise'])) {
    $exercise_id = $_POST['exercise_id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM exercises WHERE id = ?");
    $stmt->execute([$exercise_id]);
}

// Modification d'un exercice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_exercise'])) {
    $exercise_id = $_POST['exercise_id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $weight = $_POST['weight'] ?? 0;
    $sets = $_POST['sets'] ?? 0;
    $reps = $_POST['reps'] ?? 0;
    $objective_weight = $_POST['objective_weight'] ?? 0;
    $session_id = $_POST['session_id'] ?? 1;

    $stmt = $pdo->prepare("UPDATE exercises SET session_id = ?, name = ?, weight = ?, sets = ?, repetitions = ?, target_weight = ? WHERE id = ?");
    $stmt->execute([$session_id, $name, $weight, $sets, $reps, $objective_weight, $exercise_id]);
}

// Récupération des exercices
$exercises = $pdo->query("SELECT * FROM exercises")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Exercices</title>
</head>
<body>
    <h2>Ajouter un exercice</h2>
    <form method="post">
        <input type="text" name="name" placeholder="Nom de l'exercice" required>
        <input type="number" name="weight" placeholder="Poids" required>
        <input type="number" name="sets" placeholder="Séries" required>
        <input type="number" name="reps" placeholder="Répétitions" required>
        <input type="number" name="objective_weight" placeholder="Poids Objectif" required>
        <label for="session_id">Session :</label>
        <select name="session_id" required>
            <option value="1">Bras</option>
            <option value="2">Jambes</option>
            <option value="3">Abdos</option>
            <option value="4">Jambes (Olivia)</option>
        </select>
        <button type="submit" name="add_exercise">Ajouter</button>
    </form>

    <h2>Liste des exercices</h2>
    <table border="1">
        <tr>
            <th>Nom</th>
            <th>Poids</th>
            <th>Séries</th>
            <th>Répétitions</th>
            <th>Poids Objectif</th>
            <th>Session</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($exercises as $exercise): ?>
            <tr>
                <td><?= htmlspecialchars($exercise['name'] ?? 'Nom inconnu') ?></td>
                <td><?= htmlspecialchars($exercise['weight'] ?? '0') ?></td>
                <td><?= htmlspecialchars($exercise['sets'] ?? '0') ?></td>
                <td><?= htmlspecialchars($exercise['repetitions'] ?? '0') ?></td>
                <td><?= htmlspecialchars($exercise['target_weight'] ?? '0') ?></td>
                <td><?= htmlspecialchars($exercise['session_id'] ?? '0') ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="exercise_id" value="<?= $exercise['id'] ?>">
                        <button type="submit" name="delete_exercise">Supprimer</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="exercise_id" value="<?= $exercise['id'] ?>">
                        <input type="text" name="name" value="<?= htmlspecialchars($exercise['name'] ?? '') ?>" required>
                        <input type="number" name="weight" value="<?= $exercise['weight'] ?>" required>
                        <input type="number" name="sets" value="<?= $exercise['sets'] ?>" required>
                        <input type="number" name="reps" value="<?= $exercise['repetitions'] ?>" required>
                        <input type="number" name="objective_weight" value="<?= $exercise['target_weight'] ?>" required>
                        <select name="session_id">
                            <option value="1" <?= $exercise['session_id'] == 1 ? 'selected' : '' ?>>Bras</option>
                            <option value="2" <?= $exercise['session_id'] == 2 ? 'selected' : '' ?>>Jambes</option>
                            <option value="3" <?= $exercise['session_id'] == 3 ? 'selected' : '' ?>>Abdos</option>
                            <option value="4" <?= $exercise['session_id'] == 4 ? 'selected' : '' ?>>Jambes (Olivia)</option>
                        </select>
                        <button type="submit" name="update_exercise">Modifier</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>