$(document).ready(function() {
	var html = "<div id='login_pw_box'><label for='password'>Enter your password</label><input id='password' name='password' type='password'><div class='go'></div></div>";
	var visible = false;
	var clickedOn;
	
	$('ul#login li').click(function() {
		if (!visible) {
			$(this).css({'background', '#A5A5A5');
			$('#login_main').append(html);
			clickedOn = $(this).attr('id');
			visible = true;
		} else if (clickedOn == $(this).attr('id')) {
			$(this).css('background', '#E9E9E9');
			$('#login_pw_box').remove();
			visible = false;
		}
	});
});