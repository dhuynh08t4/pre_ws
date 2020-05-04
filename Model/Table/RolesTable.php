<?php
// src/Model/Table/RolesTable.php
namespace App\Model\Table;

use Cake\ORM\Table;

class RolesTable extends Table
{
    public function initialize(array $config): void
    {
		$this->addAssociations([
           'hasMany' => [
               'Employees' => [
					'className' => 'Employees',
					'foreignKey' => 'employee_id'
				],				
            ],
       ]);
    }
	public function getListRole($role_id=2, $getlist = false){
		$cond = [];
		if( $role_id){
			$cond['id >='] = $role_id;
		}
		$find_type ='list'; 
		$find_options = [];
		switch($getlist){
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
		$roles = $this->find($find_type, $find_options)->where($cond)->toArray();
		if( !empty($roles)) return $roles;
		return [];
	}
}