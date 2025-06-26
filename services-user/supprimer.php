<?php
session_start(); // *** Katbda session bach n3rfou l'utilisateur li da5el

require_once '/xamppa/htdocs/PFE/include/conexion.php'; // *** Katconnecta b la base de données

// *** Ila user ma da5elch, katsiftoh l page de connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}

// *** Katchecki wach kayn id_service f lien (GET) w wach howa numéro
if (isset($_GET['id_service']) && is_numeric($_GET['id_service'])) {
    try {
        // *** Katjib titre w date dyal service li bghina nsuppromiw, walakin khas user howa li 3ndo
        $stmt = $pdo->prepare("SELECT titre, date FROM service WHERE id_service = ? AND user_id = ?");
        $stmt->execute([$_GET['id_service'], $_SESSION['user_id']]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC); // *** Kat7et résultat f variable

        if ($service) {
            // *** Ila lservice kayn w howa dyal user, katdir suppression
            $stmt = $pdo->prepare("DELETE FROM service WHERE id_service = ? AND user_id = ?");
            $stmt->execute([$_GET['id_service'], $_SESSION['user_id']]);

            // *** Kat7et message f session bach taffichi f page ba3d
            $_SESSION['service_supprime'] = [
                'titre' => $service['titre'], // *** titre dyal service li tsuprimi
                'date' => date('d/m/Y H:i:s') // *** wa9t li tms7 fih
            ];
        }
    } catch (PDOException $e) {
        // *** Ila wa9a chi erreur f la base de données, kataffichiha
        echo "<p style='color: red;'>Erreur lors de la suppression : " . htmlspecialchars($e->getMessage()) . "</p>";
        exit;
    }
}

// *** Bach n3awd nredirectiw l'utilisateur l page dyal ses services
header("Location: /PFE/services-user/services-user.php");
exit;
?>
