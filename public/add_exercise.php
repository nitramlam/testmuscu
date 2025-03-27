<?php
require 'db.php';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer l'ID de la session
    $session_id = $_GET['session_id'] ?? null;
    if (!$session_id) {
        echo "Erreur : Aucun ID de session fourni.";
        exit;
    }

    // Ajouter un exercice existant à la session
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_existing_exercise'])) {
        $exercise_id = $_POST['exercise_id'] ?? null;
        if ($exercise_id) {
            $sql = "INSERT INTO exercise_sessions (exercise_id, session_id) VALUES (:exercise_id, :session_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':exercise_id' => $exercise_id, ':session_id' => $session_id]);
        }
        header("Location: session_details.php?session_id=" . $session_id);
        exit;
    }

    // Supprimer un exercice de la session
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_exercise'])) {
        $exercise_id = $_POST['exercise_id'] ?? null;
        if ($exercise_id) {
            $sql = "DELETE FROM exercise_sessions WHERE exercise_id = :exercise_id AND session_id = :session_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':exercise_id' => $exercise_id, ':session_id' => $session_id]);
        }
        header("Location: session_details.php?session_id=" . $session_id);
        exit;
    }

    // Récupérer les informations de la session
    $sql = "SELECT * FROM sessions WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $session_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$session) {
        echo "Erreur : Session introuvable.";
        exit;
    }

    // Récupérer les exercices associés
    $sql = "SELECT e.id, e.exercise_name, e.weight, e.sets, e.reps, e.objective_weight
            FROM exercises e
            INNER JOIN exercise_sessions es ON e.id = es.exercise_id
            WHERE es.session_id = :session_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':session_id' => $session_id]);
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer tous les exercices
    $all_exercises = $pdo->query("SELECT id, exercise_name FROM exercises")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Session - <?= htmlspecialchars($session['session_name'] ?? 'Session') ?></title>
</head>
<body>
    <h1>Détails de la Session : <?= htmlspecialchars($session['session_name'] ?? 'Session') ?></h1>

    <h2>Exercices associés</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Poids</th>
                <th>Séries</th>
                <th>Répétitions</th>
                <th>Poids ObjectPPPPPPPPPif</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($exercises as $exercise): ?>
                <tr>
                    <td><?= htmlspecialchars($exercise['id']) ?></td>
                    <td><?= htmlspecialchars($exercise['exercise_name']) ?></td>
                    <td><?= htmlspecialchars($exercise['weight']) ?></td>
                    <td><?= htmlspecialchars($exercise['sets']) ?></td>
                    <td><?= htmlspecialchars($exercise['reps']) ?></td>
                    <td><?= htmlspecialchars($exercise['objective_weight']) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="exercise_id" value="<?= htmlspecialchars($exercise['id']) ?>">
                            <button type="submit" name="remove_exercise">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Ajouter un exercice existant</h2>
    <form method="POST">
        <label for="exercise_id">Exercice :</label>
        <select name="exercise_id" id="exercise_id">
            <?php foreach ($all_exercises as $exercise): ?>
                <option value="<?= htmlspecialchars($exercise['id']) ?>">
                    <?= htmlspecialchars($exercise['exercise_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="add_existing_exercise">Ajouter</button>
    </form>
</body>
</html>