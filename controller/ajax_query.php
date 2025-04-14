<?php
require_once '../model/Database.php';
require_once '../model/Ticket.php';
session_start();

$db = new Database();
$pdo = $db->pdo;

$call = $_GET["call"];

switch ($call) {
    case 0:
        // AUTORELLENO CLIENTE
        $q = "SELECT * FROM `info_orden` ORDER BY id DESC";
        $stmt = $pdo->prepare($q);
        $stmt->execute();
        break;
    case 1:
        // CODIGO
        $q = "SELECT `codigo_socio` FROM `info_orden` WHERE `codigo_socio` IS NOT NULL";
        $stmt = $pdo->prepare($q);
        $stmt->execute();
        break;
    case 2:
        // LISTADO
        $q = "SELECT *, DATE_FORMAT(fecha, '%d/%m/%Y') as fecha, fecha as `date` 
        FROM info_orden";

        $wheres = [];
        $wheres[] = "`estado` != 6";

        if (!empty($_GET["search"])) {
            $params = $_GET["search"];
            $wheres[] = "(`nombre_dispositivo` LIKE :search OR id LIKE :search OR
                        `nombre` LIKE :search OR `servicio` LIKE :search)";
        }

        if (isset($_GET["filter"]) && $_GET["filter"] != "todo") {
            if($_GET["filter"] == 6){
                $wheres[] = "`garantia` != 0";
            } else {
                $wheres[] = "`estado` = :filter";
            }
        }

        if (!empty($_SESSION['local'])) {
            $wheres[] = "`local` = :local";
        }

        if (!empty($wheres)) {
            $q .= " WHERE " . implode(" AND ", $wheres);
        }

        $q .= " ORDER BY id DESC";
        if(isset($_GET["limit"])) $q .= " LIMIT :limit";

        $stmt = $pdo->prepare($q);

        if (!empty($_GET["search"])) {
            $params = "%" . $params . "%";
            $stmt->bindParam(':search', $params, PDO::PARAM_STR);
        }

        if ($_GET["filter"] != 6 && isset($_GET["filter"]) && $_GET["filter"] != "todo") {
            $stmt->bindParam(':filter', $_GET["filter"]);
        }

        if (!empty($_SESSION["local"])) {
            $stmt->bindParam(':local', $_SESSION["local"], PDO::PARAM_STR);
        }

        if(isset($_GET["limit"])) {
            $limit = (int)$_GET["limit"] ?? 10;
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        break;
    case 3:
        // CAMBIAR ESTADO
        if(isset($_GET["estado"])){
            $ticket = $db->fetchId($_GET["id"]);
            $ticket->estado = $_GET["estado"];
            $db->updateTicket($ticket);
            $db->logChange($_SESSION["nombre"], "Estado cambiado a ".$ticket->pasos[$ticket->estado], $ticket->id);
        }
        break;
    case 4:
        $current_time = date("Y-m-d H:i:s");
        $stmt = $pdo->prepare("UPDATE recordatorios SET fecha_fin = :t WHERE id = :id");
        $stmt->bindParam(':id', $_GET["id"], PDO::PARAM_INT);
        $stmt->bindParam(':t', $current_time);
        $stmt->execute();
        break;
    case 5:
        $current_time = date("Y-m-d H:i:s");
        $stmt = $pdo->prepare("SELECT * FROM recordatorios WHERE fecha_fin > '$current_time' ORDER BY id DESC");
        $stmt->execute();
        break;
}


$var = "[";  // Initialize the array
$first = true;  // Flag to track if it's the first element

if(isset($stmt)){
    while ($q = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Only add a comma if it's not the first item
        if (!$first) {
            $var .= ",";  // Add a comma before the next element
        }
        // Append the current item
        $var .= json_encode($q, JSON_UNESCAPED_UNICODE);
        $first = false;  // After the first iteration, set $first to false
    }
} else {
    $var .= json_encode($ticket, JSON_UNESCAPED_UNICODE);
}

$var .= "]";  // Close the JSON array


echo $var;
