<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Model.php';

class PlatformStats extends Model
{
    public function adminKpis(): array
    {
        $db = $this->getDB();
        $usersByRole = $db->query("SELECT role, COUNT(*) c FROM users GROUP BY role")->fetchAll();
        $roles = ['admin' => 0, 'business' => 0, 'association' => 0, 'client' => 0];
        foreach ($usersByRole as $r) {
            $roles[$r['role']] = (int)$r['c'];
        }

        $ordersTotal = (int)($db->query('SELECT COUNT(*) c FROM commandes')->fetch()['c'] ?? 0);
        $ordersDelivered = (int)($db->query("SELECT COUNT(*) c FROM commandes WHERE status LIKE 'termin%'")->fetch()['c'] ?? 0);

        $kgVendus = (float)($db->query("SELECT COALESCE(SUM(cd.quantite),0) kg FROM commandes_details cd INNER JOIN commandes c ON c.id=cd.commande_id WHERE c.status LIKE 'termin%'")->fetch()['kg'] ?? 0);
        $kgDons = (float)($db->query("SELECT COALESCE(SUM(quantite),0) kg FROM donations WHERE status LIKE 'termin%'")->fetch()['kg'] ?? 0);
        $kgTotal = $kgVendus + $kgDons;

        $businessTotal = (int)($db->query('SELECT COUNT(*) c FROM business')->fetch()['c'] ?? 0);
        $businessActive = (int)($db->query("SELECT COUNT(*) c FROM business WHERE status IN ('valide','valid','active')")->fetch()['c'] ?? 0);

        return [
            'roles' => $roles,
            'orders_total' => $ordersTotal,
            'orders_delivered' => $ordersDelivered,
            'kg_total' => $kgTotal,
            'meals' => $kgTotal * 2,
            'co2' => $kgTotal * 2.5,
            'business_active' => $businessActive,
            'business_total' => $businessTotal,
        ];
    }

    public function monthlyEvolution(): array
    {
        $db = $this->getDB();
        $labels = [];
        $sold = [];
        $donated = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = (new DateTime('first day of this month'))->modify("-{$i} months");
            $labels[] = $monthDate->format('M');
            $ym = $monthDate->format('Y-m');

            $st1 = $db->prepare("SELECT COALESCE(SUM(cd.quantite),0) kg
                                FROM commandes_details cd
                                INNER JOIN commandes c ON c.id=cd.commande_id
                                WHERE DATE_FORMAT(c.date_commande,'%Y-%m') = ?
                                  AND c.status LIKE 'termin%'");
            $st1->execute([$ym]);
            $sold[] = (float)($st1->fetch()['kg'] ?? 0);

            $st2 = $db->prepare("SELECT COALESCE(SUM(quantite),0) kg
                                FROM donations
                                WHERE DATE_FORMAT(date_donation,'%Y-%m') = ?
                                  AND status LIKE 'termin%'");
            $st2->execute([$ym]);
            $donated[] = (float)($st2->fetch()['kg'] ?? 0);
        }

        return ['labels' => $labels, 'sold' => $sold, 'donated' => $donated];
    }

    public function orderStatusDistribution(): array
    {
        $db = $this->getDB();
        return [
            'pending' => (int)($db->query("SELECT COUNT(*) c FROM commandes WHERE status LIKE 'en atten%'")->fetch()['c'] ?? 0),
            'confirmed' => (int)($db->query("SELECT COUNT(*) c FROM commandes WHERE status LIKE 'confir%'")->fetch()['c'] ?? 0),
            'delivered' => (int)($db->query("SELECT COUNT(*) c FROM commandes WHERE status LIKE 'termin%'")->fetch()['c'] ?? 0),
            'cancelled' => (int)($db->query("SELECT COUNT(*) c FROM commandes WHERE status LIKE 'annul%'")->fetch()['c'] ?? 0),
        ];
    }
}
