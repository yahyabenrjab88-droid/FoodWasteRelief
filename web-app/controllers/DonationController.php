<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Controller.php';

class DonationController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $role = $_SESSION['user']['role'];
        $db = Database::getConnection();

        if ($role === 'business') {
            if (!isset($_SESSION['business_id'])) {
                $this->flash('error', 'Profil entreprise introuvable.');
                $this->redirect('/login');
            }
            $businessId = (int)$_SESSION['business_id'];

            $stmtProd = $db->prepare("SELECT id, nom, quantite FROM produits WHERE business_id = :bid AND is_active = 1");
            $stmtProd->execute(['bid' => $businessId]);
            $products = $stmtProd->fetchAll();

            $stmtStor = $db->prepare("SELECT id, item_name, quantity, unit FROM storage_items WHERE business_id = :bid");
            $stmtStor->execute(['bid' => $businessId]);
            $storage = $stmtStor->fetchAll();

            $stmtAssoc = $db->prepare("SELECT id, nom, ville FROM associations WHERE status = 'valide' ORDER BY nom ASC");
            $stmtAssoc->execute();
            $associations = $stmtAssoc->fetchAll();

            $stmtReq = $db->prepare("SELECT d.*, p.nom as produit_nom, a.nom as assoc_nom FROM donations d JOIN associations a ON a.id = d.association_id JOIN produits p ON p.id = d.produit_id WHERE d.status = 'demandé' AND p.business_id = :bid");
            $stmtReq->execute(['bid' => $businessId]);
            $donations = $stmtReq->fetchAll();
            
            $this->render('donations/index', [
                'title' => 'Dons', 
                'donations' => $donations,
                'associations' => $associations,
                'products' => $products,
                'storage' => $storage
            ], 'dashboard');
            
        } elseif ($role === 'association') {
            if (!isset($_SESSION['association_id'])) {
                $this->flash('error', 'Profil association introuvable.');
                $this->redirect('/login');
            }
            $associationId = (int)$_SESSION['association_id'];

            $stmtDon = $db->prepare("SELECT d.*, p.nom as produit_nom, b.nom_business FROM donations d JOIN produits p ON p.id = d.produit_id JOIN business b ON b.id = p.business_id WHERE d.association_id = :aid");
            $stmtDon->execute(['aid' => $associationId]);
            $donations = $stmtDon->fetchAll();

        $stmtBiz = $db->prepare("SELECT p.*, b.nom_business FROM produits p JOIN business b ON p.business_id = b.id WHERE p.type IN ('don', 'donation') AND p.is_active = 1 AND p.quantite > 0 AND b.status = 'valide'");
        $stmtBiz->execute();
        $availableProducts = $stmtBiz->fetchAll();
        
        $this->render('donations/index', [
            'title' => 'Mes Dons', 
            'donations' => $donations,
            'availableProducts' => $availableProducts
        ], 'dashboard');
    } else {
        $this->redirect('/dashboard');
    }
}

public function request(): void
{
    $this->requireRole('association');
    if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
        $this->redirect('/donations');
    }

    $assocId = (int)($_SESSION['association_id'] ?? 0);
    $produitId = (int)($_POST['produit_id'] ?? 0);
    $quantite = (float)($_POST['quantite'] ?? 0);

    $produit = (new Produit())->findById($produitId);
    if (!$produit || !in_array($produit['type'], ['don', 'donation']) || $produit['quantite'] < $quantite) {
            $this->flash('error', 'Produit non disponible pour un don ou quantité insuffisante.');
            $this->redirect('/donations');
        }

        (new Donation())->create([
            'produit_id' => $produitId,
            'association_id' => $assocId,
            'quantite' => $quantite,
            'status' => 'demandé'
        ]);

        $this->flash('success', 'Demande de don envoyée à l\'entreprise.');
        $this->redirect('/donations');
    }

    public function send(): void
    {
        $this->requireRole('business');
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->redirect('/donations');
        }

        $bizId = (int)($_SESSION['business_id'] ?? 0);
        $assocId = (int)($_POST['association_id'] ?? 0);
        $produitId = (int)($_POST['produit_id'] ?? 0);
        $quantite = (float)($_POST['quantite'] ?? 0);

        $produit = (new Produit())->findById($produitId);
        if (!$produit || (int)$produit['business_id'] !== $bizId || $produit['quantite'] < $quantite) {
            $this->flash('error', 'Produit invalide ou quantité insuffisante.');
            $this->redirect('/donations');
        }

        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            (new Donation())->create([
                'produit_id' => $produitId,
                'association_id' => $assocId,
                'quantite' => $quantite,
                'status' => 'terminé'
            ]);

            $stmt = $db->prepare('UPDATE produits SET quantite = quantite - :qty WHERE id = :id');
            $stmt->execute(['qty' => $quantite, 'id' => $produitId]);

            $db->commit();
            $this->flash('success', 'Don envoyé avec succès et stock déduit.');
            $this->redirect('/donations');
        } catch (Exception $e) {
            $db->rollBack();
            $this->flash('error', 'Une erreur est survenue.');
            $this->redirect('/donations');
        }
    }

    public function approve(string $id): void
    {
        $this->requireRole('business');
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->redirect('/donations');
        }

        $db = Database::getConnection();
        $donation = $db->query("SELECT * FROM donations WHERE id = " . (int)$id)->fetch();
        if (!$donation) {
            $this->flash('error', 'Don introuvable.');
            $this->redirect('/donations');
        }

        try {
            $db->beginTransaction();
            (new Donation())->updateStatus((int)$id, 'terminé');
            
            $stmt = $db->prepare('UPDATE produits SET quantite = quantite - :qty WHERE id = :id');
            $stmt->execute(['qty' => $donation['quantite'], 'id' => $donation['produit_id']]);
            
            $db->commit();
            $this->flash('success', 'Demande de don approuvée et stock déduit.');
        } catch (Exception $e) {
            $db->rollBack();
            $this->flash('error', 'Erreur lors de l\'approbation du don.');
        }
        $this->redirect('/donations');
    }

    public function reject(string $id): void
    {
        $this->requireRole('business');
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->redirect('/donations');
        }

        (new Donation())->updateStatus((int)$id, 'rejeté');
        $this->flash('success', 'Demande de don rejetée.');
        $this->redirect('/donations');
    }

    public function adminIndex(): void
    {
        $this->requireRole('admin');
        $donations = (new Donation())->findAll();
        $this->render('donations/admin_index', ['title' => 'Tous les Dons', 'donations' => $donations], 'dashboard');
    }
}
