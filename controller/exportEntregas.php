<?php
require_once "../model/Database.php";

$hoy = date('Y-m-d');

// Initialize the database connection
$db = new Database();
$pdo = $db->pdo;

// Prepare and execute the query
if(isset($_GET["dia"])&&$_GET["dia"] == 1){
    $stmt = $pdo->prepare("SELECT * FROM insumos WHERE fecha = :fecha");
    $stmt->bindParam(":fecha", $hoy);
} else {
    $stmt = $pdo->prepare("SELECT * FROM insumos");
}
$stmt->execute();

// Fetch all rows as an associative array
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($results)) {
    die("No data found in the 'entregas' table.");
}

// Define the CSV file name
$filename = 'entregas_export_' . $hoy . '.csv';

// Set headers to download the file
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open a file pointer for writing (output stream)
$output = fopen('php://output', 'w');

// Add the CSV headers (column names)
fputcsv($output, array_keys($results[0]));

// Add the data rows
foreach ($results as $row) {
    fputcsv($output, $row);
}

// Close the file pointer
fclose($output);
exit;