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

// Récupérer les paramètres depuis l'URL
$semaine = isset($_GET['semaine']) ? intval($_GET['semaine']) : date('W');
$annee = isset($_GET['annee']) ? intval($_GET['annee']) : date('Y');
$date = isset($_GET['date']) ? $_GET['date'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';
$heure = isset($_GET['timbrage']) ? $_GET['timbrage'] : '';

// Vérifie si tous les paramètres nécessaires sont fournis
if (empty($date) || empty($id) || empty($heure)) {
    die("Paramètres manquants pour la suppression.");
}

// Exécuter la suppression du timbrage sélectionné
$supprimerRequete = "
    DELETE FROM t_timbrage
    WHERE date_timbrage = '$date'
    AND ID_personne = '$id'
    AND heure_timbrage = '$heure'
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
