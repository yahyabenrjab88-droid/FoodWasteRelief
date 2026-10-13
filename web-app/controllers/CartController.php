<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Controller.php';

class CartController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function index(): void
    {
        $this->requireRole('client');
        $cartItems = [];
        $total = 0;

        foreach ($_SESSION['cart'] as $id => $qty) {
            $p = (new Produit())->findById((int)$id);
            if ($p) {
                $subtotal = $p['prix'] * $qty;
                $total += $subtotal;
                $p['cart_qty'] = $qty;
                $p['subtotal'] = $subtotal;
                $cartItems[] = $p;
            } else {
                unset($_SESSION['cart'][$id]); 
            }
        }

        $this->render('cart/index', ['title' => 'My Cart', 'cartItems' => $cartItems, 'total' => $total], 'main');
    }

    public function add(): void
    {
        $this->requireRole('client');
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/products');
        }

        $id = (int)$_POST['produit_id'];
        $qty = (int)($_POST['quantite'] ?? 1);

        $produit = (new Produit())->findById($id);
        if (!$produit || !$produit['is_active']) {
            $this->flash('error', 'Product not available.');
            $this->redirect('/products');
        }

        if ($qty > $produit['quantite']) {
            $this->flash('error', 'Not enough stock available.');
            $this->redirect('/products/' . $id);
        }

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] += $qty;
        } else {
            $_SESSION['cart'][$id] = $qty;
        }

        $this->flash('success', 'Added to cart successfully.');
        $this->redirect('/cart');
    }

    public function update(): void
    {
        $this->requireRole('client');
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->redirect('/cart');
        }

        $id = (int)$_POST['produit_id'];
        $qty = (int)$_POST['quantite'];

        if ($qty <= 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            $produit = (new Produit())->findById($id);
            if ($produit && $qty <= $produit['quantite']) {
                $_SESSION['cart'][$id] = $qty;
            } else {
                $this->flash('error', 'Invalid quantity.');
            }
        }
        $this->redirect('/cart');
    }

    public function remove(): void
    {
        $this->requireRole('client');
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->redirect('/cart');
        }

        $id = (int)$_POST['produit_id'];
        unset($_SESSION['cart'][$id]);
        $this->redirect('/cart');
    }

    public function checkout(): void
    {
        $this->requireRole('client');
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->redirect('/cart');
        }

        if (empty($_SESSION['cart'])) {
            $this->flash('error', 'Your cart is empty.');
            $this->redirect('/cart');
        }

        $db = Database::getConnection();

        $ordersByBusiness = [];
        foreach ($_SESSION['cart'] as $id => $qty) {
            $p = (new Produit())->findById((int)$id);
            if (!$p || $p['quantite'] < $qty || !$p['is_active']) {
                $this->flash('error', "Product {$p['nom']} is no longer available in the requested quantity.");
                $this->redirect('/cart');
            }
            $bId = $p['business_id'];
            if (!isset($ordersByBusiness[$bId])) {
                $ordersByBusiness[$bId] = ['total' => 0, 'items' => []];
            }
            $ordersByBusiness[$bId]['total'] += $p['prix'] * $qty;
            $ordersByBusiness[$bId]['items'][] = [
                'produit_id' => $id,
                'quantite' => $qty,
                'prix_unitaire' => $p['prix']
            ];
        }

        try {
            $commandeModel = new Commande();
            
            foreach ($ordersByBusiness as $bId => $data) {
                
                $orderId = $commandeModel->create(
                    (int)$_SESSION['user']['id'],
                    (int)$bId,
                    (float)$data['total'],
                    $data['items']
                );

                if ($orderId > 0) {
                    
                    foreach ($data['items'] as $item) {
                        $stmt = $db->prepare('UPDATE produits SET quantite = quantite - :qty WHERE id = :id');
                        $stmt->execute(['qty' => $item['quantite'], 'id' => $item['produit_id']]);
                    }
                }
            }

            $_SESSION['cart'] = [];
            $this->flash('success', 'Order placed successfully!');
            $this->redirect('/orders');
        } catch (Exception $e) {
            error_log('Cart checkout error: ' . $e->getMessage());
            $this->flash('error', 'An error occurred during checkout.');
            $this->redirect('/cart');
        }
    }
}
