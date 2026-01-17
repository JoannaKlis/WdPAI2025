<?php

require_once 'Repository.php';

class PetNutritionRepository extends Repository {

    // SINGLETON
    private static $instance = null;

    private function __construct()
    {
        parent::__construct();
    }

    public static function getInstance(): PetNutritionRepository
    {
        if (self::$instance === null) {
            self::$instance = new PetNutritionRepository();
        }

        return self::$instance;
    }

    // SENSITIVITIES
    public function getSensitivities(int $petId): array {
        return $this->fetchAllByPetId('pet_sensitivities', $petId);
    }

    public function addSensitivity(int $petId, string $food): void {
        $this->insert('pet_sensitivities', [
            'pet_id' => $petId,
            'food' => $food
        ]);
    }

    public function getSensitivityById(int $id): ?array {
        return $this->fetchById('pet_sensitivities', $id);
    }

    public function deleteSensitivity(int $id): void {
        $this->deleteRow('pet_sensitivities', $id);
    }

    // FAVORITE FOOD
    public function getFavoriteFood(int $petId): array {
        return $this->fetchAllByPetId('pet_favorite_food', $petId);
    }

    public function addFavoriteFood(int $petId, string $food): void {
        $this->insert('pet_favorite_food', [
            'pet_id' => $petId,
            'food' => $food
        ]);
    }

    public function getFavoriteFoodById(int $id): ?array {
        return $this->fetchById('pet_favorite_food', $id);
    }

    public function deleteFavoriteFood(int $id): void {
        $this->deleteRow('pet_favorite_food', $id);
    }

    // SUPPLEMENTS
    public function getSupplements(int $petId): array {
        return $this->fetchAllByPetId('pet_supplements', $petId);
    }

    public function addSupplement(int $petId, string $name): void {
        $this->insert('pet_supplements', [
            'pet_id' => $petId,
            'supplement_name' => $name
        ]);
    }

    public function getSupplementById(int $id): ?array {
        return $this->fetchById('pet_supplements', $id);
    }

    public function deleteSupplement(int $id): void {
        $this->deleteRow('pet_supplements', $id);
    }

    // SCHEDULE
    public function getFeedingSchedule(int $petId): array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM pet_feeding_schedule WHERE pet_id = :petId ORDER BY feeding_time ASC');
        $stmt->bindParam(':petId', $petId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addScheduleItem(int $petId, array $data): void {
        $stmt = $this->database->connect()->prepare('INSERT INTO pet_feeding_schedule (pet_id, name, feeding_time) VALUES (?, ?, ?)');
        $stmt->execute([$petId, $data['name'], $data['time']]);
    }

    public function getScheduleItemById(int $id): ?array {
        return $this->fetchById('pet_feeding_schedule', $id);
    }

    public function updateScheduleItem(int $id, string $name, string $time): void {
        $stmt = $this->database->connect()->prepare('UPDATE pet_feeding_schedule SET name = :name, feeding_time = :time WHERE id = :id');
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':time', $time, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function deleteScheduleItem(int $id): void {
        $this->deleteRow('pet_feeding_schedule', $id);
    }
}