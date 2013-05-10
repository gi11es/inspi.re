$('message_input').observe('keyup', updateSubmit).observe('change', updateSubmit);

function updateSubmit() {
	if (checkFields()) {
		$('appeal_submit').enable();
	} else {
		$('appeal_submit').disable();
	}
}

function checkFields() {
	var result = true;

	var val = $('message_input').getValue();
	if (val instanceof Error) {
		if (val.message == 'minimum')
			$('message_too_short').show();
		else
			$('message_too_long').show();
		result = false;
	} else {
		$('message_too_short').hide();
		$('message_too_long').hide();
	}
	
	return result;
}

updateSubmit();