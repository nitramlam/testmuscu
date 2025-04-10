<?php
ob_start();
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = $_POST['name'] ?? '';
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO users (name) VALUES (?)");
        $stmt->execute([$name]);
        $_SESSION['message'] = "Utilisateur ajouté avec succès.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $_SESSION['message'] = "Utilisateur supprimé avec succès.";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'] ?? 0;
    $name = $_POST['name'] ?? '';
    if (!empty($name)) {
        $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->execute([$name, $user_id]);
        $_SESSION['message'] = "Nom de l'utilisateur mis à jour.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

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
    <script src="tailwind-config.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-primary-50 to-primary-100 min-h-screen p-4 md:p-8">
<div class="max-w-6xl mx-auto">
    <?php if (!empty($_SESSION['message'])): ?>
        <div class="mb-6 p-4 rounded-lg <?= strpos($_SESSION['message'], 'succès') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
            <?= htmlspecialchars($_SESSION['message']); ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-primary-800 flex items-center">
            <i class="fas fa-users mr-3 text-primary-600"></i> Gestion des Utilisateurs
        </h1>
    </div>

    <div class="glass-effect rounded-xl shadow-soft p-6 mb-10 animate-fade-in">
        <h2 class="text-2xl font-semibold text-primary-700 mb-6 flex items-center">
            <i class="fas fa-user-plus mr-2"></i> Ajouter un utilisateur
        </h2>
        <form method="post" class="flex flex-col sm:flex-row gap-4 items-center">
            <div class="relative flex-grow w-full">
                <input type="text" name="name" placeholder="Ex: Jean Dupont" maxlength="20" required
                       class="w-full pl-10 pr-4 py-3 border border-primary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                <i class="fas fa-user absolute left-3 top-3.5 text-primary-400"></i>
            </div>
            <button type="submit" name="add_user"
                    class="btn-primary w-full sm:w-auto flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i> Ajouter
            </button>
        </form>
    </div>

    <div class="glass-effect rounded-xl shadow-soft overflow-hidden animate-fade-in-up">
        <div class="bg-gradient-to-r from-primary-600 to-primary-700 p-4 text-white">
            <h2 class="text-2xl font-semibold flex items-center">
                <i class="fas fa-list-alt mr-2"></i> Liste des utilisateurs
            </h2>
        </div>

        <?php if (empty($users)): ?>
            <div class="p-8 text-center">
                <i class="fas fa-user-slash text-5xl text-primary-300 mb-4"></i>
                <p class="text-gray-600">Aucun utilisateur enregistré</p>
            </div>
        <?php else: ?>
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($users as $user): ?>
                    <div class="bg-white bg-opacity-70 p-4 rounded-lg border border-primary-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-user-circle text-primary-400 mr-3 text-xl"></i>
                            <span class="text-gray-900 font-medium text-lg"><?= htmlspecialchars($user['name']) ?></span>
                        </div>
                        <form method="post" class="flex flex-col sm:flex-row items-center gap-2 mb-2">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <div class="relative w-full sm:w-auto flex-grow">
                                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" maxlength="15" required
                                       class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                                <i class="fas fa-edit absolute left-2 top-2.5 text-gray-400"></i>
                            </div>
                            <button type="submit" name="update_user"
                                    class="text-white bg-green-500 hover:bg-green-600 px-3 py-2 rounded-lg">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        <form method="post">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="submit" name="delete_user"
                                    onclick="return confirm('Supprimer cet utilisateur et toutes ses données ?');"
                                    class="text-white bg-red-500 hover:bg-red-600 w-full px-3 py-2 rounded-lg">
                                <i class="fas fa-trash-alt mr-1"></i> Supprimer
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
