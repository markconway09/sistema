<?php
if (session_status() == PHP_SESSION_NONE) session_start();
// IMPORT FUNCTIONS
require_once "controller/functions.php";
require_once "model/Ticket.php";
require_once "model/Insumo.php";
require_once "model/Database.php";

if (isset($_POST["login"])) {
    $db = new Database();
    $pdo = $db->pdo;
    $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :user");
    $stmt->bindParam(':user', $_POST["user"]);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (isset($row["username"])) {
        $hash = $row["password"];
        $pass = $_POST["pass"];
        $verify = password_verify($pass, $hash);
        if ($verify) {
            $_SESSION["nombre"] = $row["username"];
            $_SESSION["login"] = $row["tipo"];
            $_SESSION["local"] = $row["local"] != null ? $row["local"] : null;
            // Set cookies if "Recuérdame" is checked
            if (isset($_POST["remember"])) {
                setcookie("username", $_POST["user"], time() + (7 * 24 * 60 * 60), "/");
                setcookie("password", $_POST["pass"], time() + (7 * 24 * 60 * 60), "/");
            } else {
                setcookie("username", "", time() - 3600, "/");
                setcookie("password", "", time() - 3600, "/");
            }
        } else {
            echo '<script>alert("Contraseña incorrecta")</script>';
        }
    }
}

// PAGINAS POR DEFECTO
if (isset($_SESSION["login"])) {
    if ($_SESSION["login"] == "repartidor") {
        $default = "entregas";
    } else if ($_SESSION["login"] == "tecnico") {
        $default = "list";
    } else {
        $default = "formulario";
    }
}
if (isset($_GET["logout"])) {
    session_destroy();
    header('Location: index.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['valueLocal'])) {
        if ($_POST["valueLocal"] == "Todo") {
            $_SESSION['local'] = null;
        } else {
            $_SESSION['local'] = $_POST['valueLocal']; // Update the session variable
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico" />
    <title>Orden de reparación</title>
    <link rel="stylesheet" href="css/styles.css" />
    <!-- UIVERSE -->
    <link rel="stylesheet" href="css/styles-uiverse.css" />
    <!-- CHOICES -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <!-- GFONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- JQUERY -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <!-- NOTIFICACIÓN -->
    <script src="controller/notificar/polling.js"></script>
    <script src="controller/jSignature/jSignature.min.js"></script>
</head>

<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-light text-light" style="background-color:rgb(43,45,46);">
        <a class="navbar-brand mx-auto" href="">
            <img class="rounded mx-auto" src="LOGO.png" alt="logo" height="60">
        </a>
        <?php if (isset($_SESSION["login"])) : ?>
            <button class="btn position-relative text-light" style="right: 20px; background-color:#25BED4" data-bs-toggle="modal" data-bs-target="#messageModal">
                <i class="bi bi-megaphone"></i>
                <span id="announcementsPill" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    0
                    <span class="visually-hidden">unread messages</span>
                </span>
            </button>
        <?php endif; ?>
    </nav>

    <?php
    if (!isset($_SESSION["login"])) {
        include 'views/login.php';
        exit();
    }
    ?>

    <nav class="container rounded text-light sticky-top p-3 mt-3" style="background-color:rgb(43,45,46);">
        <div class="input-group d-flex">
            <div class="btn-group">
                <button class="button w-25 btn btn-dark mx-1 text-light my-auto" style="border-radius:6px 0 0 6px" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-fill"></i>
                    <span class="d-sm-inline-block d-none"><?php echo ucfirst($_SESSION["login"]); ?></span>
                </button>
                <ul class="dropdown-menu text-bg-dark">
                    <!-- ADMIN MENU -->
                    <?php if (isUser(["superadmin", "administrador", "jefetecnico", "director", "administrativo"])) { ?>
                        <li><a class="dropdown-item text-light" href="recordatorios"><i class="bi bi-megaphone"></i></i> Recordatorios</a></li>
                        <li><a class="dropdown-item text-light" href="entregas"><i class="bi bi-box-seam"></i> Entregas</a></li>
                        <li><a class="dropdown-item text-light" href="historial"><i class="bi bi-clock-history"></i> Historial</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-light" href="user-admin"><i class="bi bi-people"></i> Usuarios</a></li>
                        <li><a class="dropdown-item text-light" href="proveedores"><i class="bi bi-person-raised-hand"></i> Proveedores</a></li>
                        <li><a class="dropdown-item text-light" href="imageManager"><i class="bi bi-image"></i> Gestionar Fotos</a></li>
                        <li><a class="dropdown-item text-light" href="errores"><i class="bi bi-exclamation-octagon"></i> Errores</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-light" href="totalventas"><i class="bi bi-calculator"></i> Total Ventas</a></li>
                        <li><a class="dropdown-item text-light" href="infoClientes"><i class="bi bi-person-up"></i> Exportar Clientes</a></li>
                        <li><a class="dropdown-item text-light" href="infoOrdenes"><i class="bi bi-database-up"></i> Exportar Ordenes</a></li>
                        <li><a class="dropdown-item text-light" href="gestionDispositivos"><i class="bi bi-phone"></i> Gestionar Dispositivos</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                    <?php
                    }
                    ?>
                    <li>
                        <a href="index.php?logout=true" style="text-decoration: none;" class="logout m-auto noselect">
                            <span class="text">Cerrar Sesión</span>
                            <span class="icon text-light">
                                <i class="bi bi-box-arrow-in-left"></i>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
            <?php if (isNotUser(["tecnico", "repartidor"])) { ?>
                <a href="." class="button btn btn-dark mx-1 my-auto flex-fill">
                    <i class="bi bi-pencil-square"></i> <span class="d-sm-inline-block d-none">Formulario</span>
                </a>
            <?php } ?>
            <a href="list" class="button btn btn-dark mx-1 my-auto flex-fill">
                <i class="bi bi-columns-gap"></i> <span class="d-sm-inline-block d-none">Lista</span>
            </a>
            <?php if (isUser(["repartidor"])) { ?>
                <a href="entregas" class="button btn btn-dark mx-1 my-auto flex-fill">
                    <i class="bi bi-box-seam"></i> <span class="d-sm-inline-block d-none">Entregas</span>
                </a>
            <?php } ?>
        </div>
    </nav>

    <!-- CONTENIDO -->
    <div class="container my-4">
        <?php
        if (isset($_GET["pag"])) {
            include_once $_GET["pag"] . '.php';
        } else {
            include_once $default . '.php';
        }
        ?>
    </div>

    <!-- FOOTER -->
    <ul class="nav nav-tabs mt-2 border-0">
        <li class="mx-auto">
            <span class="nav-link active text-bg-dark border-0">QuickTR <span class="badge badge-pill bg-danger">2.3.1</span></span>
        </li>
    </ul>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>
</body>

<!-- RECORDATORIOS -->
<?php
include_once "views/mostrar_mensaje.php";
?>
<script>
    $(document).ready(function() {
        fetchMessage();
    });
</script>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</html>