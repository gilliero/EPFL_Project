<?php
// Démarre la session
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    // Redirige vers la page de connexion
    header("Location: ../../index.html");
    exit(); // Assure que le script s'arrête après la redirection
}

// Variables de connexion à la base de données
$serveurNom = "db-ic.epfl.ch";
$nomUtilisateur = "icit_ictrip_adm";
$motDePasse = "GdMrL0pZFGKnV8hyntQjFeKKmAbSSQRK";
$nomBaseDeDonnees = "icit_ictrip";

// Connexion à la base de données
$connexion = new mysqli($serveurNom, $nomUtilisateur, $motDePasse, $nomBaseDeDonnees);

// Vérifie la connexion à la base de données
if ($connexion->connect_error) {
    die("La connexion à la base de données a échoué : " . $connexion->connect_error);
}

// Parse URL parameters to get day, month, and year values
$day = isset($_GET['day']) ? $_GET['day'] : '';
$month = isset($_GET['month']) ? $_GET['month'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Format de la date au format YYYY-mm-dd
$formattedDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
$formatedDateFR = str_pad($day, 2, '0', STR_PAD_LEFT) . '.' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.' . $year;

// Initialisation des variables pour les valeurs existantes
$id = $_SESSION["user_id"];
$timbrages = [];

// Vérifier s'il existe déjà des enregistrements pour cet utilisateur et cette date
$check_query = "SELECT * FROM t_timbrage WHERE ID_personne = '$id' AND date_timbrage = '$formattedDate' ORDER BY position_timbrage ASC";
$result = $connexion->query($check_query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $timbrages[] = $row;
    }
}

// Préparation des variables pour les mises à jour/insertion
$debut = isset($timbrages[0]) ? $timbrages[0]['heure_timbrage'] : '';
$debut_midi = isset($timbrages[1]) ? $timbrages[1]['heure_timbrage'] : '';
$fin_midi = isset($timbrages[2]) ? $timbrages[2]['heure_timbrage'] : '';
$fin = isset($timbrages[3]) ? $timbrages[3]['heure_timbrage'] : '';

$debut_maniere = isset($timbrages[0]) ? $timbrages[0]['manière_timbrage'] : '';
$debut_midi_maniere = isset($timbrages[1]) ? $timbrages[1]['manière_timbrage'] : '';
$fin_midi_maniere = isset($timbrages[2]) ? $timbrages[2]['manière_timbrage'] : '';
$fin_maniere = isset($timbrages[3]) ? $timbrages[3]['manière_timbrage'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des valeurs du formulaire
    $debut = $_POST["debut"];
    $debut_midi = $_POST["debut_midi"];
    $fin_midi = $_POST["fin_midi"];
    $fin = $_POST["fin"];

    $update_queries = [];
    $insert_queries = [];

    if (isset($timbrages[0])) {
        $update_queries[] = "UPDATE t_timbrage SET heure_timbrage = '$debut', manière_timbrage = 'manuel', type_location = 'bureau' WHERE id_personne = '$id' AND date_timbrage = '$formattedDate' AND position_timbrage = '1' AND manière_timbrage != 'timbrage'";
    } else {
        $insert_queries[] = "('$id', '$formattedDate', '$debut', 'in', '1', 'manuel')";
    }

    if (isset($timbrages[1])) {
        $update_queries[] = "UPDATE t_timbrage SET heure_timbrage = '$debut_midi', manière_timbrage = 'manuel', type_location = 'bureau' WHERE id_personne = '$id' AND date_timbrage = '$formattedDate' AND position_timbrage = '2' AND manière_timbrage != 'timbrage'";
    } else {
        $insert_queries[] = "('$id', '$formattedDate', '$debut_midi', 'out', '2', 'manuel')";
    }

    if (isset($timbrages[2])) {
        $update_queries[] = "UPDATE t_timbrage SET heure_timbrage = '$fin_midi', manière_timbrage = 'manuel', type_location = 'bureau' WHERE id_personne = '$id' AND date_timbrage = '$formattedDate' AND position_timbrage = '3' AND manière_timbrage != 'timbrage'";
    } else {
        $insert_queries[] = "('$id', '$formattedDate', '$fin_midi', 'in', '3', 'manuel')";
    }

    if (isset($timbrages[3])) {
        $update_queries[] = "UPDATE t_timbrage SET heure_timbrage = '$fin', manière_timbrage = 'manuel', type_location = 'bureau' WHERE id_personne = '$id' AND date_timbrage = '$formattedDate' AND position_timbrage = '4' AND manière_timbrage != 'timbrage'";
    } else {
        $insert_queries[] = "('$id', '$formattedDate', '$fin', 'out', '4', 'manuel')";
    }

    // Exécution des mises à jour
    foreach ($update_queries as $update_query) {
        if ($connexion->query($update_query) === false) {
            echo "Erreur lors de la mise à jour : " . $connexion->error;
        }
    }

    // Exécution des insertions
    if (!empty($insert_queries)) {
        $insert_query = "INSERT INTO t_timbrage (ID_personne, date_timbrage, heure_timbrage, type_timbrage, position_timbrage, manière_timbrage) VALUES " . implode(", ", $insert_queries);
        if ($connexion->query($insert_query) === false) {
            echo "Erreur lors de l'insertion : " . $connexion->error;
        }
    }

    header("Location: ../Calendar/Calendarstagiaire.php");
    exit();
}

// Fermeture de la connexion à la base de données
$connexion->close();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EPFL - Gestion des heures</title>
<link rel="stylesheet" href="../../CSS/Heure/Heure.css">
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
</main>
<div class="work-hours-form">
    <h2>Entrez les heures de travail du <?php echo $formatedDateFR; ?></h2>
    
    <form action="" method="post">
        <label for="debut">Début journée :</label>
        <input type="time" name="debut" id="debut" value="<?php echo $debut ?>" <?php echo ($debut_maniere == 'timbrage') ? 'disabled' : '' ?>><br>
        
        <label for="debut_midi">Début pause Midi :</label>
        <input type="time" name="debut_midi" id="debut_midi" value="<?php echo $debut_midi ?>" <?php echo ($debut_midi_maniere == 'timbrage') ? 'disabled' : '' ?>><br>
        
        <label for="fin_midi">Fin pause Midi :</label>
        <input type="time" name="fin_midi" id="fin_midi" value="<?php echo $fin_midi ?>" <?php echo ($fin_midi_maniere == 'timbrage') ? 'disabled' : '' ?>><br>
        
        <label for="fin">Fin journée :</label>
        <input type="time" name="fin" id="fin" value="<?php echo $fin ?>" <?php echo ($fin_maniere == 'timbrage') ? 'disabled' : '' ?>><br>

        <p>
            <button type="submit">Enregistrer</button>
            <button type="button" id="horscampus-btn" onclick="horsCampus()">HORS CAMPUS</button></p>
            </form>
           <p> <button type="button" id="horscampus-btn" onclick="demihorsCampus()">½day HORS CAMPUS</button></p>

</div>

<script>
    // Fonction pour fermer la popup
    function closePopup() {
        var popup = document.querySelector('.popup');
        if (popup) {
            popup.parentNode.removeChild(popup);
        }
    }

    function horsCampus() {
        // Crée une boîte de dialogue modale
        var popup = document.createElement('div');
        popup.classList.add('popup');

        // Ajoute des boutons radio à la boîte de dialogue modale
        popup.innerHTML = `
            <span class="close-btn" onclick="closePopup()">×</span>
            <h3>Sélectionnez votre localisation</h3>
            <input type="radio" id="CoursProfessionel" name="hors_campus" value="Cours Professionel">
            <label for="CoursProfessionel">Cours Professionel</label><br>
            <input type="radio" id="CoursInterentreprise" name="hors_campus" value="Cours Interentreprise">
            <label for="CoursInterentreprise">Cours Interentreprise</label><br>
            <input type="radio" id="Vacances" name="hors_campus" value="Vacances">
            <label for="Vacances">Vacances</label><br>
            <input type="radio" id="Malade" name="hors_campus" value="Malade">
            <label for="Malade">Malade</label><br>
            <button onclick="validateSelection()">Valider</button>
        `;

        // Ajoute la boîte de dialogue modale à la page
        document.body.appendChild(popup);
    }

    function validateSelection() {
        // Récupère la valeur sélectionnée
        var selectedOption = document.querySelector('input[name="hors_campus"]:checked').value;

        // Récupérer les valeurs de date du formulaire
        var day = '<?php echo $day; ?>';
        var month = '<?php echo $month; ?>';
        var year = '<?php echo $year; ?>';

        // Construire l'URL avec les paramètres de date et la valeur sélectionnée
        var url = 'horscampus.php?day=' + day + '&month=' + month + '&year=' + year + '&option=' + encodeURIComponent(selectedOption);

        // Rediriger vers la page horscampus.php avec les paramètres de date et la valeur sélectionnée
        window.location.href = url;
    }

    var selectedMoment; // Variable pour stocker le moment de l'absence

function demihorsCampus() {
    // Crée une boîte de dialogue modale
    var popup = document.createElement('div');
    popup.classList.add('popup');

    // Ajoute des boutons radio pour sélectionner le moment de l'absence
    popup.innerHTML = `
        <span class="close-btn" onclick="closePopup()">×</span>
        <h3>Sélectionnez le moment de l'absence</h3>
        <input type="radio" id="matin" name="moment_absence" value="Matin">
        <label for="matin">Matin</label><br>
        <input type="radio" id="apresmidi" name="moment_absence" value="Après-midi">
        <label for="apresmidi">Après-midi</label><br>
        <button onclick="saveMomentAndClose()">Suivant</button>
    `;

    // Ajoute la boîte de dialogue modale à la page
    document.body.appendChild(popup);
}

function saveMomentAndClose() {
    // Récupère le moment de l'absence sélectionné
    selectedMoment = document.querySelector('input[name="moment_absence"]:checked').value;
    
    // Ferme la pop-up
    closePopup();

    // Affiche la pop-up pour demander la localisation
    askLocation();
}

function askLocation() {
    // Crée une boîte de dialogue modale pour demander la localisation
    var popup = document.createElement('div');
    popup.classList.add('popup');

    // Ajoute un champ de saisie pour la localisation
    popup.innerHTML = `
    <span class="close-btn" onclick="closePopup()">×</span>
            <h3>Sélectionnez votre localisation</h3>
            <input type="radio" id="CoursProfessionel" name="hors_campus" value="Cours Professionel">
            <label for="CoursProfessionel">Cours Professionel</label><br>
            <input type="radio" id="CoursInterentreprise" name="hors_campus" value="Cours Interentreprise">
            <label for="CoursInterentreprise">Cours Interentreprise</label><br>
            <input type="radio" id="Vacances" name="hors_campus" value="Vacances">
            <label for="Vacances">Vacances</label><br>
            <input type="radio" id="Malade" name="hors_campus" value="Malade">
            <label for="Malade">Malade</label><br>
            <button onclick="validateSelection2()">Valider</button>
    `;

    // Ajoute la boîte de dialogue modale à la page
    document.body.appendChild(popup);
    }

    function validateSelection2() {
        // Récupère la valeur sélectionnée
        var selectedOption = document.querySelector('input[name="hors_campus"]:checked').value;

        // Récupérer les valeurs de date du formulaire
        var day = '<?php echo $day; ?>';
        var month = '<?php echo $month; ?>';
        var year = '<?php echo $year; ?>';

        // Construire l'URL avec les paramètres de date et la valeur sélectionnée
        var url = 'demihorscampus.php?day=' + day + '&month=' + month + '&year=' + year + '&selectedMoment=' + selectedMoment + '&option=' + encodeURIComponent(selectedOption);

        // Rediriger vers la page horscampus.php avec les paramètres de date et la valeur sélectionnée
        window.location.href = url;
    }

function closePopup() {
    // Supprime la boîte de dialogue modale
    var popup = document.querySelector('.popup');
    popup.parentNode.removeChild(popup);
}


</script>

<script>
        // Fonction de déconnexion
        function logout() {
            // Redirige vers la page de déconnexion
            window.location.href = "../LOGIN/logout.php";
        }
    </script>

<script>
    // Affichage de la pop-up d'erreur
    window.onload = function() {
        var error = "<?php echo $error; ?>";
        if (error === "true") {
            alert("Erreur : L'une des valeurs ne peut pas être modifée, utilisé la fonction demi hors campus. Sinon contacté le formateur pour demander une modification.");
        }
    }
</script>

<script src="../../JS/Calendar/homedate&heure.js"></script>
</body>
</html>
