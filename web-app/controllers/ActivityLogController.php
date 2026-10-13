<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Controller.php';

class ActivityLogController extends Controller
{
    public function index(): void
    {
        $this->requireRole('admin');
        
        $logs = (new ActivityLog())->findAll();
        
        $this->render('activity_log/index', ['title' => 'System Activity Log', 'logs' => $logs], 'dashboard');
    }
}
