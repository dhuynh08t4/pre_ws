<?php
// src/Model/Table/ClassEmployeeRefersTable.php
namespace App\Model\Table;

use Cake\ORM\Table;

class ClassEmployeeRefersTable extends Table
{
    public function initialize(array $config): void
    {
        $this->addBehavior('Timestamp');
		/*
		$this->addAssociations([
           'belongsTo' => [
               'Employees' => [
					'className' => 'Employees',
					'foreignKey' => 'id'
				],
				
               'SchoolClasses' => [
					'className' => 'SchoolClasses',
					'foreignKey' => 'school_id'
				],
				
            ],
           // 'hasMany' => ['SchoolClass'],
           // 'belongsToMany' => [
				// 'SchoolClasses' => [
					// 'joinTable' => 'ClassEmployeeRefers'
				// ]			
			// ]
       ]);
	   */
    }
}