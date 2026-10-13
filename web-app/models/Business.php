<?php
declare(strict_types=1);

class Business
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT b.*, u.email FROM business b JOIN users u ON b.user_id = u.id WHERE b.id = :id');
        $stmt->execute(['id' => $id]);
        $biz = $stmt->fetch();
        return $biz ?: null;
    }

    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM business WHERE user_id = :uid');
        $stmt->execute(['uid' => $userId]);
        $biz = $stmt->fetch();
        return $biz ?: null;
    }

    public function findAll(): array
    {
        return $this->db->query('
            SELECT b.*, u.email, u.nom, u.prenom, u.telephone 
            FROM business b 
            JOIN users u ON b.user_id = u.id 
            ORDER BY b.id DESC
        ')->fetchAll();
    }

    public function findApproved(): array
    {
        return $this->db->query('SELECT * FROM business WHERE status = "valide" ORDER BY nom_business ASC')->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO business (user_id, nom_business, description, localisation, ville, patente, document_verification, status) 
            VALUES (:uid, :nom, :desc, :loc, :ville, :patente, :doc, :status)
        ');
        $stmt->execute([
            'uid' => $data['user_id'],
            'nom' => $data['nom_business'],
            'desc' => $data['description'] ?? null,
            'loc' => $data['localisation'] ?? null,
            'ville' => $data['ville'] ?? null,
            'patente' => $data['patente'] ?? null,
            'doc' => $data['document_verification'] ?? null,
            'status' => $data['status'] ?? 'en attente'
        ]);
        
        $id = (int)$this->db->lastInsertId();
        if (isset($_SESSION['user']['id'])) {
            (new ActivityLog())->log((int)$_SESSION['user']['id'], 'registered business', 'business', $id);
        }
        return $id;
    }

    public function approve(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE business SET status = "valide" WHERE id = :id');
        $res = $stmt->execute(['id' => $id]);

        $biz = $this->findById($id);
        if ($biz) {
            $stmtUser = $this->db->prepare('UPDATE users SET status = "actif" WHERE id = :uid');
            $stmtUser->execute(['uid' => $biz['user_id']]);
        }
        
        if ($res && isset($_SESSION['user']['id'])) {
            (new ActivityLog())->log((int)$_SESSION['user']['id'], 'approved business', 'business', $id);
        }
        return $res;
    }

    public function reject(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE business SET status = 'rejeté' WHERE id = :id");
        $res = $stmt->execute(['id' => $id]);
        if ($res && isset($_SESSION['user']['id'])) {
            (new ActivityLog())->log((int)$_SESSION['user']['id'], 'rejected business', 'business', $id);
        }
        return $res;
    }
}
