<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Controller.php';

class BusinessController extends Controller
{
    public function index(): void
    {
        $this->requireRole('admin');
        $businesses = (new Business())->findAll();
        $this->render('businesses/index', ['title' => 'Manage Businesses', 'businesses' => $businesses], 'dashboard');
    }

    public function approve(string $id): void
    {
        $this->requireRole('admin');
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->redirect('/admin/businesses');
        }

        (new Business())->approve((int)$id);
        $this->flash('success', 'Business approved.');
        $this->redirect('/admin/businesses');
    }

    public function reject(string $id): void
    {
        $this->requireRole('admin');
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->redirect('/admin/businesses');
        }

        (new Business())->reject((int)$id);
        $this->flash('success', 'Business rejected.');
        $this->redirect('/admin/businesses');
    }
}
