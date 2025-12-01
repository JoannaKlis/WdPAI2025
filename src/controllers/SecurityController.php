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
            return $this->render("login");
        }

        // pobranie danych z formularza
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->userRepository->getUserByEmail($email);

        // walidacja maila i hasła
        if (!$user || !password_verify($password, $user['password'])) {
            return $this->render('login', ['messages' => 'Incorrect email or password!']);
        }

        //TODO: create user session, cookie etc.
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}\dashboard");
    }


    public function registration() {
        if($this->isGet()) {
            return $this->render("registration");
        }

        // pobranie danych z formularza
        $firstname = $_POST['firstName'] ?? '';
        $lastname = $_POST['lastName'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmedPassword = $_POST['confirmedPassword'] ?? '';

        // // walidacja czy wszystkie pola są wypełnione
        // if (empty($firstname) || empty($lastname) || empty($email) || empty($password) || empty($confirmedPassword)) {
        //     return $this->render('registration', ['messages' => 'All fields are required!']);
        // }

        // // ^[a-zA-Z]+$ - tylko małe i duże litery dla imienia i nazwiska
        // if (!preg_match('/^[a-zA-Z]+$/', $firstname) || !preg_match('/^[a-zA-Z]+$/', $lastname)) {
        //     return $this->render('registration', ['messages' => 'First name and last name can only contain letters.']);
        // }

        // // normalizacja imienia i nazwiska
        // $firstname = ucfirst(strtolower($firstname));
        // $lastname = ucfirst(strtolower($lastname));

        // // walidacja formatu email (musi zawierać @ i domenę po .)
        // if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        //     return $this->render('registration', ['messages' => 'Email address is incorrect!']);
        // }

        // // walidacja unikalności e-maila
        // if ($this->userRepository->getUserByEmail($email)) {
        //     return $this->render('registration', ['messages' => 'User with this email already exists!']);
        // }

        // // walidacja zgodności haseł
        // if ($password !== $confirmedPassword) {
        //     return $this->render('registration', ['messages' => 'Passwords should be the same!']);
        // }

        // // funkcja pomocnicza do walidacji hasła
        // $isValidPassword = function (string $password): bool {
        //     if (strlen($password) < 6) return false; // min. 6 znaków
        //     if (!preg_match('/[A-Z]/', $password)) return false; // min. 1 duża litera
        //     if (!preg_match('/[0-9]/', $password)) return false; // min. 1 cyfra
        //     if (!preg_match('/[^A-Za-z0-9]/', $password)) return false; // min. 1 znak specjalny
        //     return true;
        // };

        // // walidacja siły hasła
        // if (!$isValidPassword($password)) {
        //     return $this->render('registration', ['messages' => 'Password must be at least 6 characters long, 1 uppercase letter, 1 number and 1 special character.']);
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
        exit();
    }
}