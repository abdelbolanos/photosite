<?php


class User {
    
    public $id = 0,$name,$crdate,$nickname,$email,$password,$company,$website,$address,$country,$amount_sold;
    
    
    /*
    * User object constructed by PDO::FETCH_ASSOC
    * if object pass an integer will try to find this on table
    * example:  new User( $row )  $row <-- PDO::FETCH_ASSCO row
    * example: new User($id) $id <-- id of the user	
    */
    public function __construct( $array_object = 0){
            if( !empty($array_object) && is_array($array_object) ){
                    foreach( $array_object as $attribute => $value ){
                            if( property_exists( $this, $attribute ) ){
                                    $this->{$attribute} = $value;
                            }
                    }
            }else if( !empty($array_object) && is_numeric($array_object) && $array_object > 0  ){
                    global $DB_PDO;
                    $std  = $DB_PDO->prepare("SELECT * FROM `fe_users` WHERE `id` = :id");
                    $std->bindValue(':id', $array_object);
                    $std->execute();
                    if( $std->rowCount() == 1  ){
                            $row = $std->fetch(PDO::FETCH_ASSOC);
                            foreach( $row as $attribute => $value ){
                                    if( property_exists( $this, $attribute ) ){
                                            $this->{$attribute} = $value;
                                    }
                            }
                    }
            }
    }
    
    /**
     * This method will return the photos uploaded by a specific user
     */
    public function getPhotos(){
        
    }
    
    
    /*
	* Save :
	* if id == 0 -> INSERT
	* if id > 0 -> UPDATE
	*/
	public function save(){
	    global $DB_PDO;
		
	    if( $this->id == 0  ){
	        //INSERT
	        $std  = $DB_PDO->prepare("INSERT INTO `fe_users` ( `fullname`, `crdate`, `nickname`, `password`, `company`, `email`, `website`, `address`, `country`) VALUE ( :fullname, :crdate, :nickname, :password, :file_name, :resized_path, :original_path, :price, :category, :code, :tags, :is_free, :free_downloads ) ");
		    $std->bindValue(':file_name', $this->file_name);
			$std->bindValue(':title', $this->title);
			$std->bindValue(':crdate', date("Y-m-d H:i:s"));
			$std->bindValue(':date_modified', date("Y-m-d H:i:s"));
			$std->bindValue(':tstamp', time());
		    $std->bindValue(':resized_path', $this->resized_path);
		    $std->bindValue(':original_path', $this->original_path);
		    $std->bindValue(':price', $this->price);
		    $std->bindValue(':category', $this->category);
		    $std->bindValue(':code', $this->code);
		    $std->bindValue(':tags', $this->tags);
                    $std->bindValue(':is_free', $this->is_free);
                    $std->bindValue(':free_downloads', $this->free_downloads);
	        if( $std->execute() ){
	            $this->id = $DB_PDO->lastInsertId();
			
		    //Save tags
		    $tags = new Tags($this->id, $this->tags);
		    $tags->save();

	            return $this->id;
	        }else{
	            throw new Exception('Error: saving photo debug '.print_r($std->errorInfo() ,true) );
	        }
	        
	    }else{
	        //UPDATE
	        $std  = $DB_PDO->prepare("UPDATE `images` SET `title` = :title, `date_modified` = :date_modified, `file_name`= :file_name, `resized_path` = :resized_path, `original_path` = :original_path, `price` = :price, `category` = :category, `code` = :code, `tags` = :tags,`is_free` = :is_free,`free_downloads` = :free_downloads WHERE id =:id ");
		    $std->bindValue(':id', $this->id);
			$std->bindValue(':title', $this->title);
			$std->bindValue(':date_modified', date("Y-m-d H:i:s"));
		    $std->bindValue(':file_name', $this->file_name);
		    $std->bindValue(':resized_path', $this->resized_path);
		    $std->bindValue(':original_path', $this->original_path);
		    $std->bindValue(':price', $this->price);
		    $std->bindValue(':category', $this->category);
		    $std->bindValue(':code', $this->code);
		    $std->bindValue(':tags', $this->tags);
                    $std->bindValue(':is_free', $this->is_free);
                    $std->bindValue(':free_downloads', $this->free_downloads);
	        if( $std->execute() ){

		    //Save tags
		    $tags = new Tags($this->id, $this->tags);
		    $tags->save();

	            return $this->id;
	        }else{
	            throw new Exception('Error: saving photo debug '.print_r($std->errorInfo() ,true) ) ;
	        }
	    } 
	}
    
    
    
}



?>
