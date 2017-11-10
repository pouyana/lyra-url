<?php 
include 'vendor/autoload.php'; 
include 'De/Uniwue/RZ/Lyra/URL/Logger.php';
global $config;

$proxy_host = getenv("PROXY_HOST") ? getenv("PROXY_HOST") : null;
$proxy_port = getenv("PROXY_PORT") ? getenv("PROXY_PORT") : null;
$proxy_auth = getenv("PROXY_AUTH") ?  getenv("PROXY_PORT") : null;
$proxy_type = getenv("PROXY_TYPE") ? getenv("PROXY_PORT") : null;

$config = array(
    "host" => $proxy_host,
    "port" => $proxy_port,
    "auth" => $proxy_auth,
    "type" => $proxy_type
);
