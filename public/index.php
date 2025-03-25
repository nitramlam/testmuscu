<?php
// Informations de connexion à la base de données
$host = 'mysql'; // Nom du service défini dans docker-compose.yml
$dbname = 'musculation_db';
$username = 'root';
$password = 'muscu1234';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fonction pour afficher une table HTML
    function afficherTable($pdo, $tableName, $columns) {
        $sql = "SELECT * FROM $tableName";
        $stmt = $pdo->query($sql);

        echo "<h2>Table : $tableName</h2>";
        echo "<table border='1'>";
        echo "<tr>";
        foreach ($columns as $column) {
            echo "<th>" . htmlspecialchars($column) . "</th>";
        }
        echo "</tr>";

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            foreach ($columns as $column) {
                echo "<td>" . htmlspecialchars($row[$column]) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table><br>";
    }

    // Afficher les données des tables
    afficherTable($pdo, 'users', ['id', 'name', 'email', 'password']);
    afficherTable($pdo, 'days', ['id', 'day_name']);
    afficherTable($pdo, 'sessions', ['id', 'session_name']);
    afficherTable($pdo, 'exercises', ['id', 'session_id', 'exercise_name', 'weight', 'sets', 'reps', 'objective_weight']);

} catch (PDOException $e) {
    // Gestion des erreurs
    echo "Erreur de connexion : " . $e->getMessage();
}
?>