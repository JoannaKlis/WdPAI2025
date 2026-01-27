<?php

require_once 'PetController.php';
require_once __DIR__.'/../repository/PetCareRepository.php';

class PetCareController extends PetController {
    private $petCareRepository;

    public function __construct() {
        parent::__construct();
        $this->petCareRepository = PetCareRepository::getInstance();
    }

    public function care() {
        // Pobranie danych zwierzaka lub 404
        $pet = $this->getPetOr404($_GET['id'] ?? null);
        $petId = (int)$pet['id'];

        // Pobranie historii z repozytorium
        $weights = $this->petCareRepository->getPetWeights($petId);
        $grooming = $this->petCareRepository->getPetGrooming($petId);
        $shearing = $this->petCareRepository->getPetShearing($petId);
        $trimming = $this->petCareRepository->getPetTrimming($petId);

        return $this->render('care/care', [
            'pet' => $pet,
            'latestWeight' => $weights[0] ?? null,
            'latestGroom' => $grooming[0] ?? null,
            'latestShearing' => $shearing[0] ?? null,
            'latestTrimming' => $trimming[0] ?? null,
            'recentWeights' => array_slice($weights, 0, 4)
        ]);
    }

    // WEIGHT
    public function weight() {
        return $this->handleView($this->petCareRepository, 'getPetWeights', 'care/weight', 'weights');
    }

    public function addWeight() {
        $pet = $this->getPetOr404($_REQUEST['id'] ?? null);

        if ($this->isPost()) {
            $_POST['weight'] = $this->getValidatedFloat('weight', "Incorrect data format!");
            $this->petCareRepository->addPetWeight((int)$pet['id'], $_POST);
        
            $this->redirectWithId('/weight', $pet['id']);
        }
        return $this->render('care/addWeight', ['petId' => $pet['id']]);
    }

    public function deleteWeight() {
        $this->handleDelete($this->petCareRepository, 'weight_id', 'getWeightById', 'deletePetWeight', '/weight');
    }

    // GROOMING
    public function groom() {
        return $this->handleView($this->petCareRepository, 'getPetGrooming', 'care/groom', 'groomingList');
    }

    public function addGroom() {
        return $this->handleAdd($this->petCareRepository, 'addPetGrooming', '/groom', 'care/addGroom');
    }

    public function deleteGroom() {
        $this->handleDelete($this->petCareRepository, 'groom_id', 'getGroomingById', 'deletePetGrooming', '/groom');
    }

    // SHEARING
    public function shearing() {
        return $this->handleView($this->petCareRepository, 'getPetShearing', 'care/shearing', 'shearingList');
    }

    public function addShearing() {
        return $this->handleAdd($this->petCareRepository, 'addPetShearing', '/shearing', 'care/addShearing');
    }

    public function deleteShearing() {
        $this->handleDelete($this->petCareRepository, 'shearing_id', 'getShearingById', 'deletePetShearing', '/shearing');
    }

    // TRIMMING
    public function trimming() {
        return $this->handleView($this->petCareRepository, 'getPetTrimming', 'care/trimming', 'trimmingList');
    }

    public function addTrimming() {
        return $this->handleAdd($this->petCareRepository, 'addPetTrimming', '/trimming', 'care/addTrimming');
    }

    public function deleteTrimming() {
        $this->handleDelete($this->petCareRepository, 'trimming_id', 'getTrimmingById', 'deletePetTrimming', '/trimming');
    }
}