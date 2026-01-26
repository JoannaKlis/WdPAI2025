<?php

require_once 'Repository.php';

class PetEventRepository extends Repository {

    private static $instance = null;

    private function __construct()
    {
        parent::__construct();
    }

    public static function getInstance(): PetEventRepository
    {
        if (self::$instance === null) {
            self::$instance = new PetEventRepository();
        }

        return self::$instance;
    }

    public function getEvents(int $userId): array {
        // Użycie widoku v_pet_events_calendar
        $sql = "
            SELECT 'event' as type, event_id as id, pet_name, picture_url, title, date, time
            FROM v_pet_events_calendar 
            WHERE user_id = :userId
            ORDER BY date ASC, time ASC
        ";

        $stmt = $this->database->connect()->prepare($sql);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPetEvent(int $petId, array $data): ?int {
        $newId = null; // Zmienna pomocnicza do przechwycenia ID

        $this->executeTransaction(function() use ($petId, $data, &$newId) {
            $pdo = $this->database->connect();

            $stmt = $pdo->prepare('
                INSERT INTO global_events (event_name, event_date, event_time)
                VALUES (:name, :date, :time)
            ');
            $stmt->bindValue(':name', $data['name']);
            $stmt->bindValue(':date', $data['date']);
            $stmt->bindValue(':time', $data['time']);
            $stmt->execute();

            $newId = (int)$pdo->lastInsertId(); // Pobranie ID wewnątrz transakcji

            $stmt = $pdo->prepare('
                INSERT INTO event_participants (event_id, pet_id)
                VALUES (:eventId, :petId)
            ');
            $stmt->bindValue(':eventId', $newId, PDO::PARAM_INT);
            $stmt->bindValue(':petId', $petId, PDO::PARAM_INT);
            $stmt->execute();
        });

        return $newId; // Zwrócenie ID do kontrolera
    }

    public function getEventById(int $id): ?array {
        return $this->fetchById('global_events', $id);
    }

    public function deleteEvent(int $id): void {
        $this->deleteRow('global_events', $id);
    }
    
    public function getEventOwnerId(int $eventId): ?int {
        $stmt = $this->database->connect()->prepare('
            SELECT p.user_id 
            FROM event_participants ep
            JOIN pets p ON ep.pet_id = p.id
            WHERE ep.event_id = :eventId
            LIMIT 1
        ');
        $stmt->bindParam(':eventId', $eventId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchColumn() ?: null;
    }
}