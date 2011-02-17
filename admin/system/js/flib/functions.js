	/** 
	 * Helper function for checking whether a Javascript object is empty.
	 * 
	 * @param					obj					The object to check.
	 * @return				boolean			True if the object is empty
	 *
	 */
	function isEmpty(obj) {
		for (var prop in obj) {
			if (obj.hasOwnProperty(prop)) return false;
		}
		return true;
	}

	/**
	 * Helper function that calculates the current scale of the image. 
	 *
	 * @param				origWidth			The original width of the image.
	 * @param				curWidth			The current width of the image.
	 * @return			float					The ratio as a float.
	 *
	 */
	function calcScale(origWidth, curWidth) {
		return origWidth / curWidth;
	}
	
	/** 
	 * Helper function that generates aspect ratio.
	 *
	 * @param				width					The desired width.
	 * @param				height				The desired height.
	 * @return			float					The calculated aspect ratio.
	 *
	 */
	function calcAspectRatio(width, height) {
		return (width/height);
	}
	

	/**
	 * Helper function that displays the page's alert message. 
	 *
	 * @param				selector			Optional selector for specificity
	 *
	 */
	function showAlert(selector) {
		if (selector == 'undefined') {
			$('.alert' + selector).show();
		} else {
			$('.alert').show();
		}
	}