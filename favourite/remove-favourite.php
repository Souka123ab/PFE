<?php
session_start();
require_once '../include/conexion.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id_service'])) {
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$id_service = intval($_GET['id_service']);

$stmt = $pdo->prepare("DELETE FROM favoris WHERE user_id = ? AND service_id = ?");
$stmt->execute([$user_id, $id_service]);

header("Location: /PFE/favourite/favourite.php");
exit;
?>