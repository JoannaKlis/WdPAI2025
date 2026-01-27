<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/PetRepository.php';

class PetController extends AppController {
    protected $petRepository;

    public function __construct() {
        parent::__construct(); 
        $this->petRepository = PetRepository::getInstance();
    }

    public function pets() {
        $this->checkUser(); 
        
        $userId = $_SESSION['user_id'];
        $user = $this->getCurrentUser();
        $pets = $this->petRepository->getPetsByUserId($userId);
        
        return $this->render('pets/pets', ['pets' => $pets, 'user' => $user]);
    }

    public function addPet() {
        $this->checkUser();
        $userId = $_SESSION['user_id'];

        if ($this->petRepository->countUserPets($userId) >= 50) {
            header("Location: /pets?error=limit_reached");
            exit;
        }

        if ($this->isPost()) {
            $pictureUrl = $this->handleImageUpload();
            $this->petRepository->addPet($_POST, $_SESSION['user_id'], $pictureUrl);
            $this->redirect('pets');
        }
        return $this->render('pets/addPet');
    }

    public function features() {
        $pet = $this->getPetOr404($_GET['id'] ?? null);
        return $this->render('pets/features', ['pet' => $pet]);
    }

    public function editPet() {
        $pet = $this->getPetOr404($_REQUEST['id'] ?? null);

        if ($this->isPost()) {
            $pictureUrl = $this->handleImageUpload();
            $this->petRepository->updatePet((int)$pet['id'], $_POST, $pictureUrl);
            $this->redirectWithId('features', $pet['id']);
        }

        return $this->render('pets/editPet', ['pet' => $pet]);
    }

    public function deletePet() {
        $this->checkUser();
        if (!$this->isPost()) {
            $this->redirect('pets');
            exit;
        }

        $petId = $_POST['id'] ?? null;
        $pet = $this->petRepository->getPetById((int)$petId);

        if ($pet && $pet['user_id'] === $_SESSION['user_id']) {
            $this->petRepository->deletePet((int)$petId);
        }

        $this->redirect('pets');
    }

    // HELPERY

    protected function getPetOr404(mixed $petId): ?array {
        $this->checkUser();
        if (!$petId) {
            $this->redirect('pets');
        }

        $pet = $this->petRepository->getPetById((int)$petId);
        if (!$pet || $pet['user_id'] !== $_SESSION['user_id']) {
            $this->render('errors/404');
            exit;
        }

        return $pet;
    }

    protected function handleView(object $repository, string $fetchMethod, string $viewTemplate, string $listVarName) {
        $pet = $this->getPetOr404($_GET['id'] ?? null);
        $list = $repository->$fetchMethod((int)$pet['id']);

        return $this->render($viewTemplate, [
            'pet' => $pet, 
            $listVarName => $list
        ]);
    }

    protected function handleAdd(object $repository, string $addMethod, string $redirectRoute, string $viewTemplate) {
        $pet = $this->getPetOr404($_REQUEST['id'] ?? null);

        if ($this->isPost()) {
            $repository->$addMethod((int)$pet['id'], $_POST);
            $this->redirectWithId($redirectRoute, $pet['id']);
        }

        return $this->render($viewTemplate, ['petId' => $pet['id']]);
    }

    protected function handleDelete(object $repository, string $postKeyId, string $fetchMethod, string $deleteMethod, string $redirectUrl) {
        if (!$this->isPost()) { 
            $this->redirect('pets');
        }

        $id = (int)($_POST[$postKeyId] ?? 0);
        $entry = $repository->$fetchMethod($id);

        if ($entry) {
            $this->getPetOr404($entry['pet_id']);
            $repository->$deleteMethod($id);
            $this->redirectWithId($redirectUrl, $entry['pet_id']);
        }
        
        $this->redirect('pets');
    }
}