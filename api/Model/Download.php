<?php

class Download{
	public $id=0;
	public $transactionId='';
	public $dateCreated='';
	public $dateModified='';
	public $file_zip='';
	public $attempts=0;
	public $active=0;
	public $download_php_file = 'download.php';
	
	/*
	* Usage:
	* Search for id  $download = new Download( array('id'=>$id) );
	* Search for transactionId  $download = new Download( array('transactionId'=>$transactionId) );
	* 
	* Download:
	* $download->download();
	*
	*/
	public function __construct($array_fields){
		global $DB_PDO;
        $SQL = "SELECT * FROM `download` WHERE ";
        if( !empty($array_fields) && is_array($array_fields) ){
            foreach( $array_fields as $field => $value ){
                $SQL .= ' `'.$field.'` = :'.$field;
            }
            $std = $DB_PDO->prepare($SQL);
            foreach( $array_fields as $field => $value ){
                $std->bindValue(':'.$field , $value);
            }
            $std->execute();
			if( $std->rowCount() == 1  ){
				$row = $std->fetch(PDO::FETCH_ASSOC);
				foreach( $row as $attribute => $value ){
					if( property_exists( $this, $attribute ) ){
						$this->{$attribute} = $value;
					}
				}
			}else if( $std->rowCount() > 1 ){
                throw new Exception(__CLASS__.'-> More than one found!, add more fields to find this unique item. File:'.__FILE__.' line('.__LINE__.')');
            }
        }
	}
	
	public function save(){
		global $DB_PDO;
		
	    if( $this->id == 0  ){
	        //INSERT
	        $std  = $DB_PDO->prepare("INSERT INTO `download` ( `transactionId`, `dateCreated`, `dateModified`, `file_zip`, `attempts`, `active`) VALUE ( :transactionId, :dateCreated, :dateModified, :file_zip, :attempts, :active) ");
		    $std->bindValue(':transactionId', $this->transactionId);
			$dateCreated = date("Y-m-d H:i:s");
			$std->bindValue(':dateCreated', $dateCreated);
			$std->bindValue(':dateModified', $dateCreated);
			$std->bindValue(':file_zip', $this->file_zip);
		    $std->bindValue(':attempts', $this->attempts);
		    $std->bindValue(':active', $this->active);
	        if( $std->execute() ){
	            $this->id = $DB_PDO->lastInsertId();
				$this->dateCreated = $dateCreated;
				$this->dateModified = $dateCreated;
	            return $this->id;
	        }else{
	            throw new Exception('Error: saving download debug '.print_r($std->errorInfo() ,true) );
	        }
	        
	    }else{
	        //UPDATE
	        $std  = $DB_PDO->prepare("UPDATE `download` SET `dateModified`= :dateModified, `file_zip` = :file_zip, `attempts` = :attempts, `active` = :active WHERE id =:id ");
		    $std->bindValue(':id', $this->id);
			$std->bindValue(':dateModified', date("Y-m-d H:i:s"));
			$std->bindValue(':file_zip', $this->file_zip);
		    $std->bindValue(':attempts', $this->attempts);
		    $std->bindValue(':active', $this->active);
	        if( $std->execute() ){
	            return $this->id;
	        }else{
	            throw new Exception('Error: saving download debug '.print_r($std->errorInfo() ,true) ) ;
	        }
	    }
		
	}
	
	/*
	* Get file zip name.extension
	*/
	public function getFileZipName(){
		$zip_name_parts = explode(DIRECTORY_SEPARATOR, $this->file_zip);
		return $zip_name_parts[ count($zip_name_parts) - 1 ];
	}
	
	/*
	* Return the link for download a file
	*/
	public function getDownloadLink(){
		global $ALIAS;
		return 'http://'.$_SERVER['SERVER_NAME'].$ALIAS.'/'.$this->download_php_file.'?zip='.base64_encode($this->dateCreated);
	}
	
	/*
	* Output a file, no headers must be sent before
	* $force: ignores MAX_FILE_DOWNLOAD limit, and attempts dont increase, overides active field
	*/
	public function download($force=false){
		global $SUPPORT_EMAIL;
		global $PATH_ROOT_FOLDERS;
		
		if( !empty($this->file_zip) ){
			if(file_exists( $PATH_ROOT_FOLDERS.$this->file_zip)){
				global $MAX_FILE_DOWNLOAD;
				if( $this->attempts <  $MAX_FILE_DOWNLOAD ){
					if( ($this->active == 1) || $force ){
						header("Pragma: public"); // required
						header("Expires: 0");
						header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
						header("Cache-Control: private",false); // required for certain browsers
						header("Content-Type: application/zip");
						$zip_name = $this->getFileZipName();
						header("Content-Disposition: attachment; filename=\"".$zip_name."\";" );
						header("Content-Transfer-Encoding: binary");
						header("Content-Length: ".filesize( $PATH_ROOT_FOLDERS.$this->file_zip));
						ob_clean();
						flush();
						readfile( $PATH_ROOT_FOLDERS.$this->file_zip );
						
						/*Save Attemps*/
						if( $force == false ){
							$this->attempts = $this->attempts + 1;
							$this->save();
						}
						
						die();
					}else{
						//Active
						$result = "---ATTENTION--".date('Y-m-d H:i:s')."--DOWNLOAD--".PHP_EOL;
						$result .= "---DOWNLOAD NOT ACTIVE---".PHP_EOL;
						$result .= "---TRANSACTION ID --".$this->transactionId."-".PHP_EOL;
						$result .= print_r($PATH_ROOT_FOLDERS.$this->file_zip, true).PHP_EOL;
						$result .= "---END---".PHP_EOL;
						$result .= PHP_EOL;
						$f=file_put_contents(__DIR__.DIRECTORY_SEPARATOR."ERRORS_DOWNLOAD.txt", $result, FILE_APPEND);
						$error =  'This download is not active, please write to '.$SUPPORT_EMAIL;
						include($PATH_ROOT_FOLDERS.'templates/error.php');
						die();
					}
				}else{
					//Disable because reached the number of attemps
					$this->active = 0;
					$this->save();
				
					//Limit
					$result = "---ATTENTION--".date('Y-m-d H:i:s')."--DOWNLOAD--".PHP_EOL;
					$result .= "---MAX LIMIT ATTEMPTS REACHED---".PHP_EOL;
					$result .= "---TRANSACTION ID --".$this->transactionId."-".PHP_EOL;
					$result .= print_r($PATH_ROOT_FOLDERS.$this->file_zip, true).PHP_EOL;
					$result .= "---END---".PHP_EOL;
					$result .= PHP_EOL;
					$f=file_put_contents(__DIR__.DIRECTORY_SEPARATOR."ERRORS_DOWNLOAD.txt", $result, FILE_APPEND);
					$error = 'You have reached the limits of download this file, please write to '.$SUPPORT_EMAIL;
					include($PATH_ROOT_FOLDERS.'templates/error.php');
					die();
				}
			}else{
				//Zip file does not exist
				$result = "---ERROR--".date('Y-m-d H:i:s')."--DOWNLOAD--".PHP_EOL;
				$result .= "---DOWNLOAD DOES NOT EXIST---".PHP_EOL;
				$result .= "---TRANSACTION ID --".$this->transactionId."-".PHP_EOL;
				$result .= print_r($PATH_ROOT_FOLDERS.$this->file_zip, true).PHP_EOL;
				$result .= "---END---".PHP_EOL;
				$result .= PHP_EOL;
				$f=file_put_contents(__DIR__.DIRECTORY_SEPARATOR."ERRORS_DOWNLOAD.txt", $result, FILE_APPEND);
				$error = 'Something bad happened, please write to '.$SUPPORT_EMAIL;
				include($PATH_ROOT_FOLDERS.'templates/error.php');
				die();
			}
		}else{
			//No file to download
			$result = "---ERROR--".date('Y-m-d H:i:s')."--DOWNLOAD--".PHP_EOL;
			$result .= "---DOWNLOAD FILE IS EMPTY---".PHP_EOL;
			$result .= print_r($PATH_ROOT_FOLDERS.$this->file_zip, true).PHP_EOL;
			$result .= "---END---".PHP_EOL;
			$result .= PHP_EOL;
			$f=file_put_contents(__DIR__.DIRECTORY_SEPARATOR."ERRORS_DOWNLOAD.txt", $result, FILE_APPEND);
			$error = 'Something bad happened, please write to '.$SUPPORT_EMAIL;
			include($PATH_ROOT_FOLDERS.'templates/error.php');
			die();
		}
	}
	
	
}