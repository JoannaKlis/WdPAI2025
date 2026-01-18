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

            $this->petEventRepository->addPetEvent((int)$pet['id'], [
                'name' => $_POST['name'],
                'date' => $_POST['date'],
                'time' => $_POST['time'] ?? null
            ]);
            
            header("Location: /calendar");
            exit;
        }
        
        header("Location: /calendar");
    }

    public function deleteEvent() {
        if (!$this->isPost()) {
            header("Location: /calendar");
            exit;
        }

        $eventId = $_POST['id'] ?? null;
        
        // Sprawdzenie czy wydarzenie istnieje
        $event = $this->petEventRepository->getEventById((int)$eventId);
        if (!$event) {
            header("Location: /calendar");
            exit;
        }

        // Sprawdzenie, czy wydarzenie należy do zwierzaka, który należy do zalogowanego użytkownika
        $ownerId = $this->petEventRepository->getEventOwnerId((int)$eventId);

        if ($ownerId !== $_SESSION['user_id']) {
            http_response_code(403);
            die("Unauthorized access");
        }

        // Usunięcie wydarzenie
        $this->petEventRepository->deleteEvent((int)$eventId);

        header("Location: /calendar");
        exit;
    }
}