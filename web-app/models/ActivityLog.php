<?php
declare(strict_types=1);

class ActivityLog
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function log(int $userId, string $action, string $entityType = null, int $entityId = null): bool
    {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO activity_logs (user_id, action, entity_type, entity_id, date_action) 
                VALUES (:uid, :act, :etype, :eid, NOW())
            ');
            return $stmt->execute([
                'uid' => $userId,
                'act' => $action,
                'etype' => $entityType,
                'eid' => $entityId
            ]);
        } catch (\Exception $e) {









            
            error_log('ActivityLog error: ' . $e->getMessage());
            return false;
        }
    }

    public function findAll(): array
    {
        return $this->db->query('
            SELECT a.*, u.email, u.role, u.nom, u.prenom 
            FROM activity_logs a 
            LEFT JOIN users u ON a.user_id = u.id 
            ORDER BY a.date_action DESC
        ')->fetchAll();
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM activity_logs WHERE user_id = :uid ORDER BY date_action DESC');
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }
}
