<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../index.html");
    exit();
}

$servername = "db-ic.epfl.ch";
$username = "icit_ictrip_adm";
$password = "GdMrL0pZFGKnV8hyntQjFeKKmAbSSQRK";
$dbname = "icit_ictrip";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$table = $_GET['table'] ?? '';
if (!$table) {
    die("Table manquante.");
}

$columns = [];
$result = $conn->query("DESCRIBE `$table`");
while ($row = $result->fetch_assoc()) {
    if (strtolower($row['Extra']) !== 'auto_increment') {
        $columns[] = $row['Field'];
    }
}

// Si formulaire soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fields = [];
    $values = [];

    foreach ($columns as $col) {
        $fields[] = "`$col`";
        $values[] = "'" . $conn->real_escape_string($_POST[$col]) . "'";
    }

    $sql = "INSERT INTO `$table` (" . implode(",", $fields) . ") VALUES (" . implode(",", $values) . ")";
    if ($conn->query($sql)) {
        header("Location: DB.php");
        exit();
    } else {
        echo "Erreur lors de l'ajout : " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un enregistrement</title>
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
    <h1>Ajouter dans <?= htmlspecialchars($table) ?></h1>
    <form method="POST">
        <?php foreach ($columns as $col): ?>
            <label><?= htmlspecialchars($col) ?> :</label>
            <input type="text" name="<?= htmlspecialchars($col) ?>" required><br><br>
        <?php endforeach; ?>
        <button type="submit">Enregistrer</button>
    </form>
</body>
</html>
