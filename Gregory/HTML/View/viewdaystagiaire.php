<?php
// Démarre la session
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    // Redirige vers la page de connexion
    header("Location: ../../index.html");
    exit(); // Assure que le script s'arrête après la redirection
}

// Connexion à la base de données (à adapter selon votre configuration)
$servername = "db-ic.epfl.ch";
$username = "icit_ictrip_adm";
$password = "GdMrL0pZFGKnV8hyntQjFeKKmAbSSQRK";
$dbname = "icit_ictrip";

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Tableau pour traduire les mois en français
$mois_francais = array(
    "January" => "Janvier",
    "February" => "Février",
    "March" => "Mars",
    "April" => "Avril",
    "May" => "Mai",
    "June" => "Juin",
    "July" => "Juillet",
    "August" => "Août",
    "September" => "Septembre",
    "October" => "Octobre",
    "November" => "Novembre",
    "December" => "Décembre"
);

function traduireDateEnFrancais($date) {
    global $mois_francais;
    $english_month = date("F", strtotime($date));
    $french_month = $mois_francais[$english_month];
    return str_replace($english_month, $french_month, date("d F Y", strtotime($date)));
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualisation des heures de travail</title>
    <link rel="stylesheet" href="../../CSS/view/viewday.css">
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
        <h1>Visuel des heures de travail</h1>
        <p class="time" id="dateTime"></p>
        
        <?php
        // Récupérer la semaine et l'année depuis le lien
        $semaine = isset($_GET['semaine']) ? intval($_GET['semaine']) : date('W');
        $annee = isset($_GET['annee']) ? intval($_GET['annee']) : date('Y');
        
        // Définir les dates des jours de la semaine
        $jours_semaine = array();
        for ($i = 1; $i <= 5; $i++) {
            $date = new DateTime();
            $date->setISODate($annee, $semaine, $i);
            $jours_semaine[] = array(
                'format' => traduireDateEnFrancais($date->format("Y-m-d")),
                'date' => $date->format("Y-m-d")
            );
        }

        // Afficher le numéro de la semaine et l'année
        echo "<p>Semaine du " . $jours_semaine[0]['format'] . " au " . $jours_semaine[4]['format'] . " $annee</p>";

        ?>
        <div class="export-button">
        <button onclick="exportPDF()">Exporter en PDF</button>
        </div>
<?php
        // Initialisation des tableaux pour stocker les timbrages et localisations
        $timbrages_par_jour = array();
        $localisations_par_jour = array();

        foreach ($jours_semaine as $jour) {
            $timbrages_par_jour[$jour['date']] = array();
            $localisations_par_jour[$jour['date']] = array();
        }

        // Exécution de la requête SQL pour récupérer les données pour chaque jour de la semaine
        $user_id = $_SESSION["user_id"];
        $dates = implode("','", array_column($jours_semaine, 'date'));
        $sql = "SELECT date_timbrage, heure_timbrage, type_location, type_timbrage, manière_timbrage
                FROM t_timbrage 
                WHERE id_personne = $user_id AND date_timbrage IN ('$dates') 
                ORDER BY date_timbrage, heure_timbrage";
        $result = $conn->query($sql);

        // Remplir les tableaux avec les données récupérées
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $date = $row["date_timbrage"];
                $heure = $row["heure_timbrage"];
                $localisation = $row["type_location"];
                $type = $row["type_timbrage"];
                $manière_timbrage = $row["manière_timbrage"];
                $timbrages_par_jour[$date][] = array("heure" => $heure, "type" => $type);
                $localisations_par_jour[$date][] = $localisation;
                $manière_timbrage_par_jour[$date][] = $manière_timbrage;

            }
        }

        // Afficher le tableau
        echo "<div class=\"tableau centered-div\">";
        echo "<table>";
        echo "<tr><th>Date</th><th>Heures Timbrées</th><th>Temps travaillé</th><th>Localisation</th><th>Manière de timbrage</th></tr>";

        foreach ($jours_semaine as $jour) {
            $date = $jour['format'];
            $date_brute = $jour['date'];
            $timbrages = $timbrages_par_jour[$date_brute];
            $localisations = $localisations_par_jour[$date_brute];

            echo "<tr>";
            echo "<td>$date</td>";
            echo "<td>";
            if (!empty($timbrages)) {
                usort($timbrages, function($a, $b) {
                    return strtotime($a['heure']) - strtotime($b['heure']);
                });
                foreach ($timbrages as $timbrage) {
                    $heure_sans_secondes = date("H:i", strtotime($timbrage['heure'])); // Enlève les secondes
                    echo $heure_sans_secondes . " (" . $timbrage['type'] . ")<br>";
                }
            } else {
                echo "Aucun timbrage";
            }
            echo "</td>";
            echo "<td>";
            // Calculer le temps travaillé
            if (!empty($timbrages)) {
                $temps_travaille = 0;
                $heure_in = null;
                foreach ($timbrages as $timbrage) {
                    if ($timbrage['type'] == 'in') {
                        $heure_in = strtotime($timbrage['heure']);
                    } elseif ($timbrage['type'] == 'out' && $heure_in !== null) {
                        $heure_out = strtotime($timbrage['heure']);
                        $temps_travaille += $heure_out - $heure_in;
                        $heure_in = null;
                    }
                }
                echo gmdate("H:i", $temps_travaille); // Affiche le temps travaillé au format heures:minutes
            } else {
                echo "0:00";
            }
            echo "</td>";
            // Afficher les localisations correspondantes
            echo "<td>";
            if (!empty($localisations)) {
                echo implode(", ", array_unique($localisations));
            } else {
                echo "Aucune localisation";
            }
            echo "</td>";
            // Afficher la manière de timbrage
            echo "<td>";
            if (!empty($manière_timbrage_par_jour[$date_brute])) {
                echo implode(", ", array_unique($manière_timbrage_par_jour[$date_brute]));
            } else {
                echo "Aucune mode de timbrage";
            }
            echo "</td>";            
            echo "</tr>";
        }

        echo "</table>";
        echo "</div>";
        ?>
    </main>
    <script>
        // Fonction de déconnexion
        function logout() {
            // Redirige vers la page de déconnexion
            window.location.href = "../../PHP/LOGIN/logout.php";
        }

         // Fonction pour exporter en PDF
         function exportPDF() {
            // Redirige vers exportPDF.php avec l'ID de l'utilisateur
            window.location.href = "./exportPDFhebdo.php?user_id=<?php echo $_SESSION['user_id']; ?>&semaine=<?php echo $semaine;?>&annee=<?php echo $annee;?>";
        }
    </script>
</body>
</html>
