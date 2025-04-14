<?php
// INITIALIZE DB CONNECTION
$db = new Database();
include_once "views/functions_ticket.php";
?>

<?php
if (isset($_GET["id"]) || isset($id)) {
    include 'views/ticket.php';
} else {
    echo '<div class="container my-4 px-4 bg-light rounded">';
    include 'views/list_ajax.php';
    echo '</div>';
}
?>

 <script>
     const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
     const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
 </script>