<?php

require_once 'AppController.php';

class SecurityController extends AppController {

    public function login() {

        // jeśli GET to wyświetl stronę logowania
        if (!$this->isPost()) {
            return $this->render("login");
        }

        // pobranie danych z formularza
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        // walidacja pustych pól
        if (empty($email) || empty($password)) {
            return $this->render("login", ["message" => "Incorrect email or password!"]);
        }

        // tymczasowy komunikat dopóki nie ma bazy:
        return $this->render("login", ["message" => "Incorrect email or password!"]);
    }


    public function register() {
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
            return $this->render("registration", ["message" => "Enter data designation in all fields!"]);
        }
        
        // walidacja zgodności haseł
        if ($password !== $confirmedPassword) {
            return $this->render("registration", ["message" => "The passwords are not identical!"]);
        }

        // tymczasowy komunikat dopóki nie ma bazy:
        return $this->render("registration", ["message" => "Your account has been created!"]);
    }
}
