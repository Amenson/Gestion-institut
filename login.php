<?php require 'conn.php'; 
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_POST) {
    // Connexion simplifiée : seul le mot de passe est vérifié (pour démo)
    if ($_POST['password'] === 'admin123') {
        $_SESSION['user'] = 'Admin';
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Mot de passe incorrect";
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header text-center bg-primary text-white">
                <h4>Connexion Admin</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="POST">
                    <div class="mb-3">
                        <input type="text" name="username" class="form-control" placeholder="Nom d'utilisateur" required value="admin">
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Mot de passe" required value="admin123">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>