<?php
require_once 'Repository.php';

class PetHealthRepository extends Repository {
    private static $instance = null;
    private function __construct() { parent::__construct(); }

    public static function getInstance(): PetHealthRepository {
        return self::$instance ??= new self();
    }

    public function getAllMedicalHistory(int $petId): array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM v_pet_medical_history WHERE pet_id = :petId');
        $stmt->bindParam(':petId', $petId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // VACCINATIONS
    public function getPetVaccinations(int $petId): array { return $this->fetchAllByPetId('pet_vaccinations', $petId, 'vaccination_date'); }
    public function addPetVaccination(int $petId, array $data): void {
        $this->insert('pet_vaccinations', [
            'pet_id' => $petId,
            'vaccination_name' => $data['name'],
            'vaccination_date' => $data['date'],
            'dose' => $data['dose'],
            'unit' => $data['unit']
        ]);
    }
    public function getVaccinationById(int $id): ?array { return $this->fetchById('pet_vaccinations', $id); }
    public function deletePetVaccination(int $id): void { $this->deleteRow('pet_vaccinations', $id); }

    // TREATMENTS & VISITS (Wspólna logika dla tabel z name/date/time)
    private function addTimedEntry(string $table, int $petId, array $data, string $prefix): void {
        $this->insert($table, [
            'pet_id' => $petId,
            "{$prefix}_name" => $data['name'],
            "{$prefix}_date" => $data['date'],
            "{$prefix}_time" => $data['time']
        ]);
    }

    // TREATMENTS
    public function getPetTreatments(int $petId) { return $this->fetchAllByPetId('pet_treatments', $petId, 'treatment_date'); }
    public function addPetTreatment(int $petId, array $data) { $this->addTimedEntry('pet_treatments', $petId, $data, 'treatment'); }
    public function getTreatmentById(int $id) { return $this->fetchById('pet_treatments', $id); }
    public function deletePetTreatment(int $id) { $this->deleteRow('pet_treatments', $id); }

    // VISITS
    public function getPetVisits(int $petId) { return $this->fetchAllByPetId('pet_visits', $petId, 'visit_date'); }
    public function addPetVisit(int $petId, array $data) { $this->addTimedEntry('pet_visits', $petId, $data, 'visit'); }
    public function getVisitById(int $id) { return $this->fetchById('pet_visits', $id); }
    public function deletePetVisit(int $id) { $this->deleteRow('pet_visits', $id); }

    // DEWORMING
    public function getPetDeworming(int $petId) { return $this->fetchAllByPetId('pet_deworming', $petId, 'deworming_date'); }
    public function addPetDeworming(int $petId, array $data) {
        $this->insert('pet_deworming', [
            'pet_id' => $petId, 
            'deworming_name' => $data['name'], 
            'deworming_date' => $data['date'], 
            'dose' => $data['dose'], 
            'unit' => $data['unit']
        ]);
    }
    
    public function getDewormingById(int $id) { return $this->fetchById('pet_deworming', $id); }
    public function deletePetDeworming(int $id) { $this->deleteRow('pet_deworming', $id); }
}