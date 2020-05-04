<?php
// src/Model/Table/ClasssesTable.php
namespace App\Model\Table;

use Cake\ORM\Table;

class ClasssesTable extends Table
{
    public function initialize(array $config): void
    {
		$this->addAssociations([
           'belongsTo' => [
               'Schools' => [
					'className' => 'Schools',
					'foreignKey' => 'school_id'
				],
				'Pupils' => [
					'className' => 'Pupils',
					'foreignKey' => 'class_id'
				],
				
            ],
           'belongsToMany' => [
				'SchoolClasses' => [
					'joinTable' => 'ClassEmployeeRefers'
				]			
			]
       ]);
	}