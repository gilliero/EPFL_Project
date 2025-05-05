<?php
// Connexion à la base de données (à adapter selon vos paramètres)
$servername = "db-ic.epfl.ch";
$username = "icit_ictrip_adm";
$password = "GdMrL0pZFGKnV8hyntQjFeKKmAbSSQRK";
$dbname = "icit_ictrip";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifie la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Vérifie si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupère les valeurs du formulaire
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Récupère le mot de passe hashé de la base de données pour le nom d'utilisateur donné
    $stmt = $conn->prepare("SELECT id_personne, password_personne FROM t_personne WHERE gaspar_personne = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($userID, $hashedPasswordFromDB);
    $stmt->fetch();
    $stmt->close();

    // Vérifie si le mot de passe correspond
    if (password_verify($password, $hashedPasswordFromDB)) {
        
        // Nouvelle requête SQL pour récupérer les informations de l'utilisateur
        $stmtUserInfo = $conn->prepare("SELECT prenom_personne, nom_personne, mail_personne, role_personne FROM t_personne WHERE id_personne = ?");
        $stmtUserInfo->bind_param("i", $userID);
        $stmtUserInfo->execute();
        $stmtUserInfo->bind_result($prenom, $nom, $mail, $role);
        $stmtUserInfo->fetch();
        $stmtUserInfo->close();

        // Crée une session pour l'utilisateur
        session_start();
        $_SESSION["user_id"] = $userID;
        $_SESSION["user_gaspar"] = $username;
        $_SESSION["user_prenom"] = $prenom;
        $_SESSION["user_nom"] = $nom;
        $_SESSION["user_mail"] = $mail;
        $_SESSION["user_role"] = $role;
    
        if ($role == 'Apprentis') {
            // Redirige vers la page d'accueil
            header("Location: ../../PHP/home/home.php");
        } elseif ($role == 'Formateur') {
            header("Location: ../../PHP/Formateur/HomeFormateur.php");
        } elseif ($role == 'Admin') {
            header("Location: ../../Admin/Adminhome.php");
        } else {
            // Affiche un message d'erreur
            echo "Nom d'utilisateur ou mot de passe incorrect.";
        }
    } else {
        // Affiche un message d'erreur
        echo "Nom d'utilisateur ou mot de passe incorrect.";
    }
}

// Ferme la connexion à la base de données
$conn->close();
?>
