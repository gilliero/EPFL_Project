<?php
// Initialisation de la session
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    // Redirige vers la page de connexion
    header("Location: ./index.html");
    exit(); // Assure que le script s'arrête après la redirection
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPFL - Home</title>
    <link rel="stylesheet" href="../CSS/HOME/Home.css">
    
</head>
<body>
    <header>
        <img src="../img/epfllogo.png" alt="EPFL Logo">
        <!-- Utilisation du dropdown -->
        <div class="dropdown">
            <p><?php echo $_SESSION["user_prenom"] . " " . $_SESSION["user_nom"] ?></p>
        </div>
    </header>
    
    <main>
        <h1><?php echo "Vous êtes connectés en tant que" . " " . $_SESSION["user_prenom"] . " " . $_SESSION["user_nom"] ?></h1>
</body>
</html>
