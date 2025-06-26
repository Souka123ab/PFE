<?php
session_start();
require_once '/xamppa/htdocs/PFE/include/conexion.php';

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Session timeout (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}
$_SESSION['last_activity'] = time();

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}

// Determine provider ID
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $_SESSION['user_id'];

// Handle delete reviews action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_reviews']) && $_SESSION['user_id'] == $userId) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }
    try {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE user_id = ?");
        $stmt->execute([$userId]);

        $stmt = $pdo->prepare("UPDATE _user SET numero = 0 WHERE user_id = ?");
        $stmt->execute([$userId]);

        header("Location: ?user_id=$userId");
        exit;
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression des commentaires : " . htmlspecialchars($e->getMessage());
    }
}

// Increment views if not viewing own profile
if ($userId !== $_SESSION['user_id']) {
    $stmt = $pdo->prepare("UPDATE _user SET views = views + 1 WHERE user_id = ?");
    $stmt->execute([$userId]);
}

// Function to fetch provider data
function getProviderData($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT u.*, COUNT(r.id) AS comment_count
            FROM _user u
            LEFT JOIN reviews r ON u.user_id = r.user_id
            WHERE u.user_id = ?
            GROUP BY u.user_id
        ");
        $stmt->execute([$userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $stmt = $pdo->prepare("SELECT reviewer_name AS name, rating, text FROM reviews WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        $data['reviews'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $data['hours'] = [
            'Lundi' => ['09:00 - 12:00', '14:00 - 16:00'],
            'Mardi' => ['09:00 - 12:00', '14:00 - 16:00'],
            'Mercredi' => ['09:00 - 12:00', '14:00 - 16:00'],
            'Jeudi' => ['09:00 - 12:00', '14:00 - 16:00'],
            'Vendredi' => ['09:00 - 12:00', '14:00 - 16:00']
        ];

        return $data;
    } catch (PDOException $e) {
        die("Erreur lors de la récupération des données : " . htmlspecialchars($e->getMessage()));
    }
}

// Handle comment submission
try {
    $providerData = getProviderData($pdo, $userId);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment']) && !empty($_POST['comment_text']) && !empty($_POST['rating'])) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("CSRF token validation failed.");
        }

        $commentText = trim($_POST['comment_text']);
        $rating = (int)$_POST['rating'];
        $reviewerName = $_SESSION['nom'] ?? 'Anonyme';

        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, reviewer_name, rating, text) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $reviewerName, $rating, $commentText]);

        $stmt = $pdo->prepare("UPDATE _user SET numero = (SELECT COUNT(*) FROM reviews WHERE user_id = ?) WHERE user_id = ?");
        $stmt->execute([$userId, $userId]);

        $providerData = getProviderData($pdo, $userId); // Refresh data to update comment_count
    }
} catch (PDOException $e) {
    $error = "Erreur : " . htmlspecialchars($e->getMessage());
}

// Handle logout
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
            <?php if (isset($error)): ?>
                <p style="color:red;"><?php echo $error; ?></p>
            <?php endif; ?>

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
                    <button class="action-btn btn-comments" id="comments-btn" aria-label="Toggle comment form">⭐ Commentaires</button>
                    <button class="action-btn btn-call" id="call-btn" aria-label="Toggle phone number visibility" aria-controls="phone-display" <?php echo empty($providerData['phone']) ? 'disabled' : ''; ?>>📞 Appel</button>
                    <button class="action-btn btn-favorite" aria-label="Add to favorites">❤️ Favori</button>
                </div>

                <div id="phone-display" style="display:none; margin-top:10px; font-size:1.2em; color:#333;">
                    Numéro de téléphone : 
                    <?php if (!empty($providerData['phone'])): ?>
                        <a href="tel:<?php echo htmlspecialchars($providerData['phone']); ?>"><?php echo htmlspecialchars($providerData['phone']); ?></a>
                    <?php else: ?>
                        Non disponible
                    <?php endif; ?>
                </div>

                <form id="comment-form" method="POST" style="display:none; margin-top:20px;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
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

                <?php if ($userId == $_SESSION['user_id'] && !empty($providerData['reviews'])): ?>
                    <form action="?user_id=<?php echo $userId; ?>" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer tous vos avis ?');">
                        <input type="hidden" name="delete_reviews" value="1">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <button type="submit" style="color: crimson; font-weight: bold; border: none; background: none; cursor: pointer;">🗑️ Supprimer tous mes avis</button>
                    </form>
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