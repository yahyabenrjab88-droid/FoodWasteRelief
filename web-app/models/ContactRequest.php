<?php
declare(strict_types=1);

class ContactRequest
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findAll(): array
    {
        try {
            return $this->db->query('SELECT * FROM contact_requests ORDER BY sent_at DESC')->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    public function findUnread(): array
    {
        try {
            return $this->db->query('SELECT * FROM contact_requests WHERE is_read = 0 ORDER BY sent_at DESC')->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    public function create(array $data): int
    {
        try {
            $stmt = $this->db->prepare('
                INSERT INTO contact_requests (sender_id, sender_role, subject, body, sent_at, is_read) 
                VALUES (:sender_id, :sender_role, :subject, :body, NOW(), 0)
            ');
            $stmt->execute([
                'sender_id' => $data['sender_id'] ?? null,
                'sender_role' => $data['sender_role'] ?? 'guest',
                'subject' => $data['subject'],
                'body' => $data['body']
            ]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) { return 0; }
    }

    public function markRead(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('UPDATE contact_requests SET is_read = 1 WHERE id = :id');
            $res = $stmt->execute(['id' => $id]);
            if ($res && isset($_SESSION['user']['id'])) {
                try {
                    (new ActivityLog())->log((int)$_SESSION['user']['id'], 'read contact message', 'contact_request', $id);
                } catch (Exception $e) {}
            }
            return $res;
        } catch (PDOException $e) { return false; }
    }
}
