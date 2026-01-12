<?php

require_once 'Repository.php';

class UserRepository extends Repository {
    public function getUsers(): ?array 
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT * FROM users ORDER BY id ASC;
            "
        );
        $query->execute();

        $users = $query->fetchAll(PDO::FETCH_ASSOC);

        return $users;
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

        $user = $query->fetch(PDO::FETCH_ASSOC);

        return $user;
    }

    public function createUser(
        string $firstname,
        string $lastname,
        string $email,
        string $hashedPassword
    ) {
        $query = $this->database->connect()->prepare(
        "
                INSERT INTO users (firstname, lastname, email, password)
                VALUES (?, ?, ?, ?);
                "
        );
        $query->execute([
            $firstname,
            $lastname,
            $email,
            $hashedPassword
        ]);
    }

    public function updateUser(int $id, string $firstname, string $lastname, string $email, ?string $password, ?string $pictureUrl): void {
        $sql = "UPDATE users SET firstname = :firstname, lastname = :lastname, email = :email";
        $params = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'id' => $id
        ];
        if ($password) {
            $sql .= ", password = :password";
            $params['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        if ($pictureUrl) {
            $sql .= ", picture_url = :picture_url";
            $params['picture_url'] = $pictureUrl;
        }

        $sql .= " WHERE id = :id";
        $stmt = $this->database->connect()->prepare($sql);
        $stmt->execute($params);
    }

    public function deleteUser(int $id): void {
        $stmt = $this->database->connect()->prepare('
            DELETE FROM users WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function updateUserByAdmin(int $id, string $firstname, string $lastname, string $email, string $role): void {
        $stmt = $this->database->connect()->prepare('
            UPDATE users 
            SET firstname = :firstname, 
                lastname = :lastname, 
                email = :email, 
                role = :role 
            WHERE id = :id
        ');

        $stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);
        $stmt->bindParam(':lastname', $lastname, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();
    }
}