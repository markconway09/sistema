<?php
require_once "../model/Database.php";

$db = new Database();
$pdo = $db->pdo;
$current_time = date("Y-m-d H:i:s");
$stmt = $pdo->prepare("SELECT * FROM recordatorios WHERE fecha_fin > '$current_time' ORDER BY id DESC");
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result);
?>