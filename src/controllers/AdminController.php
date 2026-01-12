<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/UserRepository.php';

class AdminController extends AppController {
    private $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = new UserRepository();
    }

    public function index() {
        // Sprawdź czy to admin!
        $this->checkAdmin(); 

        // Pobierz wszystkich użytkowników
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

            // admin nie może edytować samego siebie
            if ($id == $_SESSION['user_id']) {
                header("Location: /admin?error=cannot_edit_self");
                exit;
            }

            if ($id) {
                $this->userRepository->updateUserByAdmin((int)$id, $firstname, $lastname, $email, $role);
            }
        }

        header("Location: /admin");
        exit;
    }
}