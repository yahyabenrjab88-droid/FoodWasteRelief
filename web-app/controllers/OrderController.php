<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Controller.php';

class OrderController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $role = $_SESSION['user']['role'];

        if ($role === 'client') {
            $orders = (new Commande())->findByClient((int)$_SESSION['user']['id']);
            $this->render('orders/client_index', ['title' => 'Mes Commandes', 'orders' => $orders], 'dashboard');

        } elseif ($role === 'business') {
            $bid = $this->getSessionBusinessId();
            $orders = (new Commande())->findByBusiness($bid);
            $this->render('orders/business_index', ['title' => 'Commandes Reçues', 'orders' => $orders], 'dashboard');

        } else {
            $this->redirect('/dashboard');
        }
    }

    public function show(string $id): void
    {
        $this->requireAuth();
        $orderId = (int)$id;
        $role    = $_SESSION['user']['role'];

        $order = (new Commande())->getWithDetails($orderId);

        if (!$order) {
            error_log("[OrderController::show] getWithDetails returned null for order id=$orderId");
            $this->flash('error', 'Commande introuvable.');
            $this->redirect('/orders');
        }

        if ($role === 'client') {
            if ((int)$order['client_id'] !== (int)$_SESSION['user']['id']) {
                $this->flash('error', 'Accès refusé.');
                $this->redirect('/orders');
            }

        } elseif ($role === 'business') {
            $bid = $this->getSessionBusinessId();
            if ((int)$order['business_id'] !== $bid) {
                $this->flash('error', 'Cette commande n\'appartient pas à votre entreprise.');
                $this->redirect('/orders');
            }

        } elseif ($role !== 'admin') {
            $this->flash('error', 'Accès refusé.');
            $this->redirect('/dashboard');
        }

        $this->render('orders/show', [
            'title' => 'Commande #' . str_pad((string)$order['id'], 5, '0', STR_PAD_LEFT),
            'order' => $order
        ], 'dashboard');
    }

    public function updateStatus(string $id): void
    {
        $this->requireRole('business');
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/orders/' . $id);
        }

        $status = $_POST['status'] ?? '';
        $allowed = ['terminé', 'annulé'];
        if (!in_array($status, $allowed, true)) {
            $this->flash('error', 'Statut invalide.');
            $this->redirect('/orders/' . $id);
        }

        $bid     = $this->getSessionBusinessId();
        $success = (new Commande())->updateStatus((int)$id, $status, $bid);

        if ($success) {
            $this->flash('success', 'Statut mis à jour : ' . ucfirst($status) . '.');
            try {
                (new ActivityLog())->log((int)$_SESSION['user']['id'], "order $id → $status", 'commande', (int)$id);
            } catch (\Exception $e) {}
        } else {
            $this->flash('error', 'Impossible de mettre à jour le statut. Vérifiez que la commande vous appartient.');
        }

        $this->redirect('/orders/' . $id);
    }

    public function adminIndex(): void
    {
        $this->requireRole('admin');
        $orders = (new Commande())->findAll();
        $this->render('orders/admin_index', ['title' => 'Toutes les Commandes', 'orders' => $orders], 'dashboard');
    }

    private function getSessionBusinessId(): int
    {
        if (!isset($_SESSION['business_id'])) {
            $biz = (new Business())->findByUserId((int)$_SESSION['user']['id']);
            if ($biz) {
                $_SESSION['business_id'] = (int)$biz['id'];
            } else {
                $this->flash('error', 'Profil entreprise introuvable.');
                $this->redirect('/dashboard');
            }
        }
        return (int)$_SESSION['business_id'];
    }
}
