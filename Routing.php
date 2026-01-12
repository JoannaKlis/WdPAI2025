<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/UserController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/PetController.php';

class Routing {
    # TODO: singleton, regex, sesja użytkownika

    private static $controllerInstances = [];

    public static $routes = [
        "" => [
            "controller" => "SecurityController",
            "action" => "start"
        ],
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
            "controller" => "PetController",
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
            "controller" => "PetController",
            "action" => "healthBook"
        ],
        "nutrition" => [
            "controller" => "PetController",
            "action" => "nutrition"
        ],
        "vaccinations" => [
            "controller" => "PetController",
            "action" => "vaccinations"
        ],
        "treatments" => [
            "controller" => "PetController",
            "action" => "treatments"
        ],
        "deworming" => [
            "controller" => "PetController",
            "action" => "deworming"
        ],
        "visits" => [
            "controller" => "PetController",
            "action" => "visits"
        ],
        "weight" => [
            "controller" => "PetController",
            "action" => "weight"
        ],
        "groom" => [
            "controller" => "PetController",
            "action" => "groom"
        ],
        "shearing" => [
            "controller" => "PetController",
            "action" => "shearing"
        ],
        "trimming" => [
            "controller" => "PetController",
            "action" => "trimming"
        ],
        "addPet" => [
            "controller" => "PetController",
            "action" => "addPet"
        ],
        "addEvent" => [
            "controller" => "PetController",
            "action" => "addEvent"
        ],
        "deleteEvent" => [
            "controller" => "PetController",
            "action" => "deleteEvent"
        ],
        "addVaccination" => [
            "controller" => "PetController",
            "action" => "addVaccination"
        ],
        "deleteVaccination" => [
            "controller" => "PetController",
            "action" => "deleteVaccination"
        ],
        "addDeworming" => [
            "controller" => "PetController",
            "action" => "addDeworming"
        ],
        "deleteDeworming" => [
            "controller" => "PetController",
            "action" => "deleteDeworming"
        ],
        "addTreatment" => [
            "controller" => "PetController",
            "action" => "addTreatment"
        ],
        "deleteTreatment" => [
            "controller" => "PetController",
            "action" => "deleteTreatment"
        ],
        "addVisit" => [
            "controller" => "PetController",
            "action" => "addVisit"
        ],
        "deleteVisit" => [
            "controller" => "PetController",
            "action" => "deleteVisit"
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
            "controller" => "PetController",
            "action" => "addGroom"
        ],
        "deleteGroom" => [
            "controller" => "PetController",
            "action" => "deleteGroom"
        ],
        "addShearing" => [
            "controller" => "PetController",
            "action" => "addShearing"
        ],
        "deleteShearing" => [
            "controller" => "PetController",
            "action" => "deleteShearing"
        ],
        "addTrimming" => [
            "controller" => "PetController",
            "action" => "addTrimming"
        ],
        "deleteTrimming" => [
            "controller" => "PetController",
            "action" => "deleteTrimming"
        ],
        "addSensitivities" => [
            "controller" => "PetController",
            "action" => "addSensitivities"
        ],
        "deleteSensitivities" => [
            "controller" => "PetController",
            "action" => "deleteSensitivities"
        ],
        "addFavorite" => [
            "controller" => "PetController",
            "action" => "addFavorite"
        ],
        "deleteFavorite" => [
            "controller" => "PetController",
            "action" => "deleteFavorite"
        ],
        "addSupplements" => [
            "controller" => "PetController",
            "action" => "addSupplements"
        ],
        "deleteSupplements" => [
            "controller" => "PetController",
            "action" => "deleteSupplements"
        ],
        "editSchedule" => [
            "controller" => "PetController",
            "action" => "editSchedule"
        ],
        "deleteSchedule" => [
            "controller" => "PetController",
            "action" => "deleteSchedule"
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