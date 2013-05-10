document.observe('dom:loaded', initNewPrivateMessage);

function initNewPrivateMessage(event) {
	$('message_title_input').observe('keyup', updateSubmit).observe('change', updateSubmit);
	$('message_text_input').observe('keyup', updateSubmit).observe('change', updateSubmit);
	checkFields();
}

function updateSubmit() {
	if (checkFields()) {
		$('new_message_submit').enable();
	} else {
		$('new_message_submit').disable();
	}
}

function checkFields() {
	var result = true;

	var val = $('message_title_input').getValue();
	if (val instanceof Error) {
		if (val.message == 'minimum')
			$('message_title_too_short').show();
		else
			$('message_title_too_long').show();
		result = false;
	} else {
		$('message_title_too_short').hide();
		$('message_title_too_long').hide();
	}
	
	var val = $('message_text_input').getValue();
	if (val instanceof Error) {
		$('message_text_too_long').show();
		result = false;
	} else {
		$('message_text_too_long').hide();
	}
	
	return result;
}