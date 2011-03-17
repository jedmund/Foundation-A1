<?php
 	require_once(LIB_PATH.DS."init".DS."config.php");

	class Twitter {
	    const CACHE_FNAME = 'twitter.status';
	    public $cache_path;

	    function __construct($id, $anchors=true) {
        $this->id = $id;
        $this->anchors = $anchors;
        $this->tweets = $this->load();
        $this->cache_path = PUBLIC_PATH.DS."lib".DS."classes".DS."parsel".DS."plugins".DS."twitter".DS.self::CACHE_FNAME;
	    }
	    
	    private function load() {	    
        $return = '';
        
        $time = filectime($this->cache_path);
        $life = (time() - $time);

        if ($life < (10*60) && $life > 0) {
          $return = json_decode(file_get_contents($cache));
        }

        return $return;
	    }
	    
	    private function save() {
	      file_put_contents($this->cache_path, json_encode($this->tweets));
	    }
	
	    function get($num=1) {	        
        if (empty($this->tweets)) {
          $tweets = $this->fetch($num);
          $this->tweets = $tweets;
          
          if (!is_string($tweets)) {
	          $this->save();
	         }
				} else {
					$tweets = $this->tweets;
				}
				
				if (!is_string($tweets)) {
					$tweets = $this->to_array($tweets, $num);
				}
					
        return $tweets;
	    }
	    
	    function to_array($objs, $num) {
	    	$array = array();
	    	$i = 0;
	    	
				foreach ($objs as $obj) {
					if ($i < $num) {
						$tweet = array();
						$tweet['id'] = $obj->id;
						$tweet['text'] = $obj->text;
						$tweet['date'] = $obj->date;
						$tweet['href'] = "http://twitter.com/" . $this->id . "/status/" . $obj->id;
						$tweet['type'] = 'twitter';
						$array[] = $tweet;
						$i++;
					}
				}
				
				return $array;
	    }

	    private function fetch() {
	        // Initiate cURL
	        $c = curl_init();
	        curl_setopt($c, CURLOPT_URL, "http://twitter.com/statuses/user_timeline/" . $this->id . ".xml");
	        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($c, CURLOPT_HEADER, 1); 

	        
	        // Execute cURL
	        $src = curl_exec($c);
	        list($header, $body) = explode("\r\n\r\n", $src, 2);  
	        curl_close($c);
	        
	        // Extract all tweets from XML.
	        preg_match_all('/<id>(.*)<\/id>/', $body, $id);
	        preg_match_all('/<text>(.*)<\/text>/', $body, $t);
	        preg_match_all('/<created_at>(.*)<\/created_at>/', $body, $d);
	        
	        $aid = array_shift($id);
	        $t = array_shift($t);
	        $ad = array_shift($d);

				 	// Remove UIDs from ID list
 	        $id = array();
	        for ($i = 0; $i < count($aid); $i++) {
	        	if (strlen($aid[$i]) > 20) {
	        		$id[] = $aid[$i];
	        	}
	        }

	        // Remove user account creation dates from ID list by
	        // only inserting the even dates into a new array.
	        $d = array();
        	for ($i = 0; $i < count($ad); $i+=2) {
						$d[] = $ad[$i];
					}

	        // Make objects
	        $tweets = array();
	        $count = count($t);
	        
	        for ($i = 0; $i < $count; $i++) {
	        	$tweet = new stdClass();
	        	$tweet->id = str_replace(array("<id>", "</id>"), "", $id[$i]);
	        	$tweet->text = str_replace(array("<text>", "</text>"), "", $t[$i]);
	        	
	        	// Add anchors
	        	if ($this->anchors) {
		        	$tweet->text = preg_replace("#[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]#", "<a href=\"\\0\">\\0</a>", $tweet->text);
	        	}
	        	
	        	// Date as relative time
	        	$tweet->date = nicetime(str_replace(array("<created_at>", "</created_at>"), "", $d[$i]));
	        	
	        	if (substr($tweet->text, 0, 1) != '@') {
	        		$tweets[] = $tweet;
	        	}
	        }
	        
	        // Check for errors
	        if (empty($tweets)) {
	        	return "There was an error connecting to Twitter or your latest tweets are all mentions.";
	        } else {
		        return $tweets;
		      }
	    }
	}

?>