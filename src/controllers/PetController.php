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
}