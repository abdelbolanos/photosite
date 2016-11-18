<?php
namespace Controller;

use Model\PhotoFinder as PhotoFinder;

class PhotoController extends Controller
{
    
    public function __construct($route, $stringUri)
    {
        parent::__construct($route, $stringUri);
    }

    public function list()
    {
        $LIMIT = ( isset($_GET['limit']) ) ? $_GET['limit'] : 10;
        $category = ( isset($_GET['category']) ) ? $_GET['category'] :  PhotoFinder::FLORAFAUNA;
        $PAGE = ( isset($_GET['page']) ) ? $_GET['page'] : 1;

 
        $photo_finder = new PhotoFinder( $category );
        $photos_array = $photo_finder->getAllImagesWithPagination($LIMIT, $PAGE);
        $TOTAL = $photo_finder->getTotal();

        $TOTAL_PAGES = round( $TOTAL / $LIMIT );

        $pagination = '';
        for( $i=1; $i <= $TOTAL_PAGES; $i++ ){
            if( $PAGE == $i ) $pagination .= " <b> {$i} </b> "; 
            else $pagination .= " <a href='update-delete.php?page={$i}&category={$CATEGORY}'><b> {$i} </b></a> "; 
        }

        return $this->responseOk([
            'photos' => $photos_array,
            'category' => $category,
            'total' => $TOTAL,
            'totalPages' => $TOTAL_PAGES,
            'page' => $PAGE
        ]);
    }

    public function newest()
    {
        $photoList = new PhotoFinder();
        $transform = true;
        $newest = $photoList->getLastThree($transform);
        return $this->responseOk([
            'newest' => $newest
        ]);
    }

    public function category()
    {
        $parts = explode('/', $this->stringUri);

        $category = $parts[3];

        $photo_finder = new PhotoFinder($category);
        $transform = true;
        $photos_array = $photo_finder->getAllImages($transform);

        return $this->responseOk([
            'category' => $category,
            'photos' => $photos_array
        ]);
    }
}
