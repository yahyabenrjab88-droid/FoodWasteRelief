<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Controller.php';

class AssociationController extends Controller
{
    public function index(): void
    {
        $this->requireRole('admin');
        $associations = (new Association())->findAll();
        $this->render('associations/index', ['title' => 'Manage Associations', 'associations' => $associations], 'dashboard');
    }

    public function approve(string $id): void
    {
        $this->requireRole('admin');
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->redirect('/admin/associations');
        }

        (new Association())->approve((int)$id);
        $this->flash('success', 'Association approved.');
        $this->redirect('/admin/associations');
    }

    public function reject(string $id): void
    {
        $this->requireRole('admin');
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->redirect('/admin/associations');
        }

        (new Association())->reject((int)$id);
        $this->flash('success', 'Association rejected.');
        $this->redirect('/admin/associations');
    }
}
