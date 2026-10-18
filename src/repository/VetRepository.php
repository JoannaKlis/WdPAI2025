<?php
require_once 'Repository.php';

class VetRepository extends Repository {
    private static $instance = null;
    private function __construct() { parent::__construct(); }

    public static function getInstance(): VetRepository {
        return self::$instance ??= new self();
    }

    // Pobranie listy weterynarzy (do wyboru przez właściciela pupila)
    public function getAllVets(): array {
        $stmt = $this->database->connect()->prepare("
            SELECT id, firstname, lastname, email 
            FROM users 
            WHERE role = 'vet'
            ORDER BY lastname ASC, firstname ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Przypisanie zwierzaka do weterynarza (udostępnienie danych)
    public function assignPetToVet(int $vetId, int $petId): void {
        $stmt = $this->database->connect()->prepare("
            INSERT INTO pet_vet_assignments (vet_id, pet_id) 
            VALUES (:vet_id, :pet_id)
            ON CONFLICT (vet_id, pet_id) DO NOTHING
        ");
        $stmt->execute([
            ':vet_id' => $vetId,
            ':pet_id' => $petId
        ]);
    }

    // Pobranie listy pacjentów danego weterynarza z opcjonalnym filtrowaniem
    public function getPatientsByVetId(int $vetId, ?string $search = null, ?string $petType = null): array {
        $sql = "
            SELECT 
                p.id, p.name, p.pet_type, p.breed, p.birth_date, p.sex, 
                p.picture_url, p.microchip_number,
                calculate_pet_age(p.birth_date) AS age_display,
                u.firstname AS owner_firstname,
                u.lastname AS owner_lastname,
                u.email AS owner_email,
                va.assigned_at
            FROM pet_vet_assignments va
            JOIN pets p ON va.pet_id = p.id
            JOIN users u ON p.user_id = u.id
            WHERE va.vet_id = :vet_id
        ";
        
        $params = [':vet_id' => $vetId];

        if (!empty($petType)) {
            $sql .= " AND LOWER(p.pet_type) = LOWER(:pet_type)";
            $params[':pet_type'] = $petType;
        }

        if (!empty($search)) {
            $sql .= " AND (
                LOWER(p.name) LIKE LOWER(:search) 
                OR LOWER(p.microchip_number) LIKE LOWER(:search)
                OR LOWER(u.lastname) LIKE LOWER(:search)
                OR LOWER(u.email) LIKE LOWER(:search)
            )";
            $params[':search'] = '%' . trim($search) . '%';
        }

        $sql .= " ORDER BY p.name ASC";

        $stmt = $this->database->connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}