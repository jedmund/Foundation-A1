	/**
	 * Updates the element's counter on keyUp.
	 *
	 * @param				selector			The selector to target
	 * @param				element				The textarea to watch
	 * @param				limit					The counter limit
	 * @param				counter				The ID of the counter
	 *
	 */
	function updateCounter(selector, element, limit, counter) {
		$(selector).keyup(function() {
			//limitChars(element, limit, counter);
		});
	}
	
	/** 
	 * Checks to make sure that the user has filled out the required 
	 * information. If they have, we'll allow them to save.
	 *
	 * @param				saveable				Boolean that is true if we can save 
	 * @param				saver						The save button object
	 * @return			saveable				Boolean that is true if we can save
	 *
	 */
	function isSaveable(saveable, saver) {
		if ($('#content .col1').hasClass('info')) {
			if (!saveable) {
				if ($("h1#title").text() != "" &&
						$("input#foundations").val() != "" &&
						$("textarea#description").val() != "") {
					$('fieldset .segmented').parent().append(saver);
					saveable = true;
				}
				// However, if they change something and remove the required
				// information after the fact, they should not be able to save.
			} else {
				if ($("h1#title").text() == "" ||
						$("input#foundations").val() == "" ||
						$("textarea#description").val() == "") {
					$('fieldset .save').remove();
					saveable = false;
				} else {
					if ($(saver).length < 1) {
						$('fieldset .segmented').parent().append(saver);
					}
				}
			}
		} else if ($('#content .col1').hasClass('content')) {
			if (!saveable) {
				$('fieldset .segmented').parent().append(saver);
				saveable = true;
			}
		}
		return saveable;
	}
		
	/**
	 * Maps items into an object with content id and sequence order.
	 *
	 * @return				object			The data, mapped.
	 *
	 */
	function makeItemList() {
		// Get the children of each section.
		var items = $('ul#items').children();
		
		// Set empty variables.
		var id = 0;
		var sequence = 0;
		var data = new Object;		
		
		// Change the sequence for items and add to the
		// data object.
		for (var i = 0; i < items.length; i++) {
			id = $(items[i]).children('img').attr('data-id');
			// This is not i+1 because there is the "add content" item, which
			// is element 0. Therefore, content starts from element 1.
			sequence = i;
			
			$(items[i]).children('img').attr('data-sequence', sequence);
			data[id] = sequence;
		}
		return data;
	}
	
	/**
	 * I don't know what this function does, but it is called when
	 * you start moving a sortable item.
	 *
	 * @param					e					
	 * @param					ui
	 *
	 */
	function posStart(e, ui) {
		el = ui.item.get(0); 
		var index = $(this).children('.item').index(el); 
  } 

	/**
	 * Rewrites the order of all of the items in the sortable list.
	 * Then, saves the new order to the database.
	 *
	 * @param					e					
	 * @param					ui
	 *
	 */
	function posStop() {
		var data = JSON.stringify(makeItemList());
		
		$.post(
			"/admin/processor.php", 
			{ mode: 	  'reorder_content', 
				data:		 	data
			}, function(data) {
				//console.log(data);
			}
		);
	}
	
	/**
	 * Prepares the data for an image and then saves it to the database.
	 *
	 * @param					e					The event
	 *
	 */
	function saveImage() {
		var id = $('#image_col1 img').attr('data-id');
		var caption = $('textarea#caption').val();
		var link = $('#linker a').attr('href')

		$.ajax({
			url:  '/admin/rpc/save_image.php',
			data: ({
				id: id,
				caption: caption
			}), 
			async: false,
			type: "POST",
			success: function(data) {
				console.log(data);
			}
		});
	}

	function reorderImages(e, ui) {
		$('ul#items').children().each(function(i) {
			$(this).children('img').attr('data-sequence', i+1);
		});
		compileImageOrder();
	}
	
	function compileImageOrder() {
		var imageOrder = [];
		$('ul#items').children().each(function() {
			var array = {};
			array.iid = $(this).children('img').attr('data-id');
			array.seq = $(this).children('img').attr('data-sequence');
			
			imageOrder.push(array);
		});
		console.log(imageOrder);
		return imageOrder; 
	}