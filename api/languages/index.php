<?php

header("Access-Control-Allow-Origin: *");        
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PATCH, PUT");
header('Access-Control-Allow-Headers: Content-Type');

require_once("../../classes/Languages.php");


$languages = new Languages();
$languages->response();