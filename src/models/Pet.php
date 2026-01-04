<?php

class Pet {
    private $name;
    private $type;
    private $birthDate;
    private $breed;
    private $color;
    private $microchip;
    private $sex;

    public function __construct($name, $type, $birthDate, $breed, $color, $microchip, $sex) {
        $this->name = $name;
        $this->type = $type;
        $this->birthDate = $birthDate;
        $this->breed = $breed;
        $this->color = $color;
        $this->microchip = $microchip;
        $this->sex = $sex;
    }

    public function getName() { return $this->name; }
    public function getType() { return $this->type; }
    public function getBirthDate() { return $this->birthDate; }
    public function getBreed() { return $this->breed; }
    public function getColor() { return $this->color; }
    public function getMicrochip() { return $this->microchip; }
    public function getSex() { return $this->sex; }
}