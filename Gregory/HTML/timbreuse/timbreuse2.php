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

if ($result->num_rows == 0) {
    header("Location: timbreuse.php");
    exit();
}

// Récupère le dernier timbrage de l'utilisateur connecté aujourd'hui
$queryLastTimbrage = "SELECT type_timbrage FROM t_timbrage WHERE ID_personne = $user_id AND date_timbrage = '$today' ORDER BY heure_timbrage DESC LIMIT 1";
$resultLastTimbrage = $conn->query($queryLastTimbrage);

// Vérifie s'il y a des erreurs dans la requête SQL
if ($resultLastTimbrage === false) {
    die("Erreur dans la requête SQL : " . $conn->error);
}

$lastTimbrageType = ($resultLastTimbrage->num_rows > 0) ? $resultLastTimbrage->fetch_assoc()["type_timbrage"] : null;

if ($lastTimbrageType === 'in') {
    header("Location: timbreuse3.php");
    exit();
}

// Récupère les timbrages précédents de l'utilisateur connecté pour la journée actuelle
$queryPreviousTimbrages = "SELECT type_timbrage, heure_timbrage, date_timbrage FROM t_timbrage WHERE ID_personne = $user_id AND date_timbrage = '$today' ORDER BY position_timbrage ASC";
$resultPreviousTimbrages = $conn->query($queryPreviousTimbrages);

// Vérifie s'il y a des erreurs dans la requête SQL
if ($resultPreviousTimbrages === false) {
    die("Erreur dans la requête SQL : " . $conn->error);
}

// Compte le nombre de timbrages pour la journée
$queryCountTimbrages = "SELECT COUNT(*) as count FROM t_timbrage WHERE ID_personne = $user_id AND date_timbrage = '$today'";
$resultCountTimbrages = $conn->query($queryCountTimbrages);
if ($resultCountTimbrages === false) {
    die("Erreur dans la requête SQL : " . $conn->error);
}
$countTimbrages = $resultCountTimbrages->fetch_assoc()["count"];
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

    <a href="../../PHP/home/home.php" class="home-button">
        <img src="../../img/home.png" alt="home" class="imgbtn">
    </a>

    <main>
        <h1>Gestion des heures</h1>
        <p class="time" id="dateTime"></p>

        <ul id="logList">
            <?php
if (isset($resultPreviousTimbrages)) {
    while ($row = $resultPreviousTimbrages->fetch_assoc()) {
        $type = $row["type_timbrage"];
        $heure = date("H:i", strtotime($row["heure_timbrage"])); // Formatage de l'heure sans les secondes
        $date = date("d.m.Y", strtotime($row["date_timbrage"])); // Formatage de la date
        $label = ($type === 'in') ? 'Entrée' : 'Sortie';
        echo "<li class='$type'>$label le $date $heure</li>";
    }
}
            ?>
        </ul>

        <button id="toggleButton" <?php if ($countTimbrages >= 4) echo 'style="display:none;"'; ?>>Pause</button>
        <p>
            <a class="link-calendar" href="../../PHP/Calendar/Calendar.php">Entrez vos heures manuellement</a>
        </p>  
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var isPaused = false;
            var maxTimbrages = <?php echo $countTimbrages; ?>;

            function addLog(type) {
                if (maxTimbrages >= 4) {
                    return; // Ne rien faire si le nombre maximum de timbrages est atteint
                }
                var logList = document.getElementById('logList');
                var logEntry = document.createElement('li');
                var options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Europe/Zurich' };
                var timestamp = new Date().toLocaleString('fr-CH', options);

                insertIntoDatabase(type, timestamp);

                logEntry.textContent = (type === 'in' ? 'Entrée' : 'Sortie') + ' le ' + timestamp;
                logEntry.classList.add(type);
                logList.appendChild(logEntry);
                maxTimbrages++; // Incrémente le compteur de timbrages

                if (maxTimbrages >= 4) {
                    document.getElementById('toggleButton').style.display = 'none';
                }
            }

            function insertIntoDatabase(type, timestamp) {
                var url = '../../PHP/timbreuse/timbreuse.php';
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

            function updateUI() {
                var button = document.getElementById('toggleButton');

                if (isPaused) {
                    button.textContent = 'Start';
                    addLog('out');
                } else {
                    button.textContent = 'Pause';
                    addLog('in');
                }
            }

            document.getElementById('toggleButton').addEventListener('click', function () {
                isPaused = !isPaused;
                updateUI();
            });

            if (maxTimbrages >= 4) {
                document.getElementById('toggleButton').style.display = 'none';
            } else {
                updateUI();
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
