$('theme_title_input').observe('keyup', updateSubmit).observe('change', updateSubmit);
$('theme_description_input').observe('keyup', updateSubmit).observe('change', updateSubmit);

function updateSubmit() {
	if (checkFields()) {
		$('new_theme_submit').enable();
	} else {
		$('new_theme_submit').disable();
	}
}

function checkFields() {
	var result = true;

	var val = $('theme_title_input').getValue();
	if (val instanceof Error) {
		if (val.message == 'minimum')
			$('theme_title_too_short').show();
		else
			$('theme_title_too_long').show();
		result = false;
	} else {
		$('theme_title_too_short').hide();
		$('theme_title_too_long').hide();
	}
	
	var val = $('theme_description_input').getValue();
	if (val instanceof Error) {
		if (val.message == 'maximum') {
			$('theme_description_too_long').show();
			result = false;
		}
	} else {
		$('theme_description_too_long').hide();
	}
	
	return result;
}