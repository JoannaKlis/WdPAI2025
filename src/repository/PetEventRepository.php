<?php

require_once 'Repository.php';

class PetEventRepository extends Repository {

    // SINGLETON
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
        $this->insert('pet_events', [
            'pet_id' => $petId,
            'event_name' => $data['name'],
            'event_date' => $data['date'],
            'event_time' => $data['time']
        ]);
    }

    public function getEventById(int $id): ?array {
        return $this->fetchById('pet_events', $id);
    }

    public function deleteEvent(int $id): void {
        $this->deleteRow('pet_events', $id);
    }
}