<?php
// exportModelos.php

require_once "../model/Database.php";

// Database connection
$db = new Database();
$pdo = $db->pdo;
$pdo->exec("SET NAMES 'utf8'"); // Ensure UTF-8 encoding for the database connection

// Set headers to force download of the CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=partes_reparacion_export_'.date("Y-m-d").'.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8 encoding
fwrite($output, "\xEF\xBB\xBF");

// Fetch all columns from the partes_reparacion table
$stmt = $pdo->query("SELECT * FROM partes_reparacion");

// Fetch the column names
$columns = array_keys($stmt->fetch(PDO::FETCH_ASSOC));
fputcsv($output, $columns);

// Reset the cursor and fetch data again
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

// Close the output stream
fclose($output);
exit;
?>
