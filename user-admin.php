<?php
if (!isset($_SESSION["login"])) header('Location: index.php');
if ($_SESSION["login"] != "admin") header('Location: index.php');

// IMPORT FUNCTIONS
require_once "controller/functions.php";
$db = new Database();
$pdo = $db->pdo;

if (isset($_POST["insertuser"])) {
    $stmt = $pdo->prepare("INSERT INTO `user` VALUES (null, :user, :pass, :loc, :tipo)");
    if (isset($_POST["insertuser"])) {
        $user = $_POST["newuser"];
        $pass = password_hash($_POST["newpass"], PASSWORD_DEFAULT);
        $loc = $_POST["local"];
        $tipo = $_POST["tipo"];
    } else {
        $user = "repartidor";
        $pass = password_hash("Barcelona123", PASSWORD_DEFAULT);
        $loc = "Barcelona";
        $tipo = "repartidor";
    }
    $stmt->bindParam(':user', $user);
    $stmt->bindParam(':pass', $pass);
    $stmt->bindParam(':loc', $loc);
    $stmt->bindParam(':tipo', $tipo);
    try {
        $stmt->execute();
        echo '<meta http-equiv="refresh" content="0"/>';
    } catch (PDOException $e) {
        logError($e->getMessage());
    }
}

if (isset($_POST["changePass"])) {
    // Get the user ID and new password from the form
    $userId = $_POST["userId"];
    $newPassword = $_POST["newPassword"];

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Prepare the SQL statement to update the password
    $stmt = $pdo->prepare("UPDATE `user` SET `password` = :pass WHERE `id` = :userId");
    $stmt->bindParam(':pass', $hashedPassword);
    $stmt->bindParam(':userId', $userId);

    try {
        $stmt->execute();
        echo '<meta http-equiv="refresh" content="0"/>';
    } catch (PDOException $e) {
        logError($e->getMessage());
    }
}

$stmt = $pdo->prepare("SELECT * FROM user");
try {
    $stmt->execute();
} catch (PDOException $e) {
    echo $e->getMessage();
}
?>
<div class="container bg-dark p-2">
    <form action="" method="POST">
        <div class="d-flex mb-2">
            <input class="flex-fill" type="text" name="newuser" id="user" placeholder="Nombre usuario">
            <input class="flex-fill" type="password" name="newpass" id="pass" placeholder="Contraseña">
            <select class="flex-fill" name="local" id="local">
                <option value="">(Sin local asignado)</option>
                <option value="Barcelona">Barcelona</option>
                <option value="Mataró">Mataró</option>
            </select>
            <select class="flex-fill" name="tipo" id="tipo">
                <option value="dependiente">Dependiente</option>
                <option value="tecnico">Técnico</option>
                <!-- <option value="repartidor">Repartidor</option> -->
                <option value="administrativo">Administrativo</option>
                <option value="jefetecnico">Jefe Tecnico</option>
                <option value="administrador">Administrador</option>
                <option value="director">Director</option>
                <!-- <option value="superadmin">Superadmin</option> -->
            </select>
            <input type="submit" name="insertuser" class="fileButton" value="Añadir usuario">
        </div>
    </form>
    <table class="table table-dark table-striped table-hover text-bg-dark">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Nombre usuario</th>
                <th scope="col">Contraseña</th>
                <th scope="col">Local</th>
                <th scope="col">Tipo</th>
            </tr>
        </thead>
        <?php
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        ?>

            <tr>
                <th scope="row"><?php echo $row["id"]; ?></th>
                <td><?php echo $row["username"]; ?></td>
                <td>
                    <?php
                    if(isUser(["superadmin"])){
                    ?>
                    <button
                        type="button" class="btn btn-secondary change-password-btn"
                        data-bs-toggle="modal" data-bs-target="#passwordModal" data-user-id="<?php echo $row["id"] ?>">
                        Cambiar
                    </button>
                    <?php } ?>
                </td>
                <td><?php echo $row["local"]; ?></td>
                <td><?php echo ucfirst($row["tipo"]); ?></td>
            </tr>

        <?php
        }
        ?>
    </table>
</div>


<!-- Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passwordModalLabel">Cambiar contraseña para usuario #<span id="userIdPlaceholder"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="passwordForm" method="POST" action="">
                    <input type="hidden" id="userIdInput" name="userId">
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">Nueva contraseña</label>
                        <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                        <input type="checkbox" onclick="togglePassVis('newPassword')" id="toggle1"> <label for="toggle1">Mostrar contraseña</label>
                    </div>
                    <div class="mb-3">
                        <label for="repeatPassword" class="form-label">Repetir contraseña</label>
                        <input type="password" class="form-control" id="repeatPassword" required>
                        <input type="checkbox" onclick="togglePassVis('repeatPassword')" id="toggle2"> <label for="toggle2">Mostrar contraseña</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" form="passwordForm" class="btn btn-primary" name="changePass">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get all buttons with the class 'change-password-btn'
        const buttons = document.querySelectorAll('.change-password-btn');

        // Add a click event listener to each button
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                // Get the user ID from the data-user-id attribute
                const userId = this.getAttribute('data-user-id');

                // Update the modal title and hidden input field with the user ID
                document.getElementById('userIdPlaceholder').textContent = userId;
                document.getElementById('userIdInput').value = userId;
            });
        });

        // Handle form submission
        document.getElementById('passwordForm').addEventListener('submit', function(event) {
            const newPassword = document.getElementById('newPassword').value;
            const repeatPassword = document.getElementById('repeatPassword').value;

            if (newPassword !== repeatPassword) {
                alert('Passwords do not match!');
                event.preventDefault();
            }
        });
    });

    function togglePassVis(el) {
        var x = document.getElementById(el);
        if (x.type === "password") {
            x.type = "text";
        } else {
            x.type = "password";
        }
    }
</script>