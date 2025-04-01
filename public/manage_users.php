<?php

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
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 font-sans antialiased">
    <div class="container mx-auto p-6">
        <!-- Formulaire d'ajout d'utilisateur -->
        <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg mb-10">
            <h2 class="text-3xl font-semibold text-center text-indigo-600 mb-6">Ajouter un utilisateur</h2>
            <form method="post" class="flex flex-col sm:flex-row items-center sm:space-x-4 space-y-4 sm:space-y-0">
                <input type="text" name="name" placeholder="Nom de l'utilisateur" maxlength="15" required
                    class="p-3 border-2 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 w-full sm:w-2/3">
                <button type="submit" name="add_user"
                    class="bg-indigo-600 text-white py-3 px-6 rounded-md hover:bg-indigo-700 transition-colors w-full sm:w-auto">
                    Ajouter
                </button>
            </form>
        </div>

        <!-- Liste des utilisateurs -->
        <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-3xl font-semibold text-center text-indigo-600 mb-6">Liste des utilisateurs</h2>
            <table class="min-w-full table-auto border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-indigo-100 text-indigo-700">
                        <th class="px-6 py-4 text-left">Nom</th>
                        <th class="px-6 py-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr class="border-b hover:bg-gray-100">
                        <td class="px-6 py-4"><?= htmlspecialchars($user['name']) ?></td>
                        <td class="px-6 py-4 flex justify-center space-x-4">
                            <!-- Formulaire de suppression -->
                            <form method="post" class="inline-block">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="submit" name="delete_user"
                                    class="bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 transition duration-300 transform hover:scale-105">
                                    Supprimer
                                </button>
                            </form>

                            <!-- Formulaire de modification -->
                            <form method="post" class="inline-block">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>"
                                    maxlength="15" required
                                    class="p-2 border-2 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 w-32">
                                <button type="submit" name="update_user"
                                    class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition duration-300 transform hover:scale-105">
                                    Modifier
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>