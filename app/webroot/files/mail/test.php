
<?php
	$INCLUDE_DIR = "";
	$tempPath = dirname(dirname(dirname(dirname(__FILE__)))).'/tmp';
	require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/Vendor/sources/S3Component.php');

	$objS3 = new S3Component('jip_local_amazon_s3');

	echo '<pre>';
	print_r($objS3->get_bucket());
	echo '</pre>';
		
?>