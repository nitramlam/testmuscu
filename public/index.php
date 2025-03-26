<?php
require 'db.php';

// Récupérer tous les utilisateurs
$sql = "SELECT * FROM users";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Choisir un utilisateur</title>
</head>
<body>
    <h2>Choisir un utilisateur</h2>
    <form action="sessions.php" method="GET">
        <select name="user_id">
            <?php foreach ($users as $user): ?>
                <option value="<?= $user['id']; ?>"><?= htmlspecialchars($user['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Voir les sessions</button>
    </form>
</body>
</html>