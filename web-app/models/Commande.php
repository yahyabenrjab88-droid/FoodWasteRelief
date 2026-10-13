<?php
declare(strict_types=1);

class Commande
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByClient(int $clientId): array
    {
        try {
            $stmt = $this->db->prepare('
                SELECT c.*, b.nom_business 
                FROM commandes c 
                JOIN business b ON c.business_id = b.id 
                WHERE c.client_id = :client_id 
                ORDER BY c.date_commande DESC
            ');
            $stmt->execute(['client_id' => $clientId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Commande->findByClient error: ' . $e->getMessage());
            return [];
        }
    }

    public function findByBusiness(int $businessId): array
    {
        try {
            $stmt = $this->db->prepare('
                SELECT c.*, u.nom AS client_nom, u.prenom AS client_prenom 
                FROM commandes c 
                JOIN users u ON c.client_id = u.id 
                WHERE c.business_id = :business_id 
                ORDER BY c.date_commande DESC
            ');
            $stmt->execute(['business_id' => $businessId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Commande->findByBusiness error: ' . $e->getMessage());
            return [];
        }
    }

    public function findRecentByBusiness(int $businessId, int $limit = 5): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, u.nom AS client_nom, u.prenom AS client_prenom 
                FROM commandes c 
                JOIN users u ON c.client_id = u.id 
                WHERE c.business_id = :business_id 
                ORDER BY c.date_commande DESC 
                LIMIT " . (int)$limit
            );
            $stmt->execute(['business_id' => $businessId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Commande->findRecentByBusiness error: ' . $e->getMessage());
            return [];
        }
    }

    public function countByBusiness(int $businessId): int
    {
        try {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM commandes WHERE business_id = :business_id');
            $stmt->execute(['business_id' => $businessId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Commande->countByBusiness error: ' . $e->getMessage());
            return 0;
        }
    }

    public function findAll(): array
    {
        try {
            return $this->db->query('
                SELECT c.*, b.nom_business, u.nom, u.prenom 
                FROM commandes c 
                JOIN business b ON c.business_id = b.id 
                JOIN users u ON c.client_id = u.id 
                ORDER BY c.date_commande DESC
            ')->fetchAll();
        } catch (PDOException $e) {
            error_log('Commande->findAll error: ' . $e->getMessage());
            return [];
        }
    }

    public function create(int $clientId, int $businessId, float $total, array $items): int
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare('
                INSERT INTO commandes (client_id, business_id, total, status, date_commande) 
                VALUES (:client_id, :business_id, :total, :status, NOW())
            ');
            $stmt->execute([
                'client_id' => $clientId,
                'business_id' => $businessId,
                'total' => $total,
                'status' => 'en attente'
            ]);
            
            $commandeId = (int)$this->db->lastInsertId();

            if (!empty($items)) {
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
                $stmtDetails = $this->db->prepare($sql);
                $stmtDetails->execute($params);
            }

            $this->db->commit();

            if (isset($_SESSION['user']['id'])) {
                try {
                    (new ActivityLog())->log((int)$_SESSION['user']['id'], 'placed order', 'commande', $commandeId);
                } catch (\Exception $e) {}
            }

            return $commandeId;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Commande->create error: ' . $e->getMessage());
            return 0;
        }
    }

    public function updateStatus(int $id, string $status, int $businessId = 0): bool
    {
        try {
            if ($businessId > 0) {
                $stmt = $this->db->prepare('UPDATE commandes SET status = :status WHERE id = :id AND business_id = :bid');
                $res = $stmt->execute(['status' => $status, 'id' => $id, 'bid' => $businessId]);
            } else {
                $stmt = $this->db->prepare('UPDATE commandes SET status = :status WHERE id = :id');
                $res = $stmt->execute(['status' => $status, 'id' => $id]);
            }
            
            return $res && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Commande->updateStatus error: ' . $e->getMessage());
            return false;
        }
    }

    public function getWithDetails(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare('
                SELECT c.*,
                       COALESCE(b.nom_business, "Entreprise inconnue") AS nom_business,
                       COALESCE(u.nom, "") AS nom,
                       COALESCE(u.prenom, "") AS prenom,
                       COALESCE(u.email, "") AS email,
                       COALESCE(u.telephone, "") AS telephone
                FROM commandes c
                LEFT JOIN business b ON c.business_id = b.id
                LEFT JOIN users u ON c.client_id = u.id
                WHERE c.id = :id
            ');
            $stmt->execute(['id' => $id]);
            $order = $stmt->fetch();

            if (!$order) return null;

            $stmtDetails = $this->db->prepare('
                SELECT cd.*,
                       COALESCE(p.nom, "Produit supprimé") AS nom_produit,
                       COALESCE(p.image_url, "") AS image_url,
                       COALESCE(p.categorie, "") AS categorie
                FROM commandes_details cd
                LEFT JOIN produits p ON cd.produit_id = p.id
                WHERE cd.commande_id = :cid
            ');
            $stmtDetails->execute(['cid' => $id]);
            $order['details'] = $stmtDetails->fetchAll();

            $order['client_nom']    = $order['nom']    ?? '';
            $order['client_prenom'] = $order['prenom'] ?? '';

            return $order;
        } catch (PDOException $e) {
            error_log('Commande->getWithDetails error: ' . $e->getMessage());
            return null;
        }
    }
}
