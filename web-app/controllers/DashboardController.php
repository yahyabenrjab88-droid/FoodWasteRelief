<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Controller.php';

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $role = $_SESSION['user']['role'];
        $db = Database::getConnection();
        $uid = (int)$_SESSION['user']['id'];

        if ($role === 'admin') {
            try {
                $data = [
                    'users' => (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn() ?: 0,
                    'businesses' => (int)$db->query("SELECT COUNT(*) FROM business WHERE status LIKE 'en%attente%'")->fetchColumn() ?: 0,
                    'orders' => (int)$db->query('SELECT COUNT(*) FROM commandes')->fetchColumn() ?: 0,
                    'donations' => (int)$db->query("SELECT COUNT(*) FROM donations WHERE status NOT LIKE 'en%' AND status NOT LIKE 'annul%' AND status NOT LIKE 'deman%'")->fetchColumn() ?: 0,
                    'activities' => $db->query('SELECT a.*, u.prenom, u.nom FROM activity_logs a JOIN users u ON a.user_id = u.id ORDER BY a.date_action DESC LIMIT 10')->fetchAll() ?: []
                ];
            } catch (PDOException $e) {
                error_log('Admin Dashboard Error: ' . $e->getMessage());
                $this->flash('error', 'Error loading admin dashboard data.');
                $data = ['users'=>0, 'businesses'=>0, 'orders'=>0, 'donations'=>0, 'activities'=>[]];
            }
            $this->render('dashboard/admin', ['title' => 'Admin Dashboard', 'data' => $data], 'dashboard');

        } elseif ($role === 'business') {
            if (!isset($_SESSION['business_id'])) {
                
                $biz = (new Business())->findByUserId($uid);
                if (!$biz) {
                    $this->flash('error', 'Business profile not found.');
                    $this->redirect('/login');
                }
                $_SESSION['business_id'] = (int)$biz['id'];
            }
            $bid = (int)$_SESSION['business_id'];

            try {
                $data = [
                    'active_products' => (int)$db->query("SELECT COUNT(*) FROM produits WHERE business_id = $bid AND is_active = 1")->fetchColumn() ?: 0,
                    'expiring_soon' => (int)$db->query("SELECT COUNT(*) FROM produits WHERE business_id = $bid AND is_active = 1 AND date_expiration <= DATE_ADD(NOW(), INTERVAL 3 DAY)")->fetchColumn() ?: 0,
                    'total_orders' => (int)$db->query("SELECT COUNT(*) FROM commandes WHERE business_id = $bid")->fetchColumn() ?: 0,
                    'kg_donated' => (new Donation())->getTotalKgByBusiness($bid) ?: 0,
                    'recent_orders' => (new Commande())->findByBusiness($bid) ?: [],
                    'low_stock' => $db->query("SELECT * FROM produits WHERE business_id = $bid AND quantite < 5 AND is_active = 1")->fetchAll() ?: []
                ];
            } catch (PDOException $e) {
                error_log('Business Dashboard Error: ' . $e->getMessage());
                $this->flash('error', 'Error loading business dashboard data.');
                $data = ['active_products'=>0, 'expiring_soon'=>0, 'total_orders'=>0, 'kg_donated'=>0, 'recent_orders'=>[], 'low_stock'=>[]];
            }
            $this->render('dashboard/business', ['title' => 'Business Dashboard', 'data' => $data], 'dashboard');

        } elseif ($role === 'association') {
            if (!isset($_SESSION['association_id'])) {
                $assoc = (new Association())->findByUser($uid);
                if (!$assoc) {
                    $this->flash('error', 'Association profile not found.');
                    $this->redirect('/login');
                }
                $_SESSION['association_id'] = (int)$assoc['id'];
            }
            $aid = (int)$_SESSION['association_id'];

            try {
                $data = [
                    'pending_requests' => (int)$db->query("SELECT COUNT(*) FROM donations WHERE association_id = $aid AND (status LIKE 'deman%' OR status LIKE 'Deman%' OR status LIKE 'en%')")->fetchColumn() ?: 0,
                    'approved_donations' => (int)$db->query("SELECT COUNT(*) FROM donations WHERE association_id = $aid AND status NOT LIKE 'deman%' AND status NOT LIKE 'Deman%' AND status NOT LIKE 'en%' AND status NOT LIKE 'EN%'")->fetchColumn() ?: 0,
                    'kg_received' => (float)$db->query("SELECT SUM(quantite) FROM donations WHERE association_id = $aid AND status NOT LIKE 'deman%' AND status NOT LIKE 'Deman%' AND status NOT LIKE 'en%' AND status NOT LIKE 'EN%'")->fetchColumn() ?: 0.0,
                    'recent_donations' => (new Donation())->findByAssociation($aid) ?: []
                ];
            } catch (PDOException $e) {
                error_log('Association Dashboard Error: ' . $e->getMessage());
                $this->flash('error', 'Error loading association dashboard data.');
                $data = ['pending_requests'=>0, 'approved_donations'=>0, 'kg_received'=>0.0, 'recent_donations'=>[]];
            }
            $this->render('dashboard/association', ['title' => 'NGO Dashboard', 'data' => $data], 'dashboard');

        } elseif ($role === 'client') {
            try {
                $data = [
                    'total_orders' => (int)$db->query("SELECT COUNT(*) FROM commandes WHERE client_id = $uid")->fetchColumn() ?: 0,
                    'total_spent' => (float)$db->query("SELECT SUM(total) FROM commandes WHERE client_id = $uid AND status LIKE 'termin%'")->fetchColumn() ?: 0.0,
                    'items_saved'  => (int)$db->query("SELECT COALESCE(SUM(cd.quantite),0) FROM commandes_details cd JOIN commandes c ON cd.commande_id = c.id WHERE c.client_id = $uid AND c.status LIKE 'termin%'")->fetchColumn() ?: 0,
                    'recent_orders' => (new Commande())->findByClient($uid) ?: [],
                    'recommended' => $db->query("SELECT p.*, b.nom_business FROM produits p JOIN business b ON p.business_id = b.id WHERE p.is_active = 1 AND p.type = 'vente' ORDER BY RAND() LIMIT 4")->fetchAll() ?: []
                ];
            } catch (PDOException $e) {
                error_log('Client Dashboard Error: ' . $e->getMessage());
                $this->flash('error', 'Error loading client dashboard data.');
                $data = ['total_orders'=>0, 'total_spent'=>0.0, 'items_saved'=>0, 'recent_orders'=>[], 'recommended'=>[]];
            }
            $this->render('dashboard/client', ['title' => 'My Dashboard', 'data' => $data], 'dashboard');
        }
    }
}
