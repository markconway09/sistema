<div class="container p-5 text-bg-dark">
    <h1 class="text-center">Gestión de Dispositivos</h1>
    <div class="row">
        <div class="col-12 col-lg-6 mx-auto" style="overflow-y: hidden;">
            <div class="text-center p-5 rounded h-100" style="border: 2px solid #007bff;">
                <h2>Subir CSV</h2>
                <form action="controller/importPartes.php" target="_blank" method="post" enctype="multipart/form-data">
                    <div class="form-group mb-3">
                        <button type="submit" class="btn btn-primary mt-2 mb-4"><i class="bi bi-cloud-upload fs-5"></i></button>
                        <br>
                        <input type="file" name="csv_file" id="csv_file" accept=".csv" class="form-control form-control-sm" required>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-12 col-lg-6 mx-auto">
            <div class="text-center p-5 rounded h-100" style="border: 2px solid #198754;">
                <h2>Exportar CSV</h2>
                <form action="controller/exportModelos.php" target="_blank" method="post">
                    <button type="submit" class="btn btn-success mt-2 mb-4"><i class="bi bi-cloud-download fs-5"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>