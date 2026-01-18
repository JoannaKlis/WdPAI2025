<?php

class AppController {
    public function __construct() {}

    protected function checkAuthentication() {
        // Blokada cache przeglądarki
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $timeout_duration = 900; // 15 minut

        // Sprawdzenie timeoutu (tylko jeśli sesja istnieje)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
            $this->logoutAndRedirect('401');
        }

        // Sprawdzenie czy użytkownik jest zalogowany
        if (!isset($_SESSION['user_id'])) {
            // Jeśli nie ma sesji -> idź do logowania
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/login");
            exit();
        }

        // Odświeżenie czasu ostatniej aktywności
        $_SESSION['last_activity'] = time();
    }

    // Metoda pomocnicza: czyści sesję i kieruje na błąd 401
    private function logoutAndRedirect(string $errorCode) {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/{$errorCode}");
        exit();
    }

    // Tylko dla Admina (User -> 403)
    protected function checkAdmin() {
        $this->checkAuthentication();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            // Brak uprawnień -> wyloguj i błąd 403
            $this->logoutAndRedirect('403');
        }
    }

    // Tylko dla Usera (Admin -> 403)
    protected function checkUser() {
        // Sprawdzenie czy zalogowany (i czy sesja nie wygasła)
        $this->checkAuthentication();

        // Jeśli rola to 'admin' -> błąd 403
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            // Zła rola -> wyloguj i błąd 403
            $this->logoutAndRedirect('403');
        }
    }

    // Zmiana przecinka na kropkę
    protected function validateAndSanitizeFloat(string $input): ?string {
        $cleanInput = str_replace(',', '.', $input);
        
        if (!is_numeric($cleanInput) || (float)$cleanInput <= 0) {
            return null;
        }
        
        return $cleanInput;
    }

    // Metoda sprawdzająca, czy żądanie to GET
    protected function isGet(): bool{
        return $_SERVER["REQUEST_METHOD"] === 'GET';
    }

    // Metoda sprawdzająca, czy żądanie to POST (pod bazę danych i sprawdzanie błędów)
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