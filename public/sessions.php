<?php

include 'header.php'; // Vérifie le token et récupère l'utilisateur connecté

// Ajouter une session
$message = '';
$refresh = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['session_name'])) {
    $session_name = trim($_POST['session_name']);
    if (!empty($session_name)) {
        $sql = "INSERT INTO sessions (name, user_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$session_name, $user_id]);
        $message = "Session ajoutée avec succès.";
        $refresh = true;
    } else {
        $message = "Le nom de la session ne peut pas être vide.";
    }
}

// Supprimer une session
if (isset($_POST['delete_session_id'])) {
    $delete_session_id = (int) $_POST['delete_session_id'];
    $sql = "DELETE FROM sessions WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$delete_session_id, $user_id]);
    $message = "Session supprimée avec succès.";
    $refresh = true;
}

// Rafraîchir la page après une action
if ($refresh) {
    echo '<script>window.location.href="sessions.php";</script>';
    exit;
}

// Récupérer les sessions de l'utilisateur connecté via le token
$sql = "SELECT * FROM sessions WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]); // $user_id est défini dans header.php
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessions de <?= htmlspecialchars($user_name); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="tailwind-config.js"></script>
    <link href="custom.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-primary-50 to-primary-100 min-h-screen p-4 md:p-8">
    <div class="max-w-5xl mx-auto">
        <!-- Header avec bouton add flottant -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-primary-800 flex items-center">
                <i class="fas fa-calendar-alt mr-3 text-primary-600"></i>
                Mes Sessions
            </h1>
            <button onclick="document.getElementById('addSessionModal').classList.remove('hidden')"
                    class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-full shadow-lg flex items-center transition-all hover:scale-105">
                <i class="fas fa-plus mr-2"></i> Nouvelle session
            </button>
        </div>

        <?php if (!empty($message)): ?>
            <div class="mb-6 p-4 rounded-lg <?= strpos($message, 'succès') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Modal d'ajout (caché par défaut) -->
        <div id="addSessionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-xl p-6 max-w-md w-full shadow-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-primary-700">Nouvelle Session</h3>
                    <button onclick="document.getElementById('addSessionModal').classList.add('hidden')"
                            class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form method="POST" action="sessions.php">
                    <input type="text" 
                           name="session_name" 
                           placeholder="Ex: Routine Matinale" 
                           maxlength="20"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg mb-4 focus:ring-2 focus:ring-primary-500">
                    <button type="submit"
                            class="w-full bg-primary-600 hover:bg-primary-700 text-white py-3 px-4 rounded-lg transition-colors">
                        Créer la session
                    </button>
                </form>
            </div>
        </div>

        <!-- Liste des sessions -->
        <?php if (empty($sessions)): ?>
            <div class="bg-white rounded-xl p-8 text-center shadow-sm">
                <i class="fas fa-calendar-plus text-5xl text-primary-400 mb-4"></i>
                <h3 class="text-xl font-medium text-gray-700 mb-2">Aucune session créée</h3>
                <p class="text-gray-500 mb-4">Commencez par créer votre première session d'entraînement</p>
                <button onclick="document.getElementById('addSessionModal').classList.remove('hidden')"
                        class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg">
                    Créer une session
                </button>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($sessions as $session): ?>
                    <div class="bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="bg-gradient-to-r from-primary-500 to-primary-600 p-4 text-white">
                            <h3 class="text-xl font-semibold flex items-center">
                                <i class="fas fa-dumbbell mr-2"></i>
                                <?= htmlspecialchars($session['name']); ?>
                            </h3>
                        </div>
                        <div class="p-5 flex justify-between items-center">
                            <a href="exercises.php?session_id=<?= $session['id']; ?>"
                               class="text-primary-600 hover:text-primary-800 font-medium flex items-center">
                                Voir les exercices <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                            <form method="POST" action="sessions.php">
                                <input type="hidden" name="delete_session_id" value="<?= $session['id']; ?>">
                                <button type="submit" 
                                        onclick="return confirm('Supprimer cette session et tous ses exercices ?');"
                                        class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Fermer la modal avec ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('addSessionModal').classList.add('hidden');
            }
        });
    </script>
</body>
</html>