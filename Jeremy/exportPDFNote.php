<?php
// Variables de connexion à la base de données
$serveurNom = "127.0.0.1";
$nomUtilisateur = "root";
$motDePasse = "";
$nomBaseDeDonnees = "epfl_timbreuse";

// Connexion à la base de données
$connexion = new mysqli($serveurNom, $nomUtilisateur, $motDePasse, $nomBaseDeDonnees);

// Vérifie la connexion à la base de données
if ($connexion->connect_error) {
    die("La connexion à la base de données a échoué : " . $connexion->connect_error);
}

// Assurer que l'encodage de la connexion est en UTF-8
$connexion->set_charset("utf8");

// Récupération de l'ID utilisateur depuis l'URL
$id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($id <= 0) {
    die("ID utilisateur invalide.");
}

// Préparation et exécution des requêtes pour récupérer les informations de l'utilisateur
$sql = "SELECT prenom_personne, nom_personne FROM t_personne WHERE id_personne = ?";
$stmt = $connexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($prenom, $nom);
$stmt->fetch();
$stmt->close();

if (empty($prenom) || empty($nom)) {
    die("Utilisateur non trouvé.");
}

// Récupération et regroupement des notes par branche, y compris le calcul de la moyenne
$sqlNotes = "
    SELECT 
        branche, 
        GROUP_CONCAT(note ORDER BY note SEPARATOR ', ') AS notes,
        AVG(note) AS moyenne
    FROM notes 
    WHERE user_id = ? 
    GROUP BY branche
";
$stmtNotes = $connexion->prepare($sqlNotes);
$stmtNotes->bind_param("i", $id);
$stmtNotes->execute();
$resultNotes = $stmtNotes->get_result();

// Activation du tampon de sortie
ob_start();
require('fpdf/fpdf.php');

class PDF extends FPDF
{
    var $username;
    
    function __construct($username) {
        parent::__construct('L');
        $this->username = $username;
    }

    function Header()
    {
        $this->Image('../Gregory/img/epfllogo.png', 10, 6, 30);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, utf8_decode($this->username), 0, 1, 'C');
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Création du PDF
$username = $prenom . " " . $nom;
$pdf = new PDF('Notes de ' . $username);
$pdf->AddPage('L');
$pdf->SetFont('Arial', '', 12);

// Centrage du titre du rapport
$pdf->Cell(0, 10, utf8_decode("Rapport de notes"), 0, 1, 'C');
$pdf->Ln(5);

// Largeurs des colonnes
$largeurColonne = 60;
$nombreColonnes = 3;
$largeurTableau = $largeurColonne * $nombreColonnes; // Largeur totale du tableau
$positionX = (210 - $largeurTableau) / 2; // Calcul pour centrer le tableau sur la page

// Centrage des titres et données dans le tableau
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetX($positionX);  // Déplace la position à celle calculée pour centrer le tableau
$pdf->Cell($largeurColonne, 10, utf8_decode("Branches"), 1, 0, 'C');
$pdf->Cell($largeurColonne, 10, utf8_decode("Notes"), 1, 0, 'C');
$pdf->Cell($largeurColonne, 10, utf8_decode("Moyenne"), 1, 1, 'C');

$pdf->SetFont('Arial', '', 12);
while ($row = $resultNotes->fetch_assoc()) {
    $pdf->SetX($positionX);  // Réajuste la position pour chaque ligne du tableau
    $pdf->Cell($largeurColonne, 10, utf8_decode($row['branche']), 1, 0, 'C');
    $pdf->Cell($largeurColonne, 10, utf8_decode($row['notes']), 1, 0, 'C');
    $pdf->Cell($largeurColonne, 10, utf8_decode(number_format($row['moyenne'], 2)), 1, 1, 'C');
}

// Nettoyage des ressources
$stmtNotes->close();
$connexion->close();

// Nettoyage du tampon pour éviter les erreurs
if (ob_get_length()) {
    ob_clean();
}

// Ajout des en-têtes pour le téléchargement
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="rapport_semaine.pdf"');
header('Pragma: no-cache');
header('Expires: 0');

// Génération et sortie du PDF
$pdf->Output('D', 'rapport_semaine.pdf');
exit;
?>
