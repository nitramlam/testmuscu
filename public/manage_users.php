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
    <style>
        @media (max-width: 640px) {
            .user-card {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
            .user-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-primary-50 to-primary-100 min-h-screen p-4">
    <div class="max-w-6xl mx-auto">
        <!-- Message flash -->
        <?php if (!empty($_SESSION['message'])): ?>
            <div class="mb-4 p-3 rounded-lg <?= strpos($_SESSION['message'], 'succès') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= htmlspecialchars($_SESSION['message']); ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- En-tête -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h1 class="text-2xl sm:text-3xl font-bold text-primary-800 flex items-center">
                <i class="fas fa-users mr-2 sm:mr-3 text-primary-600"></i> 
                <span class="whitespace-nowrap">Gestion des Utilisateurs</span>
            </h1>
        </div>

        <!-- Formulaire d'ajout -->
        <div class="glass-effect rounded-xl shadow-soft p-4 sm:p-6 mb-6">
            <h2 class="text-xl sm:text-2xl font-semibold text-primary-700 mb-4 flex items-center">
                <i class="fas fa-user-plus mr-2"></i> Ajouter un utilisateur
            </h2>
            <form method="post" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-grow">
                    <input type="text" 
                           name="name" 
                           placeholder="Ex: Jean Dupont" 
                           maxlength="20" 
                           required
                           class="w-full pl-9 pr-4 py-2 sm:py-3 border border-primary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-primary-400"></i>
                </div>
                <button type="submit" 
                        name="add_user"
                        class="btn-primary py-2 sm:py-3 px-4 rounded-lg flex items-center justify-center whitespace-nowrap">
                    <i class="fas fa-plus mr-2"></i> Ajouter
                </button>
            </form>
        </div>

        <!-- Liste des utilisateurs -->
        <div class="glass-effect rounded-xl shadow-soft overflow-hidden">
            <div class="bg-gradient-to-r from-primary-600 to-primary-700 p-3 sm:p-4 text-white">
                <h2 class="text-xl sm:text-2xl font-semibold flex items-center">
                    <i class="fas fa-list-alt mr-2"></i> Liste des utilisateurs
                </h2>
            </div>

            <?php if (empty($users)): ?>
                <div class="p-6 sm:p-8 text-center">
                    <i class="fas fa-user-slash text-4xl sm:text-5xl text-primary-300 mb-3"></i>
                    <p class="text-gray-600">Aucun utilisateur enregistré</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                    <?php foreach ($users as $user): ?>
                        <div class="bg-white bg-opacity-70 p-4 rounded-lg border border-primary-100 hover:shadow-md transition-shadow">
                            <!-- Nom utilisateur -->
                            <div class="flex items-center mb-3">
                                <i class="fas fa-user-circle text-primary-400 mr-2 sm:mr-3 text-lg sm:text-xl"></i>
                                <span class="text-gray-900 font-medium truncate"><?= htmlspecialchars($user['name']) ?></span>
                            </div>
                            
                            <!-- Formulaire de modification -->
                            <form method="post" class="user-card grid gap-2 mb-2">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <div class="relative">
                                    <input type="text" 
                                           name="name" 
                                           value="<?= htmlspecialchars($user['name']) ?>" 
                                           maxlength="15" 
                                           required
                                           class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500">
                                    <i class="fas fa-edit absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                </div>
                                <button type="submit" 
                                        name="update_user"
                                        class="text-white bg-green-500 hover:bg-green-600 px-3 py-2 rounded-lg whitespace-nowrap">
                                    <i class="fas fa-check mr-1"></i> <span class="hidden sm:inline">Mettre à jour</span>
                                </button>
                            </form>
                            
                            <!-- Bouton suppression -->
                            <form method="post" class="user-actions grid">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="submit" 
                                        name="delete_user"
                                        onclick="return confirm('Supprimer cet utilisateur et toutes ses données ?');"
                                        class="text-white bg-red-500 hover:bg-red-600 px-3 py-2 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-trash-alt mr-1"></i> <span class="hidden sm:inline">Supprimer</span>
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
