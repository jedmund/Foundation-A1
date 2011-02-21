	/** 
	 * Helper function that instantiates a Jcrop object.
	 *
	 *
	 */
	 function cropper(element, aspectRatio, onSelect, setSelect, boxSize) {
		if (!isEmpty(setSelect) && !isEmpty(boxSize)) {
			jcrop_api = $.Jcrop(element, { 
				aspectRatio: aspectRatio,
				boxHeight:	 boxSize.h,
				boxWidth:		 boxSize.w,
				onSelect: 	 onSelect,
				onChange:		 onSelect,
				setSelect: 	 [setSelect.x, setSelect.y, setSelect.x2, setSelect.y2],
			});
		} else {
			jcrop_api = $.Jcrop(element, {
				aspectRatio: aspectRatio, 
				onSelect: 	 onSelect
			});				
		}
	}	

	function thumbLoader(img, filepath) {
		// Static column width setting
		var colWidth = 282;
		
		// Find old structure so JS works.
		$('div#crop_empty').replaceWith('<img class="thumb">');
		
		var wrapperHeight = $('div#crop_wrapper').height();

		$('div#crop_wrapper').height('auto');
		$('img.thumb').width(img.width)
									.height(img.height);
		
		$('img.thumb').attr('src', img.src);
		
		// Original width and height.
		var ow = $('img.thumb').width();
		var oh = $('img.thumb').height();
		
		// Scale between original image width and column width.
		var scale = ow/colWidth;
		//console.log(img.src + " " + ow + " " + oh + " " + scale);
		
		// Scaled width and height.
		var sw = ow/scale;
		var sh = oh/scale;
		
		// User-defined width and height for thumbnails.
		var cw = $('#cw').val();
		var ch = $('#ch').val();
		
		var ratio = calcAspectRatio(cw, ch);
		
		// Default selection region. This can be smarter.
		var setSelect = new Object;
		setSelect.x  = (sw/2 - sh/3);
		setSelect.y  = (sh/2 - sh/3);
		setSelect.x2 = (sw/2 + sw/3);
		setSelect.y2 = (sh/2 + sh/3);
		
		// Size for Jcrop to scale to.
		var boxSize = new Object;
		boxSize.w = sw;
		boxSize.h = sh;
		
		if (ow > cw || oh > ch) {
			cropper('img.thumb', ratio, updateCoords, setSelect, boxSize);
			
			html  = "<div id='crop_info'>";
			html += "<div class='thumb_uploader_btn' id='crop'>Crop this Image</div>";
			html += "<input type='hidden' id='x' name='x' value='0'>";
			html += "<input type='hidden' id='y' name='y' value='" + truncate(oh/2-oh/4) + "'>";
			html += "<input type='hidden' id='w' name='w' value='" + truncate(ow/2+ow/4) + "'>"; 
			html += "<input type='hidden' id='h' name='h' value='" + truncate(oh-oh/2) + "'>";
			html += "</div>";
			
			$('.thumb_uploader_btn').hide();
			$('.uploader').append(html);
			
			$('#crop').live('click', function() { 
				$.post(
					"/admin/rpc/crop_thumb.php", 
					{	src: filepath,
						x: $('#x').val(),
						y: $('#y').val(),
						w: $('#w').val(),
						h: $('#h').val(),
						cw: cw,
						ch: ch,
					}, function(data) {
						var path = JSON.parse(data);
						
						// Set the thumbnail value in its hidden input.
						$('input#thumbnail_value').val(path);
						$('img.thumb').attr('src', path + "?" + escape(new Date())).css('height', 'auto');
						console.log(path);
						$('div#crop_wrapper').height(wrapperHeight);

						// Reset the CSS and re-enable the upload button.
						$('.thumb_uploader_btn').show();
	
						// Remove Jcrop
						
						// !! NOTE !!
						// This test can be written better
						if ($('img.thumb').size() > 1) {
							$('img.thumb:first-child').remove();
						}
						$('#crop').remove();
						$('#cropinfo').hide();
						$('.jcrop-holder').before($('.jcrop-holder > img').unbind().css({opacity:'',position:'',display:''}));
						$('.jcrop-holder').remove();
					} 
				);
			});
		} else if (ow == cw && oh == ch) {
			$('div#crop_wrapper').height(wrapperHeight);
			$('img.thumb').attr('src', img.src);
			$('img.thumb').width(colWidth);
			$('img.thumb').height(sh);
			// Generate system thumb.
		}
	}
	
	
	/**
	 * Creates an uploader object.
	 *
	 * @param				string				The #id of the DOM element to attach to.
	 * @param				string				The name of the PHP RPC, without extension.
	 * @param				string				The string to display on the button, if applicable.
	 * @param				function			The function to execute on completion.
	 * @param				array					An array of allowed file extensions.
	 * @param				int						The Project ID, if applicable.
	 **@param				array					An array for additional params {name: value}
	 * @param				boolean				Boolean, support multiple uploads?
	 * @param				boolean				Boolean, run in debug mode?
	 *
	 */
		function uploader(id, pid, string, processor, complete, filetypes, multiple, debug) {
			var uploader = new qq.FileUploaderBasic({
				action:							'/admin/rpc/' + processor + '.php',
				allowedExtensions:	filetypes,
				button:							document.getElementById(id),
				debug:							debug,
				multiple:						multiple,
				params:							{ pid: pid },		
				string:							string,	
				onComplete:					function(id, file, response) {
															complete(id, file, response);
														}
			});
		}
		
	/**
	 * When adding images to a project, this callback function is
	 * used to instantly display the images on the page.
	 *
	 * @param				response			The path and ID of the image returned.
	 *
	 */
	 	function appendImageToPage(id, file, response) {
	 		console.log(response.path);
	 		
	 		// Check if the response path has a leading slash.
	 		var string = response.path;
	 		if (string.substr(0, 1) == '/') {
	 			string = string.substr(1);
	 			response.path = '../../' + string;
	 		} else {
	 			string = '/' + string;
	 		}
	 		
	 		// Check if we're displaying the no content tag and hide it.
	 		if ($('p#nocontent').is(':visible')) {
	 			$('p#nocontent').hide();
	 		}
	 		
	 		var item  = "<li class='item'></li>";
	 		
	 		var image = "<img data-id='" + response.id + "' data-sequence='" + ($('li.item').size()+1) + "' src='/" + string + "'>";
	 		$('ul#items').append(item);
	 		$('li.item:last-child').append(image);
	 		
	 		// reorder
	 	}