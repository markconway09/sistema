<?php
    if(!isset($_SESSION["login"])) header('Location: index.php');
    if($_SESSION["login"]!="admin") header('Location: index.php');

    $db = new Database();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticket = $db->fetchId($_POST['id']);

        $ticket->nombre = $_POST['nombre'] ?? null;
        $ticket->telefono = $_POST['telefono'] ?? null;
        $ticket->documento = $_POST['documento'] ?? null;
        $ticket->email = $_POST['email'] ?? null;
        $ticket->direccion = $_POST['direccion'] ?? null;
        $ticket->cp = $_POST['cp'] ?? null;
        $ticket->precio = $_POST['precio'] ?? null;
        $ticket->descuento = $_POST['descuento'] ?? null;
        $ticket->iva = $_POST['iva'] ?? null;
        $ticket->precio_final = $_POST['precio_final'] ?? null;
        $ticket->metodo = $_POST['metodo'] ?? null;
        $ticket->nombre_dispositivo = $_POST['nombre_dispositivo'] ?? null;
        $ticket->desc = $_POST['desc'] ?? null;
        $ticket->desc_tecnico = $_POST['desc_tecnico'] ?? null;
        $ticket->local = $_POST['local'] ?? null;
        $ticket->fecha_pago = (!empty($_POST['fecha_pago'])) ? $_POST['fecha_pago'] : null;
        $ticket->garantia = $_POST['garantia'] ?? null;
        $ticket->estado = $_POST['estado'] ?? null;
        $ticket->razon = $_POST['razon'] ?? null;
        $ticket->dept = $_POST['dept'] ?? null;
        $ticket->recurrente = $_POST['recurrente'] ?? null;
        $ticket->avisos = $_POST['avisos'] ?? null;

        $db->updateTicket($ticket);
    }

    $tickets = $db->fetchAll();
?>
<a href="controller/exportOrden.php" target="_blank" class="btn btn-primary mb-2">Exportar CSV <i class="bi bi-cloud-download"></i></a>
<table class="table table-dark table-striped text-bg-dark">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Nombre</th>
            <th scope="col">Dispositivo</th>
            <th scope="col">Servicio</th>
            <th scope="col">Detalles/Modificar</th>
        </tr>
    </thead>
    <?php
    foreach ($tickets as $ticket) {
    ?>
        <tr>
            <th scope="row"><?php echo $ticket->id; ?></th>
            <td><?php echo $ticket->nombre; ?></td>
            <td><?php echo $ticket->nombre_dispositivo; ?></td>
            <td><?php echo $ticket->servicio; ?></td>
            <td class="text-center">
                <button type="button" class="btn btn-primary" style="border-radius: 50%;" data-bs-toggle="modal" data-bs-target="#ticketModal<?php echo $ticket->id; ?>">
                    <i class="bi bi-info-circle"></i>
                </button>
            </td>
        </tr>

        <!-- Modal -->
        <div class="modal fade" id="ticketModal<?php echo $ticket->id; ?>" tabindex="-1" aria-labelledby="ticketModalLabel<?php echo $ticket->id; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content text-bg-dark">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ticketModalLabel<?php echo $ticket->id; ?>">Modificar Ticket #<?php echo $ticket->id; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="ticketForm<?php echo $ticket->id; ?>" method="post" action="">
                            <input type="hidden" name="id" value="<?php echo $ticket->id; ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control text-bg-dark" id="nombre" name="nombre" value="<?php echo $ticket->nombre; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control text-bg-dark" id="telefono" name="telefono" value="<?php echo $ticket->telefono; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="documento" class="form-label">Documento</label>
                                    <input type="text" class="form-control text-bg-dark" id="documento" name="documento" value="<?php echo $ticket->documento; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control text-bg-dark" id="email" name="email" value="<?php echo $ticket->email; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <input type="text" class="form-control text-bg-dark" id="direccion" name="direccion" value="<?php echo $ticket->direccion; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cp" class="form-label">Código Postal</label>
                                    <input type="text" class="form-control text-bg-dark" id="cp" name="cp" value="<?php echo $ticket->cp; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="precio" class="form-label">Precio</label>
                                    <input type="text" class="form-control text-bg-dark" id="precio" name="precio" value="<?php echo $ticket->precio; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="recurrente" class="form-label">Recurrente</label>
                                    <input type="text" class="form-control text-bg-dark" id="recurrente" name="recurrente" value="<?php echo $ticket->recurrente; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="descuento" class="form-label">Descuento</label>
                                    <input type="text" class="form-control text-bg-dark" id="descuento" name="descuento" value="<?php echo $ticket->descuento; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="iva" class="form-label">IVA</label>
                                    <input type="text" class="form-control text-bg-dark" id="iva" name="iva" value="<?php echo $ticket->iva; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="precio_final" class="form-label">Precio Final</label>
                                    <input type="text" class="form-control text-bg-dark" id="precio_final" name="precio_final" value="<?php echo $ticket->precio_final; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="metodo" class="form-label">Método</label>
                                    <input type="text" class="form-control text-bg-dark" id="metodo" name="metodo" value="<?php echo $ticket->metodo; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre_dispositivo" class="form-label">Dispositivo</label>
                                    <input type="text" class="form-control text-bg-dark" id="nombre_dispositivo" name="nombre_dispositivo" value="<?php echo $ticket->nombre_dispositivo; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="desc" class="form-label">Descripción</label>
                                    <input type="text" class="form-control text-bg-dark" id="desc" name="desc" value="<?php echo $ticket->desc; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="desc_tecnico" class="form-label">Descripción Técnica</label>
                                    <input type="text" class="form-control text-bg-dark" id="desc_tecnico" name="desc_tecnico" value="<?php echo $ticket->desc_tecnico; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="local" class="form-label">Local</label>
                                    <input type="text" class="form-control text-bg-dark" id="local" name="local" value="<?php echo $ticket->local; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_pago" class="form-label">Fecha Pago</label>
                                    <input type="datetime-local" class="form-control text-bg-dark" id="fecha_pago" name="fecha_pago" value="<?php echo $ticket->fecha_pago; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="avisos" class="form-label">Avisos</label>
                                    <input type="text" class="form-control text-bg-dark" id="avisos" name="avisos" value="<?php echo $ticket->avisos; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="garantia" class="form-label">Garantía</label>
                                    <input type="text" class="form-control text-bg-dark" id="garantia" name="garantia" value="<?php echo $ticket->garantia; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <input type="text" class="form-control text-bg-dark" id="estado" name="estado" value="<?php echo $ticket->estado; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="razon" class="form-label">Razón</label>
                                    <input type="text" class="form-control text-bg-dark" id="razon" name="razon" value="<?php echo $ticket->razon; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="dept" class="form-label">Departamento</label>
                                    <input type="text" class="form-control text-bg-dark" id="dept" name="dept" value="<?php echo $ticket->dept; ?>">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" form="ticketForm<?php echo $ticket->id; ?>" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }
    ?>
</table>