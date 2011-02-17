function openModalDialog(w, h, content, actionElems, loadFunc, completeFunc, closeFunc) {
	displayOverlay();
	displayDialog(w, h, content, actionElems, loadFunc, completeFunc, closeFunc);
}

function displayOverlay() { 
  var overlay = document.createElement("div"); 
 	overlay.id = "modal_overlay"; 
  document.body.appendChild(overlay); 
} 
 
function displayDialog(w, h, content, actionElems, loadFunc, completeFunc, closeFunc) { 
    // We need to create the dialog and its content and actions in the DOM 
    // before doing anything else, and assign them their respective IDs. 
    var wrapper = document.createElement("div");
    var box     = document.createElement("div"); 
    var actions = document.createElement("div"); 
 
    wrapper.id = "modal_wrapper"; 
    box.id		 = "modal_box"; 
    actions.id = "modal_actions"; 
 
    // After setting up the elements in the DOM, we append them in order 
    // to ensure correct hierarchy. 
    document.body.appendChild(wrapper); 
    wrapper.appendChild(box); 
    wrapper.appendChild(actions); 
 
    // We need to do some smart CSS to ensure that everything ends up 
    // where it should be. 
    //  
    // The dialog box should be centered on the page with our specified 
    // width and height. 
    // 
    // The actions bar should always be at the bottom of the dialog box, 
    // however tall it may be. 
    var padding = 10;
    var actionWidth = 71;
    
    var wrapperCSS = {
    	"width"					: w + "px",
    	"margin-left"		: -(w/2) + "px"
    };
    
    var boxCSS = { 
      "min-height"  : (h-(padding*2)) + "px", 
      "width"       : (w-actionWidth-(padding*2)) + "px" 
    }; 

    var actionsCSS = { 
    	"min-height" : (h + padding) + "px",
    	"width"			 : (50 - padding) + "px"  
    }; 
 
    // Afterwards, we need to apply the smart CSS to our new elements.    
    $("#modal_wrapper").css(wrapperCSS);
    $("#modal_box").css(boxCSS);
    $("#modal_actions").css(actionsCSS); 
 
    // Now, we append the actions that we passed in to our elements. 
    // We have to create the list structure so that it formats correctly. 
    $("#modal_actions").append("<ul id='modal_actionlist' />"); 
 
 
    // Action Elements are defined by the class of the contextual action we 
    // want the user to be able to take. Typically, there are either one or two
    // actions: Cancel, or Confirm/Cancel combination.
    //
    // By default, we use the Cancel-only case.
    if (typeof(actionElems) != 'undefined') { 
 	     // Primary action (e.g. Submit)    
	    if (typeof(actionElems.primary) != 'undefined') { 
	      $("#modal_actions ul").append("<li id='primary' class='btn_confirm'></li>"); 
	      $("#modal_actions ul li." + actionElems.primary).append("<a class='nav_link' href='#'></a>");
	    }
	    
	    // Secondary action (e.g. Cancel) 
	    if (typeof(actionElems.cancel) != 'undefined') { 
	      $("#modal_actions ul").append("<li id='cancel' class='btn_delete'/>"); 
	      $("#modal_actions ul li." + actionElems.cancel).append("<a class='nav_link' href='#'></a>");
	    }  
		} else {
			$("#modal_actions ul").append("<li id='cancel' class='btn_delete'/>"); 
	    $("#modal_actions ul li.btn_delete").append("<a class='nav_link' href='#'></a>");
		}  
    // We then proceed to load the given URL into the modal dialog. 
    // If a success function was passed through as well, we execute it. 
    if (content['type'] == 'url') {
	    if (loadFunc == undefined) { 
	        $("div#modal_box").load(content['value']); 
	    } else { 
	        $("div#modal_box").load(content['value'], loadFunc); 
	    } 
	  } else if (content['type'] == 'text') {
    	$("div#modal_box").html(content['value']);
    }
    
    if (typeof(completeFunc) != 'undefined') {
    	$("#primary").live('click', function() {
    		completeFunc();
    		closeDialog();
    	});
    }
} 

/**
 * This function closes the current active modal dialog.
 *
 */
function closeDialog() {
	// Disable the dialog box 
  $("div#modal_wrapper").animate( {opacity: 0.0}, 500, function() { 
      $(this).remove(); 
  }); 

  // Disable the overlay. 
  $("div#modal_overlay").animate( {opacity: 0.0}, 500, function() { 
      $(this).remove(); 
  }); 
} 

$('#cancel').live('click', function() {
	if (typeof(closeFunc) != 'undefined') {
		closeFunc();
	}
	closeDialog();
});

$(document).keydown(function(e) { 
    if (e.which == 27) {
    	if (typeof(closeFunc) != 'undefined') { 
	    	closeFunc();
	    }
    	closeDialog();
    } 
}); 
 