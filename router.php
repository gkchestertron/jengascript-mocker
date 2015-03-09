<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

$request = new Request();
class Request {
    public $rq_uri;
    public $rq_method;
    public $result = null;
    public $data;
    public $file_data;
    public $id;

    public function __construct() {
        $this->rq_uri    = explode('/', $_SERVER['REQUEST_URI']);
        $this->rq_method = $_SERVER['REQUEST_METHOD'];
        $this->getData();
        $this->processInput();
        $this->respond();
    }

    public function getData() {
        $path = array_values($this->rq_uri); // path as an array
        $this->id = array_pop($path); // get id or lack thereof
        $path[sizeof($path) - 1] = $path[sizeof($path) - 1] . '.json';

        $file_path = implode($path, '/');

        if (file_exists($file_path . '.temp')) {
            $file = fopen($file_path . '.temp', 'r') or http_response_code(500);
        } else if (file_exists($file_path)) {
            $old_file = fopen($file_path, 'r') or http_response_code(500);
            $new_file = fopen($file_path . '.temp', 'w');
            
            $this->file_data = fread($old_file, filesize($file_path));
            fwrite($new_file, $this->file_data);
            fclose($old_file);
            fclose($new_file);
            $file = fopen($file_path . '.temp', 'r') or http_response_code(500);
        } else {
            http_response_code(404);
        }
         
        echo($file_path);

        $this->file_data = fread($file, filesize($file_path . '.temp'));
        fclose($file);
        $this->data = json_decode($this->file_data, true);
    }
    
    public function processInput() {
        echo $this->file_data;
        echo 'here';
        switch ($this->rq_method) {
        case 'GET':
            if ($this->id) {
                $model  = $this->search($this->data, 'id', $id);

                if ($model) {
                    $this->result = json_encode($model);
                }
            } else {
                $this->result = $this->file_data;
            }
            break;
        case 'POST':
            break;
        case 'PUT':
            break;
        case 'DELETE':
            break;
        }
    }

    public function respond() {
        if (isset($this->result)) {
            echo $this->result;
        } else {
            http_response_code(404);
        }
    }

    public function search($array, $key, $value) {   
        foreach ($array as $subarray){  
            if (isset($subarray[$key]) && $subarray[$key] == $value)
                return $subarray;       
        } 
    }
}
?>
