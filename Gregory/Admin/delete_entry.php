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
$id = $_GET['id'] ?? null;
$primaryKey = null;

// Trouver la clé primaire
$result = $conn->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
if ($row = $result->fetch_assoc()) {
    $primaryKey = $row['Column_name'];
}

if (!$table || !$primaryKey) {
    die("Table ou clé primaire manquante.");
}

// Suppression confirmée
if ($id && $_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $conn->prepare("DELETE FROM `$table` WHERE `$primaryKey` = ?");
    $stmt->bind_param("s", $id);
    if ($stmt->execute()) {
        header("Location: DB.php");
        exit();
    } else {
        echo "Erreur lors de la suppression : " . $conn->error;
    }
}

// Si id est présent → afficher confirmation
if ($id) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
    <link rel="stylesheet" href="./admin.css">
        <meta charset="UTF-8">
        <title>Supprimer un enregistrement</title>
    </head>
    <body>
    <header>
    <img src="../img/epfllogo.png" alt="EPFL Logo">
        <!-- Utilisation du dropdown -->
        <div class="dropdown">
            <p><?php echo $_SESSION["user_prenom"] . " " . $_SESSION["user_nom"] ?></p>
            <div class="dropdown-content">
                <!-- Bouton de déconnexion -->
                <button onclick="logout()">Déconnexion</button>
            </div>
        </div>
    </header>
    
    <a href="./Adminhome.php" class="home-button">
        <img src="../img/home.png" alt="home" class="imgbtn">
    </a>
        <h1>Confirmation de suppression</h1>
        <p>Es-tu sûr de vouloir supprimer l’entrée <strong><?= htmlspecialchars($id) ?></strong> de la table <strong><?= htmlspecialchars($table) ?></strong> ?</p>
        <form method="POST">
            <button type="submit">✅ Oui, supprimer</button>
            <a href="delete_entry.php?table=<?= urlencode($table) ?>"><button type="button">❌ Annuler</button></a>
        </form>
    </body>
    </html>
    <?php
    exit();
}

// Sinon → afficher toutes les lignes avec un bouton "🗑️ Supprimer"
$data = [];
$columns = [];

$result = $conn->query("SELECT * FROM `$table`");
if ($result) {
    $columns = array_keys($result->fetch_assoc());
    $result->data_seek(0);
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Supprimer une entrée</title>
</head>
<body>
    <h1>Supprimer une entrée dans <?= htmlspecialchars($table) ?></h1>

    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <?php foreach ($columns as $col): ?>
                    <th><?= htmlspecialchars($col) ?></th>
                <?php endforeach; ?>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <?php foreach ($columns as $col): ?>
                        <td><?= htmlspecialchars($row[$col]) ?></td>
                    <?php endforeach; ?>
                    <td>
                        <a href="delete_entry.php?table=<?= urlencode($table) ?>&id=<?= urlencode($row[$primaryKey]) ?>">
                            🗑️ Supprimer
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <script>
        // Fonction de déconnexion
        function logout() {
            // Redirige vers la page de déconnexion
            window.location.href = "../PHP/LOGIN/logout.php";
        }
    </script>
</body>
</html>
