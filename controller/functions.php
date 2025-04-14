<?php
require('fpdf186/fpdf.php');

function logError($errorMessage, $logFile = 'error_log.txt') {
    // Ensure the message is sanitized
    $sanitizedMessage = htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');

    // Prepare the log entry with timestamp
    $logEntry = "[" . date("Y-m-d H:i:s") . "] [" . $_SESSION["nombre"] . "] " . $sanitizedMessage . PHP_EOL;

    // Write the log entry to the specified file
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

function isUser($perms) {
    $login = $_SESSION["login"];
    return in_array($login, $perms);
}
function isNotUser($perms) {
    $login = $_SESSION["login"];
    return !in_array($login, $perms);
}

function checkDuplicate($nombre, $disp, $fecha) {
    $db = new Database();
    $pdo = $db->pdo;
    $stmt = $pdo->prepare("
        SELECT nombre, nombre_dispositivo 
        FROM info_orden 
        WHERE fecha = :fecha AND nombre = :nombre AND nombre_dispositivo = :disp
    ");
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':disp', $disp);

    try {
        $stmt->execute();
        // Fetch the first matching record
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log or handle the error appropriately
        logError($e->getMessage());
        return false;
    }
}


// ADMIN

function totalVentas($d=0, $m, $y, $local=0){
    $date = $d==0?$m."/".$y:$d."/".$m."/".$y;
    $db = new Database();
    $pdo = $db->pdo;
    if($d == 0){
        if($local === 0){
            $stmt = $pdo->prepare("SELECT *, o.id as id, f.id as fid, d.id as did, DATE(fecha_pago) as fecha_pago FROM `info_orden` o left JOIN `factura` f ON (f.id_orden = o.id) left JOIN `devolucion` d ON (o.id = d.id_orden) WHERE MONTH(`fecha`) = :m AND YEAR(`fecha`) = :y");
        } else {
            $stmt = $pdo->prepare("SELECT *, o.id as id, f.id as fid, d.id as did, DATE(fecha_pago) as fecha_pago FROM `info_orden` o left JOIN `factura` f ON (f.id_orden = o.id) left JOIN `devolucion` d ON (o.id = d.id_orden) WHERE MONTH(`fecha`) = :m AND YEAR(`fecha`) = :y AND `local` = :loc");
            $stmt->bindParam(':loc', $local);
        }
        $stmt->bindParam(':m', $m);
        $stmt->bindParam(':y', $y);
    } else {
        if($local === 0){
            $stmt = $pdo->prepare("SELECT *, o.id as id, f.id as fid, d.id as did, DATE(fecha_pago) as fecha_pago FROM `info_orden` o left JOIN `factura` f ON (f.id_orden = o.id) left JOIN `devolucion` d ON (o.id = d.id_orden) WHERE DAY(`fecha`) = :d AND MONTH(`fecha`) = :m AND YEAR(`fecha`) = :y");
        } else {
            $stmt = $pdo->prepare("SELECT *, o.id as id, f.id as fid, d.id as did, DATE(fecha_pago) as fecha_pago FROM `info_orden` o left JOIN `factura` f ON (f.id_orden = o.id) left JOIN `devolucion` d ON (o.id = d.id_orden) WHERE DAY(`fecha`) = :d AND MONTH(`fecha`) = :m AND YEAR(`fecha`) = :y AND `local` = :loc");
            $stmt->bindParam(':loc', $local);
        }
        $stmt->bindParam(':d', $d);
        $stmt->bindParam(':m', $m);
        $stmt->bindParam(':y', $y);
    }
    try {
        $stmt->execute();
    } catch(PDOException $e){
        echo $e->getMessage();
    }
    
    // CREAR PDF
    $pdf = new FPDF("L");
    $width = $pdf->GetPageWidth();
    $pdf->AddPage();
    $pdf->SetMargins(10, 5, 5);
    // LOGO
    $pdf->Cell($width, 5);
    $pdf->Ln(1);
    $pdf->Image('LOGO.png', null, null, $width/3);
    $pdf->Ln(5);
    $pdf->SetFont('Arial','',8);
    $pdf->Cell($width/2, 5, iconv('UTF-8', 'windows-1252', "Fecha: ".$date), 0, 1);//
    if($local!=0) $pdf->Cell($width/2, 5, iconv('UTF-8', 'windows-1252', "Local: ".$local), 0, 1);
    $pdf->Ln(5);
    // DATOS
    
    $pdf->SetFont('Arial','B',8);
    $pdf->Cell(28, 5, iconv('UTF-8', 'windows-1252', 'ID - Fecha'), 1, 0);
    $pdf->Cell($width/2-60, 5, iconv('UTF-8', 'windows-1252', 'Descripción'), 1, 0);
    $pdf->Cell(4, 5, iconv('UTF-8', 'windows-1252', 'T'), 1, 0);
    $pdf->Cell(17, 5, iconv('UTF-8', 'windows-1252', 'Local'), 1, 0);
    $pdf->Cell(15, 5, iconv('UTF-8', 'windows-1252', 'Método'), 1, 0);
    $pdf->Cell(10, 5, iconv('UTF-8', 'windows-1252', '- %'), 1, 0);
    $pdf->Cell(15, 5, iconv('UTF-8', 'windows-1252', 'IVA'), 1, 0);
    $pdf->Cell(19, 5, iconv('UTF-8', 'windows-1252', 'P.U.'), 1, 0);
    $pdf->Cell(11, 5, iconv('UTF-8', 'windows-1252', 'Cant.'), 1, 0);
    $pdf->Cell(19, 5, iconv('UTF-8', 'windows-1252', 'Base Imp.'), 1, 0);
    $pdf->Cell(19, 5, iconv('UTF-8', 'windows-1252', 'Total'), 1, 0);
    $pdf->Cell(15, 5, iconv('UTF-8', 'windows-1252', 'Estado'), 1, 1);
    $pdf->SetFont('Arial','',8);
    
    $total_total = 0;
    $iva_total = 0;
    $total_efectivo = 0;
    $total_tarjeta = 0;
    $iva_efectivo = 0;
    $iva_tarjeta = 0;
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $estado = $row["estado"]==4?"Pagado":"Pendiente";
        $pdf->Cell(28, 5, iconv('UTF-8', 'windows-1252', $row["id"]." - ".(empty($row["fecha_pago"])?"Sin fecha":$row["fecha_pago"])), 1, 0);
        $pdf->Cell($width/2-60, 5, iconv('UTF-8', 'windows-1252', ucfirst($row["servicio"])), 1, 0);
        $pdf->Cell(4, 5, iconv('UTF-8', 'windows-1252', $row["ticket"]?"X":""), 1, 0);
        $pdf->Cell(17, 5, iconv('UTF-8', 'windows-1252', $row["local"]), 1, 0);
        $pdf->Cell(15, 5, iconv('UTF-8', 'windows-1252', $row["metodo"]), 1, 0);
        $pdf->Cell(10, 5, iconv('UTF-8', 'windows-1252', $row["descuento"]."%"), 1, 0);
        $pdf->Cell(15, 5, iconv('UTF-8', 'windows-1252', $row["iva"]."%"), 1, 0);
        $pdf->Cell(19, 5, iconv('UTF-8', 'windows-1252',  $row["precio"]." €"), 1, 0);
        $pdf->Cell(11, 5, iconv('UTF-8', 'windows-1252', 1), 1, 0);
        $pdf->Cell(19, 5, iconv('UTF-8', 'windows-1252',  $row["precio"]." €"), 1, 0);
        $pdf->Cell(19, 5, iconv('UTF-8', 'windows-1252',  $row["precio-final"]." €"), 1, 0);
        $pdf->Cell(15, 5, iconv('UTF-8', 'windows-1252',  $estado), 1, 1);
        if(!empty($row["did"])){
            $pdf->Cell(28, 5, iconv('UTF-8', 'windows-1252', $row["id"]." - ".$row["fecha"]), 1, 0);
            $pdf->Cell(198.5, 5, iconv('UTF-8', 'windows-1252', "DEVOLUCIÓN"), 1, 0);
            $pdf->Cell(34, 5, iconv('UTF-8', 'windows-1252',  "-".$row["precio-final"]." €"), 1, 1);
        } else if(!empty($row["fecha_pago"])) {
            $final = $row["precio"] - ($row["precio"]/100*$row["descuento"]);
            $iva = round(($final * $row["iva"])/100, 2);
            $iva_total+=$iva;
            $total_total+=doubleval($final+$iva);
            if($row["metodo"] == "Efectivo") {
                $total_efectivo += doubleval($final);
                $iva_efectivo += $iva;
            }
            if($row["metodo"] == "Tarjeta") {
                $total_tarjeta += doubleval($final);
                $iva_tarjeta += $iva;
            }
        }
        $pdf->Ln(5);
    }
    
    $pdf->Ln(15);

    // TOTAL TOTAL
    $pdf->SetX(10);
    $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252',  "TOTAL"), 0, 1);
    $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252',  "Total (Base Imp.): "), 1, 0);
    $pdf->Cell($width/5, 5, iconv('UTF-8', 'windows-1252',  $total_total." €"), 1, 1);
    //$pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252',  "Descuento: "), 1, 0);
    //$pdf->Cell($width/5, 5, iconv('UTF-8', 'windows-1252',  ($total_total-$iva_total-$total_total)." €"), 1, 1);
    $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252',  "Total IVA: "), 1, 0);
    $pdf->Cell($width/5, 5, iconv('UTF-8', 'windows-1252',  $iva_total." €"), 1, 1);
    $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252',  "Total: "), 1, 0);
    $pdf->Cell($width/5, 5, iconv('UTF-8', 'windows-1252',  ($total_total+$iva_total)." €"), 1, 1);
    
    $pdf->Ln(15);

    $pdf->SetX(10);
    $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252',  "TARJETA"), 0, 0);
    $pdf->SetX(122);
    $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252',  "EFECTIVO"), 0, 1);

    // BASE TARJETA
    $pdf->SetX(10);
    $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252',  "Total (Base Imp.): "), 1, 0);
    $pdf->Cell($width/5, 5, iconv('UTF-8', 'windows-1252',  $total_tarjeta." €"), 1, 0);
    // BASE EFECTIVO
    $pdf->SetX(122);
    $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252',  "Total (Base Imp.): "), 1, 0);
    $pdf->Cell($width/5, 5, iconv('UTF-8', 'windows-1252',  $total_efectivo." €"), 1, 1);

    // IVA TARJETA
    $pdf->SetX(10);
    $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252',  "Total IVA: "), 1, 0);
    $pdf->Cell($width/5, 5, iconv('UTF-8', 'windows-1252',  $iva_tarjeta." €"), 1, 0);
    // IVA EFECTIVO
    $pdf->SetX(122);
    $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252',  "Total IVA: "), 1, 0);
    $pdf->Cell($width/5, 5, iconv('UTF-8', 'windows-1252',  $iva_efectivo." €"), 1, 1);

    // TOTAL TARJETA
    $pdf->SetX(10);
    $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252',  "Total: "), 1, 0);
    $pdf->Cell($width/5, 5, iconv('UTF-8', 'windows-1252',  ($total_tarjeta+$iva_tarjeta)." €"), 1, 0);
    // TOTAL EFECTIVO
    $pdf->SetX(122);
    $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252',  "Total: "), 1, 0);
    $pdf->Cell($width/5, 5, iconv('UTF-8', 'windows-1252',  ($total_efectivo+$iva_efectivo)." €"), 1, 1);
    
    // ABRIR PDF
    $pdf->Output('I', null, true);
}

function tesoreria($m, $y, $local){
    $db = new Database();
    $pdo = $db->pdo;
    $stmt = $pdo->prepare("SELECT fecha, COUNT(*) as cant, SUM(`precio-final`) as total FROM `info_orden` WHERE MONTH(`fecha`) = :m AND YEAR(`fecha`) = :y AND `local` = :loc GROUP BY fecha");
    $stmt->bindParam(':loc', $local);
    $stmt->bindParam(':m', $m);
    $stmt->bindParam(':y', $y);
    try {
        $stmt->execute();
    } catch(PDOException $e){
        echo $e->getMessage();
    }
    
    // CREAR PDF
    $pdf = new FPDF();
    $width = $pdf->GetPageWidth();
    $pdf->AddPage();
    $pdf->SetMargins(10, 5, 5);
    // LOGO
    $pdf->Cell($width, 5);
    $pdf->Ln(1);
    $pdf->Image('LOGO.png', null, null, $width/3);
    $pdf->Ln(5);
    $pdf->SetFont('Arial','',8);
    $pdf->Cell($width/2, 5, iconv('UTF-8', 'windows-1252', "Fecha: ".$m."/".$y), 0, 1);
    if($local!=0) $pdf->Cell($width/2, 5, iconv('UTF-8', 'windows-1252', "Local: ".$local), 0, 1);
    $pdf->Ln(5);

    // HEADERS
    $pdf->SetFont('Arial','B',8);
    $pdf->Cell($width/6-12, 5, iconv('UTF-8', 'windows-1252', 'Fecha'), 1, 0);
    $pdf->Cell($width/6-12, 5, iconv('UTF-8', 'windows-1252', 'Cant.'), 1, 0);
    $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252', 'Efectivo'), 1, 0);
    $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252', 'Tarjeta'), 1, 0);
    $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252', 'Otros'), 1, 0);
    $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252', 'Total'), 1, 1);
    $pdf->SetFont('Arial','',8);

    // DATOS
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $tar = $pdo->prepare("SELECT SUM(`precio-final`) as total_tarjeta FROM `info_orden` WHERE `metodo` = 'tarjeta' AND `fecha` = :fecha AND `local` = :loc");
        $tar->bindParam(':fecha', $row["fecha"]);
        $tar->bindParam(':loc', $local);
        $efe = $pdo->prepare("SELECT SUM(`precio-final`) as total_efectivo FROM `info_orden` WHERE `metodo` = 'efectivo' AND `fecha` = :fecha AND `local` = :loc");
        $efe->bindParam(':fecha', $row["fecha"]);
        $efe->bindParam(':loc', $local);
        try {
            $tar->execute();
        } catch(PDOException $e){
            echo $e->getMessage();
        }
        try {
            $efe->execute();
        } catch(PDOException $e){
            echo $e->getMessage();
        }
        $tarj = $tar->fetch(PDO::FETCH_ASSOC);
        $efec = $efe->fetch(PDO::FETCH_ASSOC);
        $pdf->Cell($width/6-12, 5, iconv('UTF-8', 'windows-1252', $row["fecha"]), 1, 0);
        $pdf->Cell($width/6-12, 5, iconv('UTF-8', 'windows-1252', $row["cant"]), 1, 0);
        $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252', round($efec["total_efectivo"], 2). " €"), 1, 0);
        $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252', round($tarj["total_tarjeta"],2). " €"), 1, 0);
        $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252', round(($row["total"] - $efec["total_efectivo"] - $tarj["total_tarjeta"]),2). " €"), 1, 0);
        $pdf->Cell($width/6, 5, iconv('UTF-8', 'windows-1252', round($row["total"],2). " €"), 1, 1);
    }

    // ABRIR PDF
    $pdf->Output('I', null, true);
}

function totalGastos($m, $y, $local=0){
    $date = $m."/".$y;
    $db = new Database();
    $pdo = $db->pdo;
    if($local == 0){
        $stmt = $pdo->prepare("SELECT *, o.id as id, f.id as fid, d.id as did FROM `factura` f RIGHT JOIN `info_orden` o ON (f.id_orden = o.id) LEFT JOIN `devolucion` d ON (f.id_orden = d.id_orden) WHERE MONTH(`fecha`) = :m AND YEAR(`fecha`) = :y");
    } else {
        $stmt = $pdo->prepare("SELECT *, o.id as id, f.id as fid, d.id as did FROM `factura` f RIGHT JOIN `info_orden` o ON (f.id_orden = o.id) LEFT JOIN `devolucion` d ON (f.id_orden = d.id_orden) WHERE MONTH(`fecha`) = :m AND YEAR(`fecha`) = :y AND `local` = :loc");
        $stmt->bindParam(':loc', $local);
    }
    $stmt->bindParam(':m', $m);
    $stmt->bindParam(':y', $y);
    try {
        $stmt->execute();
    } catch(PDOException $e){
        echo $e->getMessage();
    }
    
    // CREAR PDF
    $pdf = new FPDF();
    $width = $pdf->GetPageWidth();
    $pdf->AddPage();
    $pdf->SetMargins(10, 5, 5);
    // LOGO
    $pdf->Cell($width, 5);
    $pdf->Ln(1);
    $pdf->Image('LOGO.png', null, null, $width/3);
    $pdf->Ln(5);
    $pdf->SetFont('Arial','',8);
    $pdf->Cell($width/2, 5, iconv('UTF-8', 'windows-1252', "Fecha: ".$m."/".$y), 0, 1);
    if($local!=0) $pdf->Cell($width/2, 5, iconv('UTF-8', 'windows-1252', "Local: ".$local), 0, 1);
    $pdf->Ln(5);

    // HEADERS
    $pdf->SetFont('Arial','B',8);
    $pdf->Cell($width/5.65, 5, iconv('UTF-8', 'windows-1252', 'ID - Fecha'), 1, 0);
    $pdf->Cell($width/5.65, 5, iconv('UTF-8', 'windows-1252', 'Precio'), 1, 0);
    $pdf->Cell($width/5.65, 5, iconv('UTF-8', 'windows-1252', 'Insumo'), 1, 0);
    $pdf->Cell($width/5.65, 5, iconv('UTF-8', 'windows-1252', 'Coste'), 1, 0);
    $pdf->Cell($width/5.65, 5, iconv('UTF-8', 'windows-1252', 'Total'), 1, 1);
    $pdf->SetFont('Arial','',8);

    $total = 0;
    // DATOS
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        if($row["insumo_precio"] == "" || $row["insumo_precio"] == 0) continue;
        $g = $row["precio-final"] - $row["insumo_precio"];
        $total += $g;
        $pdf->Cell($width/5.65, 5, iconv('UTF-8', 'windows-1252', $row["id"] . " - " . $row["fecha"]), 1, 0);
        $pdf->Cell($width/5.65, 5, iconv('UTF-8', 'windows-1252', $row["precio-final"] . " €"), 1, 0);
        $pdf->Cell($width/5.65, 5, iconv('UTF-8', 'windows-1252', $row["insumo_desc"]), 1, 0);
        $pdf->Cell($width/5.65, 5, iconv('UTF-8', 'windows-1252', $row["insumo_precio"] . " €"), 1, 0);
        $pdf->Cell($width/5.65, 5, iconv('UTF-8', 'windows-1252', $g . " €"), 1, 1);
    }
    $pdf->Ln(5);
    $pdf->Cell($width/5.65, 5, iconv('UTF-8', 'windows-1252', "Total: " . $total . " €"), 1, 1);

    // ABRIR PDF
    $pdf->Output('I', null, true);
}
?>