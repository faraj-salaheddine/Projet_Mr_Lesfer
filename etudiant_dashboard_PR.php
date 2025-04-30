<?php
session_start();

// ⚠️ Ici, on suppose que l'étudiant est connecté et son ID est stocké dans la session
$id_etudiant = $_SESSION['id_etudiant']; // à adapter selon ton système de login

$host = "localhost";
$db = "gestion_scolarite";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Infos de l'étudiant
$etudiant = $pdo->prepare("
    SELECT e.nom, e.prenom, f.nom_filiere
    FROM etudiants e
    JOIN filieres f ON e.id_filiere = f.id_filiere
    WHERE e.id_etudiant = ?
");
$etudiant->execute([$id_etudiant]);
$info = $etudiant->fetch(PDO::FETCH_ASSOC);

// Notes de l'étudiant
$notes = $pdo->prepare("
    SELECT m.nom_matiere, ev.note, ev.date_evaluation
    FROM evaluations ev
    JOIN matieres m ON ev.id_matiere = m.id_matiere
    WHERE ev.id_etudiant = ?
");
$notes->execute([$id_etudiant]);
$liste_notes = $notes->fetchAll(PDO::FETCH_ASSOC);

// Moyenne générale
$moyenne = $pdo->prepare("SELECT ROUND(AVG(note), 2) FROM evaluations WHERE id_etudiant = ?");
$moyenne->execute([$id_etudiant]);
$moyenneGenerale = $moyenne->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon tableau de bord</title>
    <style>
        body { font-family: Arial; padding: 30px; background: #f4f4f4; }
        h1, h2 { color: #2c3e50; }
        .box { background: white; padding: 20px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background-color: #3498db; color: white; }
    </style>
</head>
<body>

    <h1>Bienvenue, <?= htmlspecialchars($info['prenom']) ?> <?= htmlspecialchars($info['nom']) ?></h1>

    <div class="box">
        <h2>Filière : <?= htmlspecialchars($info['nom_filiere']) ?></h2>
        <p>Moyenne générale : <strong><?= $moyenneGenerale ?? 'N/A' ?></strong></p>
    </div>

    <div class="box">
        <h2>Mes notes</h2>
        <table>
            <tr>
                <th>Matière</th>
                <th>Note</th>
                <th>Date</th>
            </tr>
            <?php foreach ($liste_notes as $n): ?>
            <tr>
                <td><?= htmlspecialchars($n['nom_matiere']) ?></td>
                <td><?= $n['note'] ?></td>
                <td><?= $n['date_evaluation'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

</body>
</html>
