<div class="container border border-secondary my-4 pb-4 px-4 text-bg-dark rounded">
    <h2 class="display-5 mt-4 text-center">Entregas a tienda</h2>
    <a class="btn btn-primary mb-2" href="controller/exportEntregas.php?dia=0" target="_blank">Exportar todo a CSV <i class="bi bi-cloud-download"></i></a>
    <a class="btn btn-success mb-2" href="controller/exportEntregas.php?dia=1" target="_blank">Exportar día a CSV <i class="bi bi-cloud-download"></i></a>
    <hr>
    <!-- FORMULARIO -->
    <form action="" method="POST">
        <div class="row">
            <div class="col-md-3 col-12 mb-1">
                <div class="form-floating">
                    <input class="form-control" placeholder="Nombre" type="text" id="nombre" name="nombre" required>
                    <label for="nombre" class="text-dark">Nombre</label>
                </div>
            </div>
            <div class="col-md-3 col-12 mb-1">
                <div class="form-floating">
                    <select class="form-control" id="a" name="a" required>
                        <option value="" disabled selected>-Seleccionar Destino-</option>
                        <option value="Barcelona Oficina">Barcelona Oficina</option>
                        <option value="Barcelona Tienda">Barcelona Tienda</option>
                        <option value="Mataró">Mataró</option>
                        <option value="Otro">Otro</option>
                    </select>
                    <label for="a" class="text-dark">Local</label>
                </div>
            </div>
            <div class="col-md-3 col-12 mb-1">
                <div class="form-floating">
                    <input class="form-control" placeholder="Precio" type="text" id="precio" name="precio" required>
                    <label for="precio" class="text-dark">Precio</label>
                </div>
            </div>
            <div class="col-md-3 col-12 mb-1">
                <div class="form-floating">
                    <select class="form-control form-select" name="servicio" id="servicio">
                        <option value="-">Ninguno</option>
                        <!-- Options will be populated by AJAX -->
                    </select>
                    <label for="servicio1">Servicio</label>
                </div>
            </div>
        </div>
        <div class="row mt-2 mx-auto">
            <button type="submit" name="insert_entrega" class="btn btn-secondary" style="height: 100%;">Insertar</button>
        </div>
    </form>
    <!-- END FORMULARIO -->
    <hr>
    <h1>Pendientes</h1>
    <?php
    require_once 'controller/functions.php';


    // INSERT INTO ENTREGAS
    if (isset($_POST["insert_entrega"])) {
        $fecha = date("Y-m-d");
        $db = new Database();
        $pdo = $db->pdo;
        $stmt = $pdo->prepare("INSERT INTO insumos VALUES (null, :fecha, :nombre, :precio, :loc, 1, null, :serv)");
        $stmt->bindParam(':nombre', $_POST["nombre"]);
        $stmt->bindParam(':precio', $_POST["precio"]);
        $stmt->bindParam(':loc', $_POST["a"]);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':serv', $_POST["servicio"]);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    // END INSERT

    // CHANGE ESTADO
    if (isset($_POST["mas_estado"])) {
        $estado = $_POST["estado"] + 1;
        if ($estado <= 3) {
            $db = new Database();
            $pdo = $db->pdo;
            $stmt = $pdo->prepare("UPDATE insumos SET estado = :estado WHERE id = :id");
            $stmt->bindParam(':id', $_POST["id"]);
            $stmt->bindParam(':estado', $estado);
            try {
                $stmt->execute();
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
    }
    if (isset($_POST["menos_estado"])) {
        $estado = $_POST["estado"] - 1;
        if ($estado >= 0) {
            $db = new Database();
            $pdo = $db->pdo;
            $stmt = $pdo->prepare("UPDATE insumos SET estado = :estado WHERE id = :id");
            $stmt->bindParam(':id', $_POST["id"]);
            $stmt->bindParam(':estado', $estado);
            try {
                $stmt->execute();
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
    }
    // END CHANGE

    // AGREGAR ID ASSOCIADO
    if (isset($_POST["id_orden"])) {
        $id = $_POST["id"];
        $id_orden = $_POST["id_orden"];
        $db = new Database();
        $pdo = $db->pdo;
        $stmt = $pdo->prepare("UPDATE insumos SET id_orden = :id_orden WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':id_orden', $id_orden);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            logError($e->getMessage());
        }
    }
    // END ID

    $estados = ["Sin estado, sin ID", "PENDIENTE", "EN CAMINO", "ENTREGADO"];
    $iconos = ["", "<i class='bi bi-hourglass-bottom'></i>", "<i class='bi bi-person-walking'></i>", "<i class='bi bi-check-lg'></i>"];
    $colores = ["", "#ed7279", "#f0de92", "#8bfa82"];

    $db = new Database();
    try {
        $results = $db->fetchInsumos();

        // Check if results are not empty
        if (count($results) > 0) {
            echo "<table border='1' class='table table-secondary'>";
            echo "<thead><tr><th class='d-none d-md-table-cell'>Fecha</th><th>Nombre</th><th>Precio</th><th>Local</th><th>Servicio</th><th class='d-none d-md-table-cell'>ID Orden</th><th colspan=2>Estado</th></tr></thead>";
            echo "<tbody>";

            // Loop through results and display each row
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td class='d-none d-md-table-cell' style='background:" . $colores[$row["estado"]] . "'>" . htmlspecialchars($row['fecha']) . "</td>";
                echo "<td style='background:" . $colores[$row["estado"]] . "'>" . htmlspecialchars($row['nombre']) . "</td>";
                echo "<td style='background:" . $colores[$row["estado"]] . "'>" . htmlspecialchars($row['precio']) . " €</td>";
                echo "<td style='background:" . $colores[$row["estado"]] . "'>" . htmlspecialchars($row['local']) . "</td>";
                echo "<td style='background:" . $colores[$row["estado"]] . "'>" .
                    "<button type='button' class='btn mb-2' data-bs-toggle='modal' data-bs-target='#proveedoresModal' onclick='fetchProveedor(" . $row['id_prov'] . ")'>
                            " . htmlspecialchars($row['proveedor']) . "
                        </button></td>";

                if ($row["id_orden"] === null) {
                    echo "<td class='d-none d-md-table-cell' style='background:" . $colores[$row["estado"]] . "'>";
    ?>
                    <button type="button" class="btn btn-primary"
                        data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="setId(<?php echo $row['id']; ?>)">
                        <i class="bi bi-link" data-bs-toggle="tooltip" data-bs-title="Asociar Ticket"></i>
                    </button>
                <?php
                    echo "</td>";
                } else echo "<td class='d-none d-md-table-cell' style='background:" . $colores[$row["estado"]] . "'>" .
                    ($row['id_orden'] == 0 ? "-" : "<a href='list&id=" . $row["id_orden"] . "'>" . $row['id_orden'] . "</a>") .
                    "</td>";

                echo "<td style='background:" . $colores[$row["estado"]] . "'>" . $iconos[$row["estado"]] . " <span class='d-none d-md-inline'>" . $estados[$row['estado']] . "</span></td>";
                echo "<td style='background:" . $colores[$row["estado"]] . "'>";
                echo "<form method='post' action=''>";
                echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                echo "<input type='hidden' name='estado' value='" . $row['estado'] . "'>";
                echo "<div class='btn-group'>";
                echo "<button type='submit' name='menos_estado' class='btn btn-primary'><i class='bi bi-chevron-left'></i></button>&nbsp;";
                echo "<button type='submit' name='mas_estado' class='btn btn-primary'><i class='bi bi-chevron-right'></i></button>";
                echo "</div>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
    } catch (PDOException $e) {
        logError($e->getMessage());
    }

    try {
        $pdo = $db->pdo;
        $stmt = $pdo->prepare("SELECT insumos.*, proveedores_servicios.nombre AS proveedor, proveedores_servicios.id as id_prov FROM `insumos` LEFT JOIN `proveedores_servicios` ON insumos.id_servicio = proveedores_servicios.id WHERE insumos.estado = 0 AND insumos.id_orden IS NULL ORDER BY insumos.id DESC");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check if results are not empty
        if (count($results) > 0) {
            echo "<table border='1' class='table table-secondary'>";
            echo "<thead><tr><th class='d-none d-md-table-cell'>Fecha</th><th>Nombre</th><th>Precio</th><th>Local</th><th>Servicio</th><th class='d-none d-md-table-cell'>ID Orden</th><th colspan=2>Estado</th></tr></thead>";
            echo "<tbody>";

            // Loop through results and display each row
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td class='d-none d-md-table-cell' style='background:" . $colores[$row["estado"]] . "'>" . htmlspecialchars($row['fecha']) . "</td>";
                echo "<td style='background:" . $colores[$row["estado"]] . "'>" . htmlspecialchars($row['nombre']) . "</td>";
                echo "<td style='background:" . $colores[$row["estado"]] . "'>" . htmlspecialchars($row['precio']) . " €</td>";
                echo "<td style='background:" . $colores[$row["estado"]] . "'>" . htmlspecialchars($row['local']) . "</td>";
                echo "<td style='background:" . $colores[$row["estado"]] . "'>" .
                    "<button type='button' class='btn mb-2' data-bs-toggle='modal' data-bs-target='#proveedoresModal' onclick='fetchProveedor(" . $row['id_prov'] . ")'>
                            " . htmlspecialchars($row['proveedor']) . "
                        </button></td>";

                if ($row["id_orden"] === null) {
                    echo "<td class='d-none d-md-table-cell' style='background:" . $colores[$row["estado"]] . "'>";
                ?>
                    <button type="button" class="btn btn-primary"
                        data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="setId(<?php echo $row['id']; ?>)">
                        <i class="bi bi-link" data-bs-toggle="tooltip" data-bs-title="Asociar Ticket"></i>
                    </button>
    <?php
                    echo "</td>";
                } else echo "<td class='d-none d-md-table-cell' style='background:" . $colores[$row["estado"]] . "'>" .
                    ($row['id_orden'] == 0 ? "-" : "<a href='list&id=" . $row["id_orden"] . "'>" . $row['id_orden'] . "</a>") .
                    "</td>";

                echo "<td style='background:" . $colores[$row["estado"]] . "'>" . $iconos[$row["estado"]] . " <span class='d-none d-md-inline'>" . $estados[$row['estado']] . "</span></td>";
                echo "<td style='background:" . $colores[$row["estado"]] . "'>";
                echo "<form method='post' action=''>";
                echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                echo "<input type='hidden' name='estado' value='" . $row['estado'] . "'>";
                echo "<div class='btn-group'>";
                echo "<button type='submit' name='menos_estado' class='btn btn-primary' " . ($row["estado"] == 0 ? "disabled" : "") . "><i class='bi bi-chevron-left'></i></button>&nbsp;";
                echo "<button type='submit' name='mas_estado' class='btn btn-primary'><i class='bi bi-chevron-right'></i></button>";
                echo "</div>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
    } catch (PDOException $e) {
        logError($e->getMessage());
    }
    ?>
    <hr>
    <h1>Entregados</h1>
    <?php

    try {
        $pdo = $db->pdo;
        $stmt = $pdo->prepare("SELECT insumos.*, proveedores_servicios.nombre AS proveedor, proveedores_servicios.id as id_prov FROM `insumos` LEFT JOIN `proveedores_servicios` ON insumos.id_servicio = proveedores_servicios.id WHERE insumos.estado > 2 ORDER BY insumos.id DESC");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check if results are not empty
        if (count($results) > 0) {
            echo "<table border='1' class='table table-secondary'>";
            echo "<thead><tr><th class='d-none d-md-table-cell'>Fecha</th><th>Nombre</th><th>Precio</th><th>Local</th><th>Servicio</th><th class='d-none d-md-table-cell'>ID Orden</th><th>Estado</th></tr></thead>";
            echo "<tbody>";

            // Loop through results and display each row
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td class='d-none d-md-table-cell' style='background:" . $colores[$row["estado"]] . "'>" . htmlspecialchars($row['fecha']) . "</td>";
                echo "<td style='background:" . $colores[$row["estado"]] . "'>" . htmlspecialchars($row['nombre']) . "</td>";
                echo "<td style='background:" . $colores[$row["estado"]] . "'>" . htmlspecialchars($row['precio']) . " €</td>";
                echo "<td style='background:" . $colores[$row["estado"]] . "'>" . htmlspecialchars($row['local']) . "</td>";
                echo "<td style='background:" . $colores[$row["estado"]] . "'>" .
                    "<button type='button' class='btn mb-2' data-bs-toggle='modal' data-bs-target='#proveedoresModal' onclick='fetchProveedor(" . $row['id_prov'] . ")'>
                            " . htmlspecialchars($row['proveedor']) . "
                        </button></td>";

                if ($row["id_orden"] === null) {
                    echo "<td class='d-none d-md-table-cell' style='background:" . $colores[$row["estado"]] . "'>";
    ?>
                    <button type="button" class="btn btn-primary"
                        data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="setId(<?php echo $row['id']; ?>)">
                        <i class="bi bi-link" data-bs-toggle="tooltip" data-bs-title="Asociar Ticket"></i>
                    </button>
    <?php
                    echo "</td>";
                } else echo "<td class='d-none d-md-table-cell' style='background:" . $colores[$row["estado"]] . "'>" .
                    ($row['id_orden'] == 0 ? "-" : "<a href='list&id=" . $row["id_orden"] . "'>" . $row['id_orden'] . "</a>") .
                    "</td>";

                echo "<td style='background:" . $colores[$row["estado"]] . "'>" . $iconos[$row["estado"]] . " <span class='d-none d-md-inline'>" . $estados[$row['estado']] . "</span></td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
    } catch (PDOException $e) {
        logError($e->getMessage());
    }
    ?>
</div>
<!-- Modal -->
<div class="modal modal-lg fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Asociar Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form inside the modal -->
                <form action="" method="POST" id="asociar">
                    <!-- Hidden input for the ID -->
                    <input type="hidden" class="hiddenIdInput" name="id">

                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">ID Orden</label>
                        <div class="input-group">
                            <input type="number" step="1" name="id_orden" class="form-control">
                            <input type="submit" name="" class="btn btn-primary">
                        </div>
                        <div class="form-text">Escribe el ID o selecciona un ticket de abajo.</div>
                    </div>
                </form>
                <div class="mb-3">
                    <?php
                    // Prepare the SQL statement
                    $stmt = $pdo->prepare("SELECT id, servicio, nombre_dispositivo FROM info_orden ORDER BY id DESC");

                    // Execute the statement
                    $stmt->execute();

                    // Fetch all results as an associative array
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Check if there are any results
                    if ($results) {
                        echo '<table class="table table-bordered table-striped">';
                        echo '  <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Servicio</th>
                                            <th>Dispositivo</th>
                                            <th>Seleccionar</th>
                                        </tr>
                                    </thead>';
                        echo '  <tbody>';
                        foreach ($results as $row) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['servicio']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['nombre_dispositivo']) . '</td>';
                            echo '<td>
                                            <form method="post" action="" style="display:inline;">
                                                <input type="hidden" name="id" class="hiddenIdInput">
                                                <input type="hidden" name="id_orden" value="' . $row["id"] . '">
                                                <button type="submit" class="btn btn-primary"><i class="bi bi-check2-square"></i></button>
                                            </form>
                                        </td>';
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                    } else {
                        echo '<p>No se encontraron resultados.</p>'; // Display a message if no results are found
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Proveedores Modal -->
<div class="modal fade" id="proveedoresModal" tabindex="-1" aria-labelledby="proveedoresModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="proveedoresModalLabel">Información</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="proveedorInfo"></div>
            </div>
        </div>
    </div>
</div>

<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    function setId(id) {
        // Select all hidden inputs with the class "hiddenIdInput"
        const hiddenInputs = document.querySelectorAll('.hiddenIdInput');

        // Loop through all matching inputs and set their value
        hiddenInputs.forEach(input => {
            input.value = id;
        });
    }

    function fetchProveedor(id) {
        $.ajax({
            url: `controller/fetch_proveedores.php?id=${id}`,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                let info = '';
                if (data.error) {
                    info = `<div class="alert alert-danger">${data.error}</div>`;
                } else {
                    info = `<div class="list-group">`;
                    data.forEach(item => {
                        info += `<div class="list-group-item">
                                    <h5 class="mb-1">${item.nombre}</h5>
                                    <p class="mb-1">Teléfono: ${item.telefono}</p>
                                    <p class="mb-1">Dirección: ${item.direccion}</p>
                                 </div>`;
                    });
                    info += `</div>`;
                }
                $('#proveedorInfo').html(info);
            },
            error: function() {
                $('#proveedorInfo').html(`<p class="text-danger">Error fetching data</p>`);
            }
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        fetchProveedores();
    });

    function fetchProveedores() {
        $.ajax({
            url: 'controller/fetch_proveedores.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
            const selects = document.querySelectorAll('select[name^="servicio"]');
            selects.forEach(select => {
                data.forEach(proveedor => {
                const option = document.createElement('option');
                option.value = proveedor.id;
                option.textContent = proveedor.nombre;
                select.appendChild(option);
                });
            });
            },
            error: function(error) {
            console.error('Error fetching proveedores:', error);
            }
        });
    }
</script>