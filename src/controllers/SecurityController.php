<?php

require_once 'AppController.php';

class SecurityController extends AppController {

    private $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function login() {
        // jeśli GET to wyświetl stronę logowania
        if (!$this->isPost()) {
            return $this->render("auth/login");
        }

        // pobranie danych z formularza
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->userRepository->getUserByEmail($email);

        // walidacja maila i hasła
        if (!$user || !password_verify($password, $user['password'])) {
            return $this->render('auth/login', ['messages' => 'Incorrect email or password!']);
        }

        //TODO: create user session, cookie etc.
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/welcome");
    }


    public function registration() {
        if($this->isGet()) {
            return $this->render("auth/registration");
        }

        // pobranie danych z formularza
        $firstname = $_POST['firstName'] ?? '';
        $lastname = $_POST['lastName'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmedPassword = $_POST['confirmedPassword'] ?? '';

        // TODO: dodać walidację danych na first i last name aby nie zawierały niepożądanych znaków

        // walidacja formatu email (musi zawierać @ i domenę po .)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->render('auth/registration', ['messages' => 'Email address is incorrect!']);
        }

        // walidacja zgodności haseł
        if ($password !== $confirmedPassword) {
            return $this->render('auth/registration', ['messages' => 'Passwords should be the same!']);
        }

        // funkcja pomocnicza do walidacji hasła
        // $isValidPassword = function (string $password): bool {
        //     if (strlen($password) < 6) return false; // min. 6 znaków
        //     if (!preg_match('/[A-Z]/', $password)) return false; // min. 1 duża litera
        //     if (!preg_match('/[0-9]/', $password)) return false; // min. 1 cyfra
        //     if (!preg_match('/[^A-Za-z0-9]/', $password)) return false; // min. 1 znak specjalny
        //     return true;
        // };

        // walidacja siły hasła i unikalności emaila (do poprawy w przyszłości)
        // if (!$isValidPassword($password || $this->userRepository->getUserByEmail($email))) {
        //     return $this->render('auth/registration', ['messages' => 'Inccorect email or
        //     Password must be at least 6 characters long, 1 uppercase letter, 1 number and 1 special character.']);
        // }   

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // dodanie użytkownika do bazy
    $this->userRepository->createUser(
        $firstname,
        $lastname,
        $email,
        $hashedPassword
    );
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/login?registered=true");
    }

    public function profile() {
        return $this->render("profile/profile");
    }

    public function pets() {
        return $this->render(template: "pets/pets");
    }

    public function calendar() {
        return $this->render("main/calendar");
    }

    public function welcome() {
        return $this->render("main/welcome");
    }

    public function features() {
        return $this->render("pets/features");
    }

    public function editPet() {
        return $this->render("pets/editPet");
    }

    public function care() {
        return $this->render("care/care");
    }

    public function healthBook() {
        return $this->render("healthbook/healthBook");
    }

    public function nutrition() {
        return $this->render("nutrition/nutrition");
    }

    public function vaccinations() {
        return $this->render("healthbook/vaccinations");
    }

    public function treatments() {
        return $this->render("healthbook/treatments");
    }

    public function deworming() {
        return $this->render("healthbook/deworming");
    }

    public function visits() {
        return $this->render("healthbook/visits");
    }

    public function weight() {
        return $this->render("care/weight");
    }

    public function groom() {
        return $this->render("care/groom");
    }

    public function shearing() {
        return $this->render("care/shearing");
    }

    public function trimming() {
        return $this->render("care/trimming");
    }

    public function addPet() {
        return $this->render("pets/addPet");
    }

    public function addEvent() {
        return $this->render("main/addEvent");
    }

    public function addVaccination() {
        return $this->render("healthbook/addVaccination");
    }

    public function addDeworming() {
        return $this->render("healthbook/addDeworming");
    }

    public function addTreatment() {
        return $this->render("healthbook/addTreatment");
    }

    public function addVisit() {
        return $this->render("healthbook/addVisit");
    }

    public function addWeight() {
        return $this->render("care/addWeight");
    }

    public function addGroom() {
        return $this->render("care/addGroom");
    }

    public function addShearing() {
        return $this->render("care/addShearing");
    }

    public function addTrimming() {
        return $this->render("care/addTrimming");
    }

    public function addNutrition() {
        return $this->render("nutrition/addNutrition");
    }

    public function addSensitivities() {
        return $this->render("nutrition/addSensitivities");
    }

    public function addFavorite() {
        return $this->render("nutrition/addFavorite");
    }

    public function addSupplements() {
        return $this->render("nutrition/addSupplements");
    }

    public function editSchedule() {
        return $this->render("nutrition/editSchedule");
    }
}