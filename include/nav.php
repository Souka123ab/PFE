<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Souka.ma - Plateforme de Services</title>
    <link rel="stylesheet" href="/PFE/include/nav.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container-n">
            <div class="header-content">
                <!-- Navigation avec Logo -->
                <nav class="nav">
                    <!-- Hamburger Menu -->


                    <div class="logo">
                        <img src="/PFE/image/Capture d'écran 2025-06-16 131020.png" alt="Souka.ma Logo" class="logo-img">
                    </div>
                    <a href="/PFE/acceuil.php" id="acceuil" class="nav-link">Accueil</a>
                    <a href="/PFE/services-user/services-user.php" id="services" class="nav-link">Services</a>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/PFE/services-user/demander.php" id="demander" class="nav-link">Demander Un Service</a>
                        <a href="/PFE/contact/contact.php" class="nav-link" id="contact">Contact</a>
                        <a href="/PFE/services-user/moncompteuser.php" class="nav-link" id="mon-compte">Mon Compte</a>
                        <a href="/PFE/include/déconnexion.php" class="nav-link">Déconnexion</a>
                    <?php else: ?>
                        <a href="/PFE/auth/seconnecter.php" class="nav-link">Connexion</a>
                        <a href="/PFE/auth/s'inscrire.php" class="nav-link">Inscription</a>
                    <?php endif; ?>
                </nav>

                <!-- Actions Header -->
                <div class="header-actions">
                    <button class="notification-btn">
                        
                        <i class="fas fa-bell"></i>
                    </button>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="btnn btn-primary">
                            <a href="/PFE/pestataire/publier-anance-pestatire.php">
                            <i class="fas fa-plus"></i>
                            Publier une annonce
                        </button>
                        <button class="btnn btn-secondary">
                            <a href="/PFE/Favourite/favourite.php">
                                <i class="fas fa-heart"></i>
                                Favorit
                            </a>
                        </button>
                    <?php else: ?>
                        <button class="btnn btn-primary">
                            <a href="/PFE/auth/seconnecter.php">
                                <i class="fas fa-plus"></i>
                                Publier
                            </a>
                        </button>
                        <button class="btnn btn-secondary">
                            <a href="/PFE/auth/seconnecter.php">
                                <i class="fas fa-heart"></i>
                                Favorit
                            </a>
                        </button>
                    <?php endif; ?>
                     <button class="hamburger" id="hamburger">
                 <i class="fas fa-bars"></i>
                </button>
                </div>
                 
            </div>
             
        </div>
      
    </header>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(function(link) {
          link.addEventListener('click', function() {
            navLinks.forEach(function(l) {
              l.classList.remove('active');
            });
            this.classList.add('active');
          });
        });
      });
     
  document.addEventListener('DOMContentLoaded', function () {
    const hamburger = document.getElementById('hamburger');
    const nav = document.querySelector('.nav');

    hamburger.addEventListener('click', function () {
      nav.classList.toggle('active');
    });

    // Facultatif : Fermer le menu quand on clique sur un lien
    document.querySelectorAll('.nav-link').forEach(link => {
      link.addEventListener('click', () => {
        nav.classList.remove('active');
      });
    });
  });


    </script>
</body>
</html>
