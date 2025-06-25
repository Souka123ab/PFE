<?php
session_start();
require_once '/xamppa/htdocs/PFE/include/conexion.php';

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}

// Déterminer l'ID du prestataire
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $_SESSION['user_id'];

// Supprimer tous les commentaires du prestataire s'il a cliqué sur le bouton
if (isset($_GET['delete_reviews']) && $_SESSION['user_id'] == $userId) {
    try {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE user_id = ?");
        $stmt->execute([$userId]);

        $stmt = $pdo->prepare("UPDATE _user SET numero = 0 WHERE user_id = ?");
        $stmt->execute([$userId]);

        header("Location: ?user_id=$userId");
        exit;
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Erreur lors de la suppression des commentaires : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Incrémenter les vues uniquement si ce n'est pas son propre profil
if ($userId !== $_SESSION['user_id']) {
    $stmt = $pdo->prepare("UPDATE _user SET views = views + 1 WHERE user_id = ?");
    $stmt->execute([$userId]);
}

// Fonction pour charger les données
function getProviderData($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT _user.*, COUNT(comments.comment_id) AS comment_count
        FROM _user 
        LEFT JOIN service ON _user.user_id = service.user_id 
        LEFT JOIN comments ON service.id_service = comments.id_service 
        WHERE _user.user_id = ?");
    $stmt->execute([$userId]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $data['hours'] = [
        'Lundi' => ['09:00 - 12:00', '14:00 - 16:00'],
        'Mardi' => ['09:00 - 12:00', '14:00 - 16:00'],
        'Mercredi' => ['09:00 - 12:00', '14:00 - 16:00'],
        'Jeudi' => ['09:00 - 12:00', '14:00 - 16:00'],
        'Vendredi' => ['09:00 - 12:00', '14:00 - 16:00']
    ];

    $stmt = $pdo->prepare("SELECT reviewer_name AS name, rating, text FROM reviews WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $data['reviews'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    return $data;
}

try {
    $providerData = getProviderData($pdo, $userId);

    if (isset($_POST['submit_comment']) && !empty($_POST['comment_text']) && !empty($_POST['rating'])) {
        $commentText = trim($_POST['comment_text']);
        $rating = (int)$_POST['rating'];
        $reviewerName = $_SESSION['nom'] ?? 'Anonyme';

        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, reviewer_name, rating, text) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $reviewerName, $rating, $commentText]);

        $stmt = $pdo->prepare("UPDATE _user SET numero = numero + 1 WHERE user_id = ?");
        $stmt->execute([$userId]);

        $providerData = getProviderData($pdo, $userId);
    }
} catch (PDOException $e) {
    echo "<p style='color:red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($providerData['nom'] ?? 'Utilisateur') . ' | Souka.ma'; ?></title>
    <link rel="stylesheet" href="pestataire-compte.css">
</head>
<body>
    <?php include '../include/nav.php'; ?>

    <main class="main-content">
        <div class="container">

            <?php if ($userId == $_SESSION['user_id']): ?>
                <a href="?logout=1" class="logout-btn">Déconnexion</a>
            <?php endif; ?>

            <section class="profile-section">
                <div class="profile-avatar"></div>
                <h1 class="profile-name"><?php echo htmlspecialchars($providerData['nom'] ?? ''); ?></h1>
                <p class="profile-profession"><?php echo htmlspecialchars($providerData['profession'] ?? ''); ?></p>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-label">Commentaires</div>
                        <div class="stat-value"><?php echo htmlspecialchars($providerData['comment_count'] ?? 0); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Visites de profil</div>
                        <div class="stat-value"><?php echo htmlspecialchars($providerData['views'] ?? 0); ?></div>
                    </div>
                </div>

                <div class="profile-actions">
                    <button class="action-btn btn-comments" id="comments-btn">⭐ Commentaires</button>
                    <button class="action-btn btn-call" id="call-btn">📞 Appel</button>
                    <button class="action-btn btn-favorite">❤️ Favori</button>
                </div>

                <div id="phone-display" style="display:none; margin-top:10px; font-size:1.2em; color:#333;">
                    Numéro de téléphone : <?php echo htmlspecialchars($providerData['phone'] ?? 'Non disponible'); ?>
                </div>

                <form id="comment-form" method="POST" style="display:none; margin-top:20px;">
                    <div style="margin-bottom:10px;">
                        <label for="rating">Note (1-5) :</label>
                        <select name="rating" id="rating" required>
                            <option value="">Choisir une note</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div style="margin-bottom:10px;">
                        <textarea name="comment_text" rows="4" style="width:100%;" placeholder="Écrivez votre commentaire..." required></textarea>
                    </div>
                    <button type="submit" name="submit_comment" class="action-btn">Soumettre le commentaire</button>
                </form>
            </section>

            <section class="about-section">
                <h2 class="section-title">À propos</h2>
                <p class="about-text"><?php echo nl2br(htmlspecialchars($providerData['about'] ?? '')); ?></p>
            </section>

            <section class="reviews-section" id="reviews-section">
                <h2 class="section-title">Avis</h2>

                <?php if ($userId == $_SESSION['user_id'] && count($providerData['reviews']) > 0): ?>
                    <div style="margin-bottom: 10px;">
                        <a href="?delete_reviews=1" style="color: crimson; font-weight: bold;">🗑️ Supprimer tous mes avis</a>
                    </div>
                <?php endif; ?>

                <div class="reviews-grid">
                    <?php if (empty($providerData['reviews'])): ?>
                        <p>Aucun avis pour le moment.</p>
                    <?php else: ?>
                        <?php foreach ($providerData['reviews'] as $review): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <span class="reviewer-name"><?php echo htmlspecialchars($review['name']); ?></span>
                                    <span class="stars"><?php echo str_repeat('⭐', (int)$review['rating']); ?></span>
                                </div>
                                <p class="review-text"><?php echo htmlspecialchars($review['text']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <section class="hours-section">
                <h2 class="section-title">Heures d'ouverture</h2>
                <div class="hours-header">
                    <span class="time-period">Matin</span>
                    <span class="time-period">Après-midi</span>
                </div>
                <div class="hours-table">
                    <?php foreach ($providerData['hours'] as $day => $slots): ?>
                        <div class="hours-row">
                            <span class="day-label"><?php echo htmlspecialchars($day); ?></span>
                            <div class="time-slots">
                                <?php foreach ($slots as $slot): ?>
                                    <div class="time-slot"><?php echo htmlspecialchars($slot); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>

    <?php include '../include/footer.html'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const commentsBtn = document.getElementById('comments-btn');
            const callBtn = document.getElementById('call-btn');
            const phoneDisplay = document.getElementById('phone-display');
            const commentForm = document.getElementById('comment-form');

            commentsBtn.addEventListener('click', function() {
                commentForm.style.display = commentForm.style.display === 'none' ? 'block' : 'none';
                document.getElementById('reviews-section').scrollIntoView({ behavior: 'smooth' });
            });

            callBtn.addEventListener('click', function() {
                phoneDisplay.style.display = phoneDisplay.style.display === 'none' ? 'block' : 'none';
            });

            document.querySelector('.btn-favorite').addEventListener('click', function() {
                alert("Fonction favori à implémenter.");
            });
        });
    </script>
</body>
</html>
