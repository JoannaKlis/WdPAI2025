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
}