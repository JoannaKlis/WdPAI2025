<?php

require_once 'AppController.php';

class SecurityController extends AppController{


    public function login() {

        // TODO: zwróć HTML logowania, przetwórz dane
        return $this->render("login", ["message" => "Błędne hasło"]);
    }

    public function register() {

        return $this->render("login", ["message" => "Błędne hasło"]);
    }
}