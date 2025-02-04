<?php
session_start();
 
$serveur = "localhost";
$utilisateur = "root";
$motDePasse = "";
$nomBaseDeDonnees = "EPFL_timbreuse";
 
$connexion = new mysqli($serveur, $utilisateur, $motDePasse, $nomBaseDeDonnees);
 
if ($connexion->connect_error) {
    die("Erreur de connexion à la base de données : " . $connexion->connect_error);
}
 
$prenom = $_POST['prenom'];
$nom = $_POST['nom'];
$mail = $_POST['mail'];
$gaspar = $_POST['gaspar'];
$password = $_POST['password'];
$formation = $_POST['formation'];
$year = $_POST['year'];
 
if (!$mail) {
    die("Adresse e-mail invalide.");
}
 
if (empty($prenom) || empty($nom) || empty($gaspar) || empty($password) || empty($formation) || empty($year)) {
    die("Tous les champs sont obligatoires.");
}
 
// Afficher les valeurs pour le débogage
echo "Prénom: " . $prenom . "<br>";
echo "Nom: " . $nom . "<br>";
echo "Email: " . $mail . "<br>";
echo "Gaspar: " . $gaspar . "<br>";
echo "Mot de passe: " . $password . "<br>";
echo "Formation: " . $formation . "<br>";
echo "Année: " . $year . "<br>";
 
// Crypter le mot de passe avec bcrypt
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
 
// Requête préparée pour l'insertion
$requete = "INSERT INTO t_personne (prenom_personne, nom_personne, mail_personne, gaspar_personne
, password_personne, formation, year) VALUES (?, ?, ?, ?, ?, ?, ?);";
$stmt = $connexion->prepare($requete);
 
if ($stmt === false) {
    die("Erreur de préparation de la requête : " . $connexion->error);
}
 
$stmt->bind_param("sssssss", $prenom, $nom, $mail, $gaspar, $hashedPassword, $formation, $year);
 
if ($stmt->execute()) {
    // Fetch the last inserted ID
    $userID = $stmt->insert_id;
 
    // Requête pour récupérer le rôle de l'utilisateur
    $roleRequete = "SELECT role_personne FROM t_personne WHERE id_personne = ?;";
    $roleStmt = $connexion->prepare($roleRequete);
 
    if ($roleStmt === false) {
        die("Erreur de préparation de la requête pour récupérer le rôle : " . $connexion->error);
    }
 
    $roleStmt->bind_param("i", $userID);
 
    if ($roleStmt->execute()) {
        $roleResult = $roleStmt->get_result();
        if ($roleResult->num_rows > 0) {
            $roleRow = $roleResult->fetch_assoc();
            $userRole = $roleRow['role_personne'];
 
            // Assign the user data to session variables
            $_SESSION["user_id"] = $userID;
            $_SESSION["user_gaspar"] = $gaspar;
            $_SESSION["user_prenom"] = $prenom;
            $_SESSION["user_nom"] = $nom;
            $_SESSION["user_mail"] = $mail;
            $_SESSION["user_role"] = $userRole;
 
            if ($userRole == 'Apprentis') {
                // Redirige vers la page d'accueil des apprentis
                header("Location: ../../PHP/home/home.php");
            } elseif ($userRole == 'Formateur') {
                // Redirige vers la page d'accueil des formateurs
                header("Location: ../../PHP/Formateur/HomeFormateur.php");
            }
            exit(); // Terminer le script après la redirection
        } else {
            echo "Erreur lors de la récupération du rôle de l'utilisateur.";
        }
    } else {
        echo "Erreur lors de l'exécution de la requête pour récupérer le rôle : " . $roleStmt->error;
    }
    $roleStmt->close();
} else {
    echo "Erreur lors de l'inscription : " . $stmt->error;
}
 
$stmt->close();
$connexion->close();
?>