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

$file_path = implode($path, '/');

if (file_exists($file_path . '.temp')) {
    $file = fopen($file_path. '.temp', 'r') or http_response_code(500);
} else if (file_exists($file_path)) {
    $file = fopen($file_path, 'r') or http_response_code(500);
} else {
    http_response_code(404);
}
 
$file_data = fread($file, filesize($file_path));
fclose($file);

switch ($_SERVER['REQUEST_METHOD']) {
case 'GET':
    if ($id) {
        $data   = json_decode($file_data, true);
        $model  = search($data, 'id', $id);
        $result = json_encode($model);
    } else {
        $result = $file_data;
    }
    break;
case 'POST':
    break;
case 'PUT':
    break;
case 'DELETE':
    break;
}

echo $result or http_response_code(404);

function search($array, $key, $value) {   
    foreach ($array as $subarray){  
        if (isset($subarray[$key]) && $subarray[$key] == $value)
            return $subarray;       
    } 
}
?>
