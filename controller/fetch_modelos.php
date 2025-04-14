<?php
require_once '../model/Database.php';

$db = new Database();
$pdo = $db->pdo;

if(isset($_GET['modelo'])) {
    $modelo = $_GET['modelo'];
    $stmt = $pdo->prepare("SELECT * FROM partes_reparacion WHERE Modelo = :modelo");
    $stmt->execute(['modelo' => $modelo]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT * FROM partes_reparacion");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($result);
?>