<?php
ob_start(); // 👈 Ça permet de bufferiser la sortie HTML

include 'header.php'; // session_start() est ici, nickel

// Message de confirmation
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_exercise_name']) && !empty($_POST['new_exercise_name'])) {
        $new_exercise_name = trim($_POST['new_exercise_name']);
        $sql = "INSERT INTO exercises (name) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_exercise_name]);
        $exercise_id = $pdo->lastInsertId();
        $_SESSION['message'] = "Exercice créé avec succès et ajouté aux sessions.";
    } else {
        $_SESSION['message'] = "Veuillez créer un exercice.";
    }

    if (isset($_POST['sessions']) && !empty($_POST['sessions'])) {
        foreach ($_POST['sessions'] as $session_id) {
            $sql = "INSERT INTO exercises_sessions (exercise_id, session_id) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$exercise_id, $session_id]);
        }
        $_SESSION['message'] = "Exercice ajouté à " . count($_POST['sessions']) . " sessions.";
    } else if (isset($_POST['new_exercise_name']) && !empty($_POST['new_exercise_name'])) {
        $_SESSION['message'] = "Exercice créé mais aucune session sélectionnée.";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Récupérations des données (comme avant)
$sql = "SELECT * FROM exercises";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT s.*, u.name AS user_name FROM sessions s LEFT JOIN users u ON s.user_id = u.id";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "
    SELECT u.name AS user_name, e.name AS exercise_name, s.name AS session_name
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
    <title>Ajouter Exercice aux Sessions</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900 font-sans">

<div class="max-w-4xl mx-auto mt-12 p-8 bg-white shadow-lg rounded-lg">
    <h2 class="text-3xl font-semibold text-center text-gray-800 mb-6">Ajouter un Exercice à Plusieurs Sessions</h2>

    <?php if (!empty($message)): ?>
        <p class="text-center text-green-500 mb-6"><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" action="" class="mb-6">
        <div class="flex flex-col items-center">
            <input type="text" name="new_exercise_name" placeholder="Nom du nouvel exercice" maxlength="15"
                   class="p-3 border border-gray-300 rounded-lg w-72 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500">

            <div class="w-full max-w-md space-y-2 mb-4">
                <?php foreach ($sessions as $session): ?>
                    <label class="flex items-center gap-2 text-gray-700">
                        <input type="checkbox" name="sessions[]" value="<?= $session['id']; ?>" class="accent-blue-500">
                        <span><?= htmlspecialchars($session['name']); ?> - 
                            <span class="text-gray-500"><?= htmlspecialchars($session['user_name']); ?></span>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit"
                    class="bg-blue-500 text-white p-3 rounded-lg w-72 hover:bg-blue-600 transition duration-300">
                Ajouter l'Exercice
            </button>
        </div>
    </form>

    <div class="mt-8">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Exercices par Utilisateur</h3>

        <?php
        $current_user = '';
        foreach ($exercises_by_user as $exercise):
            if ($current_user !== $exercise['user_name']):
                if ($current_user !== '') echo "</ul>";
                $current_user = $exercise['user_name'];
                echo "<h4 class='text-xl font-semibold text-gray-800 mt-4'>" . htmlspecialchars($current_user) . "</h4>";
                echo "<ul class='list-disc ml-6'>";
            endif;
        ?>
            <li>
                <?= htmlspecialchars($exercise['exercise_name']) ?>
                <span class="text-gray-500 text-sm">(<?= htmlspecialchars($exercise['session_name']) ?>)</span>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
</div>

</body>
</html>