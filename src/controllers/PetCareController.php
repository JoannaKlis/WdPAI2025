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
        $pet = $this->getPetOr404($_GET['id'] ?? null);
        $petId = (int)$pet['id'];

        // pobranie historii (Repository zwraca je posortowane datami malejąco)
        $weights = $this->petCareRepository->getPetWeights((int)$petId);
        $grooming = $this->petCareRepository->getPetGrooming((int)$petId);
        $shearing = $this->petCareRepository->getPetShearing((int)$petId);
        $trimming = $this->petCareRepository->getPetTrimming((int)$petId);

        // przygotowanie danych dla widoku, pobranie 4 najnowszych wpisów wagi do sekcji summary
        $recentWeights = array_slice($weights, 0, 4);

        return $this->render('care/care', [
            'pet' => $pet,
            'latestWeight' => $weights[0] ?? null,
            'latestGroom' => $grooming[0] ?? null,
            'latestShearing' => $shearing[0] ?? null,
            'latestTrimming' => $trimming[0] ?? null,
            'recentWeights' => $recentWeights
        ]);
    }

    // WEIGHT
    public function weight() {
        return $this->handleView($this->petCareRepository, 'getPetWeights', 'care/weight', 'weights');
    }

    public function addWeight() {
        $pet = $this->getPetOr404($_REQUEST['id'] ?? null);
        $petId = $pet['id'];

        if ($this->isPost()) {
            $weightInput = $this->validateAndSanitizeFloat($_POST['weight'] ?? '');

            if ($weightInput === null)  {
                http_response_code(422);
                return $this->render('422');
            }

            $_POST['weight'] = $weightInput;
            $this->petCareRepository->addPetWeight((int)$petId, $_POST);
            
            // powrót do listy wag tego zwierzaka
            header("Location: /weight?id=" . $petId);
            exit;
        }

        // wyświetlenie formularza - przekazanie petId, żeby formularz wiedział gdzie wysłać POST
        return $this->render('care/addWeight', ['petId' => $petId]);
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