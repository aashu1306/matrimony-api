<?php
$config = array();
define('SITE_NAME', 'Trust Index Results');

define('AMAZON_S3_API_TYPE', 'np_amazon_s3');
	

if( isset($_SERVER['HTTP_HOST']) and ($_SERVER['HTTP_HOST']=="localhost" || $_SERVER['HTTP_HOST']=="127.0.0.1" )){
    define('default_email', 'aishwarya@greatplaceitservices.com');

 }
 elseif( isset($_SERVER['HTTP_HOST']) and ($_SERVER['HTTP_HOST']=="dev-rp-chile.gpssapp.com" || $_SERVER['HTTP_HOST']=="qa-chile.gpssapp.com" )){
    define('default_email', 'qa-chile@greatplaceitservices.com');
}
elseif(isset($_SERVER['HTTP_HOST']) and ($_SERVER['HTTP_HOST']== "rp-chile.gpssapp.com" )){
 	define('default_email', 'cl_operaciones@engagementor.greatplacetowork.com');
}
?>