<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Business.php';
require_once __DIR__ . '/../models/Association.php';
require_once __DIR__ . '/../models/ActivityLog.php';

class AdminController extends Controller
{
    public function dashboard(): void
    {
        $this->requireAuth('admin');
        $users = (new User())->all();
        $businessCount = count((new Business())->all());
        $associationCount = count((new Association())->all());
        $logs = (new ActivityLog())->all();
        $this->render('admin/dashboard', [
            'title' => 'Dashboard admin',
            'usersCount' => count($users),
            'businessCount' => $businessCount,
            'associationCount' => $associationCount,
            'logs' => array_slice($logs, 0, 10),
        ]);
    }
}
