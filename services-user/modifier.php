<?php
session_start();
require_once '/xampp/htdocs/PFE/include/conexion.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}

// Check if service ID is provided
if (!isset($_GET['id_service'])) {
    header("Location: services-user.php");
    exit;
}

$id_service = $_GET['id_service'];

// Fetch service details
$stmt = $pdo->prepare("SELECT * FROM service WHERE id_service = ? AND user_id = ?");
$stmt->execute([$id_service, $_SESSION['user_id']]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    echo "<p>Service non trouvé ou vous n'avez pas la permission de le modifier.</p>";
    exit;
}

// Fetch categories
$stmt = $pdo->query("SELECT id_categorie, nom FROM categorie");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titre = trim($_POST['titre']);
    $prix = trim($_POST['prix']);
    $id_categorie = $_POST['id_categorie'];
    $telephone = trim($_POST['telephone']);
    $image = $service['image']; // Keep existing image by default

    // Handle image upload if provided
    if (!empty($_FILES['image']['name'])) {
        $image = '/PFE/images/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $image);
    }

    // Update service
    $stmt = $pdo->prepare("UPDATE service SET titre = ?, prix = ?, id_categorie = ?, telephone = ?, image = ? WHERE id_service = ? AND user_id = ?");
    $stmt->execute([$titre, $prix, $id_categorie, $telephone, $image, $id_service, $_SESSION['user_id']]);

    // Redirect to services page with success message
    $_SESSION['service_modifie'] = ['titre' => $titre, 'date' => date('Y-m-d H:i:s')];
    header("Location: services-user.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Service</title>
    <link rel="stylesheet" href="service-user.css">
</head>
<body>
    <h2>Modifier Service</h2>
    <form method="post" enctype="multipart/form-data">
        <div>
            <label for="titre">Titre</label>
            <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($service['titre']) ?>" required>
        </div>
        <div>
            <label for="prix">Prix (DH)</label>
            <input type="number" id="prix" name="prix" value="<?= htmlspecialchars($service['prix']) ?>" required>
        </div>
        <div>
            <label for="id_categorie">Catégorie</label>
            <select id="id_categorie" name="id_categorie" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id_categorie'] ?>" <?= $category['id_categorie'] == $service['id_categorie'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="telephone">Téléphone</label>
            <input type="text" id="telephone" name="telephone" value="<?= htmlspecialchars($service['telephone']) ?>" required>
        </div>
        <div>
            <label for="image">Image</label>
            <input type="file" id="image" name="image">
            <p>Image actuelle : <img src="<?= htmlspecialchars($service['image']) ?>" alt="Service Image" width="100"></p>
        </div>
        <button type="submit">Enregistrer les modifications</button>
    </form>
</body>
</html>