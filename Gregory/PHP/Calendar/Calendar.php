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
    <title>EPFL - Gestion des heures</title>
    <link rel="stylesheet" href="../../CSS/Calendar/Calendar2.css">
    <link rel="stylesheet" href="../../CSS/Calendar/Calendar.css">
</head>
<body>
<header>
        <img src="../../img/epfllogo.png" alt="EPFL Logo">
        <!-- Utilisation du dropdown -->
        <div class="dropdown">
            <p><?php echo $_SESSION["user_prenom"] . " " . $_SESSION["user_nom"] ?></p>
            <div class="dropdown-content">
                <!-- Bouton de déconnexion -->
                <button onclick="logout()">Déconnexion</button>
            </div>
        </div>
    </header>
    
    <a href="../../PHP/home/home.php" class="home-button">
                <img src="../../img/home.png" alt="home" class="imgbtn">
        </a>
    
    <main>
        <h1>Gestion des heures</h1>
        <p class="time" id="dateTime"></p>
        <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "epfl_timbreuse";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Vérification de la connexion
    if ($conn->connect_error) {
        die("Erreur de connexion à la base de données : " . $conn->connect_error);
    }
    
?>
    </main>
    <div class="container">
        <div class="calendar">
            <div class="header">
                <button id="prevBtn">&lt;</button>
                <h2 id="monthYear"></h2>
                <button id="nextBtn">&gt;</button>
            </div>
            <div class="days"></div>
        </div>
    </div>
    <div class="carrer">
    <div class="good-carrer" style="display: inline-block;"></div>
    <p style="display: inline-block;">Timbrage oke (minimum 4 timbrage)</p></br>
    <div class="suspect-carrer" style="display: inline-block;"></div>
    <p style="display: inline-block;">Timbrage suspect (moins de 4 timbrage)</p></br>
    <div class="bad-carrer" style="display: inline-block;"></div>
    <p style="display: inline-block;">Pas de timbrage</p>
</div>
<script>
        // Fonction de déconnexion
        function logout() {
            // Redirige vers la page de déconnexion
            window.location.href = "../LOGIN/logout.php";
        }
    </script>
    <script src="../../JS/Calendar/homedate&heure.js"></script>
    <script src="../../JS/Calendar/Calendar.js"></script>
</body>
</html>

