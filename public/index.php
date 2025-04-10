<?php
session_start();

require 'db.php'; // Connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;

    if ($user_id) {
        // Vérifier si l'utilisateur existe
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Générer un token unique
            $token = bin2hex(random_bytes(16));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Expire dans 1 heure

            // Mettre à jour le token et son expiration dans la base de données
            $stmt = $pdo->prepare("UPDATE users SET token = :token, token_expiry = :expiry WHERE id = :id");
            if (!$stmt->execute([':token' => $token, ':expiry' => $expiry, ':id' => $user['id']])) {
                die("Erreur : Impossible de mettre à jour le token.");
            }

            // Stocker le token dans la session
            $_SESSION['token'] = $token;

            // Rediriger vers la page des sessions
            header("Location: sessions.php");
            exit;
        } else {
            $error = "Utilisateur introuvable. Veuillez réessayer.";
        }
    } else {
        $error = "Veuillez sélectionner un utilisateur.";
    }
}

// Récupérer tous les utilisateurs pour les afficher dans le formulaire
$stmt = $pdo->query("SELECT id, name FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sélection d'utilisateur | Connexion</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Notre configuration personnalisée -->
    <script src="tailwind-config.js"></script>
   
    
    
    
</head>

<body class="bg-gradient-to-br from-primary-50 to-primary-100 min-h-screen flex items-center justify-center p-4 font-sans">
    <!-- Éléments décoratifs animés -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-20 w-64 h-64 rounded-full bg-primary-200 opacity-20 mix-blend-multiply filter blur-3xl animate-float"></div>
        <div class="absolute bottom-20 right-20 w-64 h-64 rounded-full bg-secondary-200 opacity-20 mix-blend-multiply filter blur-3xl animate-float animate-delay-2000"></div>
    </div>

    <!-- Carte principale -->
    <div class="relative max-w-md w-full mx-auto bg-glass rounded-2xl shadow-xl overflow-hidden transition-all duration-300 hover:shadow-2xl animate-fade-in">
        <!-- En-tête avec dégradé -->
        <div class="bg-gradient-to-r from-primary-600 to-primary-800 p-6 text-center">
            <div class="flex justify-center mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white">Connexion</h1>
            <p class="text-primary-200 mt-2">Sélectionnez votre profil</p>
        </div>

        <!-- Contenu du formulaire -->
        <div class="p-8">
            <?php if (!empty($error)): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 mt-0.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h3 class="font-medium">Erreur</h3>
                        <p class="text-sm mt-1"><?= htmlspecialchars($error) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="space-y-2">
                    <label for="user_id" class="block text-sm font-medium text-gray-700">Utilisateur</label>
                    <div class="relative">
                        <select 
                            name="user_id" 
                            id="user_id" 
                            class="w-full pl-4 pr-10 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200 appearance-none bg-white"
                            required
                        >
                            <option value="">-- Sélectionnez un utilisateur --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-primary-500 to-primary-600 text-white py-3 px-4 rounded-lg hover:from-primary-600 hover:to-primary-700 transition-all duration-300 shadow-md hover:shadow-lg flex items-center justify-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Se connecter
                </button>
            </form>
        </div>
    </div>
</body>

</html>