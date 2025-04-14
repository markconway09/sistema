<?php
// LOGICA POR SI SE TIENE QUE RELLENAR ALGUNOS CAMPOS
if (isset($_GET["id"])) {
    $db = new Database();
    $datos = $db->fetchId($_GET["id"]);
    $servicio = explode(": ", $datos->servicio);
}
$rellenar = false;
if (isset($_GET["form"])) {
    if ($_GET["form"] == "garantia") $rellenar = true;
}

?>

<div class="row mb-3">
    <?php include_once "views/seleccionModelo.php"; ?>
</div>
<div class="row">
    <div class="col-7">
        <div class="form-floating mb-2">
            <textarea rows="8" style="height:100%;" class="form-control" placeholder="Descripción" name="motivo" id="motivo"><?php if ($rellenar) {
                                                                                                                                    echo $datos->desc;
                                                                                                                                } ?></textarea>
            <label for="motivo">Descripción</label>
        </div>
        <label for="imageUpload" class="btn btn-light w-100"><i class="bi bi-camera-fill"></i>&nbsp;Subir Fotos</label>
        <input type="file" id="imageUpload" accept="image/*" name="images[]" capture="environment" multiple class="d-none form-control form-control-lg" onchange="displayThumbnail(event)">
    </div>
    <?php if (!$rellenar): ?>
        <div class="col-lg-5 mx-auto col-12">
            <div class="form-control">
                <div id="signature"></div>
                <input type="hidden" name="sign" id="sign">
                <button class="btn btn-secondary btn-sm" id="clear">Borrar</button>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="row mb-3">
    <div id="thumbnailsContainer" style="margin-top: 20px;display: flex;flex-wrap: wrap;"></div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-floating">
            <input class="form-control" placeholder="Cantidad pagada" step=".01" type="number" name="pagado" id="pagado" value="0">
            <label for="pagado">Cantidad pagada</label>
        </div>
        <div class="form-check text-light mb-3 d-inline-block">
            <input class="form-check-input" type="checkbox" value="25" id="diagnostico" name="diagnostico" onchange="updatePagado(this.value)">
            <label class="form-check-label" for="diagnostico">
                Diagnóstico Pagado (25€)
            </label>
        </div>
    </div>
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

<style>
    .thumbnail {
        width: 100px;
        height: 100px;
        margin: 5px;
        object-fit: cover;
        border-radius: 8px;
    }
</style>

<script src="controller/jSignature/jSignature.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    // ON READY
    $(document).ready(function() {
        var $sigdiv = $("#signature").jSignature();
        $(".jSignature").height(192);
        $(".jSignature").attr("height", 192);
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
    });
    // CÁLCULOS IVA
    function findTotal() {
        var precio = parseFloat(document.getElementById('precio').value);
        var iva = parseFloat(document.getElementById('iva').value);
        var final = parseFloat(document.getElementById('precio-final').value);
        var descuento = parseFloat(document.getElementById('descuento').value);
        let calc = precio - ((precio * descuento) / 100);
        calc = calc + (calc * (iva / 100));
        document.getElementById('precio-final').value = calc.toFixed(2);
    }

    function findPrecio() {
        var precio = parseFloat(document.getElementById('precio').value);
        var iva = parseFloat(document.getElementById('iva').value);
        var final = parseFloat(document.getElementById('precio-final').value);
        let calc = (final / (100 + iva)) * 100;
        document.getElementById('precio').value = calc.toFixed(2);
    }

    function updateSession(selectedValue) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "index.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                location.reload();
            }
        };
        xhr.send("valueLocal=" + encodeURIComponent(selectedValue));
    }

    function updatePriceFromCheckbox(checkbox, cost, index) {
        var precio = parseFloat(document.getElementById('precio-final').value);
        if (!checkbox.checked) {
            precio -= cost;
            removeHiddenCostInput(checkbox.id.split('_')[1]);
        } else {
            precio += cost;
            addHiddenCostInput(checkbox.id.split('_')[1], index);
        }
        document.getElementById('precio-final').value = precio.toFixed(2);
        findPrecio();
    }

    function addHiddenCostInput(id, index) {
        let hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'deviceServiceCosts[]';
        hiddenInput.id = 'cost_' + id;
        hiddenInput.value = index;
        document.querySelector('form').appendChild(hiddenInput);
    }

    function removeHiddenCostInput(id) {
        let hiddenInput = document.getElementById('cost_' + id);
        if (hiddenInput) {
            hiddenInput.remove();
        }
    }

    function updatePagado(cost) {
        var pagado = parseFloat(document.getElementById('pagado').value) || 0;
        var checkbox = document.getElementById('diagnostico');
        if (checkbox.checked) {
            pagado += parseFloat(cost);
        } else {
            pagado -= parseFloat(cost);
        }
        document.getElementById('pagado').value = pagado.toFixed(2);
    }


    function displayThumbnail(event) {
        const files = event.target.files;
        const thumbnailsContainer = document.getElementById('thumbnailsContainer');

        // Clear existing thumbnails
        thumbnailsContainer.innerHTML = '';

        // Loop through each file and create a thumbnail
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const reader = new FileReader();

            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('thumbnail');
                thumbnailsContainer.appendChild(img);
            };

            reader.readAsDataURL(file);
        }
    }
</script>