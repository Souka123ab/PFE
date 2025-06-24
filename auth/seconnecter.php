<?php

session_start();

// Empêcher l'accès à cette page si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    header("Location: ../acceuil.php");
    exit;
}

require_once '../include/conexion.php';
$error_message = '';
// ... (reste du code)


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // echo "we post this "; // Pour débogage seulement

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = "Veuillez remplir tous les champs.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT user_id, nom, email, password FROM _user WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
    var_dump($user);
    var_dump(password_verify($password, $user['password']));
}


            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['email'] = $user['email'];

                header("Location: ../acceuil.php");
                exit;
            } else {
                $error_message = "Email ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $error_message = "Erreur de connexion : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Souka.ma - Connexion</title>
<link rel="stylesheet" href="seconnecter.css">
<style>
    .field-error {
        color: red;
        font-size: 0.85rem;
        margin-top: 2px;
        display: none;
    }
</style>
</head>
<body>
<div class="container">
    <div class="login-panel">
        <div class="login-content">
            <div class="logo">
                <img src="../image/Capture d'écran 2025-06-16 131020.png" alt="Logo Souka.ma">
            </div>

            <div class="account-link">
                <span>Vous n'avez pas de compte ? </span>
                <a href="s'inscrire.php" class="create-account">Créez votre compte</a>
            </div>

            <h1 class="main-heading">Connexion à votre compte</h1>

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form class="login-form" id="login-form" method="post" >
                <button type="button" class="google-btn" disabled>
                    <!-- Bouton Google désactivé -->
                    <svg width="20" height="20" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="..."/>
                    </svg>
                    <span>Commencez avec Google (Bientôt disponible)</span>
                </button>

                <div class="divider"><span>Ou</span></div>

                <div class="form-group">
                    <label for="email">Adresse e-mail</label>
                    <input id="email" name="email" type="email" placeholder="Adresse e-mail" >
                    <div class="field-error" id="email-error"></div>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="password-input">
                        <input id="password" name="password" type="password" placeholder="••••••" >
                        <button type="button" class="password-toggle" onclick="togglePassword()">👁</button>
                    </div>
                    <div class="field-error" id="password-error"></div>
                </div>

                <button type="submit" class="submit-btn">Se connecter</button>
            </form>
        </div>
    </div>

    <div class="image-panel">
        <img src="../image/freepik__a-realistic-highresolution-scene-in-a-traditional-__2294.png" alt="Moroccan courtyard" class="hero-image">
    </div>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
    }

    document.getElementById('login-form').addEventListener('submit', function (e) {
        let valid = true;

        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const emailError = document.getElementById('email-error');
        const passwordError = document.getElementById('password-error');

        emailError.textContent = '';
        passwordError.textContent = '';
        emailError.style.display = 'none';
        passwordError.style.display = 'none';

        if (!email.value.trim()) {
            emailError.textContent = "Veuillez entrer votre adresse e-mail.";
            emailError.style.display = 'block';
            valid = false;
        }

        if (!password.value.trim()) {
            passwordError.textContent = "Veuillez entrer votre mot de passe.";
            passwordError.style.display = 'block';
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
        }
    });
</script>
</body>
</html>
