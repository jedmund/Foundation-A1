	function truncate(value) {
	  if (value<0) return Math.ceil(value);
	  else return Math.floor(value);
	}

	function updateCoords(c) {
		$('#x').val(c.x);
		$('#x2').val(c.x2);
		$('#y').val(c.y);
		$('#y2').val(c.y2);
		$('#w').val(c.w);
		$('#h').val(c.h);
	};
 
	function checkCoords() {
		if (parseInt($('#w').val()) > 0) {
			return true;
		}
		
		alert('Please select a crop region then press submit.');
		
		return false;
	};
	
	/**
	 * Hides the dropdown menu of a bigSelect UI element.
	 *
	 * @param				elem					  The sibling of the dropdown.
	 *
	 */
	function bSelHideDropdown(elem) {
		elem.next($('.fbselect .fbselect_dropdown')).hide();
		elem.removeClass('fbtn_clicked');
	}
	
	/**
	 * Hides the dropdown menu of a bigSelect UI element when a
	 * selection was made.
	 *
	 * @param				elem					 The sibling of the dropdown.
	 *
	 */
	function bSelHideDropdownFromSelection(elem) {
		elem.hide();
		elem.removeClass('fsselect_clicked');	
	}
	
	/**
	 * Shows the dropdown menu of a bigSelect UI element.
	 *
	 * @param				elem					 The sibling of the dropdown.
	 *
	 */
	function bSelShowDropdown(elem) {
		elem.next($('.fbselect .fbselect_dropdown')).show();
		elem.addClass('fbtn_clicked');
	}
	
	/**
	 * Hides the dropdown menu of a smallSelect UI element.
	 *
	 * @param				elem					  The sibling of the dropdown.
	 *
	 */
	function sSelHideDropdown(elem) {
		elem.next().next($('.fsselect .fsselect_dropdown')).hide();
		elem.removeClass('fsselect_clicked');
	}

	/**
	 * Hides the dropdown menu of a smallSelect UI element when a
	 * selection was made.
	 *
	 * @param				elem					 The sibling of the dropdown.
	 *
	 */
	function sSelHideDropdownFromSelection(elem) {
		elem.hide();
		elem.prev().prev().removeClass('fsselect_clicked');	
	}

	/**
	 * Shows the dropdown menu of a bigSelect UI element.
	 *
	 * @param				elem					 The sibling of the dropdown.
	 *
	 */
	function sSelShowDropdown(elem) {
		elem.next().next($('.fsselect .fsselect_dropdown')).show();
		elem.addClass('fsselect_clicked');	
	}

	function addValue(elem) {
		commitValue(elem, $(elem).val());
		elem.val('');
	}
	
	function commitValue(elem, value) {
		var html = '<li><div class="fsinput_text">' + value + '</div><div class="fsinput_del" href="#"></div></li>';
		elem.siblings('ul.fsinput_values').append(html);
	}
	
	function bindAutocomplete(element, data) {
		element.autocomplete(data, {
			width: 260,
			multiple: true,
			matchContains: true,
			multipleSeparator: ", ",
			onSelect: addValue,
			selectFirst: false
		});
	}
	
	/** 
	 * Checks the number of characters in the given textarea and updates
	 * the counter accordingly. If the length is greater than the limit,
	 * this function will return false.
	 *
	 * @param				textarea			The textarea to watch
	 * @param				limit					The limit to test
	 * @param				counter				The ID of the counter to update
	 * @return			boolean				True if we're within the limit
	 *
	 */
	function limitChars(textarea, limit, counter) {
		var text = $('#' + textarea).val(); 
		var textlength = text.length;
		var left = limit - text.length;
		$('#' + counter).text(left);
		
		if (textlength > limit) {
			return false;
		} else {
			return true;
		}
	}	
		
	/**
	 * Sends information about a Foundation to PHP, then recieves back
	 * data about its fields.
	 *
	 * @param				foundation		 The name of the Foundation whose
	 *														 fields we want.
	 * @param				action				 The PHP endpoint.
	 * @return			json					 A JSON string of the returned 
	 *														 information.
	 *
	 */
	function getFields(foundation, action) {
		var fields;
		
		$.ajax({
      url: "/admin/processor.php",
      global: false,
      type: "POST",
      data: ({foundation : foundation, mode : "get_fields"}),
      async: false,
      success: function(data) {
         fields = data;
      }
		});
		
		return fields;
	}
	 
	/** 
	 * Takes field information and draws the field at the end of the 
	 * given container.
	 *
	 * @param				elem				 The element to insert into.
	 * @param				fields			 The field information.
	 *
	 */
	 function drawFields(elem, fields) {
	 	var fields = JSON.parse(fields);
	 	for (var i in fields) {
	 		if (fields[i].type == "fstinput") {
	 			drawStructuredInput(fields[i], elem);
	 		} else if (fields[i].type = "finput") {
	 			drawInput(fields[i], elem);
	 		}
	 	}
	 }
	 
	 function buildFoundation(foundation, elem) {
	 	var fields = getFields(foundation, 'processor.php');
	 	drawFields(elem, fields);
	 }
	 
	 function drawInput(field, parent) {
	  parent.append("<label for='" + field.name + "'>" + field.label + "</label>");
	  
	  var elem = $("<div class='finput' />");
	  parent.append(elem);
	  
	  elem.append('<input class="finput" id="' + field.name + '" name="' + field.name + '" placeholder="' + field.placeholder + '" type="text" value="">');
	  
	  if ($('input.fields').length > 0) {
	  	elem.children('.finput').val($('input.fields[title="' + field.name + '"]').val());
	  }
	 }
	 
	 
	 function drawStructuredInput(field, parent) {
	  parent.append("<label for='" + field.name + "'>" + field.label + "</label>");
	  
	  var elem = $("<div class='fstinput' />");
	  parent.append(elem);
	  elem.append("<input class='finput' id='" + field.name + "' name='" + field.name + "' placeholder='" + field.placeholder + "' type='text'>");
	  elem.append("<div class='fbtn fbtn_plus' />");
	  elem.append("<ul class='fsinput_values'></ul>");
	  
	  var input = elem.children('.finput');
	  var data = removeKeys(field.data);
	  bindAutocomplete(input, data);
	  if ($('input.fields').length > 0) {
		  attachStructuredData(input.attr('id'));
		}
	 }
	 
		function attachStructuredData(field) {
			if ($('input.fields[title="' + field + '"]') &&
					$('input.fields[title="' + field + '"]').val() != 'undefined') {
				var hidden = $('input.fields[title="' + field + '"]').val();
				if (hidden != undefined) {
					var values = JSON.parse(hidden);
					var list = $('input#' + field).siblings('ul.fsinput_values');
					for (var value in values) {
						var html = "<li><div class='fsinput_text'>" + values[value] + "</div><div class='fsinput_del' href='#'></div></li>";
						list.append(html);
					}
				}
			}
		}
	
	function removeKeys(source) {
		var result = [];
		for (var i in source) {
			result.push(source[i]);
		}
		return result;
	}
	
	function switchSegment(content) {
		$("#segmented .selected_segment").removeClass('selected_segment');
		$("#segmented .segment a[title='" + content + "']").parent().addClass('selected_segment');
	}
 
	function addFoundation(elem) {
		if (elem.val() != 'undefined') {
			var foundation = elem.val();
			buildFoundation(foundation, $('#ffields'));
		} else {
			$('.alert').show();
		}
	}
		
	$(document).ready(function() {
		/**************************************************************
		 * Modal Dialog
		 *
		 *************************************************************/
		 
		 $("button#invoke").click(function() {
		   console.log("Invoking...");
		 	 openModalDialog(400, 200, null, null, null, null, null);
		 });
		 
		/**************************************************************
		 * smallSelect 
		 *
		 * smallSelects are red dropdown boxes.
		 *************************************************************/
		 	
		$('.fsselect_btn').live('click', function(e) {
			var thisBtn = $(this);		
			var thisMenu = $(this).next().next();
			var thisChildren = $(this).next().next().children();
			if (thisMenu.is(':visible')) {
				sSelHideDropdown($(this));
			} else {
				sSelShowDropdown($(this));
				
				$(document).click(function(e) {
					if ((e.target != thisMenu[0]) && 
							(e.target != thisChildren[0]) && 
							(e.target != thisBtn[0])) {
								sSelHideDropdown(thisBtn);
					}
				});
			}
		});
		
		$('.fsselect_dropdown li').live('click', function() {
			$(this).siblings().removeClass('fsselect_selected');
			$(this).addClass('fsselect_selected');
			$(this).parent().siblings("input.fsinput").val($(this).text());
			
			// Clear the current fields, then get the fields from PHP and create them.
			
			var container = $(this).parent().parent().parent().siblings("#ffields");
			var fields = getFields($(this).text(), 'processor.php');
			container.children().remove();
			drawFields(container, fields);
			
			$('.alert').hide();
			sSelHideDropdownFromSelection($(this).parent());
		});

		/**************************************************************
		 * bigSelect :: DEPRECATED
		 *
		 * bigSelects are bigger dropdown boxes with the standard grey
		 * input background-color and a red button.
		 *
		 *************************************************************/
		 
		$('.fbselect .fbtn').click(function() {
			var thisBtn = $(this);		
			var thisMenu = $(this).next();
			var thisChildren = $(this).next().children();

			if ($(this).next('.fbselect_dropdown').is(':visible')) {
				bSelHideDropdown($(this));
			} else {
				bSelShowDropdown($(this));
				
				$(document).click(function(e) {
					if ((e.target != thisMenu[0]) && 
							(e.target != thisChildren[0]) && 
							(e.target != thisBtn[0])) {
								bSelHideDropdown(thisBtn);
					}
				});
			}
		});
		
		$('.fbselect_dropdown li').click(function() {
			$('.fbselect_dropdown li').removeClass('fbselect_selected');
			$(this).addClass('fbselect_selected');
			$(this).parent().siblings("input.finput").val($(this).text());
			bSelHideDropdownFromSelection($(this).parent());
		});
		
		/**************************************************************
		 * freeInput :: DEPRECATED
		 *
		 * freeInputs collect freeform text that a user inputs into the 
		 * input box and displays this data back out as a bulleted list.
		 *
		 *************************************************************/
		 
		$('.ffinput .fbtn').click(function() {
			var input = $(this).prev().prev();
			var list  = $(this).next();
			if (input.val() != "") {
				var html = "<li>" + input.val() + "</li>";
				list.append(html);
				input.val("");
			}
		});
		
		$('.ffinput input.finput').keypress(function(e) {
			if (e.keyCode == '13' && $(this).val() != "") {
				var html = "<li>" + $(this).val() + "</li>";
				$(this).siblings('ul.ffinput_values').append(html);
				$(this).val("");
			}
		});
		
		$('.ffinput > li').live('click', function() {
			$(this).remove();
		});
		
		/**************************************************************
		 * structuredInput
		 *
		 * Structured Inputs take stored data and creates a typeahead,
		 * so that the user can quickly input information that has been
		 * added before. It displays that data back out as red boxes 
		 * with a grey deletion box.
		 *
		 *************************************************************/
		 	
		$('.fstinput .fbtn').live('click', function() {
			var input = $(this).prev();
			if (input.val() != "") {
				addValue(input, $(this).prev().val());
				input.val("");
			}
		});
		
		$('.fsinput_del').live('click', function() {
			$(this).parent().remove();
		});
		
		$('.fstinput input.finput').live('keypress', function(e) {
			if (e.keyCode == '13' && $(this).val() != "") {
				addValue($(this), $(this).val());
			}
		});
		
		$('.fstinput .ac_results ul li').live('click', function() {
			addValue($('.fstinput input.finput').val());
			$('.fstinput input.finput').val("");
		});
	
		/**************************************************************
		 * datepicker
		 *
		 * Datepickers let you select month/year combinations.
		 *
		 *************************************************************/
		
		$('.fdatepicker_btn').live('click', function() {
			var thisBtn = $(this);		
			var thisMenu = $(this).prev();
			var thisChildren = $(this).prev().children();

		
			if (thisMenu.is(':visible')) {
				thisMenu.hide();
				thisBtn.removeClass('selected');
			} else {
				thisMenu.show();
				thisBtn.addClass('selected');
				
				$(document).click(function(e) {
					if ((e.target != thisMenu[0]) && 
							(e.target != thisChildren[0]) && 
							(e.target != thisBtn[0])) {
						thisMenu.hide();
						thisBtn.removeClass('selected');
					}
				});
			}
		});
		
		$('ul.fdatepicker_values li').live('click', function() {
			$(this).siblings().removeClass('selected');
			$(this).addClass('selected');
			$(this).parent().siblings('input.fmonth').val($(this).text()).css('color', '#000000');
		});

	});