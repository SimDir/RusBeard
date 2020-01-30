<?php

namespace mvcrb;

defined('ROOT') OR die('No direct script access.');

/**
 * Description of ErrorController
 *
 * @author mvcrb
 */
class ApiController extends Controller{
    public $apiName = ''; //users
    protected $method = ''; //GET|POST|PUT|DELETE
    public $requestUri = [];
    public $requestParams = [];
    protected $action = ''; //Название метод для выполнения

    public function __construct() {
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        //Массив GET параметров разделенных слешем
        $this->requestUri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $this->requestParams = $_REQUEST;

        //Определение метода запроса
        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }
    }

    public function IndexAction($method = null) {
        return ['Error'=>'not error',
            'Method'=>$method
            ];
    }
    public function UserAction($func = null) {
        return ['$func'=>$func,
            'Method'=>$this->method
            ];
    }
}
