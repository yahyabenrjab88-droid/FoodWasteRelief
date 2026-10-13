<?php
declare(strict_types=1);

class Produit
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare('
                SELECT p.*, b.nom_business, b.ville 
                FROM produits p 
                JOIN business b ON p.business_id = b.id 
                WHERE p.id = :id
            ');
            $stmt->execute(['id' => $id]);
            $prod = $stmt->fetch();
            return $prod ?: null;
        } catch (PDOException $e) {
            error_log('Produit->findById error: ' . $e->getMessage());
            return null;
        }
    }

    public function findByBusiness(int $businessId): array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM produits WHERE business_id = :id ORDER BY date_expiration ASC');
            $stmt->execute(['id' => $businessId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Produit->findByBusiness error: ' . $e->getMessage());
            return [];
        }
    }

    public function findActive(int $businessId = null): array
    {
        try {
            if ($businessId) {
                $stmt = $this->db->prepare('
                    SELECT p.*, b.nom_business, b.ville 
                    FROM produits p 
                    JOIN business b ON p.business_id = b.id 
                    WHERE p.is_active = 1 AND p.quantite > 0 AND p.business_id = :bid
                    ORDER BY p.date_expiration ASC
                ');
                $stmt->execute(['bid' => $businessId]);
                return $stmt->fetchAll();
            }
            
            return $this->db->query('
                SELECT p.*, b.nom_business, b.ville 
                FROM produits p 
                JOIN business b ON p.business_id = b.id 
                WHERE p.is_active = 1 AND p.quantite > 0 
                ORDER BY p.date_expiration ASC
            ')->fetchAll();
        } catch (PDOException $e) {
            error_log('Produit->findActive error: ' . $e->getMessage());
            return [];
        }
    }

    public function findExpiringSoon(int $businessId, int $days = 3): array
    {
        try {
            $stmt = $this->db->prepare('
                SELECT p.*, b.nom_business 
                FROM produits p 
                JOIN business b ON p.business_id = b.id 
                WHERE p.is_active = 1 AND p.business_id = :bid AND p.date_expiration <= DATE_ADD(NOW(), INTERVAL :days DAY)
                ORDER BY p.date_expiration ASC
            ');
            $stmt->execute(['bid' => $businessId, 'days' => $days]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Produit->findExpiringSoon error: ' . $e->getMessage());
            return [];
        }
    }

    public function search(string $term, string $category, string $city, string $type, float $maxPrice = null, int $businessId = null): array
    {
        try {
            $sql = 'SELECT p.*, b.nom_business, b.ville FROM produits p JOIN business b ON p.business_id = b.id WHERE p.is_active = 1 AND p.quantite > 0';
            $params = [];

            if ($businessId) {
                $sql .= ' AND p.business_id = :bid';
                $params['bid'] = $businessId;
            }
            if ($term !== '') {
                $sql .= ' AND (LOWER(p.nom) LIKE LOWER(:term1) OR LOWER(p.description) LIKE LOWER(:term2))';
                $params['term1'] = "%$term%";
                $params['term2'] = "%$term%";
            }
            if ($category !== '') {
                $sql .= ' AND p.categorie LIKE :cat';
                $params['cat'] = "%$category%";
            }
            if ($city !== '') {
                $sql .= ' AND b.ville = :city';
                $params['city'] = $city;
            }
            if ($type !== '') {
                $sql .= ' AND p.type = :type';
                $params['type'] = $type;
            }
            if ($maxPrice !== null && $maxPrice > 0) {
                $sql .= ' AND p.prix <= :maxPrice';
                $params['maxPrice'] = $maxPrice;
            }

            $sql .= ' ORDER BY p.date_expiration ASC';
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Produit->search error: ' . $e->getMessage());
            return [];
        }
    }

    public function create(array $data): int
    {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO produits (business_id, storage_item_id, nom, description, categorie, prix, quantite, date_expiration, type, image_url, is_active) 
                VALUES (:business_id, :storage_item_id, :nom, :description, :categorie, :prix, :quantite, :date_expiration, :type, :image_url, :is_active)
            ');
            $stmt->execute([
                'business_id' => $data['business_id'],
                'storage_item_id' => $data['storage_item_id'] ?? null,
                'nom' => $data['nom'],
                'description' => $data['description'] ?? '',
                'categorie' => $data['categorie'] ?? '',
                'prix' => $data['prix'] ?? 0.0,
                'quantite' => $data['quantite'] ?? 0,
                'date_expiration' => $data['date_expiration'],
                'type' => $data['type'] ?? 'vente',
                'image_url' => $data['image_url'] ?? null,
                'is_active' => $data['is_active'] ?? 1
            ]);
            
            $id = (int)$this->db->lastInsertId();
            if (isset($_SESSION['user']['id'])) {
                try {
                    (new ActivityLog())->log((int)$_SESSION['user']['id'], 'added product', 'produit', $id);
                } catch (\Exception $e) {}
            }
            return $id;
        } catch (PDOException $e) {
            error_log('Produit->create error: ' . $e->getMessage());
            return 0;
        }
    }

    public function update(int $id, int $businessId, array $data): bool
    {
        try {
            $stmt = $this->db->prepare('
                UPDATE produits 
                SET storage_item_id = :storage_item_id, nom = :nom, description = :description, categorie = :categorie, 
                    prix = :prix, quantite = :quantite, date_expiration = :date_expiration, type = :type, 
                    image_url = :image_url, is_active = :is_active 
                WHERE id = :id AND business_id = :businessId
            ');
            $res = $stmt->execute([
                'id' => $id,
                'businessId' => $businessId,
                'storage_item_id' => $data['storage_item_id'] ?? null,
                'nom' => $data['nom'],
                'description' => $data['description'] ?? '',
                'categorie' => $data['categorie'] ?? '',
                'prix' => $data['prix'] ?? 0.0,
                'quantite' => $data['quantite'] ?? 0,
                'date_expiration' => $data['date_expiration'],
                'type' => $data['type'] ?? 'vente',
                'image_url' => $data['image_url'] ?? null,
                'is_active' => $data['is_active'] ?? 1
            ]);

            if ($res && isset($_SESSION['user']['id'])) {
                try {
                    (new ActivityLog())->log((int)$_SESSION['user']['id'], 'updated product', 'produit', $id);
                } catch (\Exception $e) {}
            }
            return $res;
        } catch (PDOException $e) {
            error_log('Produit->update error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id, int $businessId): bool
    {
        try {
            
            $stmt = $this->db->prepare('DELETE FROM produits WHERE id = :id AND business_id = :businessId');
            $res = $stmt->execute(['id' => $id, 'businessId' => $businessId]);
            if ($res && $stmt->rowCount() > 0 && isset($_SESSION['user']['id'])) {
                try { (new ActivityLog())->log((int)$_SESSION['user']['id'], 'deleted product', 'produit', $id); } catch (\Exception $e) {}
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log('Produit->delete error (trying soft delete): ' . $e->getMessage());
            try {
                
                $this->db->prepare('DELETE FROM cart_items WHERE product_id = :id')->execute(['id' => $id]);
                
                $stmt2 = $this->db->prepare('UPDATE produits SET is_active = 0 WHERE id = :id AND business_id = :businessId');
                $res2 = $stmt2->execute(['id' => $id, 'businessId' => $businessId]);
                if ($res2 && $stmt2->rowCount() > 0) {
                    try { (new ActivityLog())->log((int)$_SESSION['user']['id'], 'deactivated product', 'produit', $id); } catch (\Exception $e) {}
                    return true;
                }
                return false;
            } catch (PDOException $e2) {
                error_log('Produit->delete soft fallback error: ' . $e2->getMessage());
                return false;
            }
        }
    }

    public function findAllActive(): array
    {
        return $this->findActive();
    }

    public function countByBusiness(int $businessId): int
    {
        try {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM produits WHERE business_id = :bid AND is_active = 1');
            $stmt->execute(['bid' => $businessId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Produit->countByBusiness error: ' . $e->getMessage());
            return 0;
        }
    }
}
