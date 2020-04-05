<?php
// src/Model/Entity/Employee.php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Employee extends Entity
{
    protected $_accessible = [
        '*' => true,
        // 'created' => false,
        // 'updated' => false,
        // 'employee_updated' => false,
    ];
	protected $_hidden = ['password'];
}