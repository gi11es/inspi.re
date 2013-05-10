document.observe('dom:loaded', initNewDiscussionPost);
var enabled = false;

function initNewDiscussionPost() {
	$('text').focus();
	$('text').observe('keyup', updateSubmit).observe('change', updateSubmit);
	checkFields();
}

function submitPost() {
	if (enabled) $('new_post').submit();
	else {
		if ($('post_too_short').visible())
			new Effect.Pulsate($('post_too_short'), { pulses: 2, duration: 0.8 });
		else if ($('post_too_long').visible())
			new Effect.Pulsate($('post_too_long'), { pulses: 2, duration: 0.8 });
	}
}

function updateSubmit() {
	if (checkFields()) {
		enabled = true;
	} else {
		enabled = false;
	}
}

function checkFields() {
	var result = true;

	var val = $('text').getValue();
	if (val instanceof Error) {
		if (val.message == 'minimum')
			$('post_too_short').show();
		else
			$('post_too_long').show();
		result = false;
	} else {
		$('post_too_short').hide();
		$('post_too_long').hide();
	}
	
	return result;
}