<?php
declare(strict_types=1);

class Association
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT a.*, u.email FROM associations a JOIN users u ON a.user_id = u.id WHERE a.id = :id');
        $stmt->execute(['id' => $id]);
        $assoc = $stmt->fetch();
        return $assoc ?: null;
    }

    public function findByUser(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM associations WHERE user_id = :uid');
        $stmt->execute(['uid' => $userId]);
        $assoc = $stmt->fetch();
        return $assoc ?: null;
    }

    public function findByUserId(int $userId): ?array
    {
        return $this->findByUser($userId);
    }

    public function findAll(): array
    {
        return $this->db->query('
            SELECT a.*, u.email, u.nom as user_nom, u.prenom as user_prenom 
            FROM associations a 
            JOIN users u ON a.user_id = u.id 
            ORDER BY a.id DESC
        ')->fetchAll();
    }

    public function findApproved(): array
    {
        return $this->db->query('SELECT * FROM associations WHERE status = "valide" ORDER BY nom ASC')->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO associations (user_id, nom, ville, telephone, status)
             VALUES (:uid, :nom, :ville, :tel, :status)'
        );
        $stmt->execute([
            'uid'    => $data['user_id'],
            'nom'    => $data['nom'],
            'ville'  => $data['ville'] ?? null,
            'tel'    => $data['telephone'] ?? null,
            'status' => $data['status'] ?? 'en_attente',
        ]);
        
        $id = (int)$this->db->lastInsertId();
        if (isset($_SESSION['user']['id'])) {
            (new ActivityLog())->log((int)$_SESSION['user']['id'], 'registered association', 'association', $id);
        }
        return $id;
    }

    public function approve(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE associations SET status = "valide" WHERE id = :id');
        $res = $stmt->execute(['id' => $id]);

        $assoc = $this->findById($id);
        if ($assoc) {
            $stmtUser = $this->db->prepare('UPDATE users SET status = "actif" WHERE id = :uid');
            $stmtUser->execute(['uid' => $assoc['user_id']]);
        }
        
        if ($res && isset($_SESSION['user']['id'])) {
            (new ActivityLog())->log((int)$_SESSION['user']['id'], 'approved association', 'association', $id);
        }
        return $res;
    }

    public function reject(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE associations SET status = 'rejeté' WHERE id = :id");
        $res = $stmt->execute(['id' => $id]);
        if ($res && isset($_SESSION['user']['id'])) {
            (new ActivityLog())->log((int)$_SESSION['user']['id'], 'rejected association', 'association', $id);
        }
        return $res;
    }
}
