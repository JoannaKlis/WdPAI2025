<?php

class AppController {
    public function __construct(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function checkAuthentication() {
        // zablokowanie Cache (żeby przycisk "Wstecz" nie pokazał poprzedniej strony)
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $timeout_duration = 900; // 15 minut

        // sprawdzenie Timeoutu (gdy sesja PHP jeszcze istnieje, ale czas minął)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
            $this->logoutAndRedirectToExpired(); // kieruje na 401
        }

        // sprawdzenie czy użytkownik jest zalogowany
        if (!isset($_SESSION['user_id'])) {
            
            // jeśli brak sesji, ale jest ciasteczko 'app_active' -> sesja wygasła
            if (isset($_COOKIE['app_active'])) {
                $this->logoutAndRedirectToExpired(); // kieruje na 401
            }

            // jeśli brak sesji i brak ciasteczka -> zwykłe logowanie
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/login");
            exit();
        }

        //odświeżenie czasu ostatniej aktywności i ciasteczka
        $_SESSION['last_activity'] = time();
        setcookie("app_active", "1", time() + $timeout_duration, "/"); 
    }

    private function logoutAndRedirectToExpired() {
        // sprawdzenie czy sesja jest aktywna, zanim spróbujemy ją zniszczyć
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        
        // usuwanie ciasteczka pomocniczego
        setcookie("app_active", "", time() - 3600, "/"); 
        
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/401");
        exit();
    }

    protected function checkAdmin() {
        $this->checkAuthentication();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/403"); 
            exit();
        }
    }

    protected function checkUser() {
        // sprawdzenie czy zalogowany (i czy sesja nie wygasła)
        $this->checkAuthentication();

        // jeśli rola to 'admin' -> błąd 403
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/403");
            exit();
        }
    }

    // metoda sprawdzająca, czy żądanie to GET
    protected function isGet(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === 'GET';
    }

    // metoda sprawdzająca, czy żądanie to POST (pod bazę danych i sprawdzanie błędów)
    protected function isPost(): bool {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    }

    protected function render(?string $template = null, array $variables = [])
    {
        if ($template === null) {
            return;
        }

        $templatePath = 'public/views/' . $template . '.html';
        $templatePath404 = 'public/views/404.html';

        if (!empty($variables)) {
            extract($variables); 
        }

        ob_start();

        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            include $templatePath404;
        }

        $output = ob_get_clean();

        echo $output;
    }
}