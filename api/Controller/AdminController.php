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
}
