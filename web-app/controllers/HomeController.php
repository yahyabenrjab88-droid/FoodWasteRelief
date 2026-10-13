<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/Controller.php';

class HomeController extends Controller
{
    public function index(): void
    {
        $db = Database::getConnection();

        $totalKg = (float)($db->query("SELECT COALESCE(SUM(quantite), 0) FROM donations WHERE status LIKE 'termin%'")->fetchColumn() ?: 0) 
                  + (float)($db->query("SELECT COALESCE(SUM(cd.quantite), 0) FROM commandes_details cd JOIN commandes c ON cd.commande_id = c.id WHERE c.status LIKE 'termin%'")->fetchColumn() ?: 0);

        $stats = [
            'kg_saved' => $totalKg * 2.5, // 2.5 kg of CO2 avoided per kg of food saved
            'businesses' => (int)$db->query('SELECT COUNT(*) FROM business WHERE status = "valide" OR status = "approuvé"')->fetchColumn() ?: 0,
            'associations' => (int)$db->query('SELECT COUNT(*) FROM associations')->fetchColumn() ?: 0,
            'orders' => (int)$db->query("SELECT COUNT(*) FROM commandes WHERE status LIKE 'termin%'")->fetchColumn() 
                  + (int)$db->query("SELECT COUNT(*) FROM donations WHERE status LIKE 'termin%'")->fetchColumn()
        ];

        $this->render('home/index', ['title' => 'FoodWasteRelief - Save Food, Save the Planet', 'stats' => $stats]);
    }

    public function about(): void
    {
        $this->render('home/about', ['title' => 'About Us - FoodWasteRelief']);
    }
}
