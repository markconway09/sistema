<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = new Database();
    $pdo = $db->pdo;

    $now = new DateTime();
    // Calculos de tiempo
    $mensaje = $_POST["mensaje"];
    $f_inicio = $now->format('Y-m-d H:i:s');
    if ($_POST["end_date"] == "custom") {
        $f_fin = $_POST["expiration_time"];
    } else {
        $mod = clone $now;
        $mod->modify($_POST["end_date"]);
        $f_fin = $mod->format('Y-m-d H:i:s');
    }

    $usuario = isset($_POST['usuarios']) ? implode(",", $_POST['usuarios']) : "tecnico,dependiente,repartidor";

    $stmt = $pdo->prepare("INSERT INTO recordatorios (usuario, mensaje, fecha_inicio, fecha_fin) VALUES (:usuario, :mensaje, :f_inicio, :f_fin)");
    $stmt->bindParam(":usuario", $usuario);
    $stmt->bindParam(":mensaje", $mensaje);
    $stmt->bindParam(":f_inicio", $f_inicio);
    $stmt->bindParam(":f_fin", $f_fin);
    try {
        $stmt->execute();
    } catch (Exception $e) {
        logError($e->getMessage());
    }
}
?>

<div class="container p-4 rounded text-bg-dark">
    <h1 class="display-4 text-center">Recordatorio</h1>

    <form action="" method="post" class="text-dark">
        <div class="row mb-2">
            <div class="radio-inputs mx-auto col-12 col-lg-4 my-2">
                <label class="radio">
                    <input name="end_date" type="radio" value="+1 day" checked onclick="toggleDateInput(false); updateSelectedDate(this.value)">
                    <span class="name">1 Día</span>
                </label>
                <label class="radio">
                    <input name="end_date" type="radio" value="+1 week" onclick="toggleDateInput(false); updateSelectedDate(this.value)">
                    <span class="name">1 Semana</span>
                </label>
                <label class="radio">
                    <input name="end_date" type="radio" value="custom" onclick="toggleDateInput(true)">
                    <span class="name">Elegir...</span>
                </label>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4 mx-auto">
                <input class="form-control" type="datetime-local" name="expiration_time" id="expiration_time" hidden required disabled>
                <input class="form-control" type="text" id="selected_date" readonly>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-6 mx-auto text-light">
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="usuarios[]" value="tecnico" id="msgTec" checked>
                        <label class="form-check-label" for="msgTec">
                            Técnicos
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="usuarios[]" value="dependiente" id="msgDep" checked>
                        <label class="form-check-label" for="msgDep">
                            Dependientes
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="usuarios[]" value="repartidor" id="msgEnt">
                        <label class="form-check-label" for="msgEnt">
                            Entregas
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-6 mx-auto">
                <div class="input-group">
                    <div class="form-floating">
                        <textarea name="mensaje" id="mensaje" class="form-control" placeholder="Mensaje" rows="4" style="height: 100%;"></textarea>
                        <label for="mensaje">Mensaje</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-2">
            <div class="mx-auto">
                <button type="submit" class="fileButton mx-auto">Enviar</button>
            </div>
        </div>
    </form>

    <?php
    // Database connection
    $db = new Database();
    $pdo = $db->pdo;

    // Fetch data from the database
    $stmt = $pdo->query("SELECT * FROM recordatorios ORDER BY fecha_fin DESC LIMIT 10");
    $recordatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="container p-4 rounded text-bg-dark">
        <h1 class="display-4 text-center">Mensajes</h1>

        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th class="d-none d-md-table-cell">ID</th>
                    <th class="d-none d-md-table-cell">Usuario</th>
                    <th>Mensaje</th>
                    <th class="d-none d-md-table-cell">Inicio</th>
                    <th class="d-none d-md-table-cell">Fin</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recordatorios as $recordatorio): ?>
                    <tr>
                        <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($recordatorio['id']); ?></td>
                        <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($recordatorio['usuario']); ?></td>
                        <td><?php echo htmlspecialchars($recordatorio['mensaje']); ?></td>
                        <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($recordatorio['fecha_inicio']); ?></td>
                        <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($recordatorio['fecha_fin']); ?></td>
                        <td class="text-center">
                            <?php
                            $now = new DateTime();
                            $fecha_fin = new DateTime($recordatorio['fecha_fin']);
                            echo $fecha_fin < $now ? '<i class="text-danger rounded fs-4 bi bi-x-circle"></i>' : '
                            <button class="btn btn-dark" onclick="cancelarRecordatorio('.$recordatorio["id"].')"><i class="text-success rounded fs-4 bi bi-check-circle"></i></button>';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
    function toggleDateInput(enable) {
        document.getElementById("expiration_time").disabled = !enable;
        document.getElementById("expiration_time").hidden = !enable;
        document.getElementById("selected_date").hidden = enable;
    }
    function cancelarRecordatorio(id) {
        $.ajax({
            url: 'controller/ajax_query.php',
            type: 'GET',
            data: {
                call: 4,
                id: id
            },
            dataType: 'json',
            success: function(data) {
                console.log(data);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error: ' + status + ' - ' + error);
            }
        });
        location.reload();
    }
    function updateSelectedDate(value) {
        const selectedDateInput = document.getElementById("selected_date");
        const now = new Date();
        const newDate = new Date(now);
        if (value === "+1 day") {
            newDate.setDate(now.getDate() + 1);
        } else if (value === "+1 week") {
            newDate.setDate(now.getDate() + 7);
        }
        selectedDateInput.value = newDate.toLocaleString();
    }

    // Initialize the selected date input with the default value
    document.addEventListener("DOMContentLoaded", function() {
        updateSelectedDate(document.querySelector('input[name="end_date"]:checked').value);
    });
</script>