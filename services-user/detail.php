<?php
require_once '/xamppa/htdocs/PFE/include/conexion.php';  
// * Katd5ol fichier dial connexion m3a base de données

session_start();  
// * Katbda session bash t9der tkhli user mconnecté

if (!isset($_SESSION['user_id'])) {  
    // * Ila ma kaynash user mconnecti, rad ndir redirect l login
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}

$user_id = $_SESSION['user_id'];  
// * Khdmt id dyal user mn session

$id_service = $_GET['id_service'] ?? null;  
// * Khdmt id dyal service mn url GET, ila ma kaynach katdir null

$category = $_GET['service_name'] ?? 'Service';  
// * Katkhod isem category mn url, ila ma kaynach kat3ti default "Service"

if (!$id_service) {  
    // * Ila ma kaynash id service ma3tah, kat3ti message w kat9af
    echo "Aucun service sélectionné.";
    exit;
}

// Enregistrement commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commentText'], $_POST['commentRating'])) {
    // * Ila user sft formulaire commentaire b POST w kayn texte w rating
    $comment_text = $_POST['commentText'];  
    // * Nss dyal commentaire
    $rating = floatval($_POST['commentRating']);  
    // * Note (float)

    $stmt = $pdo->prepare("INSERT INTO comments (user_id, id_service, comment_text, rating) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $id_service, $comment_text, $rating]);
    // * Katdkhel commentaire jdida f base m3a user, service, texte, w note
}

// Récupération des commentaires
$stmt = $pdo->prepare("SELECT c.comment_text AS comment, c.rating, c.date_comment, u.nom AS user 
                      FROM comments c 
                      JOIN _user u ON u.user_id = c.user_id 
                      WHERE c.id_service = ?
                      ORDER BY c.date_comment DESC");
$stmt->execute([$id_service]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
// * Katjbed jami3 les commentaires dyal had service m3a esm user li katab commentaire, tartib mn jdid l9dim

// Infos du service
$stmt = $pdo->prepare("SELECT s.*, u.nom AS provider_name FROM service s 
                      LEFT JOIN _user u ON s.user_id = u.user_id 
                      WHERE s.id_service = ?");
$stmt->execute([$id_service]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);
// * Katjbed les informations kamlin 3la service w smiya dyal provider (prestataire)

if (!$service) {
    // * Ila ma tla3hach service, kat3ti message w kat9af
    echo "Service non trouvé.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du Service - <?php echo htmlspecialchars($service['titre']); ?></title>
    <link rel="stylesheet" href="detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* CSS dyal formulaire commentaire w design dyalo */
        .comment-form { display: none; background-color: #e0f7fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .comment-form.show { display: block; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: block; color: #424242; margin-bottom: 5px; }
        .form-group input, .form-group textarea {
            width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        .form-group input[type="number"] { width: 80px; }
        .submit-btn {
            background-color: #00796b; color: white; border: none;
            padding: 10px 20px; border-radius: 5px; cursor: pointer;
        }
        .submit-btn:hover { background-color: #004d40; }
        .comment-rating i { color: #ffc107; }
    </style>
</head>
<body>
    <?php require_once '../include/nav.php'; ?>
    <!-- * Katd5ol navbar m3a file externe -->

    <div class="container">

        <div class="detail-category">
            <span class="category-tag"><?php echo htmlspecialchars($category); ?></span>
            <!-- * T3ridh l catégorie dyal service f haut l page -->
        </div>

        <div class="detail-image-container">
            <img src="<?php echo htmlspecialchars($service['image'] ?: '/placeholder.svg'); ?>" alt="Image du service" class="detail-image">
            <!-- * T3ridh image dyal service, w ila ma kaynach t3ridh placeholder -->
        </div>

        <div class="detail-action-buttons">
            <button onclick="toggleCommentForm()">Commentaire</button>
            <!-- * Button bach yb9a ybda/ykhfi formulaire commentaire b javascript -->
        </div>

        <div class="provider-detail-info">
            <img src="/placeholder.svg" alt="Avatar du prestataire" class="provider-avatar">
            <!-- * Avatar dial prestataire (placeholder) -->
            <div class="provider-text">
                <h2><?php echo htmlspecialchars($service['provider_name'] ?: 'Prestataire'); ?></h2>
                <!-- * Smiya dyal prestataire, ila ma kaynach katb9a Prestataire -->
                <p class="provider-location"><?php echo htmlspecialchars($service['ville'] ?: 'Non spécifiée'); ?> <i class="fas fa-map-marker-alt"></i></p>
                <!-- * Ville dyal service m3a icon dyal map -->
            </div>
        </div>

        <div class="detail-description-section">
            <h3>Description</h3>
            <p><?php echo htmlspecialchars($service['discription'] ?: 'Aucune description.'); ?></p>
            <!-- * Description dyal service, ila ma kaynach kat3ti message -->
        </div>

        <!-- Formulaire de commentaire -->
        <div class="comment-form" id="commentForm">
            <form method="POST" id="commentFormElement">
                <div class="form-group">
                    <label for="commentText">Commentaire :</label>
                    <textarea id="commentText" name="commentText" rows="4" required></textarea>
                    <!-- * Textarea dyal commentaire -->
                </div>
                <div class="form-group">
                    <label for="commentRating">Note (1 à 5) :</label>
                    <input type="number" id="commentRating" name="commentRating" min="1" max="5" step="0.5" required>
                    <!-- * Input dyal note mben 1 w 5 b steps dyal 0.5 -->
                </div>
                <button type="submit" class="submit-btn">Envoyer</button>
                <!-- * Button dyal envoi formulaire -->
            </form>
        </div>

        <!-- Affichage des commentaires -->
        <div class="detail-comments-section">
            <h3>Commentaires</h3>
            <div id="commentsContainer">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item">
                        <div class="comment-content">
                            <div class="comment-header">
                                <span class="comment-user"><?php echo htmlspecialchars($comment['user']); ?></span>
                                <span class="comment-time"><?php echo date('d/m/Y H:i', strtotime($comment['date_comment'])); ?></span>
                            </div>
                            <p class="comment-text"><?php echo htmlspecialchars($comment['comment']); ?></p>
                            <div class="comment-rating">
                                <?php
                                $stars = floor($comment['rating']);
                                $half = $comment['rating'] - $stars >= 0.5;
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $stars) echo '<i class="fas fa-star"></i>';
                                    elseif ($i == $stars + 1 && $half) echo '<i class="fas fa-star-half-alt"></i>';
                                    else echo '<i class="far fa-star"></i>';
                                }
                                ?>
                                <!-- * Kat3ti stars dyal rating, stars kamlin, stars nos, w stars khawyin -->
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="detail-contact-section">
            <h3>Contact</h3>
            <p><i class="fas fa-phone"></i> <a href="tel:<?php echo htmlspecialchars($service['telephone']); ?>"><?php echo htmlspecialchars($service['telephone']); ?></a></p>
            <!-- * Contact (telephone) dyal service b link tel: -->
        </div>

        <div class="detail-footer-actions">
            <a href="demander.php?service_name=<?php echo urlencode($category); ?>&id_categorie=<?php echo urlencode($service['id_categorie']); ?>
            &phone=<?php echo urlencode($service['telephone']);
             ?>" class="btn-demander">Demander</a>
            <!-- * Link bach tdir demande, katmchi l page demander.php m3a données f GET -->
        </div>

    </div>

    <script>
        function toggleCommentForm() {
            document.getElementById('commentForm').classList.toggle('show');
            // * Javascript simple bach ybda/ykhfi formulaire commentaire
        }
    </script>
</body>
</html>
