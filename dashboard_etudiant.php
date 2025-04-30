<?php
session_start();
include("../css/config/db.php");

$id_etudiant = $_SESSION['id_etudiant'] ?? null;

if (!$id_etudiant) {
    echo "Accès refusé. Veuillez vous connecter.";
    exit;
}

// Infos étudiant
$etudiant = $conn->query("SELECT e.nom, e.prenom, f.nom_filiere 
    FROM etudiants e 
    JOIN filieres f ON e.id_filiere = f.id_filiere 
    WHERE e.id_etudiant = $id_etudiant")->fetch_assoc();

// Notes + moyenne
$result = $conn->query("SELECT m.nom_matiere, e.note, e.date_evaluation 
    FROM evaluations e 
    JOIN matieres m ON e.id_matiere = m.id_matiere 
    WHERE e.id_etudiant = $id_etudiant");

$total = 0;
$count = 0;
?>

<h2>Bienvenue, <?= $etudiant['prenom'] . " " . $etudiant['nom'] ?></h2>
<p><strong>Filière :</strong> <?= $etudiant['nom_filiere'] ?></p>

<h3>Relevé de notes</h3>
<table border="1" cellpadding="10">
    <tr><th>Matière</th><th>Note</th><th>Date</th></tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['nom_matiere'] ?></td>
            <td><?= $row['note'] ?></td>
            <td><?= $row['date_evaluation'] ?></td>
        </tr>
        <?php
        $total += $row['note'];
        $count++;
        ?>
    <?php endwhile; ?>
</table>

<?php if ($count > 0): ?>
    <p><strong>Moyenne générale :</strong> <?= round($total / $count, 2) ?>/20</p>
<?php else: ?>
    <p>Aucune note enregistrée.</p>
<?php endif; ?>

<a href="deconnexion.php">Déconnexion</a>