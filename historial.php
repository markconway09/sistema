<div class="container p-4 rounded text-bg-dark">
<h1 class="display-4">Historial Cambios</h1> 
<?php
    require_once 'model/Database.php';
    $db = new Database();
    $pdo = $db->pdo;
    $stmt = $pdo->prepare("SELECT * FROM historial_cambios ORDER BY `id` DESC");
    $stmt->execute();
    
    if ($stmt->rowCount()) {
?>   
    <table class="table table-dark table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>#</th>
                <th>Cambio</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
<?php
        // Loop through the results and execute the second query to get the order count for each codigo_socio
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            // Display the results in a table row
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row["usuario"]) . '</td>';
            echo '<td><a style="color:rgb(37,190,212)" href="list&id='.$row["id_orden"].'">' . htmlspecialchars($row["id_orden"]) . '</a></td>';
            echo '<td>' . htmlspecialchars($row["descripcion"]) . '</td>';
            echo '<td>' . htmlspecialchars($row["fecha"]) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo "No hay cambios aún.";
    }
?>
</div>