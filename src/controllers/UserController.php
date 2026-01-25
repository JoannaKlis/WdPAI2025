<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/UserRepository.php';

class UserController extends AppController {
    private $userRepository;

    public function __construct() {
        parent::__construct();
        $this->userRepository = UserRepository::getInstance();
    }

    public function profile() {
        $this->checkUser();
        $user = $this->userRepository->getUserByEmail($_SESSION['user_email']);

        if ($this->isPost()) {
            $pictureUrl = null;
            // Obsługa uploadu zdjęcia profilowego
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
}