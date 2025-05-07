<?php
// Connexion à la base de données
$servername = "db-ic.epfl.ch";
$username = "icit_ictrip_adm";
$password = "GdMrL0pZFGKnV8hyntQjFeKKmAbSSQRK";
$dbname = "icit_ictrip";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifie la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Démarrage session
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../../index.html");
    exit();
}

// Tables disponibles
$tables = [
    "branches",
    "notes",
    "t_personne",
    "t_timbrage"
];

// Si formulaire soumis
$data = [];
$columns = [];
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["table"]) && in_array($_POST["table"], $tables)) {
    $selectedTable = $_POST["table"];
    $result = $conn->query("SELECT * FROM `$selectedTable`");

    if ($result && $result->num_rows > 0) {
        $columns = array_keys($result->fetch_assoc());
        $result->data_seek(0); // Revenir au début
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    } elseif ($result) {
        $columns = []; // Table vide
    } else {
        $error = "Erreur lors de la récupération des données.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<link rel="stylesheet" href="./admin.css">
    <meta charset="UTF-8">
    <title>Gestion DB</title>
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
    <h1>Gestion DB</h1>

    <form method="POST">
        <label for="table">Choisir une table :</label>
        <select id="table" name="table" required>
            <option value="">-- Sélectionner --</option>
            <?php foreach ($tables as $table): ?>
                <option value="<?= $table ?>" <?= (isset($selectedTable) && $selectedTable === $table) ? "selected" : "" ?>>
                    <?= $table ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Soumettre</button>
    </form>

    <?php if (!empty($columns)): ?>
        <h2>Contenu de la table : <?= htmlspecialchars($selectedTable) ?></h2>

        <div class="action-buttons">
    <a href="add_entry.php?table=<?= urlencode($selectedTable) ?>"><button type="button">➕ Ajouter</button></a>
    <a href="edit_entry.php?table=<?= urlencode($selectedTable) ?>"><button type="button">✏️ Modifier</button></a>
    <a href="delete_entry.php?table=<?= urlencode($selectedTable) ?>"><button type="button">🗑️ Supprimer</button></a>
</div>

        <table>
            <thead>
                <tr>
                    <?php foreach ($columns as $col): ?>
                        <th><?= htmlspecialchars($col) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <td><?= htmlspecialchars($row[$col]) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
        <p>Aucune donnée trouvée dans la table sélectionnée.</p>
    <?php endif; ?>
    <script>
        // Fonction de déconnexion
        function logout() {
            // Redirige vers la page de déconnexion
            window.location.href = "../PHP/LOGIN/logout.php";
        }
    </script>
</body>
</html>
