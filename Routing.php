<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/UserController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/PetController.php';

class Routing {
    # TODO: singleton, regex, sesja użytkownika

    private static $controllerInstances = [];

    public static $routes = [
        "login" => [
            "controller" => "SecurityController",
            "action" => "login"
        ],
        "logout" => [
            "controller" => "SecurityController",
            "action" => "logout"
        ],
        "registration" => [
            "controller" => "SecurityController",
            "action" => "registration"
        ],
        "dashboard" => [
            "controller" => "DashboardController",
            "action" => "index"
        ],
        "search-cards" => [
            "controller" => "DashboardController",
            "action" => "search"
        ],
        "profile" => [
            "controller" => "SecurityController",
            "action" => "profile"
        ],
        "pets" => [
            "controller" => "PetController",
            "action" => "pets"
        ],
        "calendar" => [
            "controller" => "SecurityController",
            "action" => "calendar"
        ],
        "welcome" => [
            "controller" => "SecurityController",
            "action" => "welcome"
        ],
        "features" => [
            "controller" => "PetController",
            "action" => "features"
        ],
        "editPet" => [
            "controller" => "PetController",
            "action" => "editPet"
        ],
        "deletePet" => [
            "controller" => "PetController",
            "action" => "deletePet"
        ],
        "care" => [
            "controller" => "PetController",
            "action" => "care"
        ],
        "healthBook" => [
            "controller" => "SecurityController",
            "action" => "healthBook"
        ],
        "nutrition" => [
            "controller" => "SecurityController",
            "action" => "nutrition"
        ],
        "vaccinations" => [
            "controller" => "SecurityController",
            "action" => "vaccinations"
        ],
        "treatments" => [
            "controller" => "SecurityController",
            "action" => "treatments"
        ],
        "deworming" => [
            "controller" => "SecurityController",
            "action" => "deworming"
        ],
        "visits" => [
            "controller" => "SecurityController",
            "action" => "visits"
        ],
        "weight" => [
            "controller" => "PetController",
            "action" => "weight"
        ],
        "groom" => [
            "controller" => "SecurityController",
            "action" => "groom"
        ],
        "shearing" => [
            "controller" => "SecurityController",
            "action" => "shearing"
        ],
        "trimming" => [
            "controller" => "SecurityController",
            "action" => "trimming"
        ],
        "addPet" => [
            "controller" => "PetController",
            "action" => "addPet"
        ],
        "addEvent" => [
            "controller" => "SecurityController",
            "action" => "addEvent"
        ],
        "addVaccination" => [
            "controller" => "SecurityController",
            "action" => "addVaccination"
        ],
        "addDeworming" => [
            "controller" => "SecurityController",
            "action" => "addDeworming"
        ],
        "addTreatment" => [
            "controller" => "SecurityController",
            "action" => "addTreatment"
        ],
        "addVisit" => [
            "controller" => "SecurityController",
            "action" => "addVisit"
        ],
        "addWeight" => [
            "controller" => "PetController",
            "action" => "addWeight"
        ],
        "deleteWeight" => [
            "controller" => "PetController",
            "action" => "deleteWeight"
        ],
        "addGroom" => [
            "controller" => "SecurityController",
            "action" => "addGroom"
        ],
        "addShearing" => [
            "controller" => "SecurityController",
            "action" => "addShearing"
        ],
        "addTrimming" => [
            "controller" => "SecurityController",
            "action" => "addTrimming"
        ],
        "addNutrition" => [
            "controller" => "SecurityController",
            "action" => "addNutrition"
        ],
        "addSensitivities" => [
            "controller" => "SecurityController",
            "action" => "addSensitivities"
        ],
        "addFavorite" => [
            "controller" => "SecurityController",
            "action" => "addFavorite"
        ],
        "addSupplements" => [
            "controller" => "SecurityController",
            "action" => "addSupplements"
        ],
        "editSchedule" => [
            "controller" => "SecurityController",
            "action" => "editSchedule"
        ]
    ];

    private static function getControllerInstance(string $controllerClass) {
        if (isset(self::$controllerInstances[$controllerClass])) {
            return self::$controllerInstances[$controllerClass];
        }
        
        // Klasa kontrolera jest ładowana przez require_once na początku
        $instance = new $controllerClass();
        self::$controllerInstances[$controllerClass] = $instance;

        return $instance;
    }


    public static function run(string $path) {
        $dashboard_details_regex = '/^dashboard(?:\/(\d+))?$/';
        $user_details_regex = '/^user\/(\d+)$/';
        
        if (preg_match($dashboard_details_regex, $path, $matches)) {
            // $matches[1] będzie zawierać przechwycone ID lub będzie puste/null
            $id = $matches[1] ?? null;
        
            $controllerObj = self::getControllerInstance("DashboardController");
            $controllerObj->index($id);
            return;
        }

        if (preg_match($user_details_regex, $path, $matches)) {
            // $matches[1] będzie zawierać przechwycone ID użytkownika (np. 4578)
            $userId = $matches[1];
        
            $controllerObj = self::getControllerInstance("UserController");
            $controllerObj->details($userId);
            return;
        }


        // obsługa ścieżek
        if (isset(self::$routes[$path])) {
            $controller = self::$routes[$path]["controller"];
            $action = self::$routes[$path]["action"];

            $controllerObj = self::getControllerInstance($controller);
            $controllerObj->$action(); 
            return;
        }

        include 'public/views/404.html';
    }
}