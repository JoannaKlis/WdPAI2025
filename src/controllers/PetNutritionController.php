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
        $pet = $this->getPetOr404($_GET['id'] ?? null);
        $petId = (int)$pet['id'];

        return $this->render('nutrition/nutrition', [
            'pet' => $pet,
            'sensitivities' => $this->petNutritionRepository->getSensitivities((int)$petId),
            'favorites' => $this->petNutritionRepository->getFavoriteFood((int)$petId),
            'supplements' => $this->petNutritionRepository->getSupplements((int)$petId),
            'schedule' => $this->petNutritionRepository->getFeedingSchedule((int)$petId)
        ]);
    }

    public function addSensitivities() {
        $pet = $this->getPetOr404($_REQUEST['id'] ?? null);
        if ($this->isPost()) {
            $this->petNutritionRepository->addSensitivity((int)$pet['id'], $_POST['name']);
            header("Location: /nutrition?id=" . $pet['id']);
            exit;
        }
        return $this->render('nutrition/addSensitivities', ['petId' => $pet['id']]);
    }

    public function deleteSensitivities() {
        $this->handleDelete($this->petNutritionRepository, 'id', 'getSensitivityById', 'deleteSensitivity', '/nutrition');
    }

    public function addFavorite() {
        $pet = $this->getPetOr404($_REQUEST['id'] ?? null);
        if ($this->isPost()) {
            $this->petNutritionRepository->addFavoriteFood((int)$pet['id'], $_POST['name']);
            header("Location: /nutrition?id=" . $pet['id']);
            exit;
        }
        return $this->render('nutrition/addFavorite', ['petId' => $pet['id']]);
    }

    public function deleteFavorite() {
        $this->handleDelete($this->petNutritionRepository, 'id', 'getFavoriteFoodById', 'deleteFavoriteFood', '/nutrition');
    }

    public function addSupplements() {
        $pet = $this->getPetOr404($_REQUEST['id'] ?? null);
        if ($this->isPost()) {
            $this->petNutritionRepository->addSupplement((int)$pet['id'], $_POST['name']);
            header("Location: /nutrition?id=" . $pet['id']);
            exit;
        }
        return $this->render('nutrition/addSupplements', ['petId' => $pet['id']]);
    }

    public function deleteSupplements() {
        $this->handleDelete($this->petNutritionRepository, 'id', 'getSupplementById', 'deleteSupplement', '/nutrition');
    }

    public function editSchedule() {
        $pet = $this->getPetOr404($_REQUEST['id'] ?? null);
        $petId = (int)$pet['id'];

        if ($this->isPost()) {
            $scheduleId = $_POST['schedule_id'] ?? null;
            if (!empty($scheduleId)) {
                $this->petNutritionRepository->updateScheduleItem((int)$scheduleId, $_POST['name'], $_POST['time']);
            } else {
                $this->petNutritionRepository->addScheduleItem($petId, $_POST);
            }
            header("Location: /nutrition?id=" . $petId);
            exit;
        }
        return $this->render('nutrition/editSchedule', ['petId' => $petId]);
    }

    public function deleteSchedule() {
        $this->handleDelete($this->petNutritionRepository, 'id', 'getScheduleItemById', 'deleteScheduleItem', '/nutrition');
    }
}