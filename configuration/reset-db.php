<?php


require_once("../classes/DB.php");

$conn = (new DB())->connect();

$sqlFile = file_get_contents("./reset-db.sql");

$query = $conn->query($sqlFile);


// print_r($sqlFile);