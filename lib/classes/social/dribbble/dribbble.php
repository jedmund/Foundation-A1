<?php
 	require_once(LIB_PATH.DS."init".DS."config.php");

	class Dribbble {
	    const CACHE_FNAME = 'dribbble.shot';
	    public $cache_path;

	    function __construct($id) {
        $this->id = $id;
        $this->shots = $this->load();
        $this->cache_path = SLIB_PATH.DS.'dribbble'.DS.self::CACHE_FNAME;
	    }
	    
	    private function load() {	    
        $return = '';
        $cache = SLIB_PATH.DS.'dribbble'.DS.self::CACHE_FNAME;
        
        $time = filectime($cache);
        $life = (time() - $time);

        if ($life < (10*60) && $life > 0) {
          $return = json_decode(file_get_contents($cache));
        }

        return $return;
	    }
	    
	    private function save() {
	      file_put_contents($this->cache_path, json_encode($this->shots));
	    }
	
	    function get($num=1) {	        
        if (empty($this->tweets)) {
          $shots = $this->fetch();
          $this->shots = $shots;
          $this->save();
				} else {
					$shots = $this->shots;
				}
				
				$shots = $this->to_array($shots, $num);
					
        return $shots;
	    }
	    
	    function to_array($objs, $num) {
	    	$array = array();
	    	$i = 0;
	    	
				foreach ($objs as $obj) {
					if ($i < $num) {
						$shot = array();
						$shot['id'] = $obj->id;
						$shot['title'] = $obj->title;
						$shot['value'] = $obj->title;
						$shot['thumb'] = $obj->image_url;
						$shot['href'] = $obj->short_url;
						$shot['date'] = nicetime($obj->created_at);
						$shot['type'] = 'dribbble';
						$array[] = $shot;
						$i++;
					}
				}
				
				return $array;
	    }
	    	    
	    private function fetch() {
	        // Initiate cURL
	        $c = curl_init();
	        curl_setopt($c, CURLOPT_URL, "http://api.dribbble.com/players/" . $this->id . "/shots");
	        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	        
	        // Execute cURL
	        $src = curl_exec($c);
	        curl_close($c);
	        
	        // Extract all tweets from XML.
	        $dribbble = json_decode($src);
					$shots = $dribbble->shots;
					
					return $shots;
	    }
	}

?>