<?php

require_once 'AppController.php';

class UserController extends AppController {

    public function __construct() { parent::__construct(); }

    public function profile() {
        $this->checkUser();
        $user = $this->getCurrentUser();

        if ($this->isPost()) {
            $userId = $_SESSION['user_id'];
            $pictureUrl = $this->handleImageUpload();

            try {
                $this->userRepository->updateUser(
                    $userId,
                    $_POST['firstName'],
                    $_POST['lastName'],
                    $pictureUrl
                );

                $this->redirectWithQuery('profile', ['updated' => 'true']);
            } catch (Exception $e) {
                return $this->render("profile/profile", [
                    'user' => $user,
                    'messages' => 'An unexpected error occurred while updating your profile.'
                ]);
            }
        }
        
        return $this->render("profile/profile", ['user' => $user]);
    }

    public function welcome() {
        $this->checkUser();
        return $this->render("main/welcome", ['user' => $this->getCurrentUser()]);
    }
}