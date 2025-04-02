<?php
include 'header.php'; // Vérifie le token et récupère l'utilisateur connecté

// Message de confirmation
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier si un nouvel exercice est créé
    if (isset($_POST['new_exercise_name']) && !empty($_POST['new_exercise_name'])) {
        // Ajouter un nouvel exercice
        $new_exercise_name = trim($_POST['new_exercise_name']);
        // Insérer le nouvel exercice dans la base de données
        $sql = "INSERT INTO exercises (name) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_exercise_name]);
        $exercise_id = $pdo->lastInsertId(); // Récupérer l'ID du nouvel exercice ajouté
        $message = "Exercice créé avec succès et ajouté aux sessions.";
    } else {
        $message = "Veuillez créer un exercice.";
    }

    // Ajouter l'exercice à plusieurs sessions sélectionnées
    if (isset($_POST['sessions'])) {
        $sessions = $_POST['sessions'];  // Tableau de sessions sélectionnées
        if (!empty($sessions)) {
            foreach ($sessions as $session_id) {
                // Insérer l'exercice dans chaque session sélectionnée
                $sql = "INSERT INTO exercises_sessions (exercise_id, session_id) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$exercise_id, $session_id]);
            }
            $message = "Exercice ajouté à " . count($sessions) . " sessions.";
        } else {
            $message = "Veuillez sélectionner au moins une session.";
        }
    }
}

// Récupérer la liste des exercices
$sql = "SELECT * FROM exercises";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des sessions avec les utilisateurs associés
$sql = "SELECT s.*, u.name AS user_name FROM sessions s LEFT JOIN users u ON s.user_id = u.id";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les exercices par utilisateur
$sql = "
    SELECT u.name AS user_name, e.name AS exercise_name
    FROM exercises_sessions es
    JOIN exercises e ON es.exercise_id = e.id
    JOIN sessions s ON es.session_id = s.id
    JOIN users u ON s.user_id = u.id
    ORDER BY u.name, e.name";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$exercises_by_user = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Exercice aux Sessions</title>
    <!-- CDN de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-900 font-sans">

    <div class="max-w-4xl mx-auto mt-12 p-8 bg-white shadow-lg rounded-lg">
        <h2 class="text-3xl font-semibold text-center text-gray-800 mb-6">Ajouter un Exercice à Plusieurs Sessions</h2>

        <?php if (!empty($message)): ?>
            <p class="text-center text-green-500 mb-6"><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <!-- Formulaire pour ajouter un exercice à plusieurs sessions -->
        <form method="POST" action="" class="mb-6">
            <div class="flex flex-col items-center">
                <!-- Champ pour créer un nouvel exercice -->
                <input type="text" name="new_exercise_name" placeholder="Nom du nouvel exercice" maxlength="50" class="p-3 border border-gray-300 rounded-lg w-72 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500">

                <!-- Liste des sessions avec cases à cocher et noms des utilisateurs -->
                <div class="space-y-2 mb-4">
                    <?php foreach ($sessions as $session): ?>
                        <label class="flex items-center">
                            <input type="checkbox" name="sessions[]" value="<?= $session['id']; ?>" class="mr-2">
                            <?= htmlspecialchars($session['name']); ?> - <span class="text-gray-500"><?= htmlspecialchars($session['user_name']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <button type="submit"
                    class="bg-blue-500 text-white p-3 rounded-lg w-72 hover:bg-blue-600 transition duration-300">Ajouter l'Exercice</button>
            </div>
        </form>

        <!-- Liste des exercices triée par utilisateur -->
        <div class="mt-8">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Exercices par Utilisateur</h3>

            <?php 
            $current_user = '';
            foreach ($exercises_by_user as $exercise): 
                // Afficher un nouveau groupe d'exercices pour chaque utilisateur
                if ($current_user !== $exercise['user_name']):
                    if ($current_user !== '') {
                        echo "</ul>"; // Fermer la liste de l'utilisateur précédent
                    }
                    $current_user = $exercise['user_name'];
                    echo "<h4 class='text-xl font-semibold text-gray-800 mt-4'>" . htmlspecialchars($current_user) . "</h4>";
                    echo "<ul class='list-disc ml-6'>";
                endif;
            ?>
                <li><?= htmlspecialchars($exercise['exercise_name']); ?></li>
            <?php endforeach; ?>
            </ul> <!-- Fermer la dernière liste -->
        </div>

    </div>

</body>

</html>