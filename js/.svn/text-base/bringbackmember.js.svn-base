document.observe('dom:loaded', initNewPrivateMessage);

function initNewPrivateMessage(event) {
	$('message_text_input').observe('keyup', updateSubmit).observe('change', updateSubmit);
	checkFields();
	updateSubmit();
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
	
	var val = $('message_text_input').getValue();
	if (val instanceof Error) {
		if (val.message == 'minimum')
			$('message_text_too_short').show();
		else
			$('message_text_too_long').show();
		result = false;
	} else {
		$('message_text_too_short').hide();
		$('message_text_too_long').hide();
	}
	
	return result;
}