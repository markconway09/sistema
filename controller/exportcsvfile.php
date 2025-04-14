<?php 

require_once 'functions.php'; 
require_once '../model/Database.php'; 

$filename = "clientes_" . date('Y-m-d') . ".csv"; 

$delimiter = ","; 

$f = fopen('php://memory', 'w'); 
fputs($f, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

$fields = array('ID', 'Nombre', 'Telefono', 'Documento', 'Direccion', 'CP', 'Local'); 

fputcsv($f, $fields, $delimiter); 

$db = new Database();
$pdo = $db->pdo;
$stmt = $pdo->prepare("SELECT * FROM InfoClientes");
try {
    $stmt->execute();
} catch(PDOException $e){
    echo $e->getMessage();
}

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $lineData = array($row['id'], $row['nombre'], $row['telefono'], $row['documento'], $row['direccion'], $row['cp'], $row["local"]);
    fputcsv($f, $lineData, $delimiter);
}

fseek($f, 0); 

header('Content-Type: text/csv'); 

header('Content-Disposition: attachment; filename="' . $filename . '";'); 

fpassthru($f); 

exit();

?>