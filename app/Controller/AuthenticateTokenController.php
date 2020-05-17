<?php
App::uses('AppController', 'Controller');
use \Firebase\JWT\JWT;
/**
 * AuthencateToken Controller
 */
class AuthenticateTokenController extends AppController {
	var $uses = array();

/**
 * Scaffold
 *
 * @var mixed
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Security->unlockedActions = array('process','checkUserDetail','checkTypeAndId','checkValidData','checkValidMapData');

		$host = (env('HTTP_ORIGIN'))?:'http://localhost/';
		$this->response->header('Access-Control-Allow-Origin', $host);
		$this->response->header('Access-Control-Allow-Credentials', 'true');

		$this->url = Router::url('/',true);
	}
	
/* take token and authenticate it. */
	public function process(){

		$response = array(
			'is_error' => false,
			'error_message' => '',
			'data' => ''
		);

		$access = $this->checkUserDetail($_POST);
		if ($access['IsError']) {
			$response = array(
				'is_error' => true,
				'error_message' => '',
				'data' => $access['error_message'],
			);
		}
		
		$publicKey = <<<EOD
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAoIeRzmXp+Njke61JD3vW
gwsHAXQzpl/EfzhiDwgl95+HobxJaM8PAQHASex2e2Q0Qg5xe5cdUGxFAPpsQfY/
+GaCtUElUJ0iwtZfdPmVcGawc/4HSZyf1AvVU5AsQYwZhgrEbXM2oyUcdH5qFpj0
HdIfoiYvGPB23KmiSEIO1OrBd1Biao+ynYIq9GrsZhuzC9zYsAn70znHIwtAqEVB
D/tIDIhvyRY/2tR4YFwE0+JNVof9wD8wFFwJ/LuHgyS36iZk47xj9Kwu7o61bPug
HqVl0mrlCw+6ncwVfU3UJbahPRBuorg4EIBsECGQ5XJIBoHefF9TvGYHEHDNWJfI
GwIDAQAB
-----END PUBLIC KEY-----
EOD;

		$decoded = JWT::decode($_POST['Token'], $publicKey, array('RS256'));
		
		$d = new DateTime();
		$timestamp = $d->getTimestamp();

		if($timestamp > $decoded->exp){
			$response = array(
				'is_error' => true,
				'error_message' => '',
				'data' => 'Token has expired.',
			);
		}
		$_POST['decoded'] = $decoded;
		$checked = $this->checkTypeAndId($_POST);
		if ($checked['IsError']) {
			$response = array(
				'is_error' => true,
				'error_message' => '',
				'data' => 'Invailid Data.',
			);
		}

		return $response;
	}

	function checkUserDetail($data){
		
		$redirectClass = 'Authentication';
		App::import('Controller', $redirectClass);
		$newClass = $redirectClass.'Controller';		
		$redirectClassController = new $newClass;		
		$response = $redirectClassController->checkUser($data);
		return $response;
	}

	function checkTypeAndId($data){
		$valid = $this->checkValidData($data);
		if(!$valid['IsError']){
			$validmapping = $this->checkValidMapData($data);
			return $validmapping;
		}
	}

	function checkValidData($data){
		$redirectClass = 'Authentication';
		App::import('Controller', $redirectClass);
		$newClass = $redirectClass.'Controller';		
		$redirectClassController = new $newClass;		
		$response = $redirectClassController->checkValidId($data);
		return $response;
	}
	
	function checkValidMapData($data){
		$redirectClass = 'Authentication';
		App::import('Controller', $redirectClass);
		$newClass = $redirectClass.'Controller';		
		$redirectClassController = new $newClass;		
		$response = $redirectClassController->checkValidIdWithUser($data);
		return $response;
	}
}