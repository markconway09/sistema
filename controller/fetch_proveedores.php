<?php
require_once '../model/Database.php';

$db = new Database();
$pdo = $db->pdo;
if(isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM proveedores_servicios WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
} else {
    $query = "SELECT * FROM proveedores_servicios";
    $stmt = $pdo->prepare($query);
}

try {
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
