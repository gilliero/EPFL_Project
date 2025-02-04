<?php
session_start(); // Démarrer la session si ce n'est pas déjà fait

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "epfl_timbreuse";

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Récupérer les données du formulaire (assurez-vous de les filtrer et valider correctement)
$type = $_POST['type'] ?? '';
$timestamp = $_POST['timestamp'] ?? '';

// Séparer la date et l'heure
list($date, $heure) = explode(" ", $timestamp);

// Déclaration de la date
$date = date("Y/m/d");

// Déclaration de l'utilisateur connecté (assurez-vous que $_SESSION['user_id'] est défini)
$id = $_SESSION['user_id'] ?? null;

if (!$id) {
    die("Utilisateur non connecté.");
}

// Récupérer le nombre actuel de timbrages pour l'utilisateur et la date donnée
$stmt = $conn->prepare("SELECT COUNT(*) FROM t_timbrage WHERE ID_personne = ? AND date_timbrage = ?");
$stmt->bind_param("is", $id, $date);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

// Déterminer la position du nouveau timbrage
$position = $count + 1;

if ($position > 4) {
    die("Limite de 4 timbrages atteinte pour aujourd'hui.");
}

// Préparer et exécuter la requête d'insertion en utilisant une déclaration préparée
$stmt = $conn->prepare("INSERT INTO t_timbrage (ID_personne, date_timbrage, heure_timbrage, type_timbrage, position_timbrage, manière_timbrage) VALUES (?, ?, ?, ?, ?, ?)");

// Liaison des paramètres et types
$maniere = "timbrage"; // Déclarer la variable manière_timbrage
$stmt->bind_param("isssis", $id, $date, $heure, $type, $position, $maniere); // i pour integer, s pour string

// Exécuter la requête
$result = $stmt->execute();

if($result) {
    echo "Données insérées avec succès.";
} else {
    echo "Erreur lors de l'insertion des données : " . $conn->error;
}

// Fermer la déclaration et la connexion
$stmt->close();
$conn->close();
?>
