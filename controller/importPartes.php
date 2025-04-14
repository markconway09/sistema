<?php
// Include your Database class
require_once '../model/Database.php';

// Initialize the Database connection
$db = new Database();
$conn = $db->pdo; // Use the pdo property directly

// Check if a file was uploaded
if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
    $csvFile = $_FILES['csv_file']['tmp_name']; // Temporary file path

    // Open the CSV file
    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        // Read the first row (header)
        $header = fgetcsv($handle, 1000, ",");
        
        if ($header) {
            // Create table name
            $tableName = "partes_reparacion";

            // Check if the table exists
            $existingColumns = [];
            try {
                $result = $conn->query("DESCRIBE `$tableName`");
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $existingColumns[] = $row['Field'];
                }
            } catch (PDOException $e) {
                // Table does not exist, proceed to create it
            }

            // If the table exists, check for missing columns
            if (!empty($existingColumns)) {
                foreach ($header as $column) {
                    $cleanColumn = preg_replace('/[^a-zA-Z0-9_]/', '', str_replace('"', '', $column));
                    if (!in_array($cleanColumn, $existingColumns)) {
                        try {
                            $conn->exec("ALTER TABLE `$tableName` ADD `$cleanColumn` VARCHAR(255)");
                            echo "Column `$cleanColumn` added to table `$tableName`.\n";
                        } catch (PDOException $e) {
                            die("Error adding column `$cleanColumn` to table `$tableName`: " . $e->getMessage() . "\nSQL: ALTER TABLE `$tableName` ADD `$cleanColumn` VARCHAR(255)");
                        }
                    }
                }
            } else {
                // Build the CREATE TABLE query
                $columns = [];
                foreach ($header as $index => $column) {
                    $cleanColumn = preg_replace('/[^a-zA-Z0-9_]/', '', str_replace('"', '', $column));
                    if ($index === 0) {
                        // First column is `id`, set as auto-increment primary key
                        $columns[] = "`$cleanColumn` INT AUTO_INCREMENT PRIMARY KEY";
                    } else {
                        $columns[] = "`$cleanColumn` VARCHAR(255)";
                    }
                }
                $createTableQuery = "CREATE TABLE IF NOT EXISTS `$tableName` (" . implode(", ", $columns) . ")";

                // Execute the query
                try {
                    $conn->exec($createTableQuery); // Use $conn directly
                    echo "Table `$tableName` created.\n";
                } catch (PDOException $e) {
                    die("Error creating table: " . $e->getMessage());
                }
            }

            // Insert or update data
            $insertQuery = "INSERT INTO `$tableName` (" . implode(", ", array_map(function($col) {
                return "`$col`";
            }, $existingColumns)) . ") VALUES (" . implode(", ", array_fill(0, count($existingColumns), "?")) . ") ON DUPLICATE KEY UPDATE " . implode(", ", array_map(function($col) {
                return "`$col` = VALUES(`$col`)";
            }, $existingColumns));

            $stmt = $conn->prepare($insertQuery);

            // Read and insert each row
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Ensure the row is read correctly, treating commas within text as part of the same field
                try {
                    $stmt->execute($row);
                } catch (PDOException $e) {
                    echo "Error inserting row (Bound Variables (".count($row)."): " . implode(", ", $row) . " | Columns (".count($existingColumns)."): " . implode(", ", $existingColumns) . "): " . $e->getMessage() . "<br>";
                }
            }

            echo "Data imported successfully.\n";
        } else {
            die("Error reading the CSV header.");
        }

        fclose($handle);
    } else {
        die("Error opening the CSV file.");
    }
} else {
    die("No file uploaded or an error occurred during upload.");
}
?>
