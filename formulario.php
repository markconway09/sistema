<?php
// LOGICA PARA DECIDIR QUÉ FORMULARIO MOSTRAR
    $title = '<h1 class="display-5 text-light text-center mb-4">Cliente</h1>';
    $hidden = '';
    if(isset($_GET["id"])) $backbtn = '<a href="list&id='. $_GET["id"] .'" class="btn btn-secondary">Volver</a>';

    if(isset($_GET["form"])){
        if($_GET["form"] == "garantia"){
            $title = $backbtn.'<h1 class="display-5 text-light text-center mb-4">Garantía para # '. $_GET["id"] .'</h1>';
            $hidden = '<input type="hidden" name="garantia" value="'. $_GET["id"] .'">';
        }
    }

    $before = ' <form action="list" method="POST" class="form-control p-4 bg-dark border-secondary" enctype="multipart/form-data">'.$title;
    $after = '      <div class="row">
                        <div class="col-12">
                            '.$hidden.'
                            <button class="mx-auto save-ticket" name="guardar-servicio">
                                <div class="svg-wrapper-1">
                                    <div class="svg-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                                        <path fill="none" d="M0 0h24v24H0z"></path>
                                        <path fill="currentColor" d="M1.946 9.315c-.522-.174-.527-.455.01-.634l19.087-6.362c.529-.176.832.12.684.638l-5.454 19.086c-.15.529-.455.547-.679.045L12 14l6-8-8 6-8.054-2.685z"></path>
                                    </svg>
                                    </div>
                                </div>
                                <span>Guardar</span>
                            </button>
                        </div>
                    </div>
                </form>';
?>
                
<?php
    echo $before;
    include 'views/registro_cliente.php';
?>
    <hr class="text-light">
<?php
    echo '<h1 class="display-5 text-light text-center mb-4">Servicio</h1>';
    include 'views/registro_servicio.php';
    echo $after;
?>