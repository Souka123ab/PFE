<?php
session_start();
require_once '/xampp/htdocs/PFE/include/conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}

if (isset($_GET['id_service']) && is_numeric($_GET['id_service'])) {
    try {
        $stmt = $pdo->prepare("SELECT titre, date FROM service WHERE id_service = ? AND user_id = ?");
        $stmt->execute([$_GET['id_service'], $_SESSION['user_id']]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($service) {
            $stmt = $pdo->prepare("DELETE FROM service WHERE id_service = ? AND user_id = ?");
            $stmt->execute([$_GET['id_service'], $_SESSION['user_id']]);
            $_SESSION['service_supprime'] = ['titre' => $service['titre'], 'date' => date('d/m/Y H:i:s')];
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Erreur lors de la suppression : " . htmlspecialchars($e->getMessage()) . "</p>";
        exit;
    }
}

header("Location: /PFE/services-user/services-user.php");
exit;
?>