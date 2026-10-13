<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Controller.php';

class StorageController extends Controller
{
    private function getBusinessId(): int
    {
        $this->requireRole('business');
        if (!isset($_SESSION['business_id'])) {
            $biz = (new Business())->findByUserId((int)$_SESSION['user']['id']);
            if (!$biz) {
                $this->flash('error', 'Profil entreprise introuvable.');
                $this->redirect('/');
            }
            $_SESSION['business_id'] = (int)$biz['id'];
        }
        return (int)$_SESSION['business_id'];
    }

    public function index(): void
    {
        $businessId = $this->getBusinessId();
        $items = (new StorageItem())->findByBusiness($businessId);
        $this->render('storage/index', ['title' => 'Gestion du Stock', 'items' => $items], 'dashboard');
    }

    public function create(): void
    {
        $this->getBusinessId();
        $this->render('storage/create', ['title' => 'Ajouter un Article'], 'dashboard');
    }

    public function store(): void
    {
        $businessId = $this->getBusinessId();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/storage');
        }

        $itemName = trim($this->sanitize($_POST['item_name'] ?? ''));
        if (!$itemName) {
            $this->flash('error', 'Le nom de l\'article est requis.');
            $this->redirect('/storage/create');
        }

        $payload = [
            'business_id' => $businessId,
            'item_name'   => $itemName,
            'quantity'    => (float)($_POST['quantity'] ?? 0),
            'unit'        => $this->sanitize($_POST['unit'] ?? 'kg'),
            'expiry_date' => $this->sanitize($_POST['expiry_date'] ?? date('Y-m-d', strtotime('+30 days'))),
        ];

        $id = (new StorageItem())->create($payload);
        if ($id > 0) {
            $this->flash('success', 'Article ajouté au stock avec succès.');
        } else {
            $this->flash('error', 'Erreur lors de l\'ajout de l\'article.');
        }
        $this->redirect('/storage');
    }

    public function edit(string $id): void
    {
        $businessId = $this->getBusinessId();
        $item = (new StorageItem())->findByIdAndBusiness((int)$id, $businessId);

        if (!$item) {
            $this->flash('error', 'Article introuvable.');
            $this->redirect('/storage');
        }

        $this->render('storage/edit', ['title' => 'Modifier l\'Article', 'item' => $item], 'dashboard');
    }

    public function update(string $id): void
    {
        $businessId = $this->getBusinessId();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/storage');
        }

        $payload = [
            'item_name'   => $this->sanitize($_POST['item_name'] ?? ''),
            'quantity'    => (float)($_POST['quantity'] ?? 0),
            'unit'        => $this->sanitize($_POST['unit'] ?? 'kg'),
            'expiry_date' => $this->sanitize($_POST['expiry_date'] ?? ''),
        ];

        $success = (new StorageItem())->updateByBusiness((int)$id, $businessId, $payload);
        if ($success) {
            $this->flash('success', 'Article mis à jour avec succès.');
        } else {
            $this->flash('error', 'Erreur lors de la mise à jour.');
        }
        $this->redirect('/storage');
    }

    public function delete(string $id): void
    {
        $businessId = $this->getBusinessId();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/storage');
        }

        $success = (new StorageItem())->deleteByBusiness((int)$id, $businessId);
        if ($success) {
            $this->flash('success', 'Article supprimé du stock.');
        } else {
            $this->flash('error', 'Erreur lors de la suppression.');
        }
        $this->redirect('/storage');
    }
}
