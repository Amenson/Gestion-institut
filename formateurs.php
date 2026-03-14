<?php


require_once __DIR__ . '/conn.php';   // ← chemin sûr + once évite les redéfinitions


if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    die("Erreur critique : la connexion à la base de données a échoué.<br>"
      . "Vérifiez que <strong>conn.php</strong> définit bien \$pdo.<br>"
      . "Message technique probable : consultez les logs PHP ou ajoutez un try/catch visible dans conn.php");
}


session_start();   
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// ... suite de ton code sans changement ...
$message = '';
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}

$action = $_GET['action'] ?? 'list';

// AJOUT / MODIFICATION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? null;
    $telephone = $_POST['telephone'] ?? null;
    $specialite = $_POST['specialite'] ?? null;
    $id = $_POST['id'] ?? null;

    if (!$nom || !$prenom) {
        $message = 'Le nom et le prénom sont requis.';
    } else {
        if ($action === 'edit' && $id) {
            $stmt = $pdo->prepare("UPDATE formateurs SET nom = ?, prenom = ?, email = ?, telephone = ?, specialite = ? WHERE id = ?");
            $stmt->execute([$nom, $prenom, $email, $telephone, $specialite, $id]);
            header('Location: formateurs.php?message=' . urlencode('Formateur modifié avec succès !'));
        } else {
            $stmt = $pdo->prepare("INSERT INTO formateurs (nom, prenom, email, telephone, specialite) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $telephone, $specialite]);
            header('Location: formateurs.php?message=' . urlencode('Formateur ajouté avec succès !'));
        }
        exit;
    }
}

// SUPPRESSION
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM formateurs WHERE id = ?")->execute([$_GET['delete']]);
    header('Location: formateurs.php?message=' . urlencode('Formateur supprimé !'));
    exit;
}

// LISTE
$formateurs = $pdo->query("SELECT * FROM formateurs ORDER BY nom")->fetchAll();

// ÉDITION
$edit = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM formateurs WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $edit = $stmt->fetch();
}

include 'includes/header.php';
?>

<h2>Gestion des Formateurs</h2>
<?php if ($message) echo "<div class='alert alert-success alert-auto-close'>$message</div>"; ?>

<form method="POST" class="mb-5">
    <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>">
    <?php if ($edit) echo '<input type="hidden" name="id" value="' . $edit['id'] . '">'; ?>

    <div class="row g-3">
        <div class="col-md-3">
            <input type="text" name="nom" class="form-control" placeholder="Nom" required value="<?= htmlspecialchars($edit['nom'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="prenom" class="form-control" placeholder="Prénom" required value="<?= htmlspecialchars($edit['prenom'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <input type="email" name="email" class="form-control" placeholder="Email" value="<?= htmlspecialchars($edit['email'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="telephone" class="form-control" placeholder="Téléphone" value="<?= htmlspecialchars($edit['telephone'] ?? '') ?>">
        </div>
    </div>

    <div class="mt-3">
        <input type="text" name="specialite" class="form-control" placeholder="Spécialité (ex: Développement Web, Marketing Digital)"
            value="<?= htmlspecialchars($edit['specialite'] ?? '') ?>">
    </div>

    <button type="submit" class="btn btn-success mt-3"><?= $edit ? 'Modifier' : 'Ajouter' ?> le formateur</button>
</form>

<table class="table table-hover table-bordered">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Spécialité</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($formateurs as $f): ?>
            <tr>
                <td><?= $f['id'] ?></td>
                <td><?= htmlspecialchars($f['nom']) ?></td>
                <td><?= htmlspecialchars($f['prenom']) ?></td>
                <td><?= htmlspecialchars($f['email'] ?? '-') ?></td>
                <td><?= htmlspecialchars($f['telephone'] ?? '-') ?></td>
                <td><?= htmlspecialchars($f['specialite'] ?? '-') ?></td>
                <td>
                    <a href="?action=edit&id=<?= $f['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                    <a href="?delete=<?= $f['id'] ?>" data-confirm="Confirmer la suppression ?" class="btn btn-sm btn-danger">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($formateurs)): ?>
            <tr>
                <td colspan="7" class="text-center">Aucun formateur enregistré</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>