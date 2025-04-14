<div class="text-light">
    <div class="row mb-2">
        <div class="col-12">
            <!-- SEARCH -->
            <div class="row">
                <div class="col-12 my-2">
                    <div class="searchBox mt-2">
                        <input onkeyup="search(this.value)" onkeydown="search(this.value)" class="searchInput w-100" type="text" name="search" placeholder="Buscar... (Dispositivo, Id, Servicio...)">
                        <button class="searchButton">
                            <i class="bi bi-search" style="font-size: large;"></i>
                        </button>
                    </div>
                </div>
                <!-- LOCALES -->
                <?php if (isNotUser(["dependiente"])) { ?>
                    <div class="radio-inputs mx-auto col-12 col-lg-4 my-2">
                        <label class="radio">
                            <input name="local" type="radio" onchange="updateSession(this.value)" value="Todo" <?php echo $_SESSION["local"] == null ? 'checked' : '' ?>>
                            <span class="name">Todo</span>
                        </label>
                        <label class="radio">
                            <input name="local" type="radio" onchange="updateSession(this.value)" value="Barcelona" <?php echo $_SESSION["local"] == "Barcelona" ? 'checked' : '' ?>>
                            <span class="name">Barcelona</span>
                        </label>

                        <label class="radio">
                            <input name="local" type="radio" onchange="updateSession(this.value)" value="Mataró" <?php echo $_SESSION["local"] == "Mataró" ? 'checked' : '' ?>>
                            <span class="name">Mataró</span>
                        </label>
                    </div>
                <?php } ?>
            </div>
            <!-- TABS -->
            <div class="row">
                <ul class="nav nav-tabs bg-dark mt-2" id="list-tabs">
                    <?php
                    $filters = [
                        "0" => "Diagnóstico",
                        "1" => "Aprobación",
                        "2" => "Reparación",
                        "3" => "Terminado",
                        "4" => "Entregado",
                        "5" => "Devoluciones",
                        "6" => "Garantía",
                    ];

                    $tooltip = [
                        "Pendiente de diagnosticar el problema y dar presupuesto.",
                        "Esperando la aprobación del cliente.",
                        "Ticket aprobado, pendiente de reparación.",
                        "Reparación finalizada, esperando al cliente.",
                        "Ticket cerrado, entregado al cliente.",
                        "",
                        ""
                    ];
                    // Default case for "Todo"
                    echo '<li class="nav-item"><button data-filter="todo" class="nav-link ' . (!isset($_GET["filter"]) ? 'active' : 'text-light') . '">Todo</button></li>';

                    // Iterate through filters
                    foreach ($filters as $key => $label) {
                        $activeClass = (isset($_GET["filter"]) && $_GET["filter"] == $key) ? 'active' : 'text-light';
                        echo '<li class="nav-item"><button data-filter="' . $key . '" class="nav-link ' . $activeClass . '"
                                        data-bs-toggle="tooltip" data-bs-title="' . $tooltip[$key] . '">' . $label . ' <span id="badge-' . $key . '" class="badge text-bg-secondary">0</span></button></li>';
                    }
                    ?>
                </ul>
            </div>
            <div class="container-fluid bg-dark mt-2 p-2" id="list-select">
                <select class="form-select text-light bg-dark border-light" name="filter" onchange="selectFilter(this.value)">
                    <option value="" <?php echo !isset($_GET["filter"]) ? 'selected' : ''; ?>>Todo</option>
                    <?php
                    foreach ($filters as $key => $label) {
                        $selected = (isset($_GET["filter"]) && $_GET["filter"] == $key) ? 'selected' : '';
                        echo '<option value="' . $key . '" ' . $selected . ' title="' . $tooltip[$key] . '">' . $label . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>

    <!-- LIST -->
    <div id="list-container">
        <div class="spinner mx-auto my-5">
            <div class="spinner1"></div>
        </div>
    </div>
    <div id="loadMore"></div>
</div>

<script>
    var filter = 'todo'; // Default filter
    function updateSession(selectedValue) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "index.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                query();
            }
        };
        xhr.send("valueLocal=" + encodeURIComponent(selectedValue));
    }

    function selectFilter(f) {
        filter = f;
        search();
    }
    document.querySelectorAll('#list-tabs .nav-link').forEach(button => {
        button.addEventListener('click', () => {
            // Update the active tab styling
            document.querySelectorAll('#list-tabs .nav-link').forEach(link => {
                link.classList.remove('active');
                link.classList.add('text-light');
            });
            button.classList.add('active');
            button.classList.remove('text-light');

            // Update the filter variable
            filter = button.getAttribute('data-filter');
            search();
        });
    });
    var parameters = "";
    var limit = 15;
    const iconos = ['search', 'person-raised-hand', 'tools', 'check-lg', 'person-fill-check', 'arrow-counterclockwise'];
    const colores = ["#cb4351", "#ce9c3b", "#529651", "#4c6ca4", "#4a5467", "black"];
    const pasos = ["Diagnóstico", "Aprobación", "Reparación", "Terminado", "Entregado", "Devuelto"];
    const pasosLargo = ["Espera del diagnóstico", "Espera aprobación del cliente", "En Reparación", "Reparación terminada", "Entregado al cliente", "Devuelto/Cancelado"];
    const localColor = ["#25BED4", "#ff6b35"];
    const colorDias = ["", "#6b4643", "#913c36", "#9e2e2e", "#f13535"];

    function search(param) {
        if (param == "" || param == " ") {
            parameters = "";
        } else {
            parameters = param;
        }
        query();
    }

    function loadMore() {
        limit += 30;
        query();
    }

    $(document).ready(function() {
        query();
    });

    function updateBadges() {
        // Loop through each filter and update its badge
        <?php foreach ($filters as $key => $label): ?>
                (function(key, label) {
                    $.ajax({
                        url: 'controller/ajax_query.php',
                        type: 'GET',
                        data: {
                            call: 2,
                            search: parameters,
                            filter: key
                        },
                        dataType: 'json',
                        success: function(data) {
                            // Update the badge with the count from the response
                            document.getElementById('badge-' + key).innerHTML = data.length;
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error: ' + status + ' - ' + error);
                        }
                    });
                })('<?php echo $key; ?>', '<?php echo $label; ?>');
        <?php endforeach; ?>
    }

    function query() {
        // Function to make an AJAX call and fetch data
        $.ajax({
            url: 'controller/ajax_query.php',
            type: 'GET',
            data: {
                call: 2,
                search: parameters,
                filter: filter,
                limit: limit
            },
            dataType: 'json',
            success: function(data) {
                displayList(data);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error: ' + status + ' - ' + error);
            }
        });
        updateBadges();
    }

    function updateStep(id, estado) {
        if (confirm('Cambiar estado?')) {
            $.ajax({
                url: 'controller/ajax_query.php',
                type: 'GET',
                data: {
                    call: 3,
                    id: id,
                    estado: estado
                },
                dataType: 'json',
                success: function(data) {
                    query();
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ' + status + ' - ' + error);
                }
            });
        }
    }

    function displayList(data) {
        const loadMore = document.getElementById('loadMore');
        const container = document.getElementById('list-container');
        container.innerHTML = ""; // Clear loading spinner

        let rowContainer = null; // Initialize a row container

        data.forEach((item, index) => {
            let pastDate = new Date(item.date.split(' ')[0]);
            let now = new Date();
            let timeDiff = now - pastDate;
            let daysPassed = Math.floor(timeDiff / (1000 * 3600 * 24));
            if (daysPassed == 0) {
                daysPassed = "hoy";
            } else if (daysPassed == 1) {
                daysPassed = "ayer";
            } else {
                daysPassed = `hace ${daysPassed} día(s)`;
            }

            const div = document.createElement('div');
            div.className = "p-2 col-lg-4";
            // Create individual card
            const card = document.createElement('div');
            card.className = "card text-bg-light";
            card.style = "padding:0; border: none;";
            let estado = item.garantia == 0 ? pasos[item.estado] : "<i class='bi bi-file-text'></i> <a style='text-decoration:none;color:#FFA' href='list&id=" + item.garantia + "'>GARANTÍA <i class='bi bi-arrow-right-short'></i></a>";
            let desc = item.desc || "(No hay información)";
            let nombreDispositivo = item.nombre_dispositivo || "(No hay información)";
            if (nombreDispositivo.length > 35) {
                nombreDispositivo = nombreDispositivo.substring(0, 32) + '...';
            }
            let nombre = item.nombre || "(No hay información)";
            let dayIndex = Math.floor(timeDiff / (1000 * 3600 * 24)) > 4 ? 4 : Math.floor(timeDiff / (1000 * 3600 * 24));
            let colD = item.estado < 4 ? colorDias[dayIndex] : "";
            let disableLeft = (item.estado <= 0) || (item.estado == 4) ? true : false;
            let disableRight = item.estado > 2 ? true : false;
            const cardHtml = `
                <div class="card-header pt-3 text-left d-flex" style="color:white;background-color:${colores[item.estado]};">
                    <i style="padding: 0 15px 0 5px; font-size: 30px" class="bi bi-${iconos[item.estado]}"></i>    
                    <h5>`+ item.servicio.split(':')[0] +` # ${item.id}<br>
                    ${estado} | <span style="color:${localColor[item.local == "Barcelona" ? 0 : 1]}">${item.local}</span></h5>
                </div>
                <div class="card-body">
                    <p class="card-text text-truncate" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><i class="bi bi-person-fill"></i> ${nombre}</p>
                    <p class="card-text text-truncate" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><i class="bi bi-wrench-adjustable"></i> ${item.servicio}</p>
                    <p class="card-text text-truncate" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><i class="bi bi-phone-fill"></i> ${nombreDispositivo}</p>
                    <p class="card-text text-truncate" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><i class="bi bi-file-text-fill"></i> ${desc}</p>
                    <?php if (isNotUser(["tecnico"])) { ?>
                    <p class="card-text text-truncate" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <i class="bi bi-currency-exchange"> Precio: <b>${item['precio-final']}€</b>`+((item['pagado'] && item['estado']<4)? ` · Pagado: <b>`+item['pagado']+`€</b>` : ``)+`</i>
                    </p>
                    <?php } ?>
                    <div class="mb-3">
                        <div class="progress" role="progressbar" aria-label="Basic example" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: ` + ((item.estado + 1) * 20) + `%; background-color:${colores[item.estado]}">${pasos[item.estado]}</div>
                        </div>
                    </div>
                    <div class="cardIcons">
            <?php if (isNotUser(["tecnico"])) { ?>
                        <button class="cardBtn ` + (disableLeft ? 'disabled' : '') + `"
                                onclick="updateStep(${item.id}, ` + (item.estado - 1) + `)"
                                style="pointer-events: ` + (disableLeft ? 'none' : 'auto') + `;
                                color: white; background-color:` + (disableLeft ? '' : colores[item.estado - 1]) + `"
                                ` + ((item.estado==4 || item.estado==5) ? 'hidden' : '') + `>
                            <i class="bi bi-arrow-left"></i>
                        </button>
            <?php }  ?>
                        <a class="cardBtn" href="list&id=${item.id}"
                                style="color:white;background-color:#25BED4">
                            <i class="bi bi-info-circle"></i>
                        </a>
            <?php if (isNotUser(["repartidor"])) { ?>
                        <button class="cardBtn ` + (disableRight ? 'disabled' : '') + `"
                                onclick="updateStep(${item.id}, ` + (item.estado + 1) + `)"
                                style="pointer-events: ` + (disableRight ? 'none' : 'auto') + `;
                                color: white; background-color:` + (disableRight ? '' : colores[item.estado + 1]) + `"
                                ` + ((item.estado==4 || item.estado==5) ? 'hidden' : '') + `>
                            <i class="bi bi-arrow-right"></i>
                        </button>
            <?php }  ?>
                    </div>
                </div>
                <div class="card-footer">
                    <small class="text-muted col-6">${item.fecha} · <span style="color:${colD}">${daysPassed}</span></small>
                </div>
            `;
            card.innerHTML = cardHtml;
            div.append(card);

            // Check if a new row container is needed
            if (index % 3 === 0) {
                rowContainer = document.createElement('div');
                rowContainer.className = "row";
                container.appendChild(rowContainer);
            }

            // Append the card to the current row container
            rowContainer.appendChild(div);
        });

        loadMore.innerHTML = '<button onclick="loadMore()" style="color:white;background-color:rgba(37,190,212,1);" class="btn w-100 mx-auto mt-2 mb-4">Mostrar más <i class="bi bi-chevron-down"></i></button>';
    }

    // RELOAD TICKETS EVERY 10s
    setInterval(query, 10000);
</script>