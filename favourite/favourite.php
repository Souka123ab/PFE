<?php
session_start();
require_once '/xampp/htdocs/PFE/include/conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}

if (isset($_GET['id_service']) && is_numeric($_GET['id_service'])) {
    // Add to favorites (e.g., insert into a 'favorites' table)
    // Example: $pdo->prepare("INSERT INTO favorites (user_id, id_service) VALUES (?, ?)");
    echo "<p style='color: green;'>Service ajouté aux favoris.</p>";
}

header("Location: /PFE/services-user/services-user.php");
exit;
?>