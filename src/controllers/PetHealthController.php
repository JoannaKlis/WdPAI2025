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

        // Pobranie całej historii medycznej z widoku
        $fullHistory = $this->petHealthRepository->getAllMedicalHistory($petId);

        // Filtrowanie danych
        $vaccinations = array_filter($fullHistory, fn($item) => $item['type'] === 'Vaccination');
        $treatments   = array_filter($fullHistory, fn($item) => $item['type'] === 'Treatment');
        $deworming    = array_filter($fullHistory, fn($item) => $item['type'] === 'Deworming');
        $visits       = array_filter($fullHistory, fn($item) => $item['type'] === 'Visit');

        // Przekazanie gotowych tablic do widoku
        return $this->render('healthbook/healthBook', [
            'pet' => $pet,
            'vaccinations' => $vaccinations,
            'treatments' => $treatments,
            'deworming' => $deworming,
            'visits' => $visits
        ]);
    }

    // VACCINATIONS
    public function vaccinations() {
        return $this->handleView($this->petHealthRepository, 'getPetVaccinations', 'healthbook/vaccinations', 'list');
    }

    public function addVaccination() {
        $pet = $this->getPetOr404($_REQUEST['id'] ?? null);
        if ($this->isPost()) {
            $doseInput = $this->validateAndSanitizeFloat($_POST['dose'] ?? '');
            if ($doseInput === null) { http_response_code(422); return $this->render('422'); }
            
            $_POST['dose'] = $doseInput;
            $this->petHealthRepository->addPetVaccination((int)$pet['id'], $_POST);
            header("Location: /vaccinations?id=" . $pet['id']);
            exit;
        }
        return $this->render('healthbook/addVaccination', ['petId' => $pet['id']]);
    }

    public function deleteVaccination() {
        $this->handleDelete($this->petHealthRepository, 'id', 'getVaccinationById', 'deletePetVaccination', '/vaccinations');
    }

    // TREATMENTS
    public function treatments() {
        return $this->handleView($this->petHealthRepository, 'getPetTreatments', 'healthbook/treatments', 'list');
    }

    public function addTreatment() {
        return $this->handleAdd($this->petHealthRepository, 'addPetTreatment', '/treatments', 'healthbook/addTreatment');
    }

    public function deleteTreatment() {
        $this->handleDelete($this->petHealthRepository, 'id', 'getTreatmentById', 'deletePetTreatment', '/treatments');
    }

    // DEWORMING
    public function deworming() {
        return $this->handleView($this->petHealthRepository, 'getPetDeworming', 'healthbook/deworming', 'list');
    }

    public function addDeworming() {
        $pet = $this->getPetOr404($_REQUEST['id'] ?? null);
        if ($this->isPost()) {
            $doseInput = $this->validateAndSanitizeFloat($_POST['dose'] ?? '');
            if ($doseInput === null) { http_response_code(422); return $this->render('422'); }
            
            $_POST['dose'] = $doseInput;
            $this->petHealthRepository->addPetDeworming((int)$pet['id'], $_POST);
            header("Location: /deworming?id=" . $pet['id']);
            exit;
        }
        return $this->render('healthbook/addDeworming', ['petId' => $pet['id']]);
    }

    public function deleteDeworming() {
        $this->handleDelete($this->petHealthRepository, 'id', 'getDewormingById', 'deletePetDeworming', '/deworming');
    }

    // VISITS
    public function visits() {
        return $this->handleView($this->petHealthRepository, 'getPetVisits', 'healthbook/visits', 'list');
    }

    public function addVisit() {
        return $this->handleAdd($this->petHealthRepository, 'addPetVisit', '/visits', 'healthbook/addVisit');
    }

    public function deleteVisit() {
        $this->handleDelete($this->petHealthRepository, 'id', 'getVisitById', 'deletePetVisit', '/visits');
    }
}