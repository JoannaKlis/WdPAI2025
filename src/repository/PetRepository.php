<?php

require_once 'Repository.php';

class PetRepository extends Repository {

    public function addPet(array $data, int $userId, ?string $pictureUrl = null): void {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO pets (user_id, pet_type, name, birth_date, sex, breed, color, microchip_number, picture_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $userId,
            $data['type'],
            $data['name'],
            $data['birthDate'],
            $data['sex'],
            $data['breed'],
            $data['color'],
            $data['microchip'],
            $pictureUrl // ścieżka do zdjęcia
        ]);
    }

    public function getPetsByUserId(int $userId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM pets WHERE user_id = :userId ORDER BY created_at DESC
        ');
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPetById(int $id): ?array {
    $stmt = $this->database->connect()->prepare('
        SELECT * FROM pets WHERE id = :id
    ');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $pet = $stmt->fetch(PDO::FETCH_ASSOC);
    return $pet ?: null;
}

public function updatePet(int $id, array $data): void {
    $stmt = $this->database->connect()->prepare('
        UPDATE pets SET 
            name = ?, 
            pet_type = ?, 
            birth_date = ?, 
            sex = ?, 
            breed = ?, 
            color = ?, 
            microchip_number = ? 
        WHERE id = ?
    ');

    $stmt->execute([
        $data['name'],
        $data['type'],
        $data['birthDate'],
        $data['sex'],
        $data['breed'],
        $data['color'],
        $data['microchip'],
        $id
    ]);
}
}