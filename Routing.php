<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/UserController.php';
require_once 'src/controllers/DashboardController.php';

class Routing {

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
        "profile" => [
            "controller" => "SecurityController",
            "action" => "profile"
        ],
        "pets" => [
            "controller" => "SecurityController",
            "action" => "pets"
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