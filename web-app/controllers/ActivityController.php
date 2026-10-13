<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/ActivityLog.php';

class ActivityController extends Controller
{
    public function index(): void
    {
        $this->requireRole('admin');
        $logs = (new ActivityLog())->all();
        $this->render('admin/activity/index', ['title' => 'Activity Log', 'logs' => $logs], 'dashboard');
    }
}
