<?php
session_start();
require_once '/xampp/htdocs/PFE/include/conexion.php';

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupération via URL
$category = $_GET['service_name'] ?? '';
$id_categorie = isset($_GET['id_categorie']) && is_numeric($_GET['id_categorie']) ? $_GET['id_categorie'] : '';
$id_service = isset($_GET['id_service']) && is_numeric($_GET['id_service']) ? $_GET['id_service'] : '';
$phone = $_GET['phone'] ?? '';
$ville = $_GET['ville'] ?? ''; // Ajout de la récupération de ville si passée

// Validate id_categorie
if ($id_categorie) {
    $stmt = $pdo->prepare("SELECT id_categorie FROM categorie WHERE id_categorie = ?");
    $stmt->execute([$id_categorie]);
    if (!$stmt->fetch()) {
        $id_categorie = '';
        echo "<p class='error'>❌ Catégorie invalide.</p>";
    }
}

// Validate id_service
if ($id_service) {
    $stmt = $pdo->prepare("SELECT id_service FROM service WHERE id_service = ?");
    $stmt->execute([$id_service]);
    if (!$stmt->fetch()) {
        $id_service = '';
        echo "<p class='error'>❌ Service invalide.</p>";
    }
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_categorie = $_POST['id_categorie'] ?? '';
    $id_service = $_POST['id_service'] ?? '';
    $date_service = $_POST['service-date'] ?? '';
    $time_service = $_POST['service-time'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $ville = $_POST['city'] ?? '';
    $status = $_POST['status'] ?? '';

    // Combine date and time into a single datetime string
    if (!empty($date_service) && !empty($time_service)) {
        $date_service = date('Y-m-d H:i:s', strtotime("$date_service $time_service"));
    } elseif (!empty($date_service)) {
        $date_service = date('Y-m-d H:i:s', strtotime($date_service));
    } else {
        $date_service = null;
    }

    if (!empty($id_categorie) && !empty($id_service) && $date_service && !empty($phone) && !empty($ville) && !empty($status)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO reservation (
                    date_reservation, date_service, status, user_id, id_service, id_categorie, phone, ville
                ) VALUES (
                    NOW(), ?, ?, ?, ?, ?, ?, ?
                )
            ");
            $stmt->execute([
                $date_service, $status, $user_id,
                $id_service, $id_categorie, $phone, $ville
            ]);

            echo "<p class='success'>✅ Service réservé avec succès !</p>";
        } catch (PDOException $e) {
            echo "<p class='error'>❌ Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p class='warning'>❗ Veuillez remplir tous les champs.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande de Service</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="demander.css">
</head>
<body>

<?php require_once '../include/nav.php'; ?>

<main class="main">
    <div class="container">
        <div class="form-container">
            <h1 class="form-title">Validation des Services</h1>

            <form class="validation-form" method="post">
                <!-- Nom du service -->
                <div class="form-group">
                    <label>Service demandé</label>
                    <input type="text" class="form-input" value="<?= htmlspecialchars($category) ?>" readonly>
                    <input type="hidden" name="service_name" value="<?= htmlspecialchars($category) ?>">
                </div>

                <!-- Choix de la catégorie -->
                <div class="form-group">
                    <label for="id_categorie">Catégorie</label>
                    <select name="id_categorie" id="id_categorie" class="form-select" required>
                        <option value="">Sélectionner une catégorie</option>
                        <?php
                        $stmt = $pdo->query("SELECT id_categorie, nom FROM categorie");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $selected = ($row['id_categorie'] == $id_categorie) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($row['id_categorie']) . '" ' . $selected . '>' . htmlspecialchars($row['nom']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Service ID (hidden) -->
                <input type="hidden" name="id_service" value="<?= htmlspecialchars($id_service) ?>">

                <!-- Date du service -->
                <div class="form-group">
                    <label for="service-date">Date du service</label>
                    <input type="date" id="service-date" name="service-date" class="form-input" required>
                </div>

                <!-- Heure du service -->
                <div class="form-group">
                    <label for="service-time">Heure du service</label>
                    <input type="time" id="service-time" name="service-time" class="form-input" required>
                </div>

                <!-- Numéro de téléphone -->
                <div class="form-group">
                    <label for="phone">Numéro de téléphone</label>
                    <input type="tel" id="phone" name="phone" class="form-input" value="<?= htmlspecialchars($phone) ?>" required>
                </div>

                <!-- Ville -->
                <div class="form-group">
                    <label for="city">Ville</label>
                    <select id="city" name="city" class="form-select" required>
                        <option value="">Sélectionner une ville</option>
                        <option value="casablanca" <?= ($ville == 'casablanca') ? 'selected' : '' ?>>Casablanca</option>
                        <option value="rabat" <?= ($ville == 'rabat') ? 'selected' : '' ?>>Rabat</option>
                        <option value="marrakech" <?= ($ville == 'marrakech') ? 'selected' : '' ?>>Marrakech</option>
                        <option value="fes" <?= ($ville == 'fes') ? 'selected' : '' ?>>Fès</option>
                    </select>
                </div>

                <!-- Statut -->
                <div class="form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="">Sélectionner le statut</option>
                        <option value="en_attente" <?= ($status == 'en_attente') ? 'selected' : '' ?>>En attente</option>
                        <option value="valide" <?= ($status == 'valide') ? 'selected' : '' ?>>Validé</option>
                        <option value="refuse" <?= ($status == 'refuse') ? 'selected' : '' ?>>Refusé</option>
                    </select>
                </div>

                <!-- Bouton -->
                <button type="submit" class="validate-btn">Valider</button>
            </form>
        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.querySelector(".validation-form");
        form.addEventListener("submit", (e) => {
            const inputs = form.querySelectorAll("[required]");
            let isValid = true;
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = "#ff4757";
                    isValid = false;
                } else {
                    input.style.borderColor = "#ccc";
                }
            });
            if (!isValid) e.preventDefault();
        });
    });
</script>

</body>
</html>