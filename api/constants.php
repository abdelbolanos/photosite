<?php
ini_set('memory_limit','128M');
/*DATABASE*/
$DBHOST = 'mysql';
$DBNAME = 'photosite';
$DBUSER = 'root';
$DBPASSWORD = 'secret';

/*EMAILS ACCOUNTS*/
global $SUPPORT_EMAIL;
$SUPPORT_EMAIL = 'support@photosite.com';
global $SUPPORT_EMAIL_TITLE;
$SUPPORT_EMAIL_TITLE = 'Photosite';

/*ALIAS - if you are working on a virtual host leave it in blank*/
global $ALIAS;
$ALIAS = '';

/*PAYPAL CONFIGURATION*/
global $SET_PAYPAL_TEST;
$SET_PAYPAL_TEST = false;

/*LIMITS*/
global $MAX_FILE_DOWNLOAD;
$MAX_FILE_DOWNLOAD = 5;

/*PATH IMAGES FOLDER*/
global $PATH_ROOT_FOLDERS;
$PATH_ROOT_FOLDERS = __DIR__.DIRECTORY_SEPARATOR;
global $PATH_ORIGINAL_FOLDERS;
$PATH_ORIGINAL_FOLDERS = 'categories'.DIRECTORY_SEPARATOR.'original';
global $PATH_RESIZED_FOLDERS;
$PATH_RESIZED_FOLDERS = 'categories'.DIRECTORY_SEPARATOR.'resized';

/*DB*/
global $DB_PDO;
try{
	//$DB_PDO = new PDO("mysql:host=affordablephotos.db.11294321.hostedresource.com;dbname=$DBNAME", $DBUSER, $DBPASSWORD);
        $DB_PDO = new PDO("mysql:host=$DBHOST;dbname=$DBNAME", $DBUSER, $DBPASSWORD);
}catch(PDOException $e){
	//send error email TODO 
	var_dump( $e->getMessage());
	error_log('PHP LOG: Error creating db connection '.__FILE__.':line('.__LINE__.')');
        die();
}

/*USERS*/
$USERS = array(
    'abdel'  => 'secret',
    'indira' => 'secret',
);

/*Watermark Image*/
global $PATH_WATERMARK_IMAGE;
$PATH_WATERMARK_IMAGE = "../images".DIRECTORY_SEPARATOR.'watermark.png';

