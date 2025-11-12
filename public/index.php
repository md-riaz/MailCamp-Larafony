<?php

/**
 * MailCamp Entry Point
 * Simple router for Larafony MVC framework
 */

// Load configuration
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Load routes
$routes = require __DIR__ . '/../routes/web.php';

// Get current request
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Match route
$matched = false;
foreach ($routes as $route => $handler) {
    list($routeMethod, $routePath) = explode(' ', $route);
    
    // Convert route pattern to regex
    $pattern = preg_replace('/:[a-zA-Z_]+/', '([^/]+)', $routePath);
    $pattern = '#^' . $pattern . '$#';
    
    if ($routeMethod === $method && preg_match($pattern, $uri, $matches)) {
        $matched = true;
        array_shift($matches); // Remove full match
        
        // Parse handler
        list($controller, $action) = explode('@', $handler);
        $controllerClass = "App\\Controllers\\{$controller}";
        
        // Instantiate controller and call action
        $controllerInstance = new $controllerClass();
        call_user_func_array([$controllerInstance, $action], $matches);
        
        break;
    }
}

// Handle DELETE method (from POST with _method=DELETE)
if (!$matched && $method === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
    $method = 'DELETE';
    foreach ($routes as $route => $handler) {
        list($routeMethod, $routePath) = explode(' ', $route);
        
        $pattern = preg_replace('/:[a-zA-Z_]+/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        if ($routeMethod === $method && preg_match($pattern, $uri, $matches)) {
            $matched = true;
            array_shift($matches);
            
            list($controller, $action) = explode('@', $handler);
            $controllerClass = "App\\Controllers\\{$controller}";
            
            $controllerInstance = new $controllerClass();
            call_user_func_array([$controllerInstance, $action], $matches);
            
            break;
        }
    }
}

if (!$matched) {
    http_response_code(404);
    echo "404 - Page Not Found";
}
