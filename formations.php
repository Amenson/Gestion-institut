<?php

require 'conn.php';

include 'includes/auth.php';

$message = '';
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}

$action = $_GET['action'] ?? 'list';

// Récupérer les formateurs pour le select
$formateurs = $pdo->query("SELECT id, CONCAT(nom, ' ', prenom) AS nom_complet FROM formateurs ORDER BY nom")->fetchAll();

// AJOUT / MODIFICATION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';
    $nom = $_POST['nom'] ?? '';
    $description = $_POST['description'] ?? null;
    $duree_mois = (int)($_POST['duree_mois'] ?? 0);
    $prix = (float)($_POST['prix'] ?? 0);
    $formateur_id = $_POST['formateur_id'] ?: null;
    $id = $_POST['id'] ?? null;

    if (!$nom || !$prix) {
        $message = 'Le nom et le prix sont requis.';
    } else {
        if ($action === 'edit' && $id) {
            $stmt = $pdo->prepare("UPDATE formations SET nom = ?, description = ?, duree_mois = ?, prix = ?, formateur_id = ? WHERE id = ?");
            $stmt->execute([$nom, $description, $duree_mois, $prix, $formateur_id, $id]);
            header('Location: formations.php?message=' . urlencode('Formation modifiée avec succès !'));
        } else {
            $stmt = $pdo->prepare("INSERT INTO formations (nom, description, duree_mois, prix, formateur_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $description, $duree_mois, $prix, $formateur_id]);
            header('Location: formations.php?message=' . urlencode('Formation ajoutée avec succès !'));
        }
        exit;
    }
}

// SUPPRESSION
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM formations WHERE id = ?")->execute([$_GET['delete']]);
    $message = "Formation supprimée !";
}

// LISTE
$formations = $pdo->query("
    SELECT f.*, CONCAT(fr.nom, ' ', fr.prenom) AS formateur 
    FROM formations f 
    LEFT JOIN formateurs fr ON f.formateur_id = fr.id 
    ORDER BY f.nom
")->fetchAll();

// ÉDITION
$edit = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM formations WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $edit = $stmt->fetch();
}

include 'includes/header.php';
?>

<h2>Gestion des Formations</h2>
<?php if ($message) echo "<div class='alert alert-success alert-auto-close'>$message</div>"; ?>

<form method="POST" class="mb-5">
    <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>">
    <?php if ($edit) echo '<input type="hidden" name="id" value="' . $edit['id'] . '">'; ?>

    <div class="row g-3">
        <div class="col-md-6">
            <input type="text" name="nom" class="form-control" placeholder="Nom de la formation" required
                value="<?= htmlspecialchars($edit['nom'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <input type="number" name="duree_mois" class="form-control" placeholder="Durée (mois)" min="1"
                value="<?= $edit['duree_mois'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <input type="number" name="prix" class="form-control" placeholder="Prix (FCFA)" step="0.01" required
                value="<?= $edit['prix'] ?? '' ?>">
        </div>
    </div>

    <div class="mt-3">
        <select name="formateur_id" class="form-select">
            <option value="">-- Aucun formateur assigné --</option>
            <?php foreach ($formateurs as $f): ?>
                <option value="<?= $f['id'] ?>" <?= ($edit['formateur_id'] ?? 0) == $f['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['nom_complet']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mt-3">
        <textarea name="description" class="form-control" rows="4" placeholder="Description de la formation..."><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-success mt-3"><?= $edit ? 'Modifier' : 'Ajouter' ?> la formation</button>
</form>

<table class="table table-hover table-bordered">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Durée</th>
            <th>Prix</th>
            <th>Formateur</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($formations as $f): ?>
            <tr>
                <td><?= $f['id'] ?></td>
                <td><?= htmlspecialchars($f['nom']) ?></td>
                <td><?= $f['duree_mois'] ? $f['duree_mois'] . ' mois' : '-' ?></td>
                <td><?= number_format($f['prix'], 0, ',', ' ') ?> FCFA</td>
                <td><?= htmlspecialchars($f['formateur'] ?? '-') ?></td>
                <td>
                    <a href="?action=edit&id=<?= $f['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                    <a href="?delete=<?= $f['id'] ?>" data-confirm="Confirmer la suppression ?" class="btn btn-sm btn-danger">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($formations)): ?>
            <tr>
                <td colspan="6" class="text-center">Aucune formation enregistrée</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>