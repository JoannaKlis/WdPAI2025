<?php

require_once 'PetController.php';
require_once __DIR__.'/../repository/PetEventRepository.php';

class PetEventController extends PetController {
    private $petEventRepository;

    public function __construct() {
        parent::__construct();

        $this->petEventRepository = PetEventRepository::getInstance();
    }

    public function calendar() {
        $this->checkUser();
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            header("Location: /login");
            exit;
        }

        $user = $this->userRepository->getUserByEmail($_SESSION['user_email']);
        $events = $this->petEventRepository->getEvents($userId);
        $pets = $this->petRepository->getPetsByUserId($userId);

        return $this->render('main/calendar', [
            'user' => $user,
            'events' => $events,
            'pets' => $pets
        ]);
    }

    public function addEvent() {
        if ($this->isPost()) {
            $petId = $_POST['pet_id'] ?? null;
            
            // Weryfikacja czy user jest właścicielem tego peta
            $pet = $this->getPetOr404($petId);

            // Pobranie ID nowo wstawionego rekordu z repozytorium
            $newId = (int)$this->petEventRepository->addPetEvent((int)$pet['id'], [
                'name' => $_POST['name'],
                'date' => $_POST['date'],
                'time' => $_POST['time'] ?? null
            ]);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'id' => $newId,
                'picture_url' => $pet['picture_url']
            ]);
            exit;
        }
    }

    public function deleteEvent() {
        if (!$this->isPost()) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $eventId = $_POST['id'] ?? null;
        
        // Sprawdzenie czy wydarzenie istnieje
        $event = $this->petEventRepository->getEventById((int)$eventId);

        if (!$event || $this->petEventRepository->getEventOwnerId((int)$eventId) !== $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $this->petEventRepository->deleteEvent((int)$eventId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}