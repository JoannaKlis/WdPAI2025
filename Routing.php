<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/UserController.php';
require_once 'src/controllers/DashboardController.php';

class Routing {
    # TODO: singleton, regex, sesja użytkownika

    private static $controllerInstances = [];

    public static $routes = [
        "login" => [
            "controller" => "SecurityController",
            "action" => "login"
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
            "controller" => "SecurityController",
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
            "controller" => "SecurityController",
            "action" => "features"
        ],
        "editPet" => [
            "controller" => "SecurityController",
            "action" => "editPet"
        ],
        "care" => [
            "controller" => "SecurityController",
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
            "controller" => "SecurityController",
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
            "controller" => "SecurityController",
            "action" => "addPet"
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
            "controller" => "SecurityController",
            "action" => "addWeight"
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
        // /dashboard/12234 -- wyciagnie nam jakis element o wskazanym ID
        $dashboard_details_regex = '/^dashboard(?:\/(\d+))?$/'; // ścieżka do "dashboard" z opcjonalnym ID
        $user_details_regex = '/^user\/(\d+)$/'; // ścieżka do "user{id}"
        
        if (preg_match($dashboard_details_regex, $path, $matches)) {
            // $matches[1] będzie zawierać przechwycone ID lub będzie puste/null
            $id = $matches[1] ?? null;
        
            $controllerObj = self::getControllerInstance("DashboardController");
            $controllerObj->index($id); // wywołanie metody z ID lub null
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