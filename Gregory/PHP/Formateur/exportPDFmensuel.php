<?php
// Variables de connexion à la base de données
$serveurNom = "127.0.0.1";
$nomUtilisateur = "root";
$motDePasse = "";
$nomBaseDeDonnees = "epfl_timbreuse";

// Ajout des en-têtes pour le téléchargement
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="rapport.pdf"');
header('Pragma: no-cache');
header('Expires: 0');

// Connexion à la base de données
$connexion = new mysqli($serveurNom, $nomUtilisateur, $motDePasse, $nomBaseDeDonnees);

// Vérifie la connexion à la base de données
if ($connexion->connect_error) {
    die("La connexion à la base de données a échoué : " . $connexion->connect_error);
}

// Assurer que l'encodage de la connexion est en UTF-8
$connexion->set_charset("utf8");

// Récupération des données à mettre dans le PDF
$id = isset($_GET['user_id']) ? $_GET['user_id'] : '';

// Préparation et exécution des requêtes pour récupérer les informations de l'utilisateur
$sql = "SELECT prenom_personne, nom_personne FROM t_personne WHERE id_personne = ?";
$stmt = $connexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($prenom, $nom);
$stmt->fetch();
$stmt->close();

// Fonction pour traduire les mois en français
$mois_francais = array(
    "January" => "Janvier",
    "February" => "Février",
    "March" => "Mars",
    "April" => "Avril",
    "May" => "Mai",
    "June" => "Juin",
    "July" => "Juillet",
    "August" => "Août",
    "September" => "Septembre",
    "October" => "Octobre",
    "November" => "Novembre",
    "December" => "Décembre"
);

function traduireDateEnFrancais($date) {
    global $mois_francais;
    $english_month = date("F", strtotime($date));
    $french_month = $mois_francais[$english_month];
    return str_replace($english_month, $french_month, date("d F Y", strtotime($date)));
}

ob_start();
// Classe PDF avec header personnalisé
require('fpdf/fpdf.php');

class PDF extends FPDF
{
    // Propriété pour stocker le nom de l'utilisateur
    var $username;

    function __construct($username) {
        parent::__construct('L'); // Définir l'orientation en paysage
        $this->username = $username;
    }

    // Header
    function Header()
    {
        // Ajout de l'image
        $this->Image('../../img/epfllogo.png', 10, 6, 30);
        
        // Utilisation du nom de l'utilisateur dans le header
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, utf8_decode($this->username), 0, 1, 'C');
    }

    // Footer
    function Footer()
    {
        // Positionnement à 1.5 cm du bas
        $this->SetY(-15);
        // Police Arial italique 8
        $this->SetFont('Arial', 'I', 8);
        // Numéro de page
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Instanciation de la classe dérivée avec le nom de l'utilisateur
$username = $prenom . " " . $nom;
$pdf = new PDF('Heure de ' . $username);
$pdf->AddPage('L'); // Ajouter une page en mode paysage

// Récupération et affichage des données dans le PDF
$mois_nom = isset($_GET['mois']) ? $_GET['mois'] : date('F');
$annee = isset($_GET['annee']) ? intval($_GET['annee']) : date('Y');

// Conversion du nom du mois en numéro de mois
$mois = date('n', strtotime($mois_nom));

// Génération des dates pour le mois entier
$jours_mois = array();
$nombre_jours = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);

for ($i = 1; $i <= $nombre_jours; $i++) {
    $date = new DateTime();
    $date->setDate($annee, $mois, $i);
    $jours_mois[] = array(
        'format' => traduireDateEnFrancais($date->format("Y-m-d")),
        'date' => $date->format("Y-m-d")
    );
}

// Initialisation des tableaux pour stocker les timbrages et localisations
$timbrages_par_jour = array();
$localisations_par_jour = array();
$manière_timbrage_par_jour = array();

foreach ($jours_mois as $jour) {
    $timbrages_par_jour[$jour['date']] = array();
    $localisations_par_jour[$jour['date']] = array();
}

// Exécution de la requête SQL pour récupérer les données pour chaque jour du mois
$dates = implode("','", array_column($jours_mois, 'date'));
$sql = "SELECT date_timbrage, heure_timbrage, type_location, type_timbrage, manière_timbrage
        FROM t_timbrage 
        WHERE id_personne = ? AND date_timbrage IN ('$dates') 
        ORDER BY date_timbrage, heure_timbrage";
$stmt = $connexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Calculer les dates limites du mois
$firstday = reset($jours_mois)['date'];
$lastday = end($jours_mois)['date'];

// Requête SQL pour calculer les heures totales travaillées
$sql_heures_travaillees = "SELECT TIME_FORMAT(SEC_TO_TIME(ABS(SUM(IF(type_timbrage = 'out', -1, 1) * TIME_TO_SEC(heure_timbrage)))), '%H:%i') as heures_travaillees
                           FROM t_timbrage
                           WHERE id_personne = ? AND date_timbrage BETWEEN ? AND ?";

$stmt_heures_travaillees = $connexion->prepare($sql_heures_travaillees);
$stmt_heures_travaillees->bind_param("iss", $id, $firstday, $lastday);
$stmt_heures_travaillees->execute();
$stmt_heures_travaillees->bind_result($heures_travaillees);
$stmt_heures_travaillees->fetch();
$stmt_heures_travaillees->close();

// Remplir les tableaux avec les données récupérées
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $date = $row["date_timbrage"];
        $heure = $row["heure_timbrage"];
        $localisation = $row["type_location"];
        $type = $row["type_timbrage"];
        $manière_timbrage = $row["manière_timbrage"];
        $timbrages_par_jour[$date][] = array("heure" => $heure, "type" => $type);
        $localisations_par_jour[$date][] = $localisation;
        $manière_timbrage_par_jour[$date][] = $manière_timbrage;
    }
}
$stmt->close();

// Affichage du tableau dans le PDF
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode("Mois de " . $mois_francais[$mois_nom] . " " . $annee), 0, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(43, 10, utf8_decode("Date"), 1);
$pdf->Cell(90, 10, utf8_decode("Heures Timbrées"), 1);
$pdf->Cell(43, 10, utf8_decode("Temps travaillé"), 1);
$pdf->Cell(45, 10, utf8_decode("Localisation"), 1);
$pdf->Cell(55, 10, utf8_decode("Manière de timbrage"), 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
foreach ($jours_mois as $jour) {
    $date = $jour['format'];
    $date_brute = $jour['date'];
    $timbrages = isset($timbrages_par_jour[$date_brute]) ? $timbrages_par_jour[$date_brute] : [];
    $localisations = isset($localisations_par_jour[$date_brute]) ? $localisations_par_jour[$date_brute] : [];
    $manière_timbrages = isset($manière_timbrage_par_jour[$date_brute]) ? $manière_timbrage_par_jour[$date_brute] : [];

    $pdf->Cell(43, 10, utf8_decode($date), 1);
    $pdf->Cell(90, 10, utf8_decode(
        !empty($timbrages) ? implode(", ", array_map(function($timbrage) {
            return date("H:i", strtotime($timbrage['heure'])) . " (" . $timbrage['type'] . ")";
        }, $timbrages)) : "Aucun timbrage"
    ), 1);
    $pdf->Cell(43, 10, utf8_decode(
        !empty($timbrages) ? gmdate("H:i", array_reduce($timbrages, function($carry, $timbrage) {
            if ($timbrage['type'] == 'in') {
                $carry['in'] = strtotime($timbrage['heure']);
            } elseif ($timbrage['type'] == 'out' && isset($carry['in'])) {
                $carry['total'] += strtotime($timbrage['heure']) - $carry['in'];
                unset($carry['in']);
            }
            return $carry;
        }, ['total' => 0])['total']) : "0:00"
    ), 1);
    $pdf->Cell(45, 10, utf8_decode(
        !empty($localisations) ? implode(", ", array_unique($localisations)) : "Aucune localisation"
    ), 1);
    $pdf->Cell(55, 10, utf8_decode(
        !empty($manière_timbrages) ? implode(", ", array_unique($manière_timbrages)) : "Aucune mode de timbrage"
    ), 1);
    $pdf->Ln();
}
// Génération du PDF
ob_end_clean();
$pdf->Output('D', 'rapport.pdf');
?>
