<?php
// conn.php

// Ne pas mettre de session_start() ici si déjà fait dans les pages

$host     = 'localhost';
$dbname   = 'formtec';           // ← vérifie que c'est exactement le nom de ta base
$username = 'root';              // ← habituel sur WAMP
$password = '';                  // ← vide par défaut sur WAMP local

$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    // Pour debug temporaire (à commenter après) :
    // error_log("Connexion PDO OK - " . date('Y-m-d H:i:s'));
} catch (PDOException $e) {
    // Important : affiche l'erreur réelle en développement
    header('Content-Type: text/plain; charset=utf-8');
    die("Erreur de connexion à la base de données :\n" . $e->getMessage());
}