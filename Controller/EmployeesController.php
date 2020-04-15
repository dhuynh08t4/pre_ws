<?php
// src/Controller/EmployeesController.php.php

namespace App\Controller;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class EmployeesController extends AppController{
	var $expiration_date = '+2 week';
	private $userInfo = array();
	public function login() {
		debug( 'Only for web. Not for app'); exit;
		$result = 'failed';
		$query = $this->Employees
			->find()
			->where(array(
				'username' => 'dhuynh08t4'
			))
			->contain('Schools');
		$employees = $query->all();
	    die(json_encode($employees));
    }
	/* Function applogin 
	 * @input: username
	 * @input: password
	 * @input: langcode, default EN
	 @return Auth_key
	 */
	public function weblogin() {
		debug( 'Only for web. Not for app'); exit;
		
	}
	public function applogin() {
		$result = 'failed';
		$message = '';
		$data = array();
		
		$request = array_merge([
			'username' => '',
			'email' => '',
			'password' => ''
		],$this->request->getData());	
		if( !empty($request)){
			$cond = array(
				'OR' => [
					'username' => $request['username'],
					'email' => $request['username']
				],
				'password' => md5($request['password'])
			);
			if( !empty( $request['id']) ){
				$cond['Employees.id'] = $request['id'];
			}
			$query = $this->Employees
			->find()
			->where($cond);
			$count = $query->count();
			if( $count >1){
				// request select 
				$data = $query->all();
				$result = 'request_action';
				$message = __('Please select your account');
			}elseif($count == 1){ // only one school
				$query ->contain([
					'Schools' => [], 
					'Roles' => [],
					'EmployeePositions' => [],
					// 'SchoolClasses' => [],
				]);
				$data = $query->first();
				unset( $data['password']);
				$token = $this->generateAuthCode($data);
				$data['token'] = $token;
				$Tokens = TableRegistry::getTableLocator()->get('Tokens');
				$lasttk = $Tokens->find()->where([
					'employee_id' => $data['id']
				])->first();
				if( empty( $lasttk)){
					$tokenEntity = $Tokens->newEmptyEntity();
					$tokenEntity = $Tokens->newEntity([
						'employee_id' => $data['id'],
						'token' => $token,
						'created' => time(),
						
					], ['validate' => false]);
				}else{
					$tokenEntity = $lasttk;
					$tokenEntity->token = $token;
				}
				
				$tokenEntity->updated = time();
				$tokenEntity->expiration_date = !empty($this->expiration_date) ? strtotime($this->expiration_date) : null;
				$tokensaved = $Tokens->save($tokenEntity);
				$result = 'success';
				$message ='';
			}else{
				$result = 'failed';
				$message = __('Your email or password is not valid. Please check again');
			}
		}else{
			$message = __('Your data submit is invalid. Please check again');
		}
		
		$this->_api_response($result, $data, $message);
		die( 'error');
    }
	public function listEmployees(){
		$this->_checkPermission('employee', 'canManager');
		$loginEmployee = $this->loginEmployee;
		$school_id = $loginEmployee['school_id'];
		$listEmployees = $this->Employees->find()->where(['Employees.school_id' => $school_id])->contain([
			// 'School' => [], 
			'Roles' => [],
			'EmployeePositions' => [],
		])->toArray();
		$this->_api_response(true, $listEmployees);
		exit;
	}
	public function getEmployees(){
		$this->_checkPermission('employee', 'canManager');
		$loginEmployee = $this->loginEmployee;
		$school_id = $loginEmployee['school_id'];
		$listEmployees = $this->Employees->find()->where(['Employees.school_id' => $school_id])->contain([
			// 'School' => [], 
			'Roles' => [],
			'EmployeePositions' => [],
		])->toArray();
		$this->_api_response(true, $listEmployees);
		exit;
	}
	public function getEmployee(){
		$this->_checkPermission('employee', 'canManager');
		$loginEmployee = $this->loginEmployee;
		$school_id = $loginEmployee['school_id'];
		$e_id = $this->request->getQuery('id');
		// debug( $e_id);exit;
		if( $e_id){
			$employee = $this->Employees->find()->where([
				'Employees.school_id' => $school_id,
				'Employees.id' => $e_id
			])->contain([
				// 'Schools' => [], 
				'Roles' => [],
				'EmployeePositions' => [],
			])->toArray();
			if( !empty($employee)) $this->_api_response(true, $employee);
		}
		$this->_api_response('notvaild');
		exit;
	}
	public function addEmployee(){
		// debug( 1); exit;
		$this->_checkPermission('employee', 'canManager');
		$this->_loadModels('Schools','Roles', 'EmployeePositions');
		$loginEmployee = $this->loginEmployee;
		$school_id = $loginEmployee['school_id'];
		$default = [
			'password' => $this->Schools->getDefaultPassword($school_id),
			'status' => 1,
			'username' => 'user'.time()
			
		];
		$data = array_merge( $default, $this->request->getData(), [
			'school_id' => $school_id,
			'created' => time(),
			'updated' => time(),
			'employee_updated' => $loginEmployee['id'],
		]);
		$data['password'] = md5($data['password']);
		$check_role_id = $this->Roles->find()->where(['id' => $data['role_id']])->count();
		$check_position_id = $this->EmployeePositions->find()->where(['id' => $data['position_id'], 'school_id' => $school_id])->count();
		$email_exists = $this->Employees->find()->where(['email' => $data['email'], 'school_id' => $school_id])->count();
		if( $email_exists ){
			$this->_api_response(0, [], __('Email exists'), '412');
		}
		$data['full_name'] = $data['last_name'] . ' ' . $data['first_name'];
		// debug( $data); exit;
		if( $check_role_id && $check_position_id && !$email_exists){
			$_employee = $this->Employees->newEntity($data);
			if ($this->Employees->save($_employee)) {
				$this->_api_response(true, $_employee);
			}
		}
		$this->_api_response('notvaild');
		// exit;
	}
	private function canDeleteEmployee($employee_id){
		// check school 
		$loginEmployee = $this->loginEmployee;
		$school_id = $loginEmployee['school_id'];
		$employee = $this->Employees->find()->where(['id' => $employee_id, 'school_id' => $school_id])->first();
		if( empty($employee)){
			return false;
		} 
		// Check Class
		$this->_loadModels('ClassEmployeeRefers');
		$hasClass = $this->ClassEmployeeRefers->find()->where(['employee_id' => $employee_id, 'school_id' => $school_id])->first();
		if( !empty($hasClass)){
			return false;
		}
		// OK
		return true;
		
	}
	public function deleteEmployee(){
		$this->_checkPermission('employee', 'canManager');
		$e_id = $this->request->getQuery('id');
		$loginEmployee = $this->loginEmployee;
		$school_id = $loginEmployee['school_id'];
		$employee = $this->Employees->find()->where(['id' => $e_id, 'school_id' => $school_id])->first();
		if( $employee && $this->canDeleteEmployee($e_id)){
			$result = $this->Employees->delete($employee);
			$this->_api_response($result, $result);
		}
		$this->_api_response('notvaild');
	}
	
	/* Input
		data = array(
			0 => array_employee_data
			1 => array_employee_data
		)
		*/
		
	public function updateFullName(){
		$this->_checkPermission('employee', 'canManager');
		$loginEmployee = $this->loginEmployee;
		$school_id = $loginEmployee['school_id'];
		$result = $this->Employees->updateFullName($school_id);
		$this->_api_response(true, $result);
	}
	public function updateEmployee(){
		$this->_checkPermission('employee', 'canManager');
		$this->_loadModels('Schools','Roles', 'EmployeePositions');
		$this->_loadModels('Schools','Roles', 'EmployeePositions');
		$loginEmployee = $this->loginEmployee;
		$school_id = $loginEmployee['school_id'];
		$employee = $this->request->getData('data');
		// debug( $employees); exit;
		if( !empty($employee)){
			$check_school = $this->Employees->find()->where(['id' => $employee, 'school_id' => $school_id])->toList();
			$check_school = Hash::contains($check_school, $list_ids);
			debug( $check_school); exit;
			// $all_roles = $this->Roles->getListRole();
			// $all_possitions = $this->EmployeePositions->getListRoleBySchool($school_id);
		}
		$this->_api_response('notvaild');	
	}
	public function updateEmployees(){
		$this->_checkPermission('employee', 'canManager');
		$this->_loadModels('Schools','Roles', 'EmployeePositions');
		$this->_loadModels('Schools','Roles', 'EmployeePositions');
		$loginEmployee = $this->loginEmployee;
		$school_id = $loginEmployee['school_id'];
		$employees = $this->request->getData('data');
		// debug( $employees); exit;
		if( !empty($employees)){
			$list_ids = Hash::extract($employees, '{n}.id');
			debug( $list_ids); exit;
			$check_school = $this->Employees->find()->where(['id' => $list_ids, 'school_id' => $school_id])->toList();
			$check_school = Hash::contains($check_school, $list_ids);
			debug( $check_school); exit;
			// $all_roles = $this->Roles->getListRole();
			// $all_possitions = $this->EmployeePositions->getListRoleBySchool($school_id);
		}
		$this->_api_response('notvaild');
		
	}
	
}