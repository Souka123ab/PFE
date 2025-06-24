<?php
session_start();
require_once '/xampp/htdocs/PFE/include/conexion.php'; // Include database connection

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Souka.ma - Plateforme de Services</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<?php include 'include/nav.php'; ?>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container-v">
            <div class="hero-content">
                <div class="hero-left">
                    <h1 class="hero-title">Bienvenue sur notre plateforme !</h1>
                    <p class="hero-description">
                        Trouvez rapidement un professionnel de confiance près de chez vous pour tous vos besoins en services à domicile.
                    </p>
                    
                    <!-- Search Form -->
                    <div class="search-form">
                        <div class="search-inputs">
                            <div class="input-group">
                                <i class="fas fa-search input-icon"></i>
                                <input type="text" placeholder="Quel service cherchez-vous ?" class="search-input">
                            </div>
                            <div class="input-group">
                                <i class="fas fa-map-marker-alt input-icon"></i>
                                <input type="text" placeholder="Votre ville ou code postal" class="search-input">
                            </div>
                        </div>
                        <button class="search-btn">Rechercher</button>
                    </div>
                </div>

                <div class="hero-right">
                    <div class="hero-illustration">
                        <img src="image/brand loyalty-pana.png" alt="Service Platform Illustration" class="illustration-img">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nouveaux Professionnels Section -->
    <div class="container-b">
        <div class="group-1">
            <div class="v1">
                <h4>Nouveaux professionnels</h4>
            </div>
            <div class="v2">
                <div class="v3">
                    <!-- Navigation arrows can be added here if needed -->
                    <!-- <a href="#" class="view-all-link"><i class="fas fa-arrow-left"></i></a>
                    <a href="#" class="view-all-link"><i class="fas fa-arrow-right"></i></a> -->
                </div>
            </div>
        </div>
        <div class="services-grid">
            <?php
            try {
                $stmt = $pdo->query("SELECT u.nom, u.ville, u.user_id, s.titre, s.prix, s.image, s.telephone 
                                   FROM _user u 
                                   LEFT JOIN service s ON u.user_id = s.user_id 
                                   WHERE u.is_provider = 1 
                                   LIMIT 4");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $avatar = "image/default-avatar.png";
                    $nom = $row['nom'];
                    $ville = $row['ville'];
                    $titre_service = $row['titre'] ?? 'Aucun service';
                    $prix = $row['prix'] ?? 'N/A';
                    $image = $row['image'] ?? 'image/default-service.jpg';
            ?>
            <div class="service-card">
                <div class="service-image">
                    <img src="<?= htmlspecialchars($image); ?>" alt="<?= htmlspecialchars($titre_service); ?>">
                </div>
                <div class="service-content">
                    <div class="profile-header">
                        <div class="profile-info">
                            <div class="avatar">
                                <img src="<?= htmlspecialchars($avatar); ?>" alt="<?= htmlspecialchars($nom); ?>">
                            </div>
                            <div class="profile-details">
                                <div class="profile-name"><?= htmlspecialchars($nom); ?></div>
                                <div class="profile-meta">
                                    <span class="location"><?= htmlspecialchars($ville); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="service-description">
                        <?= htmlspecialchars($titre_service); ?>
                    </div>
                    <div class="service-footer">
                        <div class="price-section">
                            <div class="price-label">Prix</div>
                            <div class="price"><?= htmlspecialchars($prix); ?> DH</div>
                        </div>
                        <a href="/PFE/services-user/detail.php?user_id=<?= htmlspecialchars($row['user_id']); ?>" class="service-button <?= strtolower(str_replace(' ', '-', $ville)); ?>-btn">
                            Voir services
                        </a>
                        <!-- <button class="favorite-btn" data-user-id="<?= htmlspecialchars($row['user_id']); ?>">
                            <i class="fas fa-heart"></i>
                        </button> -->
                    </div>
                </div>
            </div>
            <?php
                }
            } catch (PDOException $e) {
                echo "<p style='color:red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            ?>
        </div>
    </div>

    <!-- Prestataires près de chez vous Section -->
    <div class="container">
        <h1 class="title">Prestataires près de chez vous</h1>
        
        <div class="providers-grid">
            <?php
            try {
                // Check if the current user is a provider and has services
                $currentUserId = $_SESSION['user_id'];
                $stmtCheckProvider = $pdo->prepare("SELECT is_provider FROM _user WHERE user_id = ?");
                $stmtCheckProvider->execute([$currentUserId]);
                $currentUser = $stmtCheckProvider->fetch(PDO::FETCH_ASSOC);
                $isCurrentUserProvider = $currentUser['is_provider'] ?? 0;

                $stmtCheckServices = $pdo->prepare("SELECT COUNT(*) FROM service WHERE user_id = ?");
                $stmtCheckServices->execute([$currentUserId]);
                $hasServices = $stmtCheckServices->fetchColumn() > 0;

                // Query providers with services
                if ($isCurrentUserProvider && $hasServices) {
                    // If the current user is a provider and has services, show other providers with services excluding the current user
                    $stmt = $pdo->prepare("SELECT u.nom, u.ville, u.user_id, s.titre, s.prix, s.image, s.telephone 
                                         FROM _user u 
                                         INNER JOIN service s ON u.user_id = s.user_id 
                                         WHERE u.is_provider = 1 AND u.user_id != ? 
                                         LIMIT 4");
                    $stmt->execute([$currentUserId]);
                } else {
                    // If the current user is not a provider or has no services, show other providers with services
                    $stmt = $pdo->prepare("SELECT u.nom, u.ville, u.user_id, s.titre, s.prix, s.image, s.telephone 
                                         FROM _user u 
                                         INNER JOIN service s ON u.user_id = s.user_id 
                                         WHERE u.is_provider = 1 AND u.user_id != ? 
                                         LIMIT 4");
                    $stmt->execute([$currentUserId]);
                }

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $avatar = "/PFE/image/profile.png";
                    $nom = $row['nom'];
                    $ville = $row['ville'];
                    $titre_service = $row['titre'] ?? 'Aucun service';
                    $prix = $row['prix'] ?? 'N/A';
                    $image = $row['image'] ?? '/image/profile.png';
                    $buttonClass = strtolower(str_replace(' ', '-', $ville)) . '-btn';
            ?>
            <div class="provider-card">
                <div class="profile-image">
                    <img src="<?= htmlspecialchars($avatar); ?>" alt="<?= htmlspecialchars($nom); ?>" class="avatar">
                </div>
                <div class="service-badge <?= strtolower(str_replace(' ', '-', $ville)); ?>">
                    <?= htmlspecialchars($ville); ?>
                </div>
                <h3 class="provider-name"><?= htmlspecialchars($nom); ?></h3>
                <div class="location">
                    <svg class="location-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span><?= htmlspecialchars($ville); ?></span>
                </div>
                <a id="pr" href="/PFE/pestataire/pestataire-compte.php?user_id=<?= htmlspecialchars($row['user_id']); ?>" class="service-button <?= $buttonClass ?>">
                    Voir profil
                </a>
            </div>
            <?php
                }
                // If no providers with services are found, display a message
                if ($stmt->rowCount() == 0) {
                    echo "<p>Aucun prestataire avec des services trouvé.</p>";
                }
            } catch (PDOException $e) {
                echo "<p style='color:red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <?php
     require_once './include/footer.html';
    ?>

    <script src="script.js">
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

        document.addEventListener('DOMContentLoaded', () => {
            const searchBtn = document.querySelector('.search-btn');
            const serviceInput = document.querySelector('.search-inputs input[placeholder*="service"]');
            const locationInput = document.querySelector('.search-inputs input[placeholder*="ville"]');

            searchBtn.addEventListener('click', () => {
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
        document.addEventListener('DOMContentLoaded', () => {
            const favoriteButtons = document.querySelectorAll('.favorite-btn');

            favoriteButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const userId = button.getAttribute('data-user-id');
                    const icon = button.querySelector('i');

                    // Toggle the favorite state
                    if (button.classList.contains('favorited')) {
                        button.classList.remove('favorited');
                        icon.style.color = 'black'; // Reset color when unfavorited
                    } else {
                        button.classList.add('favorited');
                        icon.style.color = 'red'; // Change color when favorited
                        // Redirect to favorit.php with user_id
                        window.location.href = `/PFE/favorit.php?user_id=${userId}`;
                    }
                });
            });
        });
    </script>
</body>
</html>