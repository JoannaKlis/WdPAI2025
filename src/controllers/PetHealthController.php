<?php

require_once 'PetController.php';
require_once __DIR__.'/../repository/PetHealthRepository.php';

class PetHealthController extends PetController {
    private $petHealthRepository;

    public function __construct() {
        parent::__construct();
        $this->petHealthRepository = PetHealthRepository::getInstance();
    }

    public function healthBook() {
        $pet = $this->getPetOr404($_GET['id'] ?? null);
        $petId = (int)$pet['id'];

        // Pobranie historii z repozytorium
        return $this->render('healthBook/healthBook', [
            'pet' => $pet,
            'latestVaccination' => $this->petHealthRepository->getPetVaccinations($petId)[0] ?? null,
            'latestTreatment' => $this->petHealthRepository->getPetTreatments($petId)[0] ?? null,
            'latestDeworming' => $this->petHealthRepository->getPetDeworming($petId)[0] ?? null,
            'latestVisit' => $this->petHealthRepository->getPetVisits($petId)[0] ?? null,
        ]);
    }

    // VACCINATIONS
    public function vaccinations() {
        return $this->handleView($this->petHealthRepository, 'getPetVaccinations', 'healthbook/vaccinations', 'list');
    }

    public function addVaccination() {
        $pet = $this->getPetOr404($_REQUEST['id'] ?? null);
        
        if ($this->isPost()) {
            // Walidacja dawki
            $_POST['dose'] = $this->getValidatedFloat('dose', "Incorrect dose format.");
            
            $this->petHealthRepository->addPetVaccination((int)$pet['id'], $_POST);
            $this->redirectWithId('vaccinations', $pet['id']);
        }
        return $this->render('healthbook/addVaccination', ['petId' => $pet['id']]);
    }

    public function deleteVaccination() {
        $this->handleDelete($this->petHealthRepository, 'id', 'getVaccinationById', 'deletePetVaccination', 'vaccinations');
    }

    // TREATMENTS
    public function treatments() {
        return $this->handleView($this->petHealthRepository, 'getPetTreatments', 'healthbook/treatments', 'list');
    }

    public function addTreatment() {
        return $this->handleAdd($this->petHealthRepository, 'addPetTreatment', 'treatments', 'healthbook/addTreatment');
    }

    public function deleteTreatment() {
        $this->handleDelete($this->petHealthRepository, 'id', 'getTreatmentById', 'deletePetTreatment', 'treatments');
    }

    // DEWORMING
    public function deworming() {
        return $this->handleView($this->petHealthRepository, 'getPetDeworming', 'healthbook/deworming', 'list');
    }

    public function addDeworming() {
        $pet = $this->getPetOr404($_REQUEST['id'] ?? null);
        
        if ($this->isPost()) {
            // Walidacja dawki
            $_POST['dose'] = $this->getValidatedFloat('dose', "Incorrect dose format.");
            
            $this->petHealthRepository->addPetDeworming((int)$pet['id'], $_POST);
            $this->redirectWithId('deworming', $pet['id']);
        }
        return $this->render('healthbook/addDeworming', ['petId' => $pet['id']]);
    }

    public function deleteDeworming() {
        $this->handleDelete($this->petHealthRepository, 'id', 'getDewormingById', 'deletePetDeworming', 'deworming');
    }

    // VISITS
    public function visits() {
        return $this->handleView($this->petHealthRepository, 'getPetVisits', 'healthbook/visits', 'list');
    }

    public function addVisit() {
        return $this->handleAdd($this->petHealthRepository, 'addPetVisit', 'visits', 'healthbook/addVisit');
    }

    public function deleteVisit() {
        $this->handleDelete($this->petHealthRepository, 'id', 'getVisitById', 'deletePetVisit', 'visits');
    }
}