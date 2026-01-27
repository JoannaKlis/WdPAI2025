<?php

require_once 'AppController.php';

class SecurityController extends AppController {

    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 300; // 5 minut

    public function __construct() { parent::__construct(); }

    public function start() {
        return $this->login();
    }

    // BŁĘDY
    public function error401() {
        http_response_code(401);
        return $this->render("errors/401");
    }

    public function error403() {
        http_response_code(403);
        return $this->render("errors/403");
    }

    // LOGOWANIE
    public function login() {
        if (!$this->isPost()) {
            return $this->render("auth/login");
        }

        header('Content-Type: application/json');

        // Sprawdzenie blokady czasowej
        if (isset($_SESSION['lockout_until']) && $_SESSION['lockout_until'] > time()) {
            $minutes = ceil(($_SESSION['lockout_until'] - time()) / 60);
            echo json_encode([
                'success' => false, 
                'message' => "Too many failed attempts. Try again in $minutes minute(s)."
            ]);
            exit();
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $user = $this->userRepository->getUserByEmail($email);

        // Walidacja logowania
        if (!$user || !password_verify($password, $user['password'])) {
            $this->registerFailedAttempt();
            echo json_encode(['success' => false, 'message' => 'Incorrect email or password!']);
            exit();
        }

        // Sprawdzenie bana
        if ($this->userRepository->isUserBanned($user['id'])) {
            echo json_encode(['success' => false, 'message' => 'Your account has been banned!']);
            exit();
        }

        // Sukces logowania - reset prób i ustawienie sesji
        unset($_SESSION['login_attempts'], $_SESSION['lockout_until']);

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        $url = ($user['role'] === 'admin') ? '/admin' : '/welcome';
        echo json_encode(['success' => true, 'redirect' => $url]);
        exit();
    }

    private function registerFailedAttempt() {
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;

        if ($_SESSION['login_attempts'] >= self::MAX_LOGIN_ATTEMPTS) {
            $_SESSION['lockout_until'] = time() + self::LOCKOUT_TIME;
        }
    }

    // WYLOGOWANIE
    public function logout() {
        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
        $this->redirect('login');
        exit();
    }

    // REJESTRACJA
    public function registration() {
        if($this->isGet()) {
            return $this->render("auth/registration");
        }

        header('Content-Type: application/json');

        if (!isset($_POST['privacyPolicy'])) {
            echo json_encode(['success' => false, 'message' => 'You must accept the Privacy Policy!']);
            exit();
        }

        $firstname = $_POST['firstName'] ?? '';
        $lastname = $_POST['lastName'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmedPassword = $_POST['confirmedPassword'] ?? '';

        // Walidacja imienia i nazwiska (Regex)
        if (!preg_match('/^[a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ]+$/u', $firstname) || 
            !preg_match('/^[a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ]+$/u', $lastname)) {
            echo json_encode(['success' => false, 'message' => 'First and last name must contain only letters!']);
            exit();
        }

        $genericEmailError = 'Email address is incorrect!';

        // Walidacja formatu i unikalności emaila
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $this->userRepository->getUserByEmail($email)) {
            echo json_encode(['success' => false, 'message' => $genericEmailError]);
            exit();
        }

        // Walidacja hasła
        if (!$this->isValidPassword($password)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Password must be at least 13 characters long, contain 1 uppercase letter, 1 number and 1 special character.'
            ]);
            exit();
        }

        if ($password !== $confirmedPassword) {
            echo json_encode(['success' => false, 'message' => 'Passwords should be the same!']);
            exit();
        }

        $this->userRepository->createUser($firstname, $lastname, $email, password_hash($password, PASSWORD_BCRYPT));

        echo json_encode(['success' => true, 'redirect' => '/login?registered=true']);
        exit();
    }

    private function isValidPassword(string $password): bool {
        return strlen($password) >= 13 && 
               preg_match('/[A-Z]/', $password) && 
               preg_match('/[0-9]/', $password) && 
               preg_match('/[^A-Za-z0-9]/', $password);
    }
}