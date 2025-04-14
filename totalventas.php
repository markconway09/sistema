<?php
require_once 'controller/functions.php';
require_once 'model/Database.php'; 

if(isset($_POST["dtotalB"])||isset($_POST["dtotalM"])||isset($_POST["dtotal"])){
    $date = explode("-",$_POST["dia"]);
}
if(isset($_POST["mtotalB"])||isset($_POST["mtotalM"])||isset($_POST["mtotal"])){
    $date = explode("-",$_POST["mes"]);
}
if(isset($_POST["tesB"])||isset($_POST["tesM"])){
    $date = explode("-",$_POST["tesMes"]);
}
if(isset($_POST["gastosB"])||isset($_POST["gastosM"])||isset($_POST["gastosTotal"])){
    $date = explode("-",$_POST["gastos"]);
}

if(isset($_POST["dtotalB"])) totalVentas($date[2],$date[1],$date[0],"Barcelona");
if(isset($_POST["dtotalM"])) totalVentas($date[2],$date[1],$date[0],"Mataro");
if(isset($_POST["dtotal"])) totalVentas($date[2],$date[1],$date[0]);
if(isset($_POST["mtotalB"])) totalVentas(0,$date[1],$date[0],"Barcelona");
if(isset($_POST["mtotalM"])) totalVentas(0,$date[1],$date[0],"Mataro");
if(isset($_POST["mtotal"])) totalVentas(0,$date[1],$date[0]);
if(isset($_POST["tesB"])) tesoreria($date[1],$date[0],"Barcelona");
if(isset($_POST["tesM"])) tesoreria($date[1],$date[0],"Mataro");
if(isset($_POST["gastosB"])) totalGastos($date[1],$date[0],"Barcelona");
if(isset($_POST["gastosM"])) totalGastos($date[1],$date[0],"Mataro");
if(isset($_POST["gastosTotal"])) totalGastos($date[1],$date[0]);
?>

        <div class="container border border-secondary text-bg-dark rounded mt-5 p-5 text-center">
            <div class="row text-center">
                <div class="col-12">
                    <h1 class="display-2">Total Ventas</h1>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 col-md-4 mx-auto">
                    <form action="totalventas.php" target="_blank" method="post">
                        <label for="dia">Total/Día</label>
                        <input type="date" name="dia" id="dia" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        <button type="submit" class="btn btn-primary mt-2" name="dtotalB">Barcelona</button>
                        <button type="submit" class="btn btn-danger mt-2" name="dtotalM">Mataró</button>
                        <button type="submit" class="btn btn-success mt-2" name="dtotal">Total <i class="bi bi-arrow-right"></i></button>
                    </form>
                </div>
                <div class="col-12 col-md-4 mx-auto">
                    <form action="totalventas.php" target="_blank" method="post">
                        <label for="mes">Total/Mes</label>
                        <input type="month" name="mes" id="mes" class="form-control" value="<?php echo date('Y-m'); ?>">
                        <button type="submit" class="btn btn-primary mt-2" name="mtotalB">Barcelona</button>
                        <button type="submit" class="btn btn-danger mt-2" name="mtotalM">Mataró</button>
                        <button type="submit" class="btn btn-success mt-2" name="mtotal">Total <i class="bi bi-arrow-right"></i></button>
                    </form>
                </div>
            </div>
            <hr class="my-5">
            <div class="row mt-3">
                <div class="col-12 col-md-4 mx-auto">
                    <form action="totalventas.php" target="_blank" method="post">
                        <label for="tesMes">Suma Total/Día</label>
                        <input type="month" name="tesMes" id="tesMes" class="form-control" value="<?php echo date('Y-m'); ?>">
                        <button type="submit" class="btn btn-primary mt-2" name="tesB">Barcelona</button>
                        <button type="submit" class="btn btn-danger mt-2" name="tesM">Mataró</button>
                    </form>
                </div>
                <!-- <div class="col-12 col-md-4 mx-auto">
                    <form action="totalventas.php" target="_blank" method="post">
                        <label for="gastos">Reporte Gastos</label>
                        <input type="month" name="gastos" id="gastos" class="form-control" value="<?php echo date('Y-m'); ?>">
                        <button type="submit" class="btn btn-primary mt-2" name="gastosB">Barcelona</button>
                        <button type="submit" class="btn btn-danger mt-2" name="gastosM">Mataró</button>
                        <button type="submit" class="btn btn-success mt-2" name="gastosTotal">Total <i class="bi bi-arrow-right"></i></button>
                    </form>
                </div> -->
            </div>
        </div>