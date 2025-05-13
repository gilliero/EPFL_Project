<?php
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    // Redirige vers la page de connexion
    header("Location: ../index.html");
    exit(); // Assure que le script s'arrête après la redirection
}



$servername = "db-ic.epfl.ch";
$username = "icit_ictrip_adm";
$password = "GdMrL0pZFGKnV8hyntQjFeKKmAbSSQRK";
$dbname = "icit_ictrip";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("La connexion à la base de données a échoué : " . $conn->connect_error);
}

$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["user_gaspar"];

// Récupérer les informations de formation et d'année de l'utilisateur
$sql_user = "SELECT formation, year FROM t_personne WHERE id_personne = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_info = $result_user->fetch_assoc();
$formation = $user_info['formation'];
$year = $user_info['year'];
$stmt_user->close();

// Récupérer les modules avec leur catégorie
$sql_branches = "SELECT subject, category FROM branches WHERE formation = ? AND year = ?";
$stmt_branches = $conn->prepare($sql_branches);
$stmt_branches->bind_param("si", $formation, $year);
$stmt_branches->execute();
$result_branches = $stmt_branches->get_result();

$modules = [];
while ($row = $result_branches->fetch_assoc()) {
    $modules[$row['category']][] = $row['subject'];
}

$stmt_branches->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["action"]) && $_POST["action"] === "edit" && isset($_POST["note_id"]) && isset($_POST["new_note"])) {
        $note_id_to_edit = $_POST["note_id"];
        $new_note = $_POST["new_note"];

        $sql_update = "UPDATE notes SET note = ? WHERE id = ? AND user_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sii", $new_note, $note_id_to_edit, $user_id);

        if ($stmt_update->execute()) {
            echo '<p class="green">Note modifiée avec succès.</p>';
        } else {
            echo '<p class="red">Erreur lors de la modification de la note : ' . $stmt_update->error . '</p>';
        }

        $stmt_update->close();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $branche = $_POST["branche"];
        $note = $_POST["note"];

        if ($user_id !== null) {
            $sql_insert = "INSERT INTO notes (user_id, branche, note) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("isd", $user_id, $branche, $note);

            if ($stmt_insert->execute()) {
                echo '<script>
                        const user = "' . addslashes($user_name) . '";
                        const branche = "' . addslashes($branche) . '";
                        const note = "' . addslashes($note) . '";
                        const email = "jeremy.noth@epfl.ch";
                        const subject = encodeURIComponent("Nouvelle note ajoutée pour " + user);
                        const body = encodeURIComponent("Bonjour,\\n\\nL\\\'apprenti " + user + " a ajouté une nouvelle note dans l\\\'Application. Il s\\\'agit d\\\'un " + note + " en " + branche + ".\\n\\nCordialement,\\nGESTION DES NOTH");
                        const mailtoLink = "mailto:" + email + "?subject=" + subject + "&body=" + body;
                        window.location.href = mailtoLink;
                      </script>';
            } else {
                echo '<p class="red">Erreur lors de l\'ajout de la note : ' . $stmt_insert->error . '</p>';
            }

            $stmt_insert->close();
            exit();
        } else {
            echo "Erreur : L'ID de l'utilisateur n'est pas défini.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["note_id"])) {
    $note_id = $_GET["note_id"];

    $sql_delete = "DELETE FROM notes WHERE id = ? AND user_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("ii", $note_id, $user_id);

    if ($stmt_delete->execute()) {
        echo '<p class="green">Note supprimée avec succès.</p>';
    } else {
        echo '<p class="red">Erreur lors de la suppression de la note : ' . $stmt_delete->error . '</p>';
    }

    $stmt_delete->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="addNotes.css">
    <title>Ajouter les notes</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }

        header {
            background-color: #B51F1F;
            color: #fff;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        main {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2, h3 {
            font-size: 32px;
            color: #333;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        select, input[type="number"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        input[type="number"] {
            max-width: 300px; /* Limite la largeur de la zone de texte */
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .green {
            color: green;
        }

        .orange {
            color: orange;
        }

        .red {
            color: red;
        }

        .average {
            font-weight: bold;
        }

        .average.green {
            color: green;
        }

        .average.orange {
            color: orange;
        }

        .average.red {
            color: red;
        }

        .btn {
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s ease;
            margin-right: 5px;
        }

        .popup {
            display: none;
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background-color: #fefefe;
            padding: 20px;
            border: 1px solid #888;
            z-index: 1000;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .popup input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            max-width: 300px; /* Limite la largeur de la zone de texte */
        }

        .popup input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>
</head>
<body>
    <header>
        <img src="../Gregory/img/epfllogo.png" alt="">
        <div class="title-container">
            <h1>GESTION DES NOTH</h1>
        </div>
    </header>
    <main>
        <h2>Ajouter une note</h2>
        
        <form method="post" action="">
            <label for="branche">Branche :</label>
            <select name="branche" required>
                <?php
                foreach ($modules as $category => $subjects) {
                    echo '<optgroup label="' . htmlspecialchars($category) . '">';
                    foreach ($subjects as $subject) {
                        echo '<option value="' . htmlspecialchars($subject) . '">' . htmlspecialchars($subject) . '</option>';
                    }
                    echo '</optgroup>';
                }
                ?>
            </select>
            <label for="note">Note :</label>
            <input type="number" name="note" step="0.1" min="1" max="6" required oninput="validateNote(this)">
            <input type="submit" value="Ajouter Note">
        </form>

        <h3>Notes actuelles :</h3>
        <table border="1">
            <tr>
                <th>Branche</th>
                <th>Notes</th>
                <th>Moyenne</th>
                <th>Actions</th>
            </tr>
            <?php
            $sql_select = "SELECT t_personne.gaspar_personne, notes.id, notes.branche, notes.note
                           FROM notes
                           INNER JOIN t_personne ON notes.user_id = t_personne.id_personne
                           WHERE notes.user_id = ?
                           ORDER BY notes.branche";
            $stmt_select = $conn->prepare($sql_select);
            $stmt_select->bind_param("i", $user_id);
            $stmt_select->execute();
            $result = $stmt_select->get_result();

            $notesByBranche = [];

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $branche = $row["branche"];
                    $noteValue = $row["note"];
                    $note_id = $row["id"];
            
                    if (!isset($notesByBranche[$branche])) {
                        $notesByBranche[$branche] = [];
                    }
                    $notesByBranche[$branche][$note_id] = $noteValue;
                }
            
                foreach ($modules as $category => $subjects) {
                    echo '<tr>';
                    echo '<th colspan="4">' . htmlspecialchars($category) . '</th>';
                    echo '</tr>';
            
                    foreach ($subjects as $subject) {
                        if (isset($notesByBranche[$subject])) {
                            $moyenne = count($notesByBranche[$subject]) > 0 ? array_sum($notesByBranche[$subject]) / count($notesByBranche[$subject]) : 0;
            
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($subject) . '</td>';
                            echo '<td>' . implode(' / ', $notesByBranche[$subject]) . '</td>';
                            echo '<td class="average ' . getColorClass($moyenne) . '">' . number_format($moyenne, 2) . '</td>';
                            echo '<td>';
                            $last_note_id = array_key_last($notesByBranche[$subject]);
                            echo '<button class="btn btn-edit" onclick="showEditPopup(' . $last_note_id . ', ' . htmlspecialchars(json_encode($notesByBranche[$subject][$last_note_id])) . ')">Modifier la dernière note</button>';
                            echo '<a class="btn btn-delete" href="' . $_SERVER['PHP_SELF'] . '?action=delete&note_id=' . $last_note_id . '">Supprimer</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                }
            } else {
                echo '<tr><td colspan="4">Aucune note disponible</td></tr>';
            }
            
            function getColorClass($moyenne) {
                if ($moyenne > 4) {
                    return 'green';
                } elseif ($moyenne == 4) {
                    return 'orange';
                } else {
                    return 'red';
                }
            }
            ?>
        </table>
        <br>
        <button id="exportPDF" onclick="exportPDF()">Exporter en PDF</button>

        <div id="editPopup" class="popup">
    <form id="editForm" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="note_id" id="editNoteId">
        <label for="new_note">Nouvelle Note :</label>
        <input type="number" name="new_note" id="newNote" step="0.1" min="1" max="6" required>
        <br><br>
        <input type="submit" value="Modifier">
        <button type="button" onclick="closeEditPopup()">Annuler</button>
    </form>
</div>


        <script>
            function validateNote(input) {
                const value = parseFloat(input.value);
                if (isNaN(value) || value < 1 || value > 6) {
                    input.setCustomValidity('La note doit être un nombre entre 1 et 6.');
                } else {
                    input.setCustomValidity('');
                }
            }

            function validateNoteEdit(input) {
                const value = parseFloat(input.value);
                if (isNaN(value) || value < 1 || value > 6) {
                    input.setCustomValidity('La note doit être un nombre entre 1 et 6.');
                } else {
                    input.setCustomValidity('');
                }
            }

            function showEditPopup(noteId, noteValue) {
    document.getElementById("editNoteId").value = noteId;
    document.getElementById("newNote").value = noteValue;
    document.getElementById("editPopup").style.display = "block";
}

function closeEditPopup() {
        document.getElementById("editPopup").style.display = "none";
    }

         // Fonction pour exporter en PDF
         function exportPDF() {
            // Redirige vers exportPDF.php avec l'ID de l'utilisateur
            window.location.href = "./exportPDFNote.php?user_id=<?php echo $_SESSION['user_id']; ?>&branche=<?php echo $branche;?>&note=<?php echo $noteValue;?>&user_id=<?php echo $user_id;?>";
        }
        </script>
    </main>
</body>
</html>

<?php
$conn->close();
?>
