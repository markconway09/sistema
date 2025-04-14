<?php
// INITIALIZE DB CONNECTION
$db = new Database();
include_once "views/functions_ticket.php";
?>

 <div class="container border border-secondary my-4 px-4 bg-dark rounded">
    <?php
    if (isset($_GET["id"]) || isset($id)) {
        include 'views/ticket.php';
    } else {
        include 'views/list_ajax.php'; 
    }
    ?>
 </div>

 <script>
     const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
     const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
 </script>