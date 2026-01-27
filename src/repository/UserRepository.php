<?php
require_once 'Repository.php';

class UserRepository extends Repository {
    private static $instance = null;
    private function __construct() { parent::__construct(); }

    public static function getInstance(): UserRepository {
        return self::$instance ??= new self();
    }

    public function getUsers(): ?array {
        $query = $this->database->connect()->prepare("
            SELECT u.*, (b.user_id IS NOT NULL) as is_banned 
            FROM users u 
            LEFT JOIN user_bans b ON u.id = b.user_id 
            ORDER BY u.id ASC
        ");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserByEmail(string $email)
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT * FROM users WHERE email = :email;
            "
        );
        $query->bindParam(':email', $email);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser(string $firstname, string $lastname, string $email, string $hashedPassword) {
        $this->insert('users', ['firstname' => $firstname, 'lastname' => $lastname, 'email' => $email, 'password' => $hashedPassword]);
    }

    public function updateUser(int $id, string $firstname, string $lastname, ?string $pictureUrl): void {
        $data = [
            'firstname' => $firstname, 
            'lastname' => $lastname
        ];

        if ($pictureUrl) {
            $data['picture_url'] = $pictureUrl;
        }

        $this->update('users', $id, $data);
    }

    public function deleteUser(int $id): void { $this->deleteRow('users', $id); }

    public function updateUserByAdmin(int $id, string $firstname, string $lastname, string $email, string $role): void {
        $this->update('users', $id, [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'role' => $role
        ]);
    }

    public function isUserBanned(int $userId): bool {
        $stmt = $this->database->connect()->prepare('
            SELECT 1 FROM user_bans WHERE user_id = :id
        ');
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetch();
    }

    public function banUser(int $userId, string $reason = 'No reason provided'): void {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO user_bans (user_id, reason) 
            VALUES (:id, :reason)
            ON CONFLICT (user_id) DO NOTHING
        ');
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function unbanUser(int $userId): void {
        $stmt = $this->database->connect()->prepare('
            DELETE FROM user_bans WHERE user_id = :id
        ');
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }
}