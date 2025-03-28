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
<html>

<head>
    <title>Sessions de <?= htmlspecialchars($user_name); ?></title>
</head>

<body>
    <h2>Sessions de <?= htmlspecialchars($user_name); ?></h2>

    <?php if (!empty($message)): ?>
        <p><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Formulaire pour ajouter une session -->
    <form method="POST" action="sessions.php">
        <input type="text" name="session_name" placeholder="Nom de la session" required>
        <button type="submit">Ajouter</button>
    </form>

    <ul>
        <?php foreach ($sessions as $session): ?>
            <li>
                <a href="exercises.php?session_id=<?= $session['id']; ?>">
                    <?= htmlspecialchars($session['name']); ?>
                </a>
                <!-- Formulaire pour supprimer une session -->
                <form method="POST" action="sessions.php" style="display:inline;">
                    <input type="hidden" name="delete_session_id" value="<?= $session['id']; ?>">
                    <button type="submit"
                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette session ?');">Supprimer</button>
                </form>
            </pa>
        <?php endforeach; ?>
    </ul>
</body>

</html>