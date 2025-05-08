<?php

namespace App\Core;

class Router
{
    private $routes = [];
    private $middlewares = [];

    public function addMiddleware($paths, $middleware)
    {
        $paths = is_array($paths) ? $paths : [$paths];
        if (is_string($middleware) && strpos($middleware, '@') !== false) {
            list($class, $method) = explode('@', $middleware);
            $middleware = ["App\\Middleware\\{$class}", $method];
        }
        foreach ($paths as $path) {
            $this->middlewares[$path][] = $middleware;
        }
    }

    public function get($path, $handler, $middlewares = [], $injectedData = [])
    {
        $this->addRoute('GET', $path, $handler, $middlewares, $injectedData);
    }

    public function post($path, $handler, $middlewares = [], $injectedData = [])
    {
        $this->addRoute('POST', $path, $handler, $middlewares, $injectedData);
    }

    public function put($path, $handler, $middlewares = [], $injectedData = [])
    {
        $this->addRoute('PUT', $path, $handler, $middlewares, $injectedData);
    }

    public function delete($path, $handler, $middlewares = [], $injectedData = [])
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares, $injectedData);
    }

    private function addRoute($method, $path, $handler, $middlewares = [], $injectedData = [])
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares,
            'injectedData' => $injectedData
        ];
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $data = $this->getRequestData();

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path, $params)) {
                $data = array_merge($data, $params);
                $middlewareData = [];

                // Middlewares globais (addMiddleware)
                foreach ($this->middlewares as $middlewarePath => $middlewares) {
                    if ($this->matchMiddlewarePath($middlewarePath, $path)) {
                        foreach ($middlewares as $middleware) {
                            if (is_array($middleware)) {
                                $instance = new $middleware[0]();
                                $methodName = $middleware[1];
                                $result = $instance->$methodName($data, $route['injectedData']);
                                if ($result !== null) {
                                    $middlewareData = array_merge($middlewareData, $result);
                                }
                            } else {
                                $result = $middleware($data, $route['injectedData']);
                                if ($result !== null) {
                                    $middlewareData = array_merge($middlewareData, $result);
                                }
                            }
                        }
                    }
                }

                // Middlewares da rota
                foreach ($route['middlewares'] as $middleware) {
                    if (is_string($middleware) && strpos($middleware, '@') !== false) {
                        list($class, $methodName) = explode('@', $middleware);
                        $instance = new ("App\\Middleware\\{$class}")();
                        $result = $instance->$methodName($data, $route['injectedData']);
                        if ($result !== null) {
                            $middlewareData = array_merge($middlewareData, $result);
                        }
                    } elseif (is_array($middleware)) {
                        $instance = new $middleware[0]();
                        $methodName = $middleware[1];
                        $result = $instance->$methodName($data, $route['injectedData']);
                        if ($result !== null) {
                            $middlewareData = array_merge($middlewareData, $result);
                        }
                    } else if (is_callable($middleware)) {
                        $result = $middleware($data, $route['injectedData']);
                        if ($result !== null) {
                            $middlewareData = array_merge($middlewareData, $result);
                        }
                    }
                }

                // Chama o controller, passando $data, $middlewareData, $route['injectedData']
                list($controller, $action) = explode('@', $route['handler']);
                $controllerClass = "App\\Controllers\\{$controller}";
                
                // Verifica se o controller existe
                if (!class_exists($controllerClass)) {
                    http_response_code(501);
                    return [
                        'status' => 'error',
                        'message' => 'Controller não implementado'
                    ];
                }

                $controllerInstance = new $controllerClass();
                
                // Verifica se o método existe
                if (!method_exists($controllerInstance, $action)) {
                    http_response_code(501);
                    return [
                        'status' => 'error',
                        'message' => 'Método não implementado'
                    ];
                }

                return call_user_func([
                    $controllerInstance,
                    $action
                ], $data, $middlewareData, $route['injectedData']);
            }
        }

        // Rota não encontrada
        http_response_code(404);
        return ['error' => 'Rota não encontrada'];
    }

    private function matchPath($routePath, $requestPath, &$params = [])
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));

        if (count($routeParts) !== count($requestParts)) {
            return false;
        }

        $params = [];
        for ($i = 0; $i < count($routeParts); $i++) {
            if (preg_match('/^{(.+)}$/', $routeParts[$i], $matches)) {
                $params[$matches[1]] = $requestParts[$i];
            } elseif ($routeParts[$i] !== $requestParts[$i]) {
                return false;
            }
        }

        return true;
    }

    private function matchMiddlewarePath($middlewarePath, $requestPath)
    {
        if ($middlewarePath === '/*') {
            return true;
        }
        if (substr($middlewarePath, -2) === '/*') {
            $prefix = substr($middlewarePath, 0, -2);
            return strpos($requestPath, $prefix) === 0;
        }
        return $middlewarePath === $requestPath;
    }

    private function getRequestData()
    {
        $data = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
            $data = $_POST;
        }
        $json = file_get_contents('php://input');
        if ($json) {
            $jsonData = json_decode($json, true);
            if ($jsonData) {
                $data = array_merge($data, $jsonData);
            }
        }
        $queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        if ($queryString) {
            parse_str($queryString, $queryData);
            $data = array_merge($data, $queryData);
        }
        if (!empty($_FILES)) {
            $data['files'] = $this->processFiles($_FILES);
        }
        return $data;
    }

    private function processFiles($files)
    {
        $processed = [];
        foreach ($files as $key => $file) {
            if (is_array($file['name'])) {
                $processed[$key] = [];
                foreach ($file['name'] as $index => $name) {
                    $processed[$key][] = [
                        'name' => $name,
                        'type' => $file['type'][$index],
                        'tmp_name' => $file['tmp_name'][$index],
                        'error' => $file['error'][$index],
                        'size' => $file['size'][$index]
                    ];
                }
            } else {
                $processed[$key] = $file;
            }
        }
        return $processed;
    }
}
