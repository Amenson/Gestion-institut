<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formtec - Gestion Institut</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Formtec</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= $current === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">Tableau de bord</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current === 'etudiants.php' ? 'active' : '' ?>" href="etudiants.php">Étudiants</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current === 'formateurs.php' ? 'active' : '' ?>" href="formateurs.php">Formateurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current === 'formations.php' ? 'active' : '' ?>" href="formations.php">Formations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current === 'inscriptions.php' ? 'active' : '' ?>" href="inscriptions.php">Inscriptions</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <?php if (isset($_SESSION['user'])): ?>
                        <span class="navbar-text text-white me-3"> <?= htmlspecialchars($_SESSION['user']) ?></span>
                        <a class="nav-link text-danger p-0" href="logout.php">Déconnexion</a>
                    <?php else: ?>
                        <a class="nav-link text-white p-0" href="login.php">Connexion</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-4">