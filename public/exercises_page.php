<?php
include 'header.php'; // Vérifie le token et récupère l'utilisateur connecté

// Stocker les informations de l'utilisateur dans des variables globales
$user_id = $user['id'];
$user_name = $user['name'];

// Régénérer le token et mettre à jour son expiration
$new_token = bin2hex(random_bytes(16));
$new_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
$stmt = $pdo->prepare("UPDATE users SET token = ?, token_expiry = ? WHERE id = ?");
$stmt->execute([$new_token, $new_expiry, $user_id]);

// Mettre à jour le token dans la session
$_SESSION['token'] = $new_token;

// Récupérer le session_id depuis l'URL si disponible
$session_id = $_GET['session_id'] ?? null;

// Ajouter les informations de l'utilisateur et de la session dans l'URL pour les liens
$query_params = http_build_query(['user_id' => $user_id] + ($session_id ? ['session_id' => $session_id] : []));
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercices</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-900 font-sans">
    <!-- Inclure le header -->

    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-6">Liste des Exercices</h1>
        <table class="table-auto w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-blue-600 text-white">
                    <th class="border border-gray-300 px-4 py-2">Nom de l'exercice</th>
                    <th class="border border-gray-300 px-4 py-2">Utilisateur</th>
                    <th class="border border-gray-300 px-4 py-2">Session</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Récupération des exercices avec les utilisateurs associés
                $query = $pdo->query("
                    SELECT e.id AS exercise_id, e.name AS exercise_name, u.name AS user_name, s.name AS session_name
                    FROM exercises e
                    LEFT JOIN exercises_sessions es ON e.id = es.exercise_id
                    LEFT JOIN sessions s ON es.session_id = s.id
                    LEFT JOIN users u ON s.user_id = u.id
                ");
                $exercises = $query->fetchAll(PDO::FETCH_ASSOC);

                foreach ($exercises as $exercise): ?>
                    <tr class="bg-white hover:bg-gray-100">
                        <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($exercise['exercise_name']) ?>
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            <?= htmlspecialchars($exercise['user_name'] ?? 'Aucun') ?></td>
                        <td class="border border-gray-300 px-4 py-2">
                            <?= htmlspecialchars($exercise['session_name'] ?? 'Aucune') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2 class="text-2xl font-bold mt-8 mb-4">Ajouter un exercice à plusieurs sessions</h2>
        <form action="add_exercise_to_sessions.php" method="POST" class="bg-white p-6 rounded shadow-md">
            <div class="mb-4">
                <label for="exercise_name" class="block text-gray-700 font-medium mb-2">Nom de l'exercice :</label>
                <input type="text" id="exercise_name" name="exercise_name" required
                    class="w-full border border-gray-300 rounded px-4 py-2">
            </div>
            <div class="mb-4">
                <label for="sessions" class="block text-gray-700 font-medium mb-2">Sélectionnez les sessions :</label>
                <select id="sessions" name="sessions[]" multiple required
                    class="w-full border border-gray-300 rounded px-4 py-2">
                    <?php
                    // Récupération des sessions pour le formulaire
                    $sessionsQuery = $pdo->query("SELECT s.id AS session_id, s.name AS session_name, u.name AS user_name FROM sessions s LEFT JOIN users u ON s.user_id = u.id");
                    $sessions = $sessionsQuery->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($sessions as $session): ?>
                        <option value="<?= $session['session_id'] ?>">
                            <?= htmlspecialchars($session['session_name'] . ' (Utilisateur : ' . $session['user_name'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-300">
                Ajouter
            </button>
        </form>
    </div>
</body>

</html>