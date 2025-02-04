<?php
// Démarre la session
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    // Redirige vers la page de connexion
    header("../../index.html");
    exit(); // Assure que le script s'arrête après la redirection
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="../../CSS/Calendar/Calendar2.css">
</head>
<body>
    <header>
        <img src="../../img/epfllogo.png" alt="EPFL Logo">
        <p id="name"><?php echo $_SESSION["user_prenom"] . " " . $_SESSION["user_nom"] ?></p>
    </header>
    
    <a href="../../PHP/home/home.php" class="home-button">
                <img src="../../img/home.png" alt="home" class="imgbtn">
        </a>
    
    <main>
        <h1>Visuel des heures</h1>
        <p class="time" id="dateTime"></p>
    </main>

</body>
</html>


