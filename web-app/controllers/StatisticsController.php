<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Controller.php';

class StatisticsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->render('statistics/index', ['title' => 'Statistiques'], 'dashboard');
    }

    public function data(): void
    {
        $this->requireAuth();
        $db = Database::getConnection();
        $role = $_SESSION['user']['role'];
        $uid = (int)$_SESSION['user']['id'];

        $res = [];

        if ($role === 'admin') {
            $usersByRole = $db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role")->fetchAll();
            $ordersByDay = $db->query("SELECT DATE(date_commande) as date, COUNT(*) as count FROM commandes GROUP BY DATE(date_commande) ORDER BY date DESC LIMIT 7")->fetchAll();
            
            $res = [
                'usersByRole' => $usersByRole,
                'ordersByDay' => array_reverse($ordersByDay)
            ];
        } elseif ($role === 'business') {
            $bid = (int)($_SESSION['business_id'] ?? 0);
            
            $ordersByDay = $db->query("SELECT DATE(date_commande) as date, COUNT(*) as count FROM commandes WHERE business_id = $bid GROUP BY DATE(date_commande) ORDER BY date DESC LIMIT 7")->fetchAll();
            $donationsByDay = $db->query("SELECT DATE(d.date_donation) as date, SUM(d.quantite) as qty FROM donations d JOIN produits p ON d.produit_id = p.id WHERE p.business_id = $bid AND (d.status LIKE 'termin%' OR d.status LIKE 'Termin%' OR d.status LIKE 'TERMIN%') GROUP BY DATE(d.date_donation) ORDER BY date DESC LIMIT 7")->fetchAll();
            
            $res = [
                'ordersByDay' => array_reverse($ordersByDay),
                'donationsByDay' => array_reverse($donationsByDay)
            ];
        } elseif ($role === 'association') {
            if (!isset($_SESSION['association_id'])) {
                $assoc = (new Association())->findByUser($uid);
                if ($assoc) $_SESSION['association_id'] = (int)$assoc['id'];
            }
            $aid = (int)($_SESSION['association_id'] ?? 0);
            
            try {
                $donationsPerMonth = $db->query("SELECT MONTH(date_donation) as mois, YEAR(date_donation) as annee, COUNT(*) as total FROM donations WHERE association_id = $aid AND status NOT LIKE 'deman%' AND status NOT LIKE 'en%' GROUP BY annee, mois ORDER BY annee, mois LIMIT 12")->fetchAll();
                $totalKgReceived = (float)$db->query("SELECT SUM(quantite) FROM donations WHERE association_id = $aid AND status NOT LIKE 'deman%' AND status NOT LIKE 'en%'")->fetchColumn();
                $donationsByCategory = $db->query("SELECT p.categorie, COUNT(*) as total FROM donations d JOIN produits p ON p.id = d.produit_id WHERE d.association_id = $aid AND d.status NOT LIKE 'deman%' AND d.status NOT LIKE 'en%' GROUP BY p.categorie")->fetchAll();
                $topDonors = $db->query("SELECT b.nom_business, COUNT(*) as total_dons, SUM(d.quantite) as kg FROM donations d JOIN produits p ON p.id = d.produit_id JOIN business b ON b.id = p.business_id WHERE d.association_id = $aid AND d.status NOT LIKE 'deman%' AND d.status NOT LIKE 'en%' GROUP BY b.id ORDER BY total_dons DESC LIMIT 5")->fetchAll();
                
                $res = [
                    'donationsPerMonth' => $donationsPerMonth,
                    'totalKgReceived' => $totalKgReceived,
                    'donationsByCategory' => $donationsByCategory,
                    'topDonors' => $topDonors
                ];
            } catch (PDOException $e) {
                $res = [
                    'donationsPerMonth' => [],
                    'totalKgReceived' => 0,
                    'donationsByCategory' => [],
                    'topDonors' => []
                ];
            }
        } else {
            
            $spentByDay = $db->query("SELECT DATE(date_commande) as date, SUM(total) as amount FROM commandes WHERE client_id = $uid AND (status LIKE 'termin%' OR status LIKE 'Termin%' OR status LIKE 'TERMIN%') AND date_commande >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(date_commande) ORDER BY date ASC")->fetchAll();
            
            // Fill missing days with 0
            $fullData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $found = false;
                foreach ($spentByDay as $row) {
                    if ($row['date'] === $date) {
                        $fullData[] = ['date' => $date, 'amount' => (float)$row['amount']];
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $fullData[] = ['date' => $date, 'amount' => 0.0];
                }
            }

            $res = [
                'spentByDay' => $fullData
            ];
        }

        $this->jsonResponse($res);
    }
}
