<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/PetRepository.php';
require_once __DIR__.'/../repository/UserRepository.php';

class PetController extends AppController {
    private $petRepository;
    private $userRepository;

    public function __construct() {
        parent::__construct();
        
        $this->petRepository = PetRepository::getInstance();
        $this->userRepository = UserRepository::getInstance();
    }

    public function pets() {
        $this->checkUser();
        
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
        $this->checkUser();
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            header("Location: http://$_SERVER[HTTP_HOST]/login");
            exit;
        }

        // użytkownik nie moze mieć więcej niż 50 zwierząt
        $currentPetCount = $this->petRepository->countUserPets($userId);
        if ($currentPetCount >= 50) {
            // tu w przyszłości można dodać komunikat o błędzie 
            header("Location: http://$_SERVER[HTTP_HOST]/pets?error=limit_reached");
            exit;
        }

        if (!$this->isPost()) {
            return $this->render('pets/addPet', ['pet' => ['picture_url' => null]]);
        }

        $name = $_POST['name'] ?? '';
        $microchip = $_POST['microchip'] ?? '';

        // walidacja mikroczipa (musi mieć dokładnie 15 cyfr, jeśli został podany)
        if (!empty($microchip)) {
            if (!preg_match('/^\d{15}$/', $microchip)) {
                http_response_code(422);
                return $this->render('422', ['message' => 'Microchip number must consist of exactly 15 digits.']);
            }
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
        $this->checkUser();
        $userId = $_SESSION['user_id'] ?? null;
        $petId = $_GET['id'] ?? null;

        if (!$petId || !$userId) {
            header("Location: http://$_SERVER[HTTP_HOST]/pets");
            exit;
        }

        $pet = $this->petRepository->getPetById((int)$petId);

        if (!$pet || $pet['user_id'] !== $userId) {
            return $this->render('404');
        }

        return $this->render('pets/features', ['pet' => $pet]);
    }

    public function editPet() {
        $this->checkUser();
        $userId = $_SESSION['user_id'] ?? null;
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
            $name = $_POST['name'] ?? '';
            $microchip = $_POST['microchip'] ?? '';

            if (!empty($microchip)) {
                if (!preg_match('/^\d{15}$/', $microchip)) {
                    http_response_code(422);
                    return $this->render('422', ['message' => 'Microchip number must consist of exactly 15 digits.']);
                }
            }

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
        $this->checkUser();
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

    // HELPERS
    protected function getPetOr404(mixed $petId): ?array {
        $this->checkUser(); // Sprawdza sesję
        $userId = $_SESSION['user_id'] ?? null;

        if (!$petId || !$userId) {
            header("Location: /pets");
            exit;
        }

        $pet = $this->petRepository->getPetById((int)$petId);

        // Sprawdzenie czy zwierzak istnieje i należy do użytkownika
        if (!$pet || $pet['user_id'] !== $userId) {
            $this->render('404'); // Renderuje błąd
            exit; // Zatrzymuje dalsze wykonywanie skryptu w miejscu wywołania
        }

        return $pet;
    }

    protected function handleView(object $repository, string $fetchMethod, string $viewTemplate, string $listVariableName) {
        $pet = $this->getPetOr404($_GET['id'] ?? null);
        
        // Wywołanie metody na przekazanym repozytorium
        $list = $repository->$fetchMethod((int)$pet['id']);

        return $this->render($viewTemplate, [
            'pet' => $pet, 
            $listVariableName => $list
        ]);
    }

    protected function handleAdd(object $repository, string $addMethod, string $redirectRoute, string $viewTemplate) {
        $pet = $this->getPetOr404($_REQUEST['id'] ?? null);
        $petId = (int)$pet['id'];

        if ($this->isPost()) {
            $repository->$addMethod($petId, $_POST);
            
            header("Location: " . $redirectRoute . "?id=" . $petId);
            exit;
        }

        return $this->render($viewTemplate, ['petId' => $petId]);
    }

    protected function handleDelete(object $repository, string $postKeyId, string $fetchMethod, string $deleteMethod, string $redirectUrl) {
        if (!$this->isPost()) { 
            header("Location: /pets"); 
            exit; 
        }

        $id = $_POST[$postKeyId] ?? null;
        
        $entry = $repository->$fetchMethod((int)$id);

        if ($entry) {
            $this->getPetOr404($entry['pet_id']);
            $repository->$deleteMethod((int)$id);
            header("Location: " . $redirectUrl . "?id=" . $entry['pet_id']);
            exit;
        }
        
        header("Location: /pets");
        exit;
    }
}