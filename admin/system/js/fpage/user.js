	var complete = function(id, file, response) {
		console.log(id + " " + file + " " + response);
		var path = JSON.parse(response);
		
		var img = new Image;
		img.src = path + "?" + escape(new Date());
		img.onload = function() {
			// Static column width setting
			var colWidth = 282;
			
			// Find old structure so JS works.	
			$('div#crop_empty').replaceWith('<img class="thumb">');
			
			var wrapperHeight = $('div#crop_wrapper').height();
			$('div#crop_wrapper').height('auto');
			
			var ratio = img.width/colWidth;
			
			$('img.thumb').width(282)
										.height(img.height/ratio);

			$('img.thumb').attr('src', img.src);

			$('#photo_value').val(path);
		};
	}
	
	$(document).ready(function() {
		$('textarea#bio').markItUp(myMarkdownSettings);
		
		var filetypes = ["jpg", "jpeg", "png", "gif"];
		uploader('user_photo', 10, 'Upload photo', 
						 'user_photo', complete, filetypes, false, true);

	});