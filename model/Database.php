<?php
class Database
{
    public $pdo;
    private $_host = "localhost";
    private $_dbname = "quicktr";
    private $_user = "root";
    private $_pass = "";

    public function __construct()
    {
        $db = "mysql:host=$this->_host;dbname=$this->_dbname";
        $this->pdo = new PDO($db, "$this->_user", "$this->_pass");
    }

    public function fetchId($id)
    {
        $q = "SELECT *, i.id as id, f.id as id_firma, archivo as firma FROM info_orden i
                LEFT JOIN `firma` f ON (i.id = f.id_orden) WHERE i.id = :id";
        $stmt = $this->pdo->prepare($q);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $ticket = new Ticket();
            foreach ($data as $key => $value) {
                $key = str_replace("-", "_", $key);
                if (property_exists($ticket, $key)) {
                    $ticket->$key = $value;
                }
            }
            return $ticket;
        }

        return null;
    }

    public function fetchAll()
    {
        $q = "SELECT *, o.id as id, d.id as did, f.id as fid, s.archivo as firma, DATE_FORMAT(fecha, '%d/%m/%Y') as fecha, fecha as `date`
                FROM `info_orden` o
                LEFT JOIN `devolucion` d ON (d.id_orden = o.id)
                LEFT JOIN `factura` f ON (f.id_orden = o.id)
                LEFT JOIN `firma` s ON (s.id_orden = o.id)
                ORDER BY o.id DESC";
        $stmt = $this->pdo->prepare($q);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            return [];
        }

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $tickets = [];

        foreach ($results as $data) {
            $ticket = new Ticket();
            foreach ($data as $key => $value) {
                $key = str_replace("-", "_", $key);
                if (property_exists($ticket, $key)) {
                    $ticket->$key = $value;
                }
            }
            $tickets[] = $ticket;
        }

        return $tickets;
    }

    // OUTDATED DELETE SOON
    public function fetchPartes($partes) {
        $result = [];
        foreach ($partes as $parte) {
            $q = "SELECT * FROM d_parte WHERE id = :id";
            $stmt = $this->pdo->prepare($q);
            $stmt->bindParam(':id', $parte, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $result[] = $data;
        }
        return $result;
    }

    public function insertTicket(Ticket $ticket)
    {
        $sql = "INSERT INTO info_orden SET 
            id = ?,
            nombre = ?,
            telefono = ?,
            documento = ?,
            servicio = ?,
            partes = ?,
            costes_partes = ?,
            email = ?,
            direccion = ?,
            cp = ?,
            precio = ?,
            descuento = ?,
            iva = ?,
            `precio-final` = ?,
            pagado = ?,
            metodo = ?,
            `nombre_dispositivo` = ?,
            `desc` = ?,
            `desc_tecnico` = ?,
            `local` = ?,
            fecha = ?,
            fecha_pago = ?,
            garantia = ?,
            estado = ?,
            razon = ?,
            dept = ?,
            recurrente = ?";


        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([
                null,
                $ticket->nombre,
                $ticket->telefono,
                $ticket->documento,
                $ticket->servicio,
                $ticket->partes,
                $ticket->costes_partes,
                $ticket->email,
                $ticket->direccion,
                $ticket->cp,
                $ticket->precio,
                $ticket->descuento,
                $ticket->iva,
                $ticket->precio_final,
                $ticket->pagado,
                $ticket->metodo,
                $ticket->nombre_dispositivo,
                $ticket->desc,
                $ticket->desc_tecnico,
                $ticket->local,
                $ticket->fecha,
                $ticket->fecha_pago,
                $ticket->garantia,
                $ticket->estado,
                $ticket->razon,
                $ticket->dept,
                $ticket->recurrente
            ]);
            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            logError($e->getMessage());
        }
    }

    public function isDuplicate(Ticket $ticket)
    {
        $pdo = $this->pdo;
        $stmt = $pdo->prepare("
            SELECT * 
            FROM info_orden 
            WHERE DATE(fecha) = DATE(:fecha) AND nombre = :nombre AND nombre_dispositivo = :disp
        ");
        $fecha = date("Y-m-d", strtotime($ticket->fecha));
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':nombre', $ticket->nombre);
        $stmt->bindParam(':disp', $ticket->nombre_dispositivo);

        try {
            $stmt->execute();
            // Return true if a record is found, false otherwise
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (PDOException $e) {
            // Log the error with additional context if necessary
            logError("Error checking for duplicate ticket: " . $e->getMessage());
            return false; // Return false in case of an error
        }
    }

    public function insertPhotos($id, $photos, $e = "Entrada")
    {
        $targetDir = "fotos/";
        foreach ($photos['name'] as $key => $name) {
            $fecha = date("Y-m-d");
            $estado = $e;
            $fileTmpPath = $photos['tmp_name'][$key];
            $fileName = basename($name);
            $targetFilePath = $targetDir . $fileName;
            // Move the uploaded file to the target directory
            if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
                $stmt = $this->pdo->prepare("INSERT INTO foto (id_orden, archivo, fecha, estado) VALUES (:id, :archivo, :fecha, :estado)");
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':archivo', $fileName);
                $stmt->bindParam(':fecha', $fecha);
                $stmt->bindParam(':estado', $estado);
                try {
                    $stmt->execute();
                } catch (PDOException $e) {
                    logError($e->getMessage());
                }
            }
        }
    }

    public function insertSignature($id, $sig)
    {
        // SUBIR FIRMA
        $folderPath = "firmas/";
        $image_parts = explode(";base64,", $sig);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $image_id = uniqid() . '.' . $image_type;
        $file = $folderPath . $image_id;
        $saveResult = file_put_contents($file, $image_base64);
        if ($saveResult) {
            $pdo = $this->pdo;
            $stmt = $pdo->prepare("INSERT INTO firma VALUES (null, :id, :archivo)");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':archivo', $image_id);

            try {
                $stmt->execute(); // Insert record into the database
            } catch (PDOException $e) {
                logError($e->getMessage());
            }
        }
    }

    public function fetchInsumos($id = null)
    {
        $pdo = $this->pdo;
        if ($id == null) {
            $stmt = $pdo->prepare("
            SELECT i.*, p.nombre as proveedor, p.id as id_prov 
            FROM `insumos` i
            LEFT JOIN `proveedores_servicios` p ON i.id_servicio = p.id
            WHERE i.estado > 0 AND i.estado < 3 
            ORDER BY i.id DESC
            ");
        } else {
            $stmt = $pdo->prepare("
            SELECT i.*, p.nombre as proveedor 
            FROM `insumos` i
            LEFT JOIN `proveedores_servicios` p ON i.id_servicio = p.id
            WHERE i.id_orden = :id
            ");
            $stmt->bindParam(":id", $id);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchInsumoFromId($id_ins)
    {
        $pdo = $this->pdo;
        $stmt = $pdo->prepare("SELECT * FROM `insumos` WHERE id = :id");
        $stmt->bindParam(":id", $id_ins);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertInsumo(Insumo $ins)
    {
        $pdo = $this->pdo;
        // Prepare the SQL statement
        $stmt = $pdo->prepare("
            INSERT INTO `insumos` (fecha, nombre, precio, local, estado, id_orden, id_servicio)
            VALUES (:fecha, :nombre, :precio, :loc, :estado, :id_orden, :servicio)");

        // Bind the parameters from the $ins array
        $stmt->bindParam(":fecha", $ins->fecha);
        $stmt->bindParam(":nombre", $ins->nombre);
        $stmt->bindParam(":precio", $ins->precio);
        $stmt->bindParam(":loc", $ins->local);
        $stmt->bindParam(":estado", $ins->estado);
        $stmt->bindParam(":id_orden", $ins->id_orden);
        $stmt->bindParam(":servicio", $ins->servicio);

        try {
            $stmt->execute();
        } catch (Exception $e) {
            logError($e->getMessage());
        }
    }

    public function updateTicket($ticket)
    {
        $sql = "UPDATE info_orden SET 
        nombre = ?,
        telefono = ?,
        documento = ?,
        servicio = ?,
        partes = ?,
        costes_partes = ?,
        email = ?,
        direccion = ?,
        cp = ?,
        precio = ?,
        descuento = ?,
        iva = ?,
        `precio-final` = ?,
        `pagado` = ?,
        metodo = ?,
        `nombre_dispositivo` = ?,
        `desc` = ?,
        `desc_tecnico` = ?,
        `local` = ?,
        fecha = ?,
        fecha_pago = ?,
        garantia = ?,
        estado = ?,
        razon = ?,
        dept = ?,
        avisos = ?,
        recurrente = ?
        WHERE id = ?";

        // Assuming you have a PDO connection
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([
                $ticket->nombre,
                $ticket->telefono,
                $ticket->documento,
                $ticket->servicio,
                $ticket->partes,
                $ticket->costes_partes,
                $ticket->email,
                $ticket->direccion,
                $ticket->cp,
                $ticket->precio,
                $ticket->descuento,
                $ticket->iva,
                $ticket->precio_final,
                $ticket->pagado,
                $ticket->metodo,
                $ticket->nombre_dispositivo,
                $ticket->desc,
                $ticket->desc_tecnico,
                $ticket->local,
                $ticket->fecha,
                $ticket->fecha_pago,
                $ticket->garantia,
                $ticket->estado,
                $ticket->razon,
                $ticket->dept,
                $ticket->avisos,
                $ticket->recurrente,
                $ticket->id
            ]);
        } catch (Exception $e) {
            logError($e->getMessage());
        }
    }

    public function logChange($user, $desc, $orden)
    {
        $fecha = new DateTime();
        $fecha = $fecha->format('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare("INSERT INTO historial_cambios VALUES (null, :user, :fecha, :descripcion, :id_orden)");
        $stmt->bindParam(":user", $user);
        $stmt->bindParam(":fecha", $fecha);
        $stmt->bindParam(":descripcion", $desc);
        $stmt->bindParam(":id_orden", $orden);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            logError($e->getMessage());
        }
    }

    public function fetchTicketHistory($ticket)
    {
        $q = "SELECT * FROM historial_cambios WHERE id_orden = :id";
        $stmt = $this->pdo->prepare($q);
        $stmt->bindParam(':id', $ticket->id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return $data;
        }

        return null;
    }
}
