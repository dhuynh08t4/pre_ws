<?php
// src/Model/Entity/School.php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class School extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];
}