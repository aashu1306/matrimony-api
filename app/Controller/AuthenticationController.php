<?php
App::uses('AppController', 'Controller');
use \Firebase\JWT\JWT;
/**
 * Authentication Controller
 */
class AuthenticationController extends AppController {
	var $name = 'Authentication';
	var $uses = array('User');
/**
 * Scaffold
 *
 * @var mixed
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Security->unlockedActions = array('login','process','checkUser','getToken','checkToken');
		$host = (env('HTTP_ORIGIN'))?:'http://localhost/';
		$this->response->header('Access-Control-Allow-Origin', $host);
		$this->response->header('Access-Control-Allow-Credentials', 'true');
		$this->url = Router::url('/',true);
	}
	
	public function login(){
		$response = $this->process();
		$this->set(array('response' => $response, '_serialize' => 'response'));
	}	

	/* take token and authenticate it. */
	public function process(){
		$response = array(
			'code' => '',
			'message' => '',
			'data' => ''
		);
		$access = $this->checkUser($_POST);
		if ($access['code'] != '200') {
			$this->headerStatus($access['code']);
			$response = array(
				'code' => $access['code'],
				'message' => $access['message'],
				'data' => '',
			);
		}
		if ($access['code'] == '200') {
			$this->headerStatus(200);
			$userData = $this->User->find('first', array('conditions'=>array('User.id' => $access['data'])));
			$userData['User']['token'] = $this->getToken($access['data']);
			$response = array(
				'code' => $access['code'],
				'message' => $access['message'],
				'data' => $userData
			);
		}
		return $response;
	}

	public function checkUser($details){
		$returnArr =array();
		if (!empty($details['Username']) && !empty($details['Password'])) {
			$userData = $this->User->find('first', array('conditions'=>array('User.username' => $details['Username'], 'User.password' => md5($details['Password']), 'User.status' => 1),'fields'=>array('User.id')));
			
			if (count($userData) > 0) {
				$returnArr['code'] = '200';
	       		$returnArr['message'] = 'OK';
	       		$returnArr['data'] = $userData['User']['id'];
			}else {
				$returnArr['code'] = '401';
				$returnArr['message'] = 'Unauthorized';
				$returnArr['data'] = '';
			}	
		}else {
				$returnArr['code'] = '404';
				$returnArr['message'] = 'Not Found.';
				$returnArr['data'] = '';
			}
		return $returnArr;
	}

	public function getToken($id){
		$privateKey = <<<EOD
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEAoIeRzmXp+Njke61JD3vWgwsHAXQzpl/EfzhiDwgl95+HobxJ
aM8PAQHASex2e2Q0Qg5xe5cdUGxFAPpsQfY/+GaCtUElUJ0iwtZfdPmVcGawc/4H
SZyf1AvVU5AsQYwZhgrEbXM2oyUcdH5qFpj0HdIfoiYvGPB23KmiSEIO1OrBd1Bi
ao+ynYIq9GrsZhuzC9zYsAn70znHIwtAqEVBD/tIDIhvyRY/2tR4YFwE0+JNVof9
wD8wFFwJ/LuHgyS36iZk47xj9Kwu7o61bPugHqVl0mrlCw+6ncwVfU3UJbahPRBu
org4EIBsECGQ5XJIBoHefF9TvGYHEHDNWJfIGwIDAQABAoIBAQCSy6Y/0d5lWyGF
H29CI4KEDt93KuXYbJbpp4u0J0Vg7ZdABUgz+bTEvO80KnImX/mRtld8JzH9SyTG
wjmhEChrZIJ+cXZIA4m4FgTwmRNY1+7gAxpy7DET3UZUxfBSeGUWuF3roIIEKnmc
5cTpqTEC3BVlV/mVmr93BgCKhy288QEIY0kZem9NFR7dRQLjwyJN3l4jbFulob7e
jm5ji9taC0m0ZmURybane8LG0CGleJ/tjAmi6gCZQVMmINFQC1icdGbadiFBG1V9
WqmurFKsurXb18oDrDkz7MVi8O0UBGcQKchSU61jFsZ/V8zO4sKIs/QoqXtalmh3
07t9ebCBAoGBAOqL7H8aPAURN9QLcot0I3AsNwdTJBtCSfiV9dtso6vebBMZnZLG
y9wEnTafZDWP8VzKFAiEztXh0pEdVzIjvxOMSGjx3SMadEfKlYKMX28N3xLzEASW
zqT+LPOl5iUlZnD4TbmCGwZALB/bDf45gWmGs5dlh6rHb1PhOArhT00hAoGBAK82
fAQzxMMjlPcnshefj50yma2B7lj76PYl5FyE1EH/1axM1dLMhjVSaqTJB2zBGW/9
Atbu/rYgYgAZK6AdQrJ49k9VEGvWRwNxCIm/nan1EBe1L8+D2nEHFMJhO5U/aGDp
Z0iZ9Fz0RS+01Ops7WctYL+3zuuNNwA36M5I41G7AoGBAIah6RAQjBFQj95c79RS
xyDVkITY2an4BCP4WJcqpky6sQjJtGSTTmOuFlxLZCdGyAI+UP+O1Hd7V/ZKhEnY
sQ7UgKAU7Z3/ym2HQQkd8I37xWfINBKeSmH1MPJu8UuzSzlfnqX0o/STk4B5qm+a
rMlZM++crSJ/tkzMw/Gi4XVhAoGAUGA+I+9bo+j+vSKIoC2iRAqiVOX14PwusjxP
teF5PY2PB6t3q2wHZQ6ZvV46+bjbYnQ+iTq5vfK9Ai6JxLmnjxfOZjYvgkiZ6wo/
UHGGciDpcPa9KATkgFUvQLw6CQ09ZLetmbCGWN31nxzlT2UIwvweFdTMJ2JwiLkd
IwRsw2ECgYACHE5MyY/uOHiRRB1XlF1J8KVII+W17laeufQcKfY4c8htiNtcLCCN
qB+v9gAER/T5kHB/IbwwEmNbHgAa7OAOehyyro7efQaBQuamx9hKxUccaCyp0I8T
Z57CvByNFNQ5oQOL5E5WnBLlo5LPI28bPunJddKtX+nzTlYSFTensQ==
-----END RSA PRIVATE KEY-----
EOD;

		$d = new DateTime();
		$timestamp = $d->getTimestamp();
		
		$token = array(
			"iss" => "https://".$_SERVER['HTTP_HOST']."/",
			"iat" => $timestamp,
			"nbf" => $timestamp,
			"exp"  => $timestamp + (20 * 60),
			"data" => [                  
				'userId'   => $id,
			]
		);
		
		$jwt = JWT::encode($token, $privateKey, 'RS256');
		
		return $jwt;
	}

	public function checkToken(){	
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
		if (!isset($_POST['token'])) {
			$response = array(
			'code' => '403',
			'message' => 'Forbidden',
			'data' => ''
			);
		}
		if (isset($_POST['token']) && $_POST['token'] == '') {
			$response = array(
			'code' => '403',
			'message' => 'Forbidden',
			'data' => ''
			);
		}
		if (isset($_POST['token']) && $_POST['token'] != '') {
			$decoded = JWT::decode($_POST['token'], $publicKey, array('RS256'));
			$userData = $this->User->find('first', array('conditions'=>array('User.id' => $decoded->data->userId, 'User.status' => 1),'fields'=>array('User.id')));
			if (isset($_POST['token']) && ($_POST['token'] != '') && !count($userData) > 0) {
				$response = array(
				'code' => '401',
				'message' => 'Unauthorized',
				'data' => ''
				);
			}
			$d = new DateTime();
			$timestamp = $d->getTimestamp();
			if(isset($_POST['token']) && ($_POST['token'] != '') && ($timestamp > $decoded->exp)){
				$response = array(
					'code' => '401',
					'message' => 'Token has expired.',
					'data' => '',
				);
			}
			if(isset($_POST['token']) && ($_POST['token'] != '') && !($timestamp > $decoded->exp) && (count($userData) > 0)){
				$response = array(
					'code' => '200',
					'message' => 'OK',
					'data' => array('id' => $decoded->data->userId, 'token' => $_POST['token']),
				);
			}
		}
		$this->set(array('response' => $response, '_serialize' => 'response'));
	}		
}