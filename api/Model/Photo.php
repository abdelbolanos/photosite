<?php
namespace Model;

class Photo{
    public $id=0, $title, $crdate, $date_modified, $tstamp,$file_name, $resized_path, $original_path, $price, $category, $code, $tags,$is_free,$free_downloads;
    
    /*
    * Photo object constructed by PDO::FETCH_ASSOC
    * if object pass an integer will try to find this on table
    * example:  new Photo( $row )  $row <-- PDO::FETCH_ASSCO row
    * example: new Photo($id) $id <-- id of the photo   
    */
    public function __construct( $array_object=0){
        if( !empty($array_object) && is_array($array_object) ){
            foreach( $array_object as $attribute => $value ){
                if( property_exists( $this, $attribute ) ){
                    $value = $this->clean_separator($attribute, $value);
                    $this->{$attribute} = $value;
                }
            }
        }else if( !empty($array_object) && is_numeric($array_object) && $array_object > 0  ){
            global $DB_PDO;
            $std  = $DB_PDO->prepare("SELECT * FROM `images` WHERE `id` = :id");
            $std->bindValue(':id', $array_object);
            $std->execute();
            if( $std->rowCount() == 1  ){
                $row = $std->fetch(\PDO::FETCH_ASSOC);
                foreach( $row as $attribute => $value ){
                    if( property_exists( $this, $attribute ) ){
                        $value = $this->clean_separator($attribute, $value);
                        $this->{$attribute} = $value;
                    }
                }
            }
        }
    }

    public function clean_separator($name, $value)
    {
        $win_sep = '\\';
        $lin_sep = '/';
        if ($name == 'resized_path' || $name == 'original_path') {      
            if (DIRECTORY_SEPARATOR == $lin_sep) {
                $value = str_replace($win_sep, $lin_sep, $value);
            } else {
                $value = str_replace($lin_sep, $win_sep, $value);
            }
        }

        return $value;
    } 

    public function getApiUri() {
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT'] . '/api/';
    }

    /*
    * return path related to URL
    *
    */
    public function getImageURI(){
        if( !empty($this->resized_path) && !empty($this->file_name) ){
            return $this->getApiUri() . $this->resized_path.$this->file_name;
        }else{
            return false;
        }
    }
        
        /*
    * return path related to URL resized
    *
    */
    public function getImageURIResized($width, $height){
        if( !empty($this->resized_path) && !empty($this->file_name) ){
            return $this->getApiUri() . "resize-image/{$this->id}/width/{$width}/height/{$height}";
        }else{
            return false;
        }
    }
    
    public function getPageURI(){
        return 'photo-'.$this->id.'-'.strtolower(str_replace(' ','-',$this->title));
    }
    
    /*
    * return path resized image
    * false if object not initilized
    */
    public function getImagePathResized(){
        global $PATH_ROOT_FOLDERS;
        if( !empty($this->resized_path) && !empty($this->file_name) ){
            return $PATH_ROOT_FOLDERS.DIRECTORY_SEPARATOR.$this->resized_path.DIRECTORY_SEPARATOR.$this->file_name;
        }else{
            return false;
        }
    }
    
    /*
    * return path original image
    * false if not initialized object
    */
    public function getImagePathOriginal(){
        global $PATH_ROOT_FOLDERS;
        if( !empty($this->resized_path) && !empty($this->file_name) ){
            return $PATH_ROOT_FOLDERS.DIRECTORY_SEPARATOR.$this->original_path.DIRECTORY_SEPARATOR.$this->file_name;
        }else{
            return false;
        }
    }   
    
    /*
    * Return photo dimensions as an array
    * [ 'width'=>'', 'height'=>'' ]
    * or false if image does not exist
    */
    public function getSize(){
        global $PATH_ROOT_FOLDERS;
        if( !empty($this->resized_path) && !empty($this->file_name) ){
            list($width, $height) = getimagesize( $this->getImagePathOriginal() );
            return array('width'=>$width, 'height'=>$height);
        }else{
            return false;
        }
    }
    
    
    /*
    * Return image record by tstamp (tstamp is considered the id)
    *
    * or false if tstamp does not exist
    */
    public function getPhotoByTStamp($tstamp){      
        global $DB_PDO;
        $std  = $DB_PDO->prepare("SELECT * FROM `images` WHERE `tstamp` = :tstamp");
        $std->bindValue(':tstamp', $tstamp);
        $std->execute();
        if( $std->rowCount() == 1  ){
            $row = $std->fetch(PDO::FETCH_ASSOC);
            foreach( $row as $attribute => $value ){
                    if( property_exists( $this, $attribute ) ){
                        $this->{$attribute} = $value;
                    }
            }
            return $this;
        }else{
          return false;
        }
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
            $std  = $DB_PDO->prepare("INSERT INTO `images` ( `title`, `crdate`, `date_modified`, `tstamp`, `file_name`, `resized_path`, `original_path`, `price`, `category`, `code`, `tags`,`is_free`,`free_downloads`) VALUE ( :title, :crdate, :date_modified, :tstamp, :file_name, :resized_path, :original_path, :price, :category, :code, :tags, :is_free, :free_downloads ) ");
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
    
    /*
    * DELETE
    */
    public function delete(){
        global $DB_PDO;
        
         //DELETE
        $std  = $DB_PDO->prepare("DELETE FROM `images`  WHERE id =:id ");
        $std->bindValue(':id', $this->id);
        $std->execute();

        //Delete physical images
        global $PATH_ROOT_FOLDERS;
        unlink( $PATH_ROOT_FOLDERS . $this->original_path . $this->file_name );
        unlink( $PATH_ROOT_FOLDERS . $this->resized_path . $this->file_name );
         
    }
    
    /*
    * return array of 'tag' => 'url'
    */
    public function getTagLinks(){
            $tagLinks_array = array();
            $tags = new Tags($this->id, $this->tags);
            $tagLinks_array = $tags->getTagLinks();
            return $tagLinks_array;
    }
        
    /**
     * 
     */
    public static function createResizedImage($nameOriginalImage, $category, $pathOriginalImage){
        global $PATH_WATERMARK_IMAGE;
        global $PATH_ROOT_FOLDERS;
        global $PATH_RESIZED_FOLDERS;
        $thumbnail_image_width = 400;
        $thumbnail_image_height = 300;
        
        // Load the stamp and the photo to apply the watermark to
        $stamp = imagecreatefrompng($PATH_WATERMARK_IMAGE);
        $f=file_put_contents(__DIR__.DIRECTORY_SEPARATOR."DEBUG.txt", 'path original:' . $pathOriginalImage, FILE_APPEND);
        $f=file_put_contents(__DIR__.DIRECTORY_SEPARATOR."DEBUG.txt", 'name original:' . $nameOriginalImage, FILE_APPEND);
        list($width_t, $height_t, $type_t) = getimagesize($pathOriginalImage);
        $f=file_put_contents(__DIR__.DIRECTORY_SEPARATOR."DEBUG.txt", sprintf("GETIMAGESIZE - Width: %s, Height: %s", $width_t, $height_t), FILE_APPEND);
        
        $source_gd_image = imagecreatefromjpeg($pathOriginalImage);
        $originalWidth = imagesx($source_gd_image);
        $originalHeight = imagesy($source_gd_image);
        
        $f=file_put_contents(__DIR__.DIRECTORY_SEPARATOR."DEBUG.txt", sprintf("Width: %s, Height: %s", $originalWidth, $originalHeight), FILE_APPEND);
        
        if($originalWidth < $originalHeight){
            $thumbnail_image_width = 150;
            $thumbnail_image_height = 200;
        }

        //Resize original image
        list($source_image_width, $source_image_height, $source_image_type) = getimagesize($pathOriginalImage);
        
        if($originalWidth < $originalHeight){
            $source_aspect_ratio = $source_image_height / $source_image_width;
            $thumbnail_aspect_ratio = $thumbnail_image_height / $thumbnail_image_width;
        }else{
            $source_aspect_ratio = $source_image_width / $source_image_height;
            $thumbnail_aspect_ratio = $thumbnail_image_width / $thumbnail_image_height;
        }
        
        if ($source_image_width <= $thumbnail_image_width && $source_image_height <= $thumbnail_image_height) {
            $thumbnail_image_width = $source_image_width;
            $thumbnail_image_height = $source_image_height;
        } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
            $thumbnail_image_width = (int) ($thumbnail_image_height * $source_aspect_ratio);
            $thumbnail_image_height = $thumbnail_image_height;
        } else {
            $thumbnail_image_width = $thumbnail_image_width;
            $thumbnail_image_height = (int) ($thumbnail_image_width / $source_aspect_ratio);
        }
        $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
        imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
        imagedestroy($source_gd_image);
        // Set the margins for the stamp and get the height/width of the stamp image
        $marge_right = 10;
        $marge_bottom = 10;
        $sx = imagesx($stamp);
        $sy = imagesy($stamp);

        // Copy the stamp image onto our photo using the margin offsets and the photo 
        // width to calculate positioning of the stamp. 
        imagecopy($thumbnail_gd_image, $stamp, (imagesx($thumbnail_gd_image) - $sx)/2, (imagesy($thumbnail_gd_image) - $sy)/2, 0, 0, imagesx($stamp), imagesy($stamp));

        imagedestroy($stamp);
        
        $resized_path = $PATH_RESIZED_FOLDERS.DIRECTORY_SEPARATOR.$category.DIRECTORY_SEPARATOR;
        $resized_image = $PATH_ROOT_FOLDERS.$resized_path.$nameOriginalImage;
        
        imagejpeg($thumbnail_gd_image, $resized_image, 100);
        imagedestroy($thumbnail_gd_image);
        
        return $resized_image;
    }

    public static function createImageFromEncode64($string64, $fileSavePath) {
        try {
            $string_data = explode(',', $string64)[1];
            $data = base64_decode($string_data);
            $image_gd = imagecreatefromstring($data);
            imagejpeg($image_gd, $fileSavePath, 100);
            imagedestroy($image_gd);
            return true;
        } catch (\Exception $e) {
            return $e->message;
        }
    }

    public static function generateImagePaths($original_photo_name, $category, $generate_unique_name=false) {
        global $PATH_ROOT_FOLDERS;
        global $PATH_ORIGINAL_FOLDERS;
        global $PATH_RESIZED_FOLDERS;

        if ($generate_unique_name) {
            $photo_name_original = 'photo_'.time().'_'.str_replace(' ', '', $original_photo_name);
            $photo_name_original = preg_replace('/^(.*)?\.(.*)$/', '$1.jpg', $photo_name_original);
        } else {
            $photo_name_original = $original_photo_name;
        }

        $original_path = $PATH_ORIGINAL_FOLDERS . DIRECTORY_SEPARATOR. $category . DIRECTORY_SEPARATOR; 
        $resized_path = $PATH_RESIZED_FOLDERS . DIRECTORY_SEPARATOR.$category . DIRECTORY_SEPARATOR;
        $full_path_original =  $PATH_ROOT_FOLDERS . $original_path . $photo_name_original;
        $full_path_resized = $PATH_ROOT_FOLDERS . $resized_path . $photo_name_original;

        return array(
            'original_path' => $original_path,
            'resized_path' => $resized_path,
            'photo_name_original' => $photo_name_original,
            'full_path_original' => $full_path_original,
            'full_path_resized' => $full_path_resized
        );
    }

}
