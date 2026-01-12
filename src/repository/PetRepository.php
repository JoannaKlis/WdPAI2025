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

    public function getSensitivities(int $petId): array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM pet_sensitivities WHERE pet_id = :petId ORDER BY id DESC');
        $stmt->bindParam(':petId', $petId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function addSensitivity(int $petId, string $food): void {
        $stmt = $this->database->connect()->prepare('INSERT INTO pet_sensitivities (pet_id, food) VALUES (?, ?)');
        $stmt->execute([$petId, $food]);
    }
    public function getSensitivityById(int $id): ?array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM pet_sensitivities WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    public function deleteSensitivity(int $id): void {
        $stmt = $this->database->connect()->prepare('DELETE FROM pet_sensitivities WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getFavoriteFood(int $petId): array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM pet_favorite_food WHERE pet_id = :petId ORDER BY id DESC');
        $stmt->bindParam(':petId', $petId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function addFavoriteFood(int $petId, string $food): void {
        $stmt = $this->database->connect()->prepare('INSERT INTO pet_favorite_food (pet_id, food) VALUES (?, ?)');
        $stmt->execute([$petId, $food]);
    }
    public function getFavoriteFoodById(int $id): ?array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM pet_favorite_food WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    public function deleteFavoriteFood(int $id): void {
        $stmt = $this->database->connect()->prepare('DELETE FROM pet_favorite_food WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getSupplements(int $petId): array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM pet_supplements WHERE pet_id = :petId ORDER BY id DESC');
        $stmt->bindParam(':petId', $petId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function addSupplement(int $petId, string $name): void {
        $stmt = $this->database->connect()->prepare('INSERT INTO pet_supplements (pet_id, supplement_name) VALUES (?, ?)');
        $stmt->execute([$petId, $name]);
    }
    public function getSupplementById(int $id): ?array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM pet_supplements WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    public function deleteSupplement(int $id): void {
        $stmt = $this->database->connect()->prepare('DELETE FROM pet_supplements WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

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
        $stmt = $this->database->connect()->prepare('SELECT * FROM pet_feeding_schedule WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    public function deleteScheduleItem(int $id): void {
        $stmt = $this->database->connect()->prepare('DELETE FROM pet_feeding_schedule WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function updateScheduleItem(int $id, string $name, string $time): void {
        $stmt = $this->database->connect()->prepare('
            UPDATE pet_feeding_schedule 
            SET name = :name, feeding_time = :time 
            WHERE id = :id
        ');
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':time', $time, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getEvents(int $userId): array {
        $sql = "
            SELECT 'event' as type, e.id, p.name as pet_name, p.picture_url, e.event_name as title, e.event_date as date, e.event_time as time
            FROM pet_events e JOIN pets p ON e.pet_id = p.id WHERE p.user_id = :userId
            
            UNION ALL

            SELECT 'visit' as type, v.id, p.name as pet_name, p.picture_url, v.visit_name as title, v.visit_date as date, v.visit_time as time
            FROM pet_visits v JOIN pets p ON v.pet_id = p.id WHERE p.user_id = :userId
            
            UNION ALL
            
            SELECT 'treatment' as type, t.id, p.name as pet_name, p.picture_url, t.treatment_name as title, t.treatment_date as date, t.treatment_time as time
            FROM pet_treatments t JOIN pets p ON t.pet_id = p.id WHERE p.user_id = :userId
            
            UNION ALL
            
            SELECT 'vaccination' as type, vac.id, p.name as pet_name, p.picture_url, vac.vaccination_name as title, vac.vaccination_date as date, NULL as time
            FROM pet_vaccinations vac JOIN pets p ON vac.pet_id = p.id WHERE p.user_id = :userId
            
            UNION ALL
            
            SELECT 'deworming' as type, d.id, p.name as pet_name, p.picture_url, d.deworming_name as title, d.deworming_date as date, NULL as time
            FROM pet_deworming d JOIN pets p ON d.pet_id = p.id WHERE p.user_id = :userId
            
            UNION ALL
            
            SELECT 'grooming' as type, g.id, p.name as pet_name, p.picture_url, g.name as title, g.groom_date as date, NULL as time
            FROM pet_grooming g JOIN pets p ON g.pet_id = p.id WHERE p.user_id = :userId
            
            UNION ALL
            
            SELECT 'shearing' as type, s.id, p.name as pet_name, p.picture_url, s.name as title, s.shearing_date as date, NULL as time
            FROM pet_shearing s JOIN pets p ON s.pet_id = p.id WHERE p.user_id = :userId
            
            UNION ALL
            
            SELECT 'trimming' as type, tr.id, p.name as pet_name, p.picture_url, tr.name as title, tr.trimming_date as date, NULL as time
            FROM pet_trimming tr JOIN pets p ON tr.pet_id = p.id WHERE p.user_id = :userId
            
            ORDER BY date ASC, time ASC
        ";

        $stmt = $this->database->connect()->prepare($sql);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPetEvent(int $petId, array $data): void {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO pet_events (pet_id, event_name, event_date, event_time)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([
            $petId,
            $data['name'],
            $data['date'],
            $data['time']
        ]);
    }
}