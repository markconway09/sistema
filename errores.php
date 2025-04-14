<div class="container text-bg-dark p-5 rounded">
<?php
$logFilePath = 'error_log.txt';

if (file_exists($logFilePath)) {
    // Read the contents of the error log file into an array
    $logContents = file($logFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (!empty($logContents)) {
        // Reverse the array to display newest errors at the top
        $logContents = array_reverse($logContents);

        // Print each error line
        foreach ($logContents as $line) {
            ?>
            <div class="card">
                <div class="tools">
                    <div class="circle">
                    <span class="red box"></span>
                    </div>
                    <div class="circle">
                    <span class="yellow box"></span>
                    </div>
                    <div class="circle">
                    <span class="green box"></span>
                    </div>
                </div>
                <div class="card__content">
                    <?php echo $line . "<br>"; ?>
                </div>
            </div>
            <?php
        }
    } else {
        echo "No errors found in the log file.";
    }
} else {
    echo "Error log file does not exist.";
}
?>
</div>
<style>
    /* From Uiverse.io by EmmaxPlay */ 
.card {
 width: 100%;
 height: auto;
 margin: 5px auto;
 background-color: #555;
 color: white;
 border-radius: 8px;
 z-index: 1;
}

.card__content{
    padding: 0 10px 10px;
}

.tools {
 display: flex;
 align-items: center;
 padding: 9px;
}

.circle {
 padding: 0 4px;
}

.box {
 display: inline-block;
 align-items: center;
 width: 10px;
 height: 10px;
 padding: 1px;
 border-radius: 50%;
}

.red {
 background-color: #ff605c;
}

.yellow {
 background-color: #ffbd44;
}

.green {
 background-color: #00ca4e;
}

</style>