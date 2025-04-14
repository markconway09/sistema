<?php

require "functions.php";

$pdo = connect();
if(isset($_POST["editpass"])){
    $stmt = $pdo->prepare("UPDATE `user` SET `password` = :pass WHERE `username` = :user");
    $user = $_POST["user"];
    $pass = password_hash($_POST["pass"], PASSWORD_DEFAULT);
    $stmt->bindParam(':user', $user);
    $stmt->bindParam(':pass', $pass);
} else {
    $stmt = $pdo->prepare("INSERT INTO `user` VALUES (null, :user, :pass, 'Barcelona', 'repartidor')");
    if(isset($_POST["insertuser"])){
        $user = $_POST["newuser"];
        $pass = password_hash($_POST["newpass"], PASSWORD_DEFAULT);
    } else {
        $user = "repartidor";
        $pass = password_hash("Barcelona123", PASSWORD_DEFAULT);
    }
    $stmt->bindParam(':user', $user);
    $stmt->bindParam(':pass', $pass);
}
try {
    $stmt->execute();
} catch(PDOException $e){
    echo $e->getMessage();
}