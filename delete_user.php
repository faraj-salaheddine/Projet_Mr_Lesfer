<?php
session_start();

// Vérifie que l'utilisateur est un administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Vérifie que l'ID a bien été passé en paramètre
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("❌ ID utilisateur non fourni.");
}

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "gestion_scolarite");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$id = intval($_GET['id']); // sécurisation

// ...existing code...
?>
<td>
    <a href="edit_user.php?id=<?php echo $user['id']; ?>">Modifier</a> |
    <a href="delete_user.php?id=<?php echo $user['id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
</td>
<?php
// ...existing code...

// Supprime l'utilisateur
$stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Redirection après suppression
    header("Location: liste_utilisateurs.php?msg=deleted");
    exit;
} else {
    echo "❌ Erreur lors de la suppression : " . $stmt->error;
}
?>
