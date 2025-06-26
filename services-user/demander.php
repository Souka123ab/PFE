<?php
session_start(); 
// * Katbda session bach t9der tkhli user mconnecté

require_once '/xamppa/htdocs/PFE/include/conexion.php'; 
// * Katd5ol fichier dyal connexion m3a base de données

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) { 
    // * Ila ma kaynash user mconnecti, radi ndir redirect l login
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}

// Vérifier si id_service m3tah f URL
if(isset($_GET['id_service'])){
   $id_service = $_GET['id_service'];
   $stmt = $pdo->prepare("SELECT * FROM service WHERE id_service = ?");
   $stmt->execute([$id_service]);
   $service = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
   // * Ila ma 3tawnach id_service f URL, ndir redirect l liste dyal services dyal user
   header("Location: /PFE/services-user/services-user.php");
   exit;
}

// Récupération des données depuis URL ou variables
$category = $_GET['service_name'] ?? '';  
// * Katkhod isem service/category mn URL, ila ma kaynach khaliha fargha

$id_categorie = isset($_GET['id_categorie']) && is_numeric($_GET['id_categorie']) ? $_GET['id_categorie'] : '';
// * Kat5tar id dyal categorie ila kayn w valid (numerique)

$id_service = isset($_GET['id_service']) && is_numeric($_GET['id_service']) ? $_GET['id_service'] : '';
// * Kat5tar id dyal service men URL ila kayn w valid

$phone = $_GET['phone'] ?? '';
// * Katkhod phone mn URL ila kayn

$ville = $_GET['ville'] ?? ''; 
// * Katkhod ville mn URL ila kayn

// Validation dyal id_categorie f base
if ($id_categorie) {
    $stmt = $pdo->prepare("SELECT id_categorie FROM categorie WHERE id_categorie = ?");
    $stmt->execute([$id_categorie]);
    if (!$stmt->fetch()) {
        // * Ila ma tla3hach categorie, kanb9aw khaliin id_categorie w n3tiw message error
        $id_categorie = '';
        echo "<p class='error'>❌ Catégorie invalide.</p>";
    }
}

// Validation dyal id_service f base
if ($id_service) {
    $stmt = $pdo->prepare("SELECT id_service FROM service WHERE id_service = ?");
    $stmt->execute([$id_service]);
    if (!$stmt->fetch()) {
        // * Ila ma tla3hach service, kanb9aw khaliin id_service w n3tiw message error
        $id_service = '';
        echo "<p class='error'>❌ Service invalide.</p>";
    }
}

// Traitement formulaire quand submit POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_categorie = $_POST['id_categorie'] ?? '';
    $id_service = $_POST['id_service'] ?? '';
    $date_service = $_POST['service-date'] ?? '';
    $time_service = $_POST['service-time'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $ville = $_POST['city'] ?? '';
    // $status = $_POST['status'] ?? ''; // (optionnel, pas utilisé ici)

    // Combinaison date + heure en datetime unique
    if (!empty($date_service) && !empty($time_service)) {
        $date_service = date('Y-m-d H:i:s', strtotime("$date_service $time_service"));
    } elseif (!empty($date_service)) {
        $date_service = date('Y-m-d H:i:s', strtotime($date_service));
    } else {
        $date_service = null;
    }

    // Validation que tous les champs obligatoires sont remplis
    if (!empty($id_categorie) && !empty($id_service) && $date_service && !empty($phone) && !empty($ville)) {
        try {
            // Insertion réservation dans base
            $stmt = $pdo->prepare("
                INSERT INTO reservation (
                    date_reservation, date_service, user_id, id_service, id_categorie, phone, ville
                ) VALUES (
                    NOW(), ?, ?, ?, ?, ?, ?
                )
            ");
            $stmt->execute([
                $date_service, $_SESSION['user_id'],
                $id_service, $id_categorie, $phone, $ville
            ]);
            echo "<p class='success'>✅ Service réservé avec succès !</p>";
        } catch (PDOException $e) {
            // Affichage erreur SQL si problème
            echo "<p class='error'>❌ Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        // Message si un ou plusieurs champs obligatoires manquent
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
<!-- * Nav bar externe -->

<main class="main">
    <div class="container">
        <div class="form-container">
            <h1 class="form-title">Validation des Services</h1>

            <form class="validation-form" method="post">
                <!-- Nom du service (readonly) -->
                <div class="form-group">
                    <label>Service demandé</label>
                    <input type="text" class="form-input" value="<?= htmlspecialchars($category) ?>" readonly>
                    <input type="hidden" name="service_name" value="<?= htmlspecialchars($category) ?>">
                </div>

                <!-- Choix catégorie -->
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

                <!-- ID service caché -->
                <input type="hidden" name="id_service" value="<?= htmlspecialchars($id_service) ?>">

                <!-- Date service -->
                <div class="form-group">
                    <label for="service-date">Date du service</label>
                    <input type="date" id="service-date" name="service-date" class="form-input" required>
                </div>

                <!-- Heure service -->
                <div class="form-group">
                    <label for="service-time">Heure du service</label>
                    <input type="time" id="service-time" name="service-time" class="form-input" required>
                </div>

                <!-- Téléphone -->
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

                <!-- Bouton validation -->
                <button type="submit" class="validate-btn">Valider</button>
            </form>
        </div>
    </div>
</main>

<script>
    // Validation JS simple pour inputs obligatoires
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.querySelector(".validation-form");
        form.addEventListener("submit", (e) => {
            const inputs = form.querySelectorAll("[required]");
            let isValid = true;
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = "#ff4757"; // rouge si vide
                    isValid = false;
                } else {
                    input.style.borderColor = "#ccc"; // gris si ok
                }
            });
            if (!isValid) e.preventDefault(); // bloquer submit si un champ vide
        });
    });
</script>

</body>
</html>
