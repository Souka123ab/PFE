<?php
session_start(); // ⭐ Démarre la session pour accéder aux infos de l'utilisateur connecté
require_once '/xamppa/htdocs/PFE/include/conexion.php'; // ⭐ Connexion à la base de données

// ⭐ Vérifie si l'utilisateur est connecté, sinon redirige vers page login
if (!isset($_SESSION['user_id'])) {
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}

// ⭐ Si l'utilisateur clique sur "Devenir Prestataire"
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['become_provider'])) {
    try {
        // ⭐ On change son statut dans la base de données
        $stmt = $pdo->prepare("UPDATE _user SET is_provider = 1 WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        // ⭐ On met à jour la session pour qu’il soit reconnu comme prestataire
        $_SESSION['is_prestataire'] = true;

        // ⭐ Redirige vers la page de compte prestataire
        header("Location: /PFE/pestataire/pestataire-compte.php");
        exit;
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// ⭐ Si l’utilisateur met à jour ses informations de profil
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    $nom = $_POST['nom'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $ville = $_POST['ville'] ?? '';

    try {
        // ⭐ Met à jour le nom, numéro, ville dans la base
        $stmt = $pdo->prepare("UPDATE _user SET nom = ?, numero = ?, ville = ? WHERE user_id = ?");
        $stmt->execute([$nom, $numero, $ville, $_SESSION['user_id']]);

        // ⭐ Recharge la même page pour voir les changements
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Erreur mise à jour : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// ⭐ Récupération des infos utilisateur pour pré-remplir le formulaire
$user = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM _user WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ⭐ Mets à jour la session avec le nouveau statut (prestataire ou non)
    $_SESSION['is_prestataire'] = ($user['is_provider'] ?? 0) == 1 ? true : false;

    // ⭐ Si nom vide dans la base, utilise celui de la session
    if (empty($user['nom']) && isset($_SESSION['nom'])) {
        $user['nom'] = $_SESSION['nom'];
    }
} catch (PDOException $e) {
    echo "<p style='color:red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Souka.ma - Mon Compte</title>
    <link rel="stylesheet" href="moncomptuser.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<?php include '../include/nav.php'; ?> <!-- ⭐ Navigation haut de page -->

<main class="main">
    <div class="container">
        <div class="profile-section">
            <div class="profile-avatar">
                <div class="avatar-placeholder"><i class="fas fa-user"></i></div> <!-- ⭐ Avatar par défaut -->
            </div>

            <h2>Bienvenue, <?php echo htmlspecialchars($user['nom'] ?? 'Utilisateur'); ?>!</h2> <!-- ⭐ Affiche nom de l’utilisateur -->

            <?php if (!$_SESSION['is_prestataire']): ?> <!-- ⭐ Si utilisateur n’est pas encore prestataire -->
                <form method="post" style="display:inline;">
                    <button type="submit" name="become_provider" class="become-provider-btn">
                        Devenir Prestataire
                    </button>
                </form>
            <?php else: ?> <!-- ⭐ S’il est déjà prestataire -->
                <div class="pestataire">
                    <p>Vous êtes déjà un prestataire. 
                       <a href="/PFE/pestataire/pestataire-compte.php">Voir votre profil prestataire</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- ⭐ Formulaire mise à jour du profil -->
        <div class="form-section">
            <form class="profile-form" method="post" action="">
                <div class="form-group">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" id="nom" name="nom" class="form-input"
                        value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="telephone" class="form-label">Numéro de téléphone</label>
                    <input type="tel" id="telephone" name="numero" class="form-input"
                        value="<?php echo htmlspecialchars($user['numero'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="ville" class="form-label">Ville</label>
                    <div class="select-wrapper">
                        <!-- ⭐ Sélecteur de ville avec valeur actuelle sélectionnée -->
                        <select id="ville" name="ville" class="form-select">
                            <option value="taounate" <?php echo ($user['ville'] ?? '') === 'taounate' ? 'selected' : ''; ?>>Taounate</option>
                            <option value="rabat" <?php echo ($user['ville'] ?? '') === 'rabat' ? 'selected' : ''; ?>>Rabat</option>
                            <option value="casablanca" <?php echo ($user['ville'] ?? '') === 'casablanca' ? 'selected' : ''; ?>>Casablanca</option>
                            <option value="fes" <?php echo ($user['ville'] ?? '') === 'fes' ? 'selected' : ''; ?>>Fès</option>
                            <option value="marrakech" <?php echo ($user['ville'] ?? '') === 'marrakech' ? 'selected' : ''; ?>>Marrakech</option>
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                    </div>
                </div>

                <!-- ⭐ Bouton de mise à jour -->
                <button type="submit" name="update_profile" class="update-btn">Mettre à jour</button>
            </form>
        </div>
    </div>
</main>

<?php require_once '../include/footer.html'; ?> <!-- ⭐ Pied de page -->

<!-- ⭐ Script JS pour formater le numéro de téléphone -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const phoneInput = document.getElementById('telephone');
        phoneInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, ''); // Supprime tout sauf chiffres
            if (value.length > 0 && !value.startsWith('212')) {
                if (value.startsWith('0')) {
                    value = '212' + value.substring(1); // remplace 0 par 212
                } else if (value.length === 9) {
                    value = '212' + value;
                }
            }
            if (value.startsWith('212')) {
                value = '+212 ' + value.substring(3); // ajoute +212
            }
            e.target.value = value;
        });
    });
</script>

</body>
</html>
