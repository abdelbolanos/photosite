<?php
namespace Controller;

use Model\PhotoFinder as PhotoFinder;
use Model\Photo as Photo;

class AdminController extends Controller
{
    
    public function __construct($route, $stringUri)
    {
        parent::__construct($route, $stringUri);
    }

    public function list()
    {
        $LIMIT = ( isset($_GET['limit']) ) ? $_GET['limit'] : 10;
        $PAGE = ( isset($_GET['page']) ) ? $_GET['page'] : 1;

        $photo_finder = new PhotoFinder();
        $photos_array = $photo_finder->getAllImagesWithPagination($LIMIT, $PAGE);
        $TOTAL = $photo_finder->getTotal();
        $TOTAL_PAGES = PhotoFinder::getTotalPages($TOTAL, $LIMIT);

        return $this->responseOk([
                    'photos' => $photos_array,
                    'category' => $category,
                    'total' => $TOTAL,
                    'totalPages' => $TOTAL_PAGES,
                    'page' => $PAGE,
                    'limit' => $LIMIT
            ]);
    }

    public function photo()
    {
        $parts = explode('/', $this->stringUri);
        $id = $parts[4];

        $photo = new Photo($id);

        return $this->responseOk([
            'photo' => $photo
        ]);
    }

    public function add()
    {
        //ADD
        if($_FILES['original_photo']['error'] == UPLOAD_ERR_OK  && !empty($_POST['price'])  ){
                
            global $PATH_ROOT_FOLDERS;
            
            $photoNameOriginal = 'photo_'.time().'_'.str_replace(' ', '', $_FILES['original_photo']['name']); 
            
            $category = $_POST['category'];
            $original_path = $PATH_ORIGINAL_FOLDERS.DIRECTORY_SEPARATOR.$category.DIRECTORY_SEPARATOR; 
            $resized_path = $PATH_RESIZED_FOLDERS.DIRECTORY_SEPARATOR.$category.DIRECTORY_SEPARATOR;
            $fullPathOriginal =  $PATH_ROOT_FOLDERS.$original_path.$photoNameOriginal;

            //Try to move files to folder
            //var_dump($fullPathOriginal);
            //var_dump($fullPathResized);
            if( move_uploaded_file( $_FILES['original_photo']['tmp_name'],  $fullPathOriginal)  ){
                    
                //create resized (watermark)
                $fullPathResized = Photo::createResizedImage( $photoNameOriginal, $category, $_FILES['original_photo']['tmp_name']);
            
                $photo = new Photo();
                $photo->title = $_POST['title'];
                $photo->file_name = $photoNameOriginal;
                $photo->resized_path = $resized_path;
                $photo->original_path =  $original_path;
                $photo->price = $_POST['price'];
                $photo->category =  $category;
                $photo->code = $_POST['code'];
                $photo->tags = $_POST['tags'];
                $photo->save();
                $ok = 'Photo saved with id '.$photo->id;

            }else{
                    $error = 'Error: uploaded files could not move to categories folder "'.$_POST['category'].'"';
            }
            
        } else {
            if (  $_FILES['original_photo']['error'] == UPLOAD_ERR_INI_SIZE ){
                $error = 'Error: Increase  upload_max_filesize  in server';
            } else {
                $error = 'Error: All fields are mandatory (except Code!)! Upload error: orignal-'.$_FILES['original_photo']['error'].', resized-'.$_FILES['resized_photo']['error'] ;
            }
        }
    }
}
