<?php
session_start(); // *** Hadi katbda session bach n3rfou wach user da5el ola la

require_once '/xamppa/htdocs/PFE/include/conexion.php'; // *** Katconnecta b la base de données

// *** Ila user ma da5elch, katsiftoh l page dyal connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"> <!-- *** Charset standard -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- *** Responsive view -->
    <title>Souka.ma - Plateforme de Services</title> <!-- *** Titre ta3 site -->
    <link rel="stylesheet" href="style.css"> <!-- *** Fichier CSS dyal design -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"> <!-- *** Icons font-awesome -->
</head>
<body>

<?php include 'include/nav.php'; ?> <!-- *** Navbar dyal site -->

<!-- Section d'accueil (Hero) -->
<section class="hero">
    <div class="container-v">
        <div class="hero-content">
            <div class="hero-left">
                <h1 class="hero-title">Bienvenue sur notre plateforme !</h1> <!-- *** Titre d'accueil -->
                <p class="hero-description">
                    Trouvez rapidement un professionnel de confiance près de chez vous pour tous vos besoins en services à domicile.
                </p> <!-- *** Description m3a mission ta3 site -->
            </div>
            <div class="hero-right">
                <div class="hero-illustration">
                    <img src="image/brand loyalty-pana.png" alt="Service Platform Illustration" class="illustration-img"> <!-- *** Image décorative -->
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section des nouveaux professionnels -->
<div class="container-b">
    <div class="group-1">
        <div class="v1">
            <h4>Nouveaux professionnels</h4> <!-- *** Titre ta3 section -->
        </div>
        <div class="v2">
            <div class="v3"></div> <!-- *** Espace vide design -->
        </div>
    </div>

    <div class="services-grid">
        <?php
        try {
            // *** Katjib les 4 nouveaux prestataires avec leurs services
            $stmt = $pdo->query("SELECT u.nom, u.ville, u.user_id, s.id_service, s.titre, s.prix, s.image, s.telephone 
                                 FROM _user u 
                                 LEFT JOIN service s ON u.user_id = s.user_id 
                                 WHERE u.is_provider = 1 
                                 LIMIT 4");

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // *** Déclaration des variables
                $avatar = "/PFE/image/human.png"; // *** Avatar par défaut
                $nom = $row['nom'];
                $ville = $row['ville'];
                $titre_service = $row['titre'] ?? 'Aucun service';
                $prix = $row['prix'] ?? 'N/A';
                $image = $row['image'] ?? 'image/default-service.jpg';
                $id_service = $row['id_service'] ?? null;
        ?>
        <div class="service-card">
            <div class="service-image">
                <img src="<?= htmlspecialchars($image); ?>" alt="<?= htmlspecialchars($titre_service); ?>"> <!-- *** Image dyal service -->
            </div>
            <div class="service-content">
                <div class="profile-header">
                    <div class="profile-info">
                        <div class="avatar">
                            <img src="<?= htmlspecialchars($avatar); ?>" alt="<?= htmlspecialchars($nom); ?>"> <!-- *** Image utilisateur -->
                        </div>
                        <div class="profile-details">
                            <div class="profile-name"><?= htmlspecialchars($nom); ?></div> <!-- *** Nom utilisateur -->
                            <div class="profile-meta">
                                <span class="location"><?= htmlspecialchars($ville); ?></span> <!-- *** Ville utilisateur -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="service-description">
                    <?= htmlspecialchars($titre_service); ?> <!-- *** Titre du service -->
                </div>
                <div class="service-footer">
                    <div class="price-section">
                        <div class="price-label">Prix</div>
                        <div class="price"><?= htmlspecialchars($prix); ?> DH</div> <!-- *** Prix dyal service -->
                    </div>
                    <a href="/PFE/services-user/detail.php?id_service=<?= htmlspecialchars($id_service); ?>&user_id=<?= htmlspecialchars($row['user_id']); ?>&service_name=<?= urlencode($titre_service); ?>" class="service-button <?= strtolower(str_replace(' ', '-', $ville)); ?>-btn">
                        Voir services <!-- *** Lien pour voir le service complet -->
                    </a>
                </div>
            </div>
        </div>
        <?php
            }
        } catch (PDOException $e) {
            echo "<p style='color:red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>"; // *** Affiche l'erreur SQL
        }
        ?>
    </div>
</div>

<!-- Section des prestataires près de chez vous -->
<div class="container">
    <h1 class="title">Prestataires près de chez vous</h1>

    <div class="providers-grid">
        <?php
        try {
            $currentUserId = $_SESSION['user_id'];

            // *** Katsawal wach user howa provider
            $stmtCheckProvider = $pdo->prepare("SELECT is_provider FROM _user WHERE user_id = ?");
            $stmtCheckProvider->execute([$currentUserId]);
            $currentUser = $stmtCheckProvider->fetch(PDO::FETCH_ASSOC);
            $isCurrentUserProvider = $currentUser['is_provider'] ?? 0;

            // *** Katsawal wach 3ando services
            $stmtCheckServices = $pdo->prepare("SELECT COUNT(*) FROM service WHERE user_id = ?");
            $stmtCheckServices->execute([$currentUserId]);
            $hasServices = $stmtCheckServices->fetchColumn() > 0;

            // *** Katjib les autres prestataires (ma chi howa)
            $stmt = $pdo->prepare("SELECT u.nom, u.ville, u.user_id, s.id_service, s.titre, s.prix, s.image, s.telephone 
                                   FROM _user u 
                                   INNER JOIN service s ON u.user_id = s.user_id 
                                   WHERE u.is_provider = 1 AND u.user_id != ? 
                                   LIMIT 4");
            $stmt->execute([$currentUserId]);

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $avatar = "/PFE/image/profile.png"; // *** Avatar l9dim
                $nom = $row['nom'];
                $ville = $row['ville'];
                $titre_service = $row['titre'] ?? 'Aucun service';
                $prix = $row['prix'] ?? 'N/A';
                $image = $row['image'] ?? '/image/profile.png';
                $id_service = $row['id_service'] ?? null;
                $buttonClass = strtolower(str_replace(' ', '-', $ville)) . '-btn'; // *** class selon ville
        ?>
        <div class="provider-card">
            <div class="profile-image">
                <img src="<?= htmlspecialchars($avatar); ?>" alt="<?= htmlspecialchars($nom); ?>" class="avatar"> <!-- *** Avatar dyal l'autre prestataire -->
            </div>
            <div class="service-badge <?= strtolower(str_replace(' ', '-', $ville)); ?>">
                <?= htmlspecialchars($ville); ?> <!-- *** Badge dyal la ville -->
            </div>
            <h3 class="provider-name"><?= htmlspecialchars($nom); ?></h3> <!-- *** Smya dyal prestataire -->
            <div class="location">
                <svg class="location-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <span><?= htmlspecialchars($ville); ?></span> <!-- *** Ville encore une fois -->
            </div>
            <a id="pr" href="/PFE/services-user/detail.php?id_service=<?= htmlspecialchars($id_service); ?>&user_id=<?= htmlspecialchars($row['user_id']); ?>&service_name=<?= urlencode($titre_service); ?>" class="service-button <?= $buttonClass ?>">
                Voir profil <!-- *** Lien vers page détail de ce prestataire -->
            </a>
        </div>
        <?php
            }
            if ($stmt->rowCount() == 0) {
                echo "<p>Aucun prestataire avec des services trouvé.</p>"; // *** Message si aucun trouvé
            }
        } catch (PDOException $e) {
            echo "<p style='color:red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>"; // *** Affichage erreur SQL
        }
        ?>
    </div>
</div>

<!-- Pied de page -->
<?php require_once './include/footer.html'; ?> <!-- *** Footer -->

<!-- Scripts JavaScript -->
<script src="script.js">

// *** Fonction favoris avec cœur rouge
function toggleFavorite(button) {
    const svg = button.querySelector('svg');
    const path = svg.querySelector('path');

    if (button.classList.contains('favorited')) {
        button.classList.remove('favorited');
        path.setAttribute('fill', 'none');
    } else {
        button.classList.add('favorited');
        path.setAttribute('fill', '#e74c3c');
    }
}

// *** Filtrage ta3 les cards selon inputs
document.addEventListener('DOMContentLoaded', () => {
    const searchBtn = document.querySelector('.search-btn');
    const serviceInput = document.querySelector('.search-inputs input[placeholder*="service"]');
    const locationInput = document.querySelector('.search-inputs input[placeholder*="ville"]');

    searchBtn?.addEventListener('click', () => {
        const service = serviceInput.value.toLowerCase();
        const location = locationInput.value.toLowerCase();
        const providers = document.querySelectorAll('.provider-card');

        providers.forEach(provider => {
            const badge = provider.querySelector('.service-badge')?.textContent.toLowerCase();
            const city = provider.querySelector('.location span')?.textContent.toLowerCase();

            if ((service && !badge.includes(service)) || (location && !city.includes(location))) {
                provider.style.display = 'none';
            } else {
                provider.style.display = 'block';
            }
        });
    });
});

// *** Boutons pour ajouter aux favoris
document.addEventListener('DOMContentLoaded', () => {
    const favoriteButtons = document.querySelectorAll('.favorite-btn');

    favoriteButtons.forEach(button => {
        button.addEventListener('click', () => {
            const userId = button.getAttribute('data-user-id');
            const icon = button.querySelector('i');

            if (button.classList.contains('favorited')) {
                button.classList.remove('favorited');
                icon.style.color = 'black';
            } else {
                button.classList.add('favorited');
                icon.style.color = 'red';
                window.location.href = `/PFE/favorit.php?user_id=${userId}`; // *** Redirection vers ajout favoris
            }
        });
    });
});
</script>

</body>
</html>
