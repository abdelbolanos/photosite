<?php
namespace Router;

include 'routes.php';

class Router
{
    public $route;
    public $stringUri;

    public function __construct($stringUri)
    {
        $this->stringUri = $stringUri;
        $this->route = $this->getRoute($stringUri);
    }

    private function getRoute($stringUri)
    {
        global $routes;

        foreach ($routes as $routeConfig) {
            $routeMatch = $routeConfig['route'];
            $search = ["/"];
            $replace = ["\/"];
            $routeMatch = '/' . str_replace($search, $replace, $routeMatch) . '/';
            $match = preg_match($routeMatch, $stringUri);
            if ($match === 1) {
                return $routeConfig;
                break;
            }
        }

        return false;
    }

    public function processRoute()
    {
        $response = [
            'ResponseCode' => 404,
            'Route not Found' => $this->stringUri
        ];

        if ($this->route) {
            $controller = $this->route['controller'];
            $method = $this->route['action'];
            try {
                $instance = new $controller($this->route, $this->stringUri);
                $response = $instance->$method();
            } catch (\Exception $e) {
                $response = [
                    'ResponseCode' => 500,
                    'data' => [
                        'Controller' => $controller,
                        'Method' => $method,
                        'ErrorCode' => $e->getCode(),
                        'ErrorMessage' => $e->getMessage(),
                        'ErrorTrace' => $e->getTraceAsString(),
                    ]
                ];
            }
            
        }

        header('Access-Control-Allow-Origin: http://localhost:80');
        header('Access-Control-Allow-Origin: http://localhost:9000');
        http_response_code($response['ResponseCode']);

        switch ($this->route['type']) {
            case 'image':
                header('Content-Length: ' . filesize($response['data']['location']));
                header("Content-Type: " . $response['data']['content-type']);
                ob_clean();
                flush();
                readfile($response['data']['location']);
                break;
            default:
                header('Content-Type: application/json');
                echo json_encode($response['data']);
                break;
        }

    }
}