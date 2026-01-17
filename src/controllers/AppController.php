<?php

class AppController {
    public function __construct(){
        // start sesji raz dla wszystkich kontrolerów
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function checkAuthentication() {
        // blokada cache przeglądarki
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $timeout_duration = 1800; // 30 minut

        // sprawdzenie timeoutu (tylko jeśli sesja istnieje)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
            $this->logoutAndRedirectTo401();
        }

        // sprawdzenie czy użytkownik jest zalogowany
        if (!isset($_SESSION['user_id'])) {
            // jeśli nie ma sesji -> idź do logowania
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/login");
            exit();
        }

        // odświeżenie czasu ostatniej aktywności
        $_SESSION['last_activity'] = time();
    }

    // metoda pomocnicza: czyści sesję i kieruje na błąd 401
    private function logoutAndRedirectTo401() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/401");
        exit();
    }

    // tylko dla Admina (User -> 403)
    protected function checkAdmin() {
        $this->checkAuthentication();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/403"); 
            exit();
        }
    }

    // tylko dla Usera (Admin -> 403)
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

    // zmiana przecinka na kropkę
    protected function validateAndSanitizeFloat(string $input): ?string {
        $cleanInput = str_replace(',', '.', $input);
        
        if (!is_numeric($cleanInput) || (float)$cleanInput <= 0) {
            return null;
        }
        
        return $cleanInput;
    }

    // metoda sprawdzająca, czy żądanie to GET
    protected function isGet(): bool{
        return $_SERVER["REQUEST_METHOD"] === 'GET';
    }

    // metoda sprawdzająca, czy żądanie to POST (pod bazę danych i sprawdzanie błędów)
    protected function isPost(): bool {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    }

    protected function render(?string $template = null, array $variables = []) {
        if ($template === null) return;

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