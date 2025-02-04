<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../index.html");
    exit();
}

$serveurNom = "127.0.0.1";
$nomUtilisateur = "root";
$motDePasse = "";
$nomBaseDeDonnees = "epfl_timbreuse";

$connexion = new mysqli($serveurNom, $nomUtilisateur, $motDePasse, $nomBaseDeDonnees);

if ($connexion->connect_error) {
    die("La connexion à la base de données a échoué : " . $connexion->connect_error);
}

$day = isset($_GET['day']) ? $_GET['day'] : '';
$month = isset($_GET['month']) ? $_GET['month'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';
$location = isset($_GET['option']) ? $_GET['option'] : '';
$user_id = $_SESSION["user_id"];

$formattedDate = sprintf('%04d-%02d-%02d', $year, $month, $day);

// Check if any manière_timbrage is 'timbrage' after the update
$checkManiereQuery = "SELECT manière_timbrage FROM t_timbrage WHERE ID_personne = ? AND date_timbrage = ?";
$stmt = $connexion->prepare($checkManiereQuery);
$stmt->bind_param('ss', $user_id, $formattedDate);
$stmt->execute();
$result = $stmt->get_result();

$redirect = false;
while ($row = $result->fetch_assoc()) {
    if ($row['manière_timbrage'] == 'timbrage') {
        $redirect = true;
        break;
    }
}

if ($redirect) {
    header("Location: ../Heure/Heure.php?day=$day&month=$month&year=$year&error=true");
    exit();
} else {
    // Check if there are already timestamps for the selected date
    $checkQuery = "SELECT COUNT(*) as count FROM t_timbrage WHERE ID_personne = ? AND date_timbrage = ?";
    $stmt = $connexion->prepare($checkQuery);
    $stmt->bind_param('ss', $user_id, $formattedDate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $row = $result->fetch_assoc();
        if ($row['count'] == 0) {
            // No existing timestamps, proceed with INSERT
            $query = "INSERT INTO t_timbrage (ID_personne, date_timbrage, heure_timbrage, type_timbrage, type_location, position_timbrage, manière_timbrage) VALUES 
            (?, ?, '08:00:00', 'in', ?, '1', 'absence'), 
            (?, ?, '12:00:00', 'out', ?, '2', 'absence'), 
            (?, ?, '13:00:00', 'in', ?, '3', 'absence'), 
            (?, ?, '17:12:00', 'out', ?, '4', 'absence')";
            $stmt = $connexion->prepare($query);
            if ($stmt === false) {
                die("Erreur de préparation de la requête d'insertion : " . $connexion->error);
            }
            $stmt->bind_param('ssssssssssss', $user_id, $formattedDate, $location, $user_id, $formattedDate, $location, $user_id, $formattedDate, $location, $user_id, $formattedDate, $location);

            if ($stmt->execute() === false) {
                die("Erreur lors de l'exécution de la requête d'insertion : " . $stmt->error);
            }
        } else {
            // Update existing timestamps (assuming we want to update specific times)
            $update_queries = [
                ["UPDATE t_timbrage SET heure_timbrage = '08:00:00', type_location = ?, type_timbrage = 'in', manière_timbrage = 'absence' WHERE ID_personne = ? AND date_timbrage = ? AND position_timbrage = '1' AND manière_timbrage != 'timbrage'", $location, $user_id, $formattedDate],
                ["UPDATE t_timbrage SET heure_timbrage = '12:00:00', type_location = ?, type_timbrage = 'out', manière_timbrage = 'absence' WHERE ID_personne = ? AND date_timbrage = ? AND position_timbrage = '2' AND manière_timbrage != 'timbrage'", $location, $user_id, $formattedDate],
                ["UPDATE t_timbrage SET heure_timbrage = '13:00:00', type_location = ?, type_timbrage = 'in', manière_timbrage = 'absence' WHERE ID_personne = ? AND date_timbrage = ? AND position_timbrage = '3' AND manière_timbrage != 'timbrage'", $location, $user_id, $formattedDate],
                ["UPDATE t_timbrage SET heure_timbrage = '17:12:00', type_location = ?, type_timbrage = 'out', manière_timbrage = 'absence' WHERE ID_personne = ? AND date_timbrage = ? AND position_timbrage = '4' AND manière_timbrage != 'timbrage'", $location, $user_id, $formattedDate]
            ];

            foreach ($update_queries as $query_data) {
                $stmt = $connexion->prepare($query_data[0]);
                if ($stmt === false) {
                    die("Erreur de préparation de la requête de mise à jour : " . $connexion->error);
                }
                $stmt->bind_param('sss', $query_data[1], $query_data[2], $query_data[3]);
                if ($stmt->execute() === false) {
                    die("Erreur lors de l'exécution de la requête de mise à jour : " . $stmt->error);
                }
            }
        }
    } else {
        die("Erreur lors de la vérification des timbrages existants : " . $connexion->error);
    }
}

$stmt->close();
$connexion->close();

header("Location: ../Calendar/Calendar.php");
exit();
?>
