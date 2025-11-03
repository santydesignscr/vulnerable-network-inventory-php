<?php
namespace NetInventory;

/**
 * Simple Router Class
 * 
 * ⚠️ VULNERABLE BY DESIGN - FOR EDUCATIONAL PURPOSES ONLY
 * 
 * This router handles URL routing with minimal security:
 * - No CSRF token validation
 * - Direct parameter passing to controllers
 * - No input validation
 */

class Router
{
    private $routes = [];
    private $app;
    
    public function __construct(App $app)
    {
        $this->app = $app;
    }
    
    /**
     * Register a GET route
     */
    public function get($path, $handler)
    {
        $this->addRoute('GET', $path, $handler);
    }
    
    /**
     * Register a POST route
     */
    public function post($path, $handler)
    {
        $this->addRoute('POST', $path, $handler);
    }
    
    /**
     * Add route to registry
     */
    private function addRoute($method, $path, $handler)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    /**
     * Dispatch the request
     * VULNERABLE: No validation of controller/method existence
     * VULNERABLE: Direct passing of $_GET/$_POST without sanitization
     */
    public function dispatch()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];
        
        // Remove query string and base path
        $requestUri = strtok($requestUri, '?');
        $requestUri = str_replace('/net-inventory/public', '', $requestUri);
        $requestUri = rtrim($requestUri, '/');
        
        if (empty($requestUri)) {
            $requestUri = '/';
        }
        
        // Try to match route
        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }
            
            $pattern = $this->convertPathToRegex($route['path']);
            
            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // Remove full match
                
                return $this->callHandler($route['handler'], $matches);
            }
        }
        
        // No route found
        http_response_code(404);
        echo $this->app->render('404.php');
    }
    
    /**
     * Convert route path to regex pattern
     */
    private function convertPathToRegex($path)
    {
        // Convert :param to capture group
        $pattern = preg_replace('/\/:([^\/]+)/', '/([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    /**
     * Call the route handler
     * VULNERABLE: No input validation, direct instantiation
     */
    private function callHandler($handler, $params = [])
    {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }
        
        if (is_string($handler)) {
            list($controllerClass, $method) = explode('@', $handler);
            
            // VULNERABLE: No validation that class exists or is safe to instantiate
            $controllerClass = "NetInventory\\Controller\\" . $controllerClass;
            
            if (!class_exists($controllerClass)) {
                die("Controller not found: {$controllerClass}");
            }
            
            $controller = new $controllerClass($this->app);
            
            if (!method_exists($controller, $method)) {
                die("Method not found: {$method}");
            }
            
            // VULNERABLE: Direct passing of parameters without validation
            return call_user_func_array([$controller, $method], $params);
        }
        
        die("Invalid route handler");
    }
    
    /**
     * Middleware helper (not implemented - deliberately missing security)
     * In a secure app, this would handle authentication, CSRF, etc.
     */
    public function middleware($name)
    {
        // VULNERABLE: No middleware implementation
        return $this;
    }
}
