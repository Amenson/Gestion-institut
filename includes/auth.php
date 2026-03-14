<?php
session_start();

// -------------------------------
// 1. Configuration de base
// -------------------------------
define('LOGIN_PAGE', 'login.php');
define('DASHBOARD_PAGE', 'dashboard.php');
define('SESSION_USER_KEY', 'user');           // clé de session pour l'utilisateur connecté
define('SESSION_TIMEOUT', 1800);              // 30 minutes d'inactivité → déconnexion auto (en secondes)

// -------------------------------
// 2. Fonction : est-ce que l'utilisateur est connecté ?
// -------------------------------
function isLoggedIn(): bool {
    if (!isset($_SESSION[SESSION_USER_KEY])) {
        return false;
    }

    // Vérification du timeout de session (optionnel mais recommandé)
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        logout();
        return false;
    }

    // Mise à jour du timestamp de dernière activité
    $_SESSION['last_activity'] = time();

    return true;
}

// -------------------------------
// 3. Fonction : redirection si non connecté
// -------------------------------
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . LOGIN_PAGE);
        exit;
    }
}

// -------------------------------
// 4. Fonction : déconnexion complète
// -------------------------------
function logout(): void {
    // Vide toutes les variables de session
    $_SESSION = [];

    // Détruit la session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();

    // Redirection vers la page de login
    header('Location: ' . LOGIN_PAGE);
    exit;
}

// -------------------------------
// 5. Protection automatique des pages
//    (exécuté à chaque inclusion de auth.php)
// -------------------------------
if (basename($_SERVER['PHP_SELF']) !== LOGIN_PAGE) {
    requireLogin();
}

// -------------------------------
// 6. Optionnel : régénération d'ID de session après connexion réussie
//    (à appeler après login réussi dans login.php)
// -------------------------------
function regenerateSessionAfterLogin(): void {
    session_regenerate_id(true);
    $_SESSION['last_activity'] = time();
}