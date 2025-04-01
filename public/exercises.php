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
        $sql = "INSERT INTO exercises (session_id, name, weight, sets, repetitions, target_weight) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$session_id, $exercise_name, $weight, $sets, $reps, $objective_weight]);
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

// Traitement de la mise à jour des exercices
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_exercise_id'])) {
    $update_exercise_id = (int) $_POST['update_exercise_id'];
    $weight = $_POST['weight'] ?? 0;
    $sets = $_POST['sets'] ?? 0;
    $reps = $_POST['reps'] ?? 0;
    $objective_weight = $_POST['objective_weight'] ?? 0;

    $sql = "UPDATE exercises SET weight = ?, sets = ?, repetitions = ?, target_weight = ? WHERE id = ? AND session_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$weight, $sets, $reps, $objective_weight, $update_exercise_id, $session_id]);
    $message = "Exercice mis à jour avec succès.";
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
    <script>
        // Validation pour limiter les champs numériques à 3 chiffres
        function validateNumericInput(event) {
            const input = event.target;
            if (input.value.length > 3) {
                input.value = input.value.slice(0, 3);
            }
        }

        // Permet de transformer un élément en champ éditable
        function makeEditable(element, tickElement) {
            const currentValue = element.textContent.trim();
            const input = document.createElement('input');
            input.type = 'number';
            input.value = currentValue;
            input.maxLength = 3;
            input.className = "w-16 p-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500";
            input.oninput = validateNumericInput;

            input.onblur = function () {
                element.textContent = input.value || currentValue;
                element.dataset.value = input.value || currentValue;
                element.nextElementSibling.value = input.value || currentValue; // Met à jour la valeur cachée
                tickElement.style.display = "none"; // Cache le tick
                element.onclick = () => makeEditable(element, tickElement);
            };

            element.textContent = '';
            element.appendChild(input);
            input.focus();
            tickElement.style.display = "inline"; // Affiche le tick
        }
    </script>
    <!-- CDN de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-900 font-sans">

    <div class="max-w-4xl mx-auto p-8">

        <h2 class="text-2xl font-semibold mb-4">Ajouter un exercice à la session en cours</h2>

        <?php if (!empty($message)): ?>
            <p class="text-green-500"><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <!-- Formulaire pour ajouter un exercice -->
        <form method="POST" action="exercises.php?session_id=<?= htmlspecialchars($session_id); ?>" class="space-y-4">
            <input type="text" name="exercise_name" placeholder="Nom de l'exercice" maxlength="15" required
                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="number" name="weight" placeholder="Poids" required oninput="validateNumericInput(event)"
                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="number" name="sets" placeholder="Séries" required oninput="validateNumericInput(event)"
                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="number" name="reps" placeholder="Répétitions" required oninput="validateNumericInput(event)"
                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="number" name="objective_weight" placeholder="Poids Objectif" required
                oninput="validateNumericInput(event)"
                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                Ajouter
            </button>
        </form>

        <h2 class="text-2xl font-semibold mt-8 mb-4">Liste des exercices pour la session en cours</h2>

        <ul class="space-y-4">
            <?php foreach ($exercises as $exercise): ?>
                <li class="p-4 border border-gray-300 rounded-md shadow-sm">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-semibold"><?= htmlspecialchars($exercise['name']); ?></p>
                            <form method="POST" action="exercises.php?session_id=<?= htmlspecialchars($session_id); ?>"
                                class="mt-2 space-y-2">
                                <input type="hidden" name="update_exercise_id" value="<?= $exercise['id']; ?>">
                                <table>
                                    <tr>
                                        <td class="font-semibold">Poids :</td>
                                        <td>
                                            <span class="editable"
                                                data-value="<?= htmlspecialchars($exercise['weight']); ?>"
                                                onclick="makeEditable(this, this.nextElementSibling.nextElementSibling)">
                                                <?= htmlspecialchars($exercise['weight']); ?>
                                            </span> kg
                                            <input type="hidden" name="weight"
                                                value="<?= htmlspecialchars($exercise['weight']); ?>">
                                            <span class="text-green-600 cursor-pointer ml-2 hidden"
                                                onclick="this.closest('form').submit()">✔</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold">Séries :</td>
                                        <td>
                                            <span class="editable" data-value="<?= htmlspecialchars($exercise['sets']); ?>"
                                                onclick="makeEditable(this, this.nextElementSibling.nextElementSibling)">
                                                <?= htmlspecialchars($exercise['sets']); ?>
                                            </span>
                                            <input type="hidden" name="sets"
                                                value="<?= htmlspecialchars($exercise['sets']); ?>">
                                            <span class="text-green-600 cursor-pointer ml-2 hidden"
                                                onclick="this.closest('form').submit()">✔</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold">Répétitions :</td>
                                        <td>
                                            <span class="editable"
                                                data-value="<?= htmlspecialchars($exercise['repetitions']); ?>"
                                                onclick="makeEditable(this, this.nextElementSibling.nextElementSibling)">
                                                <?= htmlspecialchars($exercise['repetitions']); ?>
                                            </span>
                                            <input type="hidden" name="reps"
                                                value="<?= htmlspecialchars($exercise['repetitions']); ?>">
                                            <span class="text-green-600 cursor-pointer ml-2 hidden"
                                                onclick="this.closest('form').submit()">✔</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold">Poids Objectif :</td>
                                        <td>
                                            <span class="editable"
                                                data-value="<?= htmlspecialchars($exercise['target_weight']); ?>"
                                                onclick="makeEditable(this, this.nextElementSibling.nextElementSibling)">
                                                <?= htmlspecialchars($exercise['target_weight']); ?>
                                            </span> kg
                                            <input type="hidden" name="objective_weight"
                                                value="<?= htmlspecialchars($exercise['target_weight']); ?>">
                                            <span class="text-green-600 cursor-pointer ml-2 hidden"
                                                onclick="this.closest('form').submit()">✔</span>
                                        </td>
                                    </tr>
                                </table>
                            </form>
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

        <a href="sessions.php"
            class="mt-8 inline-block text-blue-600 hover:text-blue-800 font-semibold transition duration-300">Retour aux
            sessions</a>

    </div>

</body>

</html>