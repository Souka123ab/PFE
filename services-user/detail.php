<?php
require_once '/xampp/htdocs/PFE/include/conexion.php';
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}

// Verify category from GET parameter
$category = isset($_GET['category']) ? htmlspecialchars(urldecode($_GET['category'])) : '';

// Fetch id_categorie if category exists
$id_categorie = null;
if (!empty($category)) {
    $stmt = $pdo->prepare("SELECT id_categorie FROM categorie WHERE nom = ? LIMIT 1");
    $stmt->execute([$category]);
    $id_categorie = $stmt->fetchColumn();
    if ($id_categorie === false) {
        $id_categorie = null; // Handle case where category doesn't exist
    }
}

// Retrieve phone if present
$phone = isset($_GET['phone']) ? htmlspecialchars(urldecode($_GET['phone'])) : '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Service</title>
    <link rel="stylesheet" href="detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .comment-form {
            display: none;
            margin-bottom: 20px;
            background-color: #e0f7fa;
            padding: 15px;
            border-radius: 5px;
        }
        .comment-form.show {
            display: block;
        }
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #424242;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group input[type="number"] {
            width: 50px;
        }
        .submit-btn {
            background-color: #00796b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background-color: #004d40;
        }
    </style>
</head>
<body>
    <?php require_once '../include/nav.php'; ?>

    <div class="container">
        <?php
        // Check if all required parameters exist in the URL
        if (isset($_GET['category']) &&
            isset($_GET['image']) &&
            isset($_GET['provider_avatar']) &&
            isset($_GET['provider_name']) &&
            isset($_GET['rating']) &&
            isset($_GET['price']) &&
            isset($_GET['phone'])) {

            // Retrieve and decode parameters safely
            $category = htmlspecialchars(urldecode($_GET['category']));
            $image = htmlspecialchars(urldecode($_GET['image']));
            $provider_avatar = htmlspecialchars(urldecode($_GET['provider_avatar']));
            $provider_name = htmlspecialchars(urldecode($_GET['provider_name']));
            $rating = htmlspecialchars(urldecode($_GET['rating']));
            $description = isset($_GET['description']) ? htmlspecialchars(urldecode($_GET['description'])) : 'Aucune description';
            $price = htmlspecialchars(urldecode($_GET['price']));
            $phone = htmlspecialchars(urldecode($_GET['phone']));

            // Initialize comments if not set
            if (!isset($_SESSION['comments'])) {
                $_SESSION['comments'] = [
                    [
                        'user' => 'Soukayna machraa',
                        'comment' => 'Très bon travail merci!',
                        'rating' => 4.5,
                        'time' => 'il y a 20 minutes'
                    ],
                    [
                        'user' => 'Soukayna machraa',
                        'comment' => 'Très bon travail merci!',
                        'rating' => 4.0,
                        'time' => 'il y a 20 minutes'
                    ]
                ];
            }
            $comments = &$_SESSION['comments'];
        ?>
            <!-- Category Header -->
            <div class="detail-category">
                <span class="category-tag"><?php echo $category; ?></span>
            </div>

            <!-- Service Image -->
            <div class="detail-image-container">
                <img src="<?php echo $image; ?>" alt="Service Image" class="detail-image">
            </div>

            <!-- Action Buttons -->
            <div class="detail-action-buttons">
                <button class="btn-commentaire" onclick="toggleCommentForm()">Commentaire</button>
                <button class="btn-appel">Appel</button>
            </div>

            <!-- Comment Form -->
            <div class="comment-form" id="commentForm">
                <form id="commentFormElement" onsubmit="submitComment(event)">
                    <div class="form-group">
                        <label for="commentUser">Votre nom:</label>
                        <input type="text" id="commentUser" name="commentUser" required>
                    </div>
                    <div class="form-group">
                        <label for="commentText">Commentaire:</label>
                        <textarea id="commentText" name="commentText" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="commentRating">Note (1-5):</label>
                        <input type="number" id="commentRating" name="commentRating" min="1" max="5" step="0.5" required>
                    </div>
                    <button type="submit" class="submit-btn">Envoyer</button>
                </form>
            </div>

            <!-- Provider Info -->
            <div class="provider-detail-info">
                <img src="<?php echo $provider_avatar; ?>" alt="Provider Avatar" class="provider-avatar">
                <div class="provider-text">
                    <h2><?php echo $provider_name; ?></h2>
                    <p class="provider-location">Tanger <i class="fas fa-map-marker-alt"></i></p>
                </div>
            </div>

            <!-- Description -->
            <div class="detail-description-section">
                <p class="detail-description"><?php echo $description; ?></p>
            </div>

            <!-- Contact Section -->
            <div class="detail-contact-section">
                <h3>Contact</h3>
                <p class="detail-phone"><i class="fas fa-phone"></i> <a href="tel:<?php echo $phone; ?>"><?php echo $phone; ?></a></p>
            </div>

            <!-- Comments Section -->
            <div class="detail-comments-section">
                <h3>Commentaires</h3>
                <div id="commentsContainer">
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item">
                            <div class="comment-content">
                                <div class="comment-header">
                                    <span class="comment-user"><?php echo htmlspecialchars($comment['user']); ?></span>
                                    <span class="comment-time"><?php echo htmlspecialchars($comment['time']); ?></span>
                                </div>
                                <p class="comment-text"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                <div class="comment-rating">
                                    <?php
                                    $stars = floor($comment['rating']);
                                    $halfStar = $comment['rating'] - $stars >= 0.5;
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $stars) {
                                            echo '<i class="fas fa-star"></i>';
                                        } elseif ($i == $stars + 1 && $halfStar) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Demand Button -->
            <div class="detail-footer-actions">
                <a href="demander.php?service_name=<?php echo urlencode($category); ?>&id_categorie=<?php echo urlencode($id_categorie); ?>&phone=<?php echo urlencode($phone); ?>" class="btn-demander">Demander</a>
            </div>

        <?php
        } else {
            // Display error message if parameters are missing
            echo "<div class='error-message'>";
            echo "<h2>Oops!</h2>";
            echo "<p>Aucun détail de service n'a été trouvé ou les informations sont incomplètes.</p>";
            echo "<p>Veuillez retourner à la page principale et sélectionner un service.</p>";
            echo "<a href='services-user.php'>Retour à la page des services</a>";
            echo "</div>";
        }
        ?>
    </div>

    <script>
        function toggleCommentForm() {
            const form = document.getElementById('commentForm');
            form.classList.toggle('show');
        }

        function submitComment(event) {
            event.preventDefault();

            const user = document.getElementById('commentUser').value;
            const text = document.getElementById('commentText').value;
            const rating = parseFloat(document.getElementById('commentRating').value);
            const time = 'il y a quelques secondes';

            if (user && text && !isNaN(rating)) {
                const newComment = {
                    user: user,
                    comment: text,
                    rating: rating,
                    time: time
                };

                // Add comment to the DOM
                const commentsContainer = document.getElementById('commentsContainer');
                const commentDiv = document.createElement('div');
                commentDiv.className = 'comment-item';
                commentDiv.innerHTML = `
                    <div class="comment-content">
                        <div class="comment-header">
                            <span class="comment-user">${user}</span>
                            <span class="comment-time">${time}</span>
                        </div>
                        <p class="comment-text">${text}</p>
                        <div class="comment-rating">
                            ${generateStars(rating)}
                        </div>
                    </div>
                `;
                commentsContainer.appendChild(commentDiv);

                // Update session storage (simulating persistence)
                let comments = <?php echo json_encode($comments); ?>;
                comments.push(newComment);

                // Send to server (placeholder, replace with actual API call)
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'update_comments=' + encodeURIComponent(JSON.stringify(comments))
                }).then(response => {
                    if (response.ok) {
                        console.log('Comment saved to session');
                    }
                }).catch(error => console.error('Error:', error));

                // Clear form
                document.getElementById('commentFormElement').reset();
                toggleCommentForm();
            } else {
                alert('Veuillez remplir tous les champs correctement.');
            }
        }

        function generateStars(rating) {
            let stars = '';
            const fullStars = Math.floor(rating);
            const hasHalfStar = rating - fullStars >= 0.5;
            for (let i = 1; i <= 5; i++) {
                if (i <= fullStars) {
                    stars += '<i class="fas fa-star"></i>';
                } else if (i == fullStars + 1 && hasHalfStar) {
                    stars += '<i class="fas fa-star-half-alt"></i>';
                } else {
                    stars += '<i class="far fa-star"></i>';
                }
            }
            return stars;
        }
    </script>
</body>
</html>