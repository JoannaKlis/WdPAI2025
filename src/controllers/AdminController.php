<?php

require_once 'AppController.php';

class AdminController extends AppController {

    public function __construct() { parent::__construct(); }

    public function index() {
        // Weryfikacja czy użytkownik jest adminem
        $this->checkAdmin(); 

        $users = $this->userRepository->getUsers();

        return $this->render('admin/users', ['users' => $users]);
    }

    public function deleteUser() {
        $this->checkAdmin();

        if ($this->isPost()) {
            $userId = (int)$_POST['id'];
            
            // Zabezpieczenie: Admin nie może usunąć samego siebie
            if ($userId !== (int)$_SESSION['user_id']) {
                $this->userRepository->deleteUser($userId);
            }
        }

        $this->redirect('admin');
    }

    public function editUser() {
        $this->checkAdmin();

        if ($this->isPost()) {
            $id = $_POST['id'] ?? null;
            
            if ($id) {
                $this->userRepository->updateUserByAdmin(
                    (int)$id, 
                    $_POST['firstname'], 
                    $_POST['lastname'], 
                    $_POST['email'], 
                    $_POST['role']
                );
            }
        }

        $this->redirect('admin');
    }

    public function toggleBan() {
        $this->checkAdmin();

        if ($this->isPost()) {
            $userId = (int)$_POST['id'];
        
            // Admin nie może zbanować samego siebie
            if ($userId !== (int)$_SESSION['user_id']) {
                if ($this->userRepository->isUserBanned($userId)) {
                    $this->userRepository->unbanUser($userId);
                } else {
                    $this->userRepository->banUser($userId, "Banned by administrator");
                }
            }
        }

        $this->redirect('admin');
    }
}