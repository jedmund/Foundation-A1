	function makeData(children) {
		// Get the children of each section.
		var projects = $('ul#projects').children();
		var archived = $('ul#archives').children();
		
		// Set empty variables.
		var pid = 0;
		var sequence = 0;
		var data = new Object;		
		
		// Change the sequence for active projects and add to the
		// data object.
		for (var i = 0; i < projects.length; i++) {
			pid = $(projects[i]).attr('data-pid');
			sequence = i+1;
			
			$(projects[i]).attr('data-sequence', sequence);
			data[pid] = ["active", sequence];
		}
		
		// Change the sequenced for archived projects and add to the
		// data object.
		for (var i = 0; i < archived.length; i++) {
			pid = $(archived[i]).attr('data-pid');
			sequence = i+1;
			
			$(archived[i]).attr('data-sequence', sequence);
			data[pid] = ["archived", sequence];
		}
				
		return data;
	}
	
	var posStart = function(e, ui) {
		el = ui.item.get(0); 
		var index = $(this).children('li').index(el); 
  } 

	var posStop = function(e, ui) { 
		var index = $(this).children('li').index(el); 
		var children = $(this).children();
		
		var data = JSON.stringify(makeData());
		$.post(
			"/admin/processor.php", 
			{ mode: 	  'reorder_projects', 
				projects: data
			}, function(data) {
				//console.log(data);
			}
		);
	}
	
	$(document).ready(function() {

		// Make the Projects and Archives lists sortable and linked.
		$("ul#projects, ul#archives").sortable({
			connectWith: ".connected",
			forcePlaceholderSize: true,
			placeholder: "placeholder",
			remove: function(event, ui) {
				if (ui.item.hasClass('active')) {
					ui.item.removeClass('active').addClass('archived');
					var size = $('ul#archives').children().size();
					if (size == 2 && ui.item.siblings('.empty')) {
						ui.item.siblings('.empty').remove();
					}
				} else if (ui.item.hasClass('archived')) {
					ui.item.removeClass('archived').addClass('active');	
					var size = $('ul#archives').children().size();
					 if (size == 0) {
						$('ul#archives').append('<li class="empty">No projects</li>');
					}
				}
			},
			start: posStart,
			stop: posStop
		}).disableSelection();
			
		// The event for deletion.
		$('.action.del').click(function() {
			// Get information about the project and prepare the modal dialog.
			var id = $(this).parent().parent().parent().attr('data-pid');
			var title = $(this).parent().siblings('h3').text();
			var elem = $(this).parent().parent().parent();
			
			var str = "You are about to delete \"" + title + "\" permanently. <br> Are you sure you want to continue?";
				
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
						$(elem).fadeOut(500), function() { 
							$(this).remove(); 
						};
					}
				);
			};
									
			openModalDialog(400, 90, content, actions, null, complete);
		});
			
		// The event for editing redirects you to the correct page based
		// on the selected project.
		$('.action.edit').click(function() { 				
		window.location.href = "edit?p=" + selected;
		});
	});