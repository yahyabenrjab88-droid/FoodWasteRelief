<?php
declare(strict_types=1);

class CommandeDetail
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByCommande(int $commandeId): array
    {
        try {
            $stmt = $this->db->prepare('
                SELECT cd.*, p.nom as produit_nom 
                FROM commandes_details cd 
                JOIN produits p ON cd.produit_id = p.id 
                WHERE cd.commande_id = :cid
            ');
            $stmt->execute(['cid' => $commandeId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('CommandeDetail->findByCommande error: ' . $e->getMessage());
            return [];
        }
    }

    public function createBulk(int $commandeId, array $items): bool
    {
        if (empty($items)) return false;

        try {
            $sql = 'INSERT INTO commandes_details (commande_id, produit_id, quantite, prix_unitaire) VALUES ';
            $values = [];
            $params = [];
            
            $i = 0;
            foreach ($items as $item) {
                $values[] = "(:c{$i}, :p{$i}, :q{$i}, :u{$i})";
                $params["c{$i}"] = $commandeId;
                $params["p{$i}"] = $item['produit_id'];
                $params["q{$i}"] = $item['quantite'];
                $params["u{$i}"] = $item['prix_unitaire'];
                $i++;
            }
            
            $sql .= implode(', ', $values);
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('CommandeDetail->createBulk error: ' . $e->getMessage());
            return false;
        }
    }
}
