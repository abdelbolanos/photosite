<?php 
namespace Model;

class PhotoFinder{
    /*Create categories as constants*/
    const FLORAFAUNA = 'florafauna'; 
    const ARCHITECTURE = 'architecture';
    const EMOTIONS = 'emotions';
    const OBJECTS = 'objects';
    const FOOD = 'food';

    public $category;
    
    /**/
    public function __construct($category=''){
        $this->category = $category;
    }
    
    /*
    * Return array of images objects
    */
    public function getAllImages($transform = false){
        global $DB_PDO;
        $photo_array = array();
        $std  = $DB_PDO->prepare("SELECT * FROM `images` WHERE `category` = :category AND is_free = 0 ");
        $std->bindValue(':category', $this->category);
        if($std->execute()){
            while( $row = $std->fetch(\PDO::FETCH_ASSOC) ){
                $photo = new Photo($row);
                //If the foto really exist return this object
                if( file_exists($photo->getImagePathOriginal()) && file_exists($photo->getImagePathResized()) ){
                    $photo_array[] = $photo;
                }else{
                    //send email photos problem TODO
                    error_log('PHP LOG: Photo PATH problems ->id:'.$photo->id.'  '.__FILE__.' line:'.__LINE__ );
                }
            }
        }else{
            //send error message TODO
            error_log(print_r($std->errorInfo(),true).'  '.__FILE__.' line:'.__LINE__ );
        }

        if ($transform) {
            $photo_array = $this->transformPhotoArray($photo_array);
        }

        return $photo_array;
    }
        
        /*
    * Return array of images objects
    */
    public function getAllFreeImages(){
        global $DB_PDO;
        $photo_array = array();
        $std  = $DB_PDO->prepare("SELECT * FROM `images` WHERE is_free = 1 ");
        if($std->execute()){
            while( $row = $std->fetch(PDO::FETCH_ASSOC) ){
                $photo = new Photo($row);
                //If the foto really exist return this object
                if( file_exists($photo->getImagePathOriginal()) && file_exists($photo->getImagePathResized()) ){
                    $photo_array[] = $photo;
                }else{
                    //send email photos problem TODO
                    error_log('PHP LOG: Photo PATH problems ->id:'.$photo->id.'  '.__FILE__.' line:'.__LINE__ );
                }
            }
        }else{
            //send error message TODO
            error_log(print_r($std->errorInfo(),true).'  '.__FILE__.' line:'.__LINE__ );
        }
        return $photo_array;
    }
    
    /*
    * Return array of photo objects of all categories
    *
    */
    public function getAllImagesWithoutCat($array_ids=false){
        global $DB_PDO;
        $photo_array = array();
        $sql = "SELECT * FROM `images`";
        if( $array_ids && !empty($array_ids) ){
            $sql .= " WHERE `id` IN (";
            $sql .= implode(",", $array_ids);
            $sql .= ")";
        }
        $std  = $DB_PDO->prepare($sql);
        if($std->execute()){
                while( $row = $std->fetch(PDO::FETCH_ASSOC) ){
                        $photo = new Photo($row);
                        //If the foto really exist return this object
                        if( file_exists($photo->getImagePathOriginal()) && file_exists($photo->getImagePathResized()) ){
                                $photo_array[] = $photo;
                        }else{
                                //send email photos problem TODO
                                error_log('PHP LOG: Photo PATH problems ->id:'.$photo->id.'  '.__FILE__.' line:'.__LINE__ );
                        }
                }
        }else{
                //send error message TODO
                error_log(print_r($std->errorInfo(),true).'  '.__FILE__.' line:'.__LINE__ );
        }
        return $photo_array;
    }
        
    /*
    * Return array of images objects
    */
    public function getAllImagesWithPagination($limit=10, $page=1){
        global $DB_PDO;
        
        $offset = ( $page > 1 ) ? $limit * ( $page - 1 )  :  0;
        
        $photo_array = array();
        $std  = $DB_PDO->prepare("SELECT * FROM `images` WHERE `category` = :category  LIMIT :limit OFFSET :offset ");
        $std->bindValue(':category', $this->category);
        $std->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $std->bindValue(':offset', $offset, \PDO::PARAM_INT);
        if($std->execute()){
            while( $row = $std->fetch(\PDO::FETCH_ASSOC) ){
                $photo = new Photo($row);
                //If the foto really exist return this object
                if( file_exists($photo->getImagePathOriginal()) && file_exists($photo->getImagePathResized()) ){
                    $photo_array[] = $photo;
                }else{
                    //send email photos problem TODO
                    error_log('PHP LOG: Photo PATH problems ->id:'.$photo->id.'  '.__FILE__.' line:'.__LINE__ );
                }
            }
        }else{
            //send error message TODO
            error_log(print_r($std->errorInfo(),true).'  '.__FILE__.' line:'.__LINE__ );
        }
        return $photo_array;
    }
    
    /*
    * By category constructed
    */
    public function getTotal(){
        global $DB_PDO;
        $std  = $DB_PDO->prepare("SELECT COUNT(*) AS total FROM `images` WHERE `category` = :category");
            $std->bindValue(':category', $this->category);
        $std->execute();
        $row = $std->fetch(\PDO::FETCH_ASSOC);
        return $row['total'];
    }
        
        /*
         * Get the last three photos insertd in any category
         */
        public function getLastThree($transform = false){
            global $DB_PDO;
            $photo_array = array();
            $sql = "SELECT * FROM `images` ";
            if( !empty($this->category) ) " WHERE `category` = '{$this->category}'" ;
            $sql .= " ORDER BY `crdate` DESC LIMIT 6";
            $std = $DB_PDO->prepare($sql);
            if($std->execute()){
                while( $row = $std->fetch(\PDO::FETCH_ASSOC) ){
                    $photo = new Photo($row);
                    //If the foto really exist return this object
                    if( file_exists($photo->getImagePathOriginal()) && file_exists($photo->getImagePathResized()) ){
                        $photo_array[] = $photo;
                    }
                }
            }else{
        //send error message TODO
        error_log(print_r($std->errorInfo(),true).'  '.__FILE__.' line:'.__LINE__ );
            }

            if ($transform) {
                $photo_array = $this->transformPhotoArray($photo_array);
            }

            return  $photo_array;
        }
        
        /*
         * Get photos in same category, before or after a photo id
         */
        public function getPhotosBeforeOrAfterPhotoId($photoId, $total=3){
            global $DB_PDO;
            $photo_array = array();
            $sql = "SELECT * FROM `images` WHERE `id` > :id AND `category` = :category LIMIT ".$total;
            $std = $DB_PDO->prepare($sql);
            $std->bindValue(':id', $photoId);
            $std->bindValue(':category', $this->category);
            if($std->execute()){
                if( $std->rowCount() == $total ){
                    //try after this photo id
                }else{
                    //try before this photo id
                    $sql = "SELECT * FROM `images` WHERE `id` < :id AND `category` = :category LIMIT ".$total;
                    $std = $DB_PDO->prepare($sql);
                    $std->bindValue(':id', $photoId);
                    $std->bindValue(':category', $this->category);
                    $std->execute();
                }
                while( $row = $std->fetch(\PDO::FETCH_ASSOC) ){
                    $photo = new Photo($row);
                    //If the foto really exist return this object
                    if( file_exists($photo->getImagePathOriginal()) && file_exists($photo->getImagePathResized()) ){
                        $photo_array[] = $photo;
                    }
        }
            } else {
        //send error message TODO
        error_log(print_r($std->errorInfo(),true).'  '.__FILE__.' line:'.__LINE__ );
            }
        return  $photo_array;
        }

        public static function transformPhoto ($photo) {
            $photoTrans = new \stdClass();
            
            foreach( $photo as $attribute => $value ){
                $photoTrans->{$attribute} = $value;
            }
            
            $photoTrans->pageURI = $photo->getPageURI();
            $photoTrans->imageURI = $photo->getImageURI();
            $photoTrans->imageURIResized200x200 = $photo->getImageURIResized(200, 200);
            $photo_size = $photo->getSize();
            $photoTrans->width = $photo_size['width'];
            $photoTrans->height = $photo_size['height'];
            return $photoTrans;
        }

        public function transformPhotoArray ($array) {
            return array_map(
                    function($photo){
                        return self::transformPhoto($photo); 
                    }, 
                    $array
                );
        }
}
