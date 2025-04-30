<?php
session_start();

// Vérifiez si l'utilisateur est un formateur, sinon redirigez vers la page d'accueil
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "Formateur") {
    header("Location: ../index.php");
    exit();
}

// Paramètres de connexion à la base de données
$servername = "db-ic.epfl.ch";
$username = "icit_ictrip_adm";
$password = "GdMrL0pZFGKnV8hyntQjFeKKmAbSSQRK";
$dbname = "icit_ictrip";

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifiez la connexion
if ($conn->connect_error) {
    die("La connexion à la base de données a échoué : " . $conn->connect_error);
}

// Récupérez les utilisateurs apprentis
$sql_select_users = "SELECT id_personne, prenom_personne, nom_personne, formation, year FROM t_personne WHERE role_personne = 'Apprentis'";
$result_users = $conn->query($sql_select_users);

$notes = array();
$selected_user_id = isset($_POST["selected_user"]) ? $_POST["selected_user"] : null;
$selected_user_name = "";
$selected_formation = "";
$selected_year = 0;

// Si le formulaire est soumis et un utilisateur est sélectionné, récupérez les notes de cet utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && $selected_user_id) {
    $sql_select_user_info = "SELECT prenom_personne, nom_personne, formation, year FROM t_personne WHERE id_personne = $selected_user_id";
    $result_user_info = $conn->query($sql_select_user_info);
    if ($result_user_info->num_rows > 0) {
        $row_user_info = $result_user_info->fetch_assoc();
        $selected_user_name = $row_user_info['prenom_personne'] . ' ' . $row_user_info['nom_personne'];
        $selected_formation = $row_user_info['formation'];
        $selected_year = $row_user_info['year'];
    }

    $sql_select_notes = "SELECT t_personne.prenom_personne, notes.branche, notes.note FROM notes
                         INNER JOIN t_personne ON notes.user_id = t_personne.id_personne
                         WHERE notes.user_id = $selected_user_id ORDER BY notes.id DESC";
    $result_notes = $conn->query($sql_select_notes);

    // Stockez les notes dans un tableau
    if ($result_notes->num_rows > 0) {
        while ($row_note = $result_notes->fetch_assoc()) {
            $notes[] = array(
                'prenom_personne' => $row_note['prenom_personne'],
                'branche' => $row_note['branche'],
                'note' => floatval($row_note['note']) // Assurez-vous que les notes sont traitées comme des nombres
            );
        }
    }
}

// Récupérer les modules avec leur catégorie pour l'utilisateur sélectionné
$modules = [];
if ($selected_formation && $selected_year) {
    $sql_branches = "SELECT subject, category FROM branches WHERE formation = ? AND year = ?";
    $stmt_branches = $conn->prepare($sql_branches);
    $stmt_branches->bind_param("si", $selected_formation, $selected_year);
    $stmt_branches->execute();
    $result_branches = $stmt_branches->get_result();

    while ($row = $result_branches->fetch_assoc()) {
        $modules[$row['category']][] = $row['subject'];
    }

    $stmt_branches->close();
}

// Groupes et matières par défaut
$default_groups = array(
    'Scolaire' => array('Math', 'Anglais', 'Allemand'),
    'CFC' => array('Module 1', 'Module 2', 'Module 3'),
    'CIE' => array('Module 4', 'Module 5', 'Module 6')
);

// Utilisez les modules récupérés pour définir les groupes et matières si disponibles
$groups = !empty($modules) ? $modules : $default_groups;

// Répartition des notes dans les groupes et matières
$grouped_notes = array();
foreach ($groups as $group => $subjects) {
    $grouped_notes[$group] = array();
    foreach ($subjects as $subject) {
        $grouped_notes[$group][$subject] = array();
    }
}

foreach ($notes as $note) {
    $branch = $note['branche'];
    foreach ($groups as $group => $subjects) {
        if (in_array($branch, $subjects)) {
            $grouped_notes[$group][$branch][] = $note['note'];
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="viewNotes.css">
    <title>Voir les Noth</title>

    <style>
        .green {
            color: green;
        }

        .orange {
            color: orange;
        }

        .red {
            color: red;
        }
    </style>
    <!-- Inclure la bibliothèque jsPDF et jsPDF-AutoTable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>
</head>
<body>
    <header>
        <img src="../Gregory/img/epfllogo.png" alt="Logo de l'EPFL">
        <div class="title-container">
            <h1>Voir les Noth</h1>
        </div>
    </header>
    <form method="post" action="">
        <label for="selected_user">Sélectionner un utilisateur :</label>
        <select name="selected_user" required>
            <?php
            // Génération des options de sélection des utilisateurs
            if ($result_users->num_rows > 0) {
                while ($row_user = $result_users->fetch_assoc()) {
                    $selected = ($row_user["id_personne"] == $selected_user_id) ? 'selected' : '';
                    echo '<option value="' . $row_user["id_personne"] . '" ' . $selected . '>' . $row_user["prenom_personne"] . ' ' . $row_user["nom_personne"] . '</option>';
                }
            }
            ?>
        </select>
        <input type="submit" value="Afficher Notes">
    </form>

    <?php
    // Affichage des notes par groupe et par matière
    foreach ($grouped_notes as $group => $subjects) {
        echo '<div class="table-container">';
        echo '<h3>' . htmlspecialchars($group) . '</h3>';
        echo '<table>';
        echo '<tr><th>Matière</th><th>Notes</th><th>Moyenne</th></tr>';
        $group_average = 0;
        $group_count = 0;
        foreach ($subjects as $subject => $notes) {
            if (!empty($notes)) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($subject) . '</td>';
                echo '<td>';
                echo implode(" / ", $notes); // Affichage des notes avec "/"
                echo '</td>';
                $average = array_sum($notes) / count($notes);
                $colorClass = '';
                if ($average > 4) {
                    $colorClass = 'green';
                } elseif ($average == 4) {
                    $colorClass = 'orange';
                } else {
                    $colorClass = 'red';
                }
                echo '<td class="average ' . $colorClass . '">' . number_format($average, 2) . '</td>';
                echo '</tr>';
                $group_average += $average;
                $group_count++;
            }
        }
        if ($group_count > 0) {
            $group_average /= $group_count;
            $group_colorClass = '';
            // Déterminez le type de moyenne en fonction du groupe
            $average_label = '';
            if ($group === 'Scolaire') {
                $average_label = 'Moyenne Scolaire';
            } elseif ($group === 'CFC') {
                $average_label = 'Moyenne CFC';
            } elseif ($group === 'CIE') {
                $average_label = 'Moyenne CIE';
            } elseif ($group === 'Culture générale') {
                $average_label = 'Moyenne Culture générale';
            } elseif ($group === 'Compétences de base élargies') {
                $average_label = 'Moyenne Compétences de base élargies';
            }
            // Utilisez le type de moyenne déterminé
            echo '<tr class="average-row">';
            echo '<td class="moyennegroupe">' . $average_label . '</td>';
            echo '<td></td>';
            $group_colorClass = '';
            if ($group_average > 4) {
                $group_colorClass = 'green';
            } elseif ($group_average == 4) {
                $group_colorClass = 'orange';
            } else {
                $group_colorClass = 'red';
            }
            echo '<td class="average ' . $group_colorClass . '"><strong>' . number_format($group_average, 2) . '</strong></td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
    }
    ?>

    <br>
    <a class="deconnect" href="logout2.php">Se déconnecter</a>
    <button id="exportPDF">Exporter en PDF</button>

    <script>
        // Script pour exporter les notes en PDF
        document.getElementById("exportPDF").addEventListener("click", function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const userName = "<?php echo $selected_user_name; ?>";
            const currentDate = new Date().toLocaleDateString('fr-CA').replace(/-/g, '');
            const fileName = `Notes${userName.replace(/\s+/g, '')}${currentDate}.pdf`;

            // Titre
            doc.setFontSize(18);
            doc.text(`Notes de ${userName}`, 20, 20);

            // Récupérer les groupes et matières
            const groups = <?php echo json_encode($grouped_notes); ?>;
            let yOffset = 30;

            // Utiliser jsPDF-AutoTable pour créer des tableaux esthétiques
            for (const [group, subjects] of Object.entries(groups)) {
                doc.setFontSize(16);
                doc.text(group, 20, yOffset);
                yOffset += 10;

                const tableData = [];
                for (const [subject, notes] of Object.entries(subjects)) {
                    if (notes.length > 0) {
                        const average = (notes.reduce((a, b) => a + b, 0) / notes.length).toFixed(2);
                        tableData.push([subject, notes.join(" / "), average]);
                    }
                }

                if (tableData.length > 0) {
                    doc.autoTable({
                        head: [['Matière', 'Notes', 'Moyenne']],
                        body: tableData,
                        startY: yOffset,
                        styles: { halign: 'center', fillColor: [230, 230, 250] },
                        headStyles: { fillColor: [0, 57, 107] },
                    });
                    yOffset = doc.autoTable.previous.finalY + 10;
                }

                // Calculer et afficher la moyenne du groupe
                const groupNotes = Object.values(subjects).flat();
                if (groupNotes.length > 0) {
                    const groupAverage = (groupNotes.reduce((a, b) => a + b, 0) / groupNotes.length).toFixed(2);
                    doc.text(`Moyenne ${group}`, 20, yOffset);
                    doc.text(groupAverage, 150, yOffset);
                    yOffset += 10;
                }

                yOffset += 20; // Espacement entre les groupes
            }

            // Enregistrer le PDF
            doc.save(fileName);
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
