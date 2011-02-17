	$(document).ready(function() {
		var projectsPath = '/admin/projects/';

		// Set the rich text editor settings and bind it to the textarea.	
		// Then update the blurb counter.					
		var markItUpSettings = { nameSpace: 'markdown' };
		$('textarea#description').markItUp(myMarkdownSettings);
		
		updateCounter('textarea#blurb', 'blurb', 500, 'blurb_counter');

		// Create the thumbnail uploader.
		// We attach a date to the end of the image source because the 
		// filename never changes, and so the browser uses its cache and 
		// never shows the new image.
		var thumbComplete = function(id, file, response) {
													var img = new Image;
													img.src = response.path + "?" + escape(new Date());
													img.onload = function() { thumbLoader(img, response.path); };
												}
		var filetypes = ["jpg", "jpeg", "png", "gif"];
		
		uploader('thumbnail', $('input#pid').val(), 'Upload thumbnail', 
						 'upload_thumb', thumbComplete, filetypes, false, true);
						 
		uploader('uploader', $('input#pid').val(), null,
						 'add_photo', appendImageToPage, filetypes, true, true);

						 
		// Smart-value switching for content-editable title H1.
		$('h1#title').focus(function() {
			if ($(this).text() == 'Project Title') {
				$(this).text('');
			}
		});
		
		$('h1#title').blur(function() {
			if ($(this).text() == '') {
				$(this).text('Project Title');
			}
		});
				
		// Builds the fields for the current Foundation if we are editing.
		// If there is no Foundation, display the Foundation alert.
		if ($('input#foundations').val() != '') {
			buildFoundation($('input#foundations').val(), $('#ffields'));
		}	else {
			showAlert();
		}	
		
		
/*
		var masonryWidth = $('ul#items li.item:first-child').width();
		$('ul#items').masonry({
		  singleMode: true, 
		  itemSelector: '.item' 
		});
*/
		 
		// Sortable images
		$('ul#items').sortable({
			forcePlaceholderSize: true,
			placeholder: "placeholder",
			start: function() {console.log("Start")},
			stop: function(e, ui) { reorderImages(e, ui); }
		});
		
		// If the date month is filled in, we should make it not the placeholder color. 
		// It defaults to the placeholder color since the input is disabled.
		if ($('input.fmonth').val() != 'Select a month') {
			$('input.fmonth').css('color', '#000000');
		}
		
	/** 
	 * Foundation Interaction
	 * Add Field 
	 *
	 */
		 $('.fsselect_add_btn').live('click', function() {
		 	if ($('input#foundations').val() != '' && !$('#fields').is(':visible')) {
		 		$('#fields').show();
			 	$.post('/admin/processor.php',
			 		{
			 			foundation: $('input#foundations').val(),
			 			mode: 'field_list'
			 		}, function(data) {
						fields = JSON.parse(data);
						var html = '';	
			 			for (i in fields) {
			 				html += '<li data-fid="' + fields[i].id + '">' + fields[i].label + '</li>';
			 			}
			 			$('ul#field_list').html(html);
			 		}
			 	);
			} else if ($('#fields').is(':visible')) {
				$('#fields').hide();
			} else {
				var toModal = [];
				toModal['type'] = 'text';
				toModal['value'] = 'You have to choose a Foundation before you can add additional fields.';
				openModalDialog(400, 72, toModal);
			}
		 });

	/** 
	 * Foundation Interaction
	 * Saver 
	 *
	 * Sets the default save state of the project, and tests on multiple
	 * events whether the save state has changed
	 *
	 */
		var saveable = false;
		var saver = '<div class="save"></div>';
		
		// Set the default save state.
		saveable = isSaveable(saveable, saver);
	
		// On every keypress, we should determine saveability.
		// Repeat for smallSelect selections and textarea blurs.
		$(document).keypress(function() {
			saveable = isSaveable(saveable, saver);
		});
		
		$('li.fsselect_selectable').live('click', function() {
			saveable = isSaveable(saveable, saver);
		});
		
		$('input, textarea').live('blur', function() {
			saveable = isSaveable(saveable, saver);
		});
		
		// When the save button is clicked, we should save the data of the
		// current page and redirect to the Projects page.
		$('.save').live('click', function(e) {
			save(prepareInfo(), function() {
				$('.fbtn.save').addClass('saved');
				setTimeout(window.location.href = projectsPath, 1250);
			});
		});
		
		// When the delete button is clicked, we alert the user of the weight
		// of their action in a modal dialog, and continue if necessary.
		$('.delete').click(function() {
			console.log("Deleting project...");
			// Get information about the project and prepare the modal dialog.
			var id = $('input#pid').val();
			var title = $('h1#title').text();
			
			var str = "You are about to delete <span class='highlight'>" + title + "</span> permanently. This action cannot be undone.<br><br>Are you sure you want to continue?";
				
			// Setup the array to carry the modal dialog content.
			var content = [];
			content['type'] = 'text';
			content['value'] = str;

			// Setup the actions for modal dialogs.
			var actions = [];
			actions['primary'] = 'save';
			actions['cancel']  = 'del';
		
			// Setup the onComplete function for the modal dialog.
			var complete = function() {		
				$.post(
					"/admin/processor.php", 
					{ mode:  'delete_project', 
						id: 	 id
					}, function(data) {
						setTimeout(window.location.href = projectsPath, 1250);
					}
				);
			};
			openModalDialog(400, 90, content, actions, null, complete);
		});


	/** 
	 * Foundation Interaction
	 * Hidden Foundation Field
	 *
	 * This hidden input helps us quickly switch back to the 
	 * Information page and load foundations.
	 *
	 * Listens for clicks on the Foundation dropdown and updates its
	 * own value.
	 *
	 */
		$('input#foundations').siblings('ul').children('li').live('click', function() {
			$('input#foundation_value').val($('input#foundations').val());
		});	
		
	/**
	 * Foundation Interaction
	 * Content Selector
	 *
	 * Listens for clicks on thumbnails to make load larger image.
	 *
	 */
		$('#items .item').live('click', function() {
			var width = $(window).width();
			var height = $(window).height();
			
		 	var iid = $(this).children('img').attr('data-id');
			var windowWidth, windowHeight, newImgWidth, newImgHeight;
			
		 	var fullImg = new Image;
		 	fullImg.src = $(this).children('img').attr('src');
		 	
		 	$(fullImg).bind('load', function(e) {
				var imgWidth  = this.width;
				var imgHeight = this.height;
				var imgRatio  = imgWidth / imgHeight;
				
				newImgWidth = width-515;
				newImgHeight = Math.round(newImgWidth / imgRatio);
				
				windowWidth  = width-100;
				
				if (newImgHeight > $(window).height()) {
					newImgHeight /= 2;
					newImgWidth = Math.round(newImgHeight*imgRatio);
					windowWidth = newImgWidth+415;
				}
				
				if (newImgHeight+50 < 400) {
					windowHeight = 400;
				} else {
					windowHeight = newImgHeight+50;
				}
		 	});
		 	
		 	// Setup the actions for modal dialogs.
			var actions = [];
			actions['primary'] = 'save';
			actions['cancel']  = 'del';
		 	
			var toModal = [];
			toModal['type']  = 'url';
			toModal['value'] = '/admin/projects/image.php?i=' + iid + 
												 '&w=' + newImgWidth + 
												 '&h=' + newImgHeight;
			
			openModalDialog(windowWidth, windowHeight, toModal, actions, null, function() {}); 
		});
		
		
	/**
	 * Foundation Interaction
	 * Content Deleter
	 *
	 * Listens for clicks on the delete button in the content lightbox.
	 *
	 */	
		 $('#image_col1 .info .delete').live('click', function() {	
		 	console.log("Deleting...");  		  	
	  	// Create the string to insert into the modal dialog based
			// on how many projects are being deleted.
			var str = '<span>Are you sure?</span>';
			
			// Create the confirm and cancel buttons.
			var buttons;
			buttons  = '<span class="confirm">Yes</span>';
			buttons += '<span class="cancel">No</span>';
					
			// Put together the string and append it after the delete button.
			var html = '<div id="mini_modal"><div id="mini_modal_arrow"></div><div id="mini_modal_content">' + str + buttons + '</div></div>';
			$(this).after(html);
		});
		
		$('#mini_modal_content .confirm').live('click', function() {
			// Get the image ID so we know what to delete.
	  	var id = $('#image_col1 img').attr('data-id');
	  	
			$.post(
				"/admin/rpc/delete_image.php", 
				{ id: id }, function(data) {
					console.log(data);
					var response = JSON.parse(data);
					if (response.type == 1) {
						$('#items .item img[data-id="'+id+'"]').parent().remove();
						closeDialog();
					} else {
						$('#mini_modal_content').html('<p>' + response.text + '</p>');
						setTimeout(function() { $('#mini_modal').remove() }, 3000);
					}
			});
		});
		
		$('#mini_modal_content .cancel').live('click', function() {
			$('#mini_modal').remove();
		});
			/*
	
	  	
			// Setup the onComplete function for the modal dialog.
			var complete = function() {		
				$.post(
					"/admin/delete_image.php", 
					{ id: id }, function(data) {
						console.log(data);
						$('#items .item img[data-id="'+id+'"]').parent().remove();
					});
					
				// Remove the project elements from the visible display,
				// and then empty the selected array.
			};
*/
	
	function showPreview(coords) {
		var src = $('#image_col1 img').attr('src');
		
		var sw = $('#image_col1 img').width();
		var sh = $('#image_col1 img').height();
		
		var cw = $('#cw').val();
		var ch = $('#ch').val();
		
		var rx = sw / coords.w;
		var ry = sh / coords.h;
		
		if ($('#crop #preview').length < 1) {
			$('#crop').prepend('<div id="preview"><img src="' + src + '"></div>');
			$('#crop_empty').remove();
		}
		
		console.log(sw + " " + sh);
		console.log(coords);
		console.log(rx + " " + ry);
		
		$('#crop #preview img').css({
			width: Math.round(rx * sw) + 'px',
			height: Math.round(ry * sh) + 'px',
			marginLeft: '-' + Math.round(rx * coords.x) + 'px',
			marginTop: '-' +  Math.round(ry * coords.y) + 'px'
		});
	};

	/**
	 * Foundation Interaction
	 * Crop Listener
	 *
	 * Listens for clicks on the crop button.
	 *
	 */
	$('#crop_btn').live('click', function(e) {
		if ($(this).hasClass('selected')) {
	 		$(this).removeClass('selected');
	 		if (jcrop_api) {
		 		jcrop_api.destroy();
	 		}
	 	} else {
			$(this).addClass('selected');
			
			var fw = $('.image_width').text();
			var fh = $('.image_height').text();
			
			var cw = $('#cw').val();
			var ch = $('#ch').val();
			
			var sw = $('#image_col1 img').width();
			var sh = $('#image_col1 img').height();
			
			var fratio = calcAspectRatio(fw, fh);
		 	var cratio = calcAspectRatio(cw, ch);
			var scale  = calcScale(fw, $('#image_col1 img').width());
			
			var size = new Object;
			var crop = new Object;
		 	
		 	// !! BUG
		 	// Something here is scaling wrong.
		 	
		 	if ($('#w').val() < 1 || $('#h').val() < 1) {
				crop.x  = (sw/2) - (cw/2);
				crop.y  = (sh/2) - (ch/2);
				crop.x2 = parseInt(crop.x) + parseInt(cw);
				crop.y2 = parseInt(crop.y) + parseInt(ch);
		 	} else {
		 		crop.x  = $('#x').val() / scale;
		 		crop.y  = $('#y').val() / scale;
		 		crop.x2 = $('#x2').val() / scale;
		 		crop.y2 = $('#y2').val() / scale;
		 	}
			
			boxSize = new Object;
			boxSize.w = sw;
			boxSize.h = sh;
			
			setCrop('#image_col1 img', cratio, showPreview, crop, boxSize);
		}
	});
			
	/** 
	 * Foundation Interaction
	 * Segmented Control :: Information
	 *
	 * Parses the form information and sends to the processor 
	 * for storage.
	 *
	 */
		 
		// Prepare the information for submission.
		function prepareInfo() {			
			// Put the content-editable H!'s content in a hidden input.
			$('input#project_title').val($('h1#title').text());
							
			var data = [];
			$('#ffields input.finput').each(function() {
				// Store the field name for quick retrieval.
				var field = $(this).attr('name');

				// If the parent is an fstinput, we need to gather the 
				// values from the list.
				if ($(this).parent().hasClass('fstinput')) {
					var list = $(this).next().next();
					var values = [];
					
					list.children().each(function() {
						values.push($(this).text());
					});
					
					var string = JSON.stringify(values);
					$('input.fields[title="' + name + "']").val(string);
					
				} else if ($(this).parent().hasClass('finput')) {
					// Otherwise, if the parent is a standard input, 
					// we'll just put the value in the variable.
					var values = $(this).val();
					$('input.fields[title="' + name + "']").val(values);
				}
				
				// Create the object that will contain the key/value information.
				var pair = new Object;
				pair.field = $(this).attr('name');
				pair.value = values;
				
				// Stringify the information and push it into the
				// array we will push to the processor.
				var pairJson = JSON.stringify(pair);
				data.push(pairJson);
			});
			
			// Return the stringified data.
			return JSON.stringify(data);
		}

		// Information POST request
		// !! FEATURE
		// !! this function should check the temp folder for new thumbnails
		// !! and other content and copy if necessary. how do we determine 
		// !! content created by this session? maybe with some sort of session
		// !! ID. otherwise, we should empty the temp folder.
		function save(json, callback) {
			var action = '/admin/rpc/create_project.php';
		
			$.post(
				action,
				{ id:								$('input#pid').val(),
					title: 				 		$('input#project_title').val(), 
					foundation:		 		$('input#foundations').val(),
					blurb: 				 		$('textarea#blurb').val(),
					desc: 				 		$('textarea#description').val(),
					client: 			 		$('input#client').val(),
					thumbnail_value:  $('img.thumb').attr('src'),
					month:						$('input#datepicker_month').val(),
					year:							$('input#datepicker_year').val(),
					fdata:						json,
					image_order:			JSON.stringify(compileImageOrder())
				}, function(response) {
					console.log(response);
					var data = JSON.parse(response);
					// If there is an error, throw a modal dialog.
					if (data.type == 0) {
						var toModal = [];
						toModal['type'] = 'text';
						toModal['value'] = data.text;
						
						openModalDialog(400, 72, toModal); 
					} else {
						section = 'content';
						callback(data);
					}
				}
			);			
		}
	});