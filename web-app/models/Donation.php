<?php
declare(strict_types=1);

class Donation
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByBusiness(int $businessId): array
    {
        $stmt = $this->db->prepare('
            SELECT d.*, p.nom as produit_nom, a.nom as association_nom 
            FROM donations d 
            JOIN produits p ON d.produit_id = p.id 
            JOIN associations a ON d.association_id = a.id 
            WHERE p.business_id = :bid 
            ORDER BY d.date_donation DESC
        ');
        $stmt->execute(['bid' => $businessId]);
        return $stmt->fetchAll();
    }

    public function findByAssociation(int $associationId): array
    {
        $stmt = $this->db->prepare('
            SELECT d.*, p.nom as produit_nom, b.nom_business 
            FROM donations d 
            JOIN produits p ON d.produit_id = p.id 
            JOIN business b ON p.business_id = b.id 
            WHERE d.association_id = :aid 
            ORDER BY d.date_donation DESC
        ');
        $stmt->execute(['aid' => $associationId]);
        return $stmt->fetchAll();
    }

    public function findAll(): array
    {
        return $this->db->query('
            SELECT d.*, p.nom as produit_nom, a.nom as association_nom, b.nom_business 
            FROM donations d 
            JOIN produits p ON d.produit_id = p.id 
            JOIN associations a ON d.association_id = a.id 
            JOIN business b ON p.business_id = b.id 
            ORDER BY d.date_donation DESC
        ')->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO donations (produit_id, storage_item_id, association_id, quantite, date_donation, status) 
            VALUES (:pid, :sid, :aid, :qty, NOW(), :status)
        ');
        $stmt->execute([
            'pid' => $data['produit_id'],
            'sid' => $data['storage_item_id'] ?? null,
            'aid' => $data['association_id'],
            'qty' => $data['quantite'],
            'status' => $data['status'] ?? 'en attente'
        ]);
        
        $id = (int)$this->db->lastInsertId();
        if (isset($_SESSION['user']['id'])) {
            (new ActivityLog())->log((int)$_SESSION['user']['id'], 'initiated donation', 'donation', $id);
        }
        return $id;
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE donations SET status = :status WHERE id = :id');
        $res = $stmt->execute(['status' => $status, 'id' => $id]);
        if ($res && isset($_SESSION['user']['id'])) {
            (new ActivityLog())->log((int)$_SESSION['user']['id'], "updated donation status to $status", 'donation', $id);
        }
        return $res;
    }

    public function getTotalKgByBusiness(int $businessId): float
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(d.quantite), 0)
             FROM donations d
             JOIN produits p ON d.produit_id = p.id
             WHERE p.business_id = :bid AND d.status = :status'
        );
        $stmt->execute(['bid' => $businessId, 'status' => 'terminé']);
        return (float)$stmt->fetchColumn();
    }
}
