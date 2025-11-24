<?php

require_once 'AppController.php';

class SecurityController extends AppController {

    private $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }
    // reszte odu przrobić aby korzystała z UserRepository zamiast z lokalnej tablicy

    // ======= LOKALNA "BAZA" UŻYTKOWNIKÓW ======= do testów logowania bez bazy danych
    private static array $users = [
        [
            'email' => 'anna@example.com',
            'password' => '$2y$10$VljUCkQwxrsULVbZovCaF.UfkeqVNcdz8SRFQptFS/Hr8QnUgsf5G', // test123
            'name' => 'Anna'
        ],
        [
            'email' => 'bartek@example.com',
            'password' => '$2y$10$fK9rLobZK2C6rJq6B/9I6u6Udaez9CaRu7eC/0zT3pGq5piVDsElW', // haslo456
            'name' => 'Bartek'
        ],
        [
            'email' => 'celina@example.com',
            'password' => '$2y$10$Cq1J6YMGzRKR6XzTb3fDF.6sC6CShm8kFgEv7jJdtyWkhC1GuazJa', // qwerty
            'name' => 'Celina'
        ],
    ];


    public function login() {
        // jeśli GET to wyświetl stronę logowania
        if (!$this->isPost()) {
            return $this->render("login");
        }

        // pobranie danych z formularza
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // walidacja pustych pól
        if (empty($email) || empty($password)) {
            return $this->render("login", ["messages" => "Incorrect email or password!"]);
        }

        //TODO replace with search from database
        $userRow = null;
        foreach (self::$users as $u) {
            if (strcasecmp($u['email'], $email) === 0) {
                $userRow = $u;
                break;
            }
        }

        if (!$userRow) {
            return $this->render('login', ['messages' => 'User not found']);
        }

        if (!password_verify($password, $userRow['password'])) {
            return $this->render('login', ['messages' => 'Wrong password']);
        }

        // TODO możemy przechowywać sesje użytkowika lub token
        // setcookie("username", $userRow['email'], time() + 3600, '/');


        // // tymczasowy komunikat dopóki nie ma bazy:
        // return $this->render("dashboard", ["cards" => []]); lepsze rozwiazanie poniżej
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}\dashboard");
    }


    public function registration() {
        if (!$this->isPost()) {
            return $this->render("registration");
        }

        // pobranie danych do rejestracji
        $name = $_POST['name'] ?? null;
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;
        $confirmedPassword = $_POST['confirmedPassword'] ?? null;

        // walidacja pustych pól (wszystkie muszą być wypełnione)
        if (empty($name) || empty($email) || empty($password) || empty($confirmedPassword)) {
            return $this->render("registration", ["messages" => "Enter data designation in all fields!"]);
        }
        
        // walidacja zgodności haseł
        if ($password !== $confirmedPassword) {
            return $this->render("registration", ["messages" => "The passwords are not identical!"]);
        }

        // TODO this will be checked in database
        foreach (self::$users as $u) {
            if (strcasecmp($u['email'], $email) === 0) {
                return $this->render('registration', ['messages' => 'Email is taken']);
            }
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        self::$users[] = [
            'email' => $email,
            'password' => $hashedPassword,
            'name' => $name
        ];


        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}\login");
    }
}
