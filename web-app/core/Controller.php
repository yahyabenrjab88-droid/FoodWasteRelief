<?php
declare(strict_types=1);

if (!function_exists('e')) {
    function e(?string $str): string {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('time_ago')) {
    function time_ago(?string $datetime): string {
        if (empty($datetime)) return "Date inconnue";
        $time = strtotime($datetime);
        if (!$time) return "Date invalide";
        $diff = time() - $time;
        
        if ($diff < 60) return "À l'instant";
        if ($diff < 3600) return floor($diff / 60) . " min";
        if ($diff < 86400) return floor($diff / 3600) . " h";
        if ($diff < 2592000) return floor($diff / 86400) . " j";
        if ($diff < 31536000) return floor($diff / 2592000) . " mois";
        return floor($diff / 31536000) . " an(s)";
    }
}

class Controller
{
    public function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewPath = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            die("View not found: " . $viewPath);
        }

        $content = (function() use ($viewPath, $data): string {
            extract($data);
            $this_controller = $this; 
            ob_start();
            
            $__render = \Closure::bind(function() use ($viewPath, $data) {
                extract($data);
                require $viewPath;
            }, $this);
            $__render();
            return ob_get_clean();
        })->call($this);

        $layoutPath = __DIR__ . '/../views/layouts/' . $layout . '.php';
        if (file_exists($layoutPath)) {
            
            $flash = $this->getFlash();
            
            $__layoutRender = \Closure::bind(function() use ($layoutPath, $data, $content, $flash) {
                extract($data);
                require $layoutPath;
            }, $this);
            $__layoutRender();
        } else {
            echo $content;
        }
    }

    public function redirect(string $url): void
    {
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    public function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function sanitize(string $input): string
    {
        return trim(strip_tags($input));
    }

    public function paginate(int $total, int $perPage, int $currentPage): array
    {
        $totalPages = ceil($total / $perPage);
        return [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'offset' => ($currentPage - 1) * $perPage,
            'has_next' => $currentPage < $totalPages,
            'has_prev' => $currentPage > 1
        ];
    }

    public function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type, 
            'message' => $message
        ];
    }

    public function getFlash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    public function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(Session::csrfToken()) . '">';
    }

    public function requireAuth(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->flash('error', 'Please log in to continue.');
            $this->redirect('/login');
        }
    }

    public function requireRole(...$roles): void
    {
        $this->requireAuth();
        
        if (count($roles) === 1 && is_array($roles[0])) {
            $roles = $roles[0];
        }

        if (!in_array($_SESSION['user']['role'], $roles, true)) {
            $this->flash('error', 'You do not have permission to access this page.');
            $this->redirect('/dashboard');
        }
    }
}
