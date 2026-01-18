<?php

require_once __DIR__ . "/../Database.php";

class Repository {
    protected $database;

    public function __construct()
    {
        $this->database = Database::getInstance();
    }

    // Metoda do obsługi transakcji
    protected function executeTransaction(callable $callback) {
        $pdo = $this->database->connect();
        
        try {
            // Sprawdzenie czy transakcja już nie trwa (np. zagnieżdżona)
            if (!$pdo->inTransaction()) {
                // Ustawienie poziomu izolacji READ COMMITTED
                $pdo->exec("SET TRANSACTION ISOLATION LEVEL READ COMMITTED");
                $pdo->beginTransaction();
            }

            $result = $callback();

            // Zatwierdzenie
            if ($pdo->inTransaction()) {
                $pdo->commit();
            }

            return $result;

        } catch (Exception $e) {
            // Wycofanie zmian w przypadku błędu
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    // Metoda do pobierania pojedynczego wiersza po ID
    protected function fetchById(string $table, int $id): ?array {
        $stmt = $this->database->connect()->prepare("SELECT * FROM $table WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Metoda do usuwania wiersza po ID
    protected function deleteRow(string $table, int $id): void {
        $stmt = $this->database->connect()->prepare("DELETE FROM $table WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Metoda do pobierania listy dla danego zwierzaka
    protected function fetchAllByPetId(string $table, int $petId, string $orderByColumn = 'id', string $orderDir = 'DESC'): array {
        $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';    
        $sql = "SELECT * FROM $table WHERE pet_id = :petId ORDER BY $orderByColumn $orderDir, id DESC";
        
        $stmt = $this->database->connect()->prepare($sql);
        $stmt->bindParam(':petId', $petId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Metoda uniwersalna do dodawania
    protected function insert(string $table, array $data): void {
        $columns = array_keys($data);
        $columnsString = implode(', ', $columns);
        $placeholdersString = ':' . implode(', :', $columns);
        $sql = "INSERT INTO $table ($columnsString) VALUES ($placeholdersString)";
        
        $stmt = $this->database->connect()->prepare($sql);
        foreach ($data as $column => $value) {
            $stmt->bindValue(":$column", $value);
        }
        $stmt->execute();
    }
}