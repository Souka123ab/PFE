<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Souka.ma - Contact</title>
    <link rel="stylesheet" href="contact.css">
</head>
<body>
    <!-- Header -->
<?php
require_once '../include/nav.php';
?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="contact-container">
            <!-- Contact Info -->
            <div class="contact-info">
                <h2>Contactez-nous</h2>
                <div class="contact-details">
                    <div class="contact-item">
                        <p>soukaynamachraa1@gmail.com</p>
                    </div>
                    <div class="contact-item">
                        <p>+212 604773687</p>
                    </div>
                    <div class="contact-item">
                        <p>Souka.ma</p>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form-section">
                <h2>Entrer en contact</h2>
                <p>N'hésitez pas à nous écrire ci-dessous !</p>
                
                <form id="contactForm">
                    <div class="form-group">
                        <input type="text" class="form-input" placeholder="Entrer ton nom" required>
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-input" placeholder="Entrer ton e-mail" required>
                    </div>
                    <div class="form-group">
                        <textarea class="form-input form-textarea" placeholder="Tapez votre message ici..." required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Envoyer</button>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
   <?php
   require_once '../include/footer.html';
   ?>

    <script>
        // Form submission handler
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const name = this.querySelector('input[type="text"]').value;
            const email = this.querySelector('input[type="email"]').value;
            const message = this.querySelector('textarea').value;
            
            // Simple validation
            if (!name || !email || !message) {
                alert('Veuillez remplir tous les champs');
                return;
            }
            
            // Simulate form submission
            alert('Merci pour votre message ! Nous vous répondrons bientôt.');
            this.reset();
        });

        // Mobile menu toggle (if needed)
        function toggleMobileMenu() {
            const nav = document.querySelector('.nav');
            nav.classList.toggle('mobile-open');
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>