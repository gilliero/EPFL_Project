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

// Si l'ID est passé → Afficher formulaire d'édition
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM `$table` WHERE `$primaryKey` = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rowData = $result->fetch_assoc();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $updates = [];
        foreach ($rowData as $col => $val) {
            if ($col === $primaryKey) continue;
            $newVal = $conn->real_escape_string($_POST[$col]);
            $updates[] = "`$col` = '$newVal'";
        }
        $sql = "UPDATE `$table` SET " . implode(", ", $updates) . " WHERE `$primaryKey` = '$id'";
        if ($conn->query($sql)) {
            header("Location: DB.php");
            exit();
        } else {
            echo "Erreur lors de la modification : " . $conn->error;
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Modifier un enregistrement</title>
    </head>
    <body>
        <h1>Modifier une entrée de <?= htmlspecialchars($table) ?></h1>
        <form method="POST">
            <?php foreach ($rowData as $col => $val): ?>
                <?php if ($col === $primaryKey): ?>
                    <p><strong><?= htmlspecialchars($col) ?> (clé primaire) :</strong> <?= htmlspecialchars($val) ?></p>
                <?php else: ?>
                    <label><?= htmlspecialchars($col) ?> :</label>
                    <input type="text" name="<?= htmlspecialchars($col) ?>" value="<?= htmlspecialchars($val) ?>" required><br><br>
                <?php endif; ?>
            <?php endforeach; ?>
            <button type="submit">Enregistrer</button>
        </form>
    </body>
    </html>

    <?php
    exit();
}

// Sinon : afficher tableau avec boutons de modification
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
    <title>Choisir une ligne à modifier</title>
</head>
<body>
    <h1>Modifier une entrée dans <?= htmlspecialchars($table) ?></h1>

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
                        <a href="edit_entry.php?table=<?= urlencode($table) ?>&id=<?= urlencode($row[$primaryKey]) ?>">
                            ✏️ Modifier
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
