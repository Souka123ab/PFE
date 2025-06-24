<?php
session_start();
require_once 'C:/xampp/htdocs/PFE/include/conexion.php'; // Adjusted path for consistency

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /PFE/auth/seconnecter.php");
    exit;
}

// Fetch username
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT nom FROM _user WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$username = $user ? ($user['nom'] === 'brit dkfatima zhra' ? 'Prestataire' : htmlspecialchars($user['nom'])) : '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titre = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = filter_var($_POST['prix'] ?? 0, FILTER_VALIDATE_FLOAT);
    $categorie = trim($_POST['categorie'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $telephone = '+212' . preg_replace('/\D/', '', $_POST['telephone'] ?? '');

    // Validate inputs
    if ($prix === false || $prix < 0) {
        echo "<p style='color:red;'>Le prix doit être un nombre positif.</p>";
        exit;
    }
    if (!preg_match('/^\+212\d{9}$/', $telephone)) {
        echo "<p style='color:red;'>Numéro de téléphone invalide (doit être +212 suivi de 9 chiffres).</p>";
        exit;
    }
    if (empty($titre) || empty($categorie) || empty($ville)) {
        echo "<p style='color:red;'>Veuillez remplir tous les champs obligatoires.</p>";
        exit;
    }

    // Map category to id_categorie
    $category_map = [];
    $stmt = $pdo->query("SELECT id_categorie, nom FROM categorie");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $category_map[$row['nom']] = $row['id_categorie'];
    }
    $id_categorie = $category_map[$categorie] ?? null;

    // Handle image upload
    $image = '/PFE/image/profilowe bez profilowego.jpg'; // Default image
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['photo']['type'], $allowed_types)) {
            $upload_dir = 'C:/xampp/htdocs/PFE/uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $image_name = uniqid() . '_' . basename($_FILES['photo']['name']);
            $target_file = $upload_dir . $image_name;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                $image = '/PFE/uploads/' . $image_name;
            } else {
                echo "<p style='color:red;'>Erreur lors du téléversement de l'image.</p>";
            }
        } else {
            echo "<p style='color:red;'>Type de fichier non autorisé. Utilisez JPEG, PNG ou GIF.</p>";
        }
    }

    // Insert into service table
    if ($id_categorie) {
        try {
            $stmt = $pdo->prepare("INSERT INTO service (titre, discription, prix, user_id, id_categorie, image, telephone, ville, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$titre, $description, $prix, $user_id, $id_categorie, $image, $telephone, $ville]);

            // Store service details in session
            $_SESSION['service_ajoute'] = [
                'description' => $description,
                'titre' => $titre,
                'date' => date('Y-m-d H:i:s')
            ];

            header("Location: /PFE/services-user/services-user.php");
            exit;
        } catch (PDOException $e) {
            echo "<p style='color:red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color:red;'>Catégorie invalide.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Souka.ma - Publier une annonce</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="publier-anance-pestateair.css">
</head>
<body>
    <?php include '../include/nav.php'; ?>

    <main class="main-content">
        <div class="form-section">
            <h1>Publier une annonce</h1>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" class="form-control" value="<?php echo $username; ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="categorie">Catégorie</label>
                    <select id="categorie" name="categorie" class="form-control" required>
                        <option value="">Sélectionner une catégorie</option>
                        <?php
                        $stmt = $pdo->query("SELECT nom FROM categorie");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value="' . htmlspecialchars($row['nom']) . '">' . htmlspecialchars($row['nom']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ville">Ville</label>
                    <select id="ville" name="ville" class="form-control" required>
                        <option value="">Sélectionner une ville</option>
                        <option value="casablanca">Casablanca</option>
                        <option value="rabat">Rabat</option>
                        <option value="marrakech">Marrakech</option>
                        <option value="fes">Fès</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" placeholder="Écrire un message..."></textarea>
                </div>

                <div class="form-group">
                    <label for="photo">Photo de service</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="photo" name="photo" class="file-input" accept="image/*">
                        <label for="photo" class="file-input-label">Choisir un fichier</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="telephone">Numéro de téléphone</label>
                    <div class="phone-input">
                        <div class="phone-prefix">+212</div>
                        <input type="tel" id="telephone" name="telephone" class="form-control phone-number" placeholder="123456789" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="prix">Prix</label>
                    <input type="number" id="prix" name="prix" class="form-control" placeholder="0" min="0" step="0.01" required>
                </div>

                <button type="submit" class="btn btn-validate">Valider</button>
            </form>
        </div>

        <div class="illustration">
            <!-- Illustration optionnelle -->
        </div>
    </main>

    <?php include '../include/footer.html'; ?>

    <script>
        document.getElementById('photo').addEventListener('change', function(e) {
            const label = document.querySelector('.file-input-label');
            label.textContent = e.target.files.length > 0 ? e.target.files[0].name : 'Choisir un fichier';
        });

        document.getElementById('telephone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value.substring(0, 9);
        });

        document.getElementById('prix').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d.]/g, '');
            e.target.value = value;
        });
    </script>
</body>
</html>