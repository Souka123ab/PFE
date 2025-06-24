<?php

session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: ../acceuil.php");
    exit;
}

// Affichage des erreurs (à désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données
require_once '../include/conexion.php';

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['google_signup']) && $_POST['google_signup'] == 'true') {
        $first_name = $_POST['firstName'] ?? 'Google';
        $last_name = $_POST['lastName'] ?? 'User';
        $email = $_POST['email'] ?? 'user@google.com';
        $numero = $_POST['numero'] ?? '1234567890';
        $password_hashed = password_hash(uniqid('google_pass_'), PASSWORD_DEFAULT);

        $check_sql = "SELECT user_id FROM _user WHERE email = ?";
        $check_stmt = $pdo->prepare($check_sql);

        try {
            $check_stmt->execute([$email]);
            $existing_user = $check_stmt->fetch();

            if ($existing_user) {
                $error_message = "Un compte avec cette adresse e-mail existe déjà.";
            } else {
                $sql = "INSERT INTO _user (nom, numero, email, password) VALUES (:nom, :numero, :email, :password)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'nom' => $first_name . ' ' . $last_name,
                    'numero' => $numero,
                    'email' => $email,
                    'password' => $password_hashed,
                ]);
                $success_message = "Compte Google créé avec succès.";
            }
        } catch (PDOException $e) {
            error_log("Erreur Google Signup: " . $e->getMessage());
            $error_message = "Erreur lors de la création du compte: " . $e->getMessage();
        }

    } else {
        $first_name = $_POST['firstName'] ?? '';
        $last_name = $_POST['lastName'] ?? '';
        $email = $_POST['email'] ?? '';
        $numero = $_POST['numero'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirmPassword'] ?? '';

        if (empty($first_name) || empty($last_name) || empty($email) || empty($numero) || empty($password) || empty($confirm_password)) {
            $error_message = "Tous les champs sont requis.";
        } elseif ($password !== $confirm_password) {
            $error_message = "Les mots de passe ne correspondent pas.";
        } elseif (strlen($password) > 8) {
            $error_message = "Le mot de passe ne doit pas dépasser 8 caractères.";
        } else {
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);

            $check_sql = "SELECT user_id FROM _user WHERE email = ?";
            $check_stmt = $pdo->prepare($check_sql);

            try {
                $check_stmt->execute([$email]);
                $existing_user = $check_stmt->fetch();
                

                if ($existing_user) {
                    $error_message = "Un compte avec cette adresse e-mail existe déjà.";
                } else {
                    $sql = "INSERT INTO _user (nom, numero, email, password) VALUES (:nom, :numero, :email, :password)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        'nom' => $first_name . ' ' . $last_name,
                        'numero' => $numero,
                        'email' => $email,
                        'password' => $password_hashed,
                    ]);
                    $success_message = "Compte créé avec succès.";
                     header('Location: seconnecter.php');
                    exit;
                }
            } catch (PDOException $e) {
                error_log("Erreur Signup: " . $e->getMessage());
                $error_message = "Erreur lors de l'inscription: " . $e->getMessage();
            }
        }
    }
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Souka.ma - Créez votre compte</title>
    <link rel="stylesheet" href="s'scrire.css"> 
   
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="logo">
                <img src="../image/Capture d'écran 2025-06-16 131020.png" alt="Logo"> 
            </div>
            <div class="login-link">
                Vous avez un compte ? <a href="#">Connectez-vous ici</a>
            </div>
            <h1 class="form-title">Créez votre compte</h1>

            <?php if (!empty($success_message)): ?>
                <div class="server-message success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
                <div class="server-message error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <button type="button" class="google-btn" id="googleBtn">
                <svg width="20" height="20" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <span>Commencez avec Google</span>
            </button>
            <div class="divider">Ou</div>
            <form id="signupForm" method="post">
                <div class="form-group">
                    <label class="form-label" for="firstName">Prénom</label>
                    <input type="text" id="firstName" name="firstName" class="form-input" placeholder="Votre prénom" value="<?php echo htmlspecialchars($first_name ?? ''); ?>">
                    <span id="firstNameError" class="error-message">Veuillez entrer votre prénom !</span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="lastName">Nom</label>
                    <input type="text" id="lastName" name="lastName" class="form-input" placeholder="Votre nom" value="<?php echo htmlspecialchars($last_name ?? ''); ?>">
                    <span id="lastNameError" class="error-message">Veuillez entrer votre nom !</span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" class="form-input" placeholder="adresse e-mail" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    <span id="emailError" class="error-message">Veuillez entrer une adresse e-mail valide !</span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="numero">Numéro de téléphone</label>
                    <input type="text" id="numero" name="numero" class="form-input" placeholder="Numéro de téléphone" value="<?php echo htmlspecialchars($phone_number ?? ''); ?>">
                    <span id="numeroError" class="error-message">Veuillez entrer un numéro de téléphone valide (10 chiffres) !</span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" class="form-input" placeholder="••••••" maxlength="8">
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">👁</button>
                    </div>
                    <div class="password-dots">
                        <div class="dot"></div><div class="dot"></div><div class="dot"></div>
                        <div class="dot"></div><div class="dot"></div><div class="dot"></div>
                        <div class="dot"></div><div class="dot"></div>
                    </div>
                    <span id="passwordError" class="error-message">Veuillez entrer un mot de passe (max 8 caractères) !</span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirmPassword">Confirmez le mot de passe</label>
                    <div class="password-container">
                        <input type="password" id="confirmPassword" name="confirmPassword" class="form-input" placeholder="••••••" maxlength="8">
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">👁</button>
                    </div>
                    <div class="password-dots">
                        <div class="dot"></div><div class="dot"></div><div class="dot"></div>
                        <div class="dot"></div><div class="dot"></div><div class="dot"></div>
                        <div class="dot"></div><div class="dot"></div>
                    </div>
                    <span id="confirmPasswordError" class="error-message">Les mots de passe ne correspondent pas !</span>
                </div>
                <button type="submit" class="submit-btn">Créer un compte</button>
            </form>
        </div>
        <div class="right-panel">
            <img src="../image/freepik__a-realistic-highresolution-scene-in-a-traditional-__2294.png" alt="Moroccan Architecture">
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleBtn = passwordInput.nextElementSibling;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = '🙈'; 
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = '👁'; 
            }
        }

        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const dots = e.target.closest('.form-group').querySelectorAll('.password-dots .dot');
            const strength = Math.min(password.length, 8); 
            dots.forEach((dot, index) => (index < strength ? dot.classList.add('active') : dot.classList.remove('active')));
        });

        document.getElementById('confirmPassword').addEventListener('input', function(e) {
            const confirmPassword = e.target.value;
            const dots = e.target.closest('.form-group').querySelectorAll('.password-dots .dot');
            const strength = Math.min(confirmPassword.length, 8); 
            dots.forEach((dot, index) => (index < strength ? dot.classList.add('active') : dot.classList.remove('active')));
        });

        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const firstName = document.getElementById('firstName').value;
            const lastName = document.getElementById('lastName').value;
            const email = document.getElementById('email').value;
            const numero = document.getElementById('numero').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            // Reset all error messages
            document.getElementById('firstNameError').classList.remove('active');
            document.getElementById('lastNameError').classList.remove('active');
            document.getElementById('emailError').classList.remove('active');
            document.getElementById('numeroError').classList.remove('active');
            document.getElementById('passwordError').classList.remove('active');
            document.getElementById('confirmPasswordError').classList.remove('active');

            let hasError = false;

            // Validate First Name
            if (!firstName) {
                document.getElementById('firstNameError').classList.add('active');
                hasError = true;
            }

            // Validate Last Name
            if (!lastName) {
                document.getElementById('lastNameError').classList.add('active');
                hasError = true;
            }

            // Validate Email
            if (!email) {
                document.getElementById('emailError').textContent = 'Veuillez entrer une adresse e-mail !';
                document.getElementById('emailError').classList.add('active');
                hasError = true;
            } else if (!/\S+@\S+\.\S+/.test(email)) { 
                document.getElementById('emailError').textContent = 'Veuillez entrer une adresse e-mail valide !';
                document.getElementById('emailError').classList.add('active');
                hasError = true;
            }

            // Validate Phone Number (10 digits for Moroccan numbers)
            if (!numero) {
                document.getElementById('numeroError').classList.add('active');
                hasError = true;
            } else if (!/^\d{10}$/.test(numero)) { 
                document.getElementById('numeroError').textContent = 'Veuillez entrer un numéro de téléphone valide (10 chiffres) !';
                document.getElementById('numeroError').classList.add('active');
                hasError = true;
            }

            // Validate Password
            if (!password) {
                document.getElementById('passwordError').textContent = 'Veuillez entrer un mot de passe !';
                document.getElementById('passwordError').classList.add('active');
                hasError = true;
            } else if (password.length > 8) {
                document.getElementById('passwordError').textContent = 'Le mot de passe ne doit pas dépasser 8 caractères.';
                document.getElementById('passwordError').classList.add('active');
                hasError = true;
            }

            // Validate Confirm Password
            if (!confirmPassword) {
                document.getElementById('confirmPasswordError').textContent = 'Veuillez confirmer votre mot de passe !';
                document.getElementById('confirmPasswordError').classList.add('active');
                hasError = true;
            } else if (confirmPassword.length > 8) { 
                document.getElementById('confirmPasswordError').textContent = 'Le mot de passe de confirmation ne doit pas dépasser 8 caractères.';
                document.getElementById('confirmPasswordError').classList.add('active');
                hasError = true;
            } else if (password !== confirmPassword) {
                document.getElementById('confirmPasswordError').textContent = 'Les mots de passe ne correspondent pas !';
                document.getElementById('confirmPasswordError').classList.add('active');
                hasError = true;
            }

            // If any error exists, prevent form submission
            if (hasError) {
                e.preventDefault(); 
            }
        });

        // Event listener for Google button (if you want to keep the mock)
        document.getElementById('googleBtn').addEventListener('click', function() {
            const formData = new FormData();
            formData.append('google_signup', 'true');
            // Mock data - In a real app, these values would come from the Google API
            formData.append('firstName', 'Google'); 
            formData.append('lastName', 'User');
            formData.append('email', 'user@google.com'); 
            formData.append('numero', '1234567890'); 

            fetch('seconnecter.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }
                return response.text(); 
            })
            .then(data => {
                alert(data); 
            })
            .catch(error => {
                alert('Erreur lors de la connexion avec Google: ' + error.message);
                console.error('Fetch error:', error); 
            });
        });
    </script>
</body>
</html>