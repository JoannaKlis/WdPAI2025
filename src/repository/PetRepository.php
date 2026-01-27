<?php
require_once 'Repository.php';

class PetRepository extends Repository {
    private static $instance = null;
    private function __construct() { parent::__construct(); }

    public static function getInstance(): PetRepository {
        return self::$instance ??= new self();
    }

    public function addPet(array $data, int $userId, ?string $pictureUrl = null): void {
        $this->insert('pets', [
            'user_id' => $userId,
            'pet_type' => $data['type'],
            'name' => $data['name'],
            'birth_date' => $data['birthDate'],
            'sex' => $data['sex'],
            'breed' => $data['breed'],
            'color' => $data['color'],
            'microchip_number' => $data['microchip'],
            'picture_url' => $pictureUrl
        ]);
    }

    public function countUserPets(int $userId): int {
        $stmt = $this->database->connect()->prepare('
            SELECT COUNT(*) FROM pets WHERE user_id = :userId
        ');
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return (int)$stmt->fetchColumn();
    }

    public function getPetsByUserId(int $userId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT 
                p.*,
                calculate_pet_age(p.birth_date) as age_display
            FROM pets p
            WHERE p.user_id = :userId 
            ORDER BY p.created_at DESC
        ');
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPetById(int $id): ?array {
        return $this->fetchById('pets', $id);
    }

    public function updatePet(int $id, array $data, ?string $pictureUrl = null): void {
        $updateData = [
            'name' => $data['name'],
            'pet_type' => $data['type'],
            'birth_date' => $data['birthDate'],
            'sex' => $data['sex'],
            'breed' => $data['breed'],
            'color' => $data['color'],
            'microchip_number' => $data['microchip']
        ];

        if ($pictureUrl) {
            $updateData['picture_url'] = $pictureUrl;
        }

        $this->update('pets', $id, $updateData);
    }

    public function deletePet(int $id): void {
        $this->deleteRow('pets', $id);
    }
}