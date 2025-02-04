<?php
// Connexion à la base de données (à adapter selon vos paramètres)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "EPFL_timbreuse";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifie la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialisation de la session
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    // Redirige vers la page de connexion
    header("Location: ../../../index.html");
    exit(); // Assure que le script s'arrête après la redirection
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPFL - Home</title>
    <link rel="stylesheet" href="../../CSS/HOME/Home.css">
    
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
    
    <main>
        <h1>Home</h1>
        
        <!-- Boutons restants -->
        <a href="../../../Jeremy/addNotes.php" class="home-button">
            <button>
                <img src="../../img/cahier.png" alt="Note" class="imgbtn">
                <p>Note</p>
            </button>
        </a>

        <a href="../../HTML/timbreuse/timbreuse.php" class="home-button">
            <button>
                <img src="../../img/timbrage.png" alt="Timbrage" class="imgbtn">
                <p>Timbrage</p>
            </button>
        </a>

        <a href="../../PHP/Calendar/Calendar.php" class="home-button">
            <button>
                <img src="../../img/horlorge.png" alt="Heure" class="imgbtn">
                <p>Heure</p>
            </button>
        </a>

        <a href="../../HTML/View/view.php" class="home-button">
            <button>
                <img src="../../img/view.png" alt="View" class="imgbtn">
                <p>Consulté les heures</p>
            </button>
        </a>
    </main>

    <script>
        // Fonction de déconnexion
        function logout() {
            // Redirige vers la page de déconnexion
            window.location.href = "../LOGIN/logout.php";
        }
    </script>
</body>
</html>
