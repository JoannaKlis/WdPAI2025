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

public function updatePet(int $id, array $data, ?string $pictureUrl = null): void {
        $sql = "UPDATE pets SET 
                name = :name, 
                pet_type = :type, 
                birth_date = :birthDate, 
                sex = :sex, 
                breed = :breed, 
                color = :color, 
                microchip_number = :microchip";
        
        $params = [
            ':name' => $data['name'],
            ':type' => $data['type'],
            ':birthDate' => $data['birthDate'],
            ':sex' => $data['sex'],
            ':breed' => $data['breed'],
            ':color' => $data['color'],
            ':microchip' => $data['microchip'],
            ':id' => $id
        ];

        // jeśli przesłano nowe zdjęcie, aktualizujemy je, jeśli null to zostawiamy stare bez zmian
        if ($pictureUrl) {
            $sql .= ", picture_url = :pictureUrl";
            $params[':pictureUrl'] = $pictureUrl;
        }

        $sql .= " WHERE id = :id";

        $stmt = $this->database->connect()->prepare($sql);
        $stmt->execute($params);
    }

    public function deletePet(int $id): void {
        $stmt = $this->database->connect()->prepare('
            DELETE FROM pets WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getPetWeights(int $petId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM pet_weights 
            WHERE pet_id = :petId 
            ORDER BY recorded_date DESC, id DESC
        ');
        $stmt->bindParam(':petId', $petId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPetWeight(int $petId, array $data): void {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO pet_weights (pet_id, weight, unit, recorded_date)
            VALUES (?, ?, ?, ?)
        ');

        $stmt->execute([
            $petId,
            $data['weight'],
            $data['unit'],
            $data['date']
        ]);
    }

    public function getWeightById(int $weightId): ?array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM pet_weights WHERE id = :id');
        $stmt->bindParam(':id', $weightId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function deletePetWeight(int $id): void {
        $stmt = $this->database->connect()->prepare('
            DELETE FROM pet_weights WHERE id = :id
            ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getPetGrooming(int $petId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM pet_grooming 
            WHERE pet_id = :petId 
            ORDER BY groom_date DESC, id DESC
        ');
        $stmt->bindParam(':petId', $petId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPetGrooming(int $petId, array $data): void {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO pet_grooming (pet_id, name, groom_date)
            VALUES (?, ?, ?)
        ');

        $stmt->execute([
            $petId,
            $data['name'],
            $data['date']
        ]);
    }

    public function getGroomingById(int $groomId): ?array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM pet_grooming WHERE id = :id');
        $stmt->bindParam(':id', $groomId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function deletePetGrooming(int $id): void {
        $stmt = $this->database->connect()->prepare('
            DELETE FROM pet_grooming WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getPetShearing(int $petId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM pet_shearing 
            WHERE pet_id = :petId 
            ORDER BY shearing_date DESC, id DESC
        ');
        $stmt->bindParam(':petId', $petId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPetShearing(int $petId, array $data): void {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO pet_shearing (pet_id, name, shearing_date)
            VALUES (?, ?, ?)
        ');

        $stmt->execute([
            $petId,
            $data['name'],
            $data['date']
        ]);
    }

    public function getShearingById(int $id): ?array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM pet_shearing WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function deletePetShearing(int $id): void {
        $stmt = $this->database->connect()->prepare('
            DELETE FROM pet_shearing WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getPetTrimming(int $petId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM pet_trimming 
            WHERE pet_id = :petId 
            ORDER BY trimming_date DESC, id DESC
        ');
        $stmt->bindParam(':petId', $petId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPetTrimming(int $petId, array $data): void {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO pet_trimming (pet_id, name, trimming_date)
            VALUES (?, ?, ?)
        ');

        $stmt->execute([
            $petId,
            $data['name'],
            $data['date']
        ]);
    }

    public function getTrimmingById(int $id): ?array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM pet_trimming WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function deletePetTrimming(int $id): void {
        $stmt = $this->database->connect()->prepare('
            DELETE FROM pet_trimming WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getPetVaccinations(int $petId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM pet_vaccinations WHERE pet_id = :petId ORDER BY vaccination_date DESC, id DESC
        ');
        $stmt->bindParam(':petId', $petId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPetVaccination(int $petId, array $data): void {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO pet_vaccinations (pet_id, vaccination_name, vaccination_date, dose, unit)
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $petId,
            $data['name'], // w HTML name="name"
            $data['date'],
            $data['dose'],
            $data['unit']
        ]);
    }

    public function getVaccinationById(int $id): ?array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM pet_vaccinations WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function deletePetVaccination(int $id): void {
        $stmt = $this->database->connect()->prepare('DELETE FROM pet_vaccinations WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getPetTreatments(int $petId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM pet_treatments WHERE pet_id = :petId ORDER BY treatment_date DESC, id DESC
        ');
        $stmt->bindParam(':petId', $petId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPetTreatment(int $petId, array $data): void {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO pet_treatments (pet_id, treatment_name, treatment_date, treatment_time)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([
            $petId,
            $data['name'],
            $data['date'],
            $data['time']
        ]);
    }

    public function getTreatmentById(int $id): ?array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM pet_treatments WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function deletePetTreatment(int $id): void {
        $stmt = $this->database->connect()->prepare('DELETE FROM pet_treatments WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getPetDeworming(int $petId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM pet_deworming WHERE pet_id = :petId ORDER BY deworming_date DESC, id DESC
        ');
        $stmt->bindParam(':petId', $petId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPetDeworming(int $petId, array $data): void {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO pet_deworming (pet_id, deworming_name, deworming_date, dose, unit)
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $petId,
            $data['name'],
            $data['date'],
            $data['dose'],
            $data['unit']
        ]);
    }

    public function getDewormingById(int $id): ?array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM pet_deworming WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function deletePetDeworming(int $id): void {
        $stmt = $this->database->connect()->prepare('DELETE FROM pet_deworming WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getPetVisits(int $petId): array {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM pet_visits WHERE pet_id = :petId ORDER BY visit_date DESC, id DESC
        ');
        $stmt->bindParam(':petId', $petId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPetVisit(int $petId, array $data): void {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO pet_visits (pet_id, visit_name, visit_date, visit_time)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([
            $petId,
            $data['name'],
            $data['date'],
            $data['time']
        ]);
    }

    public function getVisitById(int $id): ?array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM pet_visits WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function deletePetVisit(int $id): void {
        $stmt = $this->database->connect()->prepare('DELETE FROM pet_visits WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}