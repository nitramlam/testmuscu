<?php
ob_start();
include 'header.php';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['new_exercise_name'])) {
        $new_exercise_name = trim($_POST['new_exercise_name']);
        $stmt = $pdo->prepare("INSERT INTO exercises (name) VALUES (?)");
        $stmt->execute([$new_exercise_name]);
        $exercise_id = $pdo->lastInsertId();

        if (!empty($_POST['sessions'])) {
            foreach ($_POST['sessions'] as $session_id) {
                $stmt = $pdo->prepare("INSERT INTO exercises_sessions (exercise_id, session_id) VALUES (?, ?)");
                $stmt->execute([$exercise_id, $session_id]);
            }
            $_SESSION['message'] = "✅ Exercice créé et ajouté à " . count($_POST['sessions']) . " sessions.";
        } else {
            $_SESSION['message'] = "⚠️ Exercice créé, mais aucune session sélectionnée.";
        }
    } else {
        $_SESSION['message'] = "⚠️ Veuillez saisir un nom d'exercice.";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$exercises = $pdo->query("SELECT * FROM exercises")->fetchAll(PDO::FETCH_ASSOC);
$sessions = $pdo->query("SELECT s.*, u.name AS user_name FROM sessions s LEFT JOIN users u ON s.user_id = u.id")->fetchAll(PDO::FETCH_ASSOC);
$exercises_by_user = $pdo->query("
    SELECT u.name AS user_name, e.name AS exercise_name, s.name AS session_name
    FROM exercises_sessions es
    JOIN exercises e ON es.exercise_id = e.id
    JOIN sessions s ON es.session_id = s.id
    JOIN users u ON s.user_id = u.id
    ORDER BY u.name, e.name
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Exercices</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="custom.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-primary-50 to-primary-100 min-h-screen px-4 sm:px-6 lg:px-8 py-6">
    <div class="max-w-5xl mx-auto space-y-10">

        <!-- En-tête -->
        <div class="glass-effect rounded-xl shadow-soft p-6 animate-fade-in-down">
            <h1 class="text-2xl sm:text-3xl font-bold text-primary-800 mb-2 flex items-center justify-center">
                <i class="fas fa-dumbbell mr-3"></i>Gestion des Exercices
            </h1>
            <p class="text-center text-primary-600">Ajouter des exercices aux sessions</p>
        </div>

        <!-- Message flash -->
        <?php if (!empty($_SESSION['message'])): ?>
            <div class="mb-6 p-4 rounded-lg <?=
                str_contains($_SESSION['message'], '✅') ? 'bg-green-100 text-green-700' :
                (str_contains($_SESSION['message'], '⚠️') ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700')
            ?>">
                <?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire principal -->
        <div class="glass-effect rounded-xl shadow-soft p-6 animate-fade-in space-y-6">
            <h2 class="text-xl sm:text-2xl font-semibold text-primary-700 flex items-center">
                <i class="fas fa-plus-circle mr-2"></i>Créer un nouvel exercice
            </h2>

            <form method="POST" class="space-y-6">
                <div class="relative">
                    <input type="text"
                           name="new_exercise_name"
                           placeholder="Ex: Développé couché"
                           maxlength="30"
                           required
                           class="w-full pl-10 pr-4 py-3 border border-primary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <i class="fas fa-dumbbell absolute left-3 top-3.5 text-primary-400"></i>
                </div>

                <div class="space-y-3">
                    <h3 class="text-lg font-medium text-primary-700 flex items-center">
                        <i class="fas fa-calendar-alt mr-2"></i>Sélectionnez les sessions
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 max-h-60 overflow-y-auto p-2 custom-scrollbar">
                        <?php foreach ($sessions as $session): ?>
                        <label class="flex items-center space-x-3 p-3 bg-white bg-opacity-50 rounded-lg hover:bg-primary-50 transition-colors border border-primary-100">
                            <input type="checkbox"
                                   name="sessions[]"
                                   value="<?= $session['id']; ?>"
                                   class="rounded text-primary-600 focus:ring-primary-500 h-5 w-5">
                            <span class="flex-1">
                                <span class="font-medium text-primary-800"><?= htmlspecialchars($session['name']); ?></span>
                                <span class="block text-sm text-primary-500"><?= htmlspecialchars($session['user_name']); ?></span>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit"
                        class="w-full sm:w-auto bg-gradient-to-r from-primary-600 to-primary-700 text-white py-3 px-6 rounded-lg hover:from-primary-700 hover:to-primary-800 transition-all flex items-center justify-center shadow-md hover:shadow-lg">
                    <i class="fas fa-plus mr-2"></i>Créer et ajouter aux sessions
                </button>
            </form>
        </div>

        <!-- Liste des exercices par utilisateur -->
        <div class="glass-effect rounded-xl shadow-soft overflow-hidden animate-fade-in-up">
            <div class="bg-gradient-to-r from-primary-600 to-primary-700 p-4 text-white">
                <h2 class="text-xl sm:text-2xl font-semibold flex items-center">
                    <i class="fas fa-list-ul mr-2"></i>Répartition des exercices
                </h2>
            </div>

            <div class="p-6">
                <?php
                $current_user = '';
                foreach ($exercises_by_user as $exercise):
                    if ($current_user !== $exercise['user_name']):
                        if ($current_user !== '') echo "</div></div>";
                        $current_user = $exercise['user_name'];
                ?>
                <div class="mb-6">
                    <h3 class="text-lg sm:text-xl font-semibold text-primary-800 mb-3 flex items-center cursor-pointer transition hover:text-primary-600"
                        onclick="toggleVisibility('exercises-<?= md5($current_user) ?>')">
                        <i class="fas fa-user mr-2"></i><?= htmlspecialchars($current_user) ?>
                        <i class="fas fa-chevron-down ml-auto text-sm text-primary-500"></i>
                    </h3>

                    <div id="exercises-<?= md5($current_user) ?>" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php endif; ?>
                        <div class="bg-white bg-opacity-70 p-4 rounded-lg border border-primary-100 hover:shadow-md transition-shadow">
                            <div class="font-medium text-primary-800 flex items-center">
                                <i class="fas fa-dumbbell text-primary-500 mr-2"></i>
                                <?= htmlspecialchars($exercise['exercise_name']) ?>
                            </div>
                            <div class="text-sm text-primary-500 mt-1 flex items-center">
                                <i class="fas fa-calendar-day mr-1"></i>
                                <?= htmlspecialchars($exercise['session_name']) ?>
                            </div>
                        </div>
                <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleVisibility(id) {
            const el = document.getElementById(id);
            el.classList.toggle('hidden');
        }
    </script>
</body>
</html>
