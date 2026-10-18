<?php
require_once 'AppController.php';
require_once __DIR__ . '/../repository/VetRepository.php';
require_once __DIR__ . '/../repository/PetRepository.php';
require_once __DIR__ . '/../repository/PetHealthRepository.php';
require_once __DIR__ . '/../repository/PetNutritionRepository.php';

class VetController extends AppController {
    private $vetRepository;
    private $petRepository;
    private $healthRepository;
    private $nutritionRepository;

    public function __construct() {
        parent::__construct();
        $this->vetRepository = VetRepository::getInstance();
        $this->petRepository = PetRepository::getInstance();
        $this->healthRepository = PetHealthRepository::getInstance();
        $this->nutritionRepository = PetNutritionRepository::getInstance();
    }

    // Panel weterynarza z filtrowaniem i wyszukiwarką
    public function vetPanel(): void {
        $user = $_SESSION['user'] ?? null;
        if (!$user || ($user['role'] ?? '') !== 'vet') {
            $this->redirect('403');
            return;
        }

        $search = $_GET['search'] ?? null;
        $petType = $_GET['type'] ?? null;

        $patients = $this->vetRepository->getPatientsByVetId((int)$user['id'], $search, $petType);

        $this->render('vet/panel', [
            'user' => $user,
            'patients' => $patients,
            'currentSearch' => $search,
            'currentType' => $petType
        ]);
    }

    // Karta pacjenta – wgląd w całą książeczkę zdrowia i dane zwierzaka
    public function patientDetails(): void {
        $user = $_SESSION['user'] ?? null;
        if (!$user || ($user['role'] ?? '') !== 'vet') {
            $this->redirect('403');
            return;
        }

        $petId = (int)($_GET['id'] ?? 0);
        $pet = $this->petRepository->getPetById($petId);

        if (!$pet) {
            $this->redirect('404');
            return;
        }

        $this->render('vet/patientDetails', [
            'pet' => $pet,
            'vaccinations' => $this->healthRepository->getPetVaccinations($petId),
            'treatments' => $this->healthRepository->getPetTreatments($petId),
            'deworming' => $this->healthRepository->getPetDeworming($petId),
            'visits' => $this->healthRepository->getPetVisits($petId),
            'sensitivities' => $this->nutritionRepository->getSensitivities($petId)
        ]);
    }

    // Akcja wywoływana przez właściciela: udostępnienie danych wybranemu weterynarzowi
    public function sharePet(): void {
        $user = $_SESSION['user'] ?? null;
        $petId = (int)($_GET['id'] ?? 0);

        if (!$user || !$petId) {
            $this->redirect('welcome');
            return;
        }

        if ($this->isPost()) {
            $vetId = (int)($_POST['vet_id'] ?? 0);
            if ($vetId > 0) {
                $this->vetRepository->assignPetToVet($vetId, $petId);
                $this->redirect("features?id=$petId");
                return;
            }
        }

        $vets = $this->vetRepository->getAllVets();
        $pet = $this->petRepository->getPetById($petId);

        $this->render('sharePet', [
            'pet' => $pet,
            'vets' => $vets
        ]);
    }
}