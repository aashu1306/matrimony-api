<?php

App::uses('Component', 'Controller');
App::import('Vendor', 'S3', array('file' => 'sources/S3.php'));

/**
 * S3Component. Provides access to S3 Server from the Controller layer
 * using Third Party Vendor S3.
 *
 * @copyright     Great Place IT Services Pvt. Ltd.
 * @package       Cake.Controller.Component
 * @since         January 2016
 * @author        Vishal
 */
class S3Component extends Component{

	public $name = 'S3';
	public $access_folder = 'protected';
	var $bucket_name = '';
	var $expiretime = 900;
	var $AmazonS3;
	/**
	 * AWS URI
	 * @var string
	 * @acess public
	 * @static
	 */
	public static $endpoint = 's3.amazonaws.com';
	
	/**
	 * Constructer to initialize S3 Component and S3 Object
	 * @param $localPath, $remoteDir, $aclFlag
	 * @return true/false
	 * @author Vishal
	 **/
	public function __construct(ComponentCollection $collection, $settings = array()){
		$settings = array_merge((array)Configure::read('S3'), $settings);
		if (isset($settings['apiType'])) {
			$apiType = $settings['apiType'];
			unset($settings['apiType']);
		}
		$model = ClassRegistry::init('ApiUser');
		$returnArr = array();

		if($apiType == NULL || $apiType == ''){
			return $returnArr;
		}

		$apiUserData = $model->findByApiType($apiType); //debug($apiUserData);exit;

		$key = Configure::read('Security.cipherSeed'); 
		// $key = '5304767897258698418370730926441924952358918273645';
		$usernameEncrypted = $apiUserData['ApiUser']['api_username'];
		$passwordEncrypted = $apiUserData['ApiUser']['api_password'];
		$this->bucket_name = $apiUserData['ApiUser']['media_bucket_name'];

		$access_key = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($usernameEncrypted), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
		$secret_key = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($passwordEncrypted), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
		//debug($access_key); debug($secret_key);
		// Create An S3 Object
		$this->AmazonS3 = new S3($access_key, $secret_key);
		
		// Put bucket if not existed
		// $this->AmazonS3->putBucket($this->bucket_name, S3::ACL_AUTHENTICATED_READ);
	}

	/**
	 * Upload File/Object on S3
	 * @param $localPath, $remoteDir, $aclFlag
	 * @return true/false
	 * @author Vishal
	 **/
	public function upload($localPath, $remoteDir, $aclFlag = 'ACL_AUTHENTICATED_READ'){
		if (empty($localPath) || empty($remoteDir)) {
			return false;
		}

		$acl = $this->get_acl($aclFlag);

		$response = $this->AmazonS3->putObjectFile($localPath, $this->bucket_name,$this->access_folder.'/'.$remoteDir,$acl);

		return $response;
	}

	/**
	 * Delete File/Object on S3
	 * @param $filePath, $aclFlag
	 * @return true/false
	 * @author Vishal
	 **/
	public function delete($filePath, $aclFlag = 'ACL_AUTHENTICATED_READ'){
		if (empty($filePath)) {
			return false;
		}

		$acl = $this->get_acl($aclFlag);

		$response = $this->AmazonS3->deleteObject($this->bucket_name, $this->access_folder.'/'.$filePath, $acl);

		return $response;
	}

	/**
	 * Returns the public URL of a file
	 * @param $remoteDir
	 * @return string
	 * @author Vishal
	 **/
	public function get_url($remoteDir){

		if (empty($remoteDir)) {
			return '';
		}

		// As Public urls only will be accessible
		$this->access_folder = 'public';

		return $this->publicUrl($this->access_folder.'/'.$remoteDir);
	}

	/**
	 * Returns the authenticated URL of a file
	 * Note : This generated url will expire after specified seconds in $expiretime
	 * @param $remoteDir
	 * @return string
	 * @author Vishal
	 **/
	public function get_authenticated_url($remoteDir, $aclFlag = 'ACL_AUTHENTICATED_READ'){

		if (empty($remoteDir)) {
			return '';
		}

		$acl = $this->get_acl($aclFlag);
		
		return $this->AmazonS3->getAuthenticatedURL($this->bucket_name, $this->access_folder.'/'.$remoteDir, $this->expiretime);
	}

	/**
	 * Download S3 protected file to local
	 * @param $filePath, $savePath, $aclFlag
	 * @return response object
	 * @author Vishal
	 **/

	public function download($filePath, $savePath, $aclFlag = 'ACL_AUTHENTICATED_READ'){
		if (empty($filePath) || empty($savePath)) {
			return false;
		}

		$acl = $this->get_acl($aclFlag);

		$response = $this->AmazonS3->getObject($this->bucket_name,$this->access_folder.'/'.$filePath, $savePath);

		return $response;
	}

	/**
	 * Alias of get_url()
	 * @param $file, $ssl
	 * @return string
	 * @author Vishal
	 **/
	protected function publicUrl($file, $https = false, $hostBucket = false, $torrentUrl = false) {
	
		return sprintf(($https ? 'https' : 'http').'://%s/%s', ($hostBucket ? $this->bucket_name : ($torrentUrl ? $this->bucket_name.'.'.self::$endpoint : self::$endpoint.'/'.$this->bucket_name)), $file);
	}

	/**
	 * Get S3 Acl and Set access folder
	 * @param $aclFlag
	 * @return string
	 * @author Vishal
	 **/
	protected function get_acl($aclFlag = null){
		
		/*dynamic S3 ACL flags*/
		if($aclFlag == 'ACL_PUBLIC_READ') {
			$acl = S3::ACL_PUBLIC_READ;
		    $this->access_folder = 'public';
		}
		elseif($aclFlag == 'ACL_AUTHENTICATED_READ') {
			$acl = S3::ACL_AUTHENTICATED_READ;
		    $this->access_folder = 'protected';
		}
		else {
			$acl = S3::ACL_PRIVATE;
		    $this->access_folder = 'private';
		}

		return $acl;
	}

	/**
	 * Get bucket objects
	 * @param bucket name
	 * @return array of objects
	 * @author Vishal
	 **/
	public function get_bucket(){
		return $this->AmazonS3->getBucket($this->bucket_name);
	}
}
?>