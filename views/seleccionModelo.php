<div class="col-12">
    <div class="row mb-3">
        <div class="col-lg-4 col-12">
            <div class="form-group">
                <div class="form-floating">
                    <select class="form-control form-select" id="deviceType" name="deviceType" required>
                        <option value="Reparación Móvil">Móvil</option>
                        <option value="Reparación Ordenador">Ordenador</option>
                        <option value="Reparación Tablet">Tablet</option>
                        <option value="Reparación Consola">Consola</option>
                        <option value="Mantenimiento Otros">Mantenimiento Otros</option>
                    </select>
                    <label for="deviceType">Tipo de Dispositivo</label>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            <div class="form-group mb-2">
                <div class="form-floating" style="z-index: 10;">
                    <select onchange="selectModelo(this.value)" id="modelo_select" class="form-select" name="modelo_select">
                        <option value="">Buscar modelo...</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            <div class="form-floating" id="otherDeviceInput">
                <input type="text" class="form-control" id="dispositivo" name="otroDispositivo" placeholder="Otro dispositivo">
                <label for="dispositivo">Otro dispositivo</label>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-12 text-light">
            <div class="form-floating" id="partsCheckboxes">
                <!-- Parts checkboxes will be appended here -->
            </div>
        </div>
    </div>
</div>

<script>
    // ON READY
    $(document).ready(function() {
        // SELECCIÓN DE MODELOS
        const modelo_select = document.getElementById("modelo_select");
        const availableItems = [];
        const availableId = [];
        $.ajax({
            url: 'controller/fetch_modelos.php', // The PHP file that returns data
            type: 'GET', // Method of the request
            dataType: 'json', // Expected data type (JSON)
            success: function(data) {
                data.forEach(function(item) {
                    if (item["modelo"] != "") {
                        availableItems.push(item["Modelo"]);
                        // availableId.push(item["ID"]);
                    }
                });

                for (let i = 0; i < availableItems.length; i++) {
                    var opt = document.createElement('option');
                    opt.value = availableItems[i];
                    opt.innerHTML = availableItems[i];
                    modelo_select.appendChild(opt);
                }
                const choices = new Choices(modelo_select, {
                    searchEnabled: true, // Enable searching
                    removeItemButton: true, // Enable remove button for selected items
                    searchResultLimit: 10, // Limit the number of search results
                    searchFloor: 1, // Start searching after 1 character
                    maxItemCount: 5, // Max number of items allowed in the select
                    itemSelectText: ""
                });
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error: ' + status + ' - ' + error);
            }
        });
    });

    function selectModelo(modelo) {
        var partsCheckboxes = document.getElementById('partsCheckboxes');
        partsCheckboxes.innerHTML = '';
        $.ajax({
            url: 'controller/fetch_modelos.php', // The PHP file that returns data
            type: 'GET', // Method of the request
            dataType: 'json', // Expected data type (JSON)
            data: {modelo: modelo},
            success: function(data) {
                // TOTAL CHECKS PARA PRECIOS
                var checkboxIndex = 0;
                data.forEach(function(item) {
                    Object.keys(item).forEach(function(key, index) {
                        if (index >= 4 && item[key] !== "") { // Starting from the 5th column (index 4) and checking if value isn't empty
                            item[key] = item[key].replace(/,/g, '.');

                            // Add hidden input to store prices
                            var hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'prices[]';
                            hiddenInput.value = item[key];
                            partsCheckboxes.appendChild(hiddenInput);

                            var checkboxDiv = document.createElement('div');
                            checkboxDiv.className = 'form-check form-check-inline';

                            var checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.className = 'form-check-input';
                            checkbox.id = 'checkbox_' + key;
                            checkbox.name = 'parts[]';
                            checkbox.value = key.replace(/([A-Z])/g, ' $1').trim();
                            checkbox.setAttribute('onclick', 'updatePriceFromCheckbox(this, ' + item[key] + ', ' + checkboxIndex + ');'); // Pass index to function

                            var label = document.createElement('label');
                            label.className = 'form-check-label';
                            label.htmlFor = 'checkbox_' + key;
                            label.innerText = key.replace(/([A-Z])/g, ' $1').trim() + ' (' + item[key] + '€)';

                            checkboxDiv.appendChild(checkbox);
                            checkboxDiv.appendChild(label);
                            partsCheckboxes.appendChild(checkboxDiv);

                            checkboxIndex++;
                        }
                    });
                });
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error: ' + status + ' - ' + error);
            }
        });
    }
</script>