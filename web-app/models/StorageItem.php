<?php
declare(strict_types=1);

class StorageItem
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT id, business_id, item_name, quantity, unit, expiry_date, added_at
                 FROM storage_items WHERE id = :id'
            );
            $stmt->execute(['id' => $id]);
            $item = $stmt->fetch();
            return $item ?: null;
        } catch (PDOException $e) {
            error_log('StorageItem->findById error: ' . $e->getMessage());
            return null;
        }
    }

    public function findByIdAndBusiness(int $id, int $businessId): ?array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT id, business_id, item_name, quantity, unit, expiry_date, added_at
                 FROM storage_items WHERE id = :id AND business_id = :bid'
            );
            $stmt->execute(['id' => $id, 'bid' => $businessId]);
            $item = $stmt->fetch();
            return $item ?: null;
        } catch (PDOException $e) {
            error_log('StorageItem->findByIdAndBusiness error: ' . $e->getMessage());
            return null;
        }
    }

    public function findByBusiness(int $businessId): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT id, business_id, item_name, quantity, unit, expiry_date, added_at
                 FROM storage_items WHERE business_id = :bid ORDER BY expiry_date ASC'
            );
            $stmt->execute(['bid' => $businessId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('StorageItem->findByBusiness error: ' . $e->getMessage());
            return [];
        }
    }

    public function findExpiringSoon(int $businessId, int $days = 7): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT id, business_id, item_name, quantity, unit, expiry_date, added_at
                 FROM storage_items
                 WHERE business_id = :bid
                   AND expiry_date <= DATE_ADD(NOW(), INTERVAL :days DAY)
                   AND expiry_date >= NOW()
                 ORDER BY expiry_date ASC'
            );
            $stmt->execute(['bid' => $businessId, 'days' => $days]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('StorageItem->findExpiringSoon error: ' . $e->getMessage());
            return [];
        }
    }

    public function findExpired(int $businessId): array
    {
        try {
            $stmt = $this->db->prepare(
                'SELECT id, business_id, item_name, quantity, unit, expiry_date, added_at
                 FROM storage_items WHERE business_id = :bid AND expiry_date < NOW()
                 ORDER BY expiry_date ASC'
            );
            $stmt->execute(['bid' => $businessId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('StorageItem->findExpired error: ' . $e->getMessage());
            return [];
        }
    }

    public function create(array $data): int
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO storage_items (business_id, item_name, quantity, unit, expiry_date, added_at)
                 VALUES (:business_id, :item_name, :quantity, :unit, :expiry_date, NOW())'
            );
            $stmt->execute([
                'business_id' => $data['business_id'],
                'item_name'   => $data['item_name'],
                'quantity'    => $data['quantity'],
                'unit'        => $data['unit'] ?? 'kg',
                'expiry_date' => $data['expiry_date'],
            ]);

            $id = (int)$this->db->lastInsertId();
            if ($id && isset($_SESSION['user']['id'])) {
                try {
                    (new ActivityLog())->log((int)$_SESSION['user']['id'], 'added storage item', 'storage_item', $id);
                } catch (\Exception $e) {}
            }
            return $id;
        } catch (PDOException $e) {
            error_log('StorageItem->create error: ' . $e->getMessage());
            return 0;
        }
    }

    public function updateByBusiness(int $id, int $businessId, array $data): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE storage_items
                 SET item_name = :item_name, quantity = :quantity, unit = :unit, expiry_date = :expiry_date
                 WHERE id = :id AND business_id = :bid'
            );
            $res = $stmt->execute([
                'id'          => $id,
                'bid'         => $businessId,
                'item_name'   => $data['item_name'],
                'quantity'    => $data['quantity'],
                'unit'        => $data['unit'] ?? 'kg',
                'expiry_date' => $data['expiry_date'],
            ]);

            if ($res && $stmt->rowCount() > 0 && isset($_SESSION['user']['id'])) {
                try {
                    (new ActivityLog())->log((int)$_SESSION['user']['id'], 'updated storage item', 'storage_item', $id);
                } catch (\Exception $e) {}
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log('StorageItem->updateByBusiness error: ' . $e->getMessage());
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        return $this->updateByBusiness($id, (int)($data['business_id'] ?? 0), $data);
    }

    public function deleteByBusiness(int $id, int $businessId): bool
    {
        try {
            
            $this->db->prepare('UPDATE donations SET storage_item_id = NULL WHERE storage_item_id = :id')
                     ->execute(['id' => $id]);

            $stmt = $this->db->prepare('DELETE FROM storage_items WHERE id = :id AND business_id = :bid');
            $res = $stmt->execute(['id' => $id, 'bid' => $businessId]);
            if ($res && $stmt->rowCount() > 0 && isset($_SESSION['user']['id'])) {
                try {
                    (new ActivityLog())->log((int)$_SESSION['user']['id'], 'deleted storage item', 'storage_item', $id);
                } catch (\Exception $e) {}
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log('StorageItem->deleteByBusiness error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        return $this->deleteByBusiness($id, 0);
    }

    public function countByBusiness(int $businessId): int
    {
        try {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM storage_items WHERE business_id = :bid');
            $stmt->execute(['bid' => $businessId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('StorageItem->countByBusiness error: ' . $e->getMessage());
            return 0;
        }
    }
}
