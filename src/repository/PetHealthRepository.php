<?php

require_once 'Repository.php';

class PetHealthRepository extends Repository {

    // SINGLETON
    private static $instance = null;

    private function __construct()
    {
        parent::__construct();
    }

    public static function getInstance(): PetHealthRepository
    {
        if (self::$instance === null) {
            self::$instance = new PetHealthRepository();
        }
        return self::$instance;
    }

    // Metoda do korzystania z widoku
    public function getAllMedicalHistory(int $petId): array {
        // Pobiera szczepienia, wizyty, zabiegi w jednym zapytaniu posortowane datą
        $stmt = $this->database->connect()->prepare('SELECT * FROM v_pet_medical_history WHERE pet_id = :petId');
        $stmt->bindParam(':petId', $petId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // VACCINATIONS
    public function getPetVaccinations(int $petId): array {
        return $this->fetchAllByPetId('pet_vaccinations', $petId, 'vaccination_date');
    }

    public function addPetVaccination(int $petId, array $data): void {
        $this->insert('pet_vaccinations', [
            'pet_id' => $petId,
            'vaccination_name' => $data['name'],
            'vaccination_date' => $data['date'],
            'dose' => $data['dose'],
            'unit' => $data['unit']
        ]);
    }

    public function getVaccinationById(int $id): ?array {
        return $this->fetchById('pet_vaccinations', $id);
    }

    public function deletePetVaccination(int $id): void {
        $this->deleteRow('pet_vaccinations', $id);
    }

    // TREATMENTS
    public function getPetTreatments(int $petId): array {
        return $this->fetchAllByPetId('pet_treatments', $petId, 'treatment_date');
    }

    public function addPetTreatment(int $petId, array $data): void {
        $this->insert('pet_treatments', [
            'pet_id' => $petId,
            'treatment_name' => $data['name'],
            'treatment_date' => $data['date'],
            'treatment_time' => $data['time']
        ]);
    }

    public function getTreatmentById(int $id): ?array {
        return $this->fetchById('pet_treatments', $id);
    }

    public function deletePetTreatment(int $id): void {
        $this->deleteRow('pet_treatments', $id);
    }

    // DEWORMING
    public function getPetDeworming(int $petId): array {
        return $this->fetchAllByPetId('pet_deworming', $petId, 'deworming_date');
    }

    public function addPetDeworming(int $petId, array $data): void {
        $this->insert('pet_deworming', [
            'pet_id' => $petId,
            'deworming_name' => $data['name'],
            'deworming_date' => $data['date'],
            'dose' => $data['dose'],
            'unit' => $data['unit']
        ]);
    }

    public function getDewormingById(int $id): ?array {
        return $this->fetchById('pet_deworming', $id);
    }

    public function deletePetDeworming(int $id): void {
        $this->deleteRow('pet_deworming', $id);
    }

    // VISITS
    public function getPetVisits(int $petId): array {
        return $this->fetchAllByPetId('pet_visits', $petId, 'visit_date');
    }

    public function addPetVisit(int $petId, array $data): void {
        $this->insert('pet_visits', [
            'pet_id' => $petId,
            'visit_name' => $data['name'],
            'visit_date' => $data['date'],
            'visit_time' => $data['time']
        ]);
    }

    public function getVisitById(int $id): ?array {
        return $this->fetchById('pet_visits', $id);
    }

    public function deletePetVisit(int $id): void {
        $this->deleteRow('pet_visits', $id);
    }
}