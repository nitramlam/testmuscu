<?php
session_start();

// Vérifier si un utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Exemple : Stocker le nom de l'utilisateur dans la session (à adapter selon votre logique)
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = "Nom par défaut"; // Remplacez par une requête pour récupérer le nom réel
}

// Récupérer les informations de l'utilisateur connecté
$host = 'mysql';
$dbname = 'musculation_db';
$username = 'root';
$password = 'muscu1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Si l'utilisateur n'existe pas, détruire la session
        session_destroy();
        header("Location: users.php");
        exit;
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}
?>
<div>
    <p>Connecté en tant que : <?= htmlspecialchars($user['name']) ?></p>
    <a href="logout.php">Se déconnecter</a>
</div>