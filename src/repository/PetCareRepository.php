<?php

require_once 'Repository.php';

class PetCareRepository extends Repository {

    // SINGLETON
    private static $instance = null;

    private function __construct()
    {
        parent::__construct();
    }

    public static function getInstance(): PetCareRepository
    {
        if (self::$instance === null) {
            self::$instance = new PetCareRepository();
        }

        return self::$instance;
    }

    // WEIGHT
    public function getPetWeights(int $petId): array {
        return $this->fetchAllByPetId('pet_weights', $petId, 'recorded_date');
    }

    public function addPetWeight(int $petId, array $data): void {
        $this->insert('pet_weights', [
            'pet_id' => $petId,
            'weight' => $data['weight'],
            'unit' => $data['unit'],
            'recorded_date' => $data['date']
        ]);
    }

    public function getWeightById(int $id): ?array {
        return $this->fetchById('pet_weights', $id);
    }

    public function deletePetWeight(int $id): void {
        $this->deleteRow('pet_weights', $id);
    }

    // GROOMING
    public function getPetGrooming(int $petId): array {
        return $this->fetchAllByPetId('pet_grooming', $petId, 'groom_date');
    }

    public function addPetGrooming(int $petId, array $data): void {
        $this->insert('pet_grooming', [
            'pet_id' => $petId,
            'name' => $data['name'],
            'groom_date' => $data['date']
        ]);
    }

    public function getGroomingById(int $id): ?array {
        return $this->fetchById('pet_grooming', $id);
    }

    public function deletePetGrooming(int $id): void {
        $this->deleteRow('pet_grooming', $id);
    }

    // SHEARING
    public function getPetShearing(int $petId): array {
        return $this->fetchAllByPetId('pet_shearing', $petId, 'shearing_date');
    }

    public function addPetShearing(int $petId, array $data): void {
        $this->insert('pet_shearing', [
            'pet_id' => $petId,
            'name' => $data['name'],
            'shearing_date' => $data['date']
        ]);
    }

    public function getShearingById(int $id): ?array {
        return $this->fetchById('pet_shearing', $id);
    }

    public function deletePetShearing(int $id): void {
        $this->deleteRow('pet_shearing', $id);
    }

    // TRIMMING
    public function getPetTrimming(int $petId): array {
        return $this->fetchAllByPetId('pet_trimming', $petId, 'trimming_date');
    }

    public function addPetTrimming(int $petId, array $data): void {
        $this->insert('pet_trimming', [
            'pet_id' => $petId,
            'name' => $data['name'],
            'trimming_date' => $data['date']
        ]);
    }

    public function getTrimmingById(int $id): ?array {
        return $this->fetchById('pet_trimming', $id);
    }

    public function deletePetTrimming(int $id): void {
        $this->deleteRow('pet_trimming', $id);
    }
}