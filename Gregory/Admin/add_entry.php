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
