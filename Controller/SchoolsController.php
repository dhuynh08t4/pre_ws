<?php
// src/Controller/SchoolsController.php

namespace App\Controller;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class SchoolsController extends AppController{
	
	public function getSchoolSettings(){
		$this->_checkPermission('employee', 'canManager');
		$loginEmployee = $this->loginEmployee;
		$school_id = $loginEmployee['school_id'];
		// $role_id = $loginEmployee['role_id'];
		$schoolSettings =  $this->Schools->getSchoolSettings($school_id);
		if( !empty($schoolSettings)) $this->_api_response(true, $schoolSettings);
		$this->_api_response('notvaild');
	}
	
}