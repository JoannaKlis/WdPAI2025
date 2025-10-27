<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/UserController.php';

class Routing {

    public static $routes = [
        "login" => [
            "controller" => "SecurityController",
            "action" => "login"
        ],
        "register" => [
            "controller" => "SecurityController",
            "action" => "register"
        ]
    ];


    public static function run(string $path) {
        $user_details_regex = '/^user\/(\d+)$/';
        
        if (preg_match($user_details_regex, $path, $matches)) {
            // $matches[1] będzie zawierać przechwycone ID użytkownika (np. 4578)
            $userId = $matches[1];
        
            $controllerObj = new UserController();
            $controllerObj->details($userId);
            return;
        }

        switch($path){
            case 'dashboard':
                // TODO: connect with database
                // get elements to present on dashboard

                include 'public/views/dashboard.html';
                break;
            case 'login':
            case 'register':
                $controller = Routing::$routes[$path]["controller"];
                $action = Routing::$routes[$path]["action"];

                $controllerObj = new $controller;
                $controllerObj->$action(); 
                break;
            default:
                include 'public/views/404.html';
                break;
        }
    }
}

// zagrożeniem jest że wejście do danej scieżki za każdym razem tworzy nowy obiekt kontrolera -->
// lepszym rozwiązaniem byłoby stworzenie singletona