<?php
// src/Model/Table/EmployeesTable.php
namespace App\Model\Table;

use Cake\ORM\Table;

class EmployeesTable extends Table
{
    public function initialize(array $config): void
    {
        $this->addBehavior('Timestamp');
		$this->addAssociations([
           'belongsTo' => [
               'Schools' => [
					'className' => 'Schools',
					'foreignKey' => 'school_id'
				],
               'Roles' => [
					'className' => 'Roles',
					'foreignKey' => 'role_id'
				],
				
               'EmployeePositions' => [
					'className' => 'EmployeePositions',
					'foreignKey' => 'position_id'
				],
               'ClassEmployeeRefers' => [
					'className' => 'ClassEmployeeRefers',
					'foreignKey' => 'employee_id'
				],
				
            ],
           // 'hasMany' => ['SchoolClass'],
           // 'belongsToMany' => [
				// 'SchoolClasses' => [
					// 'joinTable' => 'ClassEmployeeRefers'
				// ]			
			// ]
       ]);
    }
	public function updateFullName($school_id = null){
		$cond = [];
		if($school_id) $cond['school_id'] = $school_id;
		$employees = $this->find()->where($cond)->toArray();
		$ok = 0;
		$fails = 0;
		foreach( $employees as &$employee){
			// if( empty( $employee['full_name'])){
				$employee['full_name'] = $employee['last_name'] . ' ' . $employee['first_name'];
				// $_employee = $this->newEntity($employee);
				if( $this->save($employee))
					$ok++;
				else $fails++;
			// }
		}
		return(['ok' => $ok, 'fails' => $fails]);
	}
}