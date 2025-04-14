<?php
$db = new Database();
$pdo = $db->pdo;
$stmt = $pdo->prepare("SELECT * FROM `foto` f LEFT JOIN `info_orden` i ON (id_orden = i.id) ORDER BY i.fecha ASC");
try {
    $stmt->execute();
} catch(PDOException $e){
    echo $e->getMessage();
}

// Configuration
$imagesDir = __DIR__ . '/fotos/'; // Path to the images folder
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];

// Function to list images in the folder
function getImages($dir, $allowedExtensions) {
    $images = [];
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($extension, $allowedExtensions)) {
                $images[] = $file;
            }
        }
    }
    asort($images); // Sort images by date (oldest first)
    return $images; // Return only filenames in sorted order
}

// Handle file deletion
if (isset($_POST['delete'])) {
    $fileToDelete = basename($_POST['delete']);
    $filePath = $imagesDir . $fileToDelete;

    if (file_exists($filePath)) {
        unlink($filePath);
        $message = "Archivo '$fileToDelete' eliminado.";
    } else {
        $error = "Archivo '$fileToDelete' no encontrado.";
    }
}

// Retrieve the list of images
$images = getImages($imagesDir, $allowedExtensions);
?>

<style>
    .card-img-top {
        width: 100%;
        height: 200px; /* Fixed height for images */
        object-fit: cover; /* Maintain aspect ratio and crop if needed */
    }
    .card{
        min-width: 200px;
    }
    .img-container { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; }
    .img-container img { max-width: 200px; max-height: 200px; display: block; margin: 0 auto 10px; }
    .img-container button { background-color: red; color: white; border: none; padding: 5px 10px; cursor: pointer; }
    .img-container button:hover { background-color: darkred; }
    .img-container .message { color: green; text-align: center; }
    .img-container .error { color: red; text-align: center; }
</style>

<div class="container border border-secondary text-bg-dark rounded p-5">
    <h1 class="display-5 mb-3">Gestionar Fotos</h1>

    <?php if (isset($message)): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <div class="img-container">
        <?php $i=0; ?>
        <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <?php if(in_array($row["archivo"], $images)): ?>
                <?php
                    if($i == 0) {
                        echo '<div class="row w-100">';
                        $i++;
                    }
                ?>
                <div class="col-3 mb-2">
                    <div class="card">
                        <img class="card-img-top" src="fotos/<?php echo htmlspecialchars($row["archivo"]); ?>" alt="Image">
                        <div class="card-body">
                            <div class="card-subtitle">
                                <small class="text-body-secondary">
                                    <?php echo $row["fecha"] ?>
                                </small>
                            </div>
                            <form action="?pag=imageManager" method="POST">
                                <input type="hidden" name="delete" value="<?php echo htmlspecialchars($row["archivo"]); ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php
                    if($i == 4) {
                        echo '</div>';
                        $i=0;
                    }
                ?>
            <?php endif; ?>
        <?php endwhile; ?>
    </div>
</div>