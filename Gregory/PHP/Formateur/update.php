<?php
// Démarre la session
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    // Redirige vers la page de connexion
    header("Location: ../../../index.html");
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

// Parse URL parameters to get date values
$date = isset($_GET['date']) ? $_GET['date'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';
// Initialisation des variables pour les valeurs existantes
$timbrages = [];

// Vérifier s'il existe déjà des enregistrements pour cet utilisateur et cette date
$check_query = "SELECT * FROM t_timbrage WHERE ID_personne = '$id' AND date_timbrage = '$date' ORDER BY position_timbrage ASC";
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
        $update_queries[] = "UPDATE t_timbrage SET heure_timbrage = '$debut', manière_timbrage = 'timbrage', type_location = 'bureau' WHERE id_personne = '$id' AND date_timbrage = '$date' AND position_timbrage = '1'";
    } else {
        $insert_queries[] = "('$id', '$date', '$debut', 'in', '1', 'timbrage')";
    }

    if (isset($timbrages[1])) {
        $update_queries[] = "UPDATE t_timbrage SET heure_timbrage = '$debut_midi', manière_timbrage = 'timbrage', type_location = 'bureau' WHERE id_personne = '$id' AND date_timbrage = '$date' AND position_timbrage = '2'";
    } else {
        $insert_queries[] = "('$id', '$date', '$debut_midi', 'out', '2', 'timbrage')";
    }

    if (isset($timbrages[2])) {
        $update_queries[] = "UPDATE t_timbrage SET heure_timbrage = '$fin_midi', manière_timbrage = 'timbrage', type_location = 'bureau' WHERE id_personne = '$id' AND date_timbrage = '$date' AND position_timbrage = '3'";
    } else {
        $insert_queries[] = "('$id', '$date', '$fin_midi', 'in', '3', 'timbrage')";
    }

    if (isset($timbrages[3])) {
        $update_queries[] = "UPDATE t_timbrage SET heure_timbrage = '$fin', manière_timbrage = 'timbrage', type_location = 'bureau' WHERE id_personne = '$id' AND date_timbrage = '$date' AND position_timbrage = '4'";
    } else {
        $insert_queries[] = "('$id', '$date', '$fin', 'out', '4', 'timbrage')";
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

    header("Location: ./viewdayFormateur.php?id_user=$id");
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
<a href="./HomeFormateur.php" class="home-button">
    <img src="../../img/home.png" alt="home" class="imgbtn">
</a>
<main>
    <h1>Gestion des heures</h1>
    <p class="time" id="dateTime"></p>
</main>
<div class="work-hours-form">
    <h2>Entrez les heures de travail du <?php echo $date; ?></h2>
    <form action="" method="post">
        <label for="debut">Début journée :</label>
        <input type="time" name="debut" id="debut" value="<?php echo $debut ?>" required><br>
        
        <label for="debut_midi">Début pause Midi :</label>
        <input type="time" name="debut_midi" id="debut_midi" value="<?php echo $debut_midi ?>" required><br>
        
        <label for="fin_midi">Fin pause Midi :</label>
        <input type="time" name="fin_midi" id="fin_midi" value="<?php echo $fin_midi ?>"  required><br>
        
        <label for="fin">Fin journée :</label>
        <input type="time" name="fin" id="fin" value="<?php echo $fin ?>" required><br>

        <p>
            <button type="submit">Enregistrer</button></p>
    </form>
</div>

<script>
        // Fonction de déconnexion
        function logout() {
            // Redirige vers la page de déconnexion
            window.location.href = "../LOGIN/logout.php";
        }
    </script>
<script src="../../JS/Calendar/homedate&heure.js"></script>
</body>
</html>
