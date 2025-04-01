<?php
// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=musculation_db;charset=utf8mb4', 'root', '');

// Récupération des données du formulaire
$exerciseName = $_POST['exercise_name'];
$sessions = $_POST['sessions'];

// Insertion de l'exercice dans la table `exercises`
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO exercises (name) VALUES (:name)");
    $stmt->execute(['name' => $exerciseName]);
    $exerciseId = $pdo->lastInsertId();

    // Association de l'exercice aux sessions sélectionnées
    $stmt = $pdo->prepare("INSERT INTO exercises_sessions (exercise_id, session_id) VALUES (:exercise_id, :session_id)");
    foreach ($sessions as $sessionId) {
        $stmt->execute(['exercise_id' => $exerciseId, 'session_id' => $sessionId]);
    }

    $pdo->commit();
    echo "Exercice ajouté avec succès !";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Erreur : " . $e->getMessage();
}
?>