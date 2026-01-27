<?php
require_once 'Repository.php';

class PetCareRepository extends Repository {
    private static $instance = null;
    private function __construct() { parent::__construct(); }

    public static function getInstance(): PetCareRepository {
        return self::$instance ??= new self();
    }

    // Funkcja pomocnicza dla powtarzalnych wpisów "pielęgnacyjnych" (Grooming, Shearing, Trimming)
    private function addSimpleCareEntry(string $table, int $petId, array $data, string $dateColumn): void {
        $this->insert($table, [
            'pet_id' => $petId,
            'name' => $data['name'],
            $dateColumn => $data['date']
        ]);
    }

    // WEIGHT
    public function getPetWeights(int $petId) { return $this->fetchAllByPetId('pet_weights', $petId, 'recorded_date'); }
    public function addPetWeight(int $petId, array $data) {
        $this->insert('pet_weights', ['pet_id' => $petId, 'weight' => $data['weight'], 'unit' => $data['unit'], 'recorded_date' => $data['date']]);
    }
    public function getWeightById(int $id) { return $this->fetchById('pet_weights', $id); }
    public function deletePetWeight(int $id) { $this->deleteRow('pet_weights', $id); }

    // GROOMING
    public function getPetGrooming(int $petId) { return $this->fetchAllByPetId('pet_grooming', $petId, 'groom_date'); }
    public function addPetGrooming(int $petId, array $data) { $this->addSimpleCareEntry('pet_grooming', $petId, $data, 'groom_date'); }
    public function getGroomingById(int $id) { return $this->fetchById('pet_grooming', $id); }
    public function deletePetGrooming(int $id) { $this->deleteRow('pet_grooming', $id); }

    // SHEARING
    public function getPetShearing(int $petId) { return $this->fetchAllByPetId('pet_shearing', $petId, 'shearing_date'); }
    public function addPetShearing(int $petId, array $data) { $this->addSimpleCareEntry('pet_shearing', $petId, $data, 'shearing_date'); }
    public function getShearingById(int $id) { return $this->fetchById('pet_shearing', $id); }
    public function deletePetShearing(int $id) { $this->deleteRow('pet_shearing', $id); }

    // TRIMMING
    public function getPetTrimming(int $petId) { return $this->fetchAllByPetId('pet_trimming', $petId, 'trimming_date'); }
    public function addPetTrimming(int $petId, array $data) { $this->addSimpleCareEntry('pet_trimming', $petId, $data, 'trimming_date'); }
    public function getTrimmingById(int $id) { return $this->fetchById('pet_trimming', $id); }
    public function deletePetTrimming(int $id) { $this->deleteRow('pet_trimming', $id); }
}