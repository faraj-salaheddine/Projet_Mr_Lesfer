<?php
// Sécurisation de la page pour s'assurer que l'utilisateur est un admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin</title>
    <link rel="stylesheet" href="style.css"> <!-- Si tu as un fichier CSS -->
</head>
<body>

    <header>
        <h1>Bienvenue sur le tableau de bord de l'admin</h1>
        <p>Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?> !</p>
    </header>

    <nav>
        <ul>
            <li><a href="admin_dashboard.php">Accueil</a></li>
            <li><a href="users.php">Gérer les utilisateurs</a></li>
            <li><a href="Gestion_pedagogique.php">Gestion Pédagogique</a></li>
            <li><a href="admin_stats.php">Statistiques</a></li>
            <li><a href="login.php">Déconnexion</a></li>
        </ul>
    </nav>
    <?php


// 🔒 Vérification admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=localhost;dbname=gestion_scolarite", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Gestion du filtre
$filtre_role = $_GET['role'] ?? 'tous';

if ($filtre_role === 'tous') {
    $stmt = $conn->prepare("SELECT * FROM utilisateurs");
    $stmt->execute();
} else {
    $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE role = ?");
    $stmt->execute([$filtre_role]);
}

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<style>
        body {
            font-family: Arial;
            padding: 20px;
            background: #f5f5f5;
        }
        h2 {
            text-align: center;
        }
        .buttons {
            text-align: center;
            margin-bottom: 20px;
        }
        .buttons a {
            padding: 10px 15px;
            margin: 5px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
        .buttons a:hover {
            background-color: #0056b3;
        }
        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background: #eee;
        }
        .action-btn {
            background: #28a745;
        }
        .delete-btn {
            background: #dc3545;
        }
    </style>
    <meta charset="UTF-8">
    <title>Gestion des utilisateurs</title>

</head>
<body>

<h2>Gestion des utilisateurs</h2>

<div class="buttons">
    <a href="exemple.php?role=tous">Tous</a>
    <a href="exemple.php?role=admin">Admins</a>
    <a href="exemple.php?role=enseignant">Enseignants</a>
    <a href="exemple.php?role=etudiant">Étudiants</a>
    <a href="add_user.php" style="background-color:#20c997;">➕ Ajouter un utilisateur</a>
</div>

<table>
    <thead>
        <tr>
            <th>Nom d'utilisateur</th>
            <th>Rôle</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td><?= htmlspecialchars($user['gmail']) ?></td>
            <td>
                <a class="buttons action-btn" href="edit_user.php?id=<?= $user['id'] ?>">Modifier</a>
                <a class="buttons delete-btn" href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Supprimer cet utilisateur ?');">Supprimer</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
