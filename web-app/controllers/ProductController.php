<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Controller.php';

class ProductController extends Controller
{
    
    public function index(): void
    {
        $page = max(1, (int)($_GET['p'] ?? 1));
        $filters = [
            'q' => $this->sanitize($_GET['q'] ?? ''),
            'ville' => $this->sanitize($_GET['ville'] ?? ''),
            'max_price' => isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null,
            'cat' => $this->sanitize($_GET['categorie'] ?? '')
        ];
        
        $role = $_SESSION['user']['role'] ?? '';
        $type = ($role === 'client' || $role === '') ? 'vente' : ($_GET['type'] ?? '');
        
        $produits = (new Produit())->search($filters['q'], $filters['cat'], $filters['ville'], (string)$type, $filters['max_price']);
        $paginated = $this->paginate(count($produits), 12, $page);
        $produitsList = array_slice($produits, $paginated['offset'], $paginated['per_page']);

        $this->render('products/catalog', [
            'title' => 'Catalog - FoodWasteRelief', 
            'produits' => $produitsList, 
            'filters' => $filters, 
            'page' => $paginated
        ], 'main');
    }

    public function show(string $id): void
    {
        $produit = (new Produit())->findById((int)$id);
        if (!$produit || !$produit['is_active']) {
            $this->flash('error', 'Product not found or no longer available.');
            $this->redirect('/products');
        }
        $this->render('products/show', ['title' => $produit['nom'], 'produit' => $produit], 'main');
    }

    private function getBusinessId(): int
    {
        $this->requireRole('business');
        $biz = (new Business())->findByUserId((int)$_SESSION['user']['id']);
        if (!$biz) {
            $this->flash('error', 'Business profile not found.');
            $this->redirect('/');
        }
        return (int)$biz['id'];
    }

    public function manage(): void
    {
        $businessId = $this->getBusinessId();
        $produits = (new Produit())->findByBusiness($businessId);
        $this->render('products/index', ['title' => 'My Products', 'produits' => $produits], 'dashboard');
    }

    public function create(): void
    {
        $businessId = $this->getBusinessId();
        $storageItems = (new StorageItem())->findByBusiness($businessId);
        $this->render('products/create', ['title' => 'Add Product', 'storageItems' => $storageItems], 'dashboard');
    }

    public function store(): void
    {
        $businessId = $this->getBusinessId();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/business/products');
        }

        $payload = [
            'business_id'     => $businessId,
            'storage_item_id' => (int)($_POST['storage_item_id'] ?? 0) ?: null,
            'nom'             => $this->sanitize($_POST['nom'] ?? ''),
            'description'     => $this->sanitize($_POST['description'] ?? ''),
            'categorie'       => $this->sanitize($_POST['categorie'] ?? ''),
            'prix'            => (float)($_POST['prix'] ?? 0),
            'quantite'        => (int)($_POST['quantite'] ?? 0),
            'date_expiration' => $this->sanitize($_POST['date_expiration'] ?? ''),
            'type'            => $this->sanitize($_POST['type'] ?? 'vente'),
            'image_url'       => $this->uploadImage('image', null),
            'is_active'       => isset($_POST['is_active']) ? 1 : 0
        ];

        (new Produit())->create($payload);
        $this->flash('success', 'Produit ajouté avec succès.');
        $this->redirect('/business/products');
    }

    public function edit(string $id): void
    {
        $businessId = $this->getBusinessId();
        $produit = (new Produit())->findById((int)$id);

        if (!$produit || (int)$produit['business_id'] !== $businessId) {
            $this->flash('error', 'Product not found.');
            $this->redirect('/business/products');
        }

        $storageItems = (new StorageItem())->findByBusiness($businessId);
        $this->render('products/edit', ['title' => 'Edit Product', 'produit' => $produit, 'storageItems' => $storageItems], 'dashboard');
    }

    public function update(string $id): void
    {
        $businessId = $this->getBusinessId();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/business/products');
        }

        $existingProduct = (new Produit())->findById((int)$id);
        $currentImage    = $existingProduct['image_url'] ?? null;

        $payload = [
            'storage_item_id' => (int)($_POST['storage_item_id'] ?? 0) ?: null,
            'nom'             => $this->sanitize($_POST['nom'] ?? ''),
            'description'     => $this->sanitize($_POST['description'] ?? ''),
            'categorie'       => $this->sanitize($_POST['categorie'] ?? ''),
            'prix'            => (float)($_POST['prix'] ?? 0),
            'quantite'        => (int)($_POST['quantite'] ?? 0),
            'date_expiration' => $this->sanitize($_POST['date_expiration'] ?? ''),
            'type'            => $this->sanitize($_POST['type'] ?? 'vente'),
            'image_url'       => $this->uploadImage('image', $currentImage),
            'is_active'       => isset($_POST['is_active']) ? 1 : 0
        ];

        $success = (new Produit())->update((int)$id, $businessId, $payload);
        if ($success) {
            $this->flash('success', 'Produit mis à jour avec succès.');
        } else {
            $this->flash('error', 'Erreur lors de la mise à jour. Veuillez vérifier les données.');
        }
        $this->redirect('/business/products');
    }

    public function delete(string $id): void
    {
        $businessId = $this->getBusinessId();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/business/products');
        }

        $success = (new Produit())->delete((int)$id, $businessId);
        if ($success) {
            $this->flash('success', 'Produit supprimé ou désactivé avec succès.');
        } else {
            $this->flash('error', 'Erreur: produit introuvable ou non autorisé.');
        }
        $this->redirect('/business/products');
    }

    public function adminIndex(): void
    {
        $this->requireRole('admin');
        
        $db = Database::getConnection();
        try {
            $produits = $db->query('SELECT p.*, b.nom_business FROM produits p JOIN business b ON p.business_id = b.id ORDER BY p.id DESC')->fetchAll();
        } catch (PDOException $e) {
            
            try {
                $produits = $db->query('SELECT p.*, b.nom_business FROM produits p LEFT JOIN business b ON p.business_id = b.id ORDER BY p.id DESC')->fetchAll();
            } catch (PDOException $e2) {
                $produits = [];
            }
        }
        
        $this->render('products/admin_index', ['title' => 'Tous les Produits', 'produits' => $produits], 'dashboard');
    }

    private function uploadImage(string $fieldName, ?string $fallback): ?string
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            return $fallback; 
        }

        $file = $_FILES[$fieldName];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log("ProductController::uploadImage error code: " . $file['error']);
            return $fallback;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mime    = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed, true)) {
            $this->flash('error', 'Format d\'image non supporté. Utilisez JPG, PNG, WebP ou GIF.');
            return $fallback;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $this->flash('error', 'L\'image ne doit pas dépasser 5 Mo.');
            return $fallback;
        }

        $ext      = match($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => 'jpg'
        };

        $uploadDir = __DIR__ . '/../public/uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid('prod_', true) . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            error_log("ProductController::uploadImage move_uploaded_file failed to $destPath");
            return $fallback;
        }

        return '/public/uploads/products/' . $filename;
    }
}
