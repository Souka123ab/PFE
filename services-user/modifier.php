<?php
session_start();  // * Katbda session bash t9der tsayb session dyal user w tsayb authentication
require_once '/xamppa/htdocs/PFE/include/conexion.php'; // * Katd5ol fichier dyal connexion b database (PDO)

if (!isset($_SESSION['user_id']) || !isset($_GET['id_service'])) { 
    // * Ila ma kaynash user mconnecti wla ma jawech id dyal service, rad ndir redirect l login
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}

$user_id = $_SESSION['user_id']; // * khdmt id dyal user mn session
$id_service = $_GET['id_service']; // * khdmt id dyal service mn url (GET)

$stmt = $pdo->prepare("SELECT * FROM service WHERE id_service = ? AND user_id = ?");
$stmt->execute([$id_service, $user_id]);
// * Katjbed service mn base li kayn f id_service w li kayn luser dyalha howa dak luser li mconnecti
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    // * Ila ma tla3hach service ola service ma kaynash wla ma kaynash dyal user hada, t9oul service machi found
    echo "Service introuvable ou accès refusé.";
    exit;
}

// * Katjbed jami3 categories mn table categorie bash t3tihom f select input
$categories = $pdo->query("SELECT id_categorie, nom FROM categorie")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // * Ila tla3 POST request, yani user bgha y3adel service
    $titre = $_POST['titre']; // * khdmt titre jdida
    $prix = $_POST['prix']; // * khdmt prix jdida
    $id_categorie = $_POST['id_categorie']; // * khdmt id categorie jdida
    $telephone = $_POST['telephone']; // * khdmt telephone jdida
    $discription = $_POST['discription']; // * khdmt description jdida
    $ville = $_POST['ville']; // * khdmt ville jdida

    // Gestion image (si nouvelle image)
    $image = $service['image']; // * b9at image li kaina
    if (!empty($_FILES['image']['name'])) {
        // * Ila user 3tana image jdida
        $nom_image = time() . '_' . $_FILES['image']['name']; 
        // * smiya jdida l'image kayna fha timestamp bach ma tdkhlch f images okhra
        $chemin_image = '/PFE/uploads/' . $nom_image; 
        // * path li ghadi tkhdem fih l'image jdida
        move_uploaded_file($_FILES['image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $chemin_image); 
        // * kat7et l'image f dossier uploads
        $image = $chemin_image; // * kaynchaf image jdida f variable image
    }

    // Requête UPDATE
    $stmt = $pdo->prepare("UPDATE service SET titre = ?, prix = ?, id_categorie = ?, telephone = ?, discription = ?, ville = ?, image = ?, date = NOW() WHERE id_service = ? AND user_id = ?");
    $stmt->execute([$titre, $prix, $id_categorie, $telephone, $discription, $ville, $image, $id_service, $user_id]);
    // * kat update service b data jdida f base, w katbdl tari5 lyouma

    $_SESSION['service_modifie'] = [
        'titre' => $titre,
        'date' => date('d/m/Y H:i:s')
    ]; 
    // * kat7fed info f session bach taffichi message ba3d ma t9dem l page dyal services-user.php

    header("Location: services-user.php"); 
    // * katredirect l list dyal services
    exit;
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Service</title>
</head>
<body>
    <h2>Modifier le Service</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Titre: <input type="text" name="titre" value="<?= htmlspecialchars($service['titre']) ?>" required></label><br>
        <!-- * input dyal titre, kay3ammar b titre li kayn daba f service -->
        <label>Prix: <input type="number" name="prix" value="<?= htmlspecialchars($service['prix']) ?>" required></label><br>
        <!-- * input dyal prix, kay3ammar b prix li kayn daba -->
        <label>Catégorie:
            <select name="id_categorie" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id_categorie'] ?>" <?= $cat['id_categorie'] == $service['id_categorie'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <!-- * select dyal catégories, kay3tiha list kamla w kay5tar li kaina m3a service -->
        <label>Téléphone: <input type="text" name="telephone" value="<?= htmlspecialchars($service['telephone']) ?>" required></label><br>
        <!-- * input dyal telephone, kay3ammar b telephone li kayn -->
        <label>Ville: <input type="text" name="ville" value="<?= htmlspecialchars($service['ville']) ?>" required></label><br>
        <!-- * input dyal ville, kay3ammar b ville li kayna -->
        <label>Description:<br><textarea name="discription" rows="4" cols="50"><?= htmlspecialchars($service['discription']) ?></textarea></label><br>
        <!-- * textarea dyal description, kay3ammar b description li kayna -->
        <label>Image: <input type="file" name="image"></label><br>
        <!-- * input dyal image (file) li 9adra tbdl biha l'image -->
        <img src="<?= htmlspecialchars($service['image']) ?>" width="120"><br><br>
        <!-- * t3ridh l'image li kayna daba bach tshoufha -->
        <button type="submit">Enregistrer les modifications</button>
        <!-- * bouton bach tsift formulaire -->
    </form>
</body>
</html>
