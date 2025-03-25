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

    // Initialiser les variables pour éviter les erreurs si les requêtes échouent
    $sessions = [];
    $exercises = [];

    // Ajouter un exercice
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_exercise'])) {
        $session_ids = !empty($_POST['session_ids']) ? $_POST['session_ids'] : [];
        $exercise_name = $_POST['exercise_name'] ?? null;

        // Vérifier que le nom de l'exercice et les sessions sont fournis
        if (empty($exercise_name) || empty($session_ids)) {
            echo "Erreur : Le nom de l'exercice et au moins une session sont obligatoires.";
            exit;
        }

        // Définir les valeurs par défaut pour les champs numériques
        $weight = $_POST['weight'] !== '' ? $_POST['weight'] : 0;
        $sets = $_POST['sets'] !== '' ? $_POST['sets'] : 0;
        $reps = $_POST['reps'] !== '' ? $_POST['reps'] : 0;
        $objective_weight = $_POST['objective_weight'] !== '' ? $_POST['objective_weight'] : 0;

        // Insérer l'exercice
        $sql = "INSERT INTO exercises (exercise_name, weight, sets, reps, objective_weight) 
                VALUES (:exercise_name, :weight, :sets, :reps, :objective_weight)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':exercise_name' => $exercise_name,
            ':weight' => $weight,
            ':sets' => $sets,
            ':reps' => $reps,
            ':objective_weight' => $objective_weight
        ]);

        // Récupérer l'ID de l'exercice inséré
        $exercise_id = $pdo->lastInsertId();

        // Associer l'exercice aux sessions sélectionnées
        foreach ($session_ids as $session_id) {
            $sql = "INSERT INTO exercise_sessions (exercise_id, session_id) VALUES (:exercise_id, :session_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':exercise_id' => $exercise_id,
                ':session_id' => $session_id
            ]);
        }
    }

    // Modifier un exercice
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_exercise'])) {
        $exercise_id = $_POST['exercise_id'];
        $exercise_name = !empty($_POST['exercise_name']) ? $_POST['exercise_name'] : null;
        $weight = !empty($_POST['weight']) ? $_POST['weight'] : null;
        $sets = !empty($_POST['sets']) ? $_POST['sets'] : null;
        $reps = !empty($_POST['reps']) ? $_POST['reps'] : null;
        $objective_weight = !empty($_POST['objective_weight']) ? $_POST['objective_weight'] : null;

        $sql = "UPDATE exercises 
                SET exercise_name = :exercise_name, weight = :weight, 
                    sets = :sets, reps = :reps, objective_weight = :objective_weight 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $exercise_id,
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

        // Supprimer les associations dans la table exercise_sessions
        $sql = "DELETE FROM exercise_sessions WHERE exercise_id = :exercise_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':exercise_id' => $exercise_id]);

        // Supprimer l'exercice
        $sql = "DELETE FROM exercises WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $exercise_id]);
    }

    // Récupérer les exercices avec leurs sessions associées
    $sql = "
        SELECT e.id, e.exercise_name, e.weight, e.sets, e.reps, e.objective_weight, 
               GROUP_CONCAT(s.session_name SEPARATOR ', ') AS sessions
        FROM exercises e
        LEFT JOIN exercise_sessions es ON e.id = es.exercise_id
        LEFT JOIN sessions s ON es.session_id = s.id
        GROUP BY e.id
    ";
    $exercises = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

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
    <script>
        function validateForm() {
            const exerciseName = document.getElementById('exercise_name').value.trim();
            const sessionCheckboxes = document.querySelectorAll('input[name="session_ids[]"]:checked');

            if (!exerciseName) {
                alert("Le nom de l'exercice est obligatoire.");
                return false;
            }

            if (sessionCheckboxes.length === 0) {
                alert("Vous devez sélectionner au moins une session.");
                return false;
            }

            return true;
        }
    </script>
</head>

<body>
    <h1>Gestion des Exercices</h1>

    <!-- Formulaire pour ajouter un exercice -->
    <h2>Ajouter un exercice</h2>
    <form method="POST" onsubmit="return validateForm();">
        <label>Sessions :</label><br>
        <?php if (!empty($sessions)): ?>
            <?php foreach ($sessions as $session): ?>
                <input type="checkbox" name="session_ids[]" value="<?= htmlspecialchars($session['id']) ?>">
                <?= htmlspecialchars($session['session_name']) ?><br>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucune session disponible.</p>
        <?php endif; ?>

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
                <th>Sessions</th>
                <th>Nom de l'exercice</th>
                <th>Poids</th>
                <th>Séries</th>
                <th>Répétitions</th>
                <th>Poids objectif</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($exercises)): ?>
                <?php foreach ($exercises as $exercise): ?>
                    <tr>
                        <td><?= htmlspecialchars($exercise['id'] ?? '') ?></td>
                        <td><?= htmlspecialchars($exercise['sessions'] ?? 'Aucune session') ?></td>
                        <td><?= htmlspecialchars($exercise['exercise_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($exercise['weight'] ?? '') ?></td>
                        <td><?= htmlspecialchars($exercise['sets'] ?? '') ?></td>
                        <td><?= htmlspecialchars($exercise['reps'] ?? '') ?></td>
                        <td><?= htmlspecialchars($exercise['objective_weight'] ?? '') ?></td>
                        <td>
                            <!-- Formulaire pour modifier un exercice -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="exercise_id" value="<?= htmlspecialchars($exercise['id']) ?>">
                                <input type="text" name="exercise_name" placeholder="Nom"
                                    value="<?= htmlspecialchars($exercise['exercise_name'] ?? '') ?>">
                                <input type="number" step="0.01" name="weight" placeholder="Poids"
                                    value="<?= htmlspecialchars($exercise['weight'] ?? '') ?>">
                                <input type="number" name="sets" placeholder="Séries"
                                    value="<?= htmlspecialchars($exercise['sets'] ?? '') ?>">
                                <input type="number" name="reps" placeholder="Répétitions"
                                    value="<?= htmlspecialchars($exercise['reps'] ?? '') ?>">
                                <input type="number" step="0.01" name="objective_weight" placeholder="Poids objectif"
                                    value="<?= htmlspecialchars($exercise['objective_weight'] ?? '') ?>">
                                <button type="submit" name="edit_exercise">Modifier</button>
                            </form>

                            <!-- Formulaire pour supprimer un exercice -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="exercise_id" value="<?= htmlspecialchars($exercise['id']) ?>">
                                <button type="submit" name="delete_exercise"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet exercice ?');">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">Aucun exercice trouvé.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>