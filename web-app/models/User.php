<?php
declare(strict_types=1);

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (PDOException $e) { return null; }
    }

    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (PDOException $e) { return null; }
    }

    public function findAll(): array
    {
        try {
            return $this->db->query('SELECT * FROM users ORDER BY id DESC')->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    public function findByRole(string $role): array
    {
        try {
            $stmt = $this->db->prepare('SELECT * FROM users WHERE role = :role ORDER BY id DESC');
            $stmt->execute(['role' => $role]);
            return $stmt->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (nom, prenom, email, password, role, telephone, status)
             VALUES (:nom, :prenom, :email, :password, :role, :telephone, :status)'
        );
        $stmt->execute([
            'nom'       => $data['nom'],
            'prenom'    => $data['prenom'],
            'email'     => $data['email'],
            'password'  => $data['password'],
            'role'      => $data['role'],
            'telephone' => $data['telephone'] ?? null,
            'status'    => $data['status'] ?? 'en_attente',
        ]);
        
        $id = (int)$this->db->lastInsertId();
        if (isset($_SESSION['user']['id'])) {
            try {
                (new ActivityLog())->log($id, 'registered account', 'user', $id);
            } catch (Exception $e) {}
        }
        return $id;
    }

    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare('
                UPDATE users 
                SET nom = :nom, prenom = :prenom, telephone = :telephone, role = :role, email = :email 
                WHERE id = :id
            ');
            $res = $stmt->execute([
                'id' => $id,
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'telephone' => $data['telephone'],
                'role' => $data['role'],
                'email' => $data['email']
            ]);
            
            if (isset($_SESSION['user']['id'])) {
                try {
                    (new ActivityLog())->log((int)$_SESSION['user']['id'], 'updated user profile', 'user', $id);
                } catch (Exception $e) {}
            }
            return $res;
        } catch (PDOException $e) { return false; }
    }

    public function updatePasswordHash(int $id, string $hash): bool
    {
        try {
            $stmt = $this->db->prepare('UPDATE users SET password = :hash WHERE id = :id');
            return $stmt->execute(['hash' => $hash, 'id' => $id]);
        } catch (PDOException $e) { return false; }
    }

    public function toggleActive(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE users SET status = IF(status = 'actif', 'inactif', 'actif') WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) { return false; }
    }

    public function delete(int $id): bool
    {
        try {
            
            $this->db->prepare('DELETE FROM cart_items WHERE client_id = :id')->execute(['id' => $id]);
            $this->db->prepare('UPDATE activity_logs SET user_id = NULL WHERE user_id = :id')->execute(['id' => $id]);
            
            $this->db->prepare('UPDATE associations SET user_id = NULL WHERE user_id = :id')->execute(['id' => $id]);
            $this->db->prepare('UPDATE business SET user_id = NULL WHERE user_id = :id')->execute(['id' => $id]);

            $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
            $res = $stmt->execute(['id' => $id]);
            if ($res && isset($_SESSION['user']['id'])) {
                try {
                    (new ActivityLog())->log((int)$_SESSION['user']['id'], 'deleted user', 'user', $id);
                } catch (Exception $e) {}
            }
            return $res;
        } catch (PDOException $e) {
            error_log('User->delete FK error (deactivating instead): ' . $e->getMessage());
            
            try {
                $stmt2 = $this->db->prepare("UPDATE users SET status = 'inactif' WHERE id = :id");
                return $stmt2->execute(['id' => $id]);
            } catch (PDOException $e2) { return false; }
        }
    }
}
