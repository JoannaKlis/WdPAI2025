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
    # TODO: singleton, regex, sesja użytkownika

    private static $controllerInstances = [];

    public static $routes = [
        "" => [
            "controller" => "SecurityController",
            "action" => "start"
        ],
        "401" => [
            "controller" => "SecurityController",
            "action" => "error401"
        ],
        "403" => [
            "controller" => "SecurityController",
            "action" => "error403"
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
        "privacy-policy" => [
            "controller" => "UserController",
            "action" => "privacyPolicy"
        ],
        "admin" => [
            "controller" => "AdminController",
            "action" => "index"
        ],
        "editUser" => [
            "controller" => "AdminController",
            "action" => "editUser"
        ],
        "deleteUser" => [
            "controller" => "AdminController",
            "action" => "deleteUser"
        ],
        "profile" => [
            "controller" => "UserController",
            "action" => "profile"
        ],
        "pets" => [
            "controller" => "PetController",
            "action" => "pets"
        ],
        "calendar" => [
            "controller" => "PetEventController",
            "action" => "calendar"
        ],
        "welcome" => [
            "controller" => "UserController",
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
            "controller" => "PetCareController",
            "action" => "care"
        ],
        "healthBook" => [
            "controller" => "PetHealthController",
            "action" => "healthBook"
        ],
        "nutrition" => [
            "controller" => "PetNutritionController",
            "action" => "nutrition"
        ],
        "vaccinations" => [
            "controller" => "PetHealthController",
            "action" => "vaccinations"
        ],
        "treatments" => [
            "controller" => "PetHealthController",
            "action" => "treatments"
        ],
        "deworming" => [
            "controller" => "PetHealthController",
            "action" => "deworming"
        ],
        "visits" => [
            "controller" => "PetHealthController",
            "action" => "visits"
        ],
        "weight" => [
            "controller" => "PetCareController",
            "action" => "weight"
        ],
        "groom" => [
            "controller" => "PetCareController",
            "action" => "groom"
        ],
        "shearing" => [
            "controller" => "PetCareController",
            "action" => "shearing"
        ],
        "trimming" => [
            "controller" => "PetCareController",
            "action" => "trimming"
        ],
        "addPet" => [
            "controller" => "PetController",
            "action" => "addPet"
        ],
        "addEvent" => [
            "controller" => "PetEventController",
            "action" => "addEvent"
        ],
        "deleteEvent" => [
            "controller" => "PetEventController",
            "action" => "deleteEvent"
        ],
        "addVaccination" => [
            "controller" => "PetHealthController",
            "action" => "addVaccination"
        ],
        "deleteVaccination" => [
            "controller" => "PetHealthController",
            "action" => "deleteVaccination"
        ],
        "addDeworming" => [
            "controller" => "PetHealthController",
            "action" => "addDeworming"
        ],
        "deleteDeworming" => [
            "controller" => "PetHealthController",
            "action" => "deleteDeworming"
        ],
        "addTreatment" => [
            "controller" => "PetHealthController",
            "action" => "addTreatment"
        ],
        "deleteTreatment" => [
            "controller" => "PetHealthController",
            "action" => "deleteTreatment"
        ],
        "addVisit" => [
            "controller" => "PetHealthController",
            "action" => "addVisit"
        ],
        "deleteVisit" => [
            "controller" => "PetHealthController",
            "action" => "deleteVisit"
        ],
        "addWeight" => [
            "controller" => "PetCareController",
            "action" => "addWeight"
        ],
        "deleteWeight" => [
            "controller" => "PetCareController",
            "action" => "deleteWeight"
        ],
        "addGroom" => [
            "controller" => "PetCareController",
            "action" => "addGroom"
        ],
        "deleteGroom" => [
            "controller" => "PetCareController",
            "action" => "deleteGroom"
        ],
        "addShearing" => [
            "controller" => "PetCareController",
            "action" => "addShearing"
        ],
        "deleteShearing" => [
            "controller" => "PetCareController",
            "action" => "deleteShearing"
        ],
        "addTrimming" => [
            "controller" => "PetCareController",
            "action" => "addTrimming"
        ],
        "deleteTrimming" => [
            "controller" => "PetCareController",
            "action" => "deleteTrimming"
        ],
        "addSensitivities" => [
            "controller" => "PetNutritionController",
            "action" => "addSensitivities"
        ],
        "deleteSensitivities" => [
            "controller" => "PetNutritionController",
            "action" => "deleteSensitivities"
        ],
        "addFavorite" => [
            "controller" => "PetNutritionController",
            "action" => "addFavorite"
        ],
        "deleteFavorite" => [
            "controller" => "PetNutritionController",
            "action" => "deleteFavorite"
        ],
        "addSupplements" => [
            "controller" => "PetNutritionController",
            "action" => "addSupplements"
        ],
        "deleteSupplements" => [
            "controller" => "PetNutritionController",
            "action" => "deleteSupplements"
        ],
        "editSchedule" => [
            "controller" => "PetNutritionController",
            "action" => "editSchedule"
        ],
        "deleteSchedule" => [
            "controller" => "PetNutritionController",
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
        $user_details_regex = '/^user\/(\d+)$/';

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