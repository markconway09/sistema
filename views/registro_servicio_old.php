<?php
// LOGICA POR SI SE TIENE QUE RELLENAR ALGUNOS CAMPOS
if(isset($_GET["id"])){
    $db = new Database();
    $datos=$db->fetchId($_GET["id"]);
    $servicio = explode(": ", $datos->servicio);
}
$rellenar = false;
if(isset($_GET["form"])){
    if($_GET["form"] == "garantia") $rellenar = true;
}

?>

<div class="row">
    <div class="col-12 col-lg-6 mb-3">
        <?php if($rellenar) : ?>
            <input type="hidden" value="<?php echo $servicio[0];?>" id="tipo_servicio">
        <?php endif ?>
        <div class="form-floating">
            <select class="form-control form-select" name="servicio" id="servicio">
                <?php if($rellenar) : ?>
                    <option value="<?php echo $servicio[0];?>" selected>Actual: <?php echo $servicio[0];?></option>
                <?php endif ?>
                <option value="Reparación Móvil">Reparación Móvil</option>
                <option value="Reparación Ordenador">Reparación Ordenador</option>
                <option value="Reparación Consola">Reparación Consola</option>
                <option value="Reparación Tablet">Reparación Tablet</option>
                <option value="Mantenimiento Otros">Mantenimiento Otros</option>
                <option value="Servicio Desarrollo Web">Servicio Desarrollo Web</option>
            </select>
            <label for="servicio">Tipo de Servicio</label>
        </div>
    </div>
    <div class="col-12 col-lg-6 mb-3">
        <div class="form-floating">
            <?php if($rellenar && isset($servicio[1])) : ?>
                <input type="text" class="form-control" value="<?php echo $servicio[1];?>" id="servicio2" name="servicio2">
                <label for="servicio2">Servicio</label>
            <?php endif ?>
            <?php if(!$rellenar) : ?>
            <select class="form-control form-select" name="servicio2" id="servicio2">
                <option value="">Selecciona un tipo</option>
            </select>
            <label for="servicio2">Servicio</label>
            <?php endif ?>
        </div>
    </div>
</div>
<div class="row mb-3">
    <?php if(!$rellenar): ?>
    <div class="col-lg-7 col-12">
    <?php endif; ?>
    <?php if($rellenar): ?>
    <div class="col-12">
    <?php endif; ?>
        <div class="form-floating mb-3">
            <input class="form-control" placeholder="Dispositivo" type="text" name="dispositivo" id="dispositivo"
                <?php if($rellenar) : ?>
                    value="<?php echo $datos->nombre_dispositivo; ?>"
                <?php endif ?>
            >
            <label for="dispositivo">Dispositivo</label>
        </div>
        <div class="form-floating mb-3">
            <textarea rows="7" style="height:100%;" class="form-control" placeholder="Descripción" name="motivo" id="motivo"><?php if($rellenar){ echo $datos->desc;}?></textarea>
            <label for="motivo">Descripción</label>
        </div>
    </div>
    <?php if(!$rellenar): ?>
    <div class="col-lg-5 col-12">
        <div class="form-control">
            <div id="signature"></div>
            <input type="hidden" name="sign" id="sign">
            <button class="btn btn-secondary btn-sm" id="clear">Borrar</button>
        </div>
    </div>
    <?php endif; ?>
</div>
<div class="row mb-4">
    <div class="col-lg-3 mx-auto">
        <label for="imageUpload" class="btn btn-light w-100 h-100"><i class="bi bi-camera-fill"></i>&nbsp;Subir Fotos</label>
        <input type="file" id="imageUpload" accept="image/*" name="images[]" capture="environment" multiple class="d-none form-control form-control-lg">
    </div>
</div>
<div class="row">
    <div class="col-12 col-md-4 mb-3">
        <div class="form-floating">
            <input class="form-control" onkeyup="findTotal()" placeholder="Precio" type="number" step="0.01" name="precio" id="precio"
                value=0>
            <label for="precio">Precio €</label>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <div class="input-group">
            <div class="form-floating">
                <input class="form-control" onkeyup="findTotal()" placeholder="Descuento" type="number" step="0.1" name="descuento" id="descuento"
                    value=0>
                <label for="descuento">Descuento</label>
            </div>
            <div class="form-floating">
                <input class="form-control" onkeyup="findTotal()" placeholder="Iva 21%" type="number" step="0.1" name="iva" id="iva"
                    value=21>
                <label for="iva">Iva 21%</label>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <div class="form-floating">
            <input class="form-control" onkeyup="findPrecio()" placeholder="Precio Final" step="0.01" type="number" name="precio-final" id="precio-final"
                value=0>
            <label for="precio-final">Precio Final €</label>
        </div>
    </div>
</div>

<script src="controller/jSignature/jSignature.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    // SELECT SERVICIOS
    function cambiarServicios(tipo) {
        switch (tipo) {
            case "Reparación Móvil":
                var newOptions = {
                    "Otros": "",
                    "Cambio de pantalla": "",
                    "Cambio de batería": "",
                    "Reparación de tapa": "",
                    "Reparación de flex de carga": "",
                    "Reparación de altavoces y microfonos": "",
                    "Desbloqueo de teléfonos": "",
                    "Recuperacion de datos": "",
                    "Reparación de daños por agua": "",
                    "Reemplazo de carcasa": "",
                    "Reparación de botones": "",
                    "Reparación de Bluetooth y Wi-Fi": "",
                    "Reparación de sensores": "",
                    "Reemplazo de SIM y bandejas": "",
                    "Instalación de aplicaciones": "",
                    "Reparación de problemas de sobrecalentamiento": "",
                    "Restauración de fabrica": "",
                    "Reparación de camaras frontales y traseras": "",
                    "Reparación de problemas de carga inalambrica": "",
                    "Desinfección del dispositivo": ""
                };
                break;
            case "Reparación Ordenador":
                var newOptions = {
                    "Otros": "",
                    "Reparación de pantalla": "",
                    "Reparación de teclado": "",
                    "Reparación de placa de la torre": "",
                    "Reparación de software": "",
                    "Reparación de altavoces y microfonos": "",
                    "Recuperacion de datos": "",
                    "Reparación de daños por agua": "",
                    "Actualizacion de hardware": "",
                    "Reparación de Bluetooh y Wi-Fi": "",
                    "Desinfección del dispositivo": ""
                };
                break;
            case "Reparación Consola":
                var newOptions = {
                    "Otros": "",
                    "Mantenimiento": "",
                    "Mantenimiento preventivo": "",
                    "Asesoramiento sobre accesorios": "",
                    "Servicios de personalización": ""
                };
                break;
            case "Reparación Tablet":
                var newOptions = {
                    "Otros": "",
                    "Cambio de pantalla": "",
                    "Reparación de tapa": "",
                    "Reparación de altavoces y microfonos": "",
                    "Recuperacion de datos": "",
                    "Reparación de daños por agua": "",
                    "Reemplazo de carcasa": "",
                    "Reparación de botones": "",
                    "Reparación de Bluetooh y Wi-Fi": "",
                    "Reparación de sensores": "",
                    "Instalación de aplicaciones": "",
                    "Reparación de problemas de sobrecalentamiento": "",
                    "Restauración de fabrica": "",
                    "Reparación de camaras frontales y traseras": "",
                    "Desinfección del dispositivo": ""
                };
                break;
            case "Mantenimiento Otros":
                var newOptions = {
                    "Otros": "",
                    "Mantenimiento": "",
                    "Mantenimiento preventivo": "",
                    "Asesoramiento sobre accesorios": "",
                    "Servicios de personalización": ""
                };
                break;
            case "Servicio Desarrollo Web":
                var newOptions = {
                    "Otros": "",
                    "Creación Página Web": "",
                    "Mantenimiento Página Web": ""
                };
                break;
        }
        var $el = $("#servicio2");
        $el.empty(); // remove old options
        $.each(newOptions, function(key, value) {
            $el.append($("<option></option>")
                .attr("value", key).text(key));
        });
    }
    $('#servicio').on('change', function() {
        cambiarServicios(this.value)
    });

    // ON READY
    $(document).ready(function() {
        var $sigdiv = $("#signature").jSignature();
        $(".jSignature").height(192);
        $(".jSignature").attr("height",192);
        $("#clear").click(borrar());
        borrar();
        
        function borrar() {
            event.preventDefault(); // Prevent form submission
            $sigdiv.jSignature("reset");
        }

        $("#clear").click(function() {
            event.preventDefault(); // Prevent form submission
            $sigdiv.jSignature("reset");
        });

        function saveSignature() {
            event.preventDefault(); // Prevent form submission
            var data = $sigdiv.jSignature("getData");
            console.log(data);
            $("#sign").val(data); // Store it in a hidden field
        }

        // Trigger save on mouseup
        $("#signature").on("mouseup", function() {
            saveSignature();
        });

        // Optional: Trigger save on touchend for mobile support
        $("#signature").on("touchend", function() {
            saveSignature();
        });

        // CODIGO REFERENCIA
        const select_code = document.getElementById("cod_ref");
        const availableItems = [];
        $.ajax({
            url: 'controller/ajax_query.php', // The PHP file that returns data
            type: 'GET', // Method of the request
            data: { call: 1 },
            dataType: 'json', // Expected data type (JSON)
            success: function(data) {
                data.forEach(function(item) {
                    availableItems.push(item["codigo_socio"]);
                });

                for (let i = 0; i < availableItems.length; i++) {
                    var opt = document.createElement('option');
                    opt.value = availableItems[i];
                    opt.innerHTML = availableItems[i];
                    select_code.appendChild(opt);
                }
                const choices = new Choices(select_code, {
                    searchEnabled: true, // Enable searching
                    removeItemButton: true, // Enable remove button for selected items
                    searchResultLimit: 10, // Limit the number of search results
                    searchFloor: 1, // Start searching after 1 character
                    maxItemCount: 5, // Max number of items allowed in the select
                });
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error: ' + status + ' - ' + error);
            }
        });

        // CAMBIAR SERVICIO DEFAULT
        cambiarServicios("Reparación Móvil");
    });
    // CÁLCULOS IVA
    function findTotal() {
        var precio = parseFloat(document.getElementById('precio').value);
        var iva = parseFloat(document.getElementById('iva').value);
        var final = parseFloat(document.getElementById('precio-final').value);
        var descuento = parseFloat(document.getElementById('descuento').value);
        let calc = precio - ((precio*descuento)/100);
        calc = calc + (calc*(iva/100));
        document.getElementById('precio-final').value = calc.toFixed(2);
    }
    function findPrecio() {
        var precio = parseFloat(document.getElementById('precio').value);
        var iva = parseFloat(document.getElementById('iva').value);
        var final = parseFloat(document.getElementById('precio-final').value);
        let calc = (final/(100+iva))*100;
        document.getElementById('precio').value = calc.toFixed(2);
    }
    function updateSession(selectedValue) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "index.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                location.reload();
            }
        };
        xhr.send("valueLocal=" + encodeURIComponent(selectedValue));
    }
</script>