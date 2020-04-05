<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/4/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
	 
	var $Tokens;
	var $loginEmployee;
	var $allowActions;
	var $listModels =[];
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
		$this->_loadModels('Tokens', 'Employees');
		$this->allowActions = [
			'Employees' => [
				'login',
				'applogin',
				'weblogin',
			]
		];
		// debug($this->_isAllowAction());exit;
		if( !$this->_isAllowAction()) $loginEmployee = $this->_checkToken();
        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/4/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }
	// /* DEV */


	protected function _loadModels($models=array()){
		$models = func_get_args();
		if( !empty($models)){
			foreach( $models as $model){
				if( !in_array( $model, $this->listModels)){
					$this->$model = TableRegistry::getTableLocator()->get($model);
					$this->listModels[] = $model;
				}
			}
		}
		return $models;
	}
	/* Function này thực hiện trước khi response data 
	 * Có thể dùng để ghi log 
	 */
	protected function _beforeResponse($data=array()){
		/*
		$controllerName = $this->request->getParam('controller');
		$passedArgs = $this->request->getParam('pass');
		$parameters = $this->request->getAttribute('params');
		*/
	}
	
	/* Response API Data by JSON */
	protected function _api_response($result=false, $data=array(), $message=null, $error_code = null){
		/* Khi release sẽ thêm dòng này */
		$this->_beforeResponse($data);
		// ob_clean();
		/* END Khi release sẽ thêm dòng này */
		if( !$result && !$message) $message = __('Failed to get data');
		$definedReturn = [
			'401' => [
				'result' => 'failed',
				'data' => [],
				'message' => __('401 Unauthorized'),
				'error_code' => '401',
				'header' => 'HTTP/1.0 401 Unauthorized'
			],
			'403' => [
				'result' => 'failed',
				'data' => [],
				'message' => __('403 Forbidden'),
				'error_code' => '403',
				'header' => 'HTTP/1.1 403 Forbidden'
			],
			'notvaild' => [
				'result' => 'failed',
				'data' => [],
				'message' => __('Your data submit is not vaild. Please check again'),
				'error_code' => '412'
			],
			'412' => [
				'result' => 'failed',
				'data' => [],
				'message' => __('Your data submit is not vaild. Please check again'),
				'error_code' => '412'
			],
			
		];
		if( !empty($definedReturn[$result])) {
			if( isset($definedReturn[$result]['header'])){
				header($definedReturn[$result]['header']);
				unset($definedReturn[$result]['header']);
			}
			die(json_encode($definedReturn[$result]));
		}
		$dataReturn = array(
			'result' => $result ? 'succcess' : 'failed',
			'data' => $data,
			'message' => $message,
		);
		if( !$result) $dataReturn['error_code'] = $error_code;
		die(json_encode($dataReturn));
		
		/* Enhancement 
		 * Response theo yêu cầu của client 
		  - XML
		  - JSON
		  - ...
		 */
	}
	
	protected function generateAuthCode($employee){
		// $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		// $charactersLength = strlen($characters);
		// $randomString = '';
		// for ($i = 0; $i < 10; $i++) {
			// $randomString .= $characters[rand(0, $charactersLength - 1)];
		// }
        // return md5(microtime().$randomString.$employee['id']);
		$token = bin2hex(openssl_random_pseudo_bytes(16)) . SHA1(($employee['id'] . time()));
        return $token;
	}
	protected function _isAllowAction(){
		$controllerName = $this->request->getParam('controller');
		$actionName = $this->request->getParam('action');
		return (!empty($this->allowActions[$controllerName])) && in_array($actionName, $this->allowActions[$controllerName]);
	}
	protected function _checkToken(){
		$requestHeaders = apache_request_headers();
		if( empty( $requestHeaders['Authorization'] ) ){
			$this->_api_response('401');
		}
		$token = explode(' ', $requestHeaders['Authorization'] )[1];
		$loginEmployeeID = $this->Tokens->find()->where(['token' => $token])->first();
		if( empty( $loginEmployeeID))$this->_api_response('403');
		$this->loginEmployee = $loginEmployee = $this->Employees->find()->where(['Employees.id' => $loginEmployeeID['employee_id']])->contain([
			'Schools' => [], 
			'Roles' => [],
			'EmployeePositions' => [],
		])->first();
		
		// debug( $loginEmployee ); exit;
	}
	
	/* Check permission for current user 
	 * @param: feature
	 * @param: permission, Accept : 'canManager', 'canWrite', 'canWrite'
	 * Return true/false
	 * if permission is empty, return array with 3 permission 'canManager', 'canWrite', 'canWrite'
	 */
	protected function _checkPermission($feature, $permission = null){
		if( $permission) return( true );
		return [true, true, true]; // Can manager , can write / can read
		/* If user have no permission
		 * then resonse 403 Error
		 
		 $this->_api_response('403');
		 */
	}
	
}