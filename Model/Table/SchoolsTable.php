<?php
// src/Model/Table/SchoolsTable.php
namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class SchoolsTable extends Table
{
    public function initialize(array $config): void
    {
        $this->addBehavior('Timestamp');
		$this->addAssociations([
           'hasMany' => [
               'Employees' => [
					'className' => 'Employees',
					'foreignKey' => 'school_id'
				],
				// 'Roles' => [
					// 'className' => 'Roles',
					// 'foreignKey' => 'schools_id'
				// ],
               'EmployeePositions' => [
					'className' => 'EmployeePositions',
					'foreignKey' => 'school_id'
				],
               'SchoolClasses' => [
					'className' => 'SchoolClasses',
					'foreignKey' => 'school_id'
				],
               'SchoolServices' => [
					'className' => 'SchoolServices',
					'foreignKey' => 'school_id'
				],
            ],
			'belongsTo' => [
               'SchoolTypes' => [
					'className' => 'SchoolTypes',
					'foreignKey' => 'school_type_id'
				]				
            ],
			
       ]);
    }
	public function getDefaultPassword($school_id = null){
		return 1;
	}
	public function getSchoolSettings($school_id ){
		// $role_id = $loginEmployee['role_id'];
		$Roles = TableRegistry::getTableLocator()->get('Roles');
		$schoolSettings = $this->find()->where([
			'Schools.id' => $school_id,
		])->contain([
			'EmployeePositions' => ['fields' => ['id','school_id','key','name']],
			'SchoolClasses' => ['fields' => ['id','school_id','name','description', 'enable']],
			'SchoolServices' => ['fields' => ['id','school_id','key','name','description']],
			'SchoolTypes' => ['fields' => ['id','name','code']],
		])->first()->toArray();	
		$roles = $Roles->find()->toArray();	
		$roles = array_map(function($r){
			return [
				'id' => $r['id'],
				'key' => $r['key'],
				'name' => $r['name'],
				'weight' => $r['weight']
			];
		}, $roles);
		$schoolSettings['roles'] = $roles;
		return $schoolSettings;
	}
	
}