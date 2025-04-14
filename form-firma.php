<?php
require_once "model/Database.php";
require_once "model/Ticket.php";

$db = new Database();
// GUARDAR DATOS
if(isset($_POST["guardar-firma"])){
    if(!empty($_POST["sign"])) $db->insertSignature($_GET["id"], $_POST['sign']);
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="favicon.ico" />
        <title>FIRMA</title>
        <!-- GFONTS -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        <!-- BOOTSTRAP -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <!-- JQUERY -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <!-- jSignature -->
        <script src="controller/jSignature/jSignature.min.js"></script>
        <style>
            body {
                font-family: "raleway";
                background-color: gray;
            }
        </style>
    </head>
    <body>
        <div class="container my-4">
            <form action="" method="POST" class="form-control text-bg-light">
                <div class="row m-3 rounded p-3 d-none d-md-block text-center" style="background-color:rgb(43,45,46);">
                    <img src="LOGO.png" alt="logo" class="img-fluid mx-auto w-25">
                </div>
                
                <div class="row px-md-5 mb-3">
                    <div class="col-12">
                        <h1 class="display-5 text-center mb-4">FIRMA</h1>
                        <div class="form-control text-center">
                            <div id="signature"></div>
                            <input type="hidden" name="sign" id="sign">
                            <button class="btn btn-secondary btn-lg" id="clear">Borrar</button>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <input type="submit" name="guardar-firma" class="btn btn-success btn-lg col-5 mx-auto" value="Enviar">
                </div>
            </form>
        </div>
    </body>
</html>
<script>
    // jSignature
    $(document).ready(function() {
        var $sigdiv = $("#signature").jSignature();
        var height = $(".jSignature").width()/2.5;
        $(".jSignature").height(height);
        $(".jSignature").attr("height",height);
        $("#clear").click(borrar());
        borrar();
        function borrar() {
            event.preventDefault(); // Prevent form submission
            $sigdiv.jSignature("reset");
        }

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
</script>