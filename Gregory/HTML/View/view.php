<?php
// Démarre la session
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    // Redirige vers la page de connexion
    header("Location: ../../index.html");
    exit(); // Assure que le script s'arrête après la redirection
}

// Connexion à la base de données
$servername = "db-ic.epfl.ch";
$username = "icit_ictrip_adm";
$password = "GdMrL0pZFGKnV8hyntQjFeKKmAbSSQRK";
$dbname = "icit_ictrip";

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
}

// Récupération des données des heures travaillées par semaine
$sql = "SELECT ID_personne, WEEK(date_timbrage) + 1 as semaine, YEAR(date_timbrage) as annee, 
        SEC_TO_TIME(ABS(SUM(IF(type_timbrage = 'out', -1, 1) * TIME_TO_SEC(heure_timbrage)))) as heures_travaillees
        FROM t_timbrage
        WHERE ID_personne = {$_SESSION['user_id']}
        GROUP BY ID_personne, semaine, annee
        ORDER BY annee DESC, semaine DESC"; // Ajout de la clause ORDER BY

$result = $conn->query($sql);

// Tableau des mois en français
$mois_francais = [
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
];

// Mois et année actuels
$current_month = date('F');
$current_year = date('Y');

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EPFL - Visuel des heures</title>
    <link rel="stylesheet" href="../../CSS/view/view.css">
    <style>
        /* Styles pour la popup modale */
        .modal {
            display: none; /* Par défaut, la popup est cachée */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4); /* Fond semi-transparent */
            overflow: auto;
            border-radius: 10px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            text-align: center;
            border-radius: 10px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            border-radius: 10px;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
            border-radius: 10px;
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
        <h1>Visuel des heures</h1>
        <!-- Bouton pour exporter en PDF -->
        <div class="export-button">
            <button onclick="showModal()">Exporter en PDF</button>
        </div>
        <?php
        if ($result->num_rows > 0) {
            echo "<div class='tableau centered-div'>";
            echo "<table>";
            echo "<tr><th>Semaine</th><th>Heures travaillées</th><th>Détaille</th></tr>";
            while ($row = $result->fetch_assoc()) {
                $semaine = $row["semaine"];
                $annee = $row["annee"];
                $heures_travaillees = substr($row["heures_travaillees"], 0, -3); // Enlève les secondes
                
                // Premier jour de la semaine
                $date_semaine_debut = new DateTime();
                $date_semaine_debut->setISODate($annee, $semaine, 1); // Le troisième paramètre est le jour de la semaine (1 pour lundi)
                $date_semaine_debut_format = $date_semaine_debut->format("d F"); // Formatage du jour et du mois

                // Vendredi de la semaine
                $date_semaine_fin = new DateTime();
                $date_semaine_fin->setISODate($annee, $semaine, 5); // Le vendredi est le cinquième jour de la semaine
                $date_semaine_fin_format = $date_semaine_fin->format("d F"); // Formatage du jour et du mois

                // Vérifier si le mois du premier jour est différent du mois du vendredi
                $mois_debut = $date_semaine_debut->format("F");
                $mois_fin = $date_semaine_fin->format("F");
                
                // Si les mois sont différents, ajoutez le mois pour le premier jour
                if ($mois_debut !== $mois_fin) {
                    $date_semaine_debut_format = $date_semaine_debut->format("d") . " " . $mois_francais[$mois_debut];
                } else {
                    $date_semaine_debut_format = $date_semaine_debut->format("d");
                }

                // Convertir le mois de fin en français
                $date_semaine_fin_format = $date_semaine_fin->format("d") . " " . $mois_francais[$mois_fin];

                echo "<tr>";
                echo "<td>Semaine du $date_semaine_debut_format au $date_semaine_fin_format $annee</td>";
                echo "<td>$heures_travaillees</td>";
                echo "<td><a href=\"./viewday.php?semaine=$semaine&annee=$annee\">Voir détails</a></td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
        } else {
            echo "<p>Aucune donnée disponible.</p>";
        }
        ?>
    </main>
    <!-- Popup modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Sélectionnez le mois et l'année</h2>
            <form id="exportForm">
                <label for="month">Mois :</label>
                <select id="month" name="month">
                    <?php
                    foreach ($mois_francais as $num => $mois) {
                        $selected = ($num == $current_month) ? "selected" : "";
                        echo "<option value=\"$num\" $selected>$mois</option>";
                    }
                    ?>
                </select>
                <br><br>
                <label for="year">Année :</label>
                <select id="year" name="year">
                    <?php
                    $current_year = date('Y');
                    for ($year = 2024; $year <= $current_year; $year++) {
                        $selected = ($year == $current_year) ? "selected" : "";
                        echo "<option value=\"$year\" $selected>$year</option>";
                    }
                    ?>
                </select>
                <br><br>
                <button type="button" onclick="exportPDF()">Valider</button>
            </form>
        </div>
    </div>

    <script>
        // Fonction de déconnexion
        function logout() {
            // Redirige vers la page de déconnexion
            window.location.href = "../../PHP/LOGIN/logout.php";
        }

        // Fonction pour afficher la popup
        function showModal() {
            var modal = document.getElementById("myModal");
            if (modal) {
                modal.style.display = "block";
            } else {
                console.error("Element with ID 'myModal' not found.");
            }
        }

        // Fonction pour fermer la popup
        function closeModal() {
            var modal = document.getElementById("myModal");
            if (modal) {
                modal.style.display = "none";
            }
        }

        // Fonction pour exporter en PDF avec les paramètres sélectionnés
        function exportPDF() {
            var month = document.getElementById("month").value;
            var year = document.getElementById("year").value;
            window.location.href = "./exportPDFmensuel.php?user_id=<?php echo $_SESSION['user_id']; ?>&mois=" + month + "&annee=" + year;
            closeModal(); // Ferme la popup après la redirection
        }
    </script>
</body>
</html>

<?php
// Fermeture de la connexion à la base de données
$conn->close();
?>
