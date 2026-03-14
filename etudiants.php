<?php

require 'conn.php';

include 'includes/auth.php';

$message = '';
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}

$action = $_GET['action'] ?? 'list';

// === AJOUT / MODIFICATION ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $date_naissance = $_POST['date_naissance'] ?? null;
    $adresse = $_POST['adresse'] ?? '';
    $id = $_POST['id'] ?? null;

    if (!$nom || !$prenom) {
        $message = 'Le nom et le prénom sont requis.';
    } else {
        if ($action === 'edit' && $id) {
            $stmt = $pdo->prepare("UPDATE etudiants SET nom=?, prenom=?, email=?, telephone=?, date_naissance=?, adresse=? WHERE id=?");
            $stmt->execute([$nom, $prenom, $email, $telephone, $date_naissance, $adresse, $id]);
            header('Location: etudiants.php?message=' . urlencode('Étudiant modifié avec succès !'));
        } else {
            $stmt = $pdo->prepare("INSERT INTO etudiants (nom, prenom, email, telephone, date_naissance, adresse) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$nom, $prenom, $email, $telephone, $date_naissance, $adresse]);
            header('Location: etudiants.php?message=' . urlencode('Étudiant ajouté avec succès !'));
        }
        exit;
    }
}

// === SUPPRESSION ===
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM etudiants WHERE id = ?")->execute([$_GET['delete']]);
    $message = "Étudiant supprimé !";
}

// === LISTE ===
$etudiants = $pdo->query("SELECT * FROM etudiants ORDER BY nom")->fetchAll();

// === ÉDITION (récupération des données)
$edit = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

include 'includes/header.php';
?>

<h2>Gestion des Étudiants</h2>
<?php if ($message) echo "<div class='alert alert-success alert-auto-close'>$message</div>"; ?>

<!-- Formulaire Ajout/Modification -->
<form method="POST" class="mb-5">
    <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>">
    <?php if ($edit) echo '<input type="hidden" name="id" value="' . $edit['id'] . '">'; ?>

    <div class="row">
        <div class="col-md-3"><input type="text" name="nom" class="form-control" placeholder="Nom" required value="<?= $edit['nom'] ?? '' ?>"></div>
        <div class="col-md-3"><input type="text" name="prenom" class="form-control" placeholder="Prénom" required value="<?= $edit['prenom'] ?? '' ?>"></div>
        <div class="col-md-3"><input type="email" name="email" class="form-control" placeholder="Email" required value="<?= $edit['email'] ?? '' ?>"></div>
        <div class="col-md-3"><input type="text" name="telephone" class="form-control" placeholder="Téléphone" value="<?= $edit['telephone'] ?? '' ?>"></div>
    </div>
    <div class="row mt-2">
        <div class="col-md-3"><input type="date" name="date_naissance" class="form-control" value="<?= $edit['date_naissance'] ?? '' ?>"></div>
        <div class="col-md-9"><input type="text" name="adresse" class="form-control" placeholder="Adresse" value="<?= $edit['adresse'] ?? '' ?>"></div>
    </div>
    <button type="submit" class="btn btn-success mt-3"><?= $edit ? 'Modifier' : 'Ajouter' ?> l'étudiant</button>
</form>

<!-- Tableau -->
<table class="table table-hover">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Date de naissance</th>
            <th>Adresse</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($etudiants as $e): ?>
            <tr>
                <td><?= htmlspecialchars($e['id']) ?></td>
                <td><?= htmlspecialchars($e['nom']) ?></td>
                <td><?= htmlspecialchars($e['prenom']) ?></td>
                <td><?= htmlspecialchars($e['email'] ?? '') ?></td>
                <td><?= htmlspecialchars($e['telephone'] ?? '') ?></td>
                <td><?= htmlspecialchars($e['date_naissance'] ?? '') ?></td>
                <td><?= htmlspecialchars($e['adresse'] ?? '') ?></td>
                <td>
                    <a href="etudiants.php?action=edit&id=<?= $e['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                    <a href="etudiants.php?delete=<?= $e['id'] ?>" data-confirm="Supprimer ?" class="btn btn-sm btn-danger">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>