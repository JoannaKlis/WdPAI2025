<?php
require_once __DIR__.'/../repository/UserRepository.php';

class AppController {
    protected $userRepository;
    public function __construct() { $this->userRepository = UserRepository::getInstance(); }

    protected function checkAuthentication() {
        // Blokada cache przeglądarki
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");

        $timeout_duration = 900; // 15 minut

        // Sprawdzenie timeoutu (tylko jeśli sesja istnieje)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
            $this->logoutAndRedirect('401');
        }

        // Sprawdzenie czy użytkownik jest zalogowany
        if (!isset($_SESSION['user_id'])) {
            // Jeśli nie ma sesji -> idź do logowania
            header("Location: /login");
            exit();
        }

        // Odświeżenie czasu ostatniej aktywności
        $_SESSION['last_activity'] = time();
    }

    protected function getCurrentUser() {
        return $this->userRepository->getUserByEmail($_SESSION['user_email'] ?? '');
    }

    // Metoda pomocnicza: czyści sesję i kieruje na błąd 401
    private function logoutAndRedirect(string $errorCode) {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        header("Location: /{$errorCode}");
        exit();
    }

    // Metoda pomocnicza do zwykłych przekierowań (np. wewnątrz logiki admina)
    protected function redirect(string $errorCode) {
        header("Location: /{$errorCode}");
        exit();
    }

    // Tylko dla Admina (User -> 403)
    protected function checkAdmin() {
        $this->checkAuthentication();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            // Brak uprawnień -> wyloguj i błąd 403
            $this->redirect('403');
        }
    }

    // Tylko dla Usera (Admin -> 403)
    protected function checkUser() {
        // Sprawdzenie czy zalogowany (i czy sesja nie wygasła)
        $this->checkAuthentication();

        // Jeśli rola to 'admin' -> błąd 403
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            // Zła rola -> wyloguj i błąd 403
            $this->redirect('403');
        }
    }

    // Zmiana przecinka na kropkę
    protected function validateAndSanitizeFloat(string $input): ?string {
        $cleanInput = str_replace(',', '.', $input);
        return (!is_numeric($cleanInput) || (float)$cleanInput <= 0) ? null : $cleanInput;
    }

    // Metoda sprawdzająca, czy żądanie to GET
    protected function isGet(): bool { return $_SERVER["REQUEST_METHOD"] === 'GET'; }

    // Metoda sprawdzająca, czy żądanie to POST (pod bazę danych i sprawdzanie błędów)
    protected function isPost(): bool { return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'; }

    protected function render(?string $template = null, array $variables = []) {
        if ($template === null) return;
        $templatePath = 'public/views/' . $template . '.html';
        if (!empty($variables)) extract($variables); 
        ob_start();
        include file_exists($templatePath) ? $templatePath : 'public/views/errors/404.html';
        echo ob_get_clean();
    }

    protected function renderError(int $code, string $message = '') {
        http_response_code($code);
        
        return $this->render("errors/{$code}", [
            'errorMessage' => $message,
            'backUrl' => $_SERVER['HTTP_REFERER'] ?? '/welcome'
        ]);
    }

    protected function handleImageUpload(): ?string {
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['picture']['tmp_name'];
            
            if (is_uploaded_file($tmpName)) {
                $imageData = file_get_contents($tmpName);
                $mimeType = mime_content_type($tmpName);
                
                if (strpos($mimeType, 'image/') === 0) {
                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
            }
        }
        return null;
    }

    protected function getValidatedFloat(string $key, string $errorMessage = "Incorrect data format!"): string {
        $value = $this->validateAndSanitizeFloat($_POST[$key] ?? '');
        
        if ($value === null) {
            $this->renderError(422, $errorMessage);
            exit;
        }
        
        return $value;
    }

    protected function redirectWithId(string $path, $id) {
        header("Location: {$path}?id={$id}");
        exit;
    }

    protected function redirectWithQuery(string $path, array $params) {
        $query = http_build_query($params);
        header("Location: /{$path}?{$query}");
        exit();
    }
}