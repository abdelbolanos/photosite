<?php
namespace Controller;

use Model\PhotoFinder as PhotoFinder;

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
		return $this->responseOk([
            		'photos' => $photos_array,
            		'category' => $category,
            		'total' => $TOTAL,
            		'totalPages' => $TOTAL_PAGES,
            		'page' => $PAGE
        	]);
	}

	public function add()
	{
		
	}
}
