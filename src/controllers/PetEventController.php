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
        
        $userId = $_SESSION['user_id'];
        $user = $this->getCurrentUser(); 
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
            $pet = $this->getPetOr404($_POST['pet_id'] ?? null);

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
        $this->redirect('calendar');
    }

    public function deleteEvent() {
        if (!$this->isPost()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $eventId = (int)($_POST['id'] ?? 0);
        $event = $this->petEventRepository->getEventById($eventId);
        
        if (!$event || $this->petEventRepository->getEventOwnerId($eventId) !== $_SESSION['user_id']) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $this->petEventRepository->deleteEvent($eventId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}