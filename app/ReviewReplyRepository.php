<?php

class ReviewReplyRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function save(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO review_replies
             (review_text, industry, stars, tone, reply_1, reply_2, reply_3, risk_level)
             VALUES (:review_text, :industry, :stars, :tone, :reply_1, :reply_2, :reply_3, :risk_level)'
        );
        $stmt->execute([
            ':review_text' => $data['review_text'],
            ':industry'    => $data['industry'],
            ':stars'       => (int)$data['stars'],
            ':tone'        => $data['tone'],
            ':reply_1'     => $data['reply_1'],
            ':reply_2'     => $data['reply_2'],
            ':reply_3'     => $data['reply_3'],
            ':risk_level'  => $data['risk_level'],
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function getHistory(int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, review_text, industry, stars, tone, reply_1, reply_2, reply_3, risk_level, created_at
             FROM review_replies
             ORDER BY created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM review_replies WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
