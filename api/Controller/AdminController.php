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
        if(!empty($_POST['original_photo'])  && !empty($_POST['price'])){
                
            global $PATH_ROOT_FOLDERS;
            global $PATH_ORIGINAL_FOLDERS;
            global $PATH_RESIZED_FOLDERS;
            
            $photoNameOriginal = 'photo_'.time().'_'.str_replace(' ', '', $_POST['original_photo_name']);
            $category = $_POST['category'];
            $original_path = $PATH_ORIGINAL_FOLDERS.DIRECTORY_SEPARATOR.$category.DIRECTORY_SEPARATOR; 
            $resized_path = $PATH_RESIZED_FOLDERS.DIRECTORY_SEPARATOR.$category.DIRECTORY_SEPARATOR;
            $fullPathOriginal =  $PATH_ROOT_FOLDERS.$original_path.$photoNameOriginal;

            //Try to move files to folder
            if (Photo::createImageFromEncode64($_POST['original_photo'], $fullPathOriginal)){

                //create resized (watermark)
                $fullPathResized = Photo::createResizedImage( $photoNameOriginal, $category, $fullPathOriginal);
            
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
                return $this->responseOk([
                    'result' => 'Photo saved with id '. $photo->id
                ]);

            } else {
                return $this->responseError([
                    'error' => 'Error: uploaded files could not move to categories folder "'.$_POST['category'].'"'
                ]);
            }
        } else {
            return $this->responseError([
                'error' => 'Error: All fields are mandatory (except Code!)!'
            ]);
        }
    }
}
