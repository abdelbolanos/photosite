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
        $photos_array = $photo_finder->getAllImagesWithPagination($LIMIT, $PAGE, true);
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
        if(!empty($_POST['original_photo'])  && !empty($_POST['price'])) {

            $category = $_POST['category'];
            $path_generated = Photo::generateImagePaths($_POST['original_photo_name'], $category, true);
            $photo_name_original = $path_generated['photo_name_original'];
            $original_path = $path_generated['original_path'];
            $resized_path = $path_generated['resized_path'];
            $full_path_original = $path_generated['full_path_original'];

            //Try to move files to folder
            $result_create = Photo::createImageFromEncode64($_POST['original_photo'], $full_path_original);
            if ($result_create === true){

                //create resized (watermark)
                $fullPathResized = Photo::createResizedImage(
                    $photo_name_original,
                    $category,
                    $full_path_original
                );
            
                $photo = new Photo();
                $photo->title = $_POST['title'];
                $photo->file_name = $photo_name_original;
                $photo->resized_path = $resized_path;
                $photo->original_path = $original_path;
                $photo->price = $_POST['price'];
                $photo->category = $category;
                $photo->code = $_POST['code'];
                $photo->tags = $_POST['tags'];

                $photo->save();
                return $this->responseOk([
                    'result' => 'Photo saved with id '. $photo->id
                ]);

            } else {
                return $this->responseError([
                    'error' => 'Error: '.$result_create
                ]);
            }
        } else {
            return $this->responseError([
                'error' => 'Error: All fields are mandatory (except Code!)!'
            ]);
        }
    }

    public function delete()
    {
        $parts = explode('/', $this->stringUri);
        $id = $parts[4];
        $photo = new Photo($id);
        $photo->delete();
        return $this->responseOk([
            'data' => 'Deleted photo '. $_POST['id']
        ]);
    }

    public function update()
    {
        //UPDATE

        //object simple fields first
        $photo = new Photo($_POST['id']);
        $photo->title = $_POST['title'];
        $photo->price = $_POST['price'];
        $photo->code = $_POST['code'];
        $photo->tags = $_POST['tags'];
        $photo->save();
        $ok = 'Updated photo';

        $category = $_POST['category'];

        //changes category
        if( $photo->category != $_POST['category'] ) {
            //changed category move to other category folder

            $path_generated = Photo::generateImagePaths($photo->file_name, $category, false);
            $original_path = $path_generated['original_path'];
            $resized_path = $path_generated['resized_path'];
            $full_path_original = $path_generated['full_path_original'];
            $full_path_resized = $path_generated['full_path_resized'];

            if(rename($photo->getImagePathOriginal(), $full_path_original ) && rename( $photo->getImagePathResized(), $full_path_resized)) {
                $photo->resized_path = $resized_path;
                $photo->original_path =  $original_path;
                $photo->category =  $category;
                $photo->save();
                $ok = 'Updated photo and category';
            } else {
                $error = 'Photo updated but category could not be changed cause could not move files in the new category.';
                return $this->responseError([
                    'error' => $error
                ]);
            }

        }

        //Uploaded new photos
        if( isset($_POST['original_photo']) && !empty($_POST['original_photo']) ){
            $error = 'NOT IMPLEMENTED!!!';

            //proceso similar al insert
            $path_generated = Photo::generateImagePaths($_POST['original_photo_name'], $category, true);
            $photo_name_original = $path_generated['photo_name_original'];
            $original_path = $path_generated['original_path'];
            $resized_path = $path_generated['resized_path'];
            $full_path_original = $path_generated['full_path_original'];
            $full_path_resized = $path_generated['full_path_resized'];

            //Try to move files to folder
            $result_create = Photo::createImageFromEncode64($_POST['original_photo'], $full_path_original);
            if($result_create === true) {

                //create resized (watermark)
                $fullPathResized = Photo::createResizedImage($photo_name_original, $category, $full_path_original);

                //Borrar fotos viejas
                unlink($photo->getImagePathOriginal());
                unlink($photo->getImagePathResized());

                $photo->file_name = $photo_name_original;
                $photo->resized_path = $resized_path;
                $photo->original_path =  $original_path;
                $photo->save();
                $ok = 'Photo updated with id '.$photo->id;

            } else {
                    $error = 'Error: uploaded files could not move to categories folder "'.$category.'"';
                    return $this->responseError([
                        'error' => $error
                    ]);
            }
        }

        return $this->responseOk([
                    'result' => $ok
                ]);

    }
}
