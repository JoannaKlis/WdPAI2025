<?php
require_once 'Repository.php';

class PetNutritionRepository extends Repository {
    private static $instance = null;
    private function __construct() { parent::__construct(); }

    public static function getInstance(): PetNutritionRepository {
        return self::$instance ??= new self();
    }

    // Metoda pomocnicza dla prostych wpisów tekstowych (Sensitivities, Food, Supplements)
    private function addSimpleNutrient(string $table, int $petId, string $column, string $value): void {
        $this->insert($table, ['pet_id' => $petId, $column => $value]);
    }

    // SENSITIVITIES
    public function getSensitivities(int $petId) { return $this->fetchAllByPetId('pet_sensitivities', $petId); }
    public function addSensitivity(int $petId, string $food) { $this->addSimpleNutrient('pet_sensitivities', $petId, 'food', $food); }
    public function getSensitivityById(int $id) { return $this->fetchById('pet_sensitivities', $id); }
    public function deleteSensitivity(int $id) { $this->deleteRow('pet_sensitivities', $id); }

    // FAVORITE FOOD
    public function getFavoriteFood(int $petId) { return $this->fetchAllByPetId('pet_favorite_food', $petId); }
    public function addFavoriteFood(int $petId, string $food) { $this->addSimpleNutrient('pet_favorite_food', $petId, 'food', $food); }
    public function getFavoriteFoodById(int $id) { return $this->fetchById('pet_favorite_food', $id); }
    public function deleteFavoriteFood(int $id) { $this->deleteRow('pet_favorite_food', $id); }

    // SUPPLEMENTS
    public function getSupplements(int $petId) { return $this->fetchAllByPetId('pet_supplements', $petId); }
    public function addSupplement(int $petId, string $name) { $this->addSimpleNutrient('pet_supplements', $petId, 'supplement_name', $name); }
    public function getSupplementById(int $id) { return $this->fetchById('pet_supplements', $id); }
    public function deleteSupplement(int $id) { $this->deleteRow('pet_supplements', $id); }

    // SCHEDULE
    public function getFeedingSchedule(int $petId): array { return $this->fetchAllByPetId('pet_feeding_schedule', $petId, 'feeding_time', 'ASC'); }
    public function addScheduleItem(int $petId, array $data): void {
        $this->insert('pet_feeding_schedule', ['pet_id' => $petId, 'name' => $data['name'], 'feeding_time' => $data['time']]);
    }
    public function updateScheduleItem(int $id, string $name, string $time): void {
        $this->update('pet_feeding_schedule', $id, ['name' => $name, 'feeding_time' => $time]);
    }

    public function getScheduleItemById(int $id) { return $this->fetchById('pet_feeding_schedule', $id); }
    public function deleteScheduleItem(int $id) { $this->deleteRow('pet_feeding_schedule', $id); }
}