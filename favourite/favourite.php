<?php
session_start();
require_once '../include/conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}

// Handle adding a service to favorites
if (isset($_GET['id_service'])) {
    $user_id = $_SESSION['user_id'];
    $id_service = intval($_GET['id_service']);

    // Check if the service already exists in favorites
    $stmt = $pdo->prepare("SELECT * FROM favoris WHERE user_id = ? AND id_service = ?");
    $stmt->execute([$user_id, $id_service]);
    $exists = $stmt->fetch();

    if (!$exists) {
        // Check if the service exists in the service table
        $checkService = $pdo->prepare("SELECT id_service FROM service WHERE id_service = ?");
        $checkService->execute([$id_service]);

        if ($checkService->rowCount() > 0) {
            $insert = $pdo->prepare("INSERT INTO favoris (user_id, id_service) VALUES (?, ?)");
            $insert->execute([$user_id, $id_service]);
        } else {
            echo "<p style='color:red;'>Service non trouvé.</p>";
        }
    }

    // Redirect back
   $redirect_url = $_SERVER['HTTP_REFERER'] ?? '/PFE/favourite/favourite.php';
header("Location: $redirect_url");
exit;

}

// Fetch favorited services
$user_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("
        SELECT s.id_service, s.titre, s.prix, s.user_id, s.id_categorie, s.image, s.telephone, s.date, s.discription, s.ville, u.nom
        FROM service s
        JOIN favoris f ON s.id_service = f.id_service
        JOIN _user u ON s.user_id = u.user_id
        WHERE f.user_id = ?
        ORDER BY s.date DESC
    ");
    $stmt->execute([$user_id]);
    $favoris = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erreur lors de la récupération des favoris : " . htmlspecialchars($e->getMessage()) . "</p>";
    $favoris = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Favoris</title>
    <link rel="stylesheet" href="/PFE/include/nav.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }
        h2 {
            color: #D54286;
            margin-bottom: 20px;
        }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .service-card {
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: 0.3s;
            position: relative;
        }
        .service-card:hover {
            transform: translateY(-5px);
        }
        .service-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .service-card h3 {
            margin: 0;
            font-size: 1.1em;
            color: #222;
        }
        .service-card .info {
            font-size: 0.9em;
            color: #555;
        }
        .service-card .price {
            color: #D54286;
            font-weight: bold;
        }
        .actions {
            margin-top: 10px;
        }
        .actions a {
            text-decoration: none;
            padding: 6px 10px;
            margin-right: 5px;
            border-radius: 4px;
            font-size: 0.85em;
            color: white;
            background-color: #2196F3;
        }
        .actions a.demander { background-color: #D54286; }
        .actions a.detail { background-color: #4CAF50; }
        .actions a.remove-favorite { background-color: #f44336; }
        .no-fav {
            color: #888;
            font-size: 1.1em;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<?php require_once '../include/nav.php'; ?>

<div class="container">
    <h2><i class="fas fa-heart"></i> Mes services favoris</h2>

    <?php if (empty($favoris)): ?>
        <p class="no-fav">Vous n'avez ajouté aucun service en favori pour le moment.</p>
    <?php else: ?>
        <div class="services-grid">
            <?php foreach ($favoris as $service): 
                $service_date = $service['date'] ? date('d/m/Y H:i', strtotime($service['date'])) : 'Date non disponible';
                $params = http_build_query([
                    'category' => '',
                    'image' => $service['image'] ?: '/placeholder.svg',
                    'provider_avatar' => '/placeholder.svg',
                    'provider_name' => $service['nom'],
                    'rating' => '4.8',
                    'price' => $service['prix'],
                    'phone' => $service['telephone'],
                    'discription' => $service['discription'] ?: 'Aucune description',
                    'id_service' => $service['id_service'],
                    'id_categorie' => $service['id_categorie'],
                    'ville' => $service['ville'] ?? 'Non spécifiée',
                    'date' => $service_date
                ]);
            ?>
                <div class="service-card">
                    <img src="<?= htmlspecialchars($service['image'] ?: '/placeholder.svg') ?>" alt="Service">
                    <h3><?= htmlspecialchars($service['titre']) ?></h3>
                    <p class="info"><?= htmlspecialchars($service['discription'] ?: 'Aucune description') ?></p>
                    <p class="price"><?= htmlspecialchars($service['prix']) ?> DH</p>
                    <p class="info"><strong>Ville :</strong> <?= htmlspecialchars($service['ville'] ?? 'Non spécifiée') ?></p>
                    <p class="info"><strong>Ajouté le :</strong> <?= $service_date ?></p>
                    <div class="actions">
                        <a class="demander" href="/PFE/services-user/demander.php?service_name=<?= urlencode($service['titre']) ?>&id_categorie=<?= $service['id_categorie'] ?>&phone=<?= urlencode($service['telephone']) ?>&id_service=<?= $service['id_service'] ?>&ville=<?= urlencode($service['ville'] ?? '') ?>">
                            <i class="fas fa-paper-plane"></i> Demander
                        </a>
                        <a class="detail" href="/PFE/services-user/detail.php?<?= $params ?>">
                            <i class="fas fa-eye"></i> Détail
                        </a>
                        <a class="remove-favorite" href="/PFE/favourite/remove-favourite.php?id_service=<?= $service['id_service'] ?>">
                            <i class="fas fa-trash"></i> Retirer
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../include/footer.html'; ?>
</body>
</html>
