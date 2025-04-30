<?php
// Démarre la session
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    // Redirige vers la page de connexion
    header("../../../index.html");
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
        
        <!-- Bouton "Note" -->
        <a href="../../../Jeremy/viewNotes.php" class="home-button">
            <button>
                <img src="../../img/cahier.png" alt="Note" class="imgbtn">
                <p>Consulter les notes</p>
            </button>
        </a>


        <!-- Bouton "Heure" -->
        <a href="./viewFormateur.php" class="home-button">
            <button>
                <img src="../../img/view.png" alt="Heure" class="imgbtn">
                <p>Consulter les heures</p>
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
