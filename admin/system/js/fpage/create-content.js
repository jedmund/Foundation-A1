	var origW;
	var origH;
	var scale;
	var jcrop_api;
	var select;
	var size = [];
	var crop = [];
	var coords = [];
	
	$(window).resize(function() {
		if ($('#viewer img') && origW) {
			scale = calcScale(origW, $('#viewer img').width());
		}
	});
	
	$(document).ready(function() {
		/**
		 * Foundation Interaction
		 * Add Content
		 *
		 * Listens for clicks on the add content item.
		 *
		 */
		  $('#items .item.add').live('click', function() {
		  	var toModal = {};
		   	toModal.type = 'url';
		   	toModal.value = '/admin/projects/modal_addcontent.php';
			 
				var pid = $('#pid').val();
			 
			 	var actions;
			 	var loadFunc = function() {
				createUploader('addphoto', 'Upload Photo from Computer', 'add_photo', pid, '/admin/processor.php', appendImage);
			 	}
			 	
			 	openModalDialog(400, 335, toModal, actions, loadFunc); 
		 });
	
		/**
		 * Foundation Interaction
		 * Delete Content
		 *
		 * Listens for clicks on the content delete button.
		 *
		 */
		  $('#viewer .delete').live('click', function() {
		  	var type;

		  	if ($('#viewer').children('img').size() > 0) {
		  		type = "image";
		  	} else {
		  		type = "video";
		  	}
		  	
		  	// Create the string to insert into the modal dialog based
				// on how many projects are being deleted.
				var str = "You are about to delete this " + type + " permanently. <br> Are you sure you want to continue?";
					
				// Setup the array to carry the modal dialog content.
				var content = [];
				content['type'] = 'text';
				content['value'] = str;
		
				// Setup the actions for modal dialogs.
				var actions = [];
				actions['primary'] = 'save';
				actions['cancel']  = 'del';
				
		  	var id = $('#viewer img').attr('data-id');
		  	
				// Setup the onComplete function for the modal dialog.
				var complete = function() {		
					$.post(
						"/admin/processor.php", 
						{ mode: 'delete_content', 
							type: type,
							id:		id
						}, function(data) {
							console.log(data);
							$('#items .item img[data-id="'+id+'"]').parent().remove();
							$('#metadata').hide();
							$('#viewer').hide();
						});
						
					// Remove the project elements from the visible display,
					// and then empty the selected array.
				};
									
				openModalDialog(400, 90, content, actions, null, complete);
		  });
	
		/**
		 * Foundation Interaction
		 * Content Selector
		 *
		 * Listens for clicks on thumbnails to make load larger image.
		 *
		 */
			$('#items .item:not(.add)').live('click', function() {		
			 	var src = $(this).children('img').attr('src');
			 	var iid = $(this).children('img').attr('data-id');
			 	var caption = $(this).children('img').attr('title');
			 	var link = $(this).children('img').attr('data-link');
			 	var rawCoords = $(this).children('img').attr('data-coords');
				if (rawCoords != '' && rawCoords != 'undefined') {
					console.log(rawCoords);
					coords = JSON.parse(rawCoords);
				}
			 	
			 	// Load an object so that we can get the original width
			 	// and height of the image.
			 	var img = new Image();
			 	img.src = src;
	
				$(img).bind('load', function (e) {
					origW = this.width;
					origH = this.height;
				});
	
				$(img).attr('data-id', iid).load(function(e) {
					if (e != undefined) {
						if ($('#viewer img').size() > 0) {
							$('#viewer img').replaceWith(this);
						} else {
							$('#viewer').append(this);
						}
						
						scale = calcScale(fw, $('#viewer img').width());
					}
				});
	
				// Reset the metadata text.			
				$('#caption p').text('');
				$('#linker a').text('');
				$('#linker a').attr('href', '');
	
				// If there is a caption, use it, otherwise, hide the box.
			 	if (caption != '') {
				 	$('#caption p').text(caption);
				}
				
				// If there is a link, use it, otherwise, hide the box.
				if (link != '') {
					$('#linker a').text(link);
					$('#linker a').attr('href', link);
					$('#linker a').show();	
				} else {
					$('#linker a').hide();
				}
				
				// Set the hidden coordinate values.
					$('#x').val(coords.x);
					$('#x2').val(coords.x);
					$('#y').val(coords.y);
					$('#y2').val(coords.y2);
					$('#w').val(coords.w);
					$('#h').val(coords.h);
				
				// Set the appropriate display values.
			 	$('#viewer').css('display', 'table-cell');
			 	$('#metadata').css('display', 'table');
			});
	
		/**
		 * Foundation Interaction
		 * Controls
		 *
		 * Control functionality.
		 *
		 */
			// Listens for clicks on the caption button.
			$('li.caption').live('click', function(e) {
				e.preventDefault();
				
				var caption = '';
				
				if ($('#caption p').text() != '') {
					caption = $('#caption p').text();
				}
				
				$('#caption p').replaceWith('<textarea name="caption">' + caption + '</textarea>');
				$('#caption textarea').focus();
			});
			
			// When the textarea is blurred, transform back into a <p>
			$('#caption textarea').live('blur', function() {
				var caption = $(this).val();
				$(this).replaceWith('<p>' + caption + '</p>');
				if (caption == '') {
					$('#caption p').hide();
				}
			});
			 
			// Listens for clicks on the link button.
			$('li.link').live('click', function(e) {
				e.preventDefault();
				
				var link = '';
				
				if ($('#linker a').attr('href') != '')	 {
					link = $('#linker a').attr('href');
				}	
				
				$('#linker a').replaceWith('<input name="linker" value="' + link + '">') 
				$('#linker input').focus();
			});
			
			// When the input is blurred, transform back into a <a>
			$('#linker input').live('blur', function() {
				var link = $(this).val();
				$(this).replaceWith('<a href="' + link + '">' + link + '</a>');
				if (link == '') {
					$('#link a').hide();
				}
			});
			 
			// Listens for clicks on the content crop button
			$('li.crop').live('click', function(e) {
				e.preventDefault();
	
				var w = $('#viewer img').width();
				var h = $('#viewer img').height();
				var ratio = calcAspectRatio($('#thumb_w').val(), $('#thumb_h').val());
					
			 	if ($(this).hasClass('selected')) {
			 		$(this).removeClass('selected');
			 		if (jcrop_api) {
				 		jcrop_api.destroy();
			 		}
			 	} else {
			 		$(this).addClass('selected');
			 		
			 		if ($('#w').val() < 1 || $('#h').val() < 1) {
			 			var bw = $('#viewer img').width();
			 			var bh = $('#viewer img').height();
			 			var sw = parseInt($('#thumb_w').val());
			 			var sh = parseInt($('#thumb_h').val());
			 		
						crop.x = ((bw/2)-(sw/2));
						console.log(crop.x);
						crop.y = (bh/2)-(sh/2);
						console.log(crop.y);
						crop.x2 = crop.x + sw;
						crop.y2 = crop.y + sh;		 		
			 		} else {
				 		crop.x = $('#x').val()/scale;
				 		crop.y = $('#y').val()/scale;
				 		crop.x2 = $('#x2').val()/scale;
				 		crop.y2 = $('#y2').val()/scale;
					}
	
			 		size.w = origW;
			 		size.h = origH;

					setCrop('#viewer img', ratio, updateCoords, crop, size);
			 	}
			});
			
			// Listens for clicks on the save button
			$('li.save').live('click', function(e) {
				saveImage(e);				
			});
		});