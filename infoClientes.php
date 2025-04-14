<?php
    if(!isset($_SESSION["login"])) header('Location: index.php');
    if($_SESSION["login"]=="user") header('Location: index.php');

    // IMPORT FUNCTIONS
    require_once "controller/functions.php";
    $db = new Database();
    $pdo = $db->pdo;
    $stmt = $pdo->prepare("SELECT * FROM InfoClientes");
    try {
        $stmt->execute();
    } catch(PDOException $e){
        echo $e->getMessage();
    }
?>
<a href="controller/exportcsvfile.php" target="_blank" class="btn btn-primary mb-2">Exportar CSV <i class="bi bi-cloud-download"></i></a>
<table class="table table-dark table-striped text-bg-dark">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Nombre</th>
            <th scope="col">Teléfono</th>
            <th scope="col">Email</th>
            <th scope="col">Documento</th>
            <th scope="col">Dirección</th>
            <th scope="col">Cód. Postal</th>
            <th scope="col">Local</th>
        </tr>
    </thead>
    <?php
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    ?>

        <tr>
            <th scope="row"><?php echo $row["id"]; ?></th>
            <td><?php echo $row["nombre"]; ?></td>
            <td><?php echo $row["telefono"]; ?></td>
            <td><?php echo $row["email"]; ?></td>
            <td><?php echo $row["documento"]; ?></td>
            <td><?php echo $row["direccion"]; ?></td>
            <td><?php echo $row["cp"]; ?></td>
            <td><?php echo $row["local"]; ?></td>
        </tr>
    
    <?php
    }
    ?>
</table>