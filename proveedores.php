<?php
    $db = new Database();
    $pdo = $db->pdo;
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nombre = $_POST['nombre'];
        $telefono = $_POST['telefono'];
        $direccion = $_POST['direccion'];

        $sql = "INSERT INTO proveedores_servicios (nombre, telefono, direccion) VALUES (:nombre, :telefono, :direccion)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':direccion', $direccion);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            logError($e->getMessage());
        }
    }
?>

<div class="container rounded bg-dark text-white p-lg-5 p-3 mt-5">
    <h2>Proveedores</h2>
    <form action="" method="post" class="d-flex flex-column flex-md-row gap-3 text-dark">
        <div class="input-group">
            <div class="form-floating">
                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" required>
                <label for="nombre">Nombre</label>
            </div>
            <div class="form-floating">
                <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono">
                <label for="telefono">Teléfono</label>
            </div>
            <div class="form-floating">
                <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección">
                <label for="direccion">Dirección</label>
            </div>
            <button type="submit" class="btn btn-primary" onclick="accept()">Añadir</button>
        </div>
    </form>

    <?php
    $sql = "SELECT * FROM proveedores_servicios";
    $stmt = $pdo->query($sql);

    if ($stmt->rowCount() > 0) {
        echo '<table class="table table-dark mt-5">';
        echo '<thead><tr><th>Nombre</th><th>Teléfono</th><th>Dirección</th></thead>';
        echo '<tbody>';
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr>';
            echo '<td>' . $row["nombre"] . '</td>';
            echo '<td>' . $row["telefono"] . '</td>';
            echo '<td>' . $row["direccion"] . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo "0 results";
    }
    ?>

</div>