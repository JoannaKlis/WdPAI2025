<?php

require_once 'Repository.php';

class UserRepository extends Repository {
    public function getUsers(): ?array 
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT * FROM users;
            "
        );
        $query->execute();

        $users = $query->fetchAll(PDO::FETCH_ASSOC);

        return $users;
    }

    // skopiować z pliku UserRepository na teamsach funkcje getUser(string $email) i przerobić

    // a tu metode addUser (first)
}