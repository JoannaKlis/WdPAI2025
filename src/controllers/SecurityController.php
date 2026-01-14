<?php

require_once 'AppController.php';

class SecurityController extends AppController {

    private $userRepository;

    public function __construct()
    {
        parent::__construct(); 
        // Singleton
        $this->userRepository = UserRepository::getInstance();
    }

    public function start() {
        return $this->render("main/start");
    }

    public function error401() {
        http_response_code(401);
        return $this->render("401");
    }

    public function error403() {
        http_response_code(403);
        return $this->render("403");
    }

    public function login() {
        // jeśli GET to wyświetl stronę logowania
        if (!$this->isPost()) {
            if(isset($_SESSION['user_id'])) {
                $url = "http://$_SERVER[HTTP_HOST]";
                if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                    header("Location: {$url}/admin");
                } else {
                    header("Location: {$url}/pets");
                }
                exit();
            }
            return $this->render("auth/login");
        }

        // pobranie danych z formularza
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->userRepository->getUserByEmail($email);

        // walidacja maila i hasła
        if (!$user || !password_verify($password, $user['password'])) {
            return $this->render('auth/login', ['messages' => 'Incorrect email or password!']);
        }

        // regeneracja ID dla bezpieczeństwa
        session_regenerate_id(true);

        // zapisanie danych użytkownika w sesji
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        $url = "http://$_SERVER[HTTP_HOST]";

        //TODO: cookie etc.
        // logika przekierowania po zalogowaniu
        if ($user['role'] === 'admin') {
            header("Location: {$url}/admin");
        } else {
            header("Location: {$url}/welcome"); 
        }
        exit();

    }

    public function logout() {
        // uruchomienie sesji, jeśli nie wystartowała w konstruktorze
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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

        if (!isset($_POST['privacyPolicy'])) {
            return $this->render('auth/registration', ['messages' => 'You must accept the Privacy Policy!']);
        }

        // pobranie danych z formularza
        $firstname = $_POST['firstName'] ?? '';
        $lastname = $_POST['lastName'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmedPassword = $_POST['confirmedPassword'] ?? '';

        // TODO: dodać walidację danych na first i last name aby nie zawierały niepożądanych znaków

        // walidacja formatu email (musi zawierać @ i domenę po .)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->render('auth/registration', ['messages' => 'Email address is incorrect!']);
        }

        // walidacja zgodności haseł
        if ($password !== $confirmedPassword) {
            return $this->render('auth/registration', ['messages' => 'Passwords should be the same!']);
        }

        // funkcja pomocnicza do walidacji hasła
        // $isValidPassword = function (string $password): bool {
        //     if (strlen($password) < 6) return false; // min. 6 znaków
        //     if (!preg_match('/[A-Z]/', $password)) return false; // min. 1 duża litera
        //     if (!preg_match('/[0-9]/', $password)) return false; // min. 1 cyfra
        //     if (!preg_match('/[^A-Za-z0-9]/', $password)) return false; // min. 1 znak specjalny
        //     return true;
        // };

        // walidacja siły hasła i unikalności emaila (do poprawy w przyszłości)
        // if (!$isValidPassword($password || $this->userRepository->getUserByEmail($email))) {
        //     return $this->render('auth/registration', ['messages' => 'Inccorect email or
        //     Password must be at least 6 characters long, 1 uppercase letter, 1 number and 1 special character.']);
        // }   

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // dodanie użytkownika do bazy
    $this->userRepository->createUser(
        $firstname,
        $lastname,
        $email,
        $hashedPassword
    );
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/login?registered=true");
    }

    public function profile() {
    $this->checkUser();
    $user = $this->userRepository->getUserByEmail($_SESSION['user_email']);

    if ($this->isPost()) {
        $pictureUrl = null;
        // obsługa uploadu zdjęcia profilowego
        if (isset($_FILES['picture']) && is_uploaded_file($_FILES['picture']['tmp_name'])) {
            $imageData = file_get_contents($_FILES['picture']['tmp_name']);
            $mimeType = mime_content_type($_FILES['picture']['tmp_name']);
            $pictureUrl = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        }

        $this->userRepository->updateUser(
            $_SESSION['user_id'],
            $_POST['firstName'],
            $_POST['lastName'],
            $_POST['email'],
            !empty($_POST['password']) ? $_POST['password'] : null,
            $pictureUrl
        );

        $_SESSION['user_email'] = $_POST['email'];
        header("Location: /profile?updated=true");
        exit();
    }
    return $this->render("profile/profile", ['user' => $user]);
    }

    public function welcome() {
        $this->checkUser();
        $user = $this->userRepository->getUserByEmail($_SESSION['user_email']);
        return $this->render("main/welcome", ['user' => $user]);
    }

    public function privacyPolicy() {
        return $this->render("main/privacy");
    }
}