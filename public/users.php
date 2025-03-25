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

    // Ajouter un utilisateur
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
        $name = !empty($_POST['name']) ? $_POST['name'] : null;

        $sql = "INSERT INTO users (name) VALUES (:name)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $name
        ]);
    }

    // Modifier un utilisateur
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
        $user_id = $_POST['user_id'];
        $name = !empty($_POST['name']) ? $_POST['name'] : null;

        $sql = "UPDATE users SET name = :name WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $user_id,
            ':name' => $name
        ]);
    }

    // Supprimer un utilisateur
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];

        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $user_id]);
    }

    // Récupérer les utilisateurs
    $users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
    <style>
        #form-container {
            display: none;
        }
    </style>
    <script>
        function toggleForm() {
            const formContainer = document.getElementById('form-container');
            formContainer.style.display = formContainer.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>

<body>
    <h1>Gestion des Utilisateurs</h1>

    <!-- Liste des utilisateurs existants -->
    <h2>Liste des utilisateurs existants</h2>
    <ul>
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <li>
                    <a href="#"><?= htmlspecialchars($user['name'] ?? '') ?></a>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>Aucun utilisateur trouvé.</li>
        <?php endif; ?>
    </ul>

    <!-- Bouton pour afficher le formulaire -->
    <button onclick="toggleForm()">Ajouter ou modifier un utilisateur</button>

    <!-- Formulaire pour ajouter, modifier ou supprimer un utilisateur -->
    <div id="form-container">
        <h2>Ajouter, modifier ou supprimer un utilisateur</h2>
        <ul>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <li>
                        <?= htmlspecialchars($user['name'] ?? '') ?>
                        <!-- Boutons pour modifier ou supprimer -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                            <input type="text" name="name" placeholder="Modifier le nom"
                                value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                            <button type="submit" name="edit_user">Modifier</button>
                            <button type="submit" name="delete_user">Supprimer</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Aucun utilisateur trouvé.</li>
            <?php endif; ?>
        </ul>

        <h2>Ajouter un utilisateur</h2>
        <form method="POST">
            <!-- Champ pour le nom de l'utilisateur -->
            <label for="name">Nom :</label>
            <input type="text" name="name" id="name" placeholder="Nom de l'utilisateur"><br>

            <!-- Bouton pour ajouter -->
            <button type="submit" name="add_user">Ajouter</button>
        </form>
    </div>
</body>

</html>