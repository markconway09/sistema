<?php
require_once "model/Database.php";
require_once "model/Ticket.php";

if(isset($_GET["tipo"]) && isset($_GET["id"])){
    $db = new Database();
    $ticket = $db->fetchId($_GET["id"]);

    if($_GET["tipo"] == "ticket"){
        $ticket->generateTicket(0);
        $db->logChange($_SESSION["nombre"], "Ticket generado", $_GET["id"]);
    } else if($_GET["tipo"] == "factura"){
        $ticket->generateInvoice();
        $db->logChange($_SESSION["nombre"], "Factura generada", $_GET["id"]);
    }
} else { exit; }