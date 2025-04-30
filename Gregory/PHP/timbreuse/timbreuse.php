<?php
session_start(); // Démarrer la session si ce n'est pas déjà fait

$servername = "db-ic.epfl.ch";
$username = "icit_ictrip_adm";
$password = "GdMrL0pZFGKnV8hyntQjFeKKmAbSSQRK";
$dbname = "icit_ictrip";

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Récupérer les données du formulaire
$type = $_POST['type'] ?? '';
$timestamp = $_POST['timestamp'] ?? '';

// Séparer la date et l'heure
list($date, $heure) = explode(" ", $timestamp);
$date = date("Y/m/d");

// Récupérer l'ID de l'utilisateur connecté
$id = $_SESSION['user_id'] ?? null;
if (!$id) {
    die("Utilisateur non connecté.");
}

// Récupérer toutes les positions déjà enregistrées pour l'utilisateur et la date donnée
$stmt = $conn->prepare("SELECT position_timbrage FROM t_timbrage WHERE ID_personne = ? AND date_timbrage = ? ORDER BY position_timbrage ASC");
$stmt->bind_param("is", $id, $date);
$stmt->execute();
$result = $stmt->get_result();
$existing_positions = [];
while ($row = $result->fetch_assoc()) {
    $existing_positions[] = $row['position_timbrage'];
}
$stmt->close();

// Trouver la prochaine position disponible en respectant la rotation 1-4
for ($i = 1; $i <= 4; $i++) {
    if (!in_array($i, $existing_positions)) {
        $position = $i;
        break;
    }
}

// Si toutes les positions 1-4 sont prises, recommencer à 1
if (!isset($position)) {
    $position = 1;
}

// Insérer le timbrage
$stmt = $conn->prepare("INSERT INTO t_timbrage (ID_personne, date_timbrage, heure_timbrage, type_timbrage, position_timbrage, manière_timbrage) VALUES (?, ?, ?, ?, ?, ?)");
$maniere = "timbrage";
$stmt->bind_param("isssis", $id, $date, $heure, $type, $position, $maniere);
$result = $stmt->execute();

if ($result) {
    echo "Données insérées avec succès.";
} else {
    echo "Erreur lors de l'insertion des données : " . $conn->error;
}

// Fermer la déclaration et la connexion
$stmt->close();
$conn->close();
?>