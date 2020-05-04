<?php
// src/Model/Table/EmployeePositionsTable.php
namespace App\Model\Table;

use Cake\ORM\Table;

class EmployeePositionsTable extends Table
{
    public function initialize(array $config): void
    {
        // $this->addBehavior('Timestamp');
		$this->addAssociations([
           'belongsTo' => [	
               'Schools' => [
					'className' => 'Schools',
					'foreignKey' => 'school_id'
				],				
            ],
			'hasMany' => [
				'Employees' => [
					'className' => 'Employees',
					'foreignKey' => 'position_id'
				],
			]
       ]);
    }
	public function getSchoolPositions($school_id=null, $getList = false){
		$cond = [];
		if( $school_id){
			$cond['school_id'] = $school_id;
		}
		$find_type ='list'; 
		$find_options = [];
		switch($getList){
			case false: 
				$find_type = 'all';
				break;
			case 'list': 
				$find_options = [
					'keyField' => 'key',
					'valueField' => 'name'
				];
				break;
			case 'list_id_name': 
				$find_options = [
					'keyField' => 'id',
					'valueField' => 'name'
				];
				break;
			case 'list_id':
				$find_options = [
					'keyField' => 'id',
					'valueField' => 'id'
				];
				break;
			default: 
				$find_options = [
					'keyField' => 'key',
					'valueField' => 'name'
				];
				break;	
		}
		$data = $this->find($find_type, $find_options)->where($cond)->toArray();
		if( !empty($data)) return $data;
		return [];
	}
}