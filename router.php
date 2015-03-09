<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
$requestURI = explode('/', $_SERVER['REQUEST_URI']);
$scriptName = explode('/', $_SERVER['SCRIPT_NAME']);

for ($i= 0; $i < sizeof($scriptName); $i++) {
    if ($requestURI[$i] == $scriptName[$i]) {
        unset($requestURI[$i]);
    }
}

$path = array_values($requestURI);
unset($path[sizeof($path) - 1]);
$path[sizeof($path) - 1] = $path[sizeof($path) - 1] . '.json';

$file = fopen(implode($path, '/'), 'r') or http_response_code(404);
echo fread($file, filesize(implode($path, '/')));
fclose($file);
?>
