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

    public function addPetEvent(int $petId, array $data): void {
        // Używamy transakcji (metoda z klasy Repository), aby zapewnić spójność
        $this->executeTransaction(function() use ($petId, $data) {
            $pdo = $this->database->connect();

            // 1. Dodaj wydarzenie do tabeli globalnej
            $stmt = $pdo->prepare('
                INSERT INTO global_events (event_name, event_date, event_time)
                VALUES (:name, :date, :time)
            ');
            $stmt->bindValue(':name', $data['name']);
            $stmt->bindValue(':date', $data['date']);
            $stmt->bindValue(':time', $data['time']);
            $stmt->execute();

            // Pobierz ID nowo utworzonego wydarzenia
            $eventId = $pdo->lastInsertId();

            // 2. Przypisz wydarzenie do zwierzaka (tabela łącząca M:N)
            $stmt = $pdo->prepare('
                INSERT INTO event_participants (event_id, pet_id)
                VALUES (:eventId, :petId)
            ');
            $stmt->bindValue(':eventId', $eventId, PDO::PARAM_INT);
            $stmt->bindValue(':petId', $petId, PDO::PARAM_INT);
            $stmt->execute();
        });
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