<?php
namespace Model;

/*
CREATE TABLE  `photosite`.`tags` (
`imageId` INT( 11 ) NOT NULL ,
`tag` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY (  `imageId` ,  `tag` )
) ENGINE = MYISAM;
*/
class Tags{
	public $tags_string = '';
	public $tags_array = array();
	public $imageId;
	
	public function __construct($imageId, $tags=false ){
		$this->imageId = $imageId;
		
		if( $tags ){
			if( is_array($tags) ){
				$this->tags_array = $tags;
				$this->tags_string = $this->toString($tags);
			}
			if( is_string($tags) ){
				$this->tags_array = $this->toArray($tags);
				$this->tags_string = $tags;
			}
		}else{
			//search in table
			global $DB_PDO;
			$std  = $DB_PDO->prepare("SELECT * FROM `tags` WHERE `imageId` = :imageId");
			$std->bindValue(':id', $this->imageId);
			$std->execute();
			if( $std->rowCount() > 0  ){
				while($row = $std->fetch(PDO::FETCH_ASSOC)){
					$this->tags_array[] = $row['tag'];
				}
				$this->tags_string = $this->toString($this->tags_array);
			}
		}
		
	}
	
	public function toString($array){
		return implode(',', trim($array));
	}
	
	public function toArray($string){
		return array_map('trim', explode(',', $string));
	}
	
	public function save(){
		global $DB_PDO;
		//delete
		$std  = $DB_PDO->prepare("DELETE FROM `tags` WHERE `imageId` = :imageId");
		$std->bindValue(':imageId', $this->imageId);
		$std->execute();
		//insert ignore
		$std  = $DB_PDO->prepare("INSERT IGNORE INTO `tags` (`imageId`, `tag`) VALUE (:imageId, :tag)");
		$std->bindValue(':imageId', $this->imageId);
		foreach( $this->tags_array as $tag ){
			$std->bindValue(':tag', $tag);
			$std->execute();
		}
	}
	
	public static function getAllImageIdByTag($tag){ 
		global $DB_PDO;
		$array_imageId = array();
		$std  = $DB_PDO->prepare("SELECT imageId FROM `tags` WHERE `tag` = :tag");
		$std->bindValue(':tag', $tag);
		$std->execute();
		if( $std->rowCount() > 0  ){
			while($row = $std->fetch(PDO::FETCH_ASSOC)){
				$array_imageId[] = $row['imageId'];
			}
		}
		return $array_imageId;
	}
	
	public static function getAllTags(){ 
		global $DB_PDO;
		$array_tags = array();
		$std  = $DB_PDO->prepare("SELECT DISTINCT(tag) AS tag FROM `tags`");
		$std->execute();
		if( $std->rowCount() > 0  ){
			while($row = $std->fetch(PDO::FETCH_ASSOC)){
				$array_tags[] = $row['tag'];
			}
		}
		return $array_tags;
	}
	
	public static function generateTagLink($tag){
		return 'tag-'.str_replace(' ','-',$tag);
	}
	
	public function getTagLinks(){
		$tagLinks_array = array();
		foreach( $this->tags_array as $tag ){
			$tagLinks_array[$tag] = '/'.self::generateTagLink($tag);
		}
		return $tagLinks_array;
	}
	
}