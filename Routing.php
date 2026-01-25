<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/UserController.php';
require_once 'src/controllers/PetController.php';
require_once 'src/controllers/AdminController.php';
require_once 'src/controllers/PetEventController.php';
require_once 'src/controllers/PetHealthController.php';
require_once 'src/controllers/PetCareController.php';
require_once 'src/controllers/PetNutritionController.php';

class Routing {
    private static $instance = null;
    private $routes = [];

    private function __construct() {
        // Zarządzanie sesją
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Rejestracja tras przy inicjalizacji
        $this->registerRoutes();
    }

    public static function getInstance(): Routing {
        if (self::$instance === null) {
            self::$instance = new Routing();
        }
        return self::$instance;
    }

    public static function run(?string $path) {
        self::getInstance()->dispatch($path ?? '');
    }

    private function dispatch(string $path) {
        // jeśli ścieżka jest pusta to przenieś do login (ekran startowy)
        if ($path === '') {
            $path = 'login';
        }

        // Sprawdzenie dokładnych dopasowań
        if (array_key_exists($path, $this->routes)) {
            $this->invoke($this->routes[$path]);
            return;
        }

        // Sprawdzenie tras Regex
        foreach ($this->routes as $route => $config) {
            if (str_starts_with($route, '^')) {
                if (preg_match("#$route#", $path, $matches)) {
                    array_shift($matches);
                    $this->invoke($config, $matches);
                    return;
                }
            }
        }

        // Brak dopasowania -> 404
        $this->handleNotFound();
    }

    private function invoke(array $config, array $args = []) {
        $controllerName = $config['controller'];
        $actionName = $config['action'];

        // Tworzenie instancji kontrolera
        $object = new $controllerName();
        
        // Wywołanie akcji z argumentami (jeśli są, np. ID z regexa)
        $action = $actionName;
        $object->$action(...$args);
    }

    private function handleNotFound() {
        http_response_code(404);
        include 'public/views/errors/404.html';
        exit;
    }

    // Konfiguracja wszystkich tras
    private function registerRoutes() {
        $this->routes = [
            // SECURITY
            "login" => ["controller" => "SecurityController", "action" => "login"],
            "logout" => ["controller" => "SecurityController", "action" => "logout"],
            "registration" => ["controller" => "SecurityController", "action" => "registration"],
            "401" => ["controller" => "SecurityController", "action" => "error401"],
            "403" => ["controller" => "SecurityController", "action" => "error403"],

            // USER
            "profile" => ["controller" => "UserController", "action" => "profile"],
            "welcome" => ["controller" => "UserController", "action" => "welcome"],
            "^user/(\d+)$" => ["controller" => "UserController", "action" => "details"],

            // ADMIN
            "admin" => ["controller" => "AdminController", "action" => "index"],
            "editUser" => ["controller" => "AdminController", "action" => "editUser"],
            "deleteUser" => ["controller" => "AdminController", "action" => "deleteUser"],

            // PETS
            "pets" => ["controller" => "PetController", "action" => "pets"],
            "features" => ["controller" => "PetController", "action" => "features"],
            "addPet" => ["controller" => "PetController", "action" => "addPet"],
            "editPet" => ["controller" => "PetController", "action" => "editPet"],
            "deletePet" => ["controller" => "PetController", "action" => "deletePet"],

            // PET CARE
            "care" => ["controller" => "PetCareController", "action" => "care"],
            "weight" => ["controller" => "PetCareController", "action" => "weight"],
            "addWeight" => ["controller" => "PetCareController", "action" => "addWeight"],
            "deleteWeight" => ["controller" => "PetCareController", "action" => "deleteWeight"],
            "groom" => ["controller" => "PetCareController", "action" => "groom"],
            "addGroom" => ["controller" => "PetCareController", "action" => "addGroom"],
            "deleteGroom" => ["controller" => "PetCareController", "action" => "deleteGroom"],
            "shearing" => ["controller" => "PetCareController", "action" => "shearing"],
            "addShearing" => ["controller" => "PetCareController", "action" => "addShearing"],
            "deleteShearing" => ["controller" => "PetCareController", "action" => "deleteShearing"],
            "trimming" => ["controller" => "PetCareController", "action" => "trimming"],
            "addTrimming" => ["controller" => "PetCareController", "action" => "addTrimming"],
            "deleteTrimming" => ["controller" => "PetCareController", "action" => "deleteTrimming"],

            // PET HEALTH
            "healthBook" => ["controller" => "PetHealthController", "action" => "healthBook"],
            "vaccinations" => ["controller" => "PetHealthController", "action" => "vaccinations"],
            "addVaccination" => ["controller" => "PetHealthController", "action" => "addVaccination"],
            "deleteVaccination" => ["controller" => "PetHealthController", "action" => "deleteVaccination"],
            "treatments" => ["controller" => "PetHealthController", "action" => "treatments"],
            "addTreatment" => ["controller" => "PetHealthController", "action" => "addTreatment"],
            "deleteTreatment" => ["controller" => "PetHealthController", "action" => "deleteTreatment"],
            "deworming" => ["controller" => "PetHealthController", "action" => "deworming"],
            "addDeworming" => ["controller" => "PetHealthController", "action" => "addDeworming"],
            "deleteDeworming" => ["controller" => "PetHealthController", "action" => "deleteDeworming"],
            "visits" => ["controller" => "PetHealthController", "action" => "visits"],
            "addVisit" => ["controller" => "PetHealthController", "action" => "addVisit"],
            "deleteVisit" => ["controller" => "PetHealthController", "action" => "deleteVisit"],

            // PET NUTRITION
            "nutrition" => ["controller" => "PetNutritionController", "action" => "nutrition"],
            "addSensitivities" => ["controller" => "PetNutritionController", "action" => "addSensitivities"],
            "deleteSensitivities" => ["controller" => "PetNutritionController", "action" => "deleteSensitivities"],
            "addFavorite" => ["controller" => "PetNutritionController", "action" => "addFavorite"],
            "deleteFavorite" => ["controller" => "PetNutritionController", "action" => "deleteFavorite"],
            "addSupplements" => ["controller" => "PetNutritionController", "action" => "addSupplements"],
            "deleteSupplements" => ["controller" => "PetNutritionController", "action" => "deleteSupplements"],
            "editSchedule" => ["controller" => "PetNutritionController", "action" => "editSchedule"],
            "deleteSchedule" => ["controller" => "PetNutritionController", "action" => "deleteSchedule"],

            // EVENTS
            "calendar" => ["controller" => "PetEventController", "action" => "calendar"],
            "addEvent" => ["controller" => "PetEventController", "action" => "addEvent"],
            "deleteEvent" => ["controller" => "PetEventController", "action" => "deleteEvent"],
        ];
    }
}