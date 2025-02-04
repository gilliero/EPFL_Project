<?php
// Démarre la session
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    // Redirige vers la page de connexion
    header("Location: ../../../index.html");
    exit(); // Assure que le script s'arrête après la redirection
}

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




 // Récupérer la semaine et l'année depuis le lien
 $semaine = isset($_GET['semaine']) ? intval($_GET['semaine']) : date('W');
 $annee = isset($_GET['annee']) ? intval($_GET['annee']) : date('Y');
 $date = isset($_GET['date']) ? $_GET['date'] : '';
 $id = isset($_GET['id']) ? $_GET['id'] : '';

// Exécuter la suppression
$supprimerRequete = "
    DELETE t1 FROM t_timbrage t1
    INNER JOIN (
        SELECT MAX(position_timbrage) as max_position
        FROM t_timbrage
        WHERE date_timbrage = '$date' AND ID_personne = '$id'
    ) t2 ON t1.position_timbrage = t2.max_position
    WHERE t1.date_timbrage = '$date' AND t1.ID_personne = '$id'
";

if ($connexion->query($supprimerRequete) === TRUE) {
    echo "Enregistrement supprimé avec succès";
} else {
    echo "Erreur lors de la suppression de l'enregistrement : " . $connexion->error;
}

// Redirige vers la page viewFormateur
header("Location: ./viewdayFormateur.php?id_user=$id&semaine=$semaine&annee=$annee");
exit();

// Fermeture de la connexion à la base de données
$connexion->close();
?>
