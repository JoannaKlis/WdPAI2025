<?php

require_once 'PetController.php';
require_once __DIR__.'/../repository/PetNutritionRepository.php';

class PetNutritionController extends PetController {
    private $petNutritionRepository;

    public function __construct() {
        parent::__construct();
        $this->petNutritionRepository = PetNutritionRepository::getInstance();
    }

    public function nutrition() {
        // Pobranie danych zwierzaka z weryfikacją właściciela
        $pet = $this->getPetOr404($_GET['id'] ?? null);
        $petId = (int)$pet['id'];

        return $this->render('nutrition/nutrition', [
            'pet' => $pet,
            'sensitivities' => $this->petNutritionRepository->getSensitivities($petId),
            'favorites' => $this->petNutritionRepository->getFavoriteFood($petId),
            'supplements' => $this->petNutritionRepository->getSupplements($petId),
            'schedule' => $this->petNutritionRepository->getFeedingSchedule($petId)
        ]);
    }

    // SENSITIVITIES
    public function addSensitivities() {
        $pet = $this->getPetOr404($_REQUEST['id'] ?? null);
        $petId = (int)$pet['id'];

        if ($this->isPost()) {
            $currentCount = count($this->petNutritionRepository->getSensitivities($petId));
            if ($currentCount < 20) {
                $this->petNutritionRepository->addSensitivity($petId, $_POST['name']);
            }
            $this->redirectWithId('nutrition', $petId);
        }
        return $this->render('nutrition/addSensitivities', ['petId' => $petId]);
    }

    public function deleteSensitivities() {
        $this->handleDelete($this->petNutritionRepository, 'id', 'getSensitivityById', 'deleteSensitivity', 'nutrition');
    }

    // FAVORITE FOOD
    public function addFavorite() {
        $pet = $this->getPetOr404($_REQUEST['id'] ?? null);
        $petId = (int)$pet['id'];

        if ($this->isPost()) {
            $currentCount = count($this->petNutritionRepository->getFavoriteFood($petId));
            if ($currentCount < 20) {
                $this->petNutritionRepository->addFavoriteFood($petId, $_POST['name']);
            }
            $this->redirectWithId('nutrition', $petId);
        }
        return $this->render('nutrition/addFavoriteFood', ['petId' => $petId]);
    }

    public function deleteFavorite() {
        $this->handleDelete($this->petNutritionRepository, 'id', 'getFavoriteFoodById', 'deleteFavoriteFood', 'nutrition');
    }

    // SUPPLEMENTS
    public function addSupplements() {
        $pet = $this->getPetOr404($_REQUEST['id'] ?? null);
        $petId = (int)$pet['id'];

        if ($this->isPost()) {
            $currentCount = count($this->petNutritionRepository->getSupplements($petId));
            if ($currentCount < 20) {
                $this->petNutritionRepository->addSupplement($petId, $_POST['name']);
            }
            $this->redirectWithId('nutrition', $petId);
        }
        return $this->render('nutrition/addSupplements', ['petId' => $petId]);
    }

    public function deleteSupplements() {
        $this->handleDelete($this->petNutritionRepository, 'id', 'getSupplementById', 'deleteSupplement', 'nutrition');
    }

    // FEEDING SCHEDULE
    public function editSchedule() {
        $pet = $this->getPetOr404($_REQUEST['id'] ?? null);
        $petId = (int)$pet['id'];

        if ($this->isPost()) {
            $scheduleId = $_POST['schedule_id'] ?? null;
            
            if (!empty($scheduleId)) {
                $this->petNutritionRepository->updateScheduleItem((int)$scheduleId, $_POST['name'], $_POST['time']);
            } else {
                $currentCount = count($this->petNutritionRepository->getFeedingSchedule($petId));
                if ($currentCount < 8) {
                    $this->petNutritionRepository->addScheduleItem($petId, $_POST);
                }
            }
            
            $this->redirectWithId('nutrition', $petId);
        }
        return $this->render('nutrition/editSchedule', ['petId' => $petId]);
    }

    public function deleteSchedule() {
        $this->handleDelete($this->petNutritionRepository, 'id', 'getScheduleItemById', 'deleteScheduleItem', 'nutrition');
    }
}