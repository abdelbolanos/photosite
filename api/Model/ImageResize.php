<?php
namespace Model;

/**
 * ImageResize
 * Resize images in categories\resized
 * 
 * @author abdel
 */
class ImageResize {
    protected $width;
    protected $height;
    protected $gd_image;
    protected $default_height;
    protected $default_width;
    protected $default_gd_image;
    
    public function __construct($file_name,$width=70, $height=70) {
        list($source_image_width, $source_image_height, $source_image_type) = getimagesize($file_name);
        
        $this->default_width = $source_image_width;
        $this->default_height = $source_image_height;
        
        switch ($source_image_type) {
            case IMAGETYPE_GIF:
                $this->default_gd_image = imagecreatefromgif($file_name);
                break;
            case IMAGETYPE_JPEG:
                $this->default_gd_image = imagecreatefromjpeg($file_name);
                break;
            case IMAGETYPE_PNG:
                $this->default_gd_image = imagecreatefrompng($file_name);
                break;
        }
        
        
        if ($this->default_gd_image === false) {
            throw new Exception('Image Resize Failed! file: '.__FILE__);
        }
        
        $source_aspect_ratio = $this->default_width / $this->default_height;
        $thumbnail_aspect_ratio = $width / $height;
        
        if ($source_image_width <= $width && $this->default_height <= $height) {
            $thumbnail_image_width = $this->default_width;
            $thumbnail_image_height = $this->default_height;
        } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
            $thumbnail_image_width = (int) ($height * $source_aspect_ratio);
            $thumbnail_image_height = $height;
        } else {
            $thumbnail_image_width = $width;
            $thumbnail_image_height = (int) ($width / $source_aspect_ratio);
        }
        
        $this->width = $thumbnail_image_width;
        $this->height = $thumbnail_image_height;
        
        $this->gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
        imagecopyresampled($this->gd_image, $this->default_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $this->default_width, $this->default_height);
        
    }
    
    public function __destruct() {
        // Free up memory
        imagedestroy($this->gd_image);
    }
    
    /*
     * Output image as JPEG
     */
    public function outputAsJPG(){
        header('Content-Type: image/jpeg');
		header ("Cache-Control: must-revalidate");
		$offset = 7 * 24 * 60 * 60;//expires one week
		$expire = "Expires: " . gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT";
		header ($expire);
        imagejpeg($this->gd_image);
    }

    public function getGdImage()
    {
        return $this->gd_image;
    }
    
    
}

