<?php
// Démarre la session
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    // Redirige vers la page de connexion
    header("../../index.html");
    exit(); // Assure que le script s'arrête après la redirection
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "epfl_timbreuse";

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
}

// Récupérer le mois passé dans l'URL
$month = $_GET["month"];
$year = $_GET["year"];

// Récupere l'ID de l'utilisateur connecter
$id = $_SESSION["user_id"];

// Récupérer la date du premier jour du mois
$firstDay = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));

// Récupérer la date du dernier jour du mois
// $lastDay = date("Y-m-t", strtotime($month));
$lastDay = date("Y-m-d", mktime(0, 0, 0, $month + 1, 0, $year));

// Préparation de la requête SQL
$sql = "SELECT ID_personne, date_timbrage, COUNT(*) AS nombre_timbrages,
SEC_TO_TIME(ABS(SUM(IF(type_timbrage = 'out', -1, 1) * TIME_TO_SEC(heure_timbrage)))) as heures_travaillees
FROM t_timbrage
WHERE id_personne = $id
AND date_timbrage BETWEEN '$firstDay' AND '$lastDay'
GROUP BY date_timbrage;";

// Exécution de la requête SQL
$result = $conn->query($sql);

// Vérification des erreurs
if (!$result) {
    die("Erreur lors de l'exécution de la requête : " . $conn->error);
}

// Initialisation du tableau pour stocker les résultats
$arr = array();

// Parcourir les résultats et les stocker dans le tableau
while ($row = $result->fetch_assoc()) {
    $arr[] = $row;
}

$conn->close();

// Encodage du tableau au format JSON
echo json_encode($arr);



?>
