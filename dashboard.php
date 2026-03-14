<?php

require_once __DIR__ . '/conn.php';

// Protection : vérifie que la connexion a réussi
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("<h2 style='color:red'>Erreur : connexion à la base impossible</h2>"
        . "<p>Vérifiez que <code>conn.php</code> existe et fonctionne correctement.</p>"
        . "<p>Essayez d'ouvrir directement : http://localhost/Gestion/conn.php</p>");
}


include 'includes/header.php';

// Statistiques (maintenant $pdo existe)
$stats = [
    'etudiants'   => $pdo->query("SELECT COUNT(*) FROM etudiants")->fetchColumn(),
    'formateurs'  => $pdo->query("SELECT COUNT(*) FROM formateurs")->fetchColumn(),
    'formations'  => $pdo->query("SELECT COUNT(*) FROM formations")->fetchColumn(),
    'inscriptions' => $pdo->query("SELECT COUNT(*) FROM inscriptions")->fetchColumn()
];
?>
<!-- Le reste de ton HTML sans changement -->
<h1 class="text-center mb-4">Tableau de bord - Institut Formtec</h1>

<div class="row">
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h3><?= $stats['etudiants'] ?></h3>
                <p>Étudiants</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h3><?= $stats['formateurs'] ?></h3>
                <p>Formateurs</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h3><?= $stats['formations'] ?></h3>
                <p>Formations</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h3><?= $stats['inscriptions'] ?></h3>
                <p>Inscriptions</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>