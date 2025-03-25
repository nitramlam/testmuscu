<?php
// Informations de connexion à la base de données
$host = 'mysql';
$dbname = 'musculation_db';
$username = 'root';
$password = 'muscu1234';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ajouter une session
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_session'])) {
        $session_name = !empty($_POST['session_name']) ? $_POST['session_name'] : null;

        $sql = "INSERT INTO sessions (session_name) VALUES (:session_name)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':session_name' => $session_name
        ]);
    }

    // Modifier une session
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_session'])) {
        $session_id = $_POST['session_id'];
        $session_name = !empty($_POST['session_name']) ? $_POST['session_name'] : null;

        $sql = "UPDATE sessions SET session_name = :session_name WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $session_id,
            ':session_name' => $session_name
        ]);
    }

    // Supprimer une session
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_session'])) {
        $session_id = $_POST['session_id'];

        $sql = "DELETE FROM sessions WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $session_id]);
    }

    // Récupérer les sessions
    $sessions = $pdo->query("SELECT * FROM sessions")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Sessions</title>
</head>

<body>
    <h1>Gestion des Sessions</h1>

    <!-- Formulaire pour ajouter une session -->
    <h2>Ajouter une session</h2>
    <form method="POST">
        <label for="session_name">Nom de la session :</label>
        <input type="text" name="session_name" id="session_name"><br>

        <button type="submit" name="add_session">Ajouter</button>
    </form>

    <!-- Liste des sessions existantes -->
    <h2>Liste des sessions</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom de la session</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($sessions)): ?>
                <?php foreach ($sessions as $session): ?>
                    <tr>
                        <td><?= htmlspecialchars($session['id'] ?? '') ?></td>
                        <td><?= htmlspecialchars($session['session_name'] ?? '') ?></td>
                        <td>
                            <!-- Formulaire pour modifier une session -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="session_id" value="<?= htmlspecialchars($session['id']) ?>">
                                <input type="text" name="session_name" placeholder="Nom de la session"
                                    value="<?= htmlspecialchars($session['session_name'] ?? '') ?>">
                                <button type="submit" name="edit_session">Modifier</button>
                            </form>

                            <!-- Formulaire pour supprimer une session -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="session_id" value="<?= htmlspecialchars($session['id']) ?>">
                                <button type="submit" name="delete_session">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Aucune session trouvée.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>