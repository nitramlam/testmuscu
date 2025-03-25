<?php
// Informations de connexion à la base de données
$host = 'mysql';
$dbname = 'musculation_db';
$username = 'root';
$password = 'muscu1234';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ajouter un exercice
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_exercise'])) {
        $session_id = !empty($_POST['session_id']) ? $_POST['session_id'] : null;
        $exercise_name = !empty($_POST['exercise_name']) ? $_POST['exercise_name'] : null;
        $weight = !empty($_POST['weight']) ? $_POST['weight'] : null;
        $sets = !empty($_POST['sets']) ? $_POST['sets'] : null;
        $reps = !empty($_POST['reps']) ? $_POST['reps'] : null;
        $objective_weight = !empty($_POST['objective_weight']) ? $_POST['objective_weight'] : null;

        $sql = "INSERT INTO exercises (session_id, exercise_name, weight, sets, reps, objective_weight) 
                VALUES (:session_id, :exercise_name, :weight, :sets, :reps, :objective_weight)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':session_id' => $session_id,
            ':exercise_name' => $exercise_name,
            ':weight' => $weight,
            ':sets' => $sets,
            ':reps' => $reps,
            ':objective_weight' => $objective_weight
        ]);
    }

    // Supprimer un exercice
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_exercise'])) {
        $exercise_id = $_POST['exercise_id'];

        $sql = "DELETE FROM exercises WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $exercise_id]);
    }

    // Récupérer les exercices
    $exercises = $pdo->query("SELECT * FROM exercises")->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les sessions pour le menu déroulant
    $sessions = $pdo->query("SELECT * FROM sessions")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Exercices</title>
</head>
<body>
    <h1>Gestion des Exercices</h1>

    <!-- Formulaire pour ajouter un exercice -->
    <h2>Ajouter un exercice</h2>
    <form method="POST">
        <label for="session_id">Session :</label>
        <select name="session_id" id="session_id">
            <option value="">-- Sélectionnez une session (facultatif) --</option>
            <?php foreach ($sessions as $session): ?>
                <option value="<?= htmlspecialchars($session['id']) ?>"><?= htmlspecialchars($session['session_name']) ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="exercise_name">Nom de l'exercice :</label>
        <input type="text" name="exercise_name" id="exercise_name"><br>

        <label for="weight">Poids :</label>
        <input type="number" step="0.01" name="weight" id="weight"><br>

        <label for="sets">Séries :</label>
        <input type="number" name="sets" id="sets"><br>

        <label for="reps">Répétitions :</label>
        <input type="number" name="reps" id="reps"><br>

        <label for="objective_weight">Poids objectif :</label>
        <input type="number" step="0.01" name="objective_weight" id="objective_weight"><br>

        <button type="submit" name="add_exercise">Ajouter</button>
    </form>

    <!-- Liste des exercices existants -->
    <h2>Liste des exercices</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Session</th>
                <th>Nom de l'exercice</th>
                <th>Poids</th>
                <th>Séries</th>
                <th>Répétitions</th>
                <th>Poids objectif</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($exercises as $exercise): ?>
                <tr>
                    <td><?= htmlspecialchars($exercise['id']) ?></td>
                    <td><?= htmlspecialchars($exercise['session_id']) ?></td>
                    <td><?= htmlspecialchars($exercise['exercise_name']) ?></td>
                    <td><?= htmlspecialchars($exercise['weight']) ?></td>
                    <td><?= htmlspecialchars($exercise['sets']) ?></td>
                    <td><?= htmlspecialchars($exercise['reps']) ?></td>
                    <td><?= htmlspecialchars($exercise['objective_weight']) ?></td>
                    <td>
                        <!-- Formulaire pour supprimer un exercice -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="exercise_id" value="<?= htmlspecialchars($exercise['id']) ?>">
                            <button type="submit" name="delete_exercise">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>