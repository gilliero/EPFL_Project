<?php
session_start(); // Démarrage de la session

$servername = "db-ic.epfl.ch";
$username = "icit_ictrip_adm";
$password = "GdMrL0pZFGKnV8hyntQjFeKKmAbSSQRK";
$dbname = "icit_ictrip";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("La connexion à la base de données a échoué : " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_user"])) {
    $prenom = $_POST["prenom"];
    $nom = $_POST["nom"];
    $username = $_POST["username"];
    $formation = $_POST["formation"];
    $year = $_POST["year"];
    $password = $_POST["password"];

    // Hachage du mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Préparation de la requête SQL pour éviter les injections SQL
    $stmt = $conn->prepare("INSERT INTO utilisateurs (prenom, nom, username, formation, year, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $prenom, $nom, $username, $formation, $year, $hashed_password);

    if ($stmt->execute()) {
        // Définir les variables de session pour l'utilisateur nouvellement ajouté
        $_SESSION["username"] = $username;
        $_SESSION["user_role"] = 'apprenti'; // Remplacez 'apprenti' par le rôle approprié si nécessaire
        $_SESSION["user_id"] = $stmt->insert_id; // Récupérer l'ID de l'utilisateur ajouté

        // Redirection en fonction du rôle
        if ($_SESSION["user_role"] === "apprenti") {
            header("Location: addNotes.php");
        } else {
            header("Location: viewNotes.php");
        }
        exit(); // Assurez-vous de quitter le script après la redirection
    } else {
        echo "Erreur lors de l'ajout de l'utilisateur : " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="createuser.css">
    <title>Création de l'utilisateur</title>
    <script>
        function updateYearOptions() {
            const formationSelect = document.getElementById('formation');
            const yearSelect = document.getElementById('year');
            const selectedFormation = formationSelect.value;

            while (yearSelect.options.length > 0) {
                yearSelect.remove(0);
            }

            if (selectedFormation === 'informaticien') {
                for (let i = 1; i <= 4; i++) {
                    let option = new Option(i, i);
                    yearSelect.add(option);
                }
            } else if (selectedFormation === 'operateur') {
                for (let i = 1; i <= 3; i++) {
                    let option = new Option(i, i);
                    yearSelect.add(option);
                }
            }
        }

        window.onload = function() {
            document.getElementById('formation').addEventListener('change', updateYearOptions);
            updateYearOptions();
        }
    </script>
</head>
<body>
<header>
        <img src="../Gregory/img/epfllogo.png" alt="">
        <div class="title-container">
            <h1>GESTION DES NOTH</h1>
        </div>
    </header>
    <!-- Formulaire pour ajouter un utilisateur -->
    <form method="post" action="">
        <label for="prenom">Prénom :</label>
        <input type="text" name="prenom" id="prenom" required><br>

        <label for="nom">Nom :</label>
        <input type="text" name="nom" id="nom" required><br>

        <label for="username">Nom d'utilisateur :</label>
        <input type="text" name="username" id="username" required><br>

        <label for="formation">Voie de formation :</label>
        <select name="formation" id="formation" required>
            <option value="operateur">Opérateur en informatique</option>
            <option value="informaticien">Informaticien</option>
        </select><br>

        <label for="year">Année d'apprentissage :</label>
        <select name="year" id="year" required>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
        </select><br>

        <label for="password">Mot de passe :</label>
        <input type="password" name="password" id="password" required><br>

        <!-- Lignes commentées pour le rôle -->
        <!-- <label for="role">Rôle :</label>
        <select name="role" required>
            <option value="apprenti">Apprenti</option>
            <option value="formateur">Formateur</option>
        </select><br> -->

        <input type="submit" name="add_user" value="Ajouter Utilisateur">
        <a href="index.php"><button type="button">Revenir à la page de connexion</button></a>
    </form>
</body>
</html>
