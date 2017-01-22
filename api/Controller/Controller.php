<?php
namespace Controller;

use Model\Photo as Photo;
use Model\ImageResize as ImageResize;

class Controller
{
    public $route;
    public $stringUri;

    public function __construct($route, $stringUri)
    {
        $this->route = $route;
        $this->stringUri = $stringUri;
    }

    public function responseOk ($data)
    {
        return [
            'ResponseCode' => 200,
            'data' => $data
        ];
    }

    public function responseDenied ($data)
    {
        return [
            'ResponseCode' => 400,
            'data' => $data
        ];
    }

    public function responseError ($data)
    {
        return [
            'ResponseCode' => 500,
            'data' => $data
        ];
    }

    public function getResizedImage()
    {
        
        $location = $this->stringUri;

        if (strpos($location, '..') !== false) {
            return $this->responseDenied([
                'data' => 'Not allowed ' . $location 
            ]);
        }

        $location = '../../' . $location;
        $info = getimagesize($location);

        return $this->responseOk([
            'location' => $location,
            'content-type' => $info['mime']
        ]);
    }

    public function resizeImage()
    {
        $parts = explode('/', $this->stringUri);
        $photoId = $parts[3];
        $width = $parts[5];
        $height = $parts[7]; 

        $photo = new Photo($photoId);
        $resize = new ImageResize($photo->getImagePathResized(), $width, $height);
        
        $resize->outputAsJPG();
        //resources can not be passed to other classes - resource(13) of type (Unknown)
        // not as expected resource(13) of type (gd)
        die();
    }

}