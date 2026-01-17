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
        $sql = "
            SELECT 'event' as type, e.id, p.name as pet_name, p.picture_url, e.event_name as title, e.event_date as date, e.event_time as time
            FROM pet_events e JOIN pets p ON e.pet_id = p.id WHERE p.user_id = :userId
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

    public function getEventById(int $id): ?array {
        return $this->fetchById('pet_events', $id);
    }

    public function deleteEvent(int $id): void {
        $this->deleteRow('pet_events', $id);
    }
}