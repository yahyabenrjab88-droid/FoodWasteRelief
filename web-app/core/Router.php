<?php
declare(strict_types=1);

class Router
{
    private array $routes = [];

    public function get(string $route, string $controller, string $action): void
    {
        $this->addRoute('GET', $route, $controller, $action);
    }

    public function post(string $route, string $controller, string $action): void
    {
        $this->addRoute('POST', $route, $controller, $action);
    }

    private function addRoute(string $method, string $route, string $controller, string $action): void
    {
        
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $route);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function dispatch(string $requestMethod, string $requestUri): void
    {
        $uri = parse_url($requestUri, PHP_URL_PATH);

        $basePath = BASE_URL;
        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        
        if ($uri === '') {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match($route['pattern'], $uri, $matches)) {
                $controllerName = $route['controller'];
                $actionName = $route['action'];

                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $controller = new $controllerName();
                call_user_func_array([$controller, $actionName], array_values($params));
                return;
            }
        }

        $this->handle404($uri);
    }

    private function handle404(string $uri): void
    {
        http_response_code(404);
        
        $controller = new class extends Controller {};
        $controller->render('errors/404', ['title' => 'Page Not Found', 'uri' => $uri], 'main');
    }
}
