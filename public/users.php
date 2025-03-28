<?php
require '../db.php'; // Connexion à la base de données

// Ajout d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $user_name = $_POST['user_name'] ?? '';
    $user_email = $_POST['user_email'] ?? '';

    if (!empty($user_name) && !empty($user_email)) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $stmt->execute([$user_name, $user_email]);
    }
}

// Suppression d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
}

// Récupérer tous les utilisateurs
$sql = "SELECT * FROM users";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Gestion des utilisateurs</title>
</head>

<body>
    <h2>Liste des utilisateurs</h2>
    <ul>
        <?php foreach ($users as $user): ?>
            <li>
                <?= htmlspecialchars($user['name']); ?> (<?= htmlspecialchars($user['email']); ?>)
                <form method="post" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <button type="submit" name="delete_user">Supprimer</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Ajouter un utilisateur</h2>
    <form method="post">
        <input type="text" name="user_name" placeholder="Nom de l'utilisateur" required>
        <input type="email" name="user_email" placeholder="Email de l'utilisateur" required>
        <button type="submit" name="add_user">Ajouter</button>
    </form>
</body>

</html>