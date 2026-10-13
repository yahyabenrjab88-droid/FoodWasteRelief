<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Controller.php';

class UserController extends Controller
{
    public function index(): void
    {
        $this->requireRole('admin');
        $users = (new User())->findAll();
        $this->render('users/index', ['title' => 'Gérer les Utilisateurs', 'users' => $users], 'dashboard');
    }

    public function edit(string $id): void
    {
        $this->requireRole('admin');
        $user = (new User())->findById((int)$id);
        if (!$user) {
            $this->flash('error', 'Utilisateur introuvable.');
            $this->redirect('/admin/users');
        }
        $this->render('users/edit', ['title' => 'Modifier l\'Utilisateur', 'user' => $user], 'dashboard');
    }

    public function update(string $id): void
    {
        $this->requireRole('admin');
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->redirect('/admin/users');
        }

        $payload = [
            'nom' => $this->sanitize($_POST['nom'] ?? ''),
            'prenom' => $this->sanitize($_POST['prenom'] ?? ''),
            'telephone' => $this->sanitize($_POST['telephone'] ?? ''),
            'role' => $this->sanitize($_POST['role'] ?? 'client'),
            'email' => filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: ''
        ];

        (new User())->update((int)$id, $payload);
        $this->flash('success', 'Utilisateur mis à jour avec succès.');
        $this->redirect('/admin/users');
    }

    public function toggleActive(string $id): void
    {
        $this->requireRole('admin');
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->redirect('/admin/users');
        }

        (new User())->toggleActive((int)$id);
        $this->flash('success', 'Statut de l\'utilisateur modifié.');
        $this->redirect('/admin/users');
    }

    public function delete(string $id): void
    {
        $this->requireRole('admin');
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->redirect('/admin/users');
        }

        if ((int)$id === (int)$_SESSION['user']['id']) {
            $this->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            $this->redirect('/admin/users');
        }

        (new User())->delete((int)$id);
        $this->flash('success', 'Utilisateur supprimé.');
        $this->redirect('/admin/users');
    }
}
