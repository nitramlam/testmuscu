<?php
require 'db.php'; // Inclusion du fichier de connexion à la base de données

// Ajout d'un exercice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_exercise'])) {
    $name = $_POST['name'] ?? '';
    $weight = $_POST['weight'] ?? 0;
    $sets = $_POST['sets'] ?? 0;
    $reps = $_POST['reps'] ?? 0;
    $objective_weight = $_POST['objective_weight'] ?? 0;

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO exercises (name, weight, sets, repetitions, target_weight) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $weight, $sets, $reps, $objective_weight]);
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

    $stmt = $pdo->prepare("UPDATE exercises SET name = ?, weight = ?, sets = ?, repetitions = ?, target_weight = ? WHERE id = ?");
    $stmt->execute([$name, $weight, $sets, $reps, $objective_weight, $exercise_id]);
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
        <input type="number" name="weight" placeholder="Poids">
        <input type="number" name="sets" placeholder="Séries">
        <input type="number" name="reps" placeholder="Répétitions">
        <input type="number" name="objective_weight" placeholder="Poids Objectif">
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
            <th>Actions</th>
        </tr>
        <?php foreach ($exercises as $exercise): ?>
            <tr>
                <td><?= htmlspecialchars($exercise['name'] ?? 'Nom inconnu') ?></td>
                <td><?= htmlspecialchars($exercise['weight'] ?? '0') ?></td>
                <td><?= htmlspecialchars($exercise['sets'] ?? '0') ?></td>
                <td><?= htmlspecialchars($exercise['repetitions'] ?? '0') ?></td>
                <td><?= htmlspecialchars($exercise['target_weight'] ?? '0') ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="exercise_id" value="<?= $exercise['id'] ?>">
                        <button type="submit" name="delete_exercise">Supprimer</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="exercise_id" value="<?= $exercise['id'] ?>">
                        <input type="text" name="name" value="<?= htmlspecialchars($exercise['name'] ?? '') ?>" required>
                        <input type="number" name="weight" value="<?= $exercise['weight'] ?>">
                        <input type="number" name="sets" value="<?= $exercise['sets'] ?>">
                        <input type="number" name="reps" value="<?= $exercise['repetitions'] ?>">
                        <input type="number" name="objective_weight" value="<?= $exercise['target_weight'] ?>">
                        <button type="submit" name="update_exercise">Modifier</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>