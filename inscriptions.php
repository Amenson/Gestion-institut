<?php

require 'conn.php';

include 'includes/auth.php';

$message = '';
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}

// Récupérer étudiants et formations pour les selects
$etudiants = $pdo->query("SELECT id, CONCAT(nom, ' ', prenom) AS nom_complet FROM etudiants ORDER BY nom")->fetchAll();
$formations = $pdo->query("SELECT id, nom FROM formations ORDER BY nom")->fetchAll();

// SUPPRESSION
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM inscriptions WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: inscriptions.php?message=' . urlencode('Inscription supprimée !'));
    exit;
}

// ÉDITION
$edit = null;
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
    $stmt = $pdo->prepare("SELECT * FROM inscriptions WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $edit = $stmt->fetch();
}

// AJOUT / MODIFICATION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $etudiant_id = $_POST['etudiant_id'] ?? null;
    $formation_id = $_POST['formation_id'] ?? null;
    $date_inscription = $_POST['date_inscription'] ?: date('Y-m-d');
    $statut = $_POST['statut'] ?? 'en cours';
    $id = $_POST['id'] ?? null;

    if (!$etudiant_id || !$formation_id) {
        $message = 'Veuillez sélectionner un étudiant et une formation.';
    } else {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE inscriptions SET etudiant_id = ?, formation_id = ?, date_inscription = ?, statut = ? WHERE id = ?");
            $stmt->execute([$etudiant_id, $formation_id, $date_inscription, $statut, $id]);
            header('Location: inscriptions.php?message=' . urlencode('Inscription modifiée avec succès !'));
        } else {
            $stmt = $pdo->prepare("INSERT INTO inscriptions (etudiant_id, formation_id, date_inscription, statut) VALUES (?, ?, ?, ?)");
            $stmt->execute([$etudiant_id, $formation_id, $date_inscription, $statut]);
            header('Location: inscriptions.php?message=' . urlencode('Inscription ajoutée avec succès !'));
        }
        exit;
    }
}

// LISTE
$inscriptions = $pdo->query(
    "SELECT i.*, CONCAT(e.nom, ' ', e.prenom) AS etudiant, f.nom AS formation
     FROM inscriptions i
     JOIN etudiants e ON i.etudiant_id = e.id
     JOIN formations f ON i.formation_id = f.id
     ORDER BY i.date_inscription DESC"
)->fetchAll();

include 'includes/header.php';
?>

<h2>Gestion des Inscriptions</h2>
<?php if ($message) echo "<div class='alert alert-success alert-auto-close'>$message</div>"; ?>

<form method="POST" class="mb-5">
    <?php if ($edit) echo '<input type="hidden" name="id" value="' . $edit['id'] . '">'; ?>

    <div class="row g-3">
        <div class="col-md-5">
            <select name="etudiant_id" class="form-select" required>
                <option value="">-- Choisir l'étudiant --</option>
                <?php foreach ($etudiants as $e): ?>
                    <option value="<?= $e['id'] ?>" <?= ($edit['etudiant_id'] ?? 0) == $e['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['nom_complet']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-5">
            <select name="formation_id" class="form-select" required>
                <option value="">-- Choisir la formation --</option>
                <?php foreach ($formations as $f): ?>
                    <option value="<?= $f['id'] ?>" <?= ($edit['formation_id'] ?? 0) == $f['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($f['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="date_inscription" class="form-control"
                value="<?= $edit['date_inscription'] ?? date('Y-m-d') ?>">
        </div>
    </div>

    <div class="mt-3">
        <select name="statut" class="form-select w-50">
            <option value="en cours" <?= ($edit['statut'] ?? '') == 'en cours'   ? 'selected' : '' ?>>En cours</option>
            <option value="terminée" <?= ($edit['statut'] ?? '') == 'terminée'   ? 'selected' : '' ?>>Terminée</option>
            <option value="abandonnée" <?= ($edit['statut'] ?? '') == 'abandonnée' ? 'selected' : '' ?>>Abandonnée</option>
        </select>
    </div>

    <button type="submit" class="btn btn-success mt-3"><?= $edit ? 'Modifier' : 'Enregistrer' ?> l'inscription</button>
</form>

<table class="table table-hover table-bordered">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Étudiant</th>
            <th>Formation</th>
            <th>Date</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($inscriptions as $i): ?>
            <tr>
                <td><?= $i['id'] ?></td>
                <td><?= htmlspecialchars($i['etudiant']) ?></td>
                <td><?= htmlspecialchars($i['formation']) ?></td>
                <td><?= date('d/m/Y', strtotime($i['date_inscription'])) ?></td>
                <td>
                    <span class="badge bg-<?= $i['statut'] == 'en cours' ? 'primary' : ($i['statut'] == 'terminée' ? 'success' : 'danger') ?>">
                        <?= ucfirst($i['statut']) ?>
                    </span>
                </td>
                <td>
                    <a href="?action=edit&id=<?= $i['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                    <a href="?delete=<?= $i['id'] ?>" data-confirm="Confirmer la suppression ?" class="btn btn-sm btn-danger">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($inscriptions)): ?>
            <tr>
                <td colspan="6" class="text-center">Aucune inscription enregistrée</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>