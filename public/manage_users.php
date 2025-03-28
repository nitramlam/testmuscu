<?php
require 'db.php'; // Inclusion du fichier de connexion à la base de données
include 'header.php'; // Vérifie le token et récupère l'utilisateur connecté

// Ajout d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = $_POST['name'] ?? '';

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO users (name) VALUES (?)");
        $stmt->execute([$name]);
    }
}

// Suppression d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
}

// Modification d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'] ?? 0;
    $name = $_POST['name'] ?? '';

    if (!empty($name)) {
        $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->execute([$name, $user_id]);
    }
}

// Récupération de tous les utilisateurs
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Gestion des Utilisateurs</title>
</head>

<body>
    <h2>Ajouter un utilisateur</h2>
    <form method="post">
        <input type="text" name="name" placeholder="Nom de l'utilisateur" required>
        <button type="submit" name="add_user">Ajouter</button>
    </form>

    <h2>Liste des utilisateurs</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <button type="submit" name="delete_user">Supprimer</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                        <button type="submit" name="update_user">Modifier</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>