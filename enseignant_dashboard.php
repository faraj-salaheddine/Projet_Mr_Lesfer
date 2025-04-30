<?php
session_start();
if (!isset($_SESSION['id_enseignant'])) {
    header("Location: login.php");
    exit();
}

$pdo = new PDO("mysql:host=localhost;dbname=gestion_scolarite", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$id_enseignant = $_SESSION['id_enseignant'];
$nom_enseignant = $_SESSION['nom_enseignant'] ?? '';

// Traitement ajout de note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_note'])) {
    $id_matiere = $_POST['matiere'];
    $id_etudiant = $_POST['etudiant'];
    $note = $_POST['note'];
    $date = $_POST['date'];

    $stmt = $pdo->prepare("INSERT INTO evaluations (id_etudiant, id_matiere, note, date_evaluation)
                           VALUES (?, ?, ?, ?)");
    $stmt->execute([$id_etudiant, $id_matiere, $note, $date]);
    $message = "Note ajoutée.";
}

// Traitement suppression
if (isset($_GET['supprimer'])) {
    $stmt = $pdo->prepare("DELETE FROM evaluations WHERE id_evaluation = ?");
    $stmt->execute([$_GET['supprimer']]);
    header("Location: enseignant_dashboard.php");
    exit();
}

// Traitement modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_note'])) {
    $id_eval = $_POST['id_evaluation'];
    $note = $_POST['note'];
    $date = $_POST['date'];

    $stmt = $pdo->prepare("UPDATE evaluations SET note = ?, date_evaluation = ? WHERE id_evaluation = ?");
    $stmt->execute([$note, $date, $id_eval]);
    $message = "Note modifiée.";
}

// Récupérer les matières
$matieres = $pdo->prepare("SELECT m.id_matiere, m.nom_matiere, f.nom_filiere
                           FROM matieres m
                           JOIN filieres f ON m.id_filiere = f.id_filiere
                           WHERE m.id_enseignant = ?");
$matieres->execute([$id_enseignant]);
$matieres = $matieres->fetchAll();

// Récupérer les notes
$notes = $pdo->prepare("SELECT ev.id_evaluation, e.nom, e.prenom, m.nom_matiere, ev.note, ev.date_evaluation
                        FROM evaluations ev
                        JOIN etudiants e ON ev.id_etudiant = e.id_etudiant
                        JOIN matieres m ON ev.id_matiere = m.id_matiere
                        WHERE m.id_enseignant = ?");
$notes->execute([$id_enseignant]);
$notes = $notes->fetchAll();

// Moyennes par étudiant
$moyennes = $pdo->prepare("SELECT e.id_etudiant, e.nom, e.prenom, ROUND(AVG(ev.note),2) AS moyenne
                           FROM evaluations ev
                           JOIN etudiants e ON ev.id_etudiant = e.id_etudiant
                           JOIN matieres m ON ev.id_matiere = m.id_matiere
                           WHERE m.id_enseignant = ?
                           GROUP BY e.id_etudiant");
$moyennes->execute([$id_enseignant]);
$moyennes = $moyennes->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord Enseignant</title>
</head>
<body>
    <h1>Bienvenue <?= htmlspecialchars($nom_enseignant) ?></h1>

    <?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>

    <h2>Mes matières</h2>
    <ul>
        <?php foreach ($matieres as $m): ?>
            <li><?= htmlspecialchars($m['nom_matiere']) ?> (<?= htmlspecialchars($m['nom_filiere']) ?>)</li>
        <?php endforeach; ?>
    </ul>

    <h2>Ajouter une note</h2>
    <form method="POST">
        <label>Matière :</label>
        <select name="matiere" required>
            <?php foreach ($matieres as $m): ?>
                <option value="<?= $m['id_matiere'] ?>"><?= $m['nom_matiere'] ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Étudiant :</label>
        <select name="etudiant" required>
            <?php
            // Liste de tous les étudiants de filières liées aux matières de cet enseignant
            $stmt = $pdo->prepare("SELECT e.id_etudiant, e.nom, e.prenom FROM etudiants e");
            $stmt->execute();
            $etudiants = $stmt->fetchAll();
            foreach ($etudiants as $e):
            ?>
                <option value="<?= $e['id_etudiant'] ?>"><?= $e['nom'] . ' ' . $e['prenom'] ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Note :</label>
        <input type="number" step="0.01" name="note" required><br><br>

        <label>Date :</label>
        <input type="date" name="date" required><br><br>

        <button type="submit" name="ajouter_note">Ajouter la note</button>
    </form>

    <h2>Notes saisies</h2>
    <table border="1">
        <tr>
            <th>Étudiant</th>
            <th>Matière</th>
            <th>Note</th>
            <th>Date</th>
            <th>Modifier</th>
            <th>Supprimer</th>
        </tr>
        <?php foreach ($notes as $n): ?>
            <tr>
                <form method="POST">
                    <td><?= $n['nom'] . ' ' . $n['prenom'] ?></td>
                    <td><?= $n['nom_matiere'] ?></td>
                    <td>
                        <input type="number" name="note" value="<?= $n['note'] ?>" step="0.01" required>
                    </td>
                    <td>
                        <input type="date" name="date" value="<?= $n['date_evaluation'] ?>" required>
                    </td>
                    <td>
                        <input type="hidden" name="id_evaluation" value="<?= $n['id_evaluation'] ?>">
                        <button type="submit" name="modifier_note">✔</button>
                    </td>
                    <td><a href="?supprimer=<?= $n['id_evaluation'] ?>" onclick="return confirm('Confirmer suppression ?')">🗑️</a></td>
                </form>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Moyennes par étudiant</h2>
    <table border="1">
        <tr>
            <th>Étudiant</th>
            <th>Moyenne</th>
        </tr>
        <?php foreach ($moyennes as $m): ?>
            <tr>
                <td><?= $m['nom'] . ' ' . $m['prenom'] ?></td>
                <td><?= $m['moyenne'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <p><a href="login.php">Se déconnecter</a></p>
</body>
</html>
