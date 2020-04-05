<?php
// src/Model/Table/TokensTable.php
namespace App\Model\Table;

use Cake\ORM\Table;

class TokensTable extends Table
{
    public function initialize(array $config): void
    {
        $this->addBehavior('Timestamp');
		$this->addAssociations([
           'belongsTo' => [
               'Employees' => [
					'className' => 'Employees',
					'foreignKey' => 'employee_id'
				],				
            ],
       ]);
    }
	public function getEmployeeByToken($token){
		$emp = $this->find()->where([
			'token' => $token,
			'OR' => [
				'expiration_date <' => time(),
				'expiration_date is NULL'
			]				
		])->first();
		// if( !empty($emp)) 
			// return $this->get($emp->employee_id, [
				// 'contain' => [
					// 'School' => [], 
					// 'Role' => [],
					// 'EmployeePosition' => [],
					// 'SchoolClasses' => [],
				// ]
			// ]);

		if( !empty($emp)) return $emp->employee_id;
		return false;
	}
}