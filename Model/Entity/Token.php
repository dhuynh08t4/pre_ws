<?php
// src/Model/Entity/Employee.php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Token extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false,
        'employee_id' => false,
        'token' => false,
        // 'position_id' => false,
        // 'role_id' => false,
        // 'address' => false,
        // 'username' => false,
        // 'username_changed' => false,
        // 'startdate' => false,
        // 'enddate' => false,
        // 'status_id' => false,
        // 'created' => time(),
        // 'updated' => time(),
    ];
}