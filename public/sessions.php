<?php
require 'db.php';
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
    <!-- CDN de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-900 font-sans">

    <div class="max-w-4xl mx-auto mt-12 p-8 bg-white shadow-lg rounded-lg">
        <h2 class="text-3xl font-semibold text-center text-gray-800 mb-6">Sessions de <?= htmlspecialchars($user_name); ?></h2>

        <?php if (!empty($message)): ?>
            <p class="text-center text-red-500 mb-6"><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <!-- Formulaire pour ajouter une session -->
        <form method="POST" action="sessions.php" class="mb-6">
            <div class="flex flex-col items-center">
                <input type="text" name="session_name" placeholder="Nom de la session" maxlength="15" required
                    class="p-3 border border-gray-300 rounded-lg w-72 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit"
                    class="bg-blue-500 text-white p-3 rounded-lg w-72 hover:bg-blue-600 transition duration-300">Ajouter</button>
            </div>
        </form>

        <!-- Liste des sessions -->
        <ul class="space-y-4">
            <?php foreach ($sessions as $session): ?>
                <li class="flex items-center justify-between p-4 bg-gray-50 rounded-lg shadow-md">
                    <a href="exercises.php?session_id=<?= $session['id']; ?>" class="text-lg font-medium text-blue-600 hover:underline">
                        <?= htmlspecialchars($session['name']); ?>
                    </a>
                    <!-- Formulaire pour supprimer une session -->
                    <form method="POST" action="sessions.php" style="display:inline;">
                        <input type="hidden" name="delete_session_id" value="<?= $session['id']; ?>">
                        <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette session ?');"
                            class="bg-red-500 text-white p-2 rounded-lg hover:bg-red-600 transition duration-300">
                            Supprimer
                        </button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

</body>

</html>