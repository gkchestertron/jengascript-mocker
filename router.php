<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

$request = new Request();

class Request {
    public $rq_uri;
    public $rq_method;
    public $rq_params;
    public $result = null;
    public $data;
    public $file_data;
    public $file_path;
    public $id;

    public function __construct() {
        $this->rq_uri    = explode('/', $_SERVER['REQUEST_URI']);
        $this->rq_method = $_SERVER['REQUEST_METHOD'];
        $this->getData();
        $this->parseIncomingParams();
        $this->processInput();
        $this->respond();
    }

    public function getData() {
        $path = array_values($this->rq_uri); // path as an array
        $this->id = array_pop($path); // get id or lack thereof
        array_shift($path);
        $path[sizeof($path) - 1] = $path[sizeof($path) - 1] . '.json';

        $file_path = implode($path, '/');

        if (file_exists($file_path . '.temp')) {
            $file = fopen($file_path . '.temp', 'r') or http_response_code(500);
        } else if (file_exists($file_path)) {
            // get data from original file
            $old_file = fopen($file_path, 'r') or http_response_code(500);
            $this->file_data = fread($old_file, filesize($file_path));
            fclose($old_file);

            // write to temp file
            $new_file = fopen($file_path . '.temp', 'w');
            fwrite($new_file, $this->file_data);
            fclose($new_file);
            $file = fopen($file_path . '.temp', 'r') or http_response_code(500);
        } else {
            http_response_code(404);
        }

        $this->file_path = $file_path . '.temp';
        $this->file_data = fread($file, filesize($file_path . '.temp'));
        fclose($file);
        $this->data = json_decode($this->file_data, true);
    }

    public function parseIncomingParams() {
        $parameters = array();
 
        // first of all, pull the GET vars
        if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $parameters);
        }
 
        // now how about PUT/POST bodies? These override what we got from GET
        $body = file_get_contents("php://input");
        $content_type = false;
        if(isset($_SERVER['CONTENT_TYPE'])) {
            $content_type = $_SERVER['CONTENT_TYPE'];
        }
        switch($content_type) {
            case "application/json":
                $body_params = json_decode($body);
                if($body_params) {
                    foreach($body_params as $param_name => $param_value) {
                        $parameters[$param_name] = $param_value;
                    }
                }
                $this->format = "json";
                break;
            case "application/x-www-form-urlencoded":
                parse_str($body, $postvars);
                foreach($postvars as $field => $value) {
                    $parameters[$field] = $value;
                }
                $this->format = "html";
                break;
            default:
                // we could parse other supported formats here
                break;
        }
        $this->rq_params = $parameters;
    }
    
    public function processInput() {
        switch ($this->rq_method) {
        case 'GET':
            if ($this->id) {
                $model  = $this->search($this->data, 'id', $this->id);

                if ($model) {
                    $this->result = json_encode($model);
                }
            } else {
                $this->result = $this->file_data;
            }
            break;

        case 'POST':
            $last_model = $this->data[sizeof($this->data) - 1];
            $model = $this->rq_params;
            $model['id'] = $last_model['id'] + 1;
            array_push($this->data, $model);
            $result = json_encode($this->data);
            $file = fopen($this->file_path, 'w');
            fwrite($file, $result);
            fclose($file);
            $this->result = $model;
            break;

        case 'PUT':
            $model = $this->search($this->data, 'id', $this->id);

            foreach ($model as $key => $value) {
                $model[$key] = $value;
            }

            $result = json_encode($this->data);
            $file   = fopen($this->file_path, 'w');
            fwrite($file, $result);
            fclose($file);
            $this->result = json_encode($model);
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
