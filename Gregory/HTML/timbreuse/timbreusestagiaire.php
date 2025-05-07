<?php
// Démarre la session
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    // Redirige vers la page de connexion
    header("Location: ../../index.html");
    exit(); // Assure que le script s'arrête après la redirection
}

// Connexion à la base de données (remplacez les détails de connexion par les vôtres)
$servername = "db-ic.epfl.ch";
$username = "icit_ictrip_adm";
$password = "GdMrL0pZFGKnV8hyntQjFeKKmAbSSQRK";
$dbname = "icit_ictrip";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifie la connexion à la base de données
if ($conn->connect_error) {
    die("Échec de la connexion à la base de données: " . $conn->connect_error);
}

// Vérifie si l'utilisateur a déjà timbré aujourd'hui
$user_id = $_SESSION["user_id"];
$today = date("Y-m-d");
$query = "SELECT * FROM t_timbrage WHERE ID_personne = $user_id AND date_timbrage = '$today'";
$result = $conn->query($query);

// Vérifie s'il y a des erreurs dans la requête SQL
if ($result === false) {
    die("Erreur dans la requête SQL : " . $conn->error);
}

$nombre_timbrage = $result->num_rows;

if ($result->num_rows > 0) {
    // L'utilisateur a déjà timbré aujourd'hui, vous pouvez prendre des mesures appropriées ici
    // Par exemple, rediriger l'utilisateur avec un message approprié
    header("Location: timbreuse2.php");
    exit();
}

// Définir la visibilité du bouton
$button_visible = $nombre_timbrage < 4;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPFL - Gestion des heures</title>
    <link rel="stylesheet" href="../../CSS/Timbreuse/timbreuse.css">
    
    <style>
        ul#logList li.in {
            color: green;
            list-style-type: none;
        }

        ul#logList li.out {
            color: red;
            list-style-type: none;
        }
    </style>
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

    <a href="../../PHP/home/Stagehome.php" class="home-button">
        <img src="../../img/home.png" alt="home" class="imgbtn">
    </a>

    <main>
        <h1>Gestion des heures</h1>
        <p class="time" id="dateTime"></p>
        <ul id="logList"></ul>
        <?php if ($button_visible): ?>
            <button id="toggleButton">Pause</button>
        <?php endif; ?>
        <p>
            <a class="link-calendar" href="../../PHP/Calendar/Calendar.php">Entrez vos heures manuellement</a>
        </p>   
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var isPaused = false;
            var nombreTimbrage = <?php echo $nombre_timbrage; ?>;

            // Fonction pour ajouter une entrée ou une sortie à la liste et à la base de données
            function addLog(type) {
                var logList = document.getElementById('logList');
                var logEntry = document.createElement('li');
                var options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Europe/Zurich' };
                var timestamp = new Date().toLocaleString('fr-CH', options);

                // Insérer dans la base de données
                insertIntoDatabase(type, timestamp);

                // Ajouter à la liste avec la classe appropriée
                logEntry.textContent = (type === 'in' ? 'Entrée' : 'Sortie') + ' le ' + timestamp;
                logEntry.classList.add(type); // Ajoute la classe 'in' ou 'out'
                logList.appendChild(logEntry);

                // Incrémente le nombre de timbrages
                nombreTimbrage++;

                // Vérifie si le bouton doit disparaître
                if (nombreTimbrage >= 4) {
                    document.getElementById('toggleButton').style.display = 'none';
                }
            }

            // Fonction pour insérer les données dans la base de données
            function insertIntoDatabase(type, timestamp) {
                // Remplacez les détails de connexion à la base de données par les vôtres
                var url = '../../PHP/timbreuse/timbreuse.php'; // Fichier PHP pour insérer les données
                var data = new FormData();
                data.append('type', type);
                data.append('timestamp', timestamp);

                fetch(url, {
                    method: 'POST',
                    body: data
                })
                .then(response => response.json())
                .then(data => console.log(data))
                .catch(error => console.error('Erreur lors de la requête :', error));
            }

            // Fonction pour mettre à jour l'interface utilisateur en fonction de l'état de pause
            function updateUI() {
                var button = document.getElementById('toggleButton');

                if (isPaused) {
                    // Si en pause, change le texte du bouton en "Start"
                    button.textContent = 'Start';
                    addLog('out'); // Ajoute une sortie à la liste et à la base de données
                } else {
                    // Si en entrée, change le texte du bouton en "Pause"
                    button.textContent = 'Pause';
                    addLog('in'); // Ajoute une entrée à la liste et à la base de données
                }
            }

            // Gestionnaire d'événements pour le bouton
            if (document.getElementById('toggleButton')) {
                document.getElementById('toggleButton').addEventListener('click', function () {
                    isPaused = !isPaused; // Inverse l'état de pause
                    updateUI(); // Met à jour l'interface utilisateur en conséquence
                });

                // Initialisation de l'interface utilisateur
                updateUI();

                // Masquer le bouton si le nombre de timbrages est déjà >= 4
                if (nombreTimbrage >= 4) {
                    document.getElementById('toggleButton').style.display = 'none';
                }
            }
        });
    </script>
     <script>
        // Fonction de déconnexion
        function logout() {
            // Redirige vers la page de déconnexion
            window.location.href = "../../PHP/LOGIN/logout.php";
        }
    </script>
    <script src="../../JS/Timbreuse/homedate&heure.js"></script>
    
</body>
</html>
