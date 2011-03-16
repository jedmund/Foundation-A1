<?php
	/**
	 * Parsel R2
	 *
	 * sequencer.php
	 * Sequencer handles tag order and sequencing within a layout file.
	 *
	 */
	 
	 class Sequencer {
	 	// All tags in the current page.
	 	private $tags;
	 	
	 	// The current index and tag.
	 	private $index;
	 	private $tag;
	 	
	 	// The previous and next tags.
	 	private $prev;
	 	private $next;
	 	
	 	/** 
	 	 * Constructor for a sequencer.
	 	 *
	 	 * @param			$tags				All of the tags as an array.
	 	 *
	 	 */
	 	public function __construct($tags) {
	 		$this->tags = $tags;
	 		$this->index = 0;
	 		
	 		$this->prev = false;
	 		if ($this->count() > 0) {
		 		$this->tag = $this->tags[$this->index];
		 		$this->next = $this->tags[$this->index + 1];
			}
	 	}
	 	
	 	public function get_tags() {
	 		return $this->tags;
	 	}
	 	
	 	public function set_tags($tags) {
	 		$this->tags = $tags;
	 	}
	 	
	 	public function get_tag() {
	 		return $this->tag;
	 	}
		
		public function set_tag($tag) {
			$this->tag = $tag;
		}
		
		public function get_index() {
			return $this->index;
		}
		
		public function set_index($index) {
			$this->index = $index;
		}
		
		public function get_prev() {
			return $this->prev;
		}
		
		public function set_prev($prev) {
			$this->prev = $prev;
		}
		
		public function get_next() {
			return $this->next;
		}
		
		public function set_next($next) {
			$this->next = $next;
		}
		
		public function count() {
			return count($this->tags);
		}
		
		/** 
		 * This function sets the index to the given index.
		 * It also returns the tag at that index. If the index
		 * is out of bounds, then it should return false and
		 * stay at the current index.
		 *
		 * @param				int					The index to jump to.
		 * @return			string			The tag at the given index.
		 * @return			boolean			False if out of bounds
		 *
		 */
		public function jump($index) {
			$tag = false;
			
			if ($index >= 0 && $index < $this->count()) {
				$this->index = $index;
				$this->tag = $this->tags[$this->index];
				$this->prev = ($this->index > 0) ? $this->tags[$this->index-1] : false;
				$this->next = ($this->index < $this->count()-1) ? $this->tags[$this->index+1] : false;
				
				$tag = $this->tag;
			}
			
			return $tag;
		}
		 
		/** 
		 * This function decreases the sequencer's pointer by one. ( • 1< • )
		 * It then resets the convenience variables for easy access to tags.
		 *
		 * @return			boolean			Returns the new index or false if no moves
		 														are possible.
		 *
		 */
		public function retreat() {
			// If the index is above zero, we can decrease the
			// index by one. We also re-set all of the convenience instance
			// variables.
			$index = false;
			if ($this->index > 0) {
				$this->index--;
				$this->next = $this->tag;
				$this->tag  = $this->prev;				
				$this->prev = ($this->index > 0) ? $this->tags[$this->index-1] : false;

				
				$index = $this->index;
			}
			
			return $index;
		}
		
		/** 
		 * This function increases the sequencer's pointer by one. ( • >1 • )
		 * It then resets the convenience variables for easy access to tags.
		 *
		 */	
		public function advance() {
			// If the index is less than the total count of tags, we can 
			// increase the index by one. We also re-set all of the convenience 
			// instance variables.
			$index = false;
			if ($this->index < $this->count()) {
				$this->index++;
				$this->prev = $this->tag;
				$this->tag = $this->next;
				$this->next = ($this->index < $this->count()-1) ? $this->tags[$this->index+1] : false;
				
				$index = $this->index;
			}
			
			return $index;
		}
		
		/** 
		 * This function returns the index of the given tag in the sequence.
		 * If the tag doesn't exist, then it will return false.
		 *
		 * @param				$tag				The tag to search for.
		 * @return			int					The integer index of the tag.
		 * @return			boolean			False if the tag was not found.
		 *
		 */
		public function index_of($tag) {
			$index = false;
			
			if (in_array($tag, $this->tags)) {
				$index = array_search($tag, $this->tags);
			}
			
			return $index;
		}
		
		/**
		 * This function returns the tag at the given index of the sequence.
		 * If the index is out of bounds, it will return false.
		 *
		 * @param				$index			The index to look at.
		 * @return 			string			The tag at the index.
		 * @return			boolean			False if the index is out of bounds.
		 *
		 */
		public function tag_at($index) {
			$tag = false;
			
			if ($index >= 0 && $index < $this->count()) {
				$tag = $this->tags[$index];
			}
			
			return $tag;
		}
	}
