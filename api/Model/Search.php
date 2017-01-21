<?php
class Search {
    public $results = array();
    public $total_results = 0;
    public $total_pages = 0;
    
    public function __construct($searchText, $page=1, $limit=10) {
        global $DB_PDO;
        
        $offset = $limit * ($page - 1);
        
        $searchText = str_replace(' ', '* ', $searchText);
        $searchText .= '*';
        
        $SQL = "SELECT *, MATCH (`title`,`tags`,`category`) AGAINST (:search IN BOOLEAN MODE) AS 'score' 
                FROM `images` WHERE MATCH (`title`,`tags`,`category`) AGAINST (:search IN BOOLEAN MODE) 
                ORDER BY 'score' DESC 
                LIMIT :limit OFFSET :offset";
        
        $std  = $DB_PDO->prepare($SQL);
        $std->bindValue(':search', $searchText);
        $std->bindValue(':offset', $offset, PDO::PARAM_INT);
        $std->bindValue(':limit', $limit, PDO::PARAM_INT);
        if( $std->execute() ){
            if( $std->rowCount() > 0  ){
                //Get results
                $results = array();
                while( $row = $std->fetch(PDO::FETCH_ASSOC)){
                    $photo = new Photo($row);
                    //If the foto really exist return this object
                    if( file_exists($photo->getImagePathOriginal()) && file_exists($photo->getImagePathResized()) ){
                            $results[] = $photo;
                    }else{
                            //send email photos problem TODO
                            error_log('PHP LOG - (SEARCH CLASS): Photo PATH problems ->id:'.$photo->id.'  '.__FILE__.' line:'.__LINE__ ); 
                    }
                }
                $this->results = $results;
                
                //Get Total
                $SQL = "SELECT COUNT(id) AS total FROM `images` WHERE MATCH (`title`,`tags`,`category`) AGAINST (:search IN BOOLEAN MODE)";
                $std  = $DB_PDO->prepare($SQL);
                $std->bindValue(':search', $searchText);
                $std->execute();
                $row = $std->fetch(PDO::FETCH_ASSOC);
                $this->total_results = $row['total'];
                
                //Get Total Pages
                $this->total_pages = round( $this->total_results / $limit );
            }
        }else{
            throw new Exception('Error: search total debug '.print_r($std->errorInfo() ,true) );
        }
        
    }
    
    
}
