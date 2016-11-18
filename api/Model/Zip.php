<?php

class Zip{
	public $file_zip;
	public $zip_name;
	public $zip_object;
	public $transactionId;
	public $FILE_EXIST=false;
	
	/*
	* Usage:
	* $zip = new Zip(); <-- Init
	* $zip->addFile(file-path, zip-name);
	* $zip->close(); <-- MUST DO THIS AT THE END!!!
	*/
	public function __construct($transactionId){
		global $PATH_ROOT_FOLDERS;
		global $SUPPORT_EMAIL;
		
		$this->transactionId = $transactionId;
		$this->zip_object = new ZipArchive();
		$this->zip_name = $transactionId;
		$this->file_zip = "temp".DIRECTORY_SEPARATOR.$this->zip_name.".zip";
		
		if( file_exists($PATH_ROOT_FOLDERS.$this->file_zip) ){
			//File exist, dont create a new one
			$this->FILE_EXIST = true;
		}else{
			if ($this->zip_object->open($PATH_ROOT_FOLDERS.$this->file_zip, ZipArchive::CREATE)!==TRUE) {
				$result = "---ERROR--".date('Y-m-d H:i:s')."--ZIP--".PHP_EOL;
				$result .= print_r($PATH_ROOT_FOLDERS.$this->file_zip, true).PHP_EOL;
				$result .= "---END---".PHP_EOL;
				$result .= PHP_EOL;
				$f=file_put_contents(__DIR__.DIRECTORY_SEPARATOR."ERRORS_ZIP.txt", $result, FILE_APPEND);
				$error =  'Something bad happened, please write to '.$SUPPORT_EMAIL;
				include($PATH_ROOT_FOLDERS.'templates/error.php');
				die();
			}
		}
	}
	
	public function addFile($file_path, $file_name_in_zip){
		if( $this->FILE_EXIST == false ){
			$this->zip_object->addFile($file_path, $file_name_in_zip);
		}
	}
	
	/*
	* return download object
	*/
	public function close(){
		if( $this->FILE_EXIST == false ){
			$this->zip_object->close();
			
			//Save this zip as a download object
			$download = new Download( array() );
			$download->transactionId = $this->transactionId;
			$download->file_zip = $this->file_zip;
			$download->active=1;
			$download->save();
		}else{
			//this file exist, search this download in table
			$download = new Download( array( 'transactionId' => $this->transactionId ) );
			return $download;
		}
		
		return $download;
	}
	
}