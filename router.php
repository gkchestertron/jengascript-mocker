<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
$requestURI = explode('/', $_SERVER['REQUEST_URI']);
$scriptName = explode('/', $_SERVER['SCRIPT_NAME']);

// handles case where you are not in home directory
for ($i= 0; $i < sizeof($scriptName); $i++) {
    if ($requestURI[$i] == $scriptName[$i]) {
        unset($requestURI[$i]);
    }
}

$path = array_values($requestURI); // path as an array
$id = array_pop($path); // get id or lack thereof
$path[sizeof($path) - 1] = $path[sizeof($path) - 1] . '.json';

$file = fopen(implode($path, '/'), 'r') or http_response_code(404);
$file_data = fread($file, filesize(implode($path, '/')));
fclose($file);

if ($id) {
    $data = json_decode($file_data);
    $result = json_encode($data[0]);
} else {
    $result = $file_data;
}

echo $result;
?>
