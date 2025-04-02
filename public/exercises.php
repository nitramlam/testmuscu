<?php
include 'header.php'; // Vérifie le token et récupère l'utilisateur connecté

// Récupération de l'ID de la session en cours depuis l'URL
$session_id = $_GET['session_id'] ?? null;

if (!$session_id) {
    die("Erreur : Aucun ID de session fourni. Vérifiez l'URL.");
}

// Récupérer le nom de la session
$sql = "SELECT name FROM sessions WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$session_id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    die("Erreur : Session introuvable.");
}

$session_name = $session['name'];

// Récupérer la liste des exercices existants
$sql = "SELECT id, name FROM exercises";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$available_exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ajouter un exercice à la session
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exercise_name'])) {
    $exercise_id = (int) $_POST['exercise_name']; // Utiliser l'ID de l'exercice sélectionné
    $weight = $_POST['weight'] ?? 0;
    $sets = $_POST['sets'] ?? 0;
    $reps = $_POST['reps'] ?? 0;
    $objective_weight = $_POST['objective_weight'] ?? 0;

    // Ajouter l'exercice à la session avec les données spécifiques
    $sql = "INSERT INTO exercises_sessions (exercise_id, session_id, weight, sets, repetitions, target_weight) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$exercise_id, $session_id, $weight, $sets, $reps, $objective_weight]);

    $message = "Exercice ajouté avec succès.";
}

// Supprimer un exercice
if (isset($_POST['delete_exercise_id'])) {
    $delete_exercise_id = (int) $_POST['delete_exercise_id'];

    // Supprimer d'abord de la table de liaison
    $sql = "DELETE FROM exercises_sessions WHERE exercise_id = ? AND session_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$delete_exercise_id, $session_id]);

    // Puis supprimer l'exercice lui-même
    $sql = "DELETE FROM exercises WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$delete_exercise_id]);

    $message = "Exercice supprimé avec succès.";
}

// Traitement de la mise à jour des exercices
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_exercise_id'])) {
    $update_exercise_id = (int) $_POST['update_exercise_id'];
    $weight = $_POST['weight'] ?? 0;
    $sets = $_POST['sets'] ?? 0;
    $reps = $_POST['reps'] ?? 0;
    $objective_weight = $_POST['objective_weight'] ?? 0;

    // Mettre à jour les données spécifiques à la session
    $sql = "UPDATE exercises_sessions 
            SET weight = ?, sets = ?, repetitions = ?, target_weight = ? 
            WHERE exercise_id = ? AND session_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$weight, $sets, $reps, $objective_weight, $update_exercise_id, $session_id]);

    $message = "Exercice mis à jour avec succès.";
}

// Rafraîchir la page après une action
if (!empty($message)) {
    echo '<script>window.location.href="exercises.php?session_id=' . htmlspecialchars($session_id) . '";</script>';
    exit;
}

// Récupérer les exercices pour la session en cours
$sql = "SELECT es.id, e.name, es.weight, es.sets, es.repetitions, es.target_weight 
        FROM exercises_sessions es
        JOIN exercises e ON es.exercise_id = e.id
        WHERE es.session_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$session_id]);
$exercises_in_session = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Gestion des Exercices</title>
    <script>
        // Validation pour limiter les champs numériques à 3 chiffres
        function validateNumericInput(event) {
            const input = event.target;
            if (input.value.length > 3) {
                input.value = input.value.slice(0, 3);
            }
        }

        // Permet de transformer un élément en champ éditable
        function makeEditable(element, formFieldName, exerciseId) {
            const currentValue = element.textContent.trim();
            const input = document.createElement('input');
            input.type = 'number';
            input.value = currentValue || 0;
            input.maxLength = 3;
            input.className = "w-16 p-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500";
            input.oninput = validateNumericInput;

            input.onblur = function () {
                saveChanges(input, formFieldName, exerciseId);
            };

            input.onkeydown = function (event) {
                if (event.key === 'Enter') {
                    saveChanges(input, formFieldName, exerciseId);
                }
            };

            element.textContent = '';
            element.appendChild(input);
            input.focus();
        }

        // Sauvegarde les modifications
        function saveChanges(input, formFieldName, exerciseId) {
            const newValue = input.value || 0; // Remplace les champs vides par zéro
            const formData = new FormData();
            formData.append('update_exercise_id', exerciseId);
            formData.append(formFieldName, newValue);

            fetch('exercises.php?session_id=<?= htmlspecialchars($session_id); ?>', {
                method: 'POST',
                body: formData
            }).then(() => {
                input.parentElement.textContent = newValue; // Met à jour l'affichage
            }).catch(() => {
                console.error('Erreur lors de la sauvegarde.');
            });
        }
    </script>
    <!-- CDN de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-900 font-sans">

    <div class="max-w-4xl mx-auto p-8">

        <h2 class="text-2xl font-semibold mb-4">Ajouter un exercice à la session <?= htmlspecialchars($session_name); ?>
        </h2>

        <?php if (!empty($message)): ?>
            <p class="text-green-500"><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <!-- Formulaire pour ajouter un exercice existant -->
        <form method="POST" action="exercises.php?session_id=<?= htmlspecialchars($session_id); ?>"
            class="space-y-4 mb-6">
            <select name="exercise_name" required
                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Choisir un exercice existant</option>
                <?php foreach ($available_exercises as $exercise): ?>
                    <option value="<?= htmlspecialchars($exercise['id']); ?>"><?= htmlspecialchars($exercise['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="number" name="weight" placeholder="Poids (kg)" min="0" max="999"
                oninput="validateNumericInput(event)"
                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <input type="number" name="sets" placeholder="Nombre de séries" min="0" max="999"
                oninput="validateNumericInput(event)"
                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <input type="number" name="reps" placeholder="Nombre de répétitions" min="0" max="999"
                oninput="validateNumericInput(event)"
                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <input type="number" name="objective_weight" placeholder="Poids objectif (kg)" min="0" max="999"
                oninput="validateNumericInput(event)"
                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

            <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                Ajouter
            </button>
        </form>

        <h2 class="text-2xl font-semibold mt-8 mb-4">Liste des exercices pour la session en cours</h2>

        <?php if (empty($exercises_in_session)): ?>
            <p class="text-gray-500">Aucun exercice n'a été ajouté à cette session pour le moment.</p>
        <?php else: ?>
            <ul class="space-y-4">
                <?php foreach ($exercises_in_session as $exercise): ?>
                    <li class="p-4 border border-gray-300 rounded-md shadow-sm">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-semibold"><?= htmlspecialchars($exercise['name'] ?? 'Non spécifié'); ?></p>
                                <table>
                                    <tr>
                                        <td class="font-semibold">Poids :</td>
                                        <td>
                                            <span class="editable cursor-pointer"
                                                onclick="makeEditable(this, 'weight', <?= $exercise['id']; ?>)">
                                                <?= htmlspecialchars($exercise['weight']); ?>
                                            </span> kg
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold">Séries :</td>
                                        <td>
                                            <span class="editable cursor-pointer"
                                                onclick="makeEditable(this, 'sets', <?= $exercise['id']; ?>)">
                                                <?= htmlspecialchars($exercise['sets']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold">Répétitions :</td>
                                        <td>
                                            <span class="editable cursor-pointer"
                                                onclick="makeEditable(this, 'reps', <?= $exercise['id']; ?>)">
                                                <?= htmlspecialchars($exercise['repetitions']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold">Poids Objectif :</td>
                                        <td>
                                            <span class="editable cursor-pointer"
                                                onclick="makeEditable(this, 'objective_weight', <?= $exercise['id']; ?>)">
                                                <?= htmlspecialchars($exercise['target_weight']); ?>
                                            </span> kg
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <form method="POST" action="exercises.php?session_id=<?= htmlspecialchars($session_id); ?>"
                                class="inline-block">
                                <input type="hidden" name="delete_exercise_id" value="<?= $exercise['id']; ?>">
                                <button type="submit"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet exercice ?');"
                                    class="bg-red-600 text-white py-1 px-4 rounded-md hover:bg-red-700 transition sm:py-1 sm:px-3 sm:text-sm">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <a href="sessions.php" class="mt-6 text-blue-600 hover:text-blue-700 inline-block">Retour aux sessions</a>

    </div>

</body>

</html>