document.observe('dom:loaded', initNewDiscussionThread);

function initNewDiscussionThread(event) {
	$('thread_title_input').observe('keyup', updateSubmit).observe('change', updateSubmit);
	$('thread_text_input').observe('keyup', updateSubmit).observe('change', updateSubmit);
	checkFields();
}

function updateSubmit() {
	if (checkFields()) {
		$('new_thread_submit').enable();
	} else {
		$('new_thread_submit').disable();
	}
}

function checkFields() {
	var result = true;

	var val = $('thread_title_input').getValue();
	if (val instanceof Error) {
		if (val.message == 'minimum')
			$('thread_title_too_short').show();
		else
			$('thread_title_too_long').show();
		result = false;
	} else {
		$('thread_title_too_short').hide();
		$('thread_title_too_long').hide();
	}
	
	var val = $('thread_text_input').getValue();
	if (val instanceof Error) {
		if (val.message == 'minimum')
			$('thread_text_too_short').show();
		else
			$('thread_text_too_long').show();
		result = false;
	} else {
		$('thread_text_too_short').hide();
		$('thread_text_too_long').hide();
	}
	
	return result;
}