<?php

require_once 'AppController.php';

class SecurityController extends AppController {

    private $userRepository;
    private const MAX_LOGIN_ATTEMPTS = 5; // 5 prób
    private const LOCKOUT_TIME = 300; // 5 minut

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = UserRepository::getInstance();
    }

    public function start() {
        return $this->login();
    }

    public function error401() {
        http_response_code(401);
        return $this->render("errors/401");
    }

    public function error403() {
        http_response_code(403);
        return $this->render("errors/403");
    }

    public function login() {
        if (!$this->isPost()) {
            return $this->render("auth/login");
        }

        header('Content-Type: application/json');

        // Sprawdzenie blokady czasowej
        if (isset($_SESSION['lockout_until']) && $_SESSION['lockout_until'] > time()) {
            $remaining = $_SESSION['lockout_until'] - time();
            $minutes = ceil($remaining / 60);
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

        // Sukces logowania - reset prób i sesja
        unset($_SESSION['login_attempts']);
        unset($_SESSION['lockout_until']);

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        $url = ($user['role'] === 'admin') ? '/admin' : '/welcome';
        echo json_encode(['success' => true, 'redirect' => $url]);
        exit();
    }

    private function registerFailedAttempt() {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
        }

        $_SESSION['login_attempts']++;

        if ($_SESSION['login_attempts'] >= self::MAX_LOGIN_ATTEMPTS) {
            $_SESSION['lockout_until'] = time() + self::LOCKOUT_TIME;
        }
    }

    public function logout() {
        $_SESSION = array(); // czyszczenie danych sesji

        // usuwanie ciasteczek sesyjnych z przeglądarki
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
            );
        }

        session_destroy(); // niszczenie sesji na serwerze

        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/login");
        exit();
    }

    public function registration() {
        if($this->isGet()) {
            return $this->render("auth/registration");
        }

        header('Content-Type: application/json');

        if (!isset($_POST['privacyPolicy'])) {
            echo json_encode(['success' => false, 'message' => 'You must accept the Privacy Policy!']);
            exit();
        }

        // pobranie danych z formularza
        $firstname = $_POST['firstName'] ?? '';
        $lastname = $_POST['lastName'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmedPassword = $_POST['confirmedPassword'] ?? '';

        // sprawdzenie czy imię i nazwisko zawierają tylko litery
        if (!preg_match('/^[a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ]+$/u', $firstname)) {
            echo json_encode(['success' => false, 'message' => 'First name must contain only letters!']);
            exit();
        }

        if (!preg_match('/^[a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ]+$/u', $lastname)) {
            echo json_encode(['success' => false, 'message' => 'Last name must contain only letters!']);
            exit();
        }

        $genericErrorMessage = 'Email address is incorrect!';

        // walidacja formatu email (musi zawierać @ i domenę po .)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => $genericErrorMessage]);
            exit();
        }

        // Sprawdzenie unikalności emaila
        if ($this->userRepository->getUserByEmail($email)) {
            echo json_encode(['success' => false, 'message' => $genericErrorMessage]);
            exit();
        }

        // walidacja długości hasła
        $isValidPassword = function (string $password): bool {
            if (strlen($password) < 13) return false; // min. 13 znaków
            if (!preg_match('/[A-Z]/', $password)) return false; // min. 1 duża litera
            if (!preg_match('/[0-9]/', $password)) return false; // min. 1 cyfra
            if (!preg_match('/[^A-Za-z0-9]/', $password)) return false; // min. 1 znak specjalny
            return true;
        };

        if (!$isValidPassword($password)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Password must be at least 13 characters long, contain 1 uppercase letter, 1 number and 1 special character.'
            ]);
            exit();
        }

        // walidacja zgodności haseł
        if ($password !== $confirmedPassword) {
            echo json_encode(['success' => false, 'message' => 'Passwords should be the same!']);
            exit();
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $this->userRepository->createUser(
            $firstname,
            $lastname,
            $email,
            $hashedPassword
        );

        echo json_encode(['success' => true, 'redirect' => '/login?registered=true']);
        exit();
    }
}