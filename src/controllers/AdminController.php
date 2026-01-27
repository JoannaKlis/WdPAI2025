<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/UserRepository.php';

class AdminController extends AppController {
    private $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = UserRepository::getInstance();
    }

    public function index() {
        // weryfikacja czy to na pewno admin
        $this->checkAdmin(); 

        $users = $this->userRepository->getUsers();

        return $this->render('admin/users', ['users' => $users]);
    }

    public function deleteUser() {
        $this->checkAdmin();

        if ($this->isPost()) {
            $userId = $_POST['id'];
            // Admin nie może usunąć samego siebie
            if ($userId != $_SESSION['user_id']) {
                $this->userRepository->deleteUser((int)$userId);
            }
        }

        header("Location: /admin");
    }

    public function editUser() {
        $this->checkAdmin();

        if ($this->isPost()) {
            $id = $_POST['id'] ?? null;
            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];
            $email = $_POST['email'];
            $role = $_POST['role'];

            if ($id) {
                $this->userRepository->updateUserByAdmin((int)$id, $firstname, $lastname, $email, $role);
            }
        }

        header("Location: /admin");
        exit;
    }

    public function toggleBan() {
        $this->checkAdmin();

        if ($this->isPost()) {
            $userId = (int)$_POST['id'];
        
            if ($userId != $_SESSION['user_id']) {
                if ($this->userRepository->isUserBanned($userId)) {
                    $this->userRepository->unbanUser($userId);
                } else {
                    $this->userRepository->banUser($userId, "Banned by administrator");
                }
            }
        }

        header("Location: /admin");
    }
}