<?php
class PaymentPayPal{
    public $id; //id in table
    public $photos=''; //coma separated photos id
    public $transactionId=''; //paypal token via GET 
    public $dateCreated='';  //First time we received transactionId
    public $dateModified=''; //Modifications on table are saved here
    private $payPalResponse=''; //serilize paypal response of this transaction
    public $isSuccess=0; //sucess transactions equal 1 or 0 on failed
	
    private $auth_toke_test = "";
    private $paypal_url_test = "www.sandbox.paypal.com";
	
    private $paypal_url = "www.paypal.com";
    private $auth_token = "";
	
    /*
    * initialize this class:
    * PaymentPayPal(array('field'=>'value')  ) //Try to search in DB by this SINGLE object, if MORE THAN ONE WILL THROW EXCEPTION
    * PaymentPayPal( array('transactionId' => $transactionId )  ) //Get object by transactionId
    * 
    */
    public function __construct( $array_fields=array()  ){
        global $DB_PDO;
        $SQL = "SELECT * FROM `paypal_transactions` WHERE ";
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
    
    /*
    * Taken the transactionId and make a CURL POST request to check this transaction ONLY
    * if payPalResponse is EMPTY, otherwise payPalResponse will be returned
	* $test_site if true send the CURL request to sandbox
	* $force = if true will query paypal for the transaction info
	* return array if ok or false if payment not approved
    */
    public function checkPayment($test_site=false, $force=false){
        if( empty( $this->payPalResponse) || $force == true  ){
			if( !empty( $this->transactionId ) ){
				//
				if( $test_site == true ){
					$url = "https://" . $this->paypal_url_test . "/cgi-bin/webscr";
					$post_vars = "cmd=_notify-synch&tx=" . $this->transactionId . "&at=" . $this->auth_toke_test; 
				}else{
					$url = "https://" . $this->paypal_url . "/cgi-bin/webscr";
					$post_vars = "cmd=_notify-synch&tx=" . $this->transactionId . "&at=" . $this->auth_token;
				}
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vars);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, 15);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE); //if complains about ssl
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_USERAGENT, 'cURL/PHP');
				
				$response = curl_exec($ch);
				
				if( $response ){
					$lines = explode("\n", $response);
					$PayPayResponse = array();
					foreach( $lines as $line ){
						$parts = explode('=', $line);
						if( isset($parts[0]) && isset($parts[1]) ) $PayPayResponse[ $parts[0] ] = $parts[1];
					}
					//set Response
					$this->setPayPalResponse($PayPayResponse);
					//and SAVE IT
					$this->save();
					
					//Was Approved or not?
					if( $PayPayResponse['payment_status'] == 'Completed'  ){
						return true;
					}else{
						return false;
					}
				}else{
					//save as a failure for later check
					$this->save();
					throw new Exception('NO_PAYPAL_RESPONSE: PayPal server is not responding.');
				}
			}else{
				throw new Exception('TX_NOT_SET: Transaction Id has not been set.');
			}
        }
		
		$payPalResponse = $this->getPayPalResponse();
		//Check if the Payment was ok or not
		if( $payPalResponse['payment_status'] == 'Completed' ){
			//OK
			$this->isSuccess = 1;
			//Set the photos bought
			for( $i=1; $i<100; $i++ ){
				if( isset( $payPalResponse['item_number'.$i] ) ){
					$this->photos .= $payPalResponse['item_number'.$i];
					if( isset( $payPalResponse['item_number'.($i + 1)] ) ){
						$this->photos .= ','; //if there is a next put the separator
					}					
				}else{
					break;
				}
				
			}
			$this->save();
			return true;
		}else{
			return false;
		}
    }
    
    public function save(){
        global $DB_PDO;
		
	    if( $this->id == 0  ){
	        //INSERT
	        $std  = $DB_PDO->prepare("INSERT INTO `paypal_transactions` ( `transactionId`, `dateCreated`, `dateModified`, `payPalResponse`, `photos`, `isSuccess`) VALUE ( :transactionId, :dateCreated, :dateModified, :payPalResponse, :photos, :isSuccess) ");
		    $std->bindValue(':transactionId', $this->transactionId);
			$std->bindValue(':dateCreated', date("Y-m-d H:i:s"));
			$std->bindValue(':dateModified', date("Y-m-d H:i:s"));
			$std->bindValue(':payPalResponse', $this->payPalResponse);
		    $std->bindValue(':photos', $this->photos);
		    $std->bindValue(':isSuccess', $this->isSuccess);
	        if( $std->execute() ){
	            $this->id = $DB_PDO->lastInsertId();
	            return $this->id;
	        }else{
	            throw new Exception('Error: saving paypal_transactions debug '.print_r($std->errorInfo() ,true) );
	        }
	        
	    }else{
	        //UPDATE
	        $std  = $DB_PDO->prepare("UPDATE `paypal_transactions` SET `dateModified`= :dateModified, `payPalResponse` = :payPalResponse, `photos` = :photos, `isSuccess` = :isSuccess WHERE id =:id ");
		    $std->bindValue(':id', $this->id);
			$std->bindValue(':dateModified', date("Y-m-d H:i:s"));
			$std->bindValue(':payPalResponse', $this->payPalResponse);
		    $std->bindValue(':photos', $this->photos);
		    $std->bindValue(':isSuccess', $this->isSuccess);
	        if( $std->execute() ){
	            return $this->id;
	        }else{
	            throw new Exception('Error: saving paypal_transactions debug '.print_r($std->errorInfo() ,true) ) ;
	        }
	    }
        
    } 
    
    
    /*
    * Only this field needs to be GET this way
    */
    public function  getPayPalResponse(){
        return ( !empty($this->payPalResponse) ) ? unserialize($this->payPalResponse) : '';
    }
    
    /*
    * Only this field needs to be SET this way
    */
    public function setPayPalResponse(array $payPalResponse ){
        $this->payPalResponse = serialize($payPalResponse);
        return $this;
    }
    
    
}
