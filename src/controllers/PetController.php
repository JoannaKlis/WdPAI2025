<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/PetRepository.php';
require_once __DIR__.'/../repository/UserRepository.php';

class PetController extends AppController {
    private $petRepository;
    private $userRepository;

    public function __construct() {
        $this->petRepository = new PetRepository();
        $this->userRepository = new UserRepository();
    }

    public function pets() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            header("Location: http://$_SERVER[HTTP_HOST]/login");
            exit;
        }
        $user = $this->userRepository->getUserByEmail($_SESSION['user_email']);
        $pets = $this->petRepository->getPetsByUserId($userId);
        
        return $this->render('pets/pets', ['pets' => $pets, 'user' => $user]);
    }

    public function addPet() {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            header("Location: http://$_SERVER[HTTP_HOST]/login");
            exit;
        }

        if (!$this->isPost()) {
            return $this->render('pets/addPet', ['pet' => ['picture_url' => null]]);
        }

        // obsługa uploadu zdjęcia
        $pictureUrl = null;
        if (isset($_FILES['picture']) && is_uploaded_file($_FILES['picture']['tmp_name'])) {
            $imageData = file_get_contents($_FILES['picture']['tmp_name']);
            $mimeType = mime_content_type($_FILES['picture']['tmp_name']);
            $pictureUrl = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        }

        // przekazanie danych i URL zdjęcia do repozytorium
        $this->petRepository->addPet($_POST, $userId, $pictureUrl);
        header("Location: http://$_SERVER[HTTP_HOST]/pets");
    }

    public function features() {
        session_start(); // inicjalizacja sesji
        $userId = $_SESSION['user_id'] ?? null;
        $petId = $_GET['id'] ?? null;

        if (!$petId || !$userId) {
            header("Location: http://$_SERVER[HTTP_HOST]/pets");
            exit;
        }

        $pet = $this->petRepository->getPetById((int)$petId);

        // walidacja właściciela rekordu
            if (!$pet || $pet['user_id'] !== $userId) {
            return $this->render('404');
        }

        return $this->render('pets/features', ['pet' => $pet]);
    }

    public function editPet() {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        // pobranie ID z parametru URL (GET) lub z formularza (POST)
        $petId = $_REQUEST['id'] ?? null;

        if (!$petId || !$userId) {
            header("Location: http://$_SERVER[HTTP_HOST]/pets");
            exit;
        }

        // walidacja właściciela zwierzaka
        $pet = $this->petRepository->getPetById((int)$petId);
        if (!$pet || $pet['user_id'] !== $userId) {
            return $this->render('404');
        }

        if ($this->isPost()) {
            // obsługa zdjęcia
            $pictureUrl = null;
            if (isset($_FILES['picture']) && is_uploaded_file($_FILES['picture']['tmp_name'])) {
                $imageData = file_get_contents($_FILES['picture']['tmp_name']);
                $mimeType = mime_content_type($_FILES['picture']['tmp_name']);
                $pictureUrl = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }

            // aktualizacja w bazie
            $this->petRepository->updatePet((int)$petId, $_POST, $pictureUrl);
            
            // przekierowanie do widoku szczegółów
            header("Location: http://$_SERVER[HTTP_HOST]/features?id=" . $petId);
            exit;
        }

        return $this->render('pets/editPet', ['pet' => $pet]);
    }

    public function deletePet() {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;

        // zabezpieczenie: jeśli ktoś próbuje wejść tu przez GET (wpisując url), od razu wyrzuć go do listy zwierząt.
        if (!$this->isPost()) {
            header("Location: http://$_SERVER[HTTP_HOST]/pets");
            exit;
        }

        $petId = $_POST['id'] ?? null;

        // walidacja danych sesji i ID
        if (!$petId || !$userId) {
             header("Location: http://$_SERVER[HTTP_HOST]/pets");
             exit;
        }

        // sprawdzenie czy użytkownik jest właścicielem (bezpieczeństwo)
        $pet = $this->petRepository->getPetById((int)$petId);
        if ($pet && $pet['user_id'] === $userId) {
            $this->petRepository->deletePet((int)$petId);
        }

        // przekierowanie do widoku /pets po wykonaniu akcji
        header("Location: http://$_SERVER[HTTP_HOST]/pets");
        exit;
    }

    public function care() {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        $petId = $_GET['id'] ?? null;

        if (!$petId || !$userId) {
            header("Location: /pets");
            exit;
        }

        // pobranie danych zwierzaka
        $pet = $this->petRepository->getPetById((int)$petId);

        // walidacja właściciela
        if (!$pet || $pet['user_id'] !== $userId) {
            return $this->render('404');
        }

        return $this->render('care/care', ['pet' => $pet]);
    }

    public function weight() {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        $petId = $_GET['id'] ?? null;

        if (!$petId || !$userId) {
            header("Location: /pets");
            exit;
        }

        // pobranie danych zwierzaka (dla nagłówka, zdjęcia itp.)
        $pet = $this->petRepository->getPetById((int)$petId);
        
        // walidacja czy to zwierzak zalogowanego użytkownika
        if (!$pet || $pet['user_id'] !== $userId) {
            return $this->render('404');
        }

        // pobranie historii wagi
        $weights = $this->petRepository->getPetWeights((int)$petId);

        return $this->render('care/weight', ['pet' => $pet, 'weights' => $weights]);
    }

    public function addWeight() {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        // ID może przyjść z GET (wyświetlenie formularza) lub POST (wysłanie formularza)
        $petId = $_REQUEST['id'] ?? null;

        if (!$petId || !$userId) {
            header("Location: /pets");
            exit;
        }

        // walidacja właściciela
        $pet = $this->petRepository->getPetById((int)$petId);
        if (!$pet || $pet['user_id'] !== $userId) {
            return $this->render('404');
        }

        if ($this->isPost()) {
            $this->petRepository->addPetWeight((int)$petId, $_POST);
            
            // powrót do listy wag tego zwierzaka
            header("Location: /weight?id=" . $petId);
            exit;
        }

        // wyświetlenie formularza - przekazanie petId, żeby formularz wiedział gdzie wysłać POST
        return $this->render('care/addWeight', ['petId' => $petId]);
    }

    public function deleteWeight() {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        if (!$this->isPost() || !$userId) { header("Location: /pets"); exit; }

        $weightId = $_POST['weight_id'] ?? null;
        
        // sprawdzenie czy waga istnieje
        $weightEntry = $this->petRepository->getWeightById((int)$weightId);
        if (!$weightEntry) { header("Location: /pets"); exit; }

        // sprawdzenie czy zwierzak należy do użytkownika
        $pet = $this->petRepository->getPetById($weightEntry['pet_id']);
        if ($pet && (int)$pet['user_id'] === (int)$userId) {
            $this->petRepository->deletePetWeight((int)$weightId);
        }

        // powrót do widoku wagi
        header("Location: /weight?id=" . $weightEntry['pet_id']);
        exit;
    }

    public function groom() {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        $petId = $_GET['id'] ?? null;

        if (!$petId || !$userId) {
            header("Location: /pets");
            exit;
        }

        $pet = $this->petRepository->getPetById((int)$petId);
        if (!$pet || $pet['user_id'] !== $userId) {
            return $this->render('404');
        }

        // pobranie listy zabiegów pielęgnacyjnych
        $groomingList = $this->petRepository->getPetGrooming((int)$petId);

        return $this->render('care/groom', ['pet' => $pet, 'groomingList' => $groomingList]);
    }

    public function addGroom() {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        $petId = $_REQUEST['id'] ?? null;

        if (!$petId || !$userId) {
            header("Location: /pets");
            exit;
        }

        $pet = $this->petRepository->getPetById((int)$petId);
        if (!$pet || $pet['user_id'] !== $userId) {
            return $this->render('404');
        }

        if ($this->isPost()) {
            $this->petRepository->addPetGrooming((int)$petId, $_POST);
            header("Location: /groom?id=" . $petId);
            exit;
        }

        return $this->render('care/addGroom', ['petId' => $petId]);
    }

    public function deleteGroom() {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        if (!$this->isPost() || !$userId) { header("Location: /pets"); exit; }

        $groomId = $_POST['groom_id'] ?? null;
        
        $entry = $this->petRepository->getGroomingById((int)$groomId);
        if (!$entry) { header("Location: /pets"); exit; }

        $pet = $this->petRepository->getPetById($entry['pet_id']);
        if ($pet && (int)$pet['user_id'] === (int)$userId) {
            $this->petRepository->deletePetGrooming((int)$groomId);
        }

        header("Location: /groom?id=" . $entry['pet_id']);
        exit;
    }

    public function shearing() {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        $petId = $_GET['id'] ?? null;

        if (!$petId || !$userId) {
            header("Location: /pets");
            exit;
        }

        $pet = $this->petRepository->getPetById((int)$petId);
        if (!$pet || $pet['user_id'] !== $userId) {
            return $this->render('404');
        }

        // pobranie listy strzyżeń
        $shearingList = $this->petRepository->getPetShearing((int)$petId);

        return $this->render('care/shearing', ['pet' => $pet, 'shearingList' => $shearingList]);
    }

    public function addShearing() {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        $petId = $_REQUEST['id'] ?? null;

        if (!$petId || !$userId) {
            header("Location: /pets");
            exit;
        }

        $pet = $this->petRepository->getPetById((int)$petId);
        if (!$pet || $pet['user_id'] !== $userId) {
            return $this->render('404');
        }

        if ($this->isPost()) {
            $this->petRepository->addPetShearing((int)$petId, $_POST);
            header("Location: /shearing?id=" . $petId);
            exit;
        }

        return $this->render('care/addShearing', ['petId' => $petId]);
    }

    public function deleteShearing() {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        if (!$this->isPost() || !$userId) { header("Location: /pets"); exit; }

        $shearingId = $_POST['shearing_id'] ?? null;
        
        $entry = $this->petRepository->getShearingById((int)$shearingId);
        if (!$entry) { header("Location: /pets"); exit; }

        $pet = $this->petRepository->getPetById($entry['pet_id']);
        if ($pet && (int)$pet['user_id'] === (int)$userId) {
            $this->petRepository->deletePetShearing((int)$shearingId);
        }

        header("Location: /shearing?id=" . $entry['pet_id']);
        exit;
    }

    public function trimming() {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        $petId = $_GET['id'] ?? null;

        if (!$petId || !$userId) {
            header("Location: /pets");
            exit;
        }

        $pet = $this->petRepository->getPetById((int)$petId);
        if (!$pet || $pet['user_id'] !== $userId) {
            return $this->render('404');
        }

        // pobranie listy
        $trimmingList = $this->petRepository->getPetTrimming((int)$petId);

        return $this->render('care/trimming', ['pet' => $pet, 'trimmingList' => $trimmingList]);
    }

    public function addTrimming() {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        $petId = $_REQUEST['id'] ?? null;

        if (!$petId || !$userId) {
            header("Location: /pets");
            exit;
        }

        $pet = $this->petRepository->getPetById((int)$petId);
        if (!$pet || $pet['user_id'] !== $userId) {
            return $this->render('404');
        }

        if ($this->isPost()) {
            $this->petRepository->addPetTrimming((int)$petId, $_POST);
            header("Location: /trimming?id=" . $petId);
            exit;
        }

        return $this->render('care/addTrimming', ['petId' => $petId]);
    }

    public function deleteTrimming() {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        if (!$this->isPost() || !$userId) { header("Location: /pets"); exit; }

        $trimmingId = $_POST['trimming_id'] ?? null;
        
        $entry = $this->petRepository->getTrimmingById((int)$trimmingId);
        if (!$entry) { header("Location: /pets"); exit; }

        $pet = $this->petRepository->getPetById($entry['pet_id']);
        if ($pet && (int)$pet['user_id'] === (int)$userId) {
            $this->petRepository->deletePetTrimming((int)$trimmingId);
        }

        header("Location: /trimming?id=" . $entry['pet_id']);
        exit;
    }
}