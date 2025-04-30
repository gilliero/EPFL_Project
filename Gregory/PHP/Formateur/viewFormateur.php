<?php
// Démarre la session
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../index.html");
    exit();
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

// Récupération des utilisateurs qui ne sont pas formateurs
$sql_non_formateurs = "SELECT ID_personne, prenom_personne, nom_personne FROM t_personne WHERE role_personne != 'formateur'";
$result_non_formateurs = $conn->query($sql_non_formateurs);

// Récupération de l'utilisateur sélectionné
$selected_user_id = isset($_POST["selectedUser"]) ? $_POST["selectedUser"] : $_SESSION["user_id"];

// Enregistrer l'utilisateur sélectionné dans la session
$_SESSION["selected_user_id"] = $selected_user_id;

// Récupération des données des heures travaillées par semaine
$sql = "SELECT ID_personne, WEEK(date_timbrage) + 1 as semaine, YEAR(date_timbrage) as annee, 
        TIME_FORMAT(SEC_TO_TIME(ABS(SUM(IF(type_timbrage = 'out', -1, 1) * TIME_TO_SEC(heure_timbrage)))), '%H:%i') as heures_travaillees
        FROM t_timbrage
        WHERE ID_personne = $selected_user_id
        GROUP BY ID_personne, semaine, annee
        ORDER BY annee DESC, semaine DESC";

$result = $conn->query($sql);

// Tableau associatif pour traduire les mois en français
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
        <div class="dropdown">
            <p><?php echo $_SESSION["user_prenom"] . " " . $_SESSION["user_nom"] ?></p>
            <div class="dropdown-content">
                <button onclick="logout()">Déconnexion</button>
            </div>
        </div>
    </header>
    
    <a href="./HomeFormateur.php" class="home-button">
        <img src="../../img/home.png" alt="home" class="imgbtn">
    </a>
    <main>
        <h1>Visuel des heures</h1>
        <!-- Nouveau champ pour afficher les utilisateurs qui ne sont pas formateurs -->
        <div>
            <form method="POST" action="">
                <label for="userDropdown">Choisissez un utilisateur:</label><br>
                <select id="userDropdown" name="selectedUser">
                    <?php
                    if ($result_non_formateurs->num_rows > 0) {
                        while($row = $result_non_formateurs->fetch_assoc()) {
                            $user_full_name = $row["prenom_personne"] . " " . $row["nom_personne"];
                            $user_id = $row["ID_personne"];
                            $selected = $selected_user_id == $user_id ? "selected" : "";
                            echo "<option value='$user_id' $selected>$user_full_name</option>";
                        }
                    } else {
                        echo "<option value=''>Aucun utilisateur trouvé</option>";
                    }
                    ?>
                </select><br>
                <button type="submit">Sélectionner</button>

            </form>
        </div>

        <?php
        if ($result->num_rows > 0) {
            // Bouton pour exporter en PDF -->
            echo "<div class='export-button'>";
               echo "<button onclick='showModal()'>Exporter en PDF</button>";
           echo "</div>";
            echo "<div class='tableau centered-div'>";
            echo "<table>";
            echo "<tr><th>Semaine</th><th>Heures travaillées</th><th>Détail</th></tr>";
            while ($row = $result->fetch_assoc()) {
                $semaine = $row["semaine"];
                $annee = $row["annee"];
                $heures_travaillees = $row["heures_travaillees"];
                $id_SelectedUser = $row["ID_personne"];

                // Premier jour de la semaine
                $date_semaine_debut = new DateTime();
                $date_semaine_debut->setISODate($annee, $semaine, 1);
                $date_semaine_debut_format = $mois_francais[$date_semaine_debut->format("F")] . " " . $date_semaine_debut->format("d");

                // Vendredi de la semaine
                $date_semaine_fin = new DateTime();
                $date_semaine_fin->setISODate($annee, $semaine, 5);
                $date_semaine_fin_format = $mois_francais[$date_semaine_fin->format("F")] . " " . $date_semaine_fin->format("d");

                echo "<tr>";
                echo "<td>Semaine du $date_semaine_debut_format au $date_semaine_fin_format $annee</td>";
                echo "<td>$heures_travaillees</td>";
                echo "<td><a href=\"./viewdayFormateur.php?semaine=$semaine&annee=$annee&id_user=$id_SelectedUser\">View</a></td>";
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
            window.location.href = "./exportPDFmensuel.php?user_id=<?php echo $selected_user_id; ?>&mois=" + month + "&annee=" + year;
            closeModal(); // Ferme la popup après la redirection
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
